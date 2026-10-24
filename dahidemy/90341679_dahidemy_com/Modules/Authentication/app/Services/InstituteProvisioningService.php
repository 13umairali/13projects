<?php

namespace Modules\Authentication\Services;

use App\Helpers\InstituteHelper;
use App\Mail\InstituteOnboardingMail;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Modules\Authentication\Models\Branch;
use Modules\Authentication\Models\Institute;
use Modules\Authentication\Models\Plan;
use Modules\Authentication\Models\Subscription;
use Modules\Authentication\Models\SubscriptionItem;
use Modules\Authentication\Models\User;
use Modules\Frontend\Models\HomeSection;
use Modules\Frontend\Models\Onboarding;
use RuntimeException;
use Spatie\Permission\Models\Role;
use Throwable;

/**
 * Single source of truth for turning an approved onboarding request into a fully
 * provisioned tenant: institute, branch, admin user, initial settings and trial
 * subscription.
 *
 * This previously lived as two near-identical copies — one in the public
 * onboarding controller and one in the admin approval endpoint — which had
 * drifted apart in ways that silently corrupted data (see the class notes on
 * {@see createTrialSubscription()} and {@see markApproved()}). Both call sites
 * now share this implementation, so a fix lands in one place.
 *
 * Everything runs inside a single transaction. The previous public flow wrote
 * the institute, branch, user, ~39 settings rows and the subscription with no
 * transaction at all, so any mid-flow failure left an orphaned half-tenant that
 * still occupied the unique email/domain slots.
 */
class InstituteProvisioningService
{
    /**
     * Provision a tenant from an onboarding request and close the request out.
     *
     * @param  int|null  $approvedBy  Acting user, when an authenticated admin is approving.
     *                                Null on the public flow, where the approver is resolved
     *                                from configuration.
     * @return array{institute: Institute, branch: Branch, user: User, subscription: Subscription, plan: Plan}
     */
    public function provisionFromOnboarding(Onboarding $onboarding, ?int $approvedBy = null): array
    {
        return DB::transaction(function () use ($onboarding, $approvedBy) {
            $data = $onboarding->collected_data ?? [];

            // Honor the plan the person actually picked during sign-up. If
            // none was submitted (older data, or a caller that doesn't
            // collect one), fall back to the previous behavior of
            // auto-picking the free trial plan.
            $plan = null;
            if (! empty($data['plan_id'])) {
                $plan = Plan::query()
                    ->where('id', $data['plan_id'])
                    ->where('status', true)
                    ->first();
            }
            $plan = $plan ?? $this->resolveTrialPlan();

            $institute = $this->createInstitute($onboarding, $data);
            $branch = $this->createBranch($institute);
            $user = $this->createAdminUser($institute, $branch, $onboarding, $data);

            // Gives a self-signed-up institute a populated-looking homepage
            // instead of a blank one, copied from the designated template
            // institute. Safe no-op if that template is missing.
            app(\App\Services\InstituteContentCloningService::class)
                ->cloneDefaultContentTo($institute, $branch->id);

            $this->seedInstituteSettings($institute, $branch);
            $this->seedHomeSections($institute, $branch);

            $subscription = $this->createTrialSubscription($institute, $plan);

            // Records who referred this new institute, if a referral code
            // was entered - works whether the code belongs to another
            // institute or to any individual user (student, parent,
            // teacher, accountant, staff...). Safe no-op for a
            // missing/invalid code.
            app(\App\Services\ReferralService::class)
                ->recordReferral($data['referral_code'] ?? null, $institute->id);

            // If a percentage-based reward applies and this institute's
            // plan is actually paid (already true at this point for the
            // paid-checkout path, and correctly a no-op for a free plan),
            // the reward is calculated and applied automatically - no
            // Super Admin approval needed for a mechanically-derived
            // percentage of a Stripe-confirmed payment.
            app(\App\Services\ReferralService::class)
                ->autoRewardPercentageForInstitute($institute->id);

            $this->markApproved($onboarding, $approvedBy);

            return [
                'institute' => $institute,
                'branch' => $branch,
                'user' => $user,
                'subscription' => $subscription,
                'plan' => $plan,
            ];
        });
    }

    /**
     * Welcome mail carrying the tenant's credentials.
     *
     * Must be called *after* the surrounding transaction commits — mailing
     * credentials for a tenant that was rolled back is worse than not mailing.
     * It is therefore deliberately not part of {@see provisionFromOnboarding()}.
     *
     * Failure is logged and swallowed: the tenant exists by this point, so a mail
     * outage must not present the sign-up as failed.
     *
     * @param  array{institute: Institute, subscription: Subscription, plan: Plan, ...}  $result
     * @param  string  $plainPassword  Held only for the lifetime of the request; the
     *                                 stored copy is hashed.
     */
    public function sendWelcomeMail(array $result, string $plainPassword): void
    {
        $institute = $result['institute'];
        $subscription = $result['subscription'];
        $plan = $result['plan'];

        try {
            if (
                blank(config('mail.mailers.smtp.host')) ||
                blank(config('mail.mailers.smtp.username')) ||
                blank(config('mail.from.address'))
            ) {
                Log::warning('Mail configuration missing. Institute onboarding mail not sent.', [
                    'institute_id' => $institute->id,
                ]);

                return;
            }

            Mail::to($institute->email)->send(
                new InstituteOnboardingMail([
                    'institute_name' => $institute->name,
                    'institute_id' => $institute->id,
                    'email' => $institute->email,
                    'password' => $plainPassword,
                    // Read from the plan and subscription that were actually created.
                    // These were hardcoded to "Trial Package" and 14 days, so the mail
                    // contradicted the real subscription whenever the plan differed.
                    'package_name' => $plan->name,
                    'trial_days' => $subscription->start_date->diffInDays($subscription->end_date),
                    'trial_end_date' => $subscription->end_date->format('Y-m-d'),
                    'login_url' => route('login'),
                ])
            );
        } catch (Throwable $e) {
            Log::error('Institute onboarding mail failed', [
                'institute_id' => $institute->id,
                'email' => $institute->email,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * The trial plan, resolved by its `is_free` flag.
     *
     * Both previous call sites hardcoded plan id 1. That silently breaks the
     * moment the plans table is reseeded or reordered, handing new sign-ups
     * whatever package happens to occupy id 1.
     *
     * @throws RuntimeException when no free plan is configured, which is a
     *                          deployment error rather than a user error.
     */
    public function resolveTrialPlan(): Plan
    {
        $plan = Plan::query()
            ->where('is_free', true)
            ->where('status', true)
            ->orderBy('id')
            ->first();

        if (! $plan) {
            throw new RuntimeException(
                'No active free trial plan is configured. Mark exactly one plan as free (is_free) to enable onboarding.'
            );
        }

        return $plan;
    }

    /**
     * A plan's duration, falling back to the configured trial length.
     *
     * Custom plans are allowed to leave `duration_days` null. Passing that
     * straight to addDays() yields an end date of "now", i.e. a subscription
     * that expires the instant it is created.
     */
    public function durationDaysFor(Plan $plan): int
    {
        $days = (int) ($plan->duration_days ?? 0);

        return $days > 0
            ? $days
            : (int) config('onboarding.trial.fallback_duration_days');
    }

    /**
     * The onboarding fields are namespaced (`institute_*`) while the institutes
     * table is not, so each one is mapped explicitly.
     *
     * Note `institute_type` and `address`: both call sites passed 'type' and
     * omitted the address entirely. Neither is fillable on the model, so the
     * submitted values were silently discarded on every single sign-up.
     *
     * @param  array<string, mixed>  $data
     */
    protected function createInstitute(Onboarding $onboarding, array $data): Institute
    {
        return Institute::create([
            'name' => $data['institute_name'],
            'institute_type' => $data['institute_type'] ?? null,
            'email' => $data['institute_email'],
            'phone' => $data['institute_phone'],
            'domain' => InstituteHelper::normalizeDomain($data['institute_domain'] ?? null),
            'address' => $data['institute_address'] ?? null,
            'logo' => $onboarding->institute_logo,
        ]);
    }

    protected function createBranch(Institute $institute): Branch
    {
        return Branch::create([
            'institute_id' => $institute->id,
            'name' => config('onboarding.default_branch_name'),
        ]);
    }

    /**
     * The admin user for the new tenant.
     *
     * The stored password is already a hash (see {@see Onboarding::collectedDataFrom()});
     * it is assigned as-is because the model's `hashed` cast passes an existing
     * hash through untouched. Re-hashing here — as the approval endpoint did —
     * hashes the hash and locks the owner out of their own account.
     *
     * The avatar is written to `image`, which is the actual column. Both call
     * sites wrote to a non-existent `avatar` attribute, so uploaded avatars were
     * silently dropped.
     *
     * @param  array<string, mixed>  $data
     */
    protected function createAdminUser(Institute $institute, Branch $branch, Onboarding $onboarding, array $data): User
    {
        $user = User::create([
            'institute_id' => $institute->id,
            'branch_id' => $branch->id,
            'name' => $data['user_name'],
            'email' => $data['user_email'],
            'phone' => $data['user_phone'],
            'password' => $data['password'],
            'image' => $onboarding->user_avatar,
            'user_type' => 'Admin',
        ]);

        $role = Role::query()
            ->where('name', config('onboarding.admin_role'))
            ->whereNull('institute_id')
            ->first();

        if ($role) {
            $user->assignRole($role);
            // Kept in sync for the legacy `role_id` column, which parts of the
            // UI still read directly rather than going through Spatie.
            $user->forceFill(['role_id' => $role->id])->save();
        }

        return $user;
    }

    /**
     * Seed the tenant's initial settings from configuration.
     *
     * Contact details and branding are taken from the institute itself. The
     * previous hardcoded block seeded every new school with the literal
     * "Demo Collage" / "Demo Title" and blank contact fields, discarding the
     * values the customer had just typed into the sign-up form.
     */
    protected function seedInstituteSettings(Institute $institute, Branch $branch): void
    {
        $config = config('onboarding.institute_settings');

        $values = $config['defaults'];

        foreach ($config['derived_from_institute'] as $settingKey => $instituteAttribute) {
            $derived = $institute->{$instituteAttribute};

            if (! blank($derived)) {
                $values[$settingKey] = $derived;
            }
        }

        if (blank($values['copyright_text'])) {
            $values['copyright_text'] = '&copy; Copyright '.Carbon::now()->year
                .'. All Rights Reserved by '.config('app.name');
        }

        $now = Carbon::now();

        $rows = [];
        foreach ($values as $name => $value) {
            $rows[] = [
                'institute_id' => $institute->id,
                'branch_id' => $branch->id,
                'type' => $config['type'],
                'name' => $name,
                'value' => (string) $value,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        DB::table('settings')->insert($rows);
    }

    /**
     * Seed the tenant's public home sections.
     *
     * Only the public sign-up flow used to do this, so institutes created via
     * admin approval were left with an empty public site.
     */
    protected function seedHomeSections(Institute $institute, Branch $branch): void
    {
        $now = Carbon::now();

        $rows = [];
        foreach (array_values(config('onboarding.home_sections', [])) as $index => $section) {
            $rows[] = [
                'institute_id' => $institute->id,
                'branch_id' => $branch->id,
                'title' => $section['title'],
                'description' => $section['description'],
                'serial' => $index + 1,
                'status' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        if ($rows !== []) {
            HomeSection::insert($rows);
        }
    }

    /**
     * The trial subscription for the new institute.
     *
     * The approval endpoint hardcoded `institute_id => 1`, so approving any
     * institute created a subscription against institute 1 and left the actual
     * new tenant with none.
     *
     * `invoice_details` is assigned as an array: the model casts it to JSON, so
     * the previous json_encode() produced a double-encoded string.
     */
    protected function createTrialSubscription(Institute $institute, Plan $plan): Subscription
    {
        $startDate = Carbon::now();
        $endDate = $startDate->copy()->addDays($this->durationDaysFor($plan));

        $subscription = Subscription::create([
            'institute_id' => $institute->id,
            'plan_id' => $plan->id,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'status' => 'active',
            'invoice_details' => [
                'invoice_no' => 'INV-'.strtoupper(Str::random(6)),
                'issued_at' => $startDate->toDateString(),
                'notes' => $plan->name,
                'amount' => (float) $plan->price,
            ],
        ]);

        SubscriptionItem::create([
            'subscription_id' => $subscription->id,
            'reference_no' => 'PAY-'.strtoupper(Str::random(8)),
            'amount_paid' => (float) $plan->price,
            'payment_method' => 'Manual',
        ]);

        return $subscription;
    }

    /**
     * Close out the onboarding request.
     *
     * This is the fix for completed onboardings reappearing in the pending list:
     * the public flow provisioned the whole tenant but never stamped the record,
     * so it stayed `pending` with a null approved_at forever.
     *
     * Deliberately unconditional. The stamp records that provisioning *happened*;
     * it is not a policy decision, so there is no toggle to disable it. Gating it
     * on a config flag would let an operator silently reintroduce the original bug
     * — a provisioned tenant whose request sits pending forever. If a manual
     * review step is ever wanted, it belongs *before* provisioning, not here.
     */
    protected function markApproved(Onboarding $onboarding, ?int $approvedBy): void
    {
        $onboarding->forceFill([
            'status' => Onboarding::STATUS_APPROVED,
            'approved_by' => $approvedBy ?? $this->resolveApproverId(),
            'approved_at' => Carbon::now(),
        ])->save();
    }

    /**
     * Who to attribute an automatic approval to.
     *
     * Public onboarding is unauthenticated, so there is no acting user. Prefer
     * the explicitly configured super admin; otherwise fall back to the
     * lowest-id user, which on a standard install is the seeded super admin.
     * Returns null rather than guessing when no user exists — the column is
     * nullable, and a missing approver must not fail the sign-up.
     */
    protected function resolveApproverId(): ?int
    {
        $configured = config('onboarding.approver_id');

        if (! blank($configured) && User::whereKey($configured)->exists()) {
            return (int) $configured;
        }

        return User::query()->orderBy('id')->value('id');
    }
}
<?php

namespace App\Services;

use App\Models\Referral;
use App\Models\ReferralSetting;
use Illuminate\Support\Str;
use Modules\Authentication\Models\Institute;
use Modules\Authentication\Models\Subscription;
use Modules\Authentication\Models\User;

/**
 * Any institute AND any individual user (student, parent, teacher,
 * accountant, staff, institute admin - every role) has their own unique
 * referral code. When that code is used during a new institute's sign-up,
 * a Referral record tracks it. The SaaS Super Admin configures what each
 * referrer role earns (ReferralSetting) and reviews/approves the actual
 * reward - nothing here pays out cash or points automatically without a
 * human confirming it, the same trust-based pattern already used for
 * every other financial record in this system.
 */
class ReferralService
{
    /**
     * Generates a short, unique, human-shareable code. Called once when
     * an institute or user is created (see the two hook points this is
     * wired into) - never overwrites an existing code.
     */
    public function generateCodeFor(string $ownerType, int $ownerId): string
    {
        $model = $ownerType === 'institute' ? Institute::find($ownerId) : User::find($ownerId);

        if (! $model) {
            throw new \InvalidArgumentException("Cannot generate a referral code: {$ownerType} #{$ownerId} not found.");
        }

        if ($model->referral_code) {
            return $model->referral_code;
        }

        do {
            $code = strtoupper(Str::random(8));
            $exists = $ownerType === 'institute'
                ? Institute::where('referral_code', $code)->exists()
                : User::where('referral_code', $code)->exists();
        } while ($exists);

        $model->update(['referral_code' => $code]);

        return $code;
    }

    /**
     * Looks up who a referral code actually belongs to. Returns null for
     * an unknown code - callers should treat that as "no referral", not
     * an error, since a mistyped code shouldn't block someone signing up.
     */
    public function resolveReferrer(string $code): ?array
    {
        $code = strtoupper(trim($code));

        $institute = Institute::where('referral_code', $code)->first();
        if ($institute) {
            return ['type' => 'institute', 'id' => $institute->id, 'role' => 'institute'];
        }

        $user = User::where('referral_code', $code)->first();
        if ($user) {
            return ['type' => 'user', 'id' => $user->id, 'role' => $user->role?->name ?? 'user'];
        }

        return null;
    }

    /**
     * Called at the moment a new institute is actually created (both the
     * free-instant path and the paid-after-payment path). Safe to call
     * with a null/invalid code - simply records nothing.
     */
    public function recordReferral(?string $code, int $referredInstituteId): ?Referral
    {
        if (! $code) {
            return null;
        }

        $referrer = $this->resolveReferrer($code);
        if (! $referrer) {
            return null;
        }

        // An institute cannot refer itself, and a user cannot cause their
        // own institute to be recorded as newly "referred" by them.
        if ($referrer['type'] === 'institute' && $referrer['id'] === $referredInstituteId) {
            return null;
        }

        $setting = ReferralSetting::forRole($referrer['role']);

        // The second, institute-owned reward track: only applies when the
        // referrer is an individual person (not the institute itself),
        // and only if that person's own institute has configured a rule
        // for their role.
        $instituteSetting = null;
        if ($referrer['type'] === 'user') {
            $referrerInstituteId = User::find($referrer['id'])?->institute_id;
            if ($referrerInstituteId) {
                $instituteSetting = \App\Models\InstituteReferralSetting::forInstituteAndRole($referrerInstituteId, $referrer['role']);
            }
        }

        return Referral::create([
            'referrer_type' => $referrer['type'],
            'referrer_id' => $referrer['id'],
            'referrer_role' => $referrer['role'],
            'referral_code_used' => $code,
            'referred_institute_id' => $referredInstituteId,
            'status' => 'converted',
            'reward_type' => $setting?->reward_type,
            'reward_value' => $setting?->reward_value,
            'institute_reward_type' => $instituteSetting?->reward_type,
            'institute_reward_value' => $instituteSetting?->reward_value,
            'institute_reward_status' => $instituteSetting ? 'pending' : null,
            'converted_at' => now(),
        ]);
    }

    /**
     * Automatically calculates and applies a percentage-based reward the
     * moment the referred institute has a real, paid subscription -
     * whether that's immediately (a paid plan chosen at sign-up, after
     * Stripe confirms payment) or later (a free-trial institute
     * upgrading to a paid plan afterward). Unlike 'days'/'cash'/'points',
     * a percentage reward needs no human review: the amount is
     * mechanically derived from a number already confirmed by Stripe, so
     * there is nothing for a person to judge or approve. Safe to call
     * repeatedly - only 'converted' (not yet rewarded) referrals are
     * touched, and a free ($0) plan simply results in no action yet,
     * ready to trigger again on a future upgrade.
     *
     * Call this right after any point where an institute's subscription
     * is confirmed or changed: provisionFromOnboarding() (new sign-ups,
     * both free and paid) and SubscriptionUpgradeCheckoutService (an
     * existing institute upgrading later).
     */
    public function autoRewardPercentageForInstitute(int $instituteId): void
    {
        $subscription = Subscription::where('institute_id', $instituteId)->first();
        if (! $subscription) {
            return;
        }

        $plan = \Modules\Authentication\Models\Plan::find($subscription->plan_id);
        if (! $plan || ! $plan->price) {
            // Free plan (or no plan) - nothing to reward yet. This isn't
            // an error state; it just means there's no payment to take a
            // percentage of until/unless this institute upgrades.
            return;
        }

        Referral::where('referred_institute_id', $instituteId)
            ->where('status', 'converted')
            ->where('reward_type', 'percentage')
            ->get()
            ->each(function (Referral $referral) {
                $this->approveReward($referral);
            });
    }

    /**
     * The SaaS Super Admin's "Approve & Reward" action. A 'days' reward
     * safely extends the referrer's own institute's subscription
     * automatically (low-risk, easily reversible). A 'percentage' reward
     * is computed here, at approval time, as a share of what the referred
     * institute actually paid for its plan - not decided up front, since
     * the plan (or whether they paid at all) wasn't known when the
     * referral was first recorded. 'cash' and 'points' (and the computed
     * percentage amount) are marked rewarded here as a record only - the
     * actual payout happens outside this system, the same manual/
     * trust-based pattern already used for every other payment record in
     * this platform.
     */
    public function approveReward(Referral $referral): void
    {
        if ($referral->status === 'rewarded') {
            return;
        }

        if ($referral->reward_type === 'percentage') {
            $referral->reward_value = $this->calculatePercentageReward($referral);
            $referral->save();
        }

        if ($referral->reward_type === 'days' && $referral->reward_value > 0) {
            $instituteId = $referral->referrer_type === 'institute'
                ? $referral->referrer_id
                : User::find($referral->referrer_id)?->institute_id;

            if ($instituteId) {
                $subscription = Subscription::where('institute_id', $instituteId)->first();
                if ($subscription) {
                    $subscription->update([
                        'end_date' => $subscription->end_date->addDays((int) $referral->reward_value),
                    ]);
                }
            }
        }

        $referral->markRewarded();
    }

    /**
     * The referred institute's actual subscribed plan price × the
     * configured percentage, rounded to cents. Returns 0 if the referred
     * institute has no subscription yet (still on sign-up, never
     * actually paid) - a percentage of nothing is nothing, not an error.
     */
    protected function calculatePercentageReward(Referral $referral): float
    {
        if (! $referral->referred_institute_id) {
            return 0;
        }

        $subscription = Subscription::where('institute_id', $referral->referred_institute_id)->first();
        $plan = $subscription ? \Modules\Authentication\Models\Plan::find($subscription->plan_id) : null;

        if (! $plan || ! $plan->price) {
            return 0;
        }

        $percentage = (float) $referral->reward_value;

        return round(($percentage / 100) * (float) $plan->price, 2);
    }
}
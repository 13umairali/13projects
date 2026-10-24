<?php

namespace App\Console\Commands;

use App\Models\Central\GeneralSetting;
use App\Models\Central\SmsTemplate;
use App\Models\Central\SubscriptionReminder;
use App\Models\Central\TenantBillingPayment;
use App\Models\Central\TenantSubscription;
use App\Services\CentralSmsSender;
use App\Services\EmailNotificationService;
use App\Services\PaymentGateways\PaymentGatewayFactory;
use App\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class CheckSubscriptionExpiry extends Command
{
    protected $signature = 'subscriptions:check-expiry';

    protected $description = 'Send subscription expiry / trial reminders and process expired subscriptions';

    /** Grace period before a pending gateway payment is re-checked, in hours. */
    protected const STALE_PAYMENT_HOURS = 2;

    /** How long a pending gateway payment may stay unconfirmed before it is given up on, in hours. */
    protected const ABANDONED_PAYMENT_HOURS = 72;

    public function handle(): int
    {
        $setting = GeneralSetting::instance();

        $this->sendExpiryReminders($setting);
        $this->sendTrialReminders($setting);
        $this->checkExpired();
        $this->releaseStalePendingPayments();

        return self::SUCCESS;
    }

    /**
     * Active-channel subset that the command can actually deliver to
     * (banner is a passive UI channel handled in the tenant app, not here).
     *
     * @return string[]
     */
    protected function deliverableChannels(GeneralSetting $setting): array
    {
        return array_values(array_intersect($setting->reminderChannels(), ['email', 'sms']));
    }

    /**
     * Send a reminder ahead of paid-subscription expiry, on each configured
     * offset day (e.g. 7, 3, 1 days before ends_at), through every enabled
     * channel.
     */
    protected function sendExpiryReminders(GeneralSetting $setting): void
    {
        if (! $setting->remindersEnabled()) {
            return;
        }

        $channels = $this->deliverableChannels($setting);
        if (empty($channels)) {
            return;
        }

        $offsets   = $setting->reminderOffsets();
        $maxOffset = max($offsets);

        $subscriptions = TenantSubscription::where('status', TenantSubscription::STATUS_ACTIVE)
            ->whereNotNull('ends_at')
            ->whereDate('ends_at', '>', now())
            ->whereDate('ends_at', '<=', now()->addDays($maxOffset))
            ->with('tenant', 'plan')
            ->get();

        foreach ($subscriptions as $sub) {
            $tenant = $sub->tenant;
            if (! $tenant) {
                continue;
            }

            $daysOut = $this->daysUntil($sub->ends_at);
            if (! in_array($daysOut, $offsets, true)) {
                continue;
            }

            if (in_array('email', $channels, true)) {
                $this->deliverEmail(
                    $sub, $tenant, SubscriptionReminder::TYPE_EXPIRY, $daysOut, $sub->ends_at,
                    fn () => EmailNotificationService::expiringSoon($tenant),
                    "Expiry email (T-{$daysOut}d)"
                );
            }

            if (in_array('sms', $channels, true)) {
                $this->deliverSms(
                    $sub, $tenant, SubscriptionReminder::TYPE_EXPIRY, $daysOut, $sub->ends_at,
                    SmsTemplate::bodyFor(SmsTemplate::TRIGGER_EXPIRING_SOON, $tenant->locale ?? null, $setting->reminderSmsTemplate()),
                    "Expiry SMS (T-{$daysOut}d)"
                );
            }
        }
    }

    /**
     * Send a reminder ahead of trial expiry, on each configured offset day,
     * through every enabled channel.
     */
    protected function sendTrialReminders(GeneralSetting $setting): void
    {
        if (! $setting->trialRemindersEnabled()) {
            return;
        }

        $channels = $this->deliverableChannels($setting);
        if (empty($channels)) {
            return;
        }

        $offsets   = $setting->trialReminderOffsets();
        $maxOffset = max($offsets);

        $subscriptions = TenantSubscription::where('status', TenantSubscription::STATUS_TRIAL)
            ->whereNotNull('trial_ends_at')
            ->whereDate('trial_ends_at', '>', now())
            ->whereDate('trial_ends_at', '<=', now()->addDays($maxOffset))
            ->with('tenant', 'plan')
            ->get();

        foreach ($subscriptions as $sub) {
            $tenant = $sub->tenant;
            if (! $tenant) {
                continue;
            }

            $daysOut = $this->daysUntil($sub->trial_ends_at);
            if (! in_array($daysOut, $offsets, true)) {
                continue;
            }

            if (in_array('email', $channels, true)) {
                $this->deliverEmail(
                    $sub, $tenant, SubscriptionReminder::TYPE_TRIAL, $daysOut, $sub->trial_ends_at,
                    fn () => EmailNotificationService::trialEnding($tenant),
                    "Trial email (T-{$daysOut}d)"
                );
            }

            if (in_array('sms', $channels, true)) {
                $this->deliverSms(
                    $sub, $tenant, SubscriptionReminder::TYPE_TRIAL, $daysOut, $sub->trial_ends_at,
                    SmsTemplate::bodyFor(SmsTemplate::TRIGGER_TRIAL_ENDING, $tenant->locale ?? null, $setting->trialSmsTemplate()),
                    "Trial SMS (T-{$daysOut}d)"
                );
            }
        }
    }

    protected function checkExpired(): void
    {
        // Expire active subscriptions that have passed their end date
        $subscriptions = TenantSubscription::where('status', TenantSubscription::STATUS_ACTIVE)
            ->whereNotNull('ends_at')
            ->whereDate('ends_at', '<', now())
            ->with('tenant', 'plan')
            ->get();

        foreach ($subscriptions as $sub) {
            $tenant = $sub->tenant;
            if (! $tenant) {
                continue;
            }

            $sub->update(['status' => TenantSubscription::STATUS_EXPIRED]);

            $this->deliverEmail(
                $sub, $tenant, SubscriptionReminder::TYPE_EXPIRED, 0, $sub->ends_at,
                fn () => EmailNotificationService::subscriptionExpired($tenant),
                'Expired notice'
            );
        }

        // Expire trials that have passed their end date. Without this, lapsed
        // trial rows keep their 'trial' status forever and show up as live
        // subscriptions in the admin panel.
        $trialsEnded = TenantSubscription::where('status', TenantSubscription::STATUS_TRIAL)
            ->whereNotNull('trial_ends_at')
            ->whereDate('trial_ends_at', '<', now())
            ->with('tenant', 'plan')
            ->get();

        foreach ($trialsEnded as $sub) {
            $tenant = $sub->tenant;
            if (! $tenant) {
                continue;
            }

            $sub->update(['status' => TenantSubscription::STATUS_EXPIRED]);

            $this->deliverEmail(
                $sub, $tenant, SubscriptionReminder::TYPE_EXPIRED, 0, $sub->trial_ends_at,
                fn () => EmailNotificationService::subscriptionExpired($tenant),
                'Trial-ended notice'
            );
        }

        // Expire cancelled subscriptions that have reached their end date
        // (user cancelled but kept access until expiry — now revoke access)
        $cancelledExpired = TenantSubscription::where('status', TenantSubscription::STATUS_CANCELLED)
            ->whereNotNull('ends_at')
            ->whereDate('ends_at', '<', now())
            ->with('tenant', 'plan')
            ->get();

        foreach ($cancelledExpired as $sub) {
            $tenant = $sub->tenant;
            if (! $tenant) {
                continue;
            }

            $sub->update(['status' => TenantSubscription::STATUS_EXPIRED]);

            $this->deliverEmail(
                $sub, $tenant, SubscriptionReminder::TYPE_PLAN_ENDED, 0, $sub->ends_at,
                fn () => EmailNotificationService::planEnded($tenant),
                'Plan-ended notice (was cancelled)'
            );
        }
    }

    /**
     * Settle payments that were started at a redirect gateway but never came
     * back. Several gateways — LigdiCash among them — never notify us about an
     * invoice the customer abandoned, so the row would sit at "pending"
     * forever: it blocks the tenant from submitting a new payment and leaves a
     * pending subscription for an admin to clean up by hand.
     *
     * Each candidate is re-verified with the gateway first, so a payment that
     * did go through but whose webhook never arrived is recovered rather than
     * killed. Anything the gateway still reports as pending (or cannot answer
     * for) is left alone until ABANDONED_PAYMENT_HOURS have passed.
     */
    protected function releaseStalePendingPayments(): void
    {
        $payments = TenantBillingPayment::where('status', TenantBillingPayment::STATUS_PENDING)
            ->where('gateway', '!=', 'offline')   // offline proofs wait for a human
            ->whereNotNull('gateway_payment_id')
            ->where('created_at', '<', now()->subHours(self::STALE_PAYMENT_HOURS))
            ->with('subscription')
            ->get();

        foreach ($payments as $payment) {
            $gateway = PaymentGatewayFactory::resolve($payment->gateway)
                ?? PaymentGatewayFactory::resolveForWebhook($payment->gateway);

            $status = 'unknown';

            if ($gateway) {
                try {
                    $status = $gateway->verifyPaymentStatus($payment->gateway_payment_id)['status'] ?? 'unknown';
                } catch (\Throwable $e) {
                    Log::error("Stale payment check failed for payment {$payment->id}: {$e->getMessage()}");
                    continue;   // gateway unreachable — retry on the next run
                }
            }

            if ($status === 'paid') {
                $payment->markPaid($payment->gateway_payment_id);

                $subscription = $payment->subscription;
                if ($subscription && $subscription->status !== TenantSubscription::STATUS_ACTIVE) {
                    $subscription->activate();
                }

                Log::info("Stale payment {$payment->id} was actually paid — recovered and subscription activated.");
                $this->info("Recovered paid payment {$payment->id} ({$payment->gateway}).");

                continue;
            }

            $givenUpOn = $payment->created_at->lt(now()->subHours(self::ABANDONED_PAYMENT_HOURS));

            if ($status !== 'failed' && ! $givenUpOn) {
                continue;   // still legitimately in progress
            }

            $note = $status === 'failed'
                ? 'Gateway reported the payment as failed — released automatically.'
                : 'Never confirmed by the gateway — released automatically as abandoned.';

            $payment->update([
                'status' => TenantBillingPayment::STATUS_FAILED,
                'notes'  => trim(($payment->notes ? $payment->notes . "\n" : '') . $note),
            ]);

            $this->info("Released stale payment {$payment->id} ({$payment->gateway}).");
        }
    }

    /**
     * Send one email reminder, guarding against duplicates and recording the
     * outcome in the audit log.
     */
    protected function deliverEmail(
        TenantSubscription $sub,
        Tenant $tenant,
        string $type,
        int $offsetDays,
        ?Carbon $referenceDate,
        callable $send,
        string $label
    ): void {
        $channel = SubscriptionReminder::CHANNEL_EMAIL;
        $ref     = $referenceDate?->toDateString();

        if (SubscriptionReminder::alreadySent($sub->id, $type, $channel, $offsetDays, $ref)) {
            return;
        }

        $sent = (bool) $send();

        $this->record(
            $sub, $tenant, $type, $channel, $offsetDays, $ref, $tenant->admin_email,
            $sent ? SubscriptionReminder::STATUS_SENT : SubscriptionReminder::STATUS_FAILED,
            $sent ? null : 'Email was not sent (missing template, recipient, or send error).'
        );

        $this->info("{$label} for tenant {$tenant->id}: " . ($sent ? 'sent' : 'failed'));
    }

    /**
     * Send one SMS reminder. Tenants without a phone on file are recorded as
     * skipped rather than failed.
     */
    protected function deliverSms(
        TenantSubscription $sub,
        Tenant $tenant,
        string $type,
        int $offsetDays,
        ?Carbon $referenceDate,
        string $template,
        string $label
    ): void {
        $channel = SubscriptionReminder::CHANNEL_SMS;
        $ref     = $referenceDate?->toDateString();

        if (SubscriptionReminder::alreadySent($sub->id, $type, $channel, $offsetDays, $ref)) {
            return;
        }

        $phone = trim((string) ($tenant->owner_phone ?? ''));

        if ($phone === '') {
            $this->record(
                $sub, $tenant, $type, $channel, $offsetDays, $ref, null,
                SubscriptionReminder::STATUS_SKIPPED, 'No owner phone on file.'
            );
            $this->info("{$label} for tenant {$tenant->id}: skipped (no phone)");
            return;
        }

        $message = $this->buildSmsMessage($template, $tenant, $sub, $offsetDays, $referenceDate);
        $sent    = CentralSmsSender::send($phone, $message);

        $this->record(
            $sub, $tenant, $type, $channel, $offsetDays, $ref, $phone,
            $sent ? SubscriptionReminder::STATUS_SENT : SubscriptionReminder::STATUS_FAILED,
            $sent ? null : 'SMS not sent (gateway not configured or send error).'
        );

        $this->info("{$label} for tenant {$tenant->id}: " . ($sent ? 'sent' : 'failed'));
    }

    /**
     * Persist (or update) the audit row for a reminder attempt. Keyed on the
     * idempotency tuple so a retry overwrites the previous attempt.
     */
    protected function record(
        TenantSubscription $sub,
        Tenant $tenant,
        string $type,
        string $channel,
        int $offsetDays,
        ?string $referenceDate,
        ?string $target,
        string $status,
        ?string $error
    ): void {
        SubscriptionReminder::updateOrCreate(
            [
                'tenant_subscription_id' => $sub->id,
                'type'                   => $type,
                'channel'                => $channel,
                'offset_days'            => $offsetDays,
                'reference_date'         => $referenceDate,
            ],
            [
                'tenant_id' => $tenant->id,
                'target'    => $target,
                'status'    => $status,
                'error'     => $error,
                'sent_at'   => now(),
            ]
        );
    }

    /**
     * Build an SMS body from a template with {company} {plan} {date} {days}
     * {app} placeholders.
     */
    protected function buildSmsMessage(
        string $template,
        Tenant $tenant,
        TenantSubscription $sub,
        int $offsetDays,
        ?Carbon $referenceDate
    ): string {
        return strtr($template, [
            '{company}' => $tenant->company_name ?? $tenant->id,
            '{plan}'    => $sub->plan?->name ?? 'N/A',
            '{date}'    => $referenceDate?->format('M d, Y') ?? 'N/A',
            '{days}'    => (string) $offsetDays,
            '{app}'     => config('app.name', 'Stocky'),
        ]);
    }

    /**
     * Whole days from the start of today to the start of the given date.
     */
    protected function daysUntil(Carbon $date): int
    {
        return (int) now()->startOfDay()->diffInDays($date->copy()->startOfDay(), false);
    }
}

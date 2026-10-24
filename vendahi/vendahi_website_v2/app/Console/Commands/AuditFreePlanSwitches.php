<?php

namespace App\Console\Commands;

use App\Models\Central\TenantBillingPayment;
use App\Models\Central\TenantSubscription;
use Illuminate\Console\Command;

class AuditFreePlanSwitches extends Command
{
    protected $signature = 'subscriptions:audit-free-switches {--fix : Set the flagged subscriptions to expired}';

    protected $description = 'Find never-expiring free-plan subscriptions held by tenants who previously paid (legacy one-click free switch after expiry), optionally expire them';

    public function handle(): int
    {
        $suspects = TenantSubscription::where('status', TenantSubscription::STATUS_ACTIVE)
            ->whereNull('ends_at')
            ->with('tenant', 'plan')
            ->get()
            ->filter(fn (TenantSubscription $sub) => $sub->plan?->isFree())
            ->filter(function (TenantSubscription $sub) {
                // Only flag former paying customers — tenants who signed up on
                // the free plan and never paid are legitimate.
                return TenantBillingPayment::where('tenant_id', $sub->tenant_id)
                    ->where('status', TenantBillingPayment::STATUS_PAID)
                    ->where('amount', '>', 0)
                    ->exists();
            });

        if ($suspects->isEmpty()) {
            $this->info('No suspicious free-plan subscriptions found.');

            return self::SUCCESS;
        }

        foreach ($suspects as $sub) {
            $company = $sub->tenant->company_name ?? $sub->tenant_id;
            $line = "subscription #{$sub->id} — tenant {$sub->tenant_id} ({$company}), plan \"{$sub->plan?->name}\"";

            if ($this->option('fix')) {
                $sub->update(['status' => TenantSubscription::STATUS_EXPIRED]);
                $this->info("EXPIRED: {$line}");
            } else {
                $this->line("FLAGGED: {$line}");
            }
        }

        if (! $this->option('fix')) {
            $this->comment('Review the list above, then run with --fix to set these subscriptions to expired.');
        }

        return self::SUCCESS;
    }
}

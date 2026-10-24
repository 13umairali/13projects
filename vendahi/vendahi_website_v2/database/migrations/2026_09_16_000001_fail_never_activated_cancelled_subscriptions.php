<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class FailNeverActivatedCancelledSubscriptions extends Migration
{
    /**
     * Data fix: cancelling a subscription whose payment was never completed
     * left a "cancelled" row with no billing period behind it. Those rows were
     * picked up as the tenant's current subscription, handing them the plan
     * they never paid for, and they still read as a real cancellation in the
     * admin lists.
     *
     * A cancelled row with neither ends_at nor trial_ends_at was never live, so
     * relabel it "failed". Going forward cancel() does this itself — see
     * TenantSubscription::cancellationStatus().
     */
    public function up(): void
    {
        DB::connection('central')
            ->table('tenant_subscriptions')
            ->where('status', 'cancelled')
            ->whereNull('ends_at')
            ->whereNull('trial_ends_at')
            ->update(['status' => 'failed']);
    }

    public function down(): void
    {
        // Irreversible data fix — these rows were never activated either way.
    }
}

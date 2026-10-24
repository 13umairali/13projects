<?php

namespace App\Console\Commands;

use App\Models\Subscription;
use App\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class SendSubscriptionSmsReminders extends Command
{
    protected $signature = 'subscriptions:send-sms-reminders';

    protected $description = 'Send SMS reminders before subscription renewal';

    public function handle()
    {
        // Subscriptions live in each tenant DB — this must run per tenant,
        // never on the central connection.
        foreach (Tenant::where('status', Tenant::STATUS_ACTIVE)->cursor() as $tenant) {
            try {
                $tenant->run(fn () => $this->processTenant());
            } catch (\Illuminate\Database\QueryException $e) {
                // Tenant DB not migrated for the subscriptions feature yet — skip quietly.
                if (str_contains($e->getMessage(), 'Base table or view not found')) {
                    continue;
                }
                Log::warning("subscriptions:send-sms-reminders failed for tenant {$tenant->id}: {$e->getMessage()}");
            } catch (\Throwable $e) {
                Log::warning("subscriptions:send-sms-reminders failed for tenant {$tenant->id}: {$e->getMessage()}");
            }
        }

        return 0;
    }

    protected function processTenant(): void
    {
        $subscriptions = Subscription::whereIn('next_billing_date', [
            Carbon::today()->addDays(7),
            Carbon::today()->addDays(3),
        ])->where('remaining_cycles', '>', 0)->where('status', 'active')->get();

        foreach ($subscriptions as $subscription) {
            app('App\Http\Controllers\SalesController')->Send_Subscription_Reminder_SMS($subscription->id);
        }
    }
}

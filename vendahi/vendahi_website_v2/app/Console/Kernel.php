<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
        'App\Console\Commands\DatabaseBackUp',
        'App\Console\Commands\WooCommerceSync',
        'App\\Console\\Commands\\WooCommercePushProducts',
        'App\Console\Commands\ShopifySync',
        'App\Console\Commands\SendMeetingReminders',
        'App\Console\Commands\ProcessScheduledCampaigns',
    ];

    /**
     * Define the application's command schedule.
     *
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {

        $schedule->command('database:backup');

        $schedule->command('subscriptions:check-expiry')->daily();

        $schedule->command('tenant:cleanup-inactive')->daily();

        $schedule->command('assets:check-validation-due')->daily();

        $schedule->command('meetings:send-reminders')->everyMinute()->withoutOverlapping();

        $schedule->command('marketing:process-scheduled')->everyMinute()->withoutOverlapping();

        // Shopify: work out which shops are due a scheduled sync and run those,
        // across every active tenant on a plan with the module. Five minutes is
        // the resolution of the per-store plan, not the sync rate — a store set
        // to sync hourly is still touched hourly; this is just how often the
        // question is asked. The command takes its own per-tenant lock, so a
        // catalogue push that runs long is never started twice.
        $schedule->command('shopify:sync')->everyFiveMinutes()->withoutOverlapping();

        // Keep integration OAuth connections alive across ALL tenants: Salla
        // refresh tokens die after 1 month idle, Xero's after 60 days. A daily
        // authenticated ping refreshes them long before either cliff.
        $schedule->command('integrations:keep-alive')->daily()->withoutOverlapping();

        // Nightly Google Sheets snapshot for tenants that opted in (kept off
        // peak hours; full-tab rewrites can take a while on big datasets).
        $schedule->command('google-sheets:auto-export')->dailyAt('02:30')->withoutOverlapping();

        /**
         * Shared hosting friendly queue processing:
         * Each minute, drain pending jobs (including WooCommerce sync batches) until the
         * queues are empty or ~50s elapse, then exit so the next minute can resume.
         *
         * IMPORTANT: You must have a cron that runs `php artisan schedule:run` every minute.
         * The WooCommerce queues are listed explicitly because Woo sync batches re-dispatch
         * onto fixed queues (woocommerce-sync / woocommerce-stock); without them the sync
         * gets stuck at "queued_next_batch" when no Supervisor/systemd worker is running.
         *
         * For higher throughput, run a persistent worker via Supervisor instead (see
         * deploy/supervisor/stocky-queue-worker.conf). Both can coexist safely.
         */
        // 'webhooks' carries outgoing webhooks, Slack/Telegram notifications and
        // the Mailchimp/Xero auto-sync jobs — it must drain here too, or none of
        // them ever run on cron-only (no Supervisor) hosting.
        $schedule->command('queue:work database --stop-when-empty --max-time=50 --queue=woocommerce-sync,woocommerce-stock,webhooks,default --sleep=1 --tries=1 --timeout='.((int) env('QUEUE_WORKER_TIMEOUT', 1200)))
            ->everyMinute()
            ->withoutOverlapping()
            ->evenInMaintenanceMode();

    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}

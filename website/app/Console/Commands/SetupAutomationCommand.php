<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SetupAutomationCommand extends Command
{
    protected $signature = 'mailtrixy:setup-automation
                            {--mode=middleware : Automation mode (middleware, queue, scheduler)}
                            {--status : Show current automation status}';

    protected $description = 'Show automation setup instructions for your hosting environment';

    public function handle(): int
    {
        if ($this->option('status')) {
            return $this->showStatus();
        }

        $mode = $this->option('mode');

        if (! in_array($mode, ['middleware', 'queue', 'scheduler'])) {
            $this->error("Invalid mode: {$mode}. Use 'middleware', 'queue', or 'scheduler'.");
            return self::FAILURE;
        }

        $this->info('');
        $this->info('  ' . config('app.name') . ' Automation Setup');
        $this->info('  ─────────────────────────');
        $this->info('');

        match ($mode) {
            'middleware' => $this->setupMiddleware(),
            'queue' => $this->setupQueue(),
            'scheduler' => $this->setupScheduler(),
        };

        return self::SUCCESS;
    }

    private function showStatus(): int
    {
        $mode = config('automation.mode', 'middleware');
        $enabled = config('automation.middleware.enabled', true);

        $this->info('');
        $this->info('  Current Automation Status');
        $this->info('  ─────────────────────────');
        $this->warn('  These are configuration values, not proof that background tasks are running.');
        $this->warn('  Email discovery while the app is closed requires server cron or schedule:work.');
        $this->table(
            ['Setting', 'Value'],
            [
                ['Mode', $mode],
                ['Middleware Enabled', $enabled ? 'Yes' : 'No'],
                ['Email Sync', config('automation.features.email_sync') ? 'On' : 'Off'],
                ['OAuth Refresh', config('automation.features.oauth_refresh') ? 'On' : 'Off'],
                ['Queue Processing', config('automation.features.queue_processing') ? 'On' : 'Off'],
                ['Drip Processing', config('automation.features.drip_processing') ? 'On' : 'Off'],
                ['Scheduled Emails', config('automation.features.scheduled_emails') ? 'On' : 'Off'],
                ['Throttle (seconds)', config('automation.middleware.throttle_seconds', 60)],
                ['Max Jobs/Request', config('automation.middleware.max_jobs_per_request', 3)],
                ['Max Seconds/Request', config('automation.middleware.max_seconds_per_request', 5)],
            ]
        );

        // Check system health
        $this->info('');
        $this->info('  System Check');
        $this->info('  ────────────');

        // Check database queue table
        try {
            $pendingJobs = \DB::table('jobs')->count();
            $this->line("  Pending queue jobs: {$pendingJobs}");
        } catch (\Exception $e) {
            $this->warn('  Queue table not found — run: php artisan migrate');
        }

        // Check connected email accounts
        $accounts = \App\Models\EmailAccount::where('status', 'connected')->count();
        $this->line("  Connected email accounts: {$accounts}");

        // Check OAuth token status
        $expiring = \App\Models\EmailAccount::where('status', 'connected')
            ->whereIn('provider', ['gmail', 'outlook'])
            ->where('oauth_token_expires_at', '<=', now()->addMinutes(15))
            ->count();
        if ($expiring > 0) {
            $this->warn("  OAuth tokens expiring soon: {$expiring}");
        } else {
            $this->line('  OAuth tokens: All healthy');
        }

        $this->info('');

        return self::SUCCESS;
    }

    private function setupMiddleware(): void
    {
        $this->info('  Mode: MIDDLEWARE (legacy setting)');
        $this->info('');
        $this->warn('  This project does not register an automation middleware.');
        $this->warn('  Web requests cannot keep polling mail while nobody is using the app.');
        $this->info('');
        $this->setupScheduler();
    }

    private function setupQueue(): void
    {
        $this->info('  Mode: QUEUE WORKER');
        $this->info('');
        $this->info('  This mode uses a dedicated queue worker process.');
        $this->info('  Recommended for VPS/dedicated servers with Supervisor.');
        $this->info('');
        $this->line('  1. Add to your .env file:');
        $this->info('');
        $this->line('    AUTOMATION_MODE=queue');
        $this->info('');
        $this->line('  2. Set up Supervisor with this config:');
        $this->info('');
        $this->line('    [program:mailtrixy-worker]');
        $this->line('    process_name=%(program_name)s_%(process_num)02d');
        $this->line('    command=php /path/to/artisan queue:work --sleep=3 --tries=3 --max-time=3600');
        $this->line('    autostart=true');
        $this->line('    autorestart=true');
        $this->line('    numprocs=2');
        $this->info('');
        $this->line('  3. Add this cron entry for the scheduler:');
        $this->info('');
        $this->line('    '.$this->schedulerCronEntry());
        $this->warn('  This command only prints instructions; add the cron entry on your server.');
        $this->info('');
    }

    private function setupScheduler(): void
    {
        $this->info('  Mode: SCHEDULER ONLY');
        $this->info('');
        $this->info('  This mode uses Laravel\'s task scheduler via cron.');
        $this->info('  Good for shared hosting with cron access but no Supervisor.');
        $this->info('');
        $this->line('  Add this entry to the application user\'s crontab on this server:');
        $this->info('');
        $this->line('    '.$this->schedulerCronEntry());
        $this->info('');
        $this->info('  The scheduler will handle email sync, OAuth refresh, drip campaigns,');
        $this->info('  and queue processing automatically.');
        $this->warn('  This command only prints instructions; it does not install cron or change .env.');
        $this->line('  Running schedule:run manually handles one cycle; cron must repeat it every minute.');
        $this->line('  Setting AUTOMATION_MODE alone does not start background processes.');
        $this->line('  Check storage/logs/scheduler.log after the next minute for execution/errors.');
        $this->info('');
    }

    private function schedulerCronEntry(): string
    {
        $command = 'cd '.escapeshellarg(base_path())
            .' && '.escapeshellarg(PHP_BINARY).' artisan schedule:run'
            .' >> '.escapeshellarg(storage_path('logs/scheduler.log')).' 2>&1';

        // Cron treats % as a newline even inside shell quotes.
        return '* * * * * '.str_replace('%', '\\%', $command);
    }
}

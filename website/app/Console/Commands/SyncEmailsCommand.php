<?php

namespace App\Console\Commands;

use App\Jobs\SyncEmailAccountJob;
use App\Models\EmailAccount;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SyncEmailsCommand extends Command
{
    protected $signature = 'mailtrixy:sync-emails
                            {--account= : Sync a specific account by ID}
                            {--workspace= : Sync all accounts in a workspace}
                            {--force : Force sync even if recently synced}
                            {--inline : Import mail and send alerts now, without a queue worker}';

    protected $description = 'Sync emails from all connected email accounts';

    public function handle(): int
    {
        if ($this->option('inline')) {
            // A terminated shared-host process must not block mail until the
            // next day. This lease also covers direct cron invocations.
            $result = Cache::lock('email-sync:inline', 300)->get(fn () => $this->syncInline());
            if ($result === null) $this->line('Email sync is already running; the next tick will retry.');
            return $result ?? self::SUCCESS;
        }

        $query = EmailAccount::whereIn('status', ['connected', 'error']);

        if ($accountId = $this->option('account')) {
            $query->where('id', $accountId);
        }

        if ($workspaceId = $this->option('workspace')) {
            $query->where('workspace_id', $workspaceId);
        }

        if (! $this->option('force')) {
            $query->where(function ($q) {
                $q->whereNull('last_synced_at')
                    ->orWhere('last_synced_at', '<=', now()->subSeconds(20));
            });
        }

        $totalCount = $query->count();

        if ($totalCount === 0) {
            Log::debug('[sync-emails] No accounts to sync');
            $this->info('No email accounts to sync.');
            return self::SUCCESS;
        }

        Log::info("[sync-emails] Dispatching sync for {$totalCount} account(s)");
        $this->info("Dispatching sync jobs for {$totalCount} account(s)...");

        $dispatched = 0;
        $query->chunkById(50, function ($accounts) use (&$dispatched) {
            foreach ($accounts as $account) {
                SyncEmailAccountJob::dispatch($account);
                $dispatched++;
                Log::info("[sync-emails] Dispatched: {$account->email} (ID:{$account->id}, provider:{$account->provider})");
                $this->line("  -> Dispatched sync for: {$account->email} (ID: {$account->id}, Provider: {$account->provider})");
            }
        });

        Log::info("[sync-emails] Done. {$dispatched} job(s) dispatched");
        $this->info("Done. {$dispatched} sync job(s) dispatched to queue.");

        return self::SUCCESS;
    }

    private function syncInline(): int
    {
        $query = EmailAccount::whereIn('status', ['connected', 'error']);
        if ($id = $this->option('account')) $query->where('id', $id);
        if ($workspace = $this->option('workspace')) $query->where('workspace_id', $workspace);
        if (!$this->option('force')) {
            $query->where(fn ($q) => $q->whereNull('last_synced_at')->orWhere('last_synced_at', '<=', now()->subSeconds(20)));
        }

        // Resume with the next account if a host kills the process or one slow
        // mailbox consumes this tick. Do not starve later accounts on each run.
        $rotate = !$this->option('account');
        $cursorKey = 'email-sync:inline:last-id:'.($this->option('workspace') ?? 'all');
        if ($rotate) {
            $query->orderByRaw('CASE WHEN id > ? THEN 0 ELSE 1 END', [(int) Cache::get($cursorKey, 0)]);
        }
        $accounts = $query->orderBy('id')->get();
        if ($accounts->isEmpty()) {
            $this->info('No email accounts to sync.');
            return self::SUCCESS;
        }

        $deadline = now()->addSeconds(40);
        $failed = false;
        foreach ($accounts as $account) {
            if (now()->greaterThanOrEqualTo($deadline)) break;
            if ($rotate) Cache::forever($cursorKey, (int) $account->id);
            try {
                // Use the existing import, OAuth, automation and deduplication
                // logic. Message persistence sends FCM directly, just like chat.
                // A queued/backlogged unique job must not prevent this poll.
                Bus::dispatchNow(new SyncEmailAccountJob($account));
                $this->line("Synced email account {$account->id} inline.");
            } catch (\Throwable $error) {
                $failed = true;
                Log::warning('Inline email sync failed', [
                    'account_id' => $account->id, 'exception' => get_class($error),
                ]);
                $this->warn("Email account {$account->id} failed; check the server log.");
            }
        }

        return $failed ? self::FAILURE : self::SUCCESS;
    }
}

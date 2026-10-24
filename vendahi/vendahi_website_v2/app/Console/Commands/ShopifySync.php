<?php

namespace App\Console\Commands;

use App\Models\ShopifyLog;
use App\Models\ShopifyStore;
use App\Services\Shopify\AutoSync;
use App\Services\Shopify\SyncService;
use App\Services\TenantLimitsService;
use App\Tenant;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

/**
 * The scheduled half of the Shopify module.
 *
 * `auto_sync` on a store has always been a stored preference; this is what
 * makes it mean something. Run it every few minutes from the scheduler and it
 * works out which stores are due, which steps of their plan are due, and runs
 * those — using exactly the same SyncService the Sync Centre button uses, so a
 * scheduled run and a manual one cannot behave differently.
 *
 * On Stocky Cloud every shop belongs to a tenant, and the shops, their plans
 * and their logs all live in that tenant's own database. So the command has
 * two layers: pick the tenants, then, inside each one, do exactly what the
 * single-tenant version does. With no --tenant it sweeps every active tenant
 * whose plan includes the Shopify feature, which is what the cron line wants;
 * run under `tenants:run` (or with --tenant) it stays inside the one tenant.
 *
 * It is also usable by hand, which is how you debug a schedule:
 *
 *   php artisan shopify:sync --list                        what would run, and when, everywhere
 *   php artisan shopify:sync                               everything due right now, every tenant
 *   php artisan shopify:sync --tenant=acme --list          one tenant's schedule
 *   php artisan shopify:sync --tenant=acme --store=3 --force
 *   php artisan shopify:sync --tenant=acme --store=shop.myshopify.com --entity=products --direction=push
 */
class ShopifySync extends Command
{
    protected $signature = 'shopify:sync
        {--tenant= : Tenant id (comma-separated for several). Default: every active tenant on a plan with Shopify}
        {--store= : Store id or shop domain. Default: every connected store with auto sync on}
        {--entity= : One step only — products|inventory|customers|orders|collections|fulfillments}
        {--direction= : push or pull. Defaults to pull for orders, push for everything else}
        {--force : Ignore the interval (and, with --store, the auto-sync switch)}
        {--list : Show what is due and exit without syncing}
        {--since= : For order pulls, only orders created on or after this date (YYYY-MM-DD)}
        {--only-unsynced : For product and customer pushes, skip records already linked}
        {--dry-run : Walk everything and report, but write nothing anywhere}
        {--max-minutes=60 : Stop starting new steps after this long. 0 = no limit}';

    protected $description = 'Run the automatic Shopify syncs that are due (products, inventory, customers, orders, collections, fulfillments)';

    /** Set when any step fails, so cron and CI see a non-zero exit. */
    private bool $failed = false;

    public function handle(): int
    {
        // A catalogue sync is minutes of work, not seconds, and the CLI has no
        // browser waiting on it.
        @ini_set('max_execution_time', '0');
        @ini_set('memory_limit', '512M');

        // Already inside a tenant — `php artisan tenants:run shopify:sync`, or
        // a call made from tenant code. Whoever initialised tenancy has
        // already chosen the tenant; choosing again here would be wrong.
        if (tenancy()->initialized) {
            return $this->runForCurrentTenant();
        }

        return $this->sweepTenants();
    }

    /**
     * Every tenant this invocation covers, one after another.
     *
     * Each tenant is entered, synced and left before the next is entered, so a
     * shop can only ever be reached through its own tenant's database — and a
     * tenant whose sync blows up (revoked token, unreachable shop, a database
     * that never ran the Shopify migrations) costs that tenant its tick and
     * nobody else theirs.
     */
    private function sweepTenants(): int
    {
        $wanted = array_values(array_filter(array_map('trim', explode(',', (string) $this->option('tenant')))));

        $query = Tenant::query();
        $wanted
            ? $query->whereIn('id', $wanted)
            : $query->where('status', Tenant::STATUS_ACTIVE);

        $exit = 0;
        $seen = 0;

        foreach ($query->cursor() as $tenant) {
            try {
                $tenant->run(function () use ($tenant, $wanted, &$exit, &$seen) {
                    // Naming a tenant is taken as knowing what you are doing;
                    // a sweep respects the plan, exactly as the routes do.
                    if (! $wanted && ! $this->tenantHasShopify()) {
                        return;
                    }

                    $seen++;
                    $this->line('');
                    $this->line('<comment>'.$tenant->id.'</comment>');

                    // Per tenant, not per sweep: one tenant's failure must not
                    // be reported against the next one's exit code.
                    $this->failed = false;

                    if ($this->runForCurrentTenant() !== 0) {
                        $exit = 1;
                    }
                });
            } catch (\Throwable $e) {
                // A tenant database that has never run the Shopify migrations
                // is not an error worth a red exit — it is a tenant that does
                // not use the module.
                if (str_contains($e->getMessage(), 'Base table or view not found')) {
                    continue;
                }

                $exit = 1;
                $this->error($tenant->id.': '.$e->getMessage());
                report($e);
            }
        }

        if ($seen === 0) {
            $this->line('No tenant has Shopify available.');
        }

        return $exit;
    }

    /** Whether this tenant's subscription actually includes the module. */
    private function tenantHasShopify(): bool
    {
        try {
            // A fresh instance per tenant: the service memoises the plan it
            // resolved, so a shared one would hand every tenant the first
            // tenant's answer.
            return (new TenantLimitsService)->hasFeature('shopify');
        } catch (\Throwable $e) {
            return false;
        }
    }

    /** One tenant's sweep — the whole of the single-tenant command. */
    private function runForCurrentTenant(): int
    {
        // Unattended sweeps take a lock. Called straight from cron there is no
        // scheduler holding one, and a catalogue push that outlives its five
        // minutes would otherwise meet the next tick head-on and spend the
        // shop's rate limit twice. A run someone started by hand is never
        // blocked by it — that would be a mystery, not a safeguard.
        //
        // The cache store is tenant-prefixed (PrefixCacheTenancyBootstrapper),
        // so this lock is one tenant's, never the whole platform's.
        $lock = $this->unattended() ? Cache::lock('shopify:sync', 7200) : null;

        if ($lock && ! $lock->get()) {
            $this->line('Another shopify:sync is still running — skipping this tick.');

            return 0;
        }

        try {
            return $this->sweep();
        } finally {
            optional($lock)->release();
        }
    }

    /** The cron case: no store named, no single step asked for, not --list. */
    private function unattended(): bool
    {
        return ! $this->option('store') && ! $this->option('entity') && ! $this->option('list');
    }

    private function sweep(): int
    {
        $stores = $this->resolveStores();

        if ($stores->isEmpty()) {
            $this->line($this->option('store')
                ? 'No store matches --store='.$this->option('store').'.'
                : 'No connected store has automatic sync switched on.');

            return 0;
        }

        $startedAt = Carbon::now();
        $budget = max(0, (int) $this->option('max-minutes'));
        $ran = 0;

        foreach ($stores as $store) {
            // --list is a question, not an instruction: work out the steps only
            // when something is actually going to be run with them.
            if ($this->option('list')) {
                $this->report($store);

                continue;
            }

            $steps = $this->stepsFor($store);
            if (! $steps) {
                continue;
            }

            foreach ($steps as $step) {
                if ($budget > 0 && $startedAt->diffInMinutes(Carbon::now()) >= $budget) {
                    $this->warn('Time budget of '.$budget.' min reached — the rest will run on the next tick.');
                    break 2;
                }

                $this->runStep($store, $step);
                $ran++;
            }
        }

        if (! $this->option('list') && $ran === 0) {
            $this->line('Nothing was due.');
        }

        return $this->failed ? 1 : 0;
    }

    // ------------------------------------------------------------ choosing --

    /**
     * Which stores this invocation covers.
     *
     * Without --store that is only stores that asked to be synced
     * automatically; naming a store is taken as asking for it explicitly, so
     * the auto-sync switch is not required — but the connection still is,
     * because there is nothing to talk to without one.
     */
    private function resolveStores()
    {
        $wanted = trim((string) $this->option('store'));

        if ($wanted === '') {
            // --list is an inspection, so it covers every connected shop, not
            // just the ones that asked to be synced automatically: "why is
            // this store not running?" is exactly the question it answers.
            return $this->option('list')
                ? ShopifyStore::whereNull('deleted_at')->where('status', 'connected')->orderBy('id')->get()
                : AutoSync::stores();
        }

        $query = ShopifyStore::whereNull('deleted_at');

        if (ctype_digit($wanted)) {
            $query->where('id', (int) $wanted);
        } else {
            $query->where('shop_domain', ShopifyStore::normaliseDomain($wanted));
        }

        return $query->get()->filter(function (ShopifyStore $store) {
            // Not connected is a reason not to sync, but not a reason to
            // refuse to show what would be synced — the report names it as the
            // blocker, which is the answer the operator came for.
            if ($store->status !== 'connected' && ! $this->option('list')) {
                $this->error($store->shop_domain.' is not connected — test the connection first.');

                return false;
            }

            // Named explicitly with --entity or --force, an off switch is not a
            // reason to refuse; left to the plan, it is. Inspecting with
            // --list is never refused.
            if (! $store->auto_sync && ! $this->option('force') && ! $this->option('entity') && ! $this->option('list')) {
                $this->warn($store->shop_domain.' has automatic sync switched off — pass --force to run its plan anyway.');

                return false;
            }

            return true;
        });
    }

    /**
     * The steps to run for one store: either the single step asked for on the
     * command line, or whatever its plan says is due.
     *
     * @return array<int, array{entity: string, direction: string}>
     */
    private function stepsFor(ShopifyStore $store): array
    {
        $entity = (string) $this->option('entity');

        if ($entity !== '') {
            $direction = (string) ($this->option('direction')
                ?: (in_array($entity, ShopifyStore::PULL_ONLY, true) ? 'pull' : 'push'));

            if (! ShopifyStore::supportsDirection($entity, $direction)) {
                $this->error(ucfirst($entity).' cannot be synced '.$direction.'.');
                $this->failed = true;

                return [];
            }

            $blocked = AutoSync::blockedReason($store, $entity)
                ?: (AutoSync::activeRun($store, $entity) ? 'a sync of '.$entity.' is already running' : null);

            if ($blocked) {
                $this->warn($store->shop_domain.': skipping '.$entity.' — '.$blocked.'.');

                return [];
            }

            return [['entity' => $entity, 'direction' => $direction]];
        }

        // --force means "ignore the clock", not "ignore the rules": a step whose
        // entity is off, or already running, is still skipped.
        if ($this->option('force')) {
            $steps = [];
            foreach ($store->autoSyncPlan() as $step) {
                $blocked = AutoSync::blockedReason($store, $step['entity'])
                    ?: (AutoSync::activeRun($store, $step['entity']) ? 'a sync of '.$step['entity'].' is already running' : null);

                if ($blocked) {
                    $this->warn($store->shop_domain.': skipping '.$step['entity'].' — '.$blocked.'.');

                    continue;
                }
                $steps[] = $step;
            }

            return $steps;
        }

        $due = AutoSync::dueSteps($store);

        // Blocked steps are reported once per tick rather than silently dropped:
        // "nothing happened and nothing said why" is the failure this module is
        // meant not to have.
        foreach (AutoSync::schedule($store) as $row) {
            if ($row['due'] && $row['blocked'] !== null) {
                ShopifyLog::write($store->id, 'auto.skip',
                    'Skipped the scheduled '.$row['entity'].' '.$row['direction'].' — '.$row['blocked'].'.',
                    'warning', [], null, $row['entity']);
                $this->warn($store->shop_domain.': skipping '.$row['entity'].' — '.$row['blocked'].'.');
            }
        }

        return $due;
    }

    // ------------------------------------------------------------- running --

    private function runStep(ShopifyStore $store, array $step): void
    {
        $label = $store->shop_domain.' · '.$step['entity'].' '.$step['direction'];
        $this->line('→ '.$label);

        $options = [
            'dry_run' => (bool) $this->option('dry-run'),
            'only_unsynced' => (bool) $this->option('only-unsynced'),
            'since' => $this->option('since') ?: null,
        ];

        try {
            $run = SyncService::for($store)->run($step['entity'], $step['direction'], $options);
        } catch (\Throwable $e) {
            // SyncService records its own failures; this catches the ones that
            // happen before a run row exists (a store whose token was revoked
            // between the due check and the first call).
            $this->failed = true;
            $this->error('   failed: '.$e->getMessage());
            ShopifyLog::write($store->id, 'auto.error',
                'Scheduled '.$step['entity'].' '.$step['direction'].' could not start: '.$e->getMessage(),
                'error', [], null, $step['entity']);

            return;
        }

        $summary = sprintf(
            '%d processed · %d created · %d updated · %d skipped%s',
            $run->processed_items, $run->created_items, $run->updated_items, $run->skipped_items,
            $run->failed_items ? ' · '.$run->failed_items.' failed' : ''
        );

        if ($run->status === 'completed') {
            $this->info('   done — '.$summary);
        } else {
            $this->failed = true;
            $this->error('   '.$run->status.' — '.($run->last_error ?: $summary));
        }

        ShopifyLog::write($store->id, 'auto.run',
            'Scheduled '.$step['entity'].' '.$step['direction'].' '.$run->status.' — '
                .($run->status === 'completed' ? $summary : ($run->last_error ?: $summary)),
            $run->status === 'completed' ? 'info' : 'error',
            ['every' => $step['every'] ?? null, 'dry_run' => $options['dry_run']],
            $run->id, $step['entity']);
    }

    // ------------------------------------------------------------ --list ----

    private function report(ShopifyStore $store): void
    {
        $this->line('');
        $this->line('<comment>'.$store->name.'</comment> ('.$store->shop_domain.')'
            .($store->auto_sync ? '' : ' <fg=yellow>— automatic sync is off; these steps only run when started by hand</>'));

        $schedule = AutoSync::schedule($store);

        if (! $schedule) {
            $this->line('  Nothing scheduled — every entity in its plan is switched off.');

            return;
        }

        $this->table(
            ['Step', 'Every', 'Last run', 'Next due', 'State'],
            array_map(fn ($row) => [
                $row['entity'].' '.$row['direction'],
                $row['every'].' min',
                $row['last_run_at'] ?: 'never',
                $row['next_due_at'],
                $row['running'] ? 'running'
                    : ($row['blocked'] ? 'blocked: '.$row['blocked']
                        : ($row['due'] ? 'due now' : 'waiting')),
            ], $schedule)
        );
    }
}

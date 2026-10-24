<?php

namespace App\Services\Shopify;

use App\Models\ShopifyLog;
use App\Models\ShopifyStore;
use App\Models\ShopifySyncRun;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Deciding what a scheduled Shopify sync should do right now.
 *
 * The clock is the run history, not a column: a step is due when the last run
 * of that exact (store, entity, direction) started longer ago than its
 * interval. Two things fall out of that for free —
 *
 *  - a manual sync from the UI counts, so starting a products push by hand at
 *    09:00 does not get it repeated by the scheduler at 09:05;
 *  - a failing step is throttled like a succeeding one, so a shop whose token
 *    expired is retried on its interval instead of every single tick.
 *
 * Nothing here talks to Shopify. It answers "what is due" and "what is in the
 * way"; the command does the running, the controller uses the same answers to
 * show the operator what will happen next.
 */
class AutoSync
{
    /**
     * How long a run may go without a heartbeat before it is treated as dead.
     * Matches ShopifySyncRun::isStale()'s default — a stalled run must not
     * block its step for ever.
     */
    public const STALE_AFTER_MINUTES = 10;

    /** Connected stores with automatic sync switched on. */
    public static function stores(): Collection
    {
        return ShopifyStore::query()->autoSyncable()->orderBy('id')->get();
    }

    /**
     * Every step of a store's plan with its clock resolved.
     *
     * @return array<int, array{
     *     entity: string, direction: string, every: int,
     *     last_run_at: ?string, next_due_at: ?string, due: bool,
     *     running: bool, blocked: ?string
     * }>
     */
    public static function schedule(ShopifyStore $store, ?Carbon $now = null): array
    {
        $now = $now ?: Carbon::now();
        $rows = [];

        foreach ($store->autoSyncPlan() as $step) {
            $last = self::lastRunAt($store, $step['entity'], $step['direction']);
            $next = $last ? (clone $last)->addMinutes($step['every']) : null;
            $running = self::activeRun($store, $step['entity']) !== null;

            $rows[] = [
                'entity' => $step['entity'],
                'direction' => $step['direction'],
                'every' => $step['every'],
                'last_run_at' => $last ? $last->toDateTimeString() : null,
                // No run yet means due now: a freshly configured store should
                // not wait out an interval before doing anything at all.
                'next_due_at' => $next ? $next->toDateTimeString() : $now->toDateTimeString(),
                'due' => ! $next || $next->lte($now),
                'running' => $running,
                'blocked' => self::blockedReason($store, $step['entity']),
            ];
        }

        return $rows;
    }

    /** Steps that should run on this tick. */
    public static function dueSteps(ShopifyStore $store, ?Carbon $now = null): array
    {
        return array_values(array_filter(
            self::schedule($store, $now),
            fn ($row) => $row['due'] && ! $row['running'] && $row['blocked'] === null
        ));
    }

    /** When this exact step last started — whoever started it. */
    public static function lastRunAt(ShopifyStore $store, string $entity, string $direction): ?Carbon
    {
        // By start time, not by id. They normally agree, but an imported or
        // seeded history need not have been written in chronological order,
        // and reading the oldest run as "the last one" would make a step look
        // permanently overdue.
        $run = ShopifySyncRun::where('store_id', $store->id)
            ->where('entity', $entity)
            ->where('direction', $direction)
            ->orderByDesc('started_at')
            ->orderByDesc('id')
            ->first();

        if (! $run) {
            return null;
        }

        $at = $run->started_at ?: $run->created_at;

        return $at ? Carbon::parse($at) : null;
    }

    /**
     * A run of this entity that is genuinely still going.
     *
     * A run whose worker died is closed out here rather than reported as busy,
     * because the alternative is a step that never runs again and no message
     * anywhere saying why.
     */
    public static function activeRun(ShopifyStore $store, string $entity): ?ShopifySyncRun
    {
        $run = ShopifySyncRun::where('store_id', $store->id)
            ->where('entity', $entity)
            ->where('status', 'running')
            ->orderByDesc('id')
            ->first();

        if (! $run) {
            return null;
        }

        if (! $run->isStale(self::STALE_AFTER_MINUTES)) {
            return $run;
        }

        $run->update([
            'status' => 'failed',
            'finished_at' => Carbon::now(),
            'last_error' => 'The process handling this run stopped responding.',
        ]);

        ShopifyLog::write($store->id, 'auto.stale',
            'Cleared a stalled '.$entity.' run (#'.$run->id.') so the schedule can continue.',
            'warning', [], $run->id, $entity);

        return null;
    }

    /**
     * Why this step is misconfigured, or null when it is fine. Mirrors the
     * checks the manual endpoint makes, so "start it by hand" and "let it run
     * itself" never disagree about what is allowed.
     *
     * Deliberately says nothing about a run that is currently going: that is
     * reported separately, because "busy" is not "broken" and showing a
     * healthy step as blocked while it works would be a lie.
     */
    public static function blockedReason(ShopifyStore $store, string $entity): ?string
    {
        if ($store->status !== 'connected') {
            return 'the store is not connected';
        }
        if (! $store->syncs($entity)) {
            return ucfirst($entity).' sync is switched off for this store';
        }
        if (in_array($entity, ['inventory', 'orders'], true) && ! $store->warehouse_id) {
            return 'no ERP warehouse is set';
        }
        if ($entity === 'inventory' && ! $store->location_id) {
            return 'no Shopify location is set';
        }

        return null;
    }
}

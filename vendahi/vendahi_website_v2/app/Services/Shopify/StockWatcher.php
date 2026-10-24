<?php

namespace App\Services\Shopify;

use App\Jobs\Shopify\ShopifyStockPushJob;
use App\Models\product_warehouse;
use App\Models\ShopifyStore;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Schema;

/**
 * Noticing that stock moved, and telling the shops that care.
 *
 * A POS sale writes `product_warehouse.qte` and nothing else; until now
 * Shopify only learned about it on the next scheduled inventory push, which
 * for a busy counter is an hour of overselling. This closes that window.
 *
 * Three things it deliberately does NOT do:
 *
 *  - It does not push during the request. One HTTP call to Shopify per line
 *    sold would make the POS wait on someone else's server. Work goes on the
 *    queue; the sale finishes at the speed it always did.
 *  - It does not dispatch per row. A twenty-line sale fires twenty model
 *    events; those are collected and sent as one job per shop at the end of
 *    the request, so a big sale costs one job, not twenty.
 *  - It does not carry quantities. The job re-reads stock when it runs, so a
 *    job delayed behind a slow queue still sends today's truth rather than a
 *    number from ten minutes ago.
 */
class StockWatcher
{
    /** How many stock rows one job handles before another is dispatched. */
    private const CHUNK = 100;

    /** Suppressed while the sync itself is writing local stock. */
    private static bool $muted = false;

    /** warehouse_id => [ 'p:<id>' | 'v:<id>' => true ] */
    private static array $pending = [];

    private static bool $flushRegistered = false;

    /** Stores wanting instant pushes; false until looked up, [] when none. */
    private static $stores = false;

    /**
     * Run something without the watcher reacting to it.
     *
     * An inventory *pull* writes local stock; without this it would bounce
     * straight back out as a push, and an order imported from the shop would
     * push the shop its own number back. Neither is harmful — the push sets an
     * absolute level — but both are pointless API calls against a rate limit
     * that matters.
     */
    public static function muted(callable $callback)
    {
        $was = self::$muted;
        self::$muted = true;

        try {
            return $callback();
        } finally {
            self::$muted = $was;
        }
    }

    public static function isMuted(): bool
    {
        return self::$muted;
    }

    /** Called by the product_warehouse observer when a quantity changes. */
    public static function record(product_warehouse $row): void
    {
        if (self::$muted) {
            return;
        }

        $warehouseId = (int) $row->warehouse_id;
        $productId = (int) $row->product_id;

        if (! $warehouseId || ! $productId) {
            return;
        }

        // Nothing to do unless some shop is both listening and pointed at this
        // warehouse — the overwhelming majority of installs, so this has to be
        // the cheap path.
        if (! self::watchers($warehouseId)) {
            return;
        }

        $variantId = (int) ($row->product_variant_id ?? 0);
        $key = $variantId ? 'v:'.$variantId : 'p:'.$productId;

        self::$pending[$warehouseId][$key] = true;
        self::registerFlush();
    }

    /**
     * Send what has piled up. Called at the end of the request; safe to call
     * by hand (a long-running command can flush between batches).
     */
    public static function flush(): void
    {
        $pending = self::$pending;
        self::$pending = [];

        foreach ($pending as $warehouseId => $keys) {
            $productIds = [];
            $variantIds = [];

            foreach (array_keys($keys) as $key) {
                [$kind, $id] = explode(':', $key, 2);
                if ($kind === 'v') {
                    $variantIds[] = (int) $id;
                } else {
                    $productIds[] = (int) $id;
                }
            }

            foreach (self::watchers((int) $warehouseId) as $store) {
                foreach (array_chunk($productIds, self::CHUNK) as $chunk) {
                    self::send(ShopifyStockPushJob::dispatch($store->id, $chunk, []));
                }
                foreach (array_chunk($variantIds, self::CHUNK) as $chunk) {
                    self::send(ShopifyStockPushJob::dispatch($store->id, [], $chunk));
                }
            }
        }
    }

    /**
     * How the push actually leaves.
     *
     * In a web request — a POS sale, an adjustment saved in the admin — it
     * runs **after the response has been sent**, in the same process. That is
     * what makes this work with nothing else installed: no queue worker, no
     * cron, and the cashier never waits for Shopify, because the sale has
     * already been answered before the call is made.
     *
     * On the CLI, and inside a queued job, there is no response to come after,
     * so it goes on the queue instead. Whatever is draining the queue for the
     * rest of the app picks it up.
     */
    private static function send($pending): void
    {
        app()->runningInConsole()
            ? $pending->onQueue('webhooks')
            : $pending->afterResponse();
    }

    /** Forget everything — for tests, and for a worker between jobs. */
    public static function reset(): void
    {
        self::$pending = [];
        self::$stores = false;
    }

    // ------------------------------------------------------------ internal --

    /** Connected shops with instant push on, for one warehouse. */
    private static function watchers(int $warehouseId): array
    {
        if (self::$stores === false) {
            self::$stores = self::lookupStores();
        }

        return array_values(array_filter(
            self::$stores,
            fn ($store) => (int) $store->warehouse_id === $warehouseId
        ));
    }

    /**
     * Read once per request. Wrapped in a guard because this runs on every
     * stock write in the application, including during an install or an update
     * where the table or the column may not exist yet — a stock movement must
     * never fail because of a Shopify feature that is not there.
     */
    private static function lookupStores(): array
    {
        try {
            if (! Schema::hasTable('shopify_stores') || ! Schema::hasColumn('shopify_stores', 'instant_stock_push')) {
                return [];
            }

            return ShopifyStore::whereNull('deleted_at')
                ->where('instant_stock_push', 1)
                ->where('sync_inventory', 1)
                ->where('status', 'connected')
                ->whereNotNull('warehouse_id')
                ->whereNotNull('location_id')
                ->get(['id', 'warehouse_id'])
                ->all();
        } catch (\Throwable $e) {
            return [];
        }
    }

    private static function registerFlush(): void
    {
        if (self::$flushRegistered) {
            return;
        }

        self::$flushRegistered = true;

        // End of the request (or of the artisan command).
        app()->terminating(function () {
            self::flush();
        });

        // And after each queued job, because a worker process does not
        // terminate between them: another module's job that moves stock — a
        // WooCommerce pull, say — would otherwise sit in the buffer until the
        // worker exited.
        Queue::after(function () {
            self::flush();
        });
    }
}

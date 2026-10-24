<?php

namespace App\Jobs\Shopify;

use App\Models\ShopifyStore;
use App\Services\Shopify\SyncService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Push the current stock of a handful of products to one shop.
 *
 * Carries ids, never quantities: the level is read when the job runs, so a job
 * that waited behind a slow queue still sends what the ERP holds now. That
 * also makes it idempotent — running it twice sets the same absolute number
 * twice, which is a no-op, not a double deduction.
 *
 * Settings are re-read at run time too, so switching instant push off, or
 * disconnecting the shop, cancels work that is already queued.
 */
class ShopifyStockPushJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    /** Up to CHUNK items, one Shopify call each, paced by the rate limiter. */
    public int $timeout = 300;

    /**
     * @param  array<int, int>  $productIds  products without variants
     * @param  array<int, int>  $variantIds  product variants
     */
    public function __construct(
        public int $storeId,
        public array $productIds = [],
        public array $variantIds = [],
    ) {}

    /** Backoff schedule (seconds): 30s, 2m. */
    public function backoff(): array
    {
        return [30, 120];
    }

    public function handle(): void
    {
        if (! $this->productIds && ! $this->variantIds) {
            return;
        }

        $store = ShopifyStore::whereNull('deleted_at')->find($this->storeId);

        if (! $store
            || ! $store->instant_stock_push
            || ! $store->syncs('inventory')
            || $store->status !== 'connected'
            || ! $store->warehouse_id
            || ! $store->location_id) {
            return;
        }

        $result = SyncService::for($store)->pushStockForProducts($this->productIds, $this->variantIds);

        // Per-item failures are logged and left alone — the scheduled push
        // reconciles them, and re-sending ninety-nine good items to retry one
        // bad one wastes the shop's rate limit. But nothing getting through at
        // all is not an item problem: it is a token, a network or a 429, and
        // that is worth the backoff.
        //
        // Only where a retry is actually possible. Run after a web response
        // this executes on the 'sync' connection, with no worker behind it, so
        // throwing would log an exception for a failure already recorded in
        // the Shopify log — and the scheduled push fixes it anyway.
        if ($this->retryable() && $result['failed'] > 0 && $result['pushed'] === 0) {
            throw new \RuntimeException(
                'Instant stock push to '.$store->shop_domain.' failed for all '.$result['failed'].' item(s).'
            );
        }
    }

    /**
     * Whether failing would actually buy a retry: true under a queue worker,
     * false when the job is executed inline (the 'sync' connection, which is
     * how an after-response push runs).
     */
    private function retryable(): bool
    {
        return $this->job !== null && $this->job->getConnectionName() !== 'sync';
    }
}

<?php

namespace App\Listeners;

use App\Services\Shopify\StockWatcher;

/**
 * Keeps the Shopify stock watcher honest across a tenant switch.
 *
 * The watcher keeps two things in static memory for the life of the process:
 * which shops asked for instant stock pushes, and which products have moved
 * since the last flush. In the single-tenant product that process handles one
 * database, so both are safe. Here a queue worker, a scheduled sweep or an
 * impersonation session can visit several tenants in a row — and neither
 * answer survives that trip. Tenant B must not inherit tenant A's shop list,
 * and a product id buffered under A must not be pushed to a shop of B's.
 *
 * So it is wiped on every tenancy boundary. It runs on InitializingTenancy and
 * EndingTenancy rather than their -ed counterparts because both of those fire
 * while the OUTGOING context is still current: the buffer is therefore flushed
 * against the database that filled it, and the incoming tenant starts with the
 * watcher knowing nothing.
 */
class ResetShopifyStockWatcher
{
    public function handle($event = null): void
    {
        try {
            StockWatcher::flush();
        } catch (\Throwable $e) {
            // A flush that cannot reach the queue must never break the
            // tenancy switch it is hanging off; the scheduled inventory push
            // reconciles whatever was lost.
            report($e);
        }

        StockWatcher::reset();
    }
}

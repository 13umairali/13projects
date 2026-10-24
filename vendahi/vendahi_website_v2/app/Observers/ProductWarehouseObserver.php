<?php

namespace App\Observers;

use App\Models\product_warehouse;
use App\Services\Shopify\StockWatcher as ShopifyStockWatcher;

/**
 * Every stock movement in the ERP lands on a product_warehouse row, whoever
 * caused it — a POS sale, a return, an adjustment, a transfer, a received
 * purchase. Watching the row rather than each of those controllers means a
 * flow nobody remembered to hook still reaches the connected shops.
 *
 * The limit of that: bulk `->update()` calls on the query builder fire no
 * model events, so a mass operation (deleting a product, resetting a
 * warehouse) is not seen here. Those are exactly the cases the scheduled
 * inventory push exists to reconcile.
 */
class ProductWarehouseObserver
{
    public function saved(product_warehouse $row): void
    {
        // A row saved for some other reason — manage_stock toggled, a
        // timestamp touched — is not a stock movement and is not worth an API
        // call to Shopify.
        if (! $row->wasRecentlyCreated && ! $row->wasChanged('qte')) {
            return;
        }

        // Shopify decides for itself whether this matters: instant push is off
        // by default and costs one cached lookup to say no.
        ShopifyStockWatcher::record($row);
    }
}

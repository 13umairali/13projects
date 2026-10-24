<?php

namespace App\Listeners\Webhooks;

use App\Events\SaleCreated;
use App\Services\Webhooks\WebhookDispatcher;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Fires a `product.low_stock` webhook event for each product a new sale
 * pushed to (or below) its stock alert threshold, in the sale's warehouse.
 *
 * Runs after the sale's transaction commits (details and stock decrements are
 * written after the sale row itself), so quantities are read post-decrement.
 */
class CheckLowStock implements ShouldQueue
{
    /** Queue with the other webhook work; never blocks the request. */
    public $queue = 'webhooks';

    /** Only run once the originating transaction is committed. */
    public $afterCommit = true;

    public function __construct(protected WebhookDispatcher $dispatcher)
    {
    }

    public function handle(SaleCreated $event): void
    {
        try {
            $sale = $event->sale;

            $rows = DB::table('sale_details')
                ->join('products', 'products.id', '=', 'sale_details.product_id')
                ->join('product_warehouse', function ($join) use ($sale) {
                    $join->on('product_warehouse.product_id', '=', 'products.id')
                        ->where('product_warehouse.warehouse_id', '=', $sale->warehouse_id)
                        ->whereNull('product_warehouse.deleted_at');
                })
                ->where('sale_details.sale_id', $sale->id)
                ->whereNull('products.deleted_at')
                ->where('products.stock_alert', '>', 0)
                ->whereColumn('product_warehouse.qte', '<=', 'products.stock_alert')
                ->groupBy('products.id', 'products.name', 'products.code', 'products.stock_alert', 'product_warehouse.warehouse_id')
                ->selectRaw('products.id, products.name, products.code, products.stock_alert, product_warehouse.warehouse_id, MIN(product_warehouse.qte) as qte')
                ->get();

            foreach ($rows as $row) {
                $this->dispatcher->dispatch('product.low_stock', [
                    'product_id' => (int) $row->id,
                    'name' => $row->name,
                    'code' => $row->code,
                    'warehouse_id' => (int) $row->warehouse_id,
                    'quantity' => (float) $row->qte,
                    'stock_alert' => (float) $row->stock_alert,
                    'sale_id' => (int) $sale->id,
                ]);
            }
        } catch (Throwable $e) {
            // Never break (or retry into) the originating business flow.
            report($e);
        }
    }
}

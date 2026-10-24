<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Product;
use App\Models\product_warehouse;
use App\Models\Sale;
use App\Models\SaleDetail;
use App\Models\Setting;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * Server-side stock guard for completed sales (sale form, POS, POS drafts).
 *
 * The browser already blocks overselling, but the API must not trust it: a
 * stale product grid, an offline-queued POS sale or a hand-crafted request
 * could otherwise drive stock negative while "Allow overselling" is off.
 *
 * Rules:
 *  - Skipped entirely when Settings > Features > Allow overselling is ON.
 *  - Service products (by their stored type), unknown products and
 *    product_warehouse rows without manage_stock are ignored (they never
 *    move stock). Products flagged "Always in stock" are ignored too: the
 *    flag is per-product overselling and wins over the global switch.
 *  - Quantities are converted to base units exactly like the deduction code
 *    (quantity × pack_multiplier, then the sale unit operator) and summed per
 *    product/variant so the same item on two lines is checked as one.
 *  - When editing a completed sale, the units that sale already took from the
 *    same warehouse are added back before comparing, so re-saving an unchanged
 *    sale never fails.
 */
trait ChecksSaleStock
{
    /**
     * @throws ValidationException
     */
    protected function assertSaleStockSufficient(Request $request, ?Sale $currentSale = null): void
    {
        $setting = Setting::whereNull('deleted_at')->first();
        if ((bool) ($setting->allow_overselling ?? false)) {
            return;
        }

        $warehouseId = (int) $request->warehouse_id;
        $details = (array) $request->input('details', []);
        if (! $warehouseId || empty($details)) {
            return;
        }

        // Base units required per product/variant across all lines.
        $required = [];
        $names = [];
        foreach ($details as $value) {
            if (empty($value['product_id']) || ! isset($value['quantity'])) {
                continue;
            }
            // Trust the product's real type, not the payload's product_type.
            $product = Product::find($value['product_id']);
            if (! $product || $product->type === 'is_service' || $product->always_in_stock) {
                continue;
            }

            $variantId = ! empty($value['product_variant_id']) ? (int) $value['product_variant_id'] : null;
            $key = $product->id.':'.($variantId ?? 0);

            $mult = isset($value['pack_multiplier']) && (float) $value['pack_multiplier'] > 0
                ? (float) $value['pack_multiplier'] : 1;
            $unitId = ! empty($value['sale_unit_id']) ? $value['sale_unit_id'] : $product->unit_sale_id;

            $required[$key] = ($required[$key] ?? 0) + $this->toBaseUnits((float) $value['quantity'] * $mult, $unitId);
            $names[$key] = $product->name.(($variantId && ! empty($value['name'])) ? ' ['.$value['name'].']' : '');
        }

        if (empty($required)) {
            return;
        }

        // Units the sale being edited already deducted from this warehouse.
        $alreadyDeducted = [];
        if ($currentSale && $currentSale->statut === 'completed' && (int) $currentSale->warehouse_id === $warehouseId) {
            $oldDetails = SaleDetail::where('sale_id', $currentSale->id)->get();
            foreach ($oldDetails as $old) {
                $key = $old->product_id.':'.($old->product_variant_id ?: 0);
                $mult = (float) ($old->pack_multiplier ?? 1) ?: 1;
                $alreadyDeducted[$key] = ($alreadyDeducted[$key] ?? 0)
                    + $this->toBaseUnits((float) $old->quantity * $mult, $old->sale_unit_id);
            }
        }

        $errors = [];
        $fmt = fn ($n) => rtrim(rtrim(number_format((float) $n, 3, '.', ''), '0'), '.');

        foreach ($required as $key => $need) {
            [$productId, $variantId] = array_map('intval', explode(':', $key));

            $pwQuery = product_warehouse::whereNull('deleted_at')
                ->where('warehouse_id', $warehouseId)
                ->where('product_id', $productId);
            if ($variantId) {
                $pwQuery->where('product_variant_id', $variantId);
            } else {
                $pwQuery->whereNull('product_variant_id');
            }
            $pw = $pwQuery->first();

            // manage_stock lives on the product_warehouse row, not on products.
            if (! $pw || ! $pw->manage_stock) {
                continue;
            }

            $available = (float) $pw->qte + ($alreadyDeducted[$key] ?? 0);
            if ($need > $available + 1e-9) {
                $errors[] = $names[$key].': needs '.$fmt($need).' but only '.$fmt(max($available, 0)).' in stock.';
            }
        }

        if (! empty($errors)) {
            throw ValidationException::withMessages(['stock' => $errors]);
        }
    }

    /**
     * Apply the sale unit operator the same way stock deduction does.
     */
    private function toBaseUnits(float $qty, $unitId): float
    {
        $unit = $unitId ? Unit::find($unitId) : null;
        if (! $unit) {
            return $qty;
        }
        $opv = (float) ($unit->operator_value ?? 1) ?: 1;

        return $unit->operator === '/' ? $qty / $opv : $qty * $opv;
    }
}

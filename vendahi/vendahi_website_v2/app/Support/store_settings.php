<?php

use App\Models\StoreSetting;
use Illuminate\Support\Facades\Cache;

if (! function_exists('store_settings')) {
    function store_settings(): StoreSetting
    {
        return Cache::remember('store_settings', 600, function () {
            return StoreSetting::query()->first() ?? new StoreSetting;
        });
    }
}

if (! function_exists('store_currency')) {
    /**
     * Multi-Currency: the currency the storefront displays right now
     * (['id','symbol','code','rate','is_base']); base currency, rate 1 when
     * the module is off or nothing is selected.
     */
    function store_currency(): array
    {
        return \App\Services\StoreCurrencyService::active();
    }
}

if (! function_exists('store_money')) {
    /**
     * Format a BASE-currency amount in the active store currency, matching
     * the storefront's historical "{symbol}{amount}" rendering. Pass an
     * explicit currency array (e.g. StoreCurrencyService::forDocument($order))
     * for order pages that must use the order's snapshotted rate.
     */
    function store_money($baseAmount, ?array $currency = null): string
    {
        $c = $currency ?? store_currency();
        $dec = \App\utils\helpers::price_decimals();

        return $c['symbol'].number_format(((float) $baseAmount) * (($c['rate'] ?? 1) ?: 1), $dec, '.', ',');
    }
}

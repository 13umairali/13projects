<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Shipping zones are gone: the flat Shipping Methods list (a price plus an
     * optional country list) is the only way to configure shipping now.
     *
     * Rates that lived inside a zone are kept as ordinary methods so nothing an
     * admin configured disappears: the zone's countries become the method's
     * region rows, and a catch-all zone becomes a method offered everywhere.
     */
    public function up(): void
    {
        if (Schema::hasColumn('shipping_methods', 'shipping_zone_id')) {
            $this->convertZoneRatesToMethods();

            Schema::table('shipping_methods', function (Blueprint $table) {
                foreach (['shipping_zone_id', 'rate_type', 'min_value', 'max_value'] as $col) {
                    if (Schema::hasColumn('shipping_methods', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }

        Schema::dropIfExists('shipping_zone_locations');
        Schema::dropIfExists('shipping_zones');
    }

    public function down(): void
    {
        // Zones are not coming back; the converted methods stay as they are.
    }

    private function convertZoneRatesToMethods(): void
    {
        if (! Schema::hasTable('shipping_zone_locations') || ! Schema::hasTable('shipping_method_regions')) {
            return;
        }

        $rates = DB::table('shipping_methods')
            ->whereNotNull('shipping_zone_id')
            ->whereNull('deleted_at')
            ->get(['id', 'shipping_zone_id']);

        $locationsByZone = DB::table('shipping_zone_locations')
            ->get(['shipping_zone_id', 'country'])
            ->groupBy('shipping_zone_id');

        $now = now();

        foreach ($rates as $rate) {
            $countries = ($locationsByZone[$rate->shipping_zone_id] ?? collect())
                ->pluck('country')
                ->map(fn ($c) => trim((string) $c))
                ->filter()
                ->unique()
                ->values();

            // A catch-all zone (NULL country) means "everywhere": no region rows.
            $isCatchAll = ($locationsByZone[$rate->shipping_zone_id] ?? collect())
                ->contains(fn ($l) => trim((string) $l->country) === '');

            if (! $isCatchAll && $countries->isNotEmpty()) {
                DB::table('shipping_method_regions')->insert(
                    $countries->map(fn ($country) => [
                        'shipping_method_id' => $rate->id,
                        'country' => $country,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ])->all()
                );
            }
        }

        DB::table('shipping_methods')->whereNotNull('shipping_zone_id')->update(['shipping_zone_id' => null]);
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Manual exchange rate per currency for the (tenant) Multi-Currency module.
 * Convention: rate = units of THIS currency per 1 unit of the base currency
 * (settings.currency_id). The base currency always keeps rate 1.
 *
 * Unrelated to the central billing FX columns added by
 * 2026_03_19_200000_add_multi_currency_support.php — this is tenant business data.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('currencies', function (Blueprint $table) {
            if (! Schema::hasColumn('currencies', 'exchange_rate')) {
                $table->decimal('exchange_rate', 18, 6)->default(1);
            }
        });
    }

    public function down(): void
    {
        Schema::table('currencies', function (Blueprint $table) {
            if (Schema::hasColumn('currencies', 'exchange_rate')) {
                $table->dropColumn('exchange_rate');
            }
        });
    }
};

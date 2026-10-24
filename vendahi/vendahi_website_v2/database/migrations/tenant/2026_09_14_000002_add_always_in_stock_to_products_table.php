<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * "Always in stock": product-level overselling. A flagged product can be
     * sold (POS, sales, quotations, storefront) when its stock is zero even
     * while Settings > Features > Allow overselling is OFF.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (! Schema::hasColumn('products', 'always_in_stock')) {
                $table->boolean('always_in_stock')->default(false)->after('hide_from_online_store');
            }
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'always_in_stock')) {
                $table->dropColumn('always_in_stock');
            }
        });
    }
};

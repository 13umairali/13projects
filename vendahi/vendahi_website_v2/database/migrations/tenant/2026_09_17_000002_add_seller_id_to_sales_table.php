<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The salesperson credited for a sale, as chosen by POS "Change
     * Salesperson". Distinct from user_id, which stays the cashier who rang
     * the sale up; NULL means "nobody was switched in", and every report
     * reads it as COALESCE(seller_id, user_id).
     *
     * The POS has been sending seller_id and the sales reports have been
     * reading it, but no migration ever created the column — so the
     * attribution was dropped on write and Report_Sales failed on read.
     */
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            if (! Schema::hasColumn('sales', 'seller_id')) {
                $table->integer('seller_id')->nullable()->after('user_id')->index('seller_id_sales');
            }
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            if (Schema::hasColumn('sales', 'seller_id')) {
                $table->dropIndex('seller_id_sales');
                $table->dropColumn('seller_id');
            }
        });
    }
};

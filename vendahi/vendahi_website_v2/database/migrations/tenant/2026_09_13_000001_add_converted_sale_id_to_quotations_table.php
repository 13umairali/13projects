<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Links a quotation to the sale it was converted into, so the quotations list
 * can show the sale and stop offering "convert" a second time.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quotations', function (Blueprint $table) {
            if (! Schema::hasColumn('quotations', 'converted_sale_id')) {
                $table->unsignedBigInteger('converted_sale_id')->nullable()->after('statut');
                $table->index('converted_sale_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('quotations', function (Blueprint $table) {
            if (Schema::hasColumn('quotations', 'converted_sale_id')) {
                $table->dropIndex(['converted_sale_id']);
                $table->dropColumn('converted_sale_id');
            }
        });
    }
};

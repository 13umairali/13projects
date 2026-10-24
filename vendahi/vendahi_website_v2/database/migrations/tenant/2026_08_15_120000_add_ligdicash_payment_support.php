<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLigdicashPaymentSupport extends Migration
{
    public function up()
    {
        // Tenant's own LigdiCash credentials (used by the storefront checkout).
        Schema::table('payment_settings', function (Blueprint $table) {
            if (! Schema::hasColumn('payment_settings', 'ligdicash_api_key')) {
                $table->string('ligdicash_api_key', 128)->nullable();
            }
            if (! Schema::hasColumn('payment_settings', 'ligdicash_auth_token')) {
                $table->text('ligdicash_auth_token')->nullable();
            }
        });

        // LigdiCash invoice token per storefront order. One invoice is created
        // per order, and the callback handler re-verifies with this stored
        // token (LigdiCash callbacks are unsigned and carry an empty token).
        Schema::table('online_orders', function (Blueprint $table) {
            if (! Schema::hasColumn('online_orders', 'ligdicash_token')) {
                $table->string('ligdicash_token', 512)->nullable();
            }
        });
    }

    public function down()
    {
        Schema::table('payment_settings', function (Blueprint $table) {
            foreach (['ligdicash_api_key', 'ligdicash_auth_token'] as $column) {
                if (Schema::hasColumn('payment_settings', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('online_orders', function (Blueprint $table) {
            if (Schema::hasColumn('online_orders', 'ligdicash_token')) {
                $table->dropColumn('ligdicash_token');
            }
        });
    }
}

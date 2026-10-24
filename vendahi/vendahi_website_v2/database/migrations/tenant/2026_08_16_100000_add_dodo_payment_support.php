<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDodoPaymentSupport extends Migration
{
    public function up()
    {
        // Tenant's own Dodo Payments credentials (used by the storefront checkout).
        Schema::table('payment_settings', function (Blueprint $table) {
            if (! Schema::hasColumn('payment_settings', 'dodo_api_key')) {
                $table->text('dodo_api_key')->nullable();
            }
            if (! Schema::hasColumn('payment_settings', 'dodo_product_id')) {
                $table->string('dodo_product_id', 64)->nullable();
            }
            if (! Schema::hasColumn('payment_settings', 'dodo_webhook_secret')) {
                $table->text('dodo_webhook_secret')->nullable();
            }
            if (! Schema::hasColumn('payment_settings', 'dodo_test_mode')) {
                $table->boolean('dodo_test_mode')->default(false);
            }
        });

        // Dodo references on storefront orders. The unique index on
        // dodo_payment_id is the race backstop against one payment settling
        // two orders.
        Schema::table('online_orders', function (Blueprint $table) {
            if (! Schema::hasColumn('online_orders', 'dodo_session_id')) {
                $table->string('dodo_session_id', 64)->nullable()->index();
            }
            if (! Schema::hasColumn('online_orders', 'dodo_payment_id')) {
                $table->string('dodo_payment_id', 64)->nullable()->unique();
            }
        });
    }

    public function down()
    {
        Schema::table('payment_settings', function (Blueprint $table) {
            foreach (['dodo_api_key', 'dodo_product_id', 'dodo_webhook_secret', 'dodo_test_mode'] as $column) {
                if (Schema::hasColumn('payment_settings', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('online_orders', function (Blueprint $table) {
            foreach (['dodo_session_id', 'dodo_payment_id'] as $column) {
                if (Schema::hasColumn('online_orders', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
}

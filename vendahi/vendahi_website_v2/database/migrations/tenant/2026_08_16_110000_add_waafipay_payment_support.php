<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddWaafipayPaymentSupport extends Migration
{
    public function up()
    {
        // Tenant's own WaafiPay credentials (used by the storefront checkout).
        Schema::table('payment_settings', function (Blueprint $table) {
            if (! Schema::hasColumn('payment_settings', 'waafipay_merchant_uid')) {
                $table->string('waafipay_merchant_uid', 64)->nullable();
            }
            if (! Schema::hasColumn('payment_settings', 'waafipay_store_id')) {
                $table->string('waafipay_store_id', 32)->nullable();
            }
            if (! Schema::hasColumn('payment_settings', 'waafipay_hpp_key')) {
                $table->text('waafipay_hpp_key')->nullable();
            }
            if (! Schema::hasColumn('payment_settings', 'waafipay_webhook_secret')) {
                $table->text('waafipay_webhook_secret')->nullable();
            }
            if (! Schema::hasColumn('payment_settings', 'waafipay_test_mode')) {
                $table->boolean('waafipay_test_mode')->default(false);
            }
        });

        // WaafiPay transaction id on storefront orders (set by the webhook).
        // Unique so one WaafiPay payment can never settle two orders.
        Schema::table('online_orders', function (Blueprint $table) {
            if (! Schema::hasColumn('online_orders', 'waafipay_transaction_id')) {
                $table->string('waafipay_transaction_id', 64)->nullable()->unique();
            }
        });
    }

    public function down()
    {
        Schema::table('payment_settings', function (Blueprint $table) {
            foreach (['waafipay_merchant_uid', 'waafipay_store_id', 'waafipay_hpp_key', 'waafipay_webhook_secret', 'waafipay_test_mode'] as $column) {
                if (Schema::hasColumn('payment_settings', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('online_orders', function (Blueprint $table) {
            if (Schema::hasColumn('online_orders', 'waafipay_transaction_id')) {
                $table->dropColumn('waafipay_transaction_id');
            }
        });
    }
}

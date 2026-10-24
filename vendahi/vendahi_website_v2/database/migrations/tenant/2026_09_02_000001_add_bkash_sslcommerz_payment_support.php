<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBkashSslcommerzPaymentSupport extends Migration
{
    public function up()
    {
        // Tenant's own bKash (Tokenized Checkout) and SSLCommerz credentials
        // (used by the storefront checkout). Both gateways charge in BDT.
        Schema::table('payment_settings', function (Blueprint $table) {
            if (! Schema::hasColumn('payment_settings', 'bkash_app_key')) {
                $table->string('bkash_app_key', 191)->nullable();
            }
            if (! Schema::hasColumn('payment_settings', 'bkash_app_secret')) {
                $table->text('bkash_app_secret')->nullable();
            }
            if (! Schema::hasColumn('payment_settings', 'bkash_username')) {
                $table->string('bkash_username', 191)->nullable();
            }
            if (! Schema::hasColumn('payment_settings', 'bkash_password')) {
                $table->text('bkash_password')->nullable();
            }
            if (! Schema::hasColumn('payment_settings', 'bkash_test_mode')) {
                $table->boolean('bkash_test_mode')->default(true);
            }

            if (! Schema::hasColumn('payment_settings', 'sslcommerz_store_id')) {
                $table->string('sslcommerz_store_id', 191)->nullable();
            }
            if (! Schema::hasColumn('payment_settings', 'sslcommerz_store_password')) {
                $table->text('sslcommerz_store_password')->nullable();
            }
            if (! Schema::hasColumn('payment_settings', 'sslcommerz_test_mode')) {
                $table->boolean('sslcommerz_test_mode')->default(true);
            }
        });

        // Gateway ids on storefront orders. The payment id / tran_id resolve
        // the pending order on the callback; the trx id / val_id record the
        // settled transaction. Unique so one payment can never settle two
        // orders (same rule as waafipay_transaction_id).
        Schema::table('online_orders', function (Blueprint $table) {
            if (! Schema::hasColumn('online_orders', 'bkash_payment_id')) {
                $table->string('bkash_payment_id', 64)->nullable()->unique();
            }
            if (! Schema::hasColumn('online_orders', 'bkash_trx_id')) {
                $table->string('bkash_trx_id', 64)->nullable()->unique();
            }
            if (! Schema::hasColumn('online_orders', 'sslcommerz_tran_id')) {
                $table->string('sslcommerz_tran_id', 64)->nullable()->unique();
            }
            if (! Schema::hasColumn('online_orders', 'sslcommerz_val_id')) {
                $table->string('sslcommerz_val_id', 64)->nullable();
            }
        });
    }

    public function down()
    {
        Schema::table('payment_settings', function (Blueprint $table) {
            foreach ([
                'sslcommerz_test_mode', 'sslcommerz_store_password', 'sslcommerz_store_id',
                'bkash_test_mode', 'bkash_password', 'bkash_username', 'bkash_app_secret', 'bkash_app_key',
            ] as $column) {
                if (Schema::hasColumn('payment_settings', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('online_orders', function (Blueprint $table) {
            foreach (['sslcommerz_val_id', 'sslcommerz_tran_id', 'bkash_trx_id', 'bkash_payment_id'] as $column) {
                if (Schema::hasColumn('online_orders', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
}

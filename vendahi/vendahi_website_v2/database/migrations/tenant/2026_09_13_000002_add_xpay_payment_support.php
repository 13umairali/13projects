<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddXpayPaymentSupport extends Migration
{
    public function up()
    {
        // Tenant's own XPay (PostEx, Pakistan) credentials used by the
        // storefront checkout. XPay charges in PKR; the store's base currency
        // must match xpay_currency. Test and live use different hosts/keys —
        // xpay_test_mode picks the host for whichever key set is stored.
        Schema::table('payment_settings', function (Blueprint $table) {
            if (! Schema::hasColumn('payment_settings', 'xpay_account_id')) {
                $table->string('xpay_account_id', 191)->nullable();
            }
            if (! Schema::hasColumn('payment_settings', 'xpay_publishable_key')) {
                $table->string('xpay_publishable_key', 191)->nullable();
            }
            if (! Schema::hasColumn('payment_settings', 'xpay_secret_key')) {
                $table->text('xpay_secret_key')->nullable();
            }
            if (! Schema::hasColumn('payment_settings', 'xpay_hmac_secret')) {
                $table->text('xpay_hmac_secret')->nullable();
            }
            if (! Schema::hasColumn('payment_settings', 'xpay_webhook_hmac_secret')) {
                $table->text('xpay_webhook_hmac_secret')->nullable();
            }
            if (! Schema::hasColumn('payment_settings', 'xpay_currency')) {
                $table->string('xpay_currency', 3)->default('PKR');
            }
            if (! Schema::hasColumn('payment_settings', 'xpay_test_mode')) {
                $table->boolean('xpay_test_mode')->default(true);
            }
        });

        // Payment Intent id resolves the order for the webhook backstop; the
        // bank transaction id records the settled charge. Both unique so one
        // payment can never settle two orders.
        Schema::table('online_orders', function (Blueprint $table) {
            if (! Schema::hasColumn('online_orders', 'xpay_intent_id')) {
                $table->string('xpay_intent_id', 64)->nullable()->unique();
            }
            if (! Schema::hasColumn('online_orders', 'xpay_transaction_id')) {
                $table->string('xpay_transaction_id', 64)->nullable()->unique();
            }
        });
    }

    public function down()
    {
        Schema::table('payment_settings', function (Blueprint $table) {
            foreach ([
                'xpay_test_mode', 'xpay_currency', 'xpay_webhook_hmac_secret',
                'xpay_hmac_secret', 'xpay_secret_key', 'xpay_publishable_key', 'xpay_account_id',
            ] as $column) {
                if (Schema::hasColumn('payment_settings', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('online_orders', function (Blueprint $table) {
            foreach (['xpay_transaction_id', 'xpay_intent_id'] as $column) {
                if (Schema::hasColumn('online_orders', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
}

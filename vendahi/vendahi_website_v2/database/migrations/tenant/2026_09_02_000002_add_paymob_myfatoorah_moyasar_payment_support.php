<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPaymobMyfatoorahMoyasarPaymentSupport extends Migration
{
    public function up()
    {
        // Tenant's own Paymob / MyFatoorah / Moyasar credentials (used by the
        // storefront checkout). Each gateway charges in its configured
        // currency — the store's base currency must match it.
        Schema::table('payment_settings', function (Blueprint $table) {
            if (! Schema::hasColumn('payment_settings', 'paymob_region')) {
                $table->string('paymob_region', 20)->nullable();
            }
            if (! Schema::hasColumn('payment_settings', 'paymob_public_key')) {
                $table->string('paymob_public_key', 191)->nullable();
            }
            if (! Schema::hasColumn('payment_settings', 'paymob_secret_key')) {
                $table->text('paymob_secret_key')->nullable();
            }
            if (! Schema::hasColumn('payment_settings', 'paymob_api_key')) {
                $table->text('paymob_api_key')->nullable();
            }
            if (! Schema::hasColumn('payment_settings', 'paymob_integration_id')) {
                $table->string('paymob_integration_id', 191)->nullable();
            }
            if (! Schema::hasColumn('payment_settings', 'paymob_hmac_secret')) {
                $table->text('paymob_hmac_secret')->nullable();
            }
            if (! Schema::hasColumn('payment_settings', 'paymob_currency')) {
                $table->string('paymob_currency', 3)->default('EGP');
            }

            if (! Schema::hasColumn('payment_settings', 'myfatoorah_api_token')) {
                $table->text('myfatoorah_api_token')->nullable();
            }
            if (! Schema::hasColumn('payment_settings', 'myfatoorah_country')) {
                $table->string('myfatoorah_country', 3)->nullable();
            }
            if (! Schema::hasColumn('payment_settings', 'myfatoorah_currency')) {
                $table->string('myfatoorah_currency', 3)->default('KWD');
            }
            if (! Schema::hasColumn('payment_settings', 'myfatoorah_test_mode')) {
                $table->boolean('myfatoorah_test_mode')->default(true);
            }

            if (! Schema::hasColumn('payment_settings', 'moyasar_secret_key')) {
                $table->text('moyasar_secret_key')->nullable();
            }
            if (! Schema::hasColumn('payment_settings', 'moyasar_currency')) {
                $table->string('moyasar_currency', 3)->default('SAR');
            }
        });

        // Gateway ids on storefront orders. The reference/invoice id resolves
        // the pending order on the callback; the transaction/payment id
        // records the settled charge. Unique so one payment can never settle
        // two orders (same rule as waafipay_transaction_id).
        Schema::table('online_orders', function (Blueprint $table) {
            if (! Schema::hasColumn('online_orders', 'paymob_reference')) {
                $table->string('paymob_reference', 64)->nullable()->unique();
            }
            if (! Schema::hasColumn('online_orders', 'paymob_transaction_id')) {
                $table->string('paymob_transaction_id', 64)->nullable()->unique();
            }
            if (! Schema::hasColumn('online_orders', 'myfatoorah_invoice_id')) {
                $table->string('myfatoorah_invoice_id', 64)->nullable()->unique();
            }
            if (! Schema::hasColumn('online_orders', 'myfatoorah_transaction_id')) {
                $table->string('myfatoorah_transaction_id', 64)->nullable();
            }
            if (! Schema::hasColumn('online_orders', 'moyasar_invoice_id')) {
                $table->string('moyasar_invoice_id', 64)->nullable()->unique();
            }
            if (! Schema::hasColumn('online_orders', 'moyasar_payment_id')) {
                $table->string('moyasar_payment_id', 64)->nullable()->unique();
            }
        });
    }

    public function down()
    {
        Schema::table('payment_settings', function (Blueprint $table) {
            foreach ([
                'moyasar_currency', 'moyasar_secret_key',
                'myfatoorah_test_mode', 'myfatoorah_currency', 'myfatoorah_country', 'myfatoorah_api_token',
                'paymob_currency', 'paymob_hmac_secret', 'paymob_integration_id', 'paymob_api_key',
                'paymob_secret_key', 'paymob_public_key', 'paymob_region',
            ] as $column) {
                if (Schema::hasColumn('payment_settings', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('online_orders', function (Blueprint $table) {
            foreach ([
                'moyasar_payment_id', 'moyasar_invoice_id',
                'myfatoorah_transaction_id', 'myfatoorah_invoice_id',
                'paymob_transaction_id', 'paymob_reference',
            ] as $column) {
                if (Schema::hasColumn('online_orders', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
}

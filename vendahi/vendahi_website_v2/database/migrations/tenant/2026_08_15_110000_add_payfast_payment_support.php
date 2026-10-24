<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPayfastPaymentSupport extends Migration
{
    public function up()
    {
        // Tenant's own PayFast credentials (used by the storefront checkout).
        Schema::table('payment_settings', function (Blueprint $table) {
            if (! Schema::hasColumn('payment_settings', 'payfast_merchant_id')) {
                $table->string('payfast_merchant_id', 32)->nullable();
            }
            if (! Schema::hasColumn('payment_settings', 'payfast_merchant_key')) {
                $table->text('payfast_merchant_key')->nullable();
            }
            if (! Schema::hasColumn('payment_settings', 'payfast_passphrase')) {
                $table->text('payfast_passphrase')->nullable();
            }
            if (! Schema::hasColumn('payment_settings', 'payfast_sandbox')) {
                $table->boolean('payfast_sandbox')->default(false);
            }
        });

        // PayFast transaction reference on storefront orders (set by the ITN).
        // Unique so one PayFast payment can never settle two orders.
        Schema::table('online_orders', function (Blueprint $table) {
            if (! Schema::hasColumn('online_orders', 'payfast_payment_id')) {
                $table->string('payfast_payment_id', 64)->nullable()->unique();
            }
        });
    }

    public function down()
    {
        Schema::table('payment_settings', function (Blueprint $table) {
            foreach (['payfast_merchant_id', 'payfast_merchant_key', 'payfast_passphrase', 'payfast_sandbox'] as $column) {
                if (Schema::hasColumn('payment_settings', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('online_orders', function (Blueprint $table) {
            if (Schema::hasColumn('online_orders', 'payfast_payment_id')) {
                $table->dropColumn('payfast_payment_id');
            }
        });
    }
}

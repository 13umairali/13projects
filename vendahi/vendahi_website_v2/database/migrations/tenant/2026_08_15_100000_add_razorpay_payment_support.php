<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRazorpayPaymentSupport extends Migration
{
    public function up()
    {
        // Tenant's own Razorpay credentials (used by the storefront checkout).
        Schema::table('payment_settings', function (Blueprint $table) {
            if (! Schema::hasColumn('payment_settings', 'razorpay_key_id')) {
                $table->string('razorpay_key_id', 64)->nullable();
            }
            if (! Schema::hasColumn('payment_settings', 'razorpay_key_secret')) {
                $table->text('razorpay_key_secret')->nullable();
            }
        });

        // Razorpay references on storefront orders. The unique index on
        // razorpay_payment_id is the race backstop against one captured
        // payment being used for two orders.
        Schema::table('online_orders', function (Blueprint $table) {
            if (! Schema::hasColumn('online_orders', 'razorpay_order_id')) {
                $table->string('razorpay_order_id', 64)->nullable()->index();
            }
            if (! Schema::hasColumn('online_orders', 'razorpay_payment_id')) {
                $table->string('razorpay_payment_id', 64)->nullable()->unique();
            }
        });
    }

    public function down()
    {
        Schema::table('payment_settings', function (Blueprint $table) {
            if (Schema::hasColumn('payment_settings', 'razorpay_key_id')) {
                $table->dropColumn('razorpay_key_id');
            }
            if (Schema::hasColumn('payment_settings', 'razorpay_key_secret')) {
                $table->dropColumn('razorpay_key_secret');
            }
        });

        Schema::table('online_orders', function (Blueprint $table) {
            if (Schema::hasColumn('online_orders', 'razorpay_order_id')) {
                $table->dropColumn('razorpay_order_id');
            }
            if (Schema::hasColumn('online_orders', 'razorpay_payment_id')) {
                $table->dropColumn('razorpay_payment_id');
            }
        });
    }
}

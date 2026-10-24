<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Columns the online-store modules (returns, reviews, coupons, shipping,
 * menus) expect on tables that already exist. Every add is guarded so the
 * migration is safe on tenants created from any earlier schema.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('products') && ! Schema::hasColumn('products', 'is_returnable')) {
            Schema::table('products', function (Blueprint $table) {
                // Digital / non-returnable products set this to false.
                $table->boolean('is_returnable')->default(true);
            });
        }

        if (Schema::hasTable('store_settings')) {
            Schema::table('store_settings', function (Blueprint $table) {
                if (! Schema::hasColumn('store_settings', 'allow_cancellations')) {
                    $table->boolean('allow_cancellations')->default(true);
                }
                if (! Schema::hasColumn('store_settings', 'return_window_days')) {
                    // Days after delivery a customer may request a return. 0 disables returns.
                    $table->unsignedInteger('return_window_days')->default(14);
                }
                if (! Schema::hasColumn('store_settings', 'auto_approve_reviews')) {
                    $table->boolean('auto_approve_reviews')->default(false);
                }
            });
        }

        if (Schema::hasTable('online_orders')) {
            Schema::table('online_orders', function (Blueprint $table) {
                // Money breakdown (total already exists = grand total)
                if (! Schema::hasColumn('online_orders', 'subtotal')) {
                    $table->decimal('subtotal', 15, 2)->default(0);
                }
                if (! Schema::hasColumn('online_orders', 'discount')) {
                    $table->decimal('discount', 15, 2)->default(0);
                }
                if (! Schema::hasColumn('online_orders', 'coupon_code')) {
                    $table->string('coupon_code', 60)->nullable();
                }
                if (! Schema::hasColumn('online_orders', 'tax')) {
                    $table->decimal('tax', 15, 2)->default(0);
                }
                if (! Schema::hasColumn('online_orders', 'tax_rate')) {
                    $table->decimal('tax_rate', 8, 3)->default(0);
                }
                if (! Schema::hasColumn('online_orders', 'shipping_cost')) {
                    $table->decimal('shipping_cost', 15, 2)->default(0);
                }
                // Shipping method used (no FK: methods may be deleted freely)
                if (! Schema::hasColumn('online_orders', 'shipping_method_id')) {
                    $table->unsignedBigInteger('shipping_method_id')->nullable()->index();
                }
                if (! Schema::hasColumn('online_orders', 'shipping_method_name')) {
                    $table->string('shipping_method_name', 120)->nullable();
                }
                // Set when the order reaches "delivered"; starts the return window.
                if (! Schema::hasColumn('online_orders', 'delivered_at')) {
                    $table->timestamp('delivered_at')->nullable();
                }
                // Customer / shipping address snapshot (frozen at order time) —
                // shipping zones and tax rates are resolved from it.
                foreach ([
                    'customer_name' => 150, 'customer_email' => 190, 'customer_phone' => 40,
                    'shipping_address' => 250, 'shipping_city' => 100, 'shipping_state' => 100,
                    'shipping_zip' => 30, 'shipping_country' => 100,
                ] as $col => $len) {
                    if (! Schema::hasColumn('online_orders', $col)) {
                        $table->string($col, $len)->nullable();
                    }
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('online_orders')) {
            Schema::table('online_orders', function (Blueprint $table) {
                foreach (['subtotal', 'discount', 'coupon_code', 'tax', 'tax_rate', 'shipping_cost', 'shipping_method_id', 'shipping_method_name', 'delivered_at',
                    'customer_name', 'customer_email', 'customer_phone', 'shipping_address', 'shipping_city', 'shipping_state', 'shipping_zip', 'shipping_country'] as $col) {
                    if (Schema::hasColumn('online_orders', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }
        if (Schema::hasTable('store_settings')) {
            Schema::table('store_settings', function (Blueprint $table) {
                foreach (['allow_cancellations', 'return_window_days', 'auto_approve_reviews'] as $col) {
                    if (Schema::hasColumn('store_settings', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }
        if (Schema::hasTable('products') && Schema::hasColumn('products', 'is_returnable')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropColumn('is_returnable');
            });
        }
    }
};

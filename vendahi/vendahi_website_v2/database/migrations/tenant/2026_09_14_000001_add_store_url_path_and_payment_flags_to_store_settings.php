<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Online-store settings the admin page already edited but the backend
     * never persisted:
     *  - store_url_path: custom storefront base path (NULL = default "online_store")
     *  - payment_stripe_enabled / payment_cod_enabled / payment_mobile_money_enabled:
     *    per-method on/off switches for the storefront checkout (default ON so
     *    existing tenants keep offering exactly what they offered before).
     */
    public function up(): void
    {
        Schema::table('store_settings', function (Blueprint $table) {
            if (! Schema::hasColumn('store_settings', 'store_url_path')) {
                $table->string('store_url_path', 100)->nullable()->after('language');
            }
            if (! Schema::hasColumn('store_settings', 'payment_stripe_enabled')) {
                $table->boolean('payment_stripe_enabled')->default(true)->after('store_url_path');
            }
            if (! Schema::hasColumn('store_settings', 'payment_cod_enabled')) {
                $table->boolean('payment_cod_enabled')->default(true)->after('payment_stripe_enabled');
            }
            if (! Schema::hasColumn('store_settings', 'payment_mobile_money_enabled')) {
                $table->boolean('payment_mobile_money_enabled')->default(true)->after('payment_cod_enabled');
            }
        });
    }

    public function down(): void
    {
        Schema::table('store_settings', function (Blueprint $table) {
            foreach (['store_url_path', 'payment_stripe_enabled', 'payment_cod_enabled', 'payment_mobile_money_enabled'] as $col) {
                if (Schema::hasColumn('store_settings', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};

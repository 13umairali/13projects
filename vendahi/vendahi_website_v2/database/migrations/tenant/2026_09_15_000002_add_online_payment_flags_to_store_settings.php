<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Per-gateway on/off switches for the storefront checkout, matching the
     * card switch (payment_stripe_enabled) added earlier: an online gateway
     * can now be switched off without clearing its stored credentials.
     * Default ON so existing tenants keep offering every gateway they have
     * configured.
     */
    private const COLUMNS = [
        'payment_razorpay_enabled',
        'payment_bkash_enabled',
        'payment_sslcommerz_enabled',
        'payment_paymob_enabled',
        'payment_myfatoorah_enabled',
        'payment_moyasar_enabled',
        'payment_xpay_enabled',
        'payment_payfast_enabled',
        'payment_ligdicash_enabled',
        'payment_dodo_enabled',
        'payment_waafipay_enabled',
    ];

    public function up(): void
    {
        Schema::table('store_settings', function (Blueprint $table) {
            $after = 'payment_mobile_money_enabled';
            foreach (self::COLUMNS as $col) {
                if (! Schema::hasColumn('store_settings', $col)) {
                    $table->boolean($col)->default(true)->after($after);
                }
                $after = $col;
            }
        });
    }

    public function down(): void
    {
        Schema::table('store_settings', function (Blueprint $table) {
            foreach (self::COLUMNS as $col) {
                if (Schema::hasColumn('store_settings', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Per-tenant payment gateway credentials (Stripe, Razorpay) for tenant-facing
 * payments: POS, sales, and the storefront. Lives in the tenant database so
 * every tenant charges into its own gateway account. Not to be confused with
 * App\Models\Central\PaymentGatewaySetting, which holds the platform's own
 * gateway used for subscription billing.
 */
class PaymentSetting extends Model
{
    protected $table = 'payment_settings';

    protected $fillable = [
        'stripe_key', 'stripe_secret',
        'razorpay_key_id', 'razorpay_key_secret',
        'payfast_merchant_id', 'payfast_merchant_key', 'payfast_passphrase', 'payfast_sandbox',
        'ligdicash_api_key', 'ligdicash_auth_token',
        'dodo_api_key', 'dodo_product_id', 'dodo_webhook_secret', 'dodo_test_mode',
        'waafipay_merchant_uid', 'waafipay_store_id', 'waafipay_hpp_key', 'waafipay_webhook_secret', 'waafipay_test_mode',
        'waafipay_api_user_id', 'waafipay_api_key',
        'bkash_app_key', 'bkash_app_secret', 'bkash_username', 'bkash_password', 'bkash_test_mode',
        'sslcommerz_store_id', 'sslcommerz_store_password', 'sslcommerz_test_mode',
        'paymob_region', 'paymob_public_key', 'paymob_secret_key', 'paymob_api_key',
        'paymob_integration_id', 'paymob_hmac_secret', 'paymob_currency',
        'myfatoorah_api_token', 'myfatoorah_country', 'myfatoorah_currency', 'myfatoorah_test_mode',
        'moyasar_secret_key', 'moyasar_currency',
        'xpay_account_id', 'xpay_publishable_key', 'xpay_secret_key', 'xpay_hmac_secret',
        'xpay_webhook_hmac_secret', 'xpay_currency', 'xpay_test_mode',
        'paypal_client_id', 'paypal_client_secret', 'paypal_webhook_id', 'paypal_test_mode',
        'paystack_public_key', 'paystack_secret_key',
        'flutterwave_public_key', 'flutterwave_secret_key', 'flutterwave_secret_hash',
    ];

    protected $hidden = [
        'stripe_secret', 'razorpay_key_secret',
        'payfast_merchant_key', 'payfast_passphrase',
        'ligdicash_auth_token',
        'dodo_api_key', 'dodo_webhook_secret',
        'waafipay_hpp_key', 'waafipay_webhook_secret', 'waafipay_api_key',
        'bkash_app_secret', 'bkash_password',
        'sslcommerz_store_password',
        'paymob_secret_key', 'paymob_api_key', 'paymob_hmac_secret',
        'myfatoorah_api_token',
        'moyasar_secret_key',
        'xpay_secret_key', 'xpay_hmac_secret', 'xpay_webhook_hmac_secret',
        'paypal_client_secret',
        'paystack_secret_key',
        'flutterwave_secret_key', 'flutterwave_secret_hash',
    ];

    protected $casts = [
        'payfast_sandbox'      => 'boolean',
        'dodo_test_mode'       => 'boolean',
        'waafipay_test_mode'   => 'boolean',
        'bkash_test_mode'      => 'boolean',
        'sslcommerz_test_mode' => 'boolean',
        'myfatoorah_test_mode' => 'boolean',
        'xpay_test_mode'       => 'boolean',
        'paypal_test_mode'     => 'boolean',
    ];

    /**
     * Return the single settings row, creating defaults if it does not exist yet.
     */
    public static function current()
    {
        return static::first() ?: static::create([]);
    }
}

<?php

namespace App\Models\Central;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class PaymentGatewaySetting extends Model
{
    protected $connection = 'central';

    protected $table = 'payment_gateway_settings';

    protected $fillable = [
        'gateway',
        'is_active',
        'test_mode',
        'credentials',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'test_mode' => 'boolean',
        'credentials' => 'array',
    ];

    /**
     * Gateway definitions: each gateway's configurable credential fields.
     * Fields marked 'secret' will render as password inputs and be encrypted at rest.
     */
    public const GATEWAYS = [
        'stripe' => [
            'label'       => 'Stripe',
            'icon'        => 'bi-stripe',
            'color'       => '#635bff',
            'description' => 'Accept credit/debit card payments worldwide.',
            'fields'      => [
                'public_key'     => ['label' => 'Publishable Key', 'secret' => false, 'placeholder' => 'pk_test_...'],
                'secret_key'     => ['label' => 'Secret Key',      'secret' => true,  'placeholder' => 'sk_test_...'],
                'webhook_secret' => ['label' => 'Webhook Secret',  'secret' => true,  'placeholder' => 'whsec_...'],
            ],
        ],
        'paypal' => [
            'label'       => 'PayPal',
            'icon'        => 'bi-paypal',
            'color'       => '#003087',
            'description' => 'Accept PayPal payments and credit cards.',
            'fields'      => [
                'client_id'     => ['label' => 'Client ID',     'secret' => false, 'placeholder' => 'AV...'],
                'client_secret' => ['label' => 'Client Secret', 'secret' => true,  'placeholder' => 'EL...'],
                'webhook_id'    => ['label' => 'Webhook ID',    'secret' => false, 'placeholder' => 'WH-...'],
            ],
        ],
        'paystack' => [
            'label'       => 'Paystack',
            'icon'        => 'bi-credit-card-2-back',
            'color'       => '#00c3f7',
            'description' => 'Simple payments for African businesses.',
            'fields'      => [
                'public_key' => ['label' => 'Public Key', 'secret' => false, 'placeholder' => 'pk_test_...'],
                'secret_key' => ['label' => 'Secret Key', 'secret' => true,  'placeholder' => 'sk_test_...'],
            ],
        ],
        'flutterwave' => [
            'label'       => 'Flutterwave',
            'icon'        => 'bi-credit-card-fill',
            'color'       => '#f5a623',
            'description' => 'Payments infrastructure for Africa and beyond.',
            'fields'      => [
                'public_key'     => ['label' => 'Public Key',     'secret' => false, 'placeholder' => 'FLWPUBK_TEST-...'],
                'secret_key'     => ['label' => 'Secret Key',     'secret' => true,  'placeholder' => 'FLWSECK_TEST-...'],
                'encryption_key' => ['label' => 'Encryption Key', 'secret' => true,  'placeholder' => 'FLWENCR_TEST-...'],
            ],
        ],
        'mollie' => [
            'label'       => 'Mollie',
            'icon'        => 'bi-credit-card',
            'color'       => '#0B7BFF',
            'description' => 'Accept cards, iDEAL, Bancontact, PayPal, and more via Mollie.',
            'fields'      => [
                'api_key' => ['label' => 'API Key', 'secret' => true, 'placeholder' => 'test_... or live_...'],
            ],
        ],
        'razorpay' => [
            'label'       => 'Razorpay',
            'icon'        => 'bi-currency-rupee',
            'color'       => '#0f4bd8',
            'description' => 'Accept payments via UPI, cards, netbanking, and wallets.',
            'fields'      => [
                'key_id'         => ['label' => 'Key ID',         'secret' => false, 'placeholder' => 'rzp_test_... or rzp_live_...'],
                'key_secret'     => ['label' => 'Key Secret',     'secret' => true,  'placeholder' => 'Your Razorpay Key Secret'],
                'webhook_secret' => ['label' => 'Webhook Secret', 'secret' => true,  'placeholder' => 'Set in Razorpay Dashboard > Webhooks'],
            ],
        ],
        'payfast' => [
            'label'       => 'PayFast',
            'icon'        => 'bi-cash-stack',
            'color'       => '#e2183d',
            'description' => 'Accept cards, Instant EFT, and more in South Africa.',
            'fields'      => [
                'merchant_id'  => ['label' => 'Merchant ID',  'secret' => false, 'placeholder' => 'e.g. 10000100'],
                'merchant_key' => ['label' => 'Merchant Key', 'secret' => true,  'placeholder' => 'Your PayFast Merchant Key'],
                'passphrase'   => ['label' => 'Passphrase',   'secret' => true,  'placeholder' => 'Your PayFast Passphrase'],
            ],
        ],
        'ligdicash' => [
            'label'       => 'LigdiCash',
            'icon'        => 'bi-wallet2',
            'color'       => '#f5821f',
            'description' => 'Accept Orange Money, Moov Money, and cards in West Africa.',
            'fields'      => [
                'api_key'    => ['label' => 'API Key',    'secret' => false, 'placeholder' => 'Your LigdiCash Apikey'],
                'auth_token' => ['label' => 'Auth Token', 'secret' => true,  'placeholder' => 'Your LigdiCash API token'],
            ],
        ],
        'dodo' => [
            'label'       => 'Dodo Payments',
            'icon'        => 'bi-credit-card-2-front',
            'color'       => '#111827',
            'description' => 'Global payments via cards, wallets, and UPI (merchant of record).',
            'fields'      => [
                'api_key'        => ['label' => 'API Key',        'secret' => true,  'placeholder' => 'Your Dodo Payments API key'],
                'product_id'     => ['label' => 'Product ID',     'secret' => false, 'placeholder' => 'pdt_...'],
                'webhook_secret' => ['label' => 'Webhook Secret', 'secret' => true,  'placeholder' => 'whsec_...'],
            ],
        ],
        'waafipay' => [
            'label'       => 'WaafiPay',
            'icon'        => 'bi-phone',
            'color'       => '#00a651',
            'description' => 'Accept EVC Plus, Zaad, Sahal, and cards in the Horn of Africa.',
            'fields'      => [
                'merchant_uid'   => ['label' => 'Merchant UID',   'secret' => false, 'placeholder' => 'M0910291...'],
                'store_id'       => ['label' => 'Store ID',       'secret' => false, 'placeholder' => 'e.g. 4001'],
                'hpp_key'        => ['label' => 'HPP Key',        'secret' => true,  'placeholder' => 'Your Hosted Payment Page key'],
                'api_user_id'    => ['label' => 'API User ID',    'secret' => false, 'placeholder' => 'e.g. 1000001'],
                'api_key'        => ['label' => 'API Key',        'secret' => true,  'placeholder' => 'API-...'],
                'webhook_secret' => ['label' => 'Webhook Secret', 'secret' => true,  'placeholder' => 'Your webhook endpoint secret'],
            ],
        ],
        'bkash' => [
            'label'       => 'bKash',
            'icon'        => 'bi-phone',
            'color'       => '#e2136e',
            'description' => 'Accept bKash mobile wallet payments in Bangladesh (BDT).',
            'fields'      => [
                'app_key'    => ['label' => 'App Key',    'secret' => false, 'placeholder' => 'Your bKash App Key'],
                'app_secret' => ['label' => 'App Secret', 'secret' => true,  'placeholder' => 'Your bKash App Secret'],
                'username'   => ['label' => 'Username',   'secret' => false, 'placeholder' => '01XXXXXXXXX'],
                'password'   => ['label' => 'Password',   'secret' => true,  'placeholder' => 'Your bKash merchant password'],
            ],
        ],
        'sslcommerz' => [
            'label'       => 'SSLCommerz',
            'icon'        => 'bi-shield-check',
            'color'       => '#2e3192',
            'description' => 'Accept cards, bKash/Nagad/Rocket and internet banking in Bangladesh (BDT).',
            'fields'      => [
                'store_id'     => ['label' => 'Store ID',       'secret' => false, 'placeholder' => 'teststore00live'],
                'store_passwd' => ['label' => 'Store Password', 'secret' => true,  'placeholder' => 'Your SSLCommerz store password'],
            ],
        ],
        'paymob' => [
            'label'       => 'Paymob',
            'icon'        => 'bi-credit-card-2-front',
            'color'       => '#0d3b66',
            'description' => 'Accept cards, wallets and local payment methods in Egypt, KSA, UAE, Pakistan and Oman.',
            'fields'      => [
                'api_region'     => ['label' => 'Region',                 'secret' => false, 'placeholder' => 'egypt / ksa / uae / pakistan / oman'],
                'public_key'     => ['label' => 'Public Key',             'secret' => false, 'placeholder' => 'egy_pk_test_...'],
                'secret_key'     => ['label' => 'Secret Key',             'secret' => true,  'placeholder' => 'egy_sk_test_...'],
                'api_key'        => ['label' => 'API Key',                'secret' => true,  'placeholder' => 'Your Paymob API Key (JWT)'],
                'integration_id' => ['label' => 'Integration ID(s)',      'secret' => false, 'placeholder' => 'e.g. 4419544 or 4419544,4419545'],
                'hmac_secret'    => ['label' => 'HMAC Secret',            'secret' => true,  'placeholder' => 'Your Paymob HMAC secret'],
            ],
        ],
        'myfatoorah' => [
            'label'       => 'MyFatoorah',
            'icon'        => 'bi-wallet2',
            'color'       => '#3fb5a3',
            'description' => 'Accept KNET, mada, cards, Apple Pay and STC Pay across the GCC.',
            'fields'      => [
                'api_token'      => ['label' => 'API Token',              'secret' => true,  'placeholder' => 'Your MyFatoorah API token'],
                'api_country'    => ['label' => 'Account Country (live)', 'secret' => false, 'placeholder' => 'KWT / SAU / QAT / EGY / ARE / BHR / OMN / JOR'],
                'webhook_secret' => ['label' => 'Webhook Secret Key',     'secret' => true,  'placeholder' => 'From Vendor Portal > Webhook Settings'],
            ],
        ],
        'moyasar' => [
            'label'       => 'Moyasar',
            'icon'        => 'bi-credit-card',
            'color'       => '#20b2aa',
            'description' => 'Accept mada, credit cards, Apple Pay and STC Pay in Saudi Arabia (SAR).',
            'fields'      => [
                'secret_key'     => ['label' => 'Secret Key',             'secret' => true,  'placeholder' => 'sk_test_... or sk_live_...'],
                'webhook_secret' => ['label' => 'Webhook Shared Secret',  'secret' => true,  'placeholder' => 'Secret token of your webhook endpoint'],
            ],
        ],
        'xpay' => [
            'label'       => 'XPay',
            'icon'        => 'bi-credit-card',
            'color'       => '#0f766e',
            'description' => 'Accept cards and wallets in Pakistan (PKR) via XPay by PostEx.',
            'fields'      => [
                'account_id'          => ['label' => 'Account ID',          'secret' => false, 'placeholder' => 'Your XPay account id'],
                'publishable_key'     => ['label' => 'Publishable Key',     'secret' => false, 'placeholder' => 'xpay_pk_...'],
                'secret_key'          => ['label' => 'Secret Key',          'secret' => true,  'placeholder' => 'xpay_sk_...'],
                'hmac_secret'         => ['label' => 'API HMAC Secret',     'secret' => true,  'placeholder' => 'Key Details > HMAC Keys'],
                'webhook_hmac_secret' => ['label' => 'Webhook HMAC Secret', 'secret' => true,  'placeholder' => 'Key Details > HMAC Keys'],
            ],
        ],
    ];

    /**
     * Encrypt secret fields before saving.
     */
    public function setCredentialsAttribute($value): void
    {
        if (is_array($value)) {
            $gatewayDef = self::GATEWAYS[$this->gateway] ?? null;
            if ($gatewayDef) {
                foreach ($gatewayDef['fields'] as $key => $field) {
                    if (!empty($field['secret']) && !empty($value[$key])) {
                        $value[$key] = Crypt::encryptString($value[$key]);
                    }
                }
            }
        }

        $this->attributes['credentials'] = is_array($value) ? json_encode($value) : $value;
    }

    /**
     * Get a decrypted credential value.
     */
    public function getCredential(string $key, ?string $default = null): ?string
    {
        $creds = $this->credentials ?? [];
        $raw = $creds[$key] ?? $default;

        if ($raw === null || $raw === '') {
            return $default;
        }

        $gatewayDef = self::GATEWAYS[$this->gateway] ?? null;
        $isSecret = $gatewayDef['fields'][$key]['secret'] ?? false;

        if ($isSecret) {
            try {
                return Crypt::decryptString($raw);
            } catch (\Exception $e) {
                return $raw;
            }
        }

        return $raw;
    }

    /**
     * Get all credentials with secrets decrypted.
     */
    public function getDecryptedCredentials(): array
    {
        $gatewayDef = self::GATEWAYS[$this->gateway] ?? null;
        if (!$gatewayDef) {
            return $this->credentials ?? [];
        }

        $result = [];
        foreach ($gatewayDef['fields'] as $key => $field) {
            $result[$key] = $this->getCredential($key, '');
        }

        return $result;
    }

    /**
     * Get all active gateways.
     */
    public static function getActiveGateways(): \Illuminate\Database\Eloquent\Collection
    {
        return static::where('is_active', true)->get();
    }

    /**
     * Load a specific gateway's settings.
     */
    public static function forGateway(string $gateway): ?self
    {
        return static::where('gateway', $gateway)->first();
    }
}

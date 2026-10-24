<?php

namespace App\Services\PaymentGateways;

class PaymentGatewayFactory
{
    protected static array $gateways = [
        'stripe'      => StripeGateway::class,
        'paypal'      => PaypalGateway::class,
        'paystack'    => PaystackGateway::class,
        'flutterwave' => FlutterwaveGateway::class,
        'mollie'      => MollieGateway::class,
        'razorpay'    => RazorpayGateway::class,
        'payfast'     => PayfastGateway::class,
        'ligdicash'   => LigdicashGateway::class,
        'dodo'        => DodoGateway::class,
        'waafipay'    => WaafipayGateway::class,
        'bkash'       => BkashGateway::class,
        'sslcommerz'  => SslcommerzGateway::class,
        'paymob'      => PaymobGateway::class,
        'myfatoorah'  => MyfatoorahGateway::class,
        'moyasar'     => MoyasarGateway::class,
        'xpay'        => XpayGateway::class,
        'offline'     => OfflineGateway::class,
    ];

    public static function resolve(string $key): ?PaymentGatewayInterface
    {
        $class = self::$gateways[$key] ?? null;

        if (! $class || ! class_exists($class)) {
            return null;
        }

        $instance = new $class();

        if (! $instance->isAvailable()) {
            return null;
        }

        return $instance;
    }

    /**
     * Resolve a gateway for webhook processing.
     * Unlike resolve(), this only requires valid credentials -- not is_active.
     * Webhooks must be processed even if the gateway is disabled for new checkouts.
     */
    public static function resolveForWebhook(string $key): ?PaymentGatewayInterface
    {
        $class = self::$gateways[$key] ?? null;

        if (! $class || ! class_exists($class)) {
            return null;
        }

        $instance = new $class();

        if (method_exists($instance, 'hasValidCredentials') && $instance->hasValidCredentials()) {
            return $instance;
        }

        if ($instance->isAvailable()) {
            return $instance;
        }

        return null;
    }

    /**
     * @return array<string, array{key: string, label: string, icon: string, color: string}>
     */
    public static function getAvailableGateways(): array
    {
        $available = [];

        foreach (self::$gateways as $key => $class) {
            if (! class_exists($class)) {
                continue;
            }

            $instance = new $class();

            if ($instance->isAvailable()) {
                $info = $instance->getDisplayInfo();
                $info['currency_config'] = self::getGatewayCurrencyConfig($key);
                $available[$key] = $info;
            }
        }

        return $available;
    }

    /**
     * Load per-gateway currency configuration from the database.
     * Falls back to built-in defaults if not configured.
     *
     * @return array{supported_currencies: string[], default_currency: string}
     */
    public static function getGatewayCurrencyConfig(string $key): array
    {
        $row = \DB::connection('central')
            ->table('payment_gateway_settings')
            ->where('gateway', $key)
            ->first();

        if ($row && ! empty($row->supported_currencies)) {
            $supported = json_decode($row->supported_currencies, true);
            if (is_array($supported) && count($supported) > 0) {
                return [
                    'supported_currencies' => array_map('strtoupper', $supported),
                    'default_currency'     => strtoupper($row->default_currency ?: $supported[0]),
                ];
            }
        }

        // Fallback to built-in defaults
        return self::getDefaultCurrencyPresets()[$key] ?? [
            'supported_currencies' => ['USD'],
            'default_currency'     => 'USD',
        ];
    }

    /**
     * Built-in currency presets per gateway.
     * Used when admin hasn't configured currencies in gateway settings.
     */
    public static function getDefaultCurrencyPresets(): array
    {
        return [
            'stripe' => [
                'supported_currencies' => ['USD', 'EUR', 'GBP', 'CAD', 'AUD', 'JPY', 'CHF', 'SGD', 'HKD', 'NZD', 'SEK', 'NOK', 'DKK', 'PLN', 'CZK', 'BRL', 'MXN', 'INR'],
                'default_currency'     => 'USD',
            ],
            'paypal' => [
                'supported_currencies' => ['USD', 'EUR', 'GBP', 'CAD', 'AUD', 'JPY', 'CHF', 'SGD', 'HKD', 'NZD', 'SEK', 'NOK', 'DKK', 'PLN', 'CZK', 'BRL', 'MXN'],
                'default_currency'     => 'USD',
            ],
            'paystack' => [
                'supported_currencies' => ['NGN', 'GHS', 'ZAR', 'KES'],
                'default_currency'     => 'NGN',
            ],
            'flutterwave' => [
                // Only include currencies natively supported without DCC.
                // USD/EUR/GBP require DCC which most accounts don't have —
                // admins can add them via Settings > Payment Gateways if enabled.
                'supported_currencies' => ['NGN', 'GHS', 'ZAR', 'KES', 'TZS', 'UGX', 'RWF', 'XOF', 'XAF'],
                'default_currency'     => 'NGN',
            ],
            'mollie' => [
                'supported_currencies' => ['EUR', 'USD', 'GBP', 'CHF', 'DKK', 'NOK', 'SEK', 'PLN', 'CAD', 'AUD', 'HUF', 'CZK'],
                'default_currency'     => 'EUR',
            ],
            'razorpay' => [
                // INR only by default — international currencies require
                // explicit activation on the Razorpay account. Admins can add
                // more via Settings > Payment Gateways once enabled.
                'supported_currencies' => ['INR'],
                'default_currency'     => 'INR',
            ],
            'payfast' => [
                // PayFast processes ZAR only.
                'supported_currencies' => ['ZAR'],
                'default_currency'     => 'ZAR',
            ],
            'ligdicash' => [
                // LigdiCash processes XOF only (no decimal subunit).
                'supported_currencies' => ['XOF'],
                'default_currency'     => 'XOF',
            ],
            'dodo' => [
                // Charged against the configured Pay-What-You-Want product,
                // which has a fixed currency (USD by default). Dodo, as
                // merchant of record, localizes presentation itself.
                'supported_currencies' => ['USD'],
                'default_currency'     => 'USD',
            ],
            'waafipay' => [
                // USD is the primary settlement currency across Waafi wallets.
                'supported_currencies' => ['USD'],
                'default_currency'     => 'USD',
            ],
            'bkash' => [
                // bKash processes BDT only.
                'supported_currencies' => ['BDT'],
                'default_currency'     => 'BDT',
            ],
            'sslcommerz' => [
                // BDT by default — non-BDT support depends on the SSLCommerz
                // account; admins can add currencies via Settings > Payment
                // Gateways once enabled on their account.
                'supported_currencies' => ['BDT'],
                'default_currency'     => 'BDT',
            ],
            'paymob' => [
                // Charge currency must match the Paymob account's region
                // (EGP for Egypt, SAR for KSA, AED for UAE, PKR for Pakistan,
                // OMR for Oman) — admins should trim this list to their region.
                'supported_currencies' => ['EGP', 'SAR', 'AED', 'PKR', 'OMR'],
                'default_currency'     => 'EGP',
            ],
            'myfatoorah' => [
                'supported_currencies' => ['KWD', 'SAR', 'AED', 'QAR', 'BHD', 'OMR', 'JOD', 'EGP', 'USD'],
                'default_currency'     => 'KWD',
            ],
            'moyasar' => [
                // Moyasar settles in SAR; USD acceptance depends on the account.
                'supported_currencies' => ['SAR'],
                'default_currency'     => 'SAR',
            ],
            'xpay' => [
                // XPay (PostEx) processes PKR only.
                'supported_currencies' => ['PKR'],
                'default_currency'     => 'PKR',
            ],
        ];
    }

    /**
     * Return definitions for all registered gateways (regardless of availability).
     */
    public static function getAllGatewayDefinitions(): array
    {
        $definitions = [];

        foreach (self::$gateways as $key => $class) {
            if (! class_exists($class)) {
                continue;
            }

            $instance = new $class();
            $info     = $instance->getDisplayInfo();

            $info['fields'] = self::getFieldsForGateway($key);

            $definitions[$key] = $info;
        }

        return $definitions;
    }

    protected static function getFieldsForGateway(string $key): array
    {
        return match ($key) {
            'stripe' => [
                'publishable_key' => [
                    'label'       => 'Publishable Key',
                    'placeholder' => 'pk_test_...',
                    'secret'      => false,
                ],
                'secret_key' => [
                    'label'       => 'Secret Key',
                    'placeholder' => 'sk_test_...',
                    'secret'      => true,
                ],
                'webhook_secret' => [
                    'label'       => 'Webhook Secret',
                    'placeholder' => 'whsec_...',
                    'secret'      => true,
                ],
            ],
            'paypal' => [
                'client_id' => [
                    'label'       => 'Client ID',
                    'placeholder' => 'AX...',
                    'secret'      => false,
                ],
                'client_secret' => [
                    'label'       => 'Client Secret',
                    'placeholder' => 'EL...',
                    'secret'      => true,
                ],
                'webhook_id' => [
                    'label'       => 'Webhook ID',
                    'placeholder' => 'e.g. 5GP028...',
                    'secret'      => false,
                ],
            ],
            'paystack' => [
                'public_key' => [
                    'label'       => 'Public Key',
                    'placeholder' => 'pk_test_...',
                    'secret'      => false,
                ],
                'secret_key' => [
                    'label'       => 'Secret Key',
                    'placeholder' => 'sk_test_...',
                    'secret'      => true,
                ],
            ],
            'flutterwave' => [
                'public_key' => [
                    'label'       => 'Public Key',
                    'placeholder' => 'FLWPUBK_TEST-...',
                    'secret'      => false,
                    'help'        => 'Starts with FLWPUBK_ (from Flutterwave dashboard)',
                ],
                'secret_key' => [
                    'label'       => 'Secret Key',
                    'placeholder' => 'FLWSECK_TEST-...',
                    'secret'      => true,
                    'help'        => 'Starts with FLWSECK_ (from Flutterwave dashboard)',
                ],
                'encryption_key' => [
                    'label'       => 'Encryption Key',
                    'placeholder' => 'Your Flutterwave Encryption Key',
                    'secret'      => true,
                ],
                'secret_hash' => [
                    'label'       => 'Secret Hash (Webhook)',
                    'placeholder' => 'Set in Flutterwave Dashboard > Webhooks',
                    'secret'      => true,
                ],
            ],
            'mollie' => [
                'api_key' => [
                    'label'       => 'API Key',
                    'placeholder' => 'test_... or live_...',
                    'secret'      => true,
                    'help'        => 'From Mollie Dashboard > Developers > API keys. Use a test_ key for sandbox, live_ for production.',
                ],
            ],
            'razorpay' => [
                'key_id' => [
                    'label'       => 'Key ID',
                    'placeholder' => 'rzp_test_... or rzp_live_...',
                    'secret'      => false,
                    'help'        => 'From Razorpay Dashboard > Account & Settings > API Keys',
                ],
                'key_secret' => [
                    'label'       => 'Key Secret',
                    'placeholder' => 'Your Razorpay Key Secret',
                    'secret'      => true,
                ],
                'webhook_secret' => [
                    'label'       => 'Webhook Secret',
                    'placeholder' => 'Set in Razorpay Dashboard > Webhooks',
                    'secret'      => true,
                    'help'        => 'Required for webhook verification — use the same secret you set when creating the webhook.',
                ],
            ],
            'payfast' => [
                'merchant_id' => [
                    'label'       => 'Merchant ID',
                    'placeholder' => 'e.g. 10000100',
                    'secret'      => false,
                    'help'        => 'From PayFast Dashboard > Settings > Integration',
                ],
                'merchant_key' => [
                    'label'       => 'Merchant Key',
                    'placeholder' => 'Your PayFast Merchant Key',
                    'secret'      => true,
                ],
                'passphrase' => [
                    'label'       => 'Passphrase',
                    'placeholder' => 'Your PayFast Passphrase',
                    'secret'      => true,
                    'help'        => 'Strongly recommended — must match the passphrase set in PayFast Dashboard > Settings > Integration. Use the Sandbox/Live toggle above to switch between sandbox.payfast.co.za and www.payfast.co.za.',
                ],
            ],
            'ligdicash' => [
                'api_key' => [
                    'label'       => 'API Key',
                    'placeholder' => 'Your LigdiCash Apikey',
                    'secret'      => false,
                    'help'        => 'From your LigdiCash dashboard, under your API project (Apikey header).',
                ],
                'auth_token' => [
                    'label'       => 'Auth Token',
                    'placeholder' => 'Your LigdiCash API token',
                    'secret'      => true,
                    'help'        => 'The Bearer token of your LigdiCash API project. XOF payments only.',
                ],
            ],
            'dodo' => [
                'api_key' => [
                    'label'       => 'API Key',
                    'placeholder' => 'Your Dodo Payments API key',
                    'secret'      => true,
                    'help'        => 'From Dodo Dashboard > Developer > API Keys. Use the Sandbox/Live toggle above to switch between test and live mode.',
                ],
                'product_id' => [
                    'label'       => 'Product ID',
                    'placeholder' => 'pdt_...',
                    'secret'      => false,
                    'help'        => 'Create a one-time product with "Pay What You Want" enabled (minimum price as low as possible) in the Dodo dashboard, and paste its product id here. Subscription amounts are charged against it.',
                ],
                'webhook_secret' => [
                    'label'       => 'Webhook Secret',
                    'placeholder' => 'whsec_...',
                    'secret'      => true,
                    'help'        => 'From the webhook endpoint you create in Dodo Dashboard > Developer > Webhooks.',
                ],
            ],
            'waafipay' => [
                'merchant_uid' => [
                    'label'       => 'Merchant UID',
                    'placeholder' => 'M0910291...',
                    'secret'      => false,
                    'help'        => 'From your WaafiPay merchant account.',
                ],
                'store_id' => [
                    'label'       => 'Store ID',
                    'placeholder' => 'e.g. 4001',
                    'secret'      => false,
                ],
                'hpp_key' => [
                    'label'       => 'HPP Key',
                    'placeholder' => 'Your Hosted Payment Page key',
                    'secret'      => true,
                ],
                'api_user_id' => [
                    'label'       => 'API User ID',
                    'placeholder' => 'e.g. 1000001',
                    'secret'      => false,
                    'help'        => 'Optional — for the direct wallet API (API_PURCHASE).',
                ],
                'api_key' => [
                    'label'       => 'API Key',
                    'placeholder' => 'API-...',
                    'secret'      => true,
                    'help'        => 'Optional — secret API key from the WaafiPay Merchant Portal (direct wallet API).',
                ],
                'webhook_secret' => [
                    'label'       => 'Webhook Secret',
                    'placeholder' => 'Secret of your registered webhook endpoint',
                    'secret'      => true,
                    'help'        => 'Required for the hosted payment page flow — payments are confirmed by signed webhooks. Use the Sandbox/Live toggle above to switch between sandbox.waafipay.net and api.waafipay.net.',
                ],
            ],
            'bkash' => [
                'app_key' => [
                    'label'       => 'App Key',
                    'placeholder' => 'Your bKash App Key',
                    'secret'      => false,
                    'help'        => 'Tokenized Checkout credentials from the bKash merchant portal. Use the Sandbox/Live toggle above to switch between tokenized.sandbox.bka.sh and tokenized.pay.bka.sh.',
                ],
                'app_secret' => [
                    'label'       => 'App Secret',
                    'placeholder' => 'Your bKash App Secret',
                    'secret'      => true,
                ],
                'username' => [
                    'label'       => 'Username',
                    'placeholder' => '01XXXXXXXXX',
                    'secret'      => false,
                ],
                'password' => [
                    'label'       => 'Password',
                    'placeholder' => 'Your bKash merchant password',
                    'secret'      => true,
                    'help'        => 'bKash charges in BDT only and sends no webhooks — payments are confirmed on the return leg.',
                ],
            ],
            'sslcommerz' => [
                'store_id' => [
                    'label'       => 'Store ID',
                    'placeholder' => 'teststore00live',
                    'secret'      => false,
                    'help'        => 'From your SSLCommerz merchant panel. Use the Sandbox/Live toggle above to switch between sandbox.sslcommerz.com and securepay.sslcommerz.com.',
                ],
                'store_passwd' => [
                    'label'       => 'Store Password',
                    'placeholder' => 'Your SSLCommerz store password',
                    'secret'      => true,
                    'help'        => 'Register the IPN URL shown in the guide under My Stores > IPN Settings — payments are confirmed via the IPN plus the validation API.',
                ],
            ],
            'paymob' => [
                'api_region' => [
                    'label'       => 'Region',
                    'placeholder' => 'egypt / ksa / uae / pakistan / oman',
                    'secret'      => false,
                    'help'        => 'The country of your Paymob account — it selects the API host (accept.paymob.com, ksa.paymob.com, uae.paymob.com, pakistan.paymob.com or oman.paymob.com). Defaults to egypt.',
                ],
                'public_key' => [
                    'label'       => 'Public Key',
                    'placeholder' => 'egy_pk_test_...',
                    'secret'      => false,
                    'help'        => 'From Paymob Dashboard > Settings > Account Info.',
                ],
                'secret_key' => [
                    'label'       => 'Secret Key',
                    'placeholder' => 'egy_sk_test_...',
                    'secret'      => true,
                ],
                'api_key' => [
                    'label'       => 'API Key',
                    'placeholder' => 'Your Paymob API Key (JWT)',
                    'secret'      => true,
                    'help'        => 'The long API key from Account Info — used for server-side transaction verification.',
                ],
                'integration_id' => [
                    'label'       => 'Integration ID(s)',
                    'placeholder' => 'e.g. 4419544 or 4419544,4419545',
                    'secret'      => false,
                    'help'        => 'Payment integration id(s) from Paymob Dashboard > Developers > Payment Integrations. Comma-separate several to offer multiple methods on the checkout.',
                ],
                'hmac_secret' => [
                    'label'       => 'HMAC Secret',
                    'placeholder' => 'Your Paymob HMAC secret',
                    'secret'      => true,
                    'help'        => 'From Account Info — required to authenticate transaction callbacks. Set the transaction processed callback URL shown in the guide on each integration.',
                ],
            ],
            'myfatoorah' => [
                'api_token' => [
                    'label'       => 'API Token',
                    'placeholder' => 'Your MyFatoorah API token',
                    'secret'      => true,
                    'help'        => 'From MyFatoorah Vendor Portal > Integration Settings > API Key. Use the Sandbox/Live toggle above to switch between apitest.myfatoorah.com and the live API.',
                ],
                'api_country' => [
                    'label'       => 'Account Country (live)',
                    'placeholder' => 'KWT / SAU / QAT / EGY / ARE / BHR / OMN / JOR',
                    'secret'      => false,
                    'help'        => 'ISO code of your MyFatoorah account country — selects the live API host (api-sa / api-qa / api-eg .myfatoorah.com; other countries use api.myfatoorah.com). Ignored in sandbox mode.',
                ],
                'webhook_secret' => [
                    'label'       => 'Webhook Secret Key',
                    'placeholder' => 'From Vendor Portal > Webhook Settings',
                    'secret'      => true,
                    'help'        => 'Recommended — enable the TransactionsStatusChanged webhook and paste its secret key here so notifications are signature-checked.',
                ],
            ],
            'moyasar' => [
                'secret_key' => [
                    'label'       => 'Secret Key',
                    'placeholder' => 'sk_test_... or sk_live_...',
                    'secret'      => true,
                    'help'        => 'From Moyasar Dashboard > Settings > API Keys. Use an sk_test_ key for sandbox — Moyasar has no separate sandbox host.',
                ],
                'webhook_secret' => [
                    'label'       => 'Webhook Shared Secret',
                    'placeholder' => 'Secret token of your webhook endpoint',
                    'secret'      => true,
                    'help'        => 'Recommended — create a webhook in Moyasar Dashboard > Settings > Webhooks pointing at the URL shown in the guide, and paste its shared secret here.',
                ],
            ],
            'xpay' => [
                'account_id' => [
                    'label'       => 'Account ID',
                    'placeholder' => 'Your XPay account id',
                    'secret'      => false,
                    'help'        => 'From XPay Dashboard > Settings > Store Info. Use the Sandbox/Live toggle above to switch between the staging and live XPay hosts — each mode has its own key set.',
                ],
                'publishable_key' => [
                    'label'       => 'Publishable Key',
                    'placeholder' => 'xpay_pk_test_... or xpay_pk_live_...',
                    'secret'      => false,
                    'help'        => 'From XPay Dashboard > Settings > Store Info > Key Details > API Keys. Used by the browser-side card element.',
                ],
                'secret_key' => [
                    'label'       => 'Secret Key',
                    'placeholder' => 'xpay_sk_...',
                    'secret'      => true,
                    'help'        => 'Key Details > API Keys. Sent as x-api-key on server calls only.',
                ],
                'hmac_secret' => [
                    'label'       => 'API HMAC Secret',
                    'placeholder' => 'Key Details > HMAC Keys',
                    'secret'      => true,
                    'help'        => 'Signs every API request (x-signature). XPay\'s own SDK also requires it in the browser element, as in their official plugins.',
                ],
                'webhook_hmac_secret' => [
                    'label'       => 'Webhook HMAC Secret',
                    'placeholder' => 'Key Details > HMAC Keys',
                    'secret'      => true,
                    'help'        => 'Recommended — verifies the X-Signature header of webhooks sent to the URL shown in the guide. Without it, webhooks are rejected and payments are confirmed on the payment page only.',
                ],
            ],
            'offline' => [],
            default => [],
        };
    }
}

<?php

namespace App\Services;

use App\Models\PaymentSetting;
use App\Services\PaymentGateways\XpayGateway;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * XPay by PostEx (Pakistan) client for tenant-facing payments (storefront
 * checkout).
 *
 * Uses the tenant's own XPay account: credentials come exclusively from the
 * tenant DB `payment_settings` row. Charges in the configured currency
 * (xpay_currency, PKR by default) — the store's base currency must match it.
 * Test vs live host/SDK is picked by xpay_test_mode (each mode has its own
 * key set on the XPay side).
 *
 * Flow (Stripe-Elements shaped, same as the storefront's credit_card flow):
 * server creates a Payment Intent for the server-computed total → the XPay
 * element on the checkout page confirms it with the client secret → the
 * order is created only after the intent is re-fetched server-side and its
 * status, amount and currency cross-checked ("never act on the browser's
 * word — always re-verify"). A signed webhook is a backstop for orders
 * already created whose paid flag is still pending.
 */
class TenantXpayService
{
    protected const LIVE_HOST = 'https://xstak-pay.xstak.com/public/';
    protected const TEST_HOST = 'https://xstak-pay-stg.xstak.com/public/';

    protected const LIVE_SDK = 'https://js.xstak.com/xpay.js';
    protected const TEST_SDK = 'https://js.xstak.com/xpay-stage.js';

    protected ?array $credentials = null;

    public function isConfigured(): bool
    {
        $c = $this->credentials();

        return $c['account_id'] !== '' && $c['publishable_key'] !== '' && $c['secret_key'] !== '' && $c['hmac_secret'] !== '';
    }

    public function currency(): string
    {
        return $this->credentials()['currency'];
    }

    public function isTestMode(): bool
    {
        return $this->credentials()['test_mode'];
    }

    public function host(): string
    {
        return $this->isTestMode() ? self::TEST_HOST : self::LIVE_HOST;
    }

    public function sdkUrl(): string
    {
        return $this->isTestMode() ? self::TEST_SDK : self::LIVE_SDK;
    }

    /**
     * Browser-side configuration for the XPay element. XPay's official SDK
     * takes the API HMAC secret as its third constructor argument (as in
     * XPay's own WooCommerce plugin), so it is part of this public config.
     */
    public function elementConfig(): array
    {
        $c = $this->credentials();

        return [
            'sdk_url'         => $this->sdkUrl(),
            'publishable_key' => $c['publishable_key'],
            'account_id'      => $c['account_id'],
            'hmac_secret'     => $c['hmac_secret'],
        ];
    }

    /**
     * Create a Payment Intent for the given (base-currency) amount.
     *
     * @return array{intent_id: string, client_secret: string, encryption_key: string}
     */
    public function createIntent(float $amount, array $customer, array $shipping, array $metadata): array
    {
        if (! $this->isConfigured()) {
            throw new RuntimeException('XPay is not configured for this tenant.');
        }

        $c = $this->credentials();

        $payload = self::filterEmpty([
            'amount'               => round($amount, 2),
            'currency'             => $c['currency'],
            'payment_method_types' => 'card',
            'customer'             => self::filterEmpty($customer),
            'shipping'             => self::filterEmpty($shipping),
            'metadata'             => array_map(fn ($v) => (string) $v, self::filterEmpty($metadata)),
        ]);

        $body = XpayGateway::canonicalJson($payload);

        $response = Http::withHeaders([
            'x-api-key'    => $c['secret_key'],
            'x-account-id' => $c['account_id'],
            'x-signature'  => hash_hmac('sha256', $body, $c['hmac_secret']),
            'Content-Type' => 'application/json',
            'Accept'       => 'application/json',
        ])->withBody($body, 'application/json')->timeout(30)->post($this->host() . 'v1/payment/intent');

        $json = $response->json() ?? [];
        $data = is_array($json['data'] ?? null) ? $json['data'] : [];

        if (! $response->successful() || empty($json['success']) || empty($data['_id']) || empty($data['pi_client_secret'])) {
            Log::error('XPay intent create failed', [
                'http_status' => $response->status(),
                'message'     => $json['message'] ?? null,
                'response'    => $json,
            ]);

            throw new RuntimeException('XPay intent creation failed: ' . (string) ($json['message'] ?? $response->status()));
        }

        return [
            'intent_id'      => (string) $data['_id'],
            'client_secret'  => (string) $data['pi_client_secret'],
            'encryption_key' => (string) ($data['encryptionKey'] ?? ''),
        ];
    }

    /**
     * Re-fetch a Payment Intent server-side.
     *
     * @return array{success: bool, status: string, transaction_id: ?string, amount: ?float, currency: ?string, metadata: array}
     */
    public function fetchIntent(string $intentId): array
    {
        $fail = ['success' => false, 'status' => '', 'transaction_id' => null, 'amount' => null, 'currency' => null, 'metadata' => []];

        if ($intentId === '' || ! $this->isConfigured()) {
            return $fail;
        }

        $c = $this->credentials();

        $response = Http::withHeaders([
            'x-api-key'    => $c['secret_key'],
            'x-account-id' => $c['account_id'],
            'Accept'       => 'application/json',
        ])->timeout(30)->get($this->host() . 'v1/payment/intent/details/' . rawurlencode($intentId));

        $json = $response->json() ?? [];
        $data = is_array($json['data'] ?? null) ? $json['data'] : [];

        if (! $response->successful() || empty($json['success']) || $data === []) {
            Log::warning('XPay intent fetch not successful', [
                'intent_id' => $intentId,
                'message'   => $json['message'] ?? null,
            ]);
            $fail['status'] = strtoupper((string) ($json['message'] ?? ''));

            return $fail;
        }

        return XpayGateway::normaliseIntent($data);
    }

    /**
     * FAILS CLOSED: true only when the intent succeeded and its amount and
     * currency match the expected (base-currency) order total. A missing
     * currency/amount on the intent object is tolerated only when XPay does
     * not return them at all (status alone then decides).
     */
    public function amountMatches(array $intent, float $expectedAmount): bool
    {
        if (empty($intent['success'])) {
            return false;
        }

        $currency = strtoupper((string) ($intent['currency'] ?? ''));
        if ($currency !== '' && $currency !== $this->currency()) {
            Log::error('XPay verification currency mismatch — order left unpaid', [
                'expected_currency' => $this->currency(),
                'charged_currency'  => $currency,
            ]);

            return false;
        }

        $amount = $intent['amount'];
        if ($amount !== null && (float) $amount + 0.01 < $expectedAmount) {
            Log::error('XPay verification amount mismatch — order left unpaid', [
                'expected' => $expectedAmount,
                'charged'  => $amount,
            ]);

            return false;
        }

        return true;
    }

    /** Verify a webhook's X-Signature against the tenant's webhook HMAC secret. */
    public function webhookSignatureValid(string $payload, string $signature): bool
    {
        return XpayGateway::webhookSignatureValid($payload, $signature, $this->credentials()['webhook_hmac_secret']);
    }

    public function hasWebhookSecret(): bool
    {
        return $this->credentials()['webhook_hmac_secret'] !== '';
    }

    /** Recursively drop empty values (XPay rejects blank required fields; omit instead). */
    protected static function filterEmpty(array $array): array
    {
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $array[$key] = self::filterEmpty($value);
            }
            if (($array[$key] === null || $array[$key] === '' || $array[$key] === []) && $array[$key] !== '0') {
                unset($array[$key]);
            }
        }

        return $array;
    }

    protected function credentials(): array
    {
        if ($this->credentials !== null) {
            return $this->credentials;
        }

        $settings = PaymentSetting::current();

        return $this->credentials = [
            'account_id'          => trim((string) ($settings->xpay_account_id ?? '')),
            'publishable_key'     => trim((string) ($settings->xpay_publishable_key ?? '')),
            'secret_key'          => trim((string) ($settings->xpay_secret_key ?? '')),
            'hmac_secret'         => trim((string) ($settings->xpay_hmac_secret ?? '')),
            'webhook_hmac_secret' => trim((string) ($settings->xpay_webhook_hmac_secret ?? '')),
            'currency'            => strtoupper(trim((string) ($settings->xpay_currency ?? '')) ?: 'PKR'),
            'test_mode'           => $settings->xpay_test_mode === null ? true : (bool) $settings->xpay_test_mode,
        ];
    }
}

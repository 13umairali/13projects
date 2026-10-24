<?php

namespace App\Services;

use App\Models\PaymentSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * Paymob (Unified Intention API) client for tenant-facing payments
 * (storefront checkout).
 *
 * Uses the tenant's own Paymob account: credentials come exclusively from the
 * tenant DB `payment_settings` row. Charges in the configured currency
 * (paymob_currency, EGP by default) — the store's base currency must match.
 *
 * Flow: pending order → server-side intention create with a self-generated
 * special_reference → buyer redirected to the region's unified checkout →
 * Paymob GETs the browser back to the return URL and POSTs the transaction
 * processed callback (HMAC-authenticated) to the webhook. Paid is only ever
 * written after the transaction inquiry API confirms the charge, with
 * reference, amount and currency cross-checked.
 */
class TenantPaymobService
{
    /** Region key → API host. */
    protected const REGION_HOSTS = [
        'egypt'    => 'https://accept.paymob.com',
        'ksa'      => 'https://ksa.paymob.com',
        'uae'      => 'https://uae.paymob.com',
        'pakistan' => 'https://pakistan.paymob.com',
        'oman'     => 'https://oman.paymob.com',
    ];

    protected ?array $credentials = null;

    public function isConfigured(): bool
    {
        $c = $this->credentials();

        return $c['secret_key'] !== '' && $c['public_key'] !== ''
            && $c['api_key'] !== '' && $c['integration_id'] !== ''
            && $c['hmac_secret'] !== '';
    }

    public function currency(): string
    {
        return $this->credentials()['currency'];
    }

    /** Minor-unit multiplier for the charge currency (Paymob amounts are integers). */
    public function minorUnits(string $currency): int
    {
        return match (strtoupper($currency)) {
            'OMR', 'KWD', 'BHD' => 1000,
            'JPY'               => 1,
            default             => 100,
        };
    }

    /**
     * Create a unified-checkout intention and return the hosted checkout URL
     * plus the self-generated special_reference (the unguessable reference
     * the callbacks and inquiry API resolve the pending order by).
     *
     * @param  array{name: string, email: string, phone: string}  $customer
     * @return array{url: string, reference: string}
     */
    public function createIntention(
        float $amount,
        string $orderRef,
        array $customer,
        string $returnUrl,
        string $webhookUrl
    ): array {
        $c = $this->credentials();

        if (! $this->isConfigured()) {
            throw new RuntimeException('Paymob is not configured for this tenant.');
        }

        $reference = 'pmb_' . bin2hex(random_bytes(16));
        $currency  = $c['currency'];

        $integrationIds = array_values(array_filter(array_map('intval', preg_split('/[\s,]+/', $c['integration_id']))));
        $amountMinor    = (int) round($amount * $this->minorUnits($currency));

        [$firstName, $lastName] = $this->splitName((string) ($customer['name'] ?? ''));

        $response = Http::withHeaders(['Authorization' => 'Token ' . $c['secret_key']])
            ->timeout(30)
            ->post($this->baseUrl() . '/v1/intention/', [
                'amount'            => $amountMinor,
                'currency'          => $currency,
                'payment_methods'   => $integrationIds,
                'items'             => [[
                    'name'        => mb_substr('Online store order ' . $orderRef, 0, 50),
                    'amount'      => $amountMinor,
                    'description' => mb_substr('Order ' . $orderRef, 0, 255),
                    'quantity'    => 1,
                ]],
                'billing_data'      => [
                    'first_name'   => $firstName,
                    'last_name'    => $lastName,
                    'email'        => ($customer['email'] ?? '') !== '' ? $customer['email'] : 'customer@example.com',
                    'phone_number' => ($customer['phone'] ?? '') !== '' ? $customer['phone'] : '+20000000000',
                ],
                'special_reference' => $reference,
                'notification_url'  => $webhookUrl,
                'redirection_url'   => $returnUrl,
            ]);

        $body = $response->json() ?? [];

        if (! $response->successful() || empty($body['client_secret'])) {
            Log::error('Paymob intention create failed', [
                'http_status' => $response->status(),
                'body'        => $body,
            ]);

            $detail = $body['detail'] ?? ($body['message'] ?? null);

            throw new RuntimeException('Paymob intention creation failed: ' . (string) ($detail ?: $response->status()));
        }

        $url = $this->baseUrl() . '/unifiedcheckout/?' . http_build_query([
            'publicKey'    => $c['public_key'],
            'clientSecret' => (string) $body['client_secret'],
        ]);

        return ['url' => $url, 'reference' => $reference];
    }

    /**
     * Verify a transaction server-side by our special_reference through the
     * transaction inquiry API. FAILS CLOSED: rejects unless the charge is
     * successful (not pending/refunded/voided) and the amount and currency
     * match the order within rounding tolerance.
     *
     * @return array{success: bool, transaction_id: ?string, status: ?string}
     */
    public function verifyByReference(string $reference, float $expectedAmount): array
    {
        $c    = $this->credentials();
        $fail = ['success' => false, 'transaction_id' => null, 'status' => null];

        $auth = Http::timeout(30)->post($this->baseUrl() . '/api/auth/tokens', [
            'api_key' => $c['api_key'],
        ])->json() ?? [];

        if (empty($auth['token'])) {
            Log::warning('Paymob auth token request failed during verification');

            return $fail;
        }

        $txn = Http::withHeaders(['Authorization' => 'Bearer ' . $auth['token']])
            ->timeout(30)
            ->post($this->baseUrl() . '/api/ecommerce/orders/transaction_inquiry', [
                'merchant_order_id' => $reference,
            ])->json() ?? [];

        return $this->checkTransaction(is_array($txn) ? $txn : [], $expectedAmount);
    }

    /**
     * Cross-check a (verified-origin) transaction object against the order.
     * Used by verifyByReference() and, after HMAC authentication, by the
     * webhook handler.
     *
     * @return array{success: bool, transaction_id: ?string, status: ?string}
     */
    public function checkTransaction(array $txn, float $expectedAmount): array
    {
        $fail = ['success' => false, 'transaction_id' => isset($txn['id']) ? (string) $txn['id'] : null, 'status' => null];

        if (empty($txn['id'])) {
            return $fail;
        }

        $success = ($txn['success'] ?? false) === true
            && ($txn['pending'] ?? false) === false
            && ($txn['is_refunded'] ?? false) === false
            && ($txn['is_voided'] ?? false) === false;

        $fail['status'] = $success ? 'PAID' : (($txn['pending'] ?? false) === true ? 'PENDING' : 'DECLINED');

        if (! $success) {
            return $fail;
        }

        $currency       = strtoupper((string) ($txn['currency'] ?? ''));
        $expectedMinor  = (int) round($expectedAmount * $this->minorUnits($this->currency()));
        $chargedMinor   = (int) ($txn['amount_cents'] ?? -1);

        if ($currency !== $this->currency() || abs($chargedMinor - $expectedMinor) > 1) {
            Log::error('Paymob verification currency/amount mismatch — order left unpaid', [
                'expected_currency' => $this->currency(),
                'charged_currency'  => $currency,
                'expected_minor'    => $expectedMinor,
                'charged_minor'     => $chargedMinor,
                'transaction_id'    => $txn['id'],
            ]);

            return $fail;
        }

        return ['success' => true, 'transaction_id' => (string) $txn['id'], 'status' => 'PAID'];
    }

    /**
     * Authenticate a transaction processed callback: HMAC-SHA512 over the
     * documented 20-field concatenation of the transaction object, keyed with
     * the account's HMAC secret. Booleans serialize as "true"/"false".
     */
    public function checkWebhookHmac(array $obj, string $signature): bool
    {
        $secret = $this->credentials()['hmac_secret'];
        if ($secret === '' || trim($signature) === '') {
            return false;
        }

        $fields = [
            $obj['amount_cents'] ?? '',
            $obj['created_at'] ?? '',
            $obj['currency'] ?? '',
            $obj['error_occured'] ?? '',
            $obj['has_parent_transaction'] ?? '',
            $obj['id'] ?? '',
            $obj['integration_id'] ?? '',
            $obj['is_3d_secure'] ?? '',
            $obj['is_auth'] ?? '',
            $obj['is_capture'] ?? '',
            $obj['is_refunded'] ?? '',
            $obj['is_standalone_payment'] ?? '',
            $obj['is_voided'] ?? '',
            $obj['order']['id'] ?? '',
            $obj['owner'] ?? '',
            $obj['pending'] ?? '',
            $obj['source_data']['pan'] ?? '',
            $obj['source_data']['sub_type'] ?? '',
            $obj['source_data']['type'] ?? '',
            $obj['success'] ?? '',
        ];

        $concatenated = implode('', array_map(static function ($value) {
            if (is_bool($value)) {
                return $value ? 'true' : 'false';
            }

            return (string) $value;
        }, $fields));

        return hash_equals(hash_hmac('sha512', $concatenated, $secret), strtolower(trim($signature)));
    }

    protected function splitName(string $name): array
    {
        $name  = trim($name) !== '' ? trim($name) : 'Customer';
        $parts = preg_split('/\s+/', $name, 2);

        return [$parts[0], $parts[1] ?? $parts[0]];
    }

    protected function baseUrl(): string
    {
        $region = strtolower($this->credentials()['region']);

        return self::REGION_HOSTS[$region] ?? self::REGION_HOSTS['egypt'];
    }

    protected function credentials(): array
    {
        if ($this->credentials !== null) {
            return $this->credentials;
        }

        $settings = PaymentSetting::current();

        return $this->credentials = [
            'region'         => trim((string) ($settings->paymob_region ?? '')),
            'public_key'     => trim((string) ($settings->paymob_public_key ?? '')),
            'secret_key'     => trim((string) ($settings->paymob_secret_key ?? '')),
            'api_key'        => trim((string) ($settings->paymob_api_key ?? '')),
            'integration_id' => trim((string) ($settings->paymob_integration_id ?? '')),
            'hmac_secret'    => trim((string) ($settings->paymob_hmac_secret ?? '')),
            'currency'       => strtoupper(trim((string) ($settings->paymob_currency ?? '')) ?: 'EGP'),
        ];
    }
}

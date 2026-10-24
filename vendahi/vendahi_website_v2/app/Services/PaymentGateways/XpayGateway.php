<?php

namespace App\Services\PaymentGateways;

use App\Models\Central\PendingRegistration;
use App\Models\Central\TenantBillingPayment;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * XPay by PostEx (Pakistan — cards, wallets, PKR).
 *
 * XPay is an embedded-element gateway (Stripe Elements shaped), not a
 * redirect one: the server creates a Payment Intent, the browser mounts the
 * XPay element and confirms with the intent's client secret, and the server
 * re-fetches the intent to learn the real outcome. To fit the platform's
 * redirect()->away() checkout flow, createCheckoutUrl() returns the URL of a
 * small internal payment page (central /xpay/pay/{ref}) that hosts the
 * element; the intent's client secret + encryption key are kept in the
 * registration/payment metadata for that page.
 *
 * API (staging xstak-pay-stg.xstak.com / live xstak-pay.xstak.com, picked
 * by test_mode): POST public/v1/payment/intent signed with x-signature =
 * hex HMAC-SHA256 of the JSON body keyed with the API HMAC secret, plus
 * x-api-key (secret key) and x-account-id; GET
 * public/v1/payment/intent/details/{id} for verification.
 *
 * Webhooks carry X-Signature = hex HMAC-SHA256 of the JSON body keyed with
 * the separate Webhook HMAC secret; the payload's status is never trusted —
 * a "succeeded" event is re-verified by re-fetching the intent.
 */
class XpayGateway implements PaymentGatewayInterface
{
    use HasGatewaySettings;

    protected const LIVE_HOST = 'https://xstak-pay.xstak.com/public/';
    protected const TEST_HOST = 'https://xstak-pay-stg.xstak.com/public/';

    protected const LIVE_SDK = 'https://js.xstak.com/xpay.js';
    protected const TEST_SDK = 'https://js.xstak.com/xpay-stage.js';

    /** Internal payment page links expire after this many seconds. */
    public const PAY_LINK_TTL = 3 * 3600;

    public function getKey(): string
    {
        return 'xpay';
    }

    protected function getRequiredCredentialKeys(): array
    {
        return ['account_id', 'publishable_key', 'secret_key', 'hmac_secret'];
    }

    public function isAvailable(): bool
    {
        return $this->isGatewayAvailable();
    }

    public function getDisplayInfo(): array
    {
        return [
            'key'         => 'xpay',
            'label'       => 'XPay',
            'description' => 'Accept cards and wallets in Pakistan (PKR) via XPay by PostEx.',
            'icon'        => 'bi-credit-card',
            'color'       => '#0f766e',
        ];
    }

    public function host(): string
    {
        return $this->isTestMode() ? self::TEST_HOST : self::LIVE_HOST;
    }

    /** Browser SDK URL for the configured mode (used by the internal payment page). */
    public function sdkUrl(): string
    {
        return $this->isTestMode() ? self::TEST_SDK : self::LIVE_SDK;
    }

    /** Public, browser-side configuration for the XPay element. */
    public function elementConfig(): array
    {
        $this->loadSettings();

        return [
            'sdk_url'         => $this->sdkUrl(),
            'publishable_key' => (string) ($this->resolvedCredentials['publishable_key'] ?? ''),
            'account_id'      => (string) ($this->resolvedCredentials['account_id'] ?? ''),
            // XPay's official SDK takes the API HMAC secret as its third
            // constructor argument (as in the official WooCommerce plugin).
            'hmac_secret'     => (string) ($this->resolvedCredentials['hmac_secret'] ?? ''),
        ];
    }

    // ──────────────────────────────────────────────
    //  HTTP helpers
    // ──────────────────────────────────────────────

    /** Canonical JSON used for request signing (matches XPay's PHP reference). */
    public static function canonicalJson($data): string
    {
        return (string) json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    protected function signedHeaders(string $body): array
    {
        $this->loadSettings();

        return [
            'x-api-key'    => (string) ($this->resolvedCredentials['secret_key'] ?? ''),
            'x-account-id' => (string) ($this->resolvedCredentials['account_id'] ?? ''),
            'x-signature'  => hash_hmac('sha256', $body, (string) ($this->resolvedCredentials['hmac_secret'] ?? '')),
            'Content-Type' => 'application/json',
            'Accept'       => 'application/json',
        ];
    }

    /**
     * Create a Payment Intent.
     *
     * @return array{intent_id: string, client_secret: string, encryption_key: string}
     */
    public function createIntent(float $amount, string $currency, array $customer, array $shipping, array $metadata): array
    {
        $this->loadSettings();

        foreach ($this->getRequiredCredentialKeys() as $key) {
            if (empty($this->resolvedCredentials[$key])) {
                throw new \RuntimeException('XPay is not fully configured (missing ' . $key . ').');
            }
        }

        $payload = [
            'amount'               => round($amount, 2),
            'currency'             => strtoupper($currency ?: 'PKR'),
            'payment_method_types' => 'card',
            'customer'             => self::filterEmpty($customer),
            'shipping'             => self::filterEmpty($shipping),
            'metadata'             => array_map(fn ($v) => (string) $v, self::filterEmpty($metadata)),
        ];
        $payload = self::filterEmpty($payload);

        $body = self::canonicalJson($payload);

        try {
            $response = Http::withHeaders($this->signedHeaders($body))
                ->withBody($body, 'application/json')
                ->timeout(30)
                ->post($this->host() . 'v1/payment/intent');
        } catch (\Throwable $e) {
            Log::error('XPay intent request failed', ['error' => $e->getMessage()]);
            throw new \RuntimeException('Could not connect to XPay: ' . $e->getMessage());
        }

        $json = $response->json() ?? [];
        $data = is_array($json['data'] ?? null) ? $json['data'] : [];

        if (! $response->successful() || empty($json['success']) || empty($data['_id']) || empty($data['pi_client_secret'])) {
            Log::error('XPay intent creation failed', [
                'http_status' => $response->status(),
                'response'    => $json,
            ]);

            throw new \RuntimeException('XPay: ' . (string) ($json['message'] ?? ('HTTP ' . $response->status())));
        }

        return [
            'intent_id'      => (string) $data['_id'],
            'client_secret'  => (string) $data['pi_client_secret'],
            'encryption_key' => (string) ($data['encryptionKey'] ?? ''),
        ];
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

    // ──────────────────────────────────────────────
    //  Checkout (internal element page)
    // ──────────────────────────────────────────────

    public function createCheckoutSession(
        TenantBillingPayment $payment,
        string $successUrl,
        string $cancelUrl
    ): string {
        $chargeAmount = $payment->gateway_amount ?? $payment->amount;

        $tenant = $payment->tenant;
        $user   = auth()->user();

        $intent = $this->createIntent(
            amount: (float) $chargeAmount,
            currency: $payment->gateway_currency ?? $payment->currency,
            customer: [
                'email' => $payment->metadata['email'] ?? $user?->email ?? $tenant?->admin_email,
                'name'  => $tenant?->company_name ?? $user?->username ?? 'Customer',
                'phone' => $tenant?->owner_phone,
            ],
            shipping: [],
            metadata: [
                'payment_id'      => $payment->id,
                'tenant_id'       => $payment->tenant_id,
                'order_reference' => 'payment-' . $payment->id,
            ],
        );

        $payment->update([
            'gateway_payment_id' => $intent['intent_id'],
            'metadata'           => array_merge($payment->metadata ?? [], [
                'xpay' => [
                    'intent_id'      => $intent['intent_id'],
                    'client_secret'  => $intent['client_secret'],
                    'encryption_key' => $intent['encryption_key'],
                    'success_url'    => $successUrl,
                    'cancel_url'     => $cancelUrl,
                ],
            ]),
        ]);

        return self::payPageUrl('pay-' . $payment->id);
    }

    /**
     * Registration checkout: create the intent, stash the element secrets on
     * the PendingRegistration and hand back the internal payment page URL.
     *
     * Returns ['url' => <internal page>, 'session_id' => <intent id>].
     */
    public function createCheckoutUrl(
        float $amount,
        string $currency,
        string $productName,
        string $description,
        array $metadata,
        string $successUrl,
        string $cancelUrl
    ): array {
        $registration = ! empty($metadata['registration_id'])
            ? PendingRegistration::find($metadata['registration_id'])
            : null;

        if (! $registration) {
            throw new \RuntimeException('XPay checkout requires a pending registration.');
        }

        $regMeta = $registration->metadata ?? [];

        $intent = $this->createIntent(
            amount: $amount,
            currency: $currency,
            customer: [
                'email' => $registration->admin_email,
                'name'  => $registration->company_name,
                'phone' => $regMeta['owner_phone'] ?? null,
            ],
            shipping: [],
            metadata: [
                'registration_id' => $registration->id,
                'order_reference' => 'registration-' . $registration->id,
            ],
        );

        $registration->update([
            'metadata' => array_merge($regMeta, [
                'xpay' => [
                    'intent_id'      => $intent['intent_id'],
                    'client_secret'  => $intent['client_secret'],
                    'encryption_key' => $intent['encryption_key'],
                    'success_url'    => $successUrl,
                    'cancel_url'     => $cancelUrl,
                ],
            ]),
        ]);

        return [
            'url'        => self::payPageUrl('reg-' . $registration->id),
            'session_id' => $intent['intent_id'],
        ];
    }

    /**
     * Signed URL of the internal payment page on the central domain. Built by
     * hand (not route()) because tenant-domain requests do not load the
     * central route file.
     */
    public static function payPageUrl(string $ref): string
    {
        $expires = time() + self::PAY_LINK_TTL;

        return rtrim((string) config('app.url'), '/') . '/xpay/pay/' . $ref
            . '?expires=' . $expires
            . '&signature=' . self::payPageSignature($ref, $expires);
    }

    public static function payPageSignature(string $ref, int $expires): string
    {
        return hash_hmac('sha256', $ref . '|' . $expires, (string) config('app.key'));
    }

    public static function payPageSignatureValid(string $ref, $expires, $signature): bool
    {
        $expires = (int) $expires;

        return $expires > time()
            && is_string($signature)
            && hash_equals(self::payPageSignature($ref, $expires), $signature);
    }

    // ──────────────────────────────────────────────
    //  Verification (intent re-fetch)
    // ──────────────────────────────────────────────

    /**
     * Re-fetch a Payment Intent and normalise its state.
     *
     * @return array{success: bool, status: string, transaction_id: ?string, amount: ?float, currency: ?string, metadata: array}
     */
    public function verifyTransaction(string $intentId): array
    {
        $fail = ['success' => false, 'status' => '', 'transaction_id' => null, 'amount' => null, 'currency' => null, 'metadata' => []];

        if ($intentId === '') {
            return $fail;
        }

        $this->loadSettings();

        try {
            $response = Http::withHeaders([
                'x-api-key'    => (string) ($this->resolvedCredentials['secret_key'] ?? ''),
                'x-account-id' => (string) ($this->resolvedCredentials['account_id'] ?? ''),
                'Accept'       => 'application/json',
            ])->timeout(30)->get($this->host() . 'v1/payment/intent/details/' . rawurlencode($intentId));
            $json = $response->json() ?? [];
        } catch (\Throwable $e) {
            Log::warning("XPay intent fetch threw for {$intentId}: {$e->getMessage()}");

            return $fail;
        }

        $data = is_array($json['data'] ?? null) ? $json['data'] : [];

        if (! $response->successful() || empty($json['success']) || $data === []) {
            $fail['status'] = (string) ($json['message'] ?? '');
            Log::warning('XPay intent fetch unsuccessful', ['intent' => $intentId, 'response' => $json]);

            return $fail;
        }

        Log::debug('XPay intent details', ['intent' => $intentId, 'data' => $data]);

        return self::normaliseIntent($data);
    }

    /** Map an intent object (from details or a webhook re-fetch) to the shared verification shape. */
    public static function normaliseIntent(array $data): array
    {
        $status = strtolower((string) ($data['status'] ?? $data['payment_status'] ?? ''));

        $transaction   = is_array($data['transaction_details'] ?? null) ? $data['transaction_details'] : [];
        $transactionId = (string) ($transaction['_id'] ?? $data['transaction_id'] ?? '');

        $metadata = is_array($data['metadata'] ?? null) ? $data['metadata'] : [];
        $currency = strtoupper((string) ($data['currency'] ?? ''));

        return [
            'success'        => in_array($status, ['succeeded', 'success', 'paid', 'captured', 'completed'], true),
            'status'         => strtoupper($status),
            'transaction_id' => $transactionId !== '' ? $transactionId : null,
            'amount'         => isset($data['amount']) && is_numeric($data['amount']) ? (float) $data['amount'] : null,
            'currency'       => $currency !== '' ? $currency : null,
            'metadata'       => $metadata,
        ];
    }

    public function verifyPaymentStatus(string $sessionId): array
    {
        $verification = $this->verifyTransaction($sessionId);

        $status = match (true) {
            $verification['success'] => 'paid',
            in_array($verification['status'], ['FAILED', 'DECLINED', 'CANCELED', 'CANCELLED', 'EXPIRED', 'VOIDED', 'ERROR'], true) => 'failed',
            default => 'pending',
        };

        return [
            'status'             => $status,
            'gateway_payment_id' => $sessionId,
            'transaction_id'     => $verification['transaction_id'],
        ];
    }

    // ──────────────────────────────────────────────
    //  Webhook verification (X-Signature + re-fetch)
    // ──────────────────────────────────────────────

    /**
     * Compare a webhook signature against the HMAC of the payload keyed with
     * the given secret. XPay's reference implementation signs the JSON
     * re-encoded from the decoded body; the raw body is accepted too.
     */
    public static function webhookSignatureValid(string $payload, string $signature, string $secret): bool
    {
        if ($secret === '' || $signature === '') {
            return false;
        }

        $signature = strtolower(trim($signature));
        $decoded   = json_decode($payload, true);

        $candidates = [hash_hmac('sha256', $payload, $secret)];
        if ($decoded !== null) {
            $candidates[] = hash_hmac('sha256', self::canonicalJson($decoded), $secret);
            $candidates[] = hash_hmac('sha256', (string) json_encode($decoded), $secret);
        }

        foreach ($candidates as $candidate) {
            if (hash_equals($candidate, $signature)) {
                return true;
            }
        }

        return false;
    }

    public function verifyWebhook(string $payload, string $signature): array
    {
        $result = [
            'valid'              => false,
            'event_type'         => '',
            'gateway_payment_id' => '',
            'transaction_id'     => '',
            'payment_id'         => null,
            'tenant_id'          => null,
            'registration_id'    => null,
            'status'             => 'unknown',
        ];

        $secret = $this->getCredential('webhook_hmac_secret');
        if ($secret === '') {
            Log::error('XPay webhook rejected: no webhook HMAC secret configured');

            return $result;
        }

        if (! self::webhookSignatureValid($payload, $signature, $secret)) {
            Log::error('XPay webhook rejected: signature mismatch');

            return $result;
        }

        $body = json_decode($payload, true);
        if (! is_array($body)) {
            return $result;
        }

        $intentId = (string) ($body['payment_intent_id'] ?? $body['payment_intent'] ?? $body['intent_id'] ?? '');
        $event    = strtolower((string) ($body['status'] ?? $body['event'] ?? $body['type'] ?? 'event'));

        $transaction = is_array($body['transaction_details'] ?? null) ? $body['transaction_details'] : [];

        $result['valid']              = true;
        $result['event_type']         = 'xpay.' . $event;
        $result['gateway_payment_id'] = $intentId;
        $result['transaction_id']     = (string) ($transaction['_id'] ?? '');

        if ($intentId === '') {
            return $result;
        }

        // Never act on the payload — re-fetch the intent for the real status
        // and our own metadata (payment_id / registration_id context).
        $verification = $this->verifyTransaction($intentId);

        $meta = $verification['metadata'];
        $result['payment_id']      = ! empty($meta['payment_id']) ? (int) $meta['payment_id'] : null;
        $result['registration_id'] = ! empty($meta['registration_id']) ? (int) $meta['registration_id'] : null;
        $result['tenant_id']       = ! empty($meta['tenant_id']) ? (string) $meta['tenant_id'] : null;

        if ($verification['success']) {
            $result['status']         = 'paid';
            $result['transaction_id'] = (string) ($verification['transaction_id'] ?? $result['transaction_id']);
            $result['amount']         = $verification['amount'];
            $result['currency']       = $verification['currency'];
        } elseif (in_array($verification['status'], ['FAILED', 'DECLINED', 'CANCELED', 'CANCELLED', 'EXPIRED', 'VOIDED', 'ERROR'], true)
            || in_array($event, ['failed', 'declined', 'cancelled', 'canceled'], true)) {
            $result['status'] = 'failed';
        }

        return $result;
    }
}

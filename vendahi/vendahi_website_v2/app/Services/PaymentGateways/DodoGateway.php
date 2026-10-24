<?php

namespace App\Services\PaymentGateways;

use App\Models\Central\TenantBillingPayment;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Dodo Payments gateway (global merchant of record — cards, wallets, UPI).
 *
 * Uses the platform's own Dodo Payments account for subscription billing.
 * Checkout is a hosted Checkout Session (POST /checkouts → checkout_url),
 * which fits the redirect()->away() flow used by all other gateways.
 *
 * Dodo is product-based, so dynamic amounts require a one-time
 * "Pay What You Want" product created in the Dodo dashboard; its product id
 * is part of the gateway credentials and the real charge amount is passed as
 * the per-item `amount` override (in the currency's smallest unit).
 *
 * Webhooks follow the Standard Webhooks spec: HMAC-SHA256 over
 * "{webhook-id}.{webhook-timestamp}.{raw body}" keyed with the base64 part
 * of the whsec_ secret, delivered in the webhook-signature header.
 * Test vs live host is controlled by the gateway's test_mode setting.
 */
class DodoGateway implements PaymentGatewayInterface
{
    use HasGatewaySettings;

    protected const LIVE_HOST = 'https://live.dodopayments.com';
    protected const TEST_HOST = 'https://test.dodopayments.com';

    /** Reject webhooks whose timestamp drifts more than this (replay guard). */
    protected const TIMESTAMP_TOLERANCE_SECONDS = 300;

    public function getKey(): string
    {
        return 'dodo';
    }

    protected function getRequiredCredentialKeys(): array
    {
        return ['api_key', 'product_id'];
    }

    public function isAvailable(): bool
    {
        return $this->isGatewayAvailable();
    }

    public function getDisplayInfo(): array
    {
        return [
            'key'         => 'dodo',
            'label'       => 'Dodo Payments',
            'description' => 'Global payments via cards, wallets, and UPI (merchant of record).',
            'icon'        => 'bi-credit-card-2-front',
            'color'       => '#111827',
        ];
    }

    protected function host(): string
    {
        return $this->isTestMode() ? self::TEST_HOST : self::LIVE_HOST;
    }

    protected function request(): \Illuminate\Http\Client\PendingRequest
    {
        $this->loadSettings();

        $apiKey = trim($this->resolvedCredentials['api_key'] ?? '');

        if ($apiKey === '') {
            throw new \RuntimeException('Dodo Payments API key is not configured.');
        }

        return Http::withToken($apiKey)->acceptJson()->timeout(30);
    }

    // ──────────────────────────────────────────────
    //  Hosted checkout (Checkout Sessions)
    // ──────────────────────────────────────────────

    public function createCheckoutSession(
        TenantBillingPayment $payment,
        string $successUrl,
        string $cancelUrl
    ): string {
        $chargeAmount = $payment->gateway_amount ?? $payment->amount;

        $email = $payment->metadata['email']
            ?? auth()->user()?->email
            ?? null;

        $result = $this->createCheckoutUrl(
            amount: (float) $chargeAmount,
            currency: $payment->gateway_currency ?? $payment->currency,
            productName: ($payment->plan->name ?? 'Subscription') . ' Plan',
            description: ucfirst($payment->billing_cycle) . ' subscription',
            metadata: [
                'payment_id' => $payment->id,
                'tenant_id'  => $payment->tenant_id,
                'email'      => $email,
            ],
            successUrl: $successUrl,
            cancelUrl: $cancelUrl,
        );

        $payment->update([
            'gateway_payment_id' => $result['session_id'],
            'metadata' => array_merge($payment->metadata ?? [], [
                'dodo_session_id' => $result['session_id'],
            ]),
        ]);

        return $result['url'];
    }

    /**
     * Create a Dodo Payments checkout session.
     *
     * Returns ['url' => <checkout_url>, 'session_id' => <session_id>].
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
        $this->loadSettings();

        $productId = trim($this->resolvedCredentials['product_id'] ?? '');
        if ($productId === '') {
            throw new \RuntimeException('Dodo Payments product id is not configured. Create a one-time "Pay What You Want" product in the Dodo dashboard and enter its product id in the gateway settings.');
        }

        // Resolve customer email + name (registration flow has no auth user).
        $customerEmail = $metadata['email'] ?? null;
        $customerName  = $metadata['customer_name'] ?? null;

        if (! empty($metadata['registration_id'])) {
            $reg = \App\Models\Central\PendingRegistration::find($metadata['registration_id']);
            if ($reg) {
                $customerEmail = $customerEmail ?: $reg->admin_email;
                $customerName  = $customerName  ?: $reg->company_name;
            }
        }

        if (empty($customerEmail)) {
            $customerEmail = auth()->user()?->email;
        }
        if (empty($customerName)) {
            $customerName = auth()->user()?->username ?? 'Customer';
        }

        $meta = array_filter([
            'payment_id'      => $metadata['payment_id'] ?? null,
            'tenant_id'       => $metadata['tenant_id'] ?? null,
            'registration_id' => $metadata['registration_id'] ?? null,
        ], fn ($v) => $v !== null);

        $payload = [
            'product_cart' => [[
                'product_id' => $productId,
                'quantity'   => 1,
                // Smallest currency unit — the PWYW override for the real charge.
                'amount'     => (int) round($amount * 100),
            ]],
            'return_url' => $successUrl,
            'metadata'   => array_map(fn ($v) => (string) $v, $meta) ?: (object) [],
        ];

        if (! empty($customerEmail)) {
            $payload['customer'] = [
                'email' => $customerEmail,
                'name'  => (string) $customerName,
            ];
        }

        try {
            $response = $this->request()->post($this->host() . '/checkouts', $payload);
        } catch (\RuntimeException $e) {
            throw $e;
        } catch (\Throwable $e) {
            Log::error('Dodo Payments checkout request failed', ['error' => $e->getMessage()]);
            throw new \RuntimeException('Could not connect to Dodo Payments: ' . $e->getMessage());
        }

        $body = $response->json() ?? [];

        if (! $response->successful() || empty($body['session_id']) || empty($body['checkout_url'])) {
            $message = $body['message'] ?? $body['error'] ?? ('HTTP ' . $response->status());

            Log::error('Dodo Payments checkout creation failed', [
                'http_status' => $response->status(),
                'response'    => $body,
            ]);

            throw new \RuntimeException('Dodo Payments: ' . (is_string($message) ? $message : json_encode($message)));
        }

        return [
            'url'        => (string) $body['checkout_url'],
            'session_id' => (string) $body['session_id'],
        ];
    }

    // ──────────────────────────────────────────────
    //  Transaction verification (checkout session lookup)
    // ──────────────────────────────────────────────

    /**
     * Verify a checkout session's payment status directly with Dodo.
     *
     * @return array{status: string, gateway_payment_id: ?string, transaction_id: ?string}
     */
    public function verifyPaymentStatus(string $sessionId): array
    {
        $result = ['status' => 'unknown', 'gateway_payment_id' => $sessionId, 'transaction_id' => null];

        try {
            $response = $this->request()->get($this->host() . '/checkouts/' . $sessionId);

            if ($response->successful()) {
                $data = $response->json() ?? [];

                $result['transaction_id'] = (string) ($data['payment_id'] ?? '');

                $status = strtolower((string) ($data['payment_status'] ?? ''));
                if ($status === 'succeeded') {
                    $result['status'] = 'paid';
                } elseif (in_array($status, ['failed', 'cancelled'], true)) {
                    $result['status'] = 'failed';
                } else {
                    // Still collecting details / processing.
                    $result['status'] = 'pending';
                }
            }
        } catch (\Throwable $e) {
            Log::error("Dodo Payments verifyPaymentStatus failed: {$e->getMessage()}");
        }

        return $result;
    }

    // ──────────────────────────────────────────────
    //  Webhook verification (Standard Webhooks spec)
    // ──────────────────────────────────────────────

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

        $this->loadSettings();
        $webhookSecret = trim($this->resolvedCredentials['webhook_secret'] ?? '');

        // The Standard Webhooks signed content also needs the id + timestamp
        // headers, which the shared controller doesn't pass through.
        $webhookId        = (string) request()->header('webhook-id', '');
        $webhookTimestamp = (string) request()->header('webhook-timestamp', '');

        if (! self::verifyStandardWebhookSignature($payload, $signature, $webhookId, $webhookTimestamp, $webhookSecret)) {
            Log::error('Dodo Payments webhook signature verification failed');
            return $result;
        }

        $body = json_decode($payload, true);
        if (! $body) {
            return $result;
        }

        $result['valid']      = true;
        $result['event_type'] = (string) ($body['type'] ?? '');

        $data = (array) ($body['data'] ?? []);
        $meta = (array) ($data['metadata'] ?? []);

        $eventType = $result['event_type'];

        if ($eventType === 'payment.succeeded') {
            $result['gateway_payment_id'] = (string) ($data['checkout_session_id'] ?? '');
            $result['transaction_id']     = (string) ($data['payment_id'] ?? '');
            $result['payment_id']         = $meta['payment_id'] ?? null;
            $result['tenant_id']          = $meta['tenant_id'] ?? null;
            $result['registration_id']    = $meta['registration_id'] ?? null;
            $result['status']             = 'paid';
            // total_amount is tax-inclusive, in the smallest currency unit.
            $result['amount']   = isset($data['total_amount']) ? ((int) $data['total_amount']) / 100 : null;
            $result['currency'] = $data['currency'] ?? null;
        }

        if ($eventType === 'payment.failed') {
            $result['gateway_payment_id'] = (string) ($data['checkout_session_id'] ?? '');
            $result['transaction_id']     = (string) ($data['payment_id'] ?? '');
            $result['payment_id']         = $meta['payment_id'] ?? null;
            $result['tenant_id']          = $meta['tenant_id'] ?? null;
            $result['registration_id']    = $meta['registration_id'] ?? null;
            $result['status']             = 'failed';
        }

        if ($eventType === 'refund.succeeded' && ! empty($data['payment_id'])) {
            $result['gateway_payment_id'] = (string) ($data['refund_id'] ?? '');
            $result['transaction_id']     = (string) $data['payment_id'];
            $result['status']             = 'refunded';
        }

        return $result;
    }

    /**
     * Standard Webhooks verification: base64 HMAC-SHA256 over
     * "{id}.{timestamp}.{payload}" keyed with the decoded whsec_ secret.
     * The signature header may hold several space-separated "v1,<sig>" entries.
     * Shared with the tenant-level service. Fails closed.
     */
    public static function verifyStandardWebhookSignature(
        string $payload,
        string $signatureHeader,
        string $webhookId,
        string $webhookTimestamp,
        string $secret
    ): bool {
        if ($secret === '' || $signatureHeader === '' || $webhookId === '' || $webhookTimestamp === '') {
            return false;
        }

        // Replay guard.
        if (! ctype_digit($webhookTimestamp)
            || abs(time() - (int) $webhookTimestamp) > self::TIMESTAMP_TOLERANCE_SECONDS) {
            return false;
        }

        $key = $secret;
        if (str_starts_with($key, 'whsec_')) {
            $key = substr($key, 6);
        }
        $decoded = base64_decode($key, true);
        if ($decoded !== false && $decoded !== '') {
            $key = $decoded;
        }

        $expected = base64_encode(hash_hmac(
            'sha256',
            $webhookId . '.' . $webhookTimestamp . '.' . $payload,
            $key,
            true
        ));

        foreach (explode(' ', trim($signatureHeader)) as $entry) {
            $parts = explode(',', $entry, 2);
            $candidate = $parts[1] ?? $parts[0];
            if ($candidate !== '' && hash_equals($expected, $candidate)) {
                return true;
            }
        }

        return false;
    }
}

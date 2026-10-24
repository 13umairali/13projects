<?php

namespace App\Services\PaymentGateways;

use App\Models\Central\TenantBillingPayment;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Moyasar gateway (Saudi Arabia — mada, credit cards, Apple Pay, STC Pay).
 *
 * Uses the Invoice API: a server-side invoice create (HTTP Basic auth with
 * the secret key) returns a hosted payment page URL to redirect the payer to.
 * The invoice id is stored as the checkout reference and re-queried for
 * verification (GET /v1/invoices/{id}) — Moyasar has no test/live host split;
 * sandbox is selected by using an sk_test_ key.
 *
 * Dashboard webhooks carry a shared secret_token inside the JSON body (no
 * signature header). The token is compared against the configured webhook
 * secret when one is set, and a paid event is always re-verified by
 * re-fetching the invoice — the paid state never comes from the payload.
 * Payment context (payment_id / registration_id) travels in the invoice
 * metadata, read back from the re-fetched invoice.
 */
class MoyasarGateway implements PaymentGatewayInterface
{
    use HasGatewaySettings;

    protected const API_BASE = 'https://api.moyasar.com';

    public function getKey(): string
    {
        return 'moyasar';
    }

    protected function getRequiredCredentialKeys(): array
    {
        return ['secret_key'];
    }

    public function isAvailable(): bool
    {
        return $this->isGatewayAvailable();
    }

    public function getDisplayInfo(): array
    {
        return [
            'key'         => 'moyasar',
            'label'       => 'Moyasar',
            'description' => 'Accept mada, credit cards, Apple Pay and STC Pay in Saudi Arabia (SAR).',
            'icon'        => 'bi-credit-card',
            'color'       => '#20b2aa',
        ];
    }

    protected function api()
    {
        $this->loadSettings();

        return Http::withBasicAuth($this->resolvedCredentials['secret_key'] ?? '', '')
            ->timeout(30);
    }

    /** Minor-unit multiplier (Moyasar amounts are integers in the smallest unit). */
    protected function minorUnits(string $currency): int
    {
        return match (strtoupper($currency)) {
            'KWD', 'BHD', 'OMR' => 1000,
            'JPY'               => 1,
            default             => 100,
        };
    }

    // ──────────────────────────────────────────────
    //  Hosted checkout (Invoice API)
    // ──────────────────────────────────────────────

    public function createCheckoutSession(
        TenantBillingPayment $payment,
        string $successUrl,
        string $cancelUrl
    ): string {
        $chargeAmount = $payment->gateway_amount ?? $payment->amount;

        $result = $this->createCheckoutUrl(
            amount: (float) $chargeAmount,
            currency: $payment->gateway_currency ?? $payment->currency,
            productName: ($payment->plan->name ?? 'Subscription') . ' Plan',
            description: ucfirst($payment->billing_cycle) . ' subscription',
            metadata: [
                'payment_id' => $payment->id,
                'tenant_id'  => $payment->tenant_id,
            ],
            successUrl: $successUrl,
            cancelUrl: $cancelUrl,
        );

        $payment->update(['gateway_payment_id' => $result['session_id']]);

        return $result['url'];
    }

    /**
     * Create a hosted invoice.
     *
     * Returns ['url' => <hosted invoice URL>, 'session_id' => <invoice id>].
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
        $currency = strtoupper($currency ?: 'SAR');

        $response = $this->api()->post(self::API_BASE . '/v1/invoices', [
            'amount'       => (int) round($amount * $this->minorUnits($currency)),
            'currency'     => $currency,
            'description'  => mb_substr($productName . ' — ' . $description, 0, 255),
            // Where the payer's browser lands after paying.
            'callback_url' => $successUrl,
            'back_url'     => $cancelUrl,
            'metadata'     => [
                'payment_id'      => (string) ($metadata['payment_id'] ?? ''),
                'registration_id' => (string) ($metadata['registration_id'] ?? ''),
                'tenant_id'       => (string) ($metadata['tenant_id'] ?? ''),
            ],
        ]);

        $body = $response->json() ?? [];

        if (! $response->successful() || empty($body['id']) || empty($body['url'])) {
            Log::error('Moyasar invoice create failed', [
                'http_status' => $response->status(),
                'message'     => $body['message'] ?? null,
                'errors'      => $body['errors'] ?? null,
            ]);

            throw new \RuntimeException('Moyasar: ' . (string) ($body['message'] ?? ('HTTP ' . $response->status())));
        }

        return [
            'url'        => (string) $body['url'],
            'session_id' => (string) $body['id'],
        ];
    }

    // ──────────────────────────────────────────────
    //  Transaction verification (invoice re-fetch)
    // ──────────────────────────────────────────────

    /**
     * Verify an invoice server-side by its id.
     *
     * @return array{success: bool, status: string, transaction_id: ?string, amount: ?float, currency: ?string, metadata: array}
     */
    public function verifyTransaction(string $invoiceId): array
    {
        $fail = ['success' => false, 'status' => '', 'transaction_id' => null, 'amount' => null, 'currency' => null, 'metadata' => []];

        try {
            $response = $this->api()->get(self::API_BASE . '/v1/invoices/' . urlencode($invoiceId));
            $body     = $response->json() ?? [];
        } catch (\Throwable $e) {
            Log::warning("Moyasar invoice fetch threw for {$invoiceId}: {$e->getMessage()}");

            return $fail;
        }

        if (! $response->successful() || empty($body['id'])) {
            $fail['status'] = (string) ($body['message'] ?? '');

            return $fail;
        }

        $status = strtolower((string) ($body['status'] ?? ''));

        // The paying transaction's id lives in the invoice's payments list.
        $transactionId = null;
        foreach ((array) ($body['payments'] ?? []) as $paymentRow) {
            if (is_array($paymentRow) && strtolower((string) ($paymentRow['status'] ?? '')) === 'paid') {
                $transactionId = (string) ($paymentRow['id'] ?? '');
                break;
            }
        }

        $currency = strtoupper((string) ($body['currency'] ?? ''));

        return [
            'success'        => $status === 'paid',
            'status'         => strtoupper($status),
            'transaction_id' => $transactionId,
            'amount'         => isset($body['amount'])
                ? ((int) $body['amount']) / $this->minorUnits($currency ?: 'SAR')
                : null,
            'currency'       => $currency ?: null,
            'metadata'       => is_array($body['metadata'] ?? null) ? $body['metadata'] : [],
        ];
    }

    public function verifyPaymentStatus(string $sessionId): array
    {
        $verification = $this->verifyTransaction($sessionId);

        $status = match (true) {
            $verification['success'] => 'paid',
            in_array($verification['status'], ['FAILED', 'CANCELED', 'CANCELLED', 'EXPIRED', 'VOIDED'], true) => 'failed',
            default => 'pending',
        };

        return [
            'status'             => $status,
            'gateway_payment_id' => $sessionId,
            'transaction_id'     => $verification['transaction_id'],
        ];
    }

    // ──────────────────────────────────────────────
    //  Webhook verification (secret_token + re-fetch)
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

        $body = json_decode($payload, true);
        if (! is_array($body) || empty($body['type']) || ! is_array($body['data'] ?? null)) {
            return $result;
        }

        // Authentication gate: the shared secret_token inside the body
        // (Moyasar sends no signature header). Enforced when configured.
        $secret = $this->getCredential('webhook_secret');
        if ($secret !== '' && ! hash_equals($secret, (string) ($body['secret_token'] ?? ''))) {
            Log::error('Moyasar webhook rejected: secret_token mismatch');

            return $result;
        }

        $data      = $body['data'];
        $invoiceId = (string) ($data['invoice_id'] ?? '');
        $eventType = strtolower((string) $body['type']);

        $result['valid']              = true;
        $result['event_type']         = 'moyasar.' . $eventType;
        $result['gateway_payment_id'] = $invoiceId !== '' ? $invoiceId : (string) ($data['id'] ?? '');
        $result['transaction_id']     = (string) ($data['id'] ?? '');

        if ($invoiceId === '') {
            // Standalone payment events (no invoice) carry no checkout
            // reference of ours — nothing to resolve.
            return $result;
        }

        // Never act on the payload: re-fetch the invoice for the real status
        // and our metadata (payment_id / registration_id context).
        $verification = $this->verifyTransaction($invoiceId);

        $meta = $verification['metadata'];
        $result['payment_id']      = ! empty($meta['payment_id']) ? (int) $meta['payment_id'] : null;
        $result['registration_id'] = ! empty($meta['registration_id']) ? (int) $meta['registration_id'] : null;
        $result['tenant_id']       = ! empty($meta['tenant_id']) ? (string) $meta['tenant_id'] : null;

        if ($eventType === 'payment_refunded') {
            // Refund revokes access downstream — confirm against the payment
            // object itself, never the payload.
            if ($this->paymentIsRefunded((string) ($data['id'] ?? ''))) {
                $result['status'] = 'refunded';
            }
        } elseif ($verification['success']) {
            $result['status']         = 'paid';
            $result['transaction_id'] = (string) ($verification['transaction_id'] ?? $result['transaction_id']);
            $result['amount']         = $verification['amount'];
            $result['currency']       = $verification['currency'];
        } elseif (in_array($eventType, ['payment_failed', 'payment_voided', 'payment_expired'], true)
            || in_array($verification['status'], ['FAILED', 'CANCELED', 'CANCELLED', 'EXPIRED', 'VOIDED'], true)) {
            $result['status'] = 'failed';
        }

        return $result;
    }

    protected function paymentIsRefunded(string $paymentId): bool
    {
        if ($paymentId === '') {
            return false;
        }

        try {
            $body = $this->api()->get(self::API_BASE . '/v1/payments/' . urlencode($paymentId))->json() ?? [];
        } catch (\Throwable $e) {
            Log::warning("Moyasar payment fetch threw for {$paymentId}: {$e->getMessage()}");

            return false;
        }

        return strtolower((string) ($body['status'] ?? '')) === 'refunded';
    }
}

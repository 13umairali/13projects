<?php

namespace App\Services\PaymentGateways;

use App\Models\Central\TenantBillingPayment;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * bKash gateway (Bangladesh — bKash mobile wallet, BDT only).
 *
 * Uses the platform's own bKash Tokenized Checkout (URL-based "0011" mode)
 * merchant account for subscription billing: a server-side /create call
 * returns a hosted bkashURL to redirect the customer to, which fits the
 * redirect()->away() flow. bKash appends ?paymentID&status=success|failure|
 * cancel to the callback URL we pass as successUrl.
 *
 * bKash has NO webhooks for this flow — confirmation happens on the return
 * leg: verifyTransaction() EXECUTES the approved payment server-side (the
 * step that actually moves the money), falling back to the payment-status
 * query when execute already ran or timed out after debiting (documented
 * bKash behavior). The redirect status param alone is never trusted.
 * Sandbox vs production host is controlled by the gateway's test_mode setting.
 */
class BkashGateway implements PaymentGatewayInterface
{
    use HasGatewaySettings;

    protected const LIVE_BASE    = 'https://tokenized.pay.bka.sh/v1.2.0-beta/tokenized/checkout';
    protected const SANDBOX_BASE = 'https://tokenized.sandbox.bka.sh/v1.2.0-beta/tokenized/checkout';

    public function getKey(): string
    {
        return 'bkash';
    }

    protected function getRequiredCredentialKeys(): array
    {
        return ['app_key', 'app_secret', 'username', 'password'];
    }

    public function isAvailable(): bool
    {
        return $this->isGatewayAvailable();
    }

    public function getDisplayInfo(): array
    {
        return [
            'key'         => 'bkash',
            'label'       => 'bKash',
            'description' => 'Accept bKash mobile wallet payments in Bangladesh (BDT).',
            'icon'        => 'bi-phone',
            'color'       => '#e2136e',
        ];
    }

    protected function baseUrl(): string
    {
        return $this->isTestMode() ? self::SANDBOX_BASE : self::LIVE_BASE;
    }

    // ──────────────────────────────────────────────
    //  Hosted checkout
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
     * Create a tokenized-checkout payment.
     *
     * Returns ['url' => <bkashURL>, 'session_id' => <paymentID>]. The
     * paymentID is what verifyTransaction() executes/queries by later.
     * bKash accepts a single callback URL (success/failure/cancel are
     * distinguished by the ?status param it appends), so cancelUrl is unused.
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

        // No metadata support — keep our context in the invoice number for
        // traceability (confirmation is by paymentID, not by this reference).
        $reference = $this->buildReference($metadata);

        $response = $this->api()->post($this->baseUrl() . '/create', [
            'mode'                  => '0011',
            'payerReference'        => $reference,
            'callbackURL'           => $successUrl,
            'amount'                => number_format($amount, 2, '.', ''),
            'currency'              => 'BDT',
            'intent'                => 'sale',
            'merchantInvoiceNumber' => $reference,
        ]);

        $body = $response->json() ?? [];

        if (! $response->successful() || ($body['statusCode'] ?? '') !== '0000' || empty($body['bkashURL'])) {
            Log::error('bKash payment create failed', [
                'http_status' => $response->status(),
                'response'    => $body,
            ]);

            throw new \RuntimeException('bKash: ' . (string) ($body['statusMessage'] ?? ('HTTP ' . $response->status())));
        }

        return [
            'url'        => (string) $body['bkashURL'],
            'session_id' => (string) $body['paymentID'],
        ];
    }

    protected function buildReference(array $metadata): string
    {
        $random = bin2hex(random_bytes(6));

        if (! empty($metadata['payment_id'])) {
            return 'bks-p' . $metadata['payment_id'] . '-' . $random;
        }

        if (! empty($metadata['registration_id'])) {
            return 'bks-r' . $metadata['registration_id'] . '-' . $random;
        }

        return 'bks-x-' . $random;
    }

    // ──────────────────────────────────────────────
    //  Transaction verification (the confirmation channel)
    // ──────────────────────────────────────────────

    /**
     * Execute-then-query an approved payment. Execute is the step that moves
     * the money; when it reports non-success (already executed on a retried
     * return, or bKash's documented timeout-after-debit) the payment-status
     * query is the authoritative state.
     *
     * @return array{success: bool, status: string, transaction_id: ?string, amount: ?float, currency: ?string}
     */
    public function verifyTransaction(string $paymentId): array
    {
        try {
            $body = $this->api()->post($this->baseUrl() . '/execute', ['paymentID' => $paymentId])->json() ?? [];
        } catch (\Throwable $e) {
            Log::warning("bKash execute threw for {$paymentId}: {$e->getMessage()}");
            $body = [];
        }

        if (($body['statusCode'] ?? '') !== '0000' || ($body['transactionStatus'] ?? '') !== 'Completed') {
            try {
                $body = $this->api()->post($this->baseUrl() . '/payment/status', ['paymentID' => $paymentId])->json() ?? [];
            } catch (\Throwable $e) {
                Log::warning("bKash status query threw for {$paymentId}: {$e->getMessage()}");
                $body = [];
            }
        }

        $status = (string) ($body['transactionStatus'] ?? '');

        return [
            'success'        => ($body['statusCode'] ?? '') === '0000' && $status === 'Completed',
            'status'         => $status,
            'transaction_id' => isset($body['trxID']) ? (string) $body['trxID'] : null,
            'amount'         => isset($body['amount']) ? (float) $body['amount'] : null,
            'currency'       => isset($body['currency']) ? (string) $body['currency'] : 'BDT',
        ];
    }

    public function verifyPaymentStatus(string $sessionId): array
    {
        $verification = $this->verifyTransaction($sessionId);

        $status = match (true) {
            $verification['success'] => 'paid',
            in_array($verification['status'], ['Cancelled', 'Failed', 'Expired'], true) => 'failed',
            default => 'pending',
        };

        return [
            'status'             => $status,
            'gateway_payment_id' => $sessionId,
            'transaction_id'     => $verification['transaction_id'],
        ];
    }

    // ──────────────────────────────────────────────
    //  Webhooks — bKash has none for this flow
    // ──────────────────────────────────────────────

    public function verifyWebhook(string $payload, string $signature): array
    {
        // Tokenized Checkout sends no server-to-server events; confirmation is
        // the return-leg execute/query. Reject anything posted here.
        return [
            'valid'              => false,
            'event_type'         => '',
            'gateway_payment_id' => '',
            'transaction_id'     => '',
            'payment_id'         => null,
            'tenant_id'          => null,
            'registration_id'    => null,
            'status'             => 'unknown',
        ];
    }

    // ──────────────────────────────────────────────
    //  Auth
    // ──────────────────────────────────────────────

    /** Pending request with the tokenized-checkout auth headers applied. */
    protected function api(): \Illuminate\Http\Client\PendingRequest
    {
        return Http::withHeaders([
            'Authorization' => $this->grantToken(),
            'X-APP-Key'     => $this->resolvedCredentials['app_key'] ?? '',
        ])->acceptJson()->timeout(30);
    }

    /**
     * Grant token (id_token, ~1h), cached until shortly before bKash expires
     * it — keyed by app key + endpoint so swapping credentials or sandbox
     * mode never reuses a stale token.
     */
    protected function grantToken(): string
    {
        $this->loadSettings();

        $appKey   = $this->resolvedCredentials['app_key'] ?? '';
        $cacheKey = 'platform_bkash_token_' . md5($appKey . '|' . $this->baseUrl());

        return Cache::remember($cacheKey, now()->addMinutes(50), function () use ($appKey) {
            $response = Http::withHeaders([
                'username' => $this->resolvedCredentials['username'] ?? '',
                'password' => $this->resolvedCredentials['password'] ?? '',
            ])->acceptJson()->timeout(30)->post($this->baseUrl() . '/token/grant', [
                'app_key'    => $appKey,
                'app_secret' => $this->resolvedCredentials['app_secret'] ?? '',
            ]);

            $body = $response->json() ?? [];

            if (! $response->successful() || empty($body['id_token'])) {
                Log::error('bKash token grant failed', ['response' => $body]);
                throw new \RuntimeException('Failed to obtain bKash grant token.');
            }

            return (string) $body['id_token'];
        });
    }
}

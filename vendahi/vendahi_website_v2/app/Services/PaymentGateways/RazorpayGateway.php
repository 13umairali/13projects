<?php

namespace App\Services\PaymentGateways;

use App\Models\Central\TenantBillingPayment;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Razorpay gateway (Payment Links API).
 *
 * Uses the platform's own Razorpay account for subscription billing.
 * Checkout is a hosted Payment Link (POST /v1/payment_links → short_url),
 * which fits the redirect()->away() flow used by all other gateways.
 * Amounts are sent in the smallest currency unit (paise for INR).
 *
 * Webhooks are verified via HMAC-SHA256 of the raw body against the
 * configured Webhook Secret (X-Razorpay-Signature header).
 */
class RazorpayGateway implements PaymentGatewayInterface
{
    use HasGatewaySettings;

    protected const API_BASE = 'https://api.razorpay.com/v1';

    public function getKey(): string
    {
        return 'razorpay';
    }

    protected function getRequiredCredentialKeys(): array
    {
        return ['key_id', 'key_secret'];
    }

    public function isAvailable(): bool
    {
        return $this->isGatewayAvailable();
    }

    public function getDisplayInfo(): array
    {
        return [
            'key'         => 'razorpay',
            'label'       => 'Razorpay',
            'description' => 'Accept payments via UPI, cards, netbanking, and wallets.',
            'icon'        => 'bi-currency-rupee',
            'color'       => '#0f4bd8',
        ];
    }

    /**
     * Authenticated HTTP client (Basic auth: key_id / key_secret).
     */
    protected function request(): \Illuminate\Http\Client\PendingRequest
    {
        $this->loadSettings();

        $keyId     = trim($this->resolvedCredentials['key_id'] ?? '');
        $keySecret = trim($this->resolvedCredentials['key_secret'] ?? '');

        if ($keyId === '' || $keySecret === '') {
            throw new \RuntimeException('Razorpay keys are not configured.');
        }

        return Http::withBasicAuth($keyId, $keySecret)->acceptJson()->timeout(30);
    }

    // ──────────────────────────────────────────────
    //  Hosted checkout (Payment Links)
    // ──────────────────────────────────────────────

    public function createCheckoutSession(
        TenantBillingPayment $payment,
        string $successUrl,
        string $cancelUrl
    ): string {
        $chargeAmount   = $payment->gateway_amount ?? $payment->amount;
        $chargeCurrency = $payment->gateway_currency ?? $payment->currency;

        $email = $payment->metadata['email']
            ?? auth()->user()?->email
            ?? null;

        $result = $this->createCheckoutUrl(
            amount: (float) $chargeAmount,
            currency: $chargeCurrency,
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
                'razorpay_payment_link_id' => $result['session_id'],
            ]),
        ]);

        return $result['url'];
    }

    /**
     * Create a Razorpay Payment Link.
     *
     * Returns ['url' => <short_url>, 'session_id' => <plink_id>].
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
        // Resolve customer email + name. The registration flow has no auth
        // user, so look up the PendingRegistration when registration_id is set.
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

        $notes = array_filter([
            'payment_id'      => $metadata['payment_id'] ?? null,
            'tenant_id'       => $metadata['tenant_id'] ?? null,
            'registration_id' => $metadata['registration_id'] ?? null,
        ], fn ($v) => $v !== null);

        // Razorpay notes values must be strings.
        $notes = array_map(fn ($v) => (string) $v, $notes);

        $payload = [
            'amount'          => (int) round($amount * 100), // smallest unit (paise)
            'currency'        => strtoupper($currency),
            'accept_partial'  => false,
            'reference_id'    => 'rzp' . bin2hex(random_bytes(12)),
            'description'     => $productName . ' — ' . $description,
            'customer'        => array_filter([
                'name'  => $customerName,
                'email' => $customerEmail,
            ]),
            'notify'          => ['sms' => false, 'email' => false],
            'callback_url'    => $successUrl,
            'callback_method' => 'get',
            'notes'           => $notes ?: (object) [],
        ];

        try {
            $response = $this->request()->post(self::API_BASE . '/payment_links', $payload);
        } catch (\RuntimeException $e) {
            throw $e;
        } catch (\Throwable $e) {
            Log::error('Razorpay payment link request failed', ['error' => $e->getMessage()]);
            throw new \RuntimeException('Could not connect to Razorpay: ' . $e->getMessage());
        }

        $body = $response->json() ?? [];

        if (! $response->successful() || empty($body['short_url']) || empty($body['id'])) {
            $message = $body['error']['description'] ?? ('HTTP ' . $response->status());

            Log::error('Razorpay payment link creation failed', [
                'http_status' => $response->status(),
                'currency'    => $payload['currency'],
                'response'    => $body,
            ]);

            throw new \RuntimeException('Razorpay: ' . $message);
        }

        return [
            'url'        => (string) $body['short_url'],
            'session_id' => (string) $body['id'],
        ];
    }

    // ──────────────────────────────────────────────
    //  Transaction verification
    // ──────────────────────────────────────────────

    /**
     * Verify a Payment Link's status directly with Razorpay.
     *
     * @return array{status: string, gateway_payment_id: ?string, transaction_id: ?string}
     */
    public function verifyPaymentStatus(string $sessionId): array
    {
        $result = ['status' => 'unknown', 'gateway_payment_id' => null, 'transaction_id' => null];

        try {
            $response = $this->request()->get(self::API_BASE . '/payment_links/' . $sessionId);

            if ($response->successful()) {
                $data = $response->json() ?? [];

                $result['gateway_payment_id'] = (string) ($data['id'] ?? $sessionId);
                $result['transaction_id']     = $this->extractCapturedPaymentId($data);

                $status = strtolower($data['status'] ?? '');
                if ($status === 'paid') {
                    $result['status'] = 'paid';
                } elseif (in_array($status, ['cancelled', 'expired'], true)) {
                    $result['status'] = 'failed';
                } else {
                    $result['status'] = 'pending';
                }
            }
        } catch (\Throwable $e) {
            Log::error("Razorpay verifyPaymentStatus failed: {$e->getMessage()}");
        }

        return $result;
    }

    /**
     * Pull the captured payment id from a fetched payment link entity.
     */
    protected function extractCapturedPaymentId(array $paymentLink): ?string
    {
        foreach ((array) ($paymentLink['payments'] ?? []) as $attempt) {
            $status = strtolower($attempt['status'] ?? '');
            if (in_array($status, ['captured', 'paid'], true) && ! empty($attempt['payment_id'])) {
                return (string) $attempt['payment_id'];
            }
        }

        return null;
    }

    // ──────────────────────────────────────────────
    //  Webhook verification (X-Razorpay-Signature)
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
        $webhookSecret = $this->resolvedCredentials['webhook_secret'] ?? '';

        // Fail closed: without a configured webhook secret we cannot
        // authenticate the caller, so the event must be rejected.
        if (empty($webhookSecret) || empty($signature)) {
            Log::error('Razorpay webhook rejected: webhook secret not configured or signature missing');
            return $result;
        }

        if (! hash_equals(hash_hmac('sha256', $payload, $webhookSecret), $signature)) {
            Log::error('Razorpay webhook signature mismatch');
            return $result;
        }

        $body = json_decode($payload, true);
        if (! $body) {
            return $result;
        }

        $result['valid']      = true;
        $result['event_type'] = $body['event'] ?? '';

        $eventType     = $result['event_type'];
        $linkEntity    = $body['payload']['payment_link']['entity'] ?? [];
        $paymentEntity = $body['payload']['payment']['entity'] ?? [];
        $refundEntity  = $body['payload']['refund']['entity'] ?? [];

        if ($eventType === 'payment_link.paid') {
            $notes = (array) ($linkEntity['notes'] ?? []) ?: (array) ($paymentEntity['notes'] ?? []);

            $result['gateway_payment_id'] = (string) ($linkEntity['id'] ?? '');
            $result['transaction_id']     = (string) ($paymentEntity['id'] ?? '');
            $result['payment_id']         = $notes['payment_id'] ?? null;
            $result['tenant_id']          = $notes['tenant_id'] ?? null;
            $result['registration_id']    = $notes['registration_id'] ?? null;
            $result['status']             = 'paid';
            // Razorpay amounts arrive in the smallest unit — convert back.
            $paidAmount         = $paymentEntity['amount'] ?? $linkEntity['amount_paid'] ?? null;
            $result['amount']   = $paidAmount !== null ? ((int) $paidAmount) / 100 : null;
            $result['currency'] = $paymentEntity['currency'] ?? $linkEntity['currency'] ?? null;
        }

        // Note: payment.captured is deliberately NOT treated as a paid signal.
        // Razorpay fires it alongside payment_link.paid for the same payment,
        // and a second "paid" delivery could re-provision a registration that
        // is being held as paid/pending-approval. payment_link.paid plus the
        // server-side verification on return fully cover confirmation.

        if ($eventType === 'refund.processed' && ! empty($refundEntity['payment_id'])) {
            $result['gateway_payment_id'] = (string) ($refundEntity['id'] ?? '');
            $result['transaction_id']     = (string) $refundEntity['payment_id'];
            $result['status']             = 'refunded';
        }

        // Note: payment.failed is deliberately ignored — Payment Links allow
        // the customer to retry, so a failed attempt is not a terminal failure.

        return $result;
    }
}

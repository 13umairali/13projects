<?php

namespace App\Services\PaymentGateways;

use App\Models\Central\TenantBillingPayment;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * PayFast gateway (South Africa — cards, Instant EFT, SnapScan, etc.).
 *
 * Uses the platform's own PayFast merchant account for subscription billing.
 * Checkout is a signed redirect to /eng/process (GET), which fits the
 * redirect()->away() flow used by all other gateways. ZAR only.
 *
 * Payment confirmation is driven exclusively by the ITN (Instant Transaction
 * Notification) webhook: the signature travels in the POST body (not a
 * header), and every ITN is additionally confirmed server-side against
 * PayFast's /eng/query/validate endpoint. There is no reliable way to query
 * a transaction by our own reference before the ITN arrives, so
 * verifyPaymentStatus() reports 'unknown' and the success page keeps polling
 * until the webhook lands (typically within seconds).
 *
 * Sandbox vs live host is controlled by the gateway's test_mode setting.
 */
class PayfastGateway implements PaymentGatewayInterface
{
    use HasGatewaySettings;

    protected const LIVE_HOST    = 'https://www.payfast.co.za';
    protected const SANDBOX_HOST = 'https://sandbox.payfast.co.za';

    public function getKey(): string
    {
        return 'payfast';
    }

    protected function getRequiredCredentialKeys(): array
    {
        return ['merchant_id', 'merchant_key'];
    }

    public function isAvailable(): bool
    {
        return $this->isGatewayAvailable();
    }

    public function getDisplayInfo(): array
    {
        return [
            'key'         => 'payfast',
            'label'       => 'PayFast',
            'description' => 'Accept cards, Instant EFT, and more in South Africa.',
            'icon'        => 'bi-cash-stack',
            'color'       => '#e2183d',
        ];
    }

    protected function host(): string
    {
        return $this->isTestMode() ? self::SANDBOX_HOST : self::LIVE_HOST;
    }

    // ──────────────────────────────────────────────
    //  Hosted checkout (signed redirect)
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
                'payfast_m_payment_id' => $result['session_id'],
            ]),
        ]);

        return $result['url'];
    }

    /**
     * Build a signed PayFast /eng/process redirect URL.
     *
     * Returns ['url' => <redirect_url>, 'session_id' => <m_payment_id>].
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

        $merchantId  = trim($this->resolvedCredentials['merchant_id'] ?? '');
        $merchantKey = trim($this->resolvedCredentials['merchant_key'] ?? '');
        $passphrase  = trim($this->resolvedCredentials['passphrase'] ?? '');

        if ($merchantId === '' || $merchantKey === '') {
            throw new \RuntimeException('PayFast merchant credentials are not configured.');
        }

        if (strtoupper($currency) !== 'ZAR') {
            throw new \RuntimeException('PayFast only supports ZAR payments.');
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

        $mPaymentId = 'pf' . bin2hex(random_bytes(12));

        // The ITN must reach the central domain regardless of which host
        // initiated checkout (tenant billing runs on tenant domains).
        $notifyUrl = rtrim((string) config('app.url'), '/') . '/webhook/payfast';

        // Field order matters: the signature is computed over the non-empty
        // fields in PayFast's prescribed attribute order.
        $fields = array_filter([
            'merchant_id'   => $merchantId,
            'merchant_key'  => $merchantKey,
            'return_url'    => $successUrl,
            'cancel_url'    => $cancelUrl,
            'notify_url'    => $notifyUrl,
            'name_first'    => $customerName,
            'email_address' => $customerEmail,
            'm_payment_id'  => $mPaymentId,
            'amount'        => number_format($amount, 2, '.', ''),
            'item_name'     => mb_substr($productName, 0, 100),
            'item_description' => mb_substr($description, 0, 255),
            'custom_str1'   => isset($metadata['payment_id']) ? (string) $metadata['payment_id'] : null,
            'custom_str2'   => isset($metadata['tenant_id']) ? (string) $metadata['tenant_id'] : null,
            'custom_str3'   => isset($metadata['registration_id']) ? (string) $metadata['registration_id'] : null,
        ], fn ($v) => $v !== null && $v !== '');

        $fields['signature'] = $this->generateSignature($fields, $passphrase);

        return [
            'url'        => $this->host() . '/eng/process?' . http_build_query($fields),
            'session_id' => $mPaymentId,
        ];
    }

    /**
     * MD5 signature over urlencoded key=value pairs in the given order,
     * with the passphrase appended when configured.
     */
    protected function generateSignature(array $fields, string $passphrase): string
    {
        $pairs = [];
        foreach ($fields as $key => $value) {
            $pairs[] = $key . '=' . urlencode(trim((string) $value));
        }

        $paramString = implode('&', $pairs);

        if ($passphrase !== '') {
            $paramString .= '&passphrase=' . urlencode($passphrase);
        }

        return md5($paramString);
    }

    // ──────────────────────────────────────────────
    //  Transaction verification
    // ──────────────────────────────────────────────

    /**
     * PayFast cannot be queried by our own m_payment_id before the ITN
     * arrives, so status is unknown until the webhook confirms it.
     */
    public function verifyPaymentStatus(string $sessionId): array
    {
        return ['status' => 'unknown', 'gateway_payment_id' => $sessionId, 'transaction_id' => null];
    }

    // ──────────────────────────────────────────────
    //  ITN webhook verification (signature in POST body)
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

        parse_str($payload, $data);

        if (empty($data) || empty($data['signature'])) {
            Log::error('PayFast ITN rejected: empty payload or missing signature');
            return $result;
        }

        $this->loadSettings();
        $passphrase = trim($this->resolvedCredentials['passphrase'] ?? '');

        // 1. Signature check: recompute over the fields in received order,
        //    excluding the signature itself.
        $pairs = [];
        foreach ($data as $key => $value) {
            if ($key === 'signature') {
                continue;
            }
            $pairs[] = $key . '=' . urlencode(trim((string) $value));
        }
        $paramString = implode('&', $pairs);

        $signedString = $paramString;
        if ($passphrase !== '') {
            $signedString .= '&passphrase=' . urlencode($passphrase);
        }

        if (! hash_equals(md5($signedString), strtolower((string) $data['signature']))) {
            Log::error('PayFast ITN signature mismatch');
            return $result;
        }

        // 2. Server confirmation: replay the (unsigned) param string to
        //    PayFast — the authoritative check that this ITN is genuine.
        try {
            $response = Http::withBody($paramString, 'application/x-www-form-urlencoded')
                ->timeout(15)
                ->post($this->host() . '/eng/query/validate');

            if (! $response->successful() || stripos(trim($response->body()), 'VALID') !== 0) {
                Log::error('PayFast ITN server validation failed', ['body' => trim($response->body())]);
                return $result;
            }
        } catch (\Throwable $e) {
            Log::error('PayFast ITN server validation request failed: ' . $e->getMessage());
            return $result;
        }

        $paymentStatus = strtoupper((string) ($data['payment_status'] ?? ''));

        $result['valid']              = true;
        $result['event_type']         = 'payfast.' . strtolower($paymentStatus ?: 'unknown');
        $result['gateway_payment_id'] = (string) ($data['m_payment_id'] ?? '');
        $result['transaction_id']     = (string) ($data['pf_payment_id'] ?? '');
        $result['payment_id']         = $data['custom_str1'] ?? null;
        $result['tenant_id']          = $data['custom_str2'] ?? null;
        $result['registration_id']    = $data['custom_str3'] ?? null;

        if ($paymentStatus === 'COMPLETE') {
            $result['status']   = 'paid';
            $result['amount']   = isset($data['amount_gross']) ? (float) $data['amount_gross'] : null;
            $result['currency'] = 'ZAR';
        } elseif ($paymentStatus === 'FAILED') {
            $result['status'] = 'failed';
        }
        // CANCELLED / PENDING are acknowledged but not acted on.

        return $result;
    }
}

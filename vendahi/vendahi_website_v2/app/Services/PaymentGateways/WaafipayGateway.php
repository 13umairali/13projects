<?php

namespace App\Services\PaymentGateways;

use App\Models\Central\TenantBillingPayment;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * WaafiPay gateway (Horn of Africa — EVC Plus, Zaad, Sahal wallets, cards).
 *
 * Uses the platform's own WaafiPay merchant account for subscription billing.
 * Checkout is the Hosted Payment Page: a server-side HPP_PURCHASE call
 * (authenticated by merchantUid + storeId + hppKey) returns an hppUrl to
 * redirect the customer to, which fits the redirect()->away() flow.
 *
 * WaafiPay has no metadata field, so our context is encoded in the
 * referenceId we generate: "wfp-p{payment_id}-{random}" for tenant billing
 * payments and "wfp-r{registration_id}-{random}" for new signups. The
 * webhook echoes the referenceId back and we parse it to route the event.
 *
 * Confirmation is webhook-driven (there is no publicly documented
 * transaction-query API): webhooks are HMAC-SHA256 signed with the endpoint
 * secret. Verification fails closed — without a configured webhook secret,
 * events are rejected. Unsigned "webhook.test" pings (sent at endpoint
 * registration) are acknowledged without acting on them.
 * Sandbox vs production host is controlled by the gateway's test_mode setting.
 */
class WaafipayGateway implements PaymentGatewayInterface
{
    use HasGatewaySettings;

    protected const LIVE_ENDPOINT    = 'https://api.waafipay.net/asm';
    protected const SANDBOX_ENDPOINT = 'http://sandbox.waafipay.net/asm';

    public function getKey(): string
    {
        return 'waafipay';
    }

    protected function getRequiredCredentialKeys(): array
    {
        return ['merchant_uid', 'store_id', 'hpp_key'];
    }

    public function isAvailable(): bool
    {
        return $this->isGatewayAvailable();
    }

    public function getDisplayInfo(): array
    {
        return [
            'key'         => 'waafipay',
            'label'       => 'WaafiPay',
            'description' => 'Accept EVC Plus, Zaad, Sahal, and cards in the Horn of Africa.',
            'icon'        => 'bi-phone',
            'color'       => '#00a651',
        ];
    }

    protected function endpoint(): string
    {
        return $this->isTestMode() ? self::SANDBOX_ENDPOINT : self::LIVE_ENDPOINT;
    }

    // ──────────────────────────────────────────────
    //  Hosted Payment Page checkout
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

        $payment->update([
            'gateway_payment_id' => $result['session_id'],
            'metadata' => array_merge($payment->metadata ?? [], [
                'waafipay_reference_id' => $result['session_id'],
            ]),
        ]);

        return $result['url'];
    }

    /**
     * Create a WaafiPay HPP session.
     *
     * Returns ['url' => <hppUrl>, 'session_id' => <referenceId>].
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

        $merchantUid = trim($this->resolvedCredentials['merchant_uid'] ?? '');
        $storeId     = trim($this->resolvedCredentials['store_id'] ?? '');
        $hppKey      = trim($this->resolvedCredentials['hpp_key'] ?? '');

        if ($merchantUid === '' || $storeId === '' || $hppKey === '') {
            throw new \RuntimeException('WaafiPay credentials are not configured.');
        }

        // No metadata support — encode context into the referenceId.
        $referenceId = $this->buildReferenceId($metadata);

        $payload = [
            'schemaVersion' => '1.0',
            'requestId'     => (string) Str::uuid(),
            'timestamp'     => (string) time(),
            'channelName'   => 'WEB',
            'serviceName'   => 'HPP_PURCHASE',
            'serviceParams' => [
                'merchantUid'           => $merchantUid,
                'storeId'               => ctype_digit($storeId) ? (int) $storeId : $storeId,
                'hppKey'                => $hppKey,
                'paymentMethod'         => 'MWALLET_ACCOUNT',
                'hppSuccessCallbackUrl' => $successUrl,
                'hppFailureCallbackUrl' => $cancelUrl,
                'hppRespDataFormat'     => 1,
                'transactionInfo'       => [
                    'referenceId' => $referenceId,
                    'invoiceId'   => $referenceId,
                    'amount'      => round($amount, 2),
                    'currency'    => strtoupper($currency),
                    'description' => mb_substr($productName . ' — ' . $description, 0, 255),
                ],
            ],
        ];

        try {
            $response = Http::acceptJson()->timeout(30)->post($this->endpoint(), $payload);
        } catch (\Throwable $e) {
            Log::error('WaafiPay HPP request failed', ['error' => $e->getMessage()]);
            throw new \RuntimeException('Could not connect to WaafiPay: ' . $e->getMessage());
        }

        $body   = $response->json() ?? [];
        $hppUrl = $body['params']['hppUrl'] ?? null;

        if (! $response->successful() || (string) ($body['responseCode'] ?? '') !== '2001' || empty($hppUrl)) {
            $message = $body['responseMsg'] ?? $body['errorCode'] ?? ('HTTP ' . $response->status());

            Log::error('WaafiPay HPP creation failed', [
                'http_status' => $response->status(),
                'response'    => $body,
            ]);

            throw new \RuntimeException('WaafiPay: ' . (is_string($message) ? $message : json_encode($message)));
        }

        return [
            'url'        => (string) $hppUrl,
            'session_id' => $referenceId,
        ];
    }

    protected function buildReferenceId(array $metadata): string
    {
        $random = bin2hex(random_bytes(6));

        if (! empty($metadata['payment_id'])) {
            return 'wfp-p' . $metadata['payment_id'] . '-' . $random;
        }

        if (! empty($metadata['registration_id'])) {
            return 'wfp-r' . $metadata['registration_id'] . '-' . $random;
        }

        return 'wfp-x-' . $random;
    }

    // ──────────────────────────────────────────────
    //  Transaction verification
    // ──────────────────────────────────────────────

    /**
     * WaafiPay has no publicly documented transaction-query API — status is
     * unknown until the webhook confirms it.
     */
    public function verifyPaymentStatus(string $sessionId): array
    {
        return ['status' => 'unknown', 'gateway_payment_id' => $sessionId, 'transaction_id' => null];
    }

    // ──────────────────────────────────────────────
    //  Webhook verification (HMAC over raw body)
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
        if (! is_array($body)) {
            return $result;
        }

        $eventType = (string) ($body['event'] ?? $body['type'] ?? '');
        $result['event_type'] = $eventType !== '' ? $eventType : 'waafipay.notification';

        // Registration ping: unsigned by design, must be acknowledged with
        // 200 OK. It carries no transaction so nothing is acted on.
        if ($eventType === 'webhook.test') {
            $result['valid'] = true;
            return $result;
        }

        $this->loadSettings();
        $webhookSecret = trim($this->resolvedCredentials['webhook_secret'] ?? '');

        if (! self::verifyHmacSignature($payload, $signature, $webhookSecret)) {
            Log::error('WaafiPay webhook rejected: signature verification failed (is the webhook secret configured?)');
            return $result;
        }

        // Payload fields vary slightly between deliveries — read defensively.
        $data = (array) ($body['data'] ?? $body['params'] ?? $body);

        $referenceId   = (string) ($data['referenceId'] ?? $data['reference_id'] ?? '');
        $transactionId = (string) ($data['transactionId'] ?? $data['transaction_id'] ?? '');
        $state         = strtoupper((string) ($data['state'] ?? $data['status'] ?? ''));

        if ($referenceId === '') {
            return $result;
        }

        $result['valid']              = true;
        $result['gateway_payment_id'] = $referenceId;
        $result['transaction_id']     = $transactionId;

        // Recover our context from the referenceId we generated at checkout.
        if (preg_match('/^wfp-p(\d+)-[a-f0-9]+$/', $referenceId, $m)) {
            $result['payment_id'] = (int) $m[1];
        } elseif (preg_match('/^wfp-r(\d+)-[a-f0-9]+$/', $referenceId, $m)) {
            $result['registration_id'] = (int) $m[1];
        }

        if (in_array($state, ['APPROVED', 'COMPLETED'], true)) {
            $result['status']   = 'paid';
            $result['amount']   = isset($data['amount']) ? (float) $data['amount'] : null;
            $result['currency'] = $data['currency'] ?? null;
        } elseif (in_array($state, ['DECLINED', 'FAILED', 'CANCELLED'], true)) {
            $result['status'] = 'failed';
        }

        return $result;
    }

    /**
     * HMAC-SHA256 of the raw body against the endpoint secret. The signature
     * header value may be the raw hex digest, base64, or "sha256=<hex>".
     * Fails closed when the secret or signature is missing.
     * Shared with the tenant-level service.
     */
    public static function verifyHmacSignature(string $payload, string $signatureHeader, string $secret): bool
    {
        $signatureHeader = trim($signatureHeader);

        if ($secret === '' || $signatureHeader === '') {
            return false;
        }

        if (str_starts_with(strtolower($signatureHeader), 'sha256=')) {
            $signatureHeader = substr($signatureHeader, 7);
        }

        $binary = hash_hmac('sha256', $payload, $secret, true);

        foreach ([bin2hex($binary), base64_encode($binary)] as $expected) {
            if (hash_equals($expected, $signatureHeader)) {
                return true;
            }
        }

        return false;
    }
}

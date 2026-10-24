<?php

namespace App\Services;

use App\Models\PaymentSetting;
use App\Services\PaymentGateways\WaafipayGateway;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * WaafiPay client for tenant-facing payments (storefront checkout).
 *
 * Uses the tenant's own WaafiPay merchant account: credentials come
 * exclusively from the tenant DB `payment_settings` row (no .env fallback).
 * The platform's WaafiPay account for subscription billing lives in
 * App\Services\PaymentGateways\WaafipayGateway.
 *
 * Flow: pending order → server-side HPP_PURCHASE call (referenceId = the
 * order ref) → buyer redirected to the returned hppUrl → the HMAC-signed
 * webhook marks the order paid. No transaction-query API exists, so the
 * webhook is the only confirmation channel.
 */
class TenantWaafipayService
{
    protected const LIVE_ENDPOINT    = 'https://api.waafipay.net/asm';
    protected const SANDBOX_ENDPOINT = 'http://sandbox.waafipay.net/asm';

    protected ?array $credentials = null;

    /**
     * HPP (hosted payment page) redirect flow configured.
     */
    public function isConfigured(): bool
    {
        $c = $this->credentials();

        return ! empty($c['merchant_uid']) && ! empty($c['store_id']) && ! empty($c['hpp_key']);
    }

    /**
     * Direct wallet API (API_PURCHASE) configured — the preferred flow:
     * synchronous USSD/PIN confirmation, no webhook dependency.
     */
    public function isDirectConfigured(): bool
    {
        $c = $this->credentials();

        return ! empty($c['merchant_uid']) && ! empty($c['api_user_id']) && ! empty($c['api_key']);
    }

    /**
     * Perform a synchronous API_PURCHASE debit: the payer receives a USSD/PIN
     * prompt on their handset and the call returns once they respond (or the
     * 60s prompt times out — hence the long HTTP timeout).
     *
     * @return array{approved: bool, transaction_id: string, state: string, message: string}
     */
    public function directPurchase(
        string $payerPhone,
        float $amount,
        string $currency,
        string $referenceId,
        string $invoiceId,
        string $description
    ): array {
        $c = $this->credentials();

        if (! $this->isDirectConfigured()) {
            throw new RuntimeException('WaafiPay direct API is not configured for this tenant.');
        }

        if (! preg_match('/^252\d{9}$/', $payerPhone)) {
            return ['approved' => false, 'transaction_id' => '', 'state' => 'INVALID', 'message' => 'Invalid wallet number. Use the format 2526XXXXXXXX.'];
        }

        $payload = [
            'schemaVersion' => '1.0',
            'requestId'     => (string) (int) (microtime(true) * 1000),
            'timestamp'     => (int) (microtime(true) * 1000),
            'channelName'   => 'WEB',
            'serviceName'   => 'API_PURCHASE',
            'serviceParams' => [
                'merchantUid'     => $c['merchant_uid'],
                'apiUserId'       => $c['api_user_id'],
                'apiKey'          => $c['api_key'],
                'paymentMethod'   => 'mwallet_account',
                'payerInfo'       => [
                    'accountNo' => $payerPhone,
                ],
                'transactionInfo' => [
                    'referenceId' => $referenceId,
                    'invoiceId'   => $invoiceId,
                    'amount'      => number_format($amount, 2, '.', ''),
                    'currency'    => strtoupper($currency),
                    'description' => mb_substr($description, 0, 255),
                ],
            ],
        ];

        // The call blocks while the payer answers the PIN prompt (up to 60s
        // on their side) — allow twice that before giving up.
        $response = Http::acceptJson()->timeout(120)->post($this->endpoint(), $payload);

        $body = $response->json() ?? [];

        $responseCode = (string) ($body['responseCode'] ?? '');
        $responseMsg  = strtoupper((string) ($body['responseMsg'] ?? ''));
        $state        = strtoupper((string) ($body['params']['state'] ?? ''));

        if ($response->successful() && $responseCode === '2001' && $state === 'APPROVED') {
            return [
                'approved'       => true,
                'transaction_id' => (string) ($body['params']['transactionId'] ?? ''),
                'state'          => $state,
                'message'        => 'Approved',
            ];
        }

        $message = match ($responseMsg) {
            'RCS_USER_REJECTED'      => 'The payment was declined on your phone.',
            'RCS_INSUFFICIENT_FUNDS' => 'Insufficient wallet balance.',
            'RCS_TIMEOUT'            => 'The PIN prompt timed out. Please try again.',
            'RCS_INVALID_ACCOUNT'    => 'This number is not active on mobile money.',
            'RCS_AUTH_FAILED'        => 'Payment service configuration error. Please contact the store.',
            default                  => (string) ($body['responseDesc'] ?? $body['responseMsg'] ?? 'Payment was not completed.'),
        };

        return [
            'approved'       => false,
            'transaction_id' => (string) ($body['params']['transactionId'] ?? ''),
            'state'          => $state ?: $responseMsg,
            'message'        => $message,
        ];
    }

    /**
     * Create an HPP session for a storefront order.
     *
     * Returns the hppUrl to redirect the buyer to.
     */
    public function createHppSession(
        float $amount,
        string $currency,
        string $orderRef,
        string $successUrl,
        string $failureUrl
    ): string {
        $c = $this->credentials();

        if (! $this->isConfigured()) {
            throw new RuntimeException('WaafiPay is not configured for this tenant.');
        }

        $payload = [
            'schemaVersion' => '1.0',
            'requestId'     => (string) Str::uuid(),
            'timestamp'     => (string) time(),
            'channelName'   => 'WEB',
            'serviceName'   => 'HPP_PURCHASE',
            'serviceParams' => [
                'merchantUid'           => $c['merchant_uid'],
                'storeId'               => ctype_digit($c['store_id']) ? (int) $c['store_id'] : $c['store_id'],
                'hppKey'                => $c['hpp_key'],
                'paymentMethod'         => 'MWALLET_ACCOUNT',
                'hppSuccessCallbackUrl' => $successUrl,
                'hppFailureCallbackUrl' => $failureUrl,
                'hppRespDataFormat'     => 1,
                'transactionInfo'       => [
                    'referenceId' => $orderRef,
                    'invoiceId'   => $orderRef,
                    'amount'      => round($amount, 2),
                    'currency'    => strtoupper($currency),
                    'description' => mb_substr('Online store order ' . $orderRef, 0, 255),
                ],
            ],
        ];

        $response = Http::acceptJson()->timeout(30)->post($this->endpoint(), $payload);

        $body   = $response->json() ?? [];
        $hppUrl = $body['params']['hppUrl'] ?? null;

        if (! $response->successful() || (string) ($body['responseCode'] ?? '') !== '2001' || empty($hppUrl)) {
            $message = $body['responseMsg'] ?? $body['errorCode'] ?? $response->status();
            throw new RuntimeException('WaafiPay HPP creation failed: ' . (is_string($message) ? $message : json_encode($message)));
        }

        return (string) $hppUrl;
    }

    /**
     * Verify the HMAC signature of an incoming webhook. Fails closed.
     */
    public function verifyWebhookSignature(string $payload, string $signatureHeader): bool
    {
        return WaafipayGateway::verifyHmacSignature(
            $payload,
            $signatureHeader,
            $this->credentials()['webhook_secret']
        );
    }

    protected function endpoint(): string
    {
        return $this->credentials()['test_mode'] ? self::SANDBOX_ENDPOINT : self::LIVE_ENDPOINT;
    }

    protected function credentials(): array
    {
        if ($this->credentials !== null) {
            return $this->credentials;
        }

        $settings = PaymentSetting::current();

        return $this->credentials = [
            'merchant_uid'   => trim((string) ($settings->waafipay_merchant_uid ?? '')),
            'store_id'       => trim((string) ($settings->waafipay_store_id ?? '')),
            'hpp_key'        => trim((string) ($settings->waafipay_hpp_key ?? '')),
            'api_user_id'    => trim((string) ($settings->waafipay_api_user_id ?? '')),
            'api_key'        => trim((string) ($settings->waafipay_api_key ?? '')),
            'webhook_secret' => trim((string) ($settings->waafipay_webhook_secret ?? '')),
            'test_mode'      => (bool) ($settings->waafipay_test_mode ?? false),
        ];
    }
}

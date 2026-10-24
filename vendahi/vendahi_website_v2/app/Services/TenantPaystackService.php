<?php

namespace App\Services;

use App\Models\PaymentSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * Paystack (Transactions API) for tenant-facing payments (storefront checkout).
 *
 * Uses the tenant's own Paystack account: credentials come exclusively from
 * the tenant DB `payment_settings` row, never .env. Test vs live is decided
 * by the key pair (sk_test_ / sk_live_), so there is no sandbox switch.
 *
 * Flow: pending order → initialize a transaction with a server-generated
 * reference and the SERVER-computed total (in subunits) → buyer pays on the
 * hosted authorization_url → the return leg VERIFIES server-side. The
 * redirect is never trusted on its own.
 */
class TenantPaystackService
{
    protected const API_BASE = 'https://api.paystack.co';

    protected ?array $credentials = null;

    public function isConfigured(): bool
    {
        $c = $this->credentials();

        return $c['public_key'] !== '' && $c['secret_key'] !== '';
    }

    /**
     * Initialize a transaction and return the hosted checkout URL plus the
     * server-generated reference. If the merchant's account cannot charge the
     * requested currency, retry without it so Paystack uses its default.
     *
     * @return array{url: string, reference: string}
     */
    public function initializeTransaction(
        float $amount,
        string $currency,
        string $email,
        array $metadata,
        string $callbackUrl
    ): array {
        if (! $this->isConfigured()) {
            throw new RuntimeException('Paystack is not configured for this tenant.');
        }

        $reference = 'pstk_' . bin2hex(random_bytes(16));

        $payload = [
            'email' => $email !== '' ? $email : 'customer@example.com',
            // Paystack expects subunits (kobo / pesewas / cents).
            'amount' => (int) round($amount * 100),
            'currency' => strtoupper($currency),
            'reference' => $reference,
            'callback_url' => $callbackUrl,
            'metadata' => $metadata,
        ];

        $response = Http::withToken($this->credentials()['secret_key'])
            ->timeout(30)
            ->post(self::API_BASE . '/transaction/initialize', $payload);

        if (! $response->successful() && $response->json('code') === 'unsupported_currency') {
            unset($payload['currency']);
            $payload['metadata']['original_currency'] = strtoupper($currency);

            $response = Http::withToken($this->credentials()['secret_key'])
                ->timeout(30)
                ->post(self::API_BASE . '/transaction/initialize', $payload);
        }

        if (! $response->successful() || ! $response->json('status')) {
            Log::error('Tenant Paystack transaction init failed', ['response' => $response->json()]);
            throw new RuntimeException('Failed to initialize Paystack transaction.');
        }

        $data = $response->json('data');

        if (empty($data['authorization_url'])) {
            throw new RuntimeException('Paystack did not return an authorization URL.');
        }

        return [
            'url' => (string) $data['authorization_url'],
            'reference' => (string) ($data['reference'] ?? $reference),
        ];
    }

    /**
     * Verify a transaction server-side. MUST be called when the buyer returns
     * — a forged redirect must never mark an order paid. When an expected
     * amount is given the charged subunits are cross-checked against it.
     *
     * @return array{success: bool, reference: string, transaction_id: ?string, status: ?string, amount: ?int, currency: ?string}
     */
    public function verifyTransaction(string $reference, ?float $expectedAmount = null, ?string $expectedCurrency = null): array
    {
        $fail = [
            'success' => false,
            'reference' => $reference,
            'transaction_id' => null,
            'status' => null,
            'amount' => null,
            'currency' => null,
        ];

        try {
            $response = Http::withToken($this->credentials()['secret_key'])
                ->timeout(30)
                ->get(self::API_BASE . '/transaction/verify/' . rawurlencode($reference));
        } catch (\Throwable $e) {
            Log::error('Tenant Paystack verify request failed: ' . $e->getMessage(), ['reference' => $reference]);

            return $fail;
        }

        if (! $response->successful() || ! $response->json('status')) {
            Log::error('Tenant Paystack transaction verification failed', [
                'reference' => $reference,
                'response' => $response->json(),
            ]);

            return $fail;
        }

        $data = $response->json('data') ?? [];
        $success = ($data['status'] ?? '') === 'success';
        $paid = isset($data['amount']) ? (int) $data['amount'] : null;
        $currency = isset($data['currency']) ? strtoupper((string) $data['currency']) : null;

        // Anti-tamper: the settled amount must cover what the order is worth.
        if ($success && $expectedAmount !== null) {
            $expectedMinor = (int) round($expectedAmount * 100);
            $currencyOk = $expectedCurrency === null || $currency === null || $currency === strtoupper($expectedCurrency);

            if ($paid === null || $paid < $expectedMinor || ! $currencyOk) {
                Log::error('Tenant Paystack amount mismatch', [
                    'reference' => $reference,
                    'paid' => $paid . ' ' . (string) $currency,
                    'expected' => $expectedMinor . ' ' . (string) $expectedCurrency,
                ]);
                $success = false;
            }
        }

        return [
            'success' => $success,
            'reference' => (string) ($data['reference'] ?? $reference),
            'transaction_id' => isset($data['id']) ? (string) $data['id'] : null,
            'status' => $data['status'] ?? null,
            'amount' => $paid,
            'currency' => $currency,
        ];
    }

    /**
     * Verify an incoming webhook (HMAC-SHA512 over the raw payload with the
     * secret key, sent as x-paystack-signature) and normalize the event.
     *
     * @return array{valid: bool, event_type: string, gateway_payment_id: string, transaction_id: string, online_order_id: ?int, status: string}
     */
    public function verifyWebhook(string $payload, string $signature): array
    {
        $result = [
            'valid' => false,
            'event_type' => '',
            'gateway_payment_id' => '',
            'transaction_id' => '',
            'online_order_id' => null,
            'status' => 'unknown',
        ];

        $secret = $this->credentials()['secret_key'];
        if ($secret === '' || $signature === '') {
            return $result;
        }

        if (! hash_equals(hash_hmac('sha512', $payload, $secret), $signature)) {
            Log::error('Tenant Paystack webhook signature mismatch');

            return $result;
        }

        $body = json_decode($payload, true);
        if (! $body) {
            return $result;
        }

        $result['valid'] = true;
        $result['event_type'] = (string) ($body['event'] ?? '');

        $data = $body['data'] ?? [];
        $meta = is_array($data['metadata'] ?? null) ? $data['metadata'] : [];

        if ($result['event_type'] === 'charge.success') {
            $result['gateway_payment_id'] = (string) ($data['reference'] ?? '');
            $result['transaction_id'] = (string) ($data['id'] ?? $data['reference'] ?? '');
            $result['online_order_id'] = isset($meta['online_order_id']) ? (int) $meta['online_order_id'] : null;
            $result['status'] = 'paid';
        } elseif ($result['event_type'] === 'charge.failed') {
            $result['gateway_payment_id'] = (string) ($data['reference'] ?? '');
            $result['transaction_id'] = (string) ($data['id'] ?? $data['reference'] ?? '');
            $result['online_order_id'] = isset($meta['online_order_id']) ? (int) $meta['online_order_id'] : null;
            $result['status'] = 'failed';
        } elseif ($result['event_type'] === 'refund.processed') {
            // The original transaction is nested for refund events.
            $transaction = $data['transaction'] ?? [];
            $refundMeta = is_array($transaction['metadata'] ?? null) ? $transaction['metadata'] : $meta;

            $result['gateway_payment_id'] = (string) ($data['id'] ?? '');      // refund id
            $result['transaction_id'] = (string) ($transaction['id'] ?? '');   // original txn id
            $result['online_order_id'] = isset($refundMeta['online_order_id']) ? (int) $refundMeta['online_order_id'] : null;
            $result['status'] = 'refunded';
        }

        return $result;
    }

    protected function credentials(): array
    {
        if ($this->credentials !== null) {
            return $this->credentials;
        }

        $settings = PaymentSetting::current();

        return $this->credentials = [
            'public_key' => trim((string) ($settings->paystack_public_key ?? '')),
            'secret_key' => trim((string) ($settings->paystack_secret_key ?? '')),
        ];
    }
}

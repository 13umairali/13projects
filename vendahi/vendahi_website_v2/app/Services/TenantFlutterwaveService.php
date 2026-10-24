<?php

namespace App\Services;

use App\Models\PaymentSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * Flutterwave v3 (Standard hosted payments) for tenant-facing payments
 * (storefront checkout).
 *
 * ALWAYS the v3 API (https://api.flutterwave.com/v3) with classic keys
 * (FLWPUBK_/FLWSECK_) — never v4, never OAuth. Uses the tenant's own
 * Flutterwave account: credentials come exclusively from the tenant DB
 * `payment_settings` row, never .env. Test vs live is decided by the key
 * pair, so there is no sandbox switch.
 *
 * Flow: pending order → initialize a payment with a server-generated tx_ref
 * and the SERVER-computed total → buyer pays on the hosted link → the return
 * leg VERIFIES by tx_ref. Webhooks are authenticated by the Secret Hash sent
 * in the `verif-hash` header; a missing configured hash REJECTS the event.
 *
 * Deliberately not ported from the central gateway: the DCC currency
 * fallback (retry in the account currency). Retrying with an unconverted
 * amount would charge the wrong price — an unsupported currency fails clean.
 */
class TenantFlutterwaveService
{
    /** Flutterwave v3 API base URL — never change to /v4. */
    protected const API_BASE = 'https://api.flutterwave.com/v3';

    protected ?array $credentials = null;

    public function isConfigured(): bool
    {
        $c = $this->credentials();

        return $c['public_key'] !== '' && $c['secret_key'] !== '';
    }

    /**
     * Initialize a v3 Standard payment and return the hosted link + tx_ref.
     *
     * @return array{url: string, tx_ref: string}
     */
    public function initializePayment(
        float $amount,
        string $currency,
        string $email,
        string $name,
        string $description,
        array $metadata,
        string $redirectUrl,
        string $title = ''
    ): array {
        if (! $this->isConfigured()) {
            throw new RuntimeException('Flutterwave is not configured for this tenant.');
        }

        $txRef = 'flw' . bin2hex(random_bytes(16));

        try {
            $response = Http::withToken($this->credentials()['secret_key'])
                ->timeout(30)
                ->post(self::API_BASE . '/payments', [
                    'tx_ref' => $txRef,
                    'amount' => $amount,
                    'currency' => strtoupper($currency),
                    'redirect_url' => $redirectUrl,
                    'customer' => [
                        'email' => $email !== '' ? $email : 'customer@example.com',
                        'name' => $name !== '' ? $name : 'Customer',
                    ],
                    'customizations' => [
                        'title' => mb_substr($title !== '' ? $title : (string) config('app.name'), 0, 60),
                        'description' => mb_substr($description, 0, 120),
                    ],
                    'meta' => $metadata,
                ]);
        } catch (\Throwable $e) {
            Log::error('Tenant Flutterwave v3 payment init request failed', ['error' => $e->getMessage()]);
            throw new RuntimeException('Could not connect to Flutterwave: ' . $e->getMessage());
        }

        $body = $response->json() ?? [];

        if (! $response->successful() || ($body['status'] ?? '') !== 'success') {
            Log::error('Tenant Flutterwave v3 payment init failed', [
                'http_status' => $response->status(),
                'currency' => strtoupper($currency),
                'response' => $body,
            ]);
            throw new RuntimeException('Flutterwave: ' . $this->extractMessage($body));
        }

        $link = (string) ($body['data']['link'] ?? '');
        if ($link === '') {
            throw new RuntimeException('Flutterwave did not return a checkout link.');
        }

        return ['url' => $link, 'tx_ref' => $txRef];
    }

    /**
     * Verify a transaction by tx_ref (v3 verify_by_reference). MUST be called
     * on the redirect back — the redirect's own status is never trusted. When
     * an expected amount is given, the settled charge is cross-checked.
     *
     * @return array{success: bool, status: ?string, transaction_id: string}
     */
    public function verifyTransaction(string $txRef, ?float $expectedAmount = null, ?string $expectedCurrency = null): array
    {
        try {
            $response = Http::withToken($this->credentials()['secret_key'])
                ->timeout(30)
                ->get(self::API_BASE . '/transactions/verify_by_reference', ['tx_ref' => $txRef]);
        } catch (\Throwable $e) {
            Log::error('Tenant Flutterwave v3 verify_by_reference failed', ['error' => $e->getMessage()]);

            return ['success' => false, 'status' => null, 'transaction_id' => ''];
        }

        $data = ($response->json() ?? [])['data'] ?? [];
        $status = strtolower((string) ($data['status'] ?? ''));
        $success = in_array($status, ['successful', 'succeeded', 'completed'], true);

        if ($success && $expectedAmount !== null) {
            $paid = (float) ($data['amount_settled'] ?? $data['amount'] ?? 0);
            $currency = strtoupper((string) ($data['currency'] ?? ''));
            $currencyOk = $expectedCurrency === null || $currency === '' || $currency === strtoupper($expectedCurrency);

            // Flutterwave may net fees out of amount_settled, so compare the
            // charged amount when it is present and fall back otherwise.
            $charged = (float) ($data['amount'] ?? $paid);

            if ($charged + 0.01 < round($expectedAmount, 2) || ! $currencyOk) {
                Log::error('Tenant Flutterwave amount mismatch', [
                    'tx_ref' => $txRef,
                    'charged' => $charged . ' ' . $currency,
                    'expected' => $expectedAmount . ' ' . (string) $expectedCurrency,
                ]);
                $success = false;
            }
        }

        return [
            'success' => $success,
            'status' => $status ?: null,
            'transaction_id' => (string) ($data['id'] ?? ''),
        ];
    }

    /**
     * Verify an incoming webhook via the Secret Hash (`verif-hash` header)
     * and normalize the event. A missing configured hash REJECTS the event —
     * an unsigned webhook must never mark orders paid.
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

        $secretHash = $this->credentials()['secret_hash'];
        if ($secretHash === '') {
            Log::warning('Tenant Flutterwave webhook received but no Secret Hash is configured.');

            return $result;
        }
        if (! hash_equals($secretHash, $signature)) {
            Log::error('Tenant Flutterwave webhook hash mismatch');

            return $result;
        }

        $body = json_decode($payload, true);
        if (! $body) {
            return $result;
        }

        $result['valid'] = true;
        $result['event_type'] = (string) ($body['event'] ?? '');

        $data = $body['data'] ?? [];
        $meta = is_array($data['meta'] ?? null) ? $data['meta'] : [];
        $chargeStatus = strtolower((string) ($data['status'] ?? ''));

        if ($result['event_type'] === 'charge.completed'
            && in_array($chargeStatus, ['successful', 'succeeded', 'completed'], true)) {
            $result['gateway_payment_id'] = (string) ($data['tx_ref'] ?? '');
            $result['transaction_id'] = (string) ($data['id'] ?? $data['tx_ref'] ?? '');
            $result['online_order_id'] = isset($meta['online_order_id']) ? (int) $meta['online_order_id'] : null;
            $result['status'] = 'paid';
        } elseif ($result['event_type'] === 'charge.completed' && $chargeStatus === 'failed') {
            $result['gateway_payment_id'] = (string) ($data['tx_ref'] ?? '');
            $result['transaction_id'] = (string) ($data['id'] ?? '');
            $result['online_order_id'] = isset($meta['online_order_id']) ? (int) $meta['online_order_id'] : null;
            $result['status'] = 'failed';
        }

        return $result;
    }

    /** Safely extract a string error message from a v3 response body. */
    protected function extractMessage(array $body, string $fallback = 'Unknown error'): string
    {
        foreach (['message', 'error_description', 'error', 'detail'] as $key) {
            if (! isset($body[$key])) {
                continue;
            }
            if (is_string($body[$key]) && $body[$key] !== '') {
                return $body[$key];
            }
            if (is_array($body[$key])) {
                return (string) json_encode($body[$key]);
            }
        }

        return $fallback;
    }

    protected function credentials(): array
    {
        if ($this->credentials !== null) {
            return $this->credentials;
        }

        $settings = PaymentSetting::current();

        return $this->credentials = [
            'public_key'  => trim((string) ($settings->flutterwave_public_key ?? '')),
            'secret_key'  => trim((string) ($settings->flutterwave_secret_key ?? '')),
            'secret_hash' => trim((string) ($settings->flutterwave_secret_hash ?? '')),
        ];
    }
}

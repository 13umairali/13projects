<?php

namespace App\Services;

use App\Models\PaymentSetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * bKash (Tokenized Checkout, URL-based "0011" mode) client for tenant-facing
 * payments (storefront checkout).
 *
 * Uses the tenant's own bKash merchant account: credentials come exclusively
 * from the tenant DB `payment_settings` row. bKash charges in BDT only.
 *
 * Flow: pending order → server-side /create call → buyer redirected to the
 * returned bkashURL → bKash sends the browser back to the callback URL with
 * ?paymentID&status → the callback EXECUTES server-side (query endpoint as
 * fallback for a retried/timed-out execute) and marks the order paid. bKash
 * has no webhook for this flow, so the callback is the only confirmation
 * channel — the status query param alone is never trusted.
 */
class TenantBkashService
{
    protected const LIVE_BASE    = 'https://tokenized.pay.bka.sh/v1.2.0-beta/tokenized/checkout';
    protected const SANDBOX_BASE = 'https://tokenized.sandbox.bka.sh/v1.2.0-beta/tokenized/checkout';

    protected ?array $credentials = null;

    public function isConfigured(): bool
    {
        $c = $this->credentials();

        return $c['app_key'] !== '' && $c['app_secret'] !== ''
            && $c['username'] !== '' && $c['password'] !== '';
    }

    /**
     * Create a tokenized-checkout payment for the SERVER-computed order total
     * and return the hosted URL to redirect the buyer to. bKash appends
     * ?paymentID&status=success|failure|cancel to the callback URL.
     *
     * @return array{url: string, payment_id: string}
     */
    public function createPayment(float $amount, string $payerReference, string $orderRef, string $callbackUrl): array
    {
        if (! $this->isConfigured()) {
            throw new RuntimeException('bKash is not configured for this tenant.');
        }

        $response = $this->api()->post($this->baseUrl() . '/create', [
            'mode'                  => '0011',
            'payerReference'        => mb_substr(trim($payerReference) !== '' ? $payerReference : $orderRef, 0, 255),
            'callbackURL'           => $callbackUrl,
            'amount'                => number_format($amount, 2, '.', ''),
            'currency'              => 'BDT',
            'intent'                => 'sale',
            'merchantInvoiceNumber' => $orderRef,
        ]);

        $body = $response->json() ?? [];
        if (! $response->successful() || ($body['statusCode'] ?? '') !== '0000' || empty($body['bkashURL'])) {
            Log::error('bKash payment create failed', ['response' => $body]);
            throw new RuntimeException('bKash payment creation failed: ' . (string) ($body['statusMessage'] ?? $response->status()));
        }

        return [
            'url'        => (string) $body['bkashURL'],
            'payment_id' => (string) $body['paymentID'],
        ];
    }

    /**
     * Execute an approved payment — the step that actually moves the money.
     * MUST run on the callback; a forged redirect must never mark an order paid.
     *
     * @return array{success: bool, payment_id: string, trx_id: ?string, status: ?string}
     */
    public function executePayment(string $paymentId): array
    {
        $response = $this->api()->post($this->baseUrl() . '/execute', [
            'paymentID' => $paymentId,
        ]);

        $body = $response->json() ?? [];

        if (($body['statusCode'] ?? '') === '0000' && ($body['transactionStatus'] ?? '') === 'Completed') {
            return [
                'success'    => true,
                'payment_id' => (string) ($body['paymentID'] ?? $paymentId),
                'trx_id'     => isset($body['trxID']) ? (string) $body['trxID'] : null,
                'status'     => (string) ($body['transactionStatus'] ?? ''),
            ];
        }

        Log::warning('bKash execute did not complete', ['payment_id' => $paymentId, 'response' => $body]);

        return ['success' => false, 'payment_id' => $paymentId, 'trx_id' => null, 'status' => (string) ($body['transactionStatus'] ?? '')];
    }

    /**
     * Query a payment's status — the recovery path when execute times out
     * after debiting (bKash-documented) or already ran on a retried callback.
     *
     * @return array{success: bool, payment_id: string, trx_id: ?string, status: ?string}
     */
    public function queryPayment(string $paymentId): array
    {
        $response = $this->api()->post($this->baseUrl() . '/payment/status', [
            'paymentID' => $paymentId,
        ]);

        $body = $response->json() ?? [];

        return [
            'success'    => ($body['statusCode'] ?? '') === '0000' && ($body['transactionStatus'] ?? '') === 'Completed',
            'payment_id' => (string) ($body['paymentID'] ?? $paymentId),
            'trx_id'     => isset($body['trxID']) ? (string) $body['trxID'] : null,
            'status'     => (string) ($body['transactionStatus'] ?? ''),
        ];
    }

    /** Pending request with the tokenized-checkout auth headers applied. */
    protected function api(): \Illuminate\Http\Client\PendingRequest
    {
        return Http::withHeaders([
            'Authorization' => $this->grantToken(),
            'X-APP-Key'     => $this->credentials()['app_key'],
        ])->acceptJson()->timeout(30);
    }

    /**
     * Grant token (id_token, ~1h), cached until shortly before bKash expires
     * it. The key carries the tenant id — the cache store may be shared
     * across tenants — plus the app key/endpoint so swapping credentials or
     * sandbox mode never reuses a stale token.
     */
    protected function grantToken(): string
    {
        $c = $this->credentials();
        $cacheKey = 'bkash_token_' . (tenant('id') ?? 'central') . '_' . md5($c['app_key'] . '|' . $this->baseUrl());

        return Cache::remember($cacheKey, now()->addMinutes(50), function () use ($c) {
            $response = Http::withHeaders([
                'username' => $c['username'],
                'password' => $c['password'],
            ])->acceptJson()->timeout(30)->post($this->baseUrl() . '/token/grant', [
                'app_key'    => $c['app_key'],
                'app_secret' => $c['app_secret'],
            ]);

            $body = $response->json() ?? [];
            if (! $response->successful() || empty($body['id_token'])) {
                Log::error('bKash token grant failed', ['response' => $body]);
                throw new RuntimeException('Failed to obtain bKash grant token.');
            }

            return (string) $body['id_token'];
        });
    }

    protected function baseUrl(): string
    {
        return $this->credentials()['test_mode'] ? self::SANDBOX_BASE : self::LIVE_BASE;
    }

    protected function credentials(): array
    {
        if ($this->credentials !== null) {
            return $this->credentials;
        }

        $settings = PaymentSetting::current();

        return $this->credentials = [
            'app_key'    => trim((string) ($settings->bkash_app_key ?? '')),
            'app_secret' => trim((string) ($settings->bkash_app_secret ?? '')),
            'username'   => trim((string) ($settings->bkash_username ?? '')),
            'password'   => trim((string) ($settings->bkash_password ?? '')),
            'test_mode'  => (bool) ($settings->bkash_test_mode ?? true),
        ];
    }
}

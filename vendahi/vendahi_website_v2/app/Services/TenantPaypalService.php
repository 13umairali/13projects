<?php

namespace App\Services;

use App\Models\PaymentSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * PayPal (Orders API v2) for tenant-facing payments (storefront checkout).
 *
 * Uses the tenant's own PayPal app: credentials come exclusively from the
 * tenant DB `payment_settings` row, never .env.
 *
 * Flow: pending order → create a PayPal order with intent CAPTURE and
 * return/cancel URLs → buyer approves on PayPal → the return leg CAPTURES
 * server-side. The redirect itself is never trusted; only a COMPLETED
 * capture marks the order paid, and ORDER_ALREADY_CAPTURED falls back to
 * re-reading the order so a retried return is idempotent.
 */
class TenantPaypalService
{
    protected const LIVE_BASE    = 'https://api-m.paypal.com';
    protected const SANDBOX_BASE = 'https://api-m.sandbox.paypal.com';

    protected ?array $credentials = null;

    public function isConfigured(): bool
    {
        $c = $this->credentials();

        return $c['client_id'] !== '' && $c['client_secret'] !== '';
    }

    /**
     * Create a PayPal order for the SERVER-computed total and return the
     * approve URL the buyer must be sent to.
     *
     * @return array{url: string, id: string}
     */
    public function createOrder(
        float $amount,
        string $currency,
        string $description,
        array $metadata,
        string $returnUrl,
        string $cancelUrl,
        string $brandName = ''
    ): array {
        if (! $this->isConfigured()) {
            throw new RuntimeException('PayPal is not configured for this tenant.');
        }

        $response = Http::withToken($this->accessToken())
            ->withOptions(['verify' => $this->shouldVerifySsl()])
            ->timeout(30)
            ->post($this->baseUrl() . '/v2/checkout/orders', [
                'intent' => 'CAPTURE',
                'purchase_units' => [[
                    'amount' => [
                        'currency_code' => strtoupper($currency),
                        'value' => number_format($amount, 2, '.', ''),
                    ],
                    'description' => mb_substr($description, 0, 127),
                    'custom_id' => json_encode($metadata),
                ]],
                'application_context' => [
                    'brand_name' => mb_substr($brandName !== '' ? $brandName : (string) config('app.name'), 0, 127),
                    'return_url' => $returnUrl,
                    'cancel_url' => $cancelUrl,
                    'user_action' => 'PAY_NOW',
                    'shipping_preference' => 'NO_SHIPPING',
                ],
            ]);

        if (! $response->successful()) {
            Log::error('Tenant PayPal order creation failed', ['response' => $response->json()]);
            throw new RuntimeException('Failed to create PayPal order.');
        }

        $order = $response->json();
        $approveLink = collect($order['links'] ?? [])->firstWhere('rel', 'approve');

        if (empty($approveLink['href'])) {
            throw new RuntimeException('PayPal did not return an approve link.');
        }

        return ['url' => $approveLink['href'], 'id' => (string) $order['id']];
    }

    /**
     * Capture an approved order, cross-checking the captured amount against
     * the total we expect. ORDER_ALREADY_CAPTURED (a retried return landed
     * first) falls through to re-reading the order.
     *
     * @return array{success: bool, order_id: string, capture_id: ?string, status: ?string}
     */
    public function captureOrder(string $orderId, ?float $expectedAmount = null, ?string $expectedCurrency = null): array
    {
        $response = Http::withToken($this->accessToken())
            ->withOptions(['verify' => $this->shouldVerifySsl()])
            ->withBody('{}', 'application/json')
            ->timeout(30)
            ->post($this->baseUrl() . "/v2/checkout/orders/{$orderId}/capture");

        $data = $response->json() ?? [];

        if (! $response->successful()) {
            if (($data['details'][0]['issue'] ?? '') === 'ORDER_ALREADY_CAPTURED') {
                return $this->getOrderCapture($orderId, $expectedAmount, $expectedCurrency);
            }

            Log::error('Tenant PayPal capture failed', [
                'order_id' => $orderId,
                'status' => $response->status(),
                'response' => $data,
            ]);

            return ['success' => false, 'order_id' => $orderId, 'capture_id' => null, 'status' => null];
        }

        return $this->normalizeCapture($data, $orderId, $expectedAmount, $expectedCurrency);
    }

    /** Re-read an order that PayPal says is already captured. */
    public function getOrderCapture(string $orderId, ?float $expectedAmount = null, ?string $expectedCurrency = null): array
    {
        $response = Http::withToken($this->accessToken())
            ->withOptions(['verify' => $this->shouldVerifySsl()])
            ->timeout(30)
            ->get($this->baseUrl() . "/v2/checkout/orders/{$orderId}");

        if (! $response->successful()) {
            Log::error('Tenant PayPal get order failed', ['order_id' => $orderId, 'response' => $response->json()]);

            return ['success' => false, 'order_id' => $orderId, 'capture_id' => null, 'status' => null];
        }

        return $this->normalizeCapture($response->json() ?? [], $orderId, $expectedAmount, $expectedCurrency);
    }

    /**
     * Verify an incoming webhook via PayPal's verify-webhook-signature API
     * and normalize the event. CHECKOUT.ORDER.APPROVED is deliberately NOT
     * treated as payment — approval fires before any money moves.
     *
     * @return array{valid: bool, event_type: string, gateway_payment_id: string, transaction_id: string, online_order_id: ?int, status: string}
     */
    public function verifyWebhook(string $payload, string $signature, array $headers = []): array
    {
        $result = [
            'valid' => false,
            'event_type' => '',
            'gateway_payment_id' => '',
            'transaction_id' => '',
            'online_order_id' => null,
            'status' => 'unknown',
        ];

        $webhookId = $this->credentials()['webhook_id'];
        if ($webhookId === '') {
            Log::warning('Tenant PayPal webhook received but no Webhook ID is configured.');

            return $result;
        }

        $body = json_decode($payload, true);
        if (! $body) {
            return $result;
        }

        try {
            $verification = Http::withToken($this->accessToken())
                ->withOptions(['verify' => $this->shouldVerifySsl()])
                ->timeout(30)
                ->post($this->baseUrl() . '/v1/notifications/verify-webhook-signature', [
                    'auth_algo' => (string) ($headers['auth_algo'] ?? ''),
                    'cert_url' => (string) ($headers['cert_url'] ?? ''),
                    'transmission_id' => (string) ($headers['transmission_id'] ?? ''),
                    'transmission_sig' => $signature,
                    'transmission_time' => (string) ($headers['transmission_time'] ?? ''),
                    'webhook_id' => $webhookId,
                    'webhook_event' => $body,
                ]);

            if (! $verification->successful() || $verification->json('verification_status') !== 'SUCCESS') {
                Log::error('Tenant PayPal webhook verification failed', ['response' => $verification->json()]);

                return $result;
            }
        } catch (\Throwable $e) {
            Log::error('Tenant PayPal webhook verification error: ' . $e->getMessage());

            return $result;
        }

        $result['valid'] = true;
        $result['event_type'] = (string) ($body['event_type'] ?? '');
        $resource = $body['resource'] ?? [];

        // Money moved.
        if (in_array($result['event_type'], ['PAYMENT.CAPTURE.COMPLETED', 'PAYMENT.SALE.COMPLETED'], true)) {
            $rawCustom = $resource['purchase_units'][0]['custom_id']
                ?? $resource['custom_id']
                ?? $resource['custom']      // v1 SALE events
                ?? '{}';
            $meta = json_decode((string) $rawCustom, true) ?: [];

            $result['gateway_payment_id'] = (string) ($resource['id'] ?? '');
            $result['transaction_id'] = (string) ($resource['id'] ?? '');
            $result['online_order_id'] = isset($meta['online_order_id']) ? (int) $meta['online_order_id'] : null;
            $result['status'] = 'paid';
        }

        // Refund / reversal. The refund resource carries no custom_id: for
        // CAPTURE refunds the "up" link points at the original capture, for
        // SALE refunds resource.sale_id does.
        elseif (in_array($result['event_type'], [
            'PAYMENT.CAPTURE.REFUNDED', 'PAYMENT.SALE.REFUNDED', 'PAYMENT.SALE.REVERSED',
        ], true)) {
            $captureId = null;
            if ($result['event_type'] === 'PAYMENT.CAPTURE.REFUNDED') {
                $upLink = collect($resource['links'] ?? [])->firstWhere('rel', 'up');
                if ($upLink) {
                    $captureId = basename((string) parse_url($upLink['href'], PHP_URL_PATH));
                }
            } else {
                $captureId = $resource['sale_id'] ?? $resource['id'] ?? null;
            }

            $result['transaction_id'] = (string) $captureId;
            $result['gateway_payment_id'] = (string) ($resource['id'] ?? '');
            $result['status'] = 'refunded';
        }

        elseif ($result['event_type'] === 'PAYMENT.CAPTURE.DENIED') {
            $result['gateway_payment_id'] = (string) ($resource['id'] ?? '');
            $result['transaction_id'] = (string) ($resource['id'] ?? '');
            $result['status'] = 'failed';
        }

        return $result;
    }

    /**
     * Pull the capture out of an order payload and confirm it is COMPLETED
     * for the amount we expect — a short capture must not mark an order paid.
     */
    protected function normalizeCapture(array $data, string $orderId, ?float $expectedAmount, ?string $expectedCurrency): array
    {
        $capture = $data['purchase_units'][0]['payments']['captures'][0] ?? null;
        $completed = $capture && ($capture['status'] ?? '') === 'COMPLETED';

        if ($completed && $expectedAmount !== null) {
            $paid = (float) ($capture['amount']['value'] ?? 0);
            $currency = strtoupper((string) ($capture['amount']['currency_code'] ?? ''));

            $amountOk = abs($paid - round($expectedAmount, 2)) < 0.01;
            $currencyOk = $expectedCurrency === null || $currency === strtoupper($expectedCurrency);

            if (! $amountOk || ! $currencyOk) {
                Log::error('Tenant PayPal capture amount mismatch', [
                    'order_id' => $orderId,
                    'captured' => $paid . ' ' . $currency,
                    'expected' => $expectedAmount . ' ' . (string) $expectedCurrency,
                ]);
                $completed = false;
            }
        }

        return [
            'success' => $completed,
            'order_id' => (string) ($data['id'] ?? $orderId),
            'capture_id' => $capture['id'] ?? null,
            'status' => $capture['status'] ?? ($data['status'] ?? null),
        ];
    }

    protected function accessToken(): string
    {
        $c = $this->credentials();

        try {
            $response = Http::asForm()
                ->withBasicAuth($c['client_id'], $c['client_secret'])
                ->withOptions(['verify' => $this->shouldVerifySsl()])
                ->timeout(30)
                ->post($this->baseUrl() . '/v1/oauth2/token', ['grant_type' => 'client_credentials']);
        } catch (\Throwable $e) {
            Log::error('Tenant PayPal token request exception: ' . $e->getMessage());
            throw new RuntimeException('Failed to obtain PayPal access token: ' . $e->getMessage());
        }

        if (! $response->successful()) {
            Log::error('Tenant PayPal token request failed', [
                'status' => $response->status(),
                'response' => $response->json() ?? $response->body(),
            ]);
            throw new RuntimeException('Failed to obtain PayPal access token (HTTP ' . $response->status() . ').');
        }

        return (string) $response->json('access_token');
    }

    /**
     * Always verify TLS against production PayPal. Only the sandbox host on a
     * local/dev machine is allowed to skip it (self-signed proxies).
     */
    protected function shouldVerifySsl(): bool
    {
        $isSandbox = str_contains($this->baseUrl(), 'sandbox');

        return ! ($isSandbox && app()->environment(['local', 'development', 'testing']));
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
            'client_id'     => trim((string) ($settings->paypal_client_id ?? '')),
            'client_secret' => trim((string) ($settings->paypal_client_secret ?? '')),
            'webhook_id'    => trim((string) ($settings->paypal_webhook_id ?? '')),
            'test_mode'     => (bool) ($settings->paypal_test_mode ?? true),
        ];
    }
}

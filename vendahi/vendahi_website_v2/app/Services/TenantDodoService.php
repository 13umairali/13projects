<?php

namespace App\Services;

use App\Models\PaymentSetting;
use App\Services\PaymentGateways\DodoGateway;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Dodo Payments client for tenant-facing payments (storefront checkout).
 *
 * Uses the tenant's own Dodo Payments account: credentials come exclusively
 * from the tenant DB `payment_settings` row (no .env fallback). The
 * platform's Dodo account for subscription billing lives in
 * App\Services\PaymentGateways\DodoGateway.
 *
 * Flow: pending order → hosted checkout session created against the tenant's
 * "Pay What You Want" product (amount override in cents, order ref in
 * metadata) → buyer redirected → the Standard-Webhooks-signed webhook marks
 * the order paid.
 */
class TenantDodoService
{
    protected const LIVE_HOST = 'https://live.dodopayments.com';
    protected const TEST_HOST = 'https://test.dodopayments.com';

    protected ?array $credentials = null;

    public function isConfigured(): bool
    {
        $c = $this->credentials();

        return ! empty($c['api_key']) && ! empty($c['product_id']);
    }

    /**
     * Create a checkout session for a storefront order.
     *
     * @return array{session_id: string, url: string}
     */
    public function createCheckoutSession(float $amount, string $orderRef, ?string $customerEmail, string $returnUrl): array
    {
        $c = $this->credentials();

        if (! $this->isConfigured()) {
            throw new RuntimeException('Dodo Payments is not configured for this tenant.');
        }

        $payload = [
            'product_cart' => [[
                'product_id' => $c['product_id'],
                'quantity'   => 1,
                // Smallest currency unit — PWYW override for the order total.
                'amount'     => (int) round($amount * 100),
            ]],
            'return_url' => $returnUrl,
            'metadata'   => ['order_ref' => $orderRef],
        ];

        if (! empty($customerEmail)) {
            $payload['customer'] = ['email' => $customerEmail, 'name' => 'Customer'];
        }

        $response = $this->request()->post($this->host() . '/checkouts', $payload);

        $body = $response->json() ?? [];

        if (! $response->successful() || empty($body['session_id']) || empty($body['checkout_url'])) {
            $message = $body['message'] ?? $body['error'] ?? ('HTTP ' . $response->status());
            throw new RuntimeException('Dodo checkout creation failed: ' . (is_string($message) ? $message : json_encode($message)));
        }

        return [
            'session_id' => (string) $body['session_id'],
            'url'        => (string) $body['checkout_url'],
        ];
    }

    /**
     * Verify the Standard Webhooks signature of an incoming webhook.
     */
    public function verifyWebhookSignature(string $payload, string $signatureHeader, string $webhookId, string $webhookTimestamp): bool
    {
        return DodoGateway::verifyStandardWebhookSignature(
            $payload,
            $signatureHeader,
            $webhookId,
            $webhookTimestamp,
            $this->credentials()['webhook_secret']
        );
    }

    protected function request(): \Illuminate\Http\Client\PendingRequest
    {
        return Http::withToken($this->credentials()['api_key'])->acceptJson()->timeout(30);
    }

    protected function host(): string
    {
        return $this->credentials()['test_mode'] ? self::TEST_HOST : self::LIVE_HOST;
    }

    protected function credentials(): array
    {
        if ($this->credentials !== null) {
            return $this->credentials;
        }

        $settings = PaymentSetting::current();

        return $this->credentials = [
            'api_key'        => trim((string) ($settings->dodo_api_key ?? '')),
            'product_id'     => trim((string) ($settings->dodo_product_id ?? '')),
            'webhook_secret' => trim((string) ($settings->dodo_webhook_secret ?? '')),
            'test_mode'      => (bool) ($settings->dodo_test_mode ?? false),
        ];
    }
}

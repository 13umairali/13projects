<?php

namespace App\Services;

use App\Models\PaymentSetting;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Razorpay client for tenant-facing payments (storefront checkout).
 *
 * Uses the tenant's own Razorpay account: credentials come exclusively from
 * the tenant DB `payment_settings` row (no .env fallback), so every tenant
 * charges into its own account. The platform's Razorpay account for
 * subscription billing lives in App\Services\PaymentGateways\RazorpayGateway.
 *
 * Hand-rolled REST client over the Http facade — no SDK dependency.
 * Amounts are sent in the smallest currency unit (paise for INR).
 */
class TenantRazorpayService
{
    protected const API_BASE = 'https://api.razorpay.com/v1';

    protected ?array $credentials = null;

    public function isConfigured(): bool
    {
        $c = $this->credentials();

        return ! empty($c['key_id']) && ! empty($c['key_secret']);
    }

    public function keyId(): ?string
    {
        return $this->credentials()['key_id'] ?: null;
    }

    /**
     * Create an order for Standard Checkout (checkout.js modal).
     *
     * @return array{id: string, amount: int, currency: string, status: string}
     */
    public function createOrder(float $amount, string $receipt, array $notes = [], string $currency = 'INR'): array
    {
        $response = $this->request()->post(self::API_BASE . '/orders', [
            'amount'          => (int) round($amount * 100), // smallest unit (paise)
            'currency'        => strtoupper($currency),
            'receipt'         => $receipt,
            'payment_capture' => 1,
            'notes'           => $notes ?: (object) [],
        ]);

        if ($response->failed()) {
            throw new RuntimeException(
                'Razorpay order creation failed: ' . ($response->json('error.description') ?? $response->status())
            );
        }

        return [
            'id'       => (string) $response->json('id'),
            'amount'   => (int) $response->json('amount'),
            'currency' => (string) $response->json('currency'),
            'status'   => (string) $response->json('status'),
        ];
    }

    /**
     * Fetch a payment for server-side cross-checking (status / order / amount).
     */
    public function fetchPayment(string $paymentId): array
    {
        $response = $this->request()->get(self::API_BASE . '/payments/' . $paymentId);

        if ($response->failed()) {
            throw new RuntimeException(
                'Razorpay payment fetch failed: ' . ($response->json('error.description') ?? $response->status())
            );
        }

        return (array) $response->json();
    }

    /**
     * Verify the checkout handler signature: HMAC-SHA256("order_id|payment_id").
     * Fails closed when the secret or signature is missing.
     */
    public function verifyPaymentSignature(string $orderId, string $paymentId, ?string $signature): bool
    {
        $secret = $this->credentials()['key_secret'];

        if (empty($secret) || empty($signature)) {
            return false;
        }

        return hash_equals(hash_hmac('sha256', $orderId . '|' . $paymentId, $secret), $signature);
    }

    protected function request(): \Illuminate\Http\Client\PendingRequest
    {
        $c = $this->credentials();

        if (empty($c['key_id']) || empty($c['key_secret'])) {
            throw new RuntimeException('Razorpay is not configured for this tenant.');
        }

        return Http::withBasicAuth($c['key_id'], $c['key_secret'])->acceptJson()->timeout(15);
    }

    protected function credentials(): array
    {
        if ($this->credentials !== null) {
            return $this->credentials;
        }

        $settings = PaymentSetting::current();

        return $this->credentials = [
            'key_id'     => trim((string) ($settings->razorpay_key_id ?? '')),
            'key_secret' => trim((string) ($settings->razorpay_key_secret ?? '')),
        ];
    }
}

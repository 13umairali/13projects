<?php

namespace App\Services;

use App\Models\PaymentSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * Moyasar (Invoice API) client for tenant-facing payments (storefront
 * checkout).
 *
 * Uses the tenant's own Moyasar account: credentials come exclusively from
 * the tenant DB `payment_settings` row. Charges in the configured currency
 * (moyasar_currency, SAR by default) — the store's base currency must match
 * it. Sandbox is selected by using an sk_test_ key (no separate host).
 *
 * Flow: pending order → server-side invoice create → buyer redirected to the
 * hosted invoice page → Moyasar redirects the browser back to the callback
 * URL. Paid is only ever written after the invoice is re-fetched server-side
 * and its status, amount and currency cross-checked ("never act on the
 * redirect — always re-verify").
 */
class TenantMoyasarService
{
    protected const API_BASE = 'https://api.moyasar.com';

    protected ?array $credentials = null;

    public function isConfigured(): bool
    {
        return $this->credentials()['secret_key'] !== '';
    }

    public function currency(): string
    {
        return $this->credentials()['currency'];
    }

    /** Minor-unit multiplier (Moyasar amounts are integers in the smallest unit). */
    public function minorUnits(string $currency): int
    {
        return match (strtoupper($currency)) {
            'KWD', 'BHD', 'OMR' => 1000,
            'JPY'               => 1,
            default             => 100,
        };
    }

    /**
     * Create a hosted invoice and return its payment page URL + invoice id
     * (the reference the callback verification resolves the pending order by).
     *
     * @return array{url: string, invoice_id: string}
     */
    public function createInvoice(
        float $amount,
        string $orderRef,
        string $callbackUrl,
        string $backUrl
    ): array {
        if (! $this->isConfigured()) {
            throw new RuntimeException('Moyasar is not configured for this tenant.');
        }

        $currency = $this->currency();

        $response = $this->api()->post(self::API_BASE . '/v1/invoices', [
            'amount'       => (int) round($amount * $this->minorUnits($currency)),
            'currency'     => $currency,
            'description'  => mb_substr('Online store order ' . $orderRef, 0, 255),
            'callback_url' => $callbackUrl,
            'back_url'     => $backUrl,
            'metadata'     => ['order_ref' => $orderRef],
        ]);

        $body = $response->json() ?? [];

        if (! $response->successful() || empty($body['id']) || empty($body['url'])) {
            Log::error('Moyasar invoice create failed', [
                'http_status' => $response->status(),
                'message'     => $body['message'] ?? null,
                'errors'      => $body['errors'] ?? null,
            ]);

            throw new RuntimeException('Moyasar invoice creation failed: ' . (string) ($body['message'] ?? $response->status()));
        }

        return [
            'url'        => (string) $body['url'],
            'invoice_id' => (string) $body['id'],
        ];
    }

    /**
     * Verify an invoice server-side by its id. FAILS CLOSED: rejects unless
     * the invoice is paid and the amount and currency match the order within
     * rounding tolerance.
     *
     * @return array{success: bool, invoice_id: string, payment_id: ?string, status: ?string}
     */
    public function verifyInvoice(string $invoiceId, float $expectedAmount): array
    {
        $fail = ['success' => false, 'invoice_id' => $invoiceId, 'payment_id' => null, 'status' => null];

        $response = $this->api()->get(self::API_BASE . '/v1/invoices/' . urlencode($invoiceId));
        $body     = $response->json() ?? [];

        if (! $response->successful() || empty($body['id'])) {
            Log::warning('Moyasar invoice fetch not successful', [
                'invoice_id' => $invoiceId,
                'message'    => $body['message'] ?? null,
            ]);

            return $fail;
        }

        $status         = strtolower((string) ($body['status'] ?? ''));
        $fail['status'] = strtoupper($status);

        if ($status !== 'paid') {
            return $fail;
        }

        $currency      = strtoupper((string) ($body['currency'] ?? ''));
        $expectedMinor = (int) round($expectedAmount * $this->minorUnits($this->currency()));
        $chargedMinor  = (int) ($body['amount'] ?? -1);

        if ($currency !== $this->currency() || abs($chargedMinor - $expectedMinor) > 1) {
            Log::error('Moyasar verification currency/amount mismatch — order left unpaid', [
                'invoice_id'        => $invoiceId,
                'expected_currency' => $this->currency(),
                'charged_currency'  => $currency,
                'expected_minor'    => $expectedMinor,
                'charged_minor'     => $chargedMinor,
            ]);

            return $fail;
        }

        // The paying transaction's id lives in the invoice's payments list.
        $paymentId = null;
        foreach ((array) ($body['payments'] ?? []) as $paymentRow) {
            if (is_array($paymentRow) && strtolower((string) ($paymentRow['status'] ?? '')) === 'paid') {
                $paymentId = (string) ($paymentRow['id'] ?? '');
                break;
            }
        }

        return [
            'success'    => true,
            'invoice_id' => $invoiceId,
            'payment_id' => $paymentId,
            'status'     => 'PAID',
        ];
    }

    protected function api()
    {
        return Http::withBasicAuth($this->credentials()['secret_key'], '')->timeout(30);
    }

    protected function credentials(): array
    {
        if ($this->credentials !== null) {
            return $this->credentials;
        }

        $settings = PaymentSetting::current();

        return $this->credentials = [
            'secret_key' => trim((string) ($settings->moyasar_secret_key ?? '')),
            'currency'   => strtoupper(trim((string) ($settings->moyasar_currency ?? '')) ?: 'SAR'),
        ];
    }
}

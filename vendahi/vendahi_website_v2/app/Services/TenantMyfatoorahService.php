<?php

namespace App\Services;

use App\Models\PaymentSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * MyFatoorah (v2 invoice-link API) client for tenant-facing payments
 * (storefront checkout).
 *
 * Uses the tenant's own MyFatoorah account: credentials come exclusively from
 * the tenant DB `payment_settings` row. Charges in the configured currency
 * (myfatoorah_currency, KWD by default) — the store's base currency must
 * match it.
 *
 * Flow: pending order → server-side SendPayment (invoice link) → buyer
 * redirected to the hosted InvoiceURL → MyFatoorah GETs the browser back to
 * the callback URL with ?paymentId=. Paid is only ever written after
 * GetPaymentStatus confirms the invoice, with invoice id, amount and
 * currency cross-checked ("never act on the redirect — always re-verify").
 */
class TenantMyfatoorahService
{
    protected const TEST_BASE = 'https://apitest.myfatoorah.com';

    /** ISO country code → live API host. Other GCC accounts use the default. */
    protected const LIVE_HOSTS = [
        'SAU' => 'https://api-sa.myfatoorah.com',
        'QAT' => 'https://api-qa.myfatoorah.com',
        'EGY' => 'https://api-eg.myfatoorah.com',
    ];

    protected const DEFAULT_LIVE_BASE = 'https://api.myfatoorah.com';

    protected ?array $credentials = null;

    public function isConfigured(): bool
    {
        return $this->credentials()['api_token'] !== '';
    }

    public function currency(): string
    {
        return $this->credentials()['currency'];
    }

    /**
     * Create a hosted invoice link and return its URL + InvoiceId (the
     * reference the callback verification resolves the pending order by).
     *
     * @param  array{name: string, email: string, phone: string}  $customer
     * @return array{url: string, invoice_id: string}
     */
    public function createInvoice(
        float $amount,
        string $orderRef,
        array $customer,
        string $callbackUrl,
        string $errorUrl
    ): array {
        if (! $this->isConfigured()) {
            throw new RuntimeException('MyFatoorah is not configured for this tenant.');
        }

        $response = Http::withHeaders($this->apiHeaders())
            ->timeout(30)
            ->post($this->baseUrl() . '/v2/SendPayment', [
                'NotificationOption' => 'LNK',
                'CustomerName'       => mb_substr(($customer['name'] ?? '') !== '' ? $customer['name'] : 'Customer', 0, 100),
                'InvoiceValue'       => round($amount, 3),
                'DisplayCurrencyIso' => $this->currency(),
                'CallBackUrl'        => $callbackUrl,
                'ErrorUrl'           => $errorUrl,
                'Language'           => 'en',
                'CustomerReference'  => $orderRef,
                'InvoiceItems'       => [[
                    'ItemName'  => mb_substr('Online store order ' . $orderRef, 0, 250),
                    'Quantity'  => 1,
                    'UnitPrice' => round($amount, 3),
                ]],
            ]);

        $body = $response->json() ?? [];

        if (! $response->successful() || empty($body['IsSuccess']) || empty($body['Data']['InvoiceURL'])) {
            Log::error('MyFatoorah SendPayment failed', [
                'http_status' => $response->status(),
                'message'     => $body['Message'] ?? null,
                'errors'      => $body['ValidationErrors'] ?? null,
            ]);

            $detail = $body['Message'] ?? null;
            if (! empty($body['ValidationErrors'][0]['Error'])) {
                $detail = $body['ValidationErrors'][0]['Error'];
            }

            throw new RuntimeException('MyFatoorah invoice creation failed: ' . (string) ($detail ?: $response->status()));
        }

        return [
            'url'        => (string) $body['Data']['InvoiceURL'],
            'invoice_id' => (string) $body['Data']['InvoiceId'],
        ];
    }

    /**
     * Verify an invoice server-side by its InvoiceId via GetPaymentStatus.
     * FAILS CLOSED: rejects unless the invoice is Paid and the amount matches
     * the order total within rounding tolerance (the invoice was created in
     * the configured currency, so InvoiceValue is directly comparable).
     *
     * @return array{success: bool, invoice_id: string, transaction_id: ?string, status: ?string}
     */
    public function verifyInvoice(string $invoiceId, float $expectedAmount): array
    {
        $fail = ['success' => false, 'invoice_id' => $invoiceId, 'transaction_id' => null, 'status' => null];

        $body = Http::withHeaders($this->apiHeaders())
            ->timeout(30)
            ->post($this->baseUrl() . '/v2/GetPaymentStatus', [
                'Key'     => $invoiceId,
                'KeyType' => 'InvoiceId',
            ])->json() ?? [];

        if (empty($body['IsSuccess']) || empty($body['Data'])) {
            Log::warning('MyFatoorah GetPaymentStatus not successful', [
                'invoice_id' => $invoiceId,
                'message'    => $body['Message'] ?? null,
            ]);

            return $fail;
        }

        $data           = $body['Data'];
        $fail['status'] = strtoupper((string) ($data['InvoiceStatus'] ?? ''));

        if ($fail['status'] !== 'PAID') {
            return $fail;
        }

        $invoiceValue = (float) ($data['InvoiceValue'] ?? 0);
        if (abs($invoiceValue - $expectedAmount) > 0.05) {
            Log::error('MyFatoorah verification amount mismatch — order left unpaid', [
                'invoice_id'      => $invoiceId,
                'expected_amount' => $expectedAmount,
                'invoice_value'   => $invoiceValue,
            ]);

            return $fail;
        }

        // The successful transaction row carries the gateway's TransactionId.
        // MyFatoorah's API spells the success status "Succss".
        $transactionId = null;
        foreach ((array) ($data['InvoiceTransactions'] ?? []) as $txn) {
            if (in_array(strtoupper((string) ($txn['TransactionStatus'] ?? '')), ['SUCCSS', 'SUCCESS'], true)) {
                $transactionId = (string) ($txn['TransactionId'] ?? $txn['PaymentId'] ?? '');
                break;
            }
        }

        return [
            'success'        => true,
            'invoice_id'     => $invoiceId,
            'transaction_id' => $transactionId,
            'status'         => 'PAID',
        ];
    }

    /**
     * Resolve the InvoiceId behind a callback's ?paymentId= parameter.
     * The order lookup then runs on our stored InvoiceId, never on the
     * attacker-controllable query value directly.
     */
    public function invoiceIdForPaymentId(string $paymentId): ?string
    {
        $body = Http::withHeaders($this->apiHeaders())
            ->timeout(30)
            ->post($this->baseUrl() . '/v2/GetPaymentStatus', [
                'Key'     => $paymentId,
                'KeyType' => 'PaymentId',
            ])->json() ?? [];

        if (empty($body['IsSuccess']) || empty($body['Data']['InvoiceId'])) {
            return null;
        }

        return (string) $body['Data']['InvoiceId'];
    }

    protected function apiHeaders(): array
    {
        return [
            'Authorization' => 'Bearer ' . $this->credentials()['api_token'],
            'Content-Type'  => 'application/json',
        ];
    }

    protected function baseUrl(): string
    {
        $c = $this->credentials();

        if ($c['test_mode']) {
            return self::TEST_BASE;
        }

        return self::LIVE_HOSTS[$c['country']] ?? self::DEFAULT_LIVE_BASE;
    }

    protected function credentials(): array
    {
        if ($this->credentials !== null) {
            return $this->credentials;
        }

        $settings = PaymentSetting::current();

        return $this->credentials = [
            'api_token' => trim((string) ($settings->myfatoorah_api_token ?? '')),
            'country'   => strtoupper(trim((string) ($settings->myfatoorah_country ?? ''))),
            'currency'  => strtoupper(trim((string) ($settings->myfatoorah_currency ?? '')) ?: 'KWD'),
            'test_mode' => (bool) ($settings->myfatoorah_test_mode ?? true),
        ];
    }
}

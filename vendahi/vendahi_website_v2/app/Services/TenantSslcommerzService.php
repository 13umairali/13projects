<?php

namespace App\Services;

use App\Models\PaymentSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * SSLCommerz (hosted checkout, gwprocess v4) client for tenant-facing
 * payments (storefront checkout).
 *
 * Uses the tenant's own SSLCommerz merchant account: credentials come
 * exclusively from the tenant DB `payment_settings` row. Charges in BDT.
 *
 * Flow: pending order → server-side session create with a self-generated
 * tran_id → buyer redirected to the GatewayPageURL → SSLCommerz POSTs the
 * browser to the success/fail/cancel URLs and (when configured) sends a
 * server-to-server IPN. The POSTed fields are NEVER trusted: paid is only
 * written after the validation API confirms the val_id, with tran_id and
 * amount cross-checked ("never act on the payload — always re-verify",
 * same rule as LigdiCash).
 */
class TenantSslcommerzService
{
    protected const LIVE_BASE    = 'https://securepay.sslcommerz.com';
    protected const SANDBOX_BASE = 'https://sandbox.sslcommerz.com';

    protected ?array $credentials = null;

    public function isConfigured(): bool
    {
        $c = $this->credentials();

        return $c['store_id'] !== '' && $c['store_password'] !== '';
    }

    /**
     * Create a payment session and return the hosted checkout URL + the
     * self-generated tran_id (the unguessable reference the callbacks and
     * IPN resolve the pending order by).
     *
     * @param  array{name: string, email: string, phone: string, address: string}  $customer
     * @return array{url: string, tran_id: string}
     */
    public function initializeSession(
        float $amount,
        string $orderRef,
        array $customer,
        string $successUrl,
        string $failUrl,
        string $cancelUrl,
        string $ipnUrl
    ): array {
        $c = $this->credentials();

        if (! $this->isConfigured()) {
            throw new RuntimeException('SSLCommerz is not configured for this tenant.');
        }

        $tranId = 'sslc_' . bin2hex(random_bytes(16));

        $response = Http::asForm()->timeout(30)->post($this->baseUrl() . '/gwprocess/v4/api.php', [
            'store_id'         => $c['store_id'],
            'store_passwd'     => $c['store_password'],
            'total_amount'     => number_format($amount, 2, '.', ''),
            'currency'         => 'BDT',
            'tran_id'          => $tranId,
            'success_url'      => $successUrl,
            'fail_url'         => $failUrl,
            'cancel_url'       => $cancelUrl,
            'ipn_url'          => $ipnUrl,
            'emi_option'       => 0,
            'cus_name'         => mb_substr($customer['name'] !== '' ? $customer['name'] : 'Customer', 0, 100),
            'cus_email'        => mb_substr($customer['email'] !== '' ? $customer['email'] : 'customer@example.com', 0, 100),
            'cus_add1'         => mb_substr($customer['address'] !== '' ? $customer['address'] : 'N/A', 0, 100),
            'cus_city'         => 'N/A',
            'cus_postcode'     => 'N/A',
            'cus_country'      => 'Bangladesh',
            'cus_phone'        => mb_substr($customer['phone'] !== '' ? $customer['phone'] : 'N/A', 0, 20),
            'shipping_method'  => 'NO',
            'num_of_item'      => 1,
            'product_name'     => mb_substr('Online store order ' . $orderRef, 0, 255),
            'product_category' => 'General',
            'product_profile'  => 'general',
            // value_a is echoed back on the callbacks/IPN — the order lookup.
            'value_a'          => $orderRef,
        ]);

        $body = $response->json() ?? [];
        if (! $response->successful() || strtoupper((string) ($body['status'] ?? '')) !== 'SUCCESS' || empty($body['GatewayPageURL'])) {
            Log::error('SSLCommerz session init failed', [
                'reason'   => $body['failedreason'] ?? null,
                'response' => $body,
            ]);
            throw new RuntimeException('SSLCommerz session creation failed: ' . (string) ($body['failedreason'] ?? $response->status()));
        }

        return [
            'url'     => (string) $body['GatewayPageURL'],
            'tran_id' => $tranId,
        ];
    }

    /**
     * Validate a transaction server-side by val_id (validationserverAPI).
     * FAILS CLOSED: rejects unless status is VALID/VALIDATED, the validated
     * tran_id matches the order's stored one (a replayed val_id from a
     * different, cheaper transaction is rejected there), the currency is BDT
     * and the amount matches the order total within rounding tolerance.
     *
     * @return array{success: bool, tran_id: string, val_id: ?string, bank_tran_id: ?string, status: ?string}
     */
    public function validateTransaction(string $valId, string $expectedTranId, float $expectedAmount): array
    {
        $c = $this->credentials();

        $response = Http::timeout(30)->get($this->baseUrl() . '/validator/api/validationserverAPI.php', [
            'val_id'       => $valId,
            'store_id'     => $c['store_id'],
            'store_passwd' => $c['store_password'],
            'format'       => 'json',
            'v'            => 1,
        ]);

        $body = $response->json() ?? [];
        $status = strtoupper((string) ($body['status'] ?? ''));
        $fail = ['success' => false, 'tran_id' => $expectedTranId, 'val_id' => $valId, 'bank_tran_id' => null, 'status' => $status ?: null];

        if (! $response->successful() || ! in_array($status, ['VALID', 'VALIDATED'], true)) {
            Log::warning('SSLCommerz validation not VALID', ['val_id' => $valId, 'response' => $body]);

            return $fail;
        }

        if ((string) ($body['tran_id'] ?? '') !== $expectedTranId) {
            Log::error('SSLCommerz validation tran_id mismatch — order left unpaid', [
                'expected'  => $expectedTranId,
                'validated' => $body['tran_id'] ?? null,
            ]);

            return $fail;
        }

        $validatedCurrency = strtoupper((string) ($body['currency_type'] ?? $body['currency'] ?? ''));
        $validatedAmount   = (float) ($body['currency_amount'] ?? $body['amount'] ?? 0);
        if ($validatedCurrency !== 'BDT' || abs($validatedAmount - $expectedAmount) > 0.05) {
            Log::error('SSLCommerz validation currency/amount mismatch — order left unpaid', [
                'validated_currency' => $validatedCurrency,
                'expected_amount'    => $expectedAmount,
                'validated_amount'   => $validatedAmount,
                'tran_id'            => $expectedTranId,
            ]);

            return $fail;
        }

        return [
            'success'      => true,
            'tran_id'      => (string) $body['tran_id'],
            'val_id'       => (string) ($body['val_id'] ?? $valId),
            'bank_tran_id' => isset($body['bank_tran_id']) ? (string) $body['bank_tran_id'] : null,
            'status'       => $status,
        ];
    }

    /**
     * Validate by tran_id (merchantTransIDvalidationAPI) — the recovery path
     * when a callback carries no val_id (lost redirect, retried return).
     *
     * @return array{success: bool, tran_id: string, val_id: ?string, bank_tran_id: ?string, status: ?string}
     */
    public function validateByTranId(string $tranId, float $expectedAmount): array
    {
        $c = $this->credentials();

        $response = Http::timeout(30)->get($this->baseUrl() . '/validator/api/merchantTransIDvalidationAPI.php', [
            'tran_id'      => $tranId,
            'store_id'     => $c['store_id'],
            'store_passwd' => $c['store_password'],
            'format'       => 'json',
        ]);

        $body = $response->json() ?? [];
        foreach ((array) ($body['element'] ?? []) as $trans) {
            $status = strtoupper((string) ($trans['status'] ?? ''));
            if (in_array($status, ['VALID', 'VALIDATED'], true) && ! empty($trans['val_id'])) {
                // Re-validate through the primary API so the tran_id/amount
                // cross-checks always run on the same path.
                return $this->validateTransaction((string) $trans['val_id'], $tranId, $expectedAmount);
            }
        }

        return ['success' => false, 'tran_id' => $tranId, 'val_id' => null, 'bank_tran_id' => null, 'status' => null];
    }

    /**
     * Authenticate an incoming IPN's verify_sign: md5 over the alphabetically
     * sorted fields named in verify_key plus store_passwd=md5(store password).
     * This only authenticates the POST — a VALID status must still be
     * confirmed through validateTransaction() before anything is marked paid.
     */
    public function checkIpnSignature(array $post): bool
    {
        $sign = strtolower((string) ($post['verify_sign'] ?? ''));
        $keys = array_filter(explode(',', (string) ($post['verify_key'] ?? '')));
        if ($sign === '' || ! $keys) {
            return false;
        }

        $fields = [];
        foreach ($keys as $key) {
            $fields[$key] = (string) ($post[$key] ?? '');
        }
        $fields['store_passwd'] = md5($this->credentials()['store_password']);
        ksort($fields);

        $pairs = [];
        foreach ($fields as $key => $value) {
            $pairs[] = $key . '=' . $value;
        }

        return hash_equals(md5(implode('&', $pairs)), $sign);
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
            'store_id'       => trim((string) ($settings->sslcommerz_store_id ?? '')),
            'store_password' => trim((string) ($settings->sslcommerz_store_password ?? '')),
            'test_mode'      => (bool) ($settings->sslcommerz_test_mode ?? true),
        ];
    }
}

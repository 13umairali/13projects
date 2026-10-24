<?php

namespace App\Services;

use App\Models\OnlineOrder;
use App\Models\PaymentSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * PayFast client for tenant-facing payments (storefront checkout).
 *
 * Uses the tenant's own PayFast merchant account: credentials come
 * exclusively from the tenant DB `payment_settings` row (no .env fallback).
 * The platform's PayFast account for subscription billing lives in
 * App\Services\PaymentGateways\PayfastGateway.
 *
 * Flow: the order is created as pending, the buyer is redirected to a signed
 * /eng/process URL, and PayFast's ITN callback (verified by signature plus a
 * server-side /eng/query/validate confirmation) marks the order paid. ZAR only.
 */
class TenantPayfastService
{
    protected const LIVE_HOST    = 'https://www.payfast.co.za';
    protected const SANDBOX_HOST = 'https://sandbox.payfast.co.za';

    protected ?array $credentials = null;

    public function isConfigured(): bool
    {
        $c = $this->credentials();

        return ! empty($c['merchant_id']) && ! empty($c['merchant_key']);
    }

    /**
     * Build the signed redirect URL that sends the buyer to PayFast.
     */
    public function buildRedirectUrl(OnlineOrder $order, string $returnUrl, string $cancelUrl, string $notifyUrl): string
    {
        $c = $this->credentials();

        if (! $this->isConfigured()) {
            throw new \RuntimeException('PayFast is not configured for this tenant.');
        }

        // Field order matters: the signature is computed over the non-empty
        // fields in PayFast's prescribed attribute order.
        $fields = array_filter([
            'merchant_id'  => $c['merchant_id'],
            'merchant_key' => $c['merchant_key'],
            'return_url'   => $returnUrl,
            'cancel_url'   => $cancelUrl,
            'notify_url'   => $notifyUrl,
            'm_payment_id' => $order->ref,
            'amount'       => number_format((float) $order->total, 2, '.', ''),
            'item_name'    => mb_substr('Order ' . $order->ref, 0, 100),
        ], fn ($v) => $v !== null && $v !== '');

        $fields['signature'] = $this->generateSignature($fields, $c['passphrase']);

        return $this->host() . '/eng/process?' . http_build_query($fields);
    }

    /**
     * Verify an ITN payload: signature check plus the authoritative
     * server-side confirmation against /eng/query/validate.
     *
     * @param array $data parsed ITN POST fields, in received order
     */
    public function verifyItn(array $data): bool
    {
        if (empty($data) || empty($data['signature'])) {
            return false;
        }

        $c = $this->credentials();

        $pairs = [];
        foreach ($data as $key => $value) {
            if ($key === 'signature') {
                continue;
            }
            $pairs[] = $key . '=' . urlencode(trim((string) $value));
        }
        $paramString = implode('&', $pairs);

        $signedString = $paramString;
        if ($c['passphrase'] !== '') {
            $signedString .= '&passphrase=' . urlencode($c['passphrase']);
        }

        if (! hash_equals(md5($signedString), strtolower((string) $data['signature']))) {
            Log::error('Storefront PayFast ITN signature mismatch');
            return false;
        }

        try {
            $response = Http::withBody($paramString, 'application/x-www-form-urlencoded')
                ->timeout(15)
                ->post($this->host() . '/eng/query/validate');

            if (! $response->successful() || stripos(trim($response->body()), 'VALID') !== 0) {
                Log::error('Storefront PayFast ITN server validation failed', ['body' => trim($response->body())]);
                return false;
            }
        } catch (\Throwable $e) {
            Log::error('Storefront PayFast ITN server validation request failed: ' . $e->getMessage());
            return false;
        }

        return true;
    }

    protected function generateSignature(array $fields, string $passphrase): string
    {
        $pairs = [];
        foreach ($fields as $key => $value) {
            $pairs[] = $key . '=' . urlencode(trim((string) $value));
        }

        $paramString = implode('&', $pairs);

        if ($passphrase !== '') {
            $paramString .= '&passphrase=' . urlencode($passphrase);
        }

        return md5($paramString);
    }

    protected function host(): string
    {
        return $this->credentials()['sandbox'] ? self::SANDBOX_HOST : self::LIVE_HOST;
    }

    protected function credentials(): array
    {
        if ($this->credentials !== null) {
            return $this->credentials;
        }

        $settings = PaymentSetting::current();

        return $this->credentials = [
            'merchant_id'  => trim((string) ($settings->payfast_merchant_id ?? '')),
            'merchant_key' => trim((string) ($settings->payfast_merchant_key ?? '')),
            'passphrase'   => trim((string) ($settings->payfast_passphrase ?? '')),
            'sandbox'      => (bool) ($settings->payfast_sandbox ?? false),
        ];
    }
}

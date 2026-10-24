<?php

namespace App\Services;

use App\Models\PaymentSetting;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * LigdiCash client for tenant-facing payments (storefront checkout).
 *
 * Uses the tenant's own LigdiCash account: credentials come exclusively from
 * the tenant DB `payment_settings` row (no .env fallback). The platform's
 * LigdiCash account for subscription billing lives in
 * App\Services\PaymentGateways\LigdicashGateway.
 *
 * Flow: the order is created as pending, a payin-redirect invoice is created
 * (returns a hosted payment URL + invoice token stored on the order), and the
 * unsigned callback only triggers a server-side re-verification of the stored
 * token via the confirm endpoint — never trusted directly. XOF only.
 */
class TenantLigdicashService
{
    protected const API_BASE = 'https://app.ligdicash.com/pay/v01';

    protected ?array $credentials = null;

    public function isConfigured(): bool
    {
        $c = $this->credentials();

        return ! empty($c['api_key']) && ! empty($c['auth_token']);
    }

    /**
     * Create a payin-redirect invoice for a storefront order.
     *
     * @return array{token: string, url: string}
     */
    public function createInvoice(
        float $amount,
        string $orderRef,
        string $storeName,
        string $returnUrl,
        string $cancelUrl,
        string $callbackUrl
    ): array {
        // XOF has no decimal subunit.
        $amountInt = (int) round($amount);

        $payload = [
            'commande' => [
                'invoice' => [
                    'items' => [[
                        'name'        => mb_substr('Order ' . $orderRef, 0, 100),
                        'description' => mb_substr('Online store order ' . $orderRef, 0, 255),
                        'quantity'    => 1,
                        'unit_price'  => $amountInt,
                        'total_price' => $amountInt,
                    ]],
                    'total_amount'       => $amountInt,
                    'devise'             => 'XOF',
                    'description'        => mb_substr('Order ' . $orderRef, 0, 255),
                    'customer'           => '',
                    'customer_firstname' => '',
                    'customer_lastname'  => '',
                    'customer_email'     => '',
                    'external_id'        => '',
                    'otp'                => '',
                ],
                'store' => [
                    'name'        => mb_substr($storeName, 0, 100),
                    'website_url' => url('/'),
                ],
                'actions' => [
                    'cancel_url'   => $cancelUrl,
                    'return_url'   => $returnUrl,
                    'callback_url' => $callbackUrl,
                ],
                'custom_data' => [
                    'order_ref' => $orderRef,
                ],
            ],
        ];

        $response = $this->request()->post(self::API_BASE . '/redirect/checkout-invoice/create', $payload);

        $body = $response->json() ?? [];

        if (! $response->successful()
            || ($body['response_code'] ?? '') !== '00'
            || empty($body['token'])
            || empty($body['response_text'])
            || ! str_starts_with((string) $body['response_text'], 'http')) {
            throw new RuntimeException(
                'LigdiCash invoice creation failed: ' . ($body['response_text'] ?? $response->status())
            );
        }

        return [
            'token' => (string) $body['token'],
            'url'   => (string) $body['response_text'],
        ];
    }

    /**
     * Confirm an invoice's status by its stored token.
     *
     * @return array{status: string, amount: ?float, request_id: string}
     *   status: 'completed', 'pending', 'failed', or 'unknown'
     */
    public function confirmByToken(string $invoiceToken): array
    {
        $response = $this->request()->get(self::API_BASE . '/redirect/checkout-invoice/confirm', [
            'invoiceToken' => $invoiceToken,
        ]);

        if ($response->failed()) {
            throw new RuntimeException('LigdiCash confirm failed: ' . $response->status());
        }

        $data = $response->json() ?? [];

        $status = strtolower($data['status'] ?? '');
        if ($status === 'nocompleted' || $status === 'notcompleted') {
            $status = 'failed';
        } elseif (! in_array($status, ['completed', 'pending'], true)) {
            $status = 'unknown';
        }

        return [
            'status'     => $status,
            'amount'     => isset($data['montant']) ? (float) $data['montant'] : null,
            'request_id' => (string) ($data['request_id'] ?? ''),
        ];
    }

    /**
     * custom_data arrives either as an associative map or as a list of
     * {keyof_customdata, valueof_customdata} pairs — normalize to a map.
     */
    public function normalizeCustomData($customData): array
    {
        if (! is_array($customData)) {
            return [];
        }

        $map = [];
        foreach ($customData as $key => $value) {
            if (is_array($value) && isset($value['keyof_customdata'])) {
                $map[(string) $value['keyof_customdata']] = (string) ($value['valueof_customdata'] ?? '');
            } elseif (! is_array($value)) {
                $map[(string) $key] = (string) $value;
            }
        }

        return $map;
    }

    protected function request(): \Illuminate\Http\Client\PendingRequest
    {
        $c = $this->credentials();

        if (! $this->isConfigured()) {
            throw new RuntimeException('LigdiCash is not configured for this tenant.');
        }

        return Http::withHeaders(['Apikey' => $c['api_key']])
            ->withToken($c['auth_token'])
            ->acceptJson()
            ->timeout(30);
    }

    protected function credentials(): array
    {
        if ($this->credentials !== null) {
            return $this->credentials;
        }

        $settings = PaymentSetting::current();

        return $this->credentials = [
            'api_key'    => trim((string) ($settings->ligdicash_api_key ?? '')),
            'auth_token' => trim((string) ($settings->ligdicash_auth_token ?? '')),
        ];
    }
}

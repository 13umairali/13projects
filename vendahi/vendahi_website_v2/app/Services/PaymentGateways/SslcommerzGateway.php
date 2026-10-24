<?php

namespace App\Services\PaymentGateways;

use App\Models\Central\TenantBillingPayment;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;

/**
 * SSLCommerz gateway (Bangladesh — cards, bKash/Nagad/Rocket mobile banking,
 * internet banking; BDT).
 *
 * Uses the platform's own SSLCommerz merchant account (gwprocess v4 hosted
 * checkout) for subscription billing: a server-side session create with a
 * self-generated tran_id returns a GatewayPageURL to redirect the customer
 * to, which fits the redirect()->away() flow.
 *
 * SSLCommerz POSTs the payer's browser back to success/fail/cancel URLs, so
 * those are wrapped in the signed, sessionless /gateway/sslcommerz/bridge
 * route (see RouteServiceProvider::mapUniversalRoutes) which converts the
 * cross-site POST into a plain GET redirect — the app's GET-only, session-
 * authenticated pages are never hit with a POST and the payer's session
 * cookie is never clobbered.
 *
 * Confirmation is IPN + return-leg driven and always re-verified: the IPN is
 * authenticated by its verify_sign MD5, and a VALID status is additionally
 * confirmed through the validation API ("never act on the payload — always
 * re-verify", same rule as LigdiCash). Sandbox vs production host is
 * controlled by the gateway's test_mode setting.
 */
class SslcommerzGateway implements PaymentGatewayInterface
{
    use HasGatewaySettings;

    protected const LIVE_BASE    = 'https://securepay.sslcommerz.com';
    protected const SANDBOX_BASE = 'https://sandbox.sslcommerz.com';

    public function getKey(): string
    {
        return 'sslcommerz';
    }

    protected function getRequiredCredentialKeys(): array
    {
        return ['store_id', 'store_passwd'];
    }

    public function isAvailable(): bool
    {
        return $this->isGatewayAvailable();
    }

    public function getDisplayInfo(): array
    {
        return [
            'key'         => 'sslcommerz',
            'label'       => 'SSLCommerz',
            'description' => 'Accept cards, bKash/Nagad/Rocket and internet banking in Bangladesh (BDT).',
            'icon'        => 'bi-shield-check',
            'color'       => '#2e3192',
        ];
    }

    protected function baseUrl(): string
    {
        return $this->isTestMode() ? self::SANDBOX_BASE : self::LIVE_BASE;
    }

    // ──────────────────────────────────────────────
    //  Hosted checkout
    // ──────────────────────────────────────────────

    public function createCheckoutSession(
        TenantBillingPayment $payment,
        string $successUrl,
        string $cancelUrl
    ): string {
        $chargeAmount = $payment->gateway_amount ?? $payment->amount;

        $result = $this->createCheckoutUrl(
            amount: (float) $chargeAmount,
            currency: $payment->gateway_currency ?? $payment->currency,
            productName: ($payment->plan->name ?? 'Subscription') . ' Plan',
            description: ucfirst($payment->billing_cycle) . ' subscription',
            metadata: [
                'payment_id' => $payment->id,
                'tenant_id'  => $payment->tenant_id,
            ],
            successUrl: $successUrl,
            cancelUrl: $cancelUrl,
        );

        $payment->update(['gateway_payment_id' => $result['session_id']]);

        return $result['url'];
    }

    /**
     * Create a hosted-checkout session.
     *
     * Returns ['url' => <GatewayPageURL>, 'session_id' => <tran_id>]. The
     * self-generated tran_id is the unguessable reference verifyTransaction()
     * and the IPN resolve/cross-check by.
     */
    public function createCheckoutUrl(
        float $amount,
        string $currency,
        string $productName,
        string $description,
        array $metadata,
        string $successUrl,
        string $cancelUrl
    ): array {
        $this->loadSettings();

        $tranId = 'sslc_' . bin2hex(random_bytes(16));

        $response = Http::asForm()->timeout(30)->post($this->baseUrl() . '/gwprocess/v4/api.php', [
            'store_id'         => $this->resolvedCredentials['store_id'] ?? '',
            'store_passwd'     => $this->resolvedCredentials['store_passwd'] ?? '',
            'total_amount'     => number_format($amount, 2, '.', ''),
            'currency'         => strtoupper($currency ?: 'BDT'),
            'tran_id'          => $tranId,
            // The browser is POSTed back cross-site; route it through the
            // signed sessionless bridge which 302s to the real page.
            'success_url'      => $this->bridge($successUrl),
            'fail_url'         => $this->bridge($cancelUrl),
            'cancel_url'       => $this->bridge($cancelUrl),
            // The /webhook/{gateway} route only exists on the CENTRAL domain;
            // tenant-billing checkouts run on tenant hosts, so the IPN target
            // is built from app.url (the central app), never the current host.
            'ipn_url'          => rtrim(config('app.url'), '/') . '/webhook/sslcommerz',
            'emi_option'       => 0,
            'cus_name'         => 'Subscriber',
            'cus_email'        => 'billing@' . (parse_url(config('app.url'), PHP_URL_HOST) ?: 'example.com'),
            'cus_add1'         => 'N/A',
            'cus_city'         => 'N/A',
            'cus_postcode'     => 'N/A',
            'cus_country'      => 'Bangladesh',
            'cus_phone'        => 'N/A',
            'shipping_method'  => 'NO',
            'num_of_item'      => 1,
            'product_name'     => mb_substr($productName . ' — ' . $description, 0, 255),
            'product_category' => 'Subscription',
            'product_profile'  => 'non-physical-goods',
            // Echoed back on the IPN — how the webhook routes the event.
            'value_a'          => (string) ($metadata['payment_id'] ?? ''),
            'value_b'          => (string) ($metadata['registration_id'] ?? ''),
            'value_c'          => (string) ($metadata['tenant_id'] ?? ''),
        ]);

        $body = $response->json() ?? [];

        if (! $response->successful() || strtoupper((string) ($body['status'] ?? '')) !== 'SUCCESS' || empty($body['GatewayPageURL'])) {
            Log::error('SSLCommerz session init failed', [
                'http_status' => $response->status(),
                'reason'      => $body['failedreason'] ?? null,
            ]);

            throw new \RuntimeException('SSLCommerz: ' . (string) ($body['failedreason'] ?? ('HTTP ' . $response->status())));
        }

        return [
            'url'        => (string) $body['GatewayPageURL'],
            'session_id' => $tranId,
        ];
    }

    /** Signed sessionless bridge URL that 302s the gateway's POST to $target. */
    protected function bridge(string $target): string
    {
        return URL::temporarySignedRoute('gateway.sslcommerz.bridge', now()->addHours(6), ['to' => $target]);
    }

    // ──────────────────────────────────────────────
    //  Transaction verification
    // ──────────────────────────────────────────────

    /**
     * Verify a transaction server-side by our tran_id via the merchant
     * validation API, then re-confirm the found val_id through the primary
     * validation API (fails closed on a tran_id mismatch — a replayed val_id
     * from a different transaction is rejected there).
     *
     * @return array{success: bool, status: string, transaction_id: ?string, amount: ?float, currency: ?string}
     */
    public function verifyTransaction(string $tranId): array
    {
        $this->loadSettings();

        $fail = ['success' => false, 'status' => '', 'transaction_id' => null, 'amount' => null, 'currency' => null];

        try {
            $body = Http::timeout(30)->get($this->baseUrl() . '/validator/api/merchantTransIDvalidationAPI.php', [
                'tran_id'      => $tranId,
                'store_id'     => $this->resolvedCredentials['store_id'] ?? '',
                'store_passwd' => $this->resolvedCredentials['store_passwd'] ?? '',
                'format'       => 'json',
            ])->json() ?? [];
        } catch (\Throwable $e) {
            Log::warning("SSLCommerz merchant validation threw for {$tranId}: {$e->getMessage()}");

            return $fail;
        }

        $lastStatus = '';
        foreach ((array) ($body['element'] ?? []) as $trans) {
            $lastStatus = strtoupper((string) ($trans['status'] ?? ''));
            if (in_array($lastStatus, ['VALID', 'VALIDATED'], true) && ! empty($trans['val_id'])) {
                return $this->validateByValId((string) $trans['val_id'], $tranId);
            }
        }

        return ['success' => false, 'status' => $lastStatus, 'transaction_id' => null, 'amount' => null, 'currency' => null];
    }

    /**
     * Primary validation API by val_id, cross-checked against our tran_id.
     *
     * @return array{success: bool, status: string, transaction_id: ?string, amount: ?float, currency: ?string}
     */
    protected function validateByValId(string $valId, string $expectedTranId): array
    {
        $this->loadSettings();

        $fail = ['success' => false, 'status' => '', 'transaction_id' => $valId, 'amount' => null, 'currency' => null];

        try {
            $body = Http::timeout(30)->get($this->baseUrl() . '/validator/api/validationserverAPI.php', [
                'val_id'       => $valId,
                'store_id'     => $this->resolvedCredentials['store_id'] ?? '',
                'store_passwd' => $this->resolvedCredentials['store_passwd'] ?? '',
                'format'       => 'json',
                'v'            => 1,
            ])->json() ?? [];
        } catch (\Throwable $e) {
            Log::warning("SSLCommerz validation threw for {$valId}: {$e->getMessage()}");

            return $fail;
        }

        $status = strtoupper((string) ($body['status'] ?? ''));
        $fail['status'] = $status;

        if (! in_array($status, ['VALID', 'VALIDATED'], true)) {
            return $fail;
        }

        if ((string) ($body['tran_id'] ?? '') !== $expectedTranId) {
            Log::error('SSLCommerz validation tran_id mismatch — rejected', [
                'expected'  => $expectedTranId,
                'validated' => $body['tran_id'] ?? null,
            ]);

            return $fail;
        }

        return [
            'success'        => true,
            'status'         => $status,
            'transaction_id' => (string) ($body['val_id'] ?? $valId),
            // currency_amount/currency_type describe the charge in the
            // currency the session was opened with — what the amount
            // anti-tamper checks compare against.
            'amount'         => isset($body['currency_amount']) ? (float) $body['currency_amount']
                : (isset($body['amount']) ? (float) $body['amount'] : null),
            'currency'       => isset($body['currency_type']) ? strtoupper((string) $body['currency_type'])
                : (isset($body['currency']) ? strtoupper((string) $body['currency']) : null),
        ];
    }

    public function verifyPaymentStatus(string $sessionId): array
    {
        $verification = $this->verifyTransaction($sessionId);

        $status = match (true) {
            $verification['success'] => 'paid',
            in_array($verification['status'], ['FAILED', 'CANCELLED', 'EXPIRED'], true) => 'failed',
            default => 'pending',
        };

        return [
            'status'             => $status,
            'gateway_payment_id' => $sessionId,
            'transaction_id'     => $verification['transaction_id'],
        ];
    }

    // ──────────────────────────────────────────────
    //  IPN verification (verify_sign + validation API)
    // ──────────────────────────────────────────────

    public function verifyWebhook(string $payload, string $signature): array
    {
        $result = [
            'valid'              => false,
            'event_type'         => '',
            'gateway_payment_id' => '',
            'transaction_id'     => '',
            'payment_id'         => null,
            'tenant_id'          => null,
            'registration_id'    => null,
            'status'             => 'unknown',
        ];

        parse_str($payload, $post);
        if (! is_array($post) || empty($post['tran_id'])) {
            return $result;
        }

        // Authentication gate: the verify_sign MD5 carried inside the body
        // (SSLCommerz has no signature header).
        if (! $this->checkIpnSignature($post)) {
            Log::error('SSLCommerz IPN rejected: verify_sign mismatch', ['tran_id' => $post['tran_id']]);

            return $result;
        }

        $ipnStatus = strtoupper((string) ($post['status'] ?? ''));
        $tranId    = (string) $post['tran_id'];

        $result['valid']              = true;
        $result['event_type']         = 'sslcommerz.' . strtolower($ipnStatus ?: 'notification');
        $result['gateway_payment_id'] = $tranId;
        $result['payment_id']         = ! empty($post['value_a']) ? (int) $post['value_a'] : null;
        $result['registration_id']    = ! empty($post['value_b']) ? (int) $post['value_b'] : null;
        $result['tenant_id']          = ! empty($post['value_c']) ? (string) $post['value_c'] : null;

        if (in_array($ipnStatus, ['VALID', 'VALIDATED'], true)) {
            // Never act on the posted status: re-confirm through the
            // validation API (which also cross-checks the tran_id).
            $verification = ! empty($post['val_id'])
                ? $this->validateByValId((string) $post['val_id'], $tranId)
                : $this->verifyTransaction($tranId);

            if ($verification['success']) {
                $result['status']         = 'paid';
                $result['transaction_id'] = (string) $verification['transaction_id'];
                $result['amount']         = $verification['amount'];
                $result['currency']       = $verification['currency'];
            }
        } elseif (in_array($ipnStatus, ['FAILED', 'CANCELLED', 'EXPIRED'], true)) {
            $result['status'] = 'failed';
        }

        return $result;
    }

    /**
     * SSLCommerz's IPN signature: md5 over the alphabetically sorted fields
     * named in verify_key plus store_passwd=md5(store password).
     */
    protected function checkIpnSignature(array $post): bool
    {
        $this->loadSettings();

        $sign = strtolower((string) ($post['verify_sign'] ?? ''));
        $keys = array_filter(explode(',', (string) ($post['verify_key'] ?? '')));
        if ($sign === '' || ! $keys) {
            return false;
        }

        $fields = [];
        foreach ($keys as $key) {
            $fields[$key] = (string) ($post[$key] ?? '');
        }
        $fields['store_passwd'] = md5($this->resolvedCredentials['store_passwd'] ?? '');
        ksort($fields);

        $pairs = [];
        foreach ($fields as $key => $value) {
            $pairs[] = $key . '=' . $value;
        }

        return hash_equals(md5(implode('&', $pairs)), $sign);
    }
}

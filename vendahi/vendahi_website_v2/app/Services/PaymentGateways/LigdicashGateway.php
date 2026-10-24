<?php

namespace App\Services\PaymentGateways;

use App\Models\Central\TenantBillingPayment;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * LigdiCash gateway (Burkina Faso / West Africa — Orange Money, Moov, cards).
 *
 * Uses the platform's own LigdiCash account for subscription billing.
 * Checkout is a hosted "payin redirect" invoice
 * (POST /pay/v01/redirect/checkout-invoice/create → payment URL), which fits
 * the redirect()->away() flow used by all other gateways. XOF only, and XOF
 * has no decimals — amounts are sent as integers.
 *
 * LigdiCash callbacks carry NO signature and an empty token; the documented
 * security model is "never act on the payload — always re-verify". So the
 * webhook handler here only uses the callback to locate our own record via
 * custom_data, then confirms the real status server-side with the invoice
 * token we stored at checkout (GET /pay/v01/redirect/checkout-invoice/confirm).
 */
class LigdicashGateway implements PaymentGatewayInterface
{
    use HasGatewaySettings;

    protected const API_BASE = 'https://app.ligdicash.com/pay/v01';

    public function getKey(): string
    {
        return 'ligdicash';
    }

    protected function getRequiredCredentialKeys(): array
    {
        return ['api_key', 'auth_token'];
    }

    public function isAvailable(): bool
    {
        return $this->isGatewayAvailable();
    }

    public function getDisplayInfo(): array
    {
        return [
            'key'         => 'ligdicash',
            'label'       => 'LigdiCash',
            'description' => 'Accept Orange Money, Moov Money, and cards in West Africa.',
            'icon'        => 'bi-wallet2',
            'color'       => '#f5821f',
        ];
    }

    protected function request(): \Illuminate\Http\Client\PendingRequest
    {
        $this->loadSettings();

        $apiKey    = trim($this->resolvedCredentials['api_key'] ?? '');
        $authToken = trim($this->resolvedCredentials['auth_token'] ?? '');

        if ($apiKey === '' || $authToken === '') {
            throw new \RuntimeException('LigdiCash credentials are not configured.');
        }

        return Http::withHeaders(['Apikey' => $apiKey])
            ->withToken($authToken)
            ->acceptJson()
            ->timeout(30);
    }

    // ──────────────────────────────────────────────
    //  Hosted checkout (payin redirect invoice)
    // ──────────────────────────────────────────────

    public function createCheckoutSession(
        TenantBillingPayment $payment,
        string $successUrl,
        string $cancelUrl
    ): string {
        $chargeAmount = $payment->gateway_amount ?? $payment->amount;

        $email = $payment->metadata['email']
            ?? auth()->user()?->email
            ?? null;

        $result = $this->createCheckoutUrl(
            amount: (float) $chargeAmount,
            currency: $payment->gateway_currency ?? $payment->currency,
            productName: ($payment->plan->name ?? 'Subscription') . ' Plan',
            description: ucfirst($payment->billing_cycle) . ' subscription',
            metadata: [
                'payment_id' => $payment->id,
                'tenant_id'  => $payment->tenant_id,
                'email'      => $email,
            ],
            successUrl: $successUrl,
            cancelUrl: $cancelUrl,
        );

        $payment->update([
            'gateway_payment_id' => $result['session_id'],
            'metadata' => array_merge($payment->metadata ?? [], [
                'ligdicash_token' => $result['session_id'],
            ]),
        ]);

        return $result['url'];
    }

    /**
     * Create a LigdiCash payin redirect invoice.
     *
     * Returns ['url' => <payment_page_url>, 'session_id' => <invoice_token>].
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
        if (strtoupper($currency) !== 'XOF') {
            throw new \RuntimeException('LigdiCash only supports XOF payments.');
        }

        // XOF has no decimal subunit.
        $amountInt = (int) round($amount);

        // Resolve customer details (registration flow has no auth user).
        $customerEmail = $metadata['email'] ?? null;
        $customerName  = $metadata['customer_name'] ?? null;

        if (! empty($metadata['registration_id'])) {
            $reg = \App\Models\Central\PendingRegistration::find($metadata['registration_id']);
            if ($reg) {
                $customerEmail = $customerEmail ?: $reg->admin_email;
                $customerName  = $customerName  ?: $reg->company_name;
            }
        }

        if (empty($customerEmail)) {
            $customerEmail = auth()->user()?->email;
        }

        $customData = array_filter([
            'payment_id'      => $metadata['payment_id'] ?? null,
            'tenant_id'       => $metadata['tenant_id'] ?? null,
            'registration_id' => $metadata['registration_id'] ?? null,
        ], fn ($v) => $v !== null);

        // The callback must reach the central domain regardless of which host
        // initiated checkout (tenant billing runs on tenant domains).
        $callbackUrl = rtrim((string) config('app.url'), '/') . '/webhook/ligdicash';

        $payload = [
            'commande' => [
                'invoice' => [
                    'items' => [[
                        'name'        => mb_substr($productName, 0, 100),
                        'description' => mb_substr($description, 0, 255),
                        'quantity'    => 1,
                        'unit_price'  => $amountInt,
                        'total_price' => $amountInt,
                    ]],
                    'total_amount'       => $amountInt,
                    'devise'             => 'XOF',
                    'description'        => mb_substr($productName . ' — ' . $description, 0, 255),
                    'customer'           => '',
                    'customer_firstname' => (string) ($customerName ?? ''),
                    'customer_lastname'  => '',
                    'customer_email'     => (string) ($customerEmail ?? ''),
                    'external_id'        => '',
                    'otp'                => '',
                ],
                'store' => [
                    'name'        => (string) config('app.name'),
                    'website_url' => (string) config('app.url'),
                ],
                'actions' => [
                    'cancel_url'   => $cancelUrl,
                    'return_url'   => $successUrl,
                    'callback_url' => $callbackUrl,
                ],
                'custom_data' => array_map(fn ($v) => (string) $v, $customData) ?: (object) [],
            ],
        ];

        try {
            $response = $this->request()->post(self::API_BASE . '/redirect/checkout-invoice/create', $payload);
        } catch (\RuntimeException $e) {
            throw $e;
        } catch (\Throwable $e) {
            Log::error('LigdiCash invoice request failed', ['error' => $e->getMessage()]);
            throw new \RuntimeException('Could not connect to LigdiCash: ' . $e->getMessage());
        }

        $body = $response->json() ?? [];

        if (! $response->successful()
            || ($body['response_code'] ?? '') !== '00'
            || empty($body['token'])
            || empty($body['response_text'])
            || ! str_starts_with((string) $body['response_text'], 'http')) {
            Log::error('LigdiCash invoice creation failed', [
                'http_status' => $response->status(),
                'response'    => $body,
            ]);

            throw new \RuntimeException('LigdiCash: ' . ($body['response_text'] ?? ('HTTP ' . $response->status())));
        }

        return [
            'url'        => (string) $body['response_text'],
            'session_id' => (string) $body['token'],
        ];
    }

    // ──────────────────────────────────────────────
    //  Transaction verification (confirm endpoint)
    // ──────────────────────────────────────────────

    /**
     * Verify an invoice's status directly with LigdiCash by its stored token.
     *
     * @return array{status: string, gateway_payment_id: ?string, transaction_id: ?string}
     */
    public function verifyPaymentStatus(string $sessionId): array
    {
        $result = ['status' => 'unknown', 'gateway_payment_id' => $sessionId, 'transaction_id' => null];

        try {
            $response = $this->request()->get(self::API_BASE . '/redirect/checkout-invoice/confirm', [
                'invoiceToken' => $sessionId,
            ]);

            if ($response->successful()) {
                $data = $response->json() ?? [];

                $result['transaction_id'] = (string) ($data['request_id'] ?? '');

                $status = strtolower($data['status'] ?? '');
                if ($status === 'completed') {
                    $result['status']   = 'paid';
                    $result['amount']   = isset($data['montant']) ? (float) $data['montant'] : null;
                    $result['currency'] = 'XOF';
                } elseif ($status === 'nocompleted' || $status === 'notcompleted') {
                    $result['status'] = 'failed';
                } elseif ($status === 'pending') {
                    $result['status'] = 'pending';
                }
            }
        } catch (\Throwable $e) {
            Log::error("LigdiCash verifyPaymentStatus failed: {$e->getMessage()}");
        }

        return $result;
    }

    // ──────────────────────────────────────────────
    //  Callback handling (no signature — re-verify by stored token)
    // ──────────────────────────────────────────────

    public function verifyWebhook(string $payload, string $signature): array
    {
        $result = [
            'valid'              => false,
            'event_type'         => 'ligdicash.callback',
            'gateway_payment_id' => '',
            'transaction_id'     => '',
            'payment_id'         => null,
            'tenant_id'          => null,
            'registration_id'    => null,
            'status'             => 'unknown',
        ];

        // LigdiCash delivers the callback twice: once as JSON and once
        // form-encoded. Accept either shape.
        $data = json_decode($payload, true);
        if (! is_array($data)) {
            parse_str($payload, $data);
        }

        if (empty($data)) {
            return $result;
        }

        $customData = $this->normalizeCustomData($data['custom_data'] ?? []);

        $paymentId      = $customData['payment_id'] ?? null;
        $registrationId = $customData['registration_id'] ?? null;

        // The callback's token is always empty and it carries no signature —
        // locate our own record and re-verify with the token stored at
        // checkout. Anything we cannot match is discarded.
        $storedToken = null;

        if ($paymentId) {
            $storedToken = TenantBillingPayment::find($paymentId)?->gateway_payment_id;
        } elseif ($registrationId) {
            $storedToken = \App\Models\Central\PendingRegistration::find($registrationId)?->gateway_session_id;
        }

        if (empty($storedToken)) {
            Log::warning('LigdiCash callback discarded: no matching stored invoice token', [
                'payment_id'      => $paymentId,
                'registration_id' => $registrationId,
            ]);
            return $result;
        }

        $verification = $this->verifyPaymentStatus($storedToken);

        if ($verification['status'] === 'unknown') {
            // Confirm endpoint unreachable — reject so LigdiCash retries.
            return $result;
        }

        $result['valid']              = true;
        $result['gateway_payment_id'] = $storedToken;
        $result['transaction_id']     = $verification['transaction_id'] ?? '';
        $result['payment_id']         = $paymentId;
        $result['tenant_id']          = $customData['tenant_id'] ?? null;
        $result['registration_id']    = $registrationId;
        $result['status']             = $verification['status'] === 'pending' ? 'unknown' : $verification['status'];

        if ($verification['status'] === 'paid') {
            $result['amount']   = $verification['amount'] ?? null;
            $result['currency'] = 'XOF';
        }

        return $result;
    }

    /**
     * custom_data arrives either as an associative map or as a list of
     * {keyof_customdata, valueof_customdata} pairs — normalize to a map.
     */
    protected function normalizeCustomData($customData): array
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
}

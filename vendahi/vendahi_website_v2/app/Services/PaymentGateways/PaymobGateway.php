<?php

namespace App\Services\PaymentGateways;

use App\Models\Central\TenantBillingPayment;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Paymob gateway (Egypt, KSA, UAE, Pakistan, Oman — cards, wallets, local
 * methods).
 *
 * Uses the modern Unified Intention API: a server-side intention create
 * (Authorization: Token <secret key>) returns a client_secret, and the payer
 * is redirected to the region's hosted unified checkout page. The account's
 * region determines the API host (api_region credential).
 *
 * Payment context travels in the self-generated special_reference
 * ('pmb-p{payment}-r{registration}-…'), which Paymob stores as the order's
 * merchant_order_id and echoes on webhooks — Paymob metadata/extras are not
 * reliably returned on callbacks.
 *
 * Webhooks (transaction processed callback) are authenticated by the HMAC
 * query parameter: SHA512 over the documented 20-field concatenation of the
 * transaction object, keyed with the account's HMAC secret. Server-side
 * verification by reference goes through the transaction inquiry API
 * (auth token from api_key → transaction_inquiry by merchant_order_id).
 */
class PaymobGateway implements PaymentGatewayInterface
{
    use HasGatewaySettings;

    /** Region key → API host. */
    protected const REGION_HOSTS = [
        'egypt'    => 'https://accept.paymob.com',
        'ksa'      => 'https://ksa.paymob.com',
        'uae'      => 'https://uae.paymob.com',
        'pakistan' => 'https://pakistan.paymob.com',
        'oman'     => 'https://oman.paymob.com',
    ];

    public function getKey(): string
    {
        return 'paymob';
    }

    protected function getRequiredCredentialKeys(): array
    {
        return ['secret_key', 'public_key', 'api_key', 'hmac_secret', 'integration_id'];
    }

    public function isAvailable(): bool
    {
        return $this->isGatewayAvailable();
    }

    public function getDisplayInfo(): array
    {
        return [
            'key'         => 'paymob',
            'label'       => 'Paymob',
            'description' => 'Accept cards, wallets and local payment methods in Egypt, KSA, UAE, Pakistan and Oman.',
            'icon'        => 'bi-credit-card-2-front',
            'color'       => '#0d3b66',
        ];
    }

    protected function baseUrl(): string
    {
        $this->loadSettings();

        $region = strtolower(trim($this->resolvedCredentials['api_region'] ?? ''));

        return self::REGION_HOSTS[$region] ?? self::REGION_HOSTS['egypt'];
    }

    /** Minor-unit multiplier for the charge currency (Paymob amounts are integers). */
    protected function minorUnits(string $currency): int
    {
        return match (strtoupper($currency)) {
            'OMR', 'KWD', 'BHD' => 1000,
            'JPY'               => 1,
            default             => 100,
        };
    }

    // ──────────────────────────────────────────────
    //  Hosted checkout (Unified Intention API)
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
     * Create a unified-checkout intention.
     *
     * Returns ['url' => <unifiedcheckout URL>, 'session_id' => <special_reference>].
     * The special_reference doubles as the merchant_order_id webhooks echo and
     * the reference verifyTransaction() queries by.
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

        $currency = strtoupper($currency ?: 'EGP');
        $reference = sprintf(
            'pmb-p%d-r%d-%s',
            (int) ($metadata['payment_id'] ?? 0),
            (int) ($metadata['registration_id'] ?? 0),
            bin2hex(random_bytes(8))
        );

        $integrationIds = array_values(array_filter(array_map(
            'intval',
            preg_split('/[\s,]+/', (string) ($this->resolvedCredentials['integration_id'] ?? ''))
        )));

        $amountMinor = (int) round($amount * $this->minorUnits($currency));

        $response = Http::withHeaders([
            'Authorization' => 'Token ' . ($this->resolvedCredentials['secret_key'] ?? ''),
        ])->timeout(30)->post($this->baseUrl() . '/v1/intention/', [
            'amount'            => $amountMinor,
            'currency'          => $currency,
            'payment_methods'   => $integrationIds,
            'items'             => [[
                'name'        => mb_substr($productName, 0, 50),
                'amount'      => $amountMinor,
                'description' => mb_substr($description, 0, 255),
                'quantity'    => 1,
            ]],
            // All billing_data fields are required by the intention API even
            // for digital goods — filled with placeholders.
            'billing_data'      => [
                'first_name'   => 'Subscriber',
                'last_name'    => 'Subscriber',
                'email'        => 'billing@' . (parse_url(config('app.url'), PHP_URL_HOST) ?: 'example.com'),
                'phone_number' => '+20000000000',
            ],
            'special_reference' => $reference,
            // The /webhook/{gateway} route only exists on the CENTRAL domain.
            'notification_url'  => rtrim(config('app.url'), '/') . '/webhook/paymob',
            'redirection_url'   => $successUrl,
        ]);

        $body = $response->json() ?? [];

        if (! $response->successful() || empty($body['client_secret'])) {
            Log::error('Paymob intention create failed', [
                'http_status' => $response->status(),
                'body'        => $body,
            ]);

            $detail = $body['detail'] ?? ($body['message'] ?? null);

            throw new \RuntimeException('Paymob: ' . (string) ($detail ?: ('HTTP ' . $response->status())));
        }

        $url = $this->baseUrl() . '/unifiedcheckout/?' . http_build_query([
            'publicKey'    => $this->resolvedCredentials['public_key'] ?? '',
            'clientSecret' => (string) $body['client_secret'],
        ]);

        return [
            'url'        => $url,
            'session_id' => $reference,
        ];
    }

    // ──────────────────────────────────────────────
    //  Transaction verification (inquiry API)
    // ──────────────────────────────────────────────

    /**
     * Verify a transaction server-side by our special_reference via the
     * transaction inquiry API (legacy auth-token endpoint).
     *
     * @return array{success: bool, status: string, transaction_id: ?string, amount: ?float, currency: ?string}
     */
    public function verifyTransaction(string $reference): array
    {
        $this->loadSettings();

        $fail = ['success' => false, 'status' => '', 'transaction_id' => null, 'amount' => null, 'currency' => null];

        try {
            $auth = Http::timeout(30)->post($this->baseUrl() . '/api/auth/tokens', [
                'api_key' => $this->resolvedCredentials['api_key'] ?? '',
            ])->json() ?? [];

            if (empty($auth['token'])) {
                Log::warning('Paymob auth token request failed during verification');

                return $fail;
            }

            $body = Http::withHeaders([
                'Authorization' => 'Bearer ' . $auth['token'],
            ])->timeout(30)->post($this->baseUrl() . '/api/ecommerce/orders/transaction_inquiry', [
                'merchant_order_id' => $reference,
            ])->json() ?? [];
        } catch (\Throwable $e) {
            Log::warning("Paymob inquiry threw for {$reference}: {$e->getMessage()}");

            return $fail;
        }

        return $this->normalizeTransaction(is_array($body) ? $body : []);
    }

    /**
     * @return array{success: bool, status: string, transaction_id: ?string, amount: ?float, currency: ?string}
     */
    protected function normalizeTransaction(array $txn): array
    {
        if (empty($txn['id'])) {
            return ['success' => false, 'status' => (string) ($txn['detail'] ?? ''), 'transaction_id' => null, 'amount' => null, 'currency' => null];
        }

        $currency = strtoupper((string) ($txn['currency'] ?? ''));
        $amount   = isset($txn['amount_cents'])
            ? ((int) $txn['amount_cents']) / $this->minorUnits($currency ?: 'EGP')
            : null;

        $success = ($txn['success'] ?? false) === true
            && ($txn['pending'] ?? false) === false
            && ($txn['is_refunded'] ?? false) === false
            && ($txn['is_voided'] ?? false) === false;

        $status = match (true) {
            ($txn['is_refunded'] ?? false) === true => 'REFUNDED',
            ($txn['is_voided'] ?? false) === true   => 'VOIDED',
            $success                                 => 'PAID',
            ($txn['pending'] ?? false) === true     => 'PENDING',
            default                                  => 'DECLINED',
        };

        return [
            'success'        => $success,
            'status'         => $status,
            'transaction_id' => (string) $txn['id'],
            'amount'         => $amount,
            'currency'       => $currency ?: null,
        ];
    }

    public function verifyPaymentStatus(string $sessionId): array
    {
        $verification = $this->verifyTransaction($sessionId);

        $status = match (true) {
            $verification['success'] => 'paid',
            in_array($verification['status'], ['DECLINED', 'VOIDED', 'REFUNDED'], true) => 'failed',
            default => 'pending',
        };

        return [
            'status'             => $status,
            'gateway_payment_id' => $sessionId,
            'transaction_id'     => $verification['transaction_id'],
        ];
    }

    // ──────────────────────────────────────────────
    //  Webhook verification (HMAC-SHA512)
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

        $data = json_decode($payload, true);
        if (! is_array($data) || strtoupper((string) ($data['type'] ?? '')) !== 'TRANSACTION') {
            return $result;
        }

        $obj = $data['obj'] ?? [];
        if (! is_array($obj) || empty($obj['id'])) {
            return $result;
        }

        if (! $this->checkWebhookHmac($obj, $signature)) {
            Log::error('Paymob webhook rejected: HMAC mismatch', ['transaction_id' => $obj['id']]);

            return $result;
        }

        $reference = (string) ($obj['order']['merchant_order_id'] ?? '');

        $result['valid']              = true;
        $result['gateway_payment_id'] = $reference;
        $result['transaction_id']     = (string) $obj['id'];

        // Context parsed back out of our special_reference (pmb-p{id}-r{id}-…).
        if (preg_match('/^pmb-p(\d+)-r(\d+)-/', $reference, $m)) {
            $result['payment_id']      = (int) $m[1] ?: null;
            $result['registration_id'] = (int) $m[2] ?: null;
        }

        $normalized = $this->normalizeTransaction($obj);

        $result['status'] = match (true) {
            $normalized['status'] === 'REFUNDED' => 'refunded',
            $normalized['success']               => 'paid',
            $normalized['status'] === 'PENDING'  => 'unknown',
            default                              => 'failed',
        };
        $result['event_type'] = 'paymob.transaction.' . strtolower($normalized['status'] ?: 'unknown');
        $result['amount']     = $normalized['amount'];
        $result['currency']   = $normalized['currency'];

        return $result;
    }

    /**
     * Paymob's callback HMAC: SHA512 over the concatenated values of the
     * documented 20 transaction fields in fixed (lexicographic) order,
     * keyed with the HMAC secret. Booleans serialize as "true"/"false".
     */
    protected function checkWebhookHmac(array $obj, string $signature): bool
    {
        $this->loadSettings();

        $secret = $this->resolvedCredentials['hmac_secret'] ?? '';
        if ($secret === '' || $signature === '') {
            return false;
        }

        $fields = [
            $obj['amount_cents'] ?? '',
            $obj['created_at'] ?? '',
            $obj['currency'] ?? '',
            $obj['error_occured'] ?? '',
            $obj['has_parent_transaction'] ?? '',
            $obj['id'] ?? '',
            $obj['integration_id'] ?? '',
            $obj['is_3d_secure'] ?? '',
            $obj['is_auth'] ?? '',
            $obj['is_capture'] ?? '',
            $obj['is_refunded'] ?? '',
            $obj['is_standalone_payment'] ?? '',
            $obj['is_voided'] ?? '',
            $obj['order']['id'] ?? '',
            $obj['owner'] ?? '',
            $obj['pending'] ?? '',
            $obj['source_data']['pan'] ?? '',
            $obj['source_data']['sub_type'] ?? '',
            $obj['source_data']['type'] ?? '',
            $obj['success'] ?? '',
        ];

        $concatenated = implode('', array_map(static function ($value) {
            if (is_bool($value)) {
                return $value ? 'true' : 'false';
            }

            return (string) $value;
        }, $fields));

        $computed = hash_hmac('sha512', $concatenated, $secret);

        return hash_equals($computed, strtolower(trim($signature)));
    }
}

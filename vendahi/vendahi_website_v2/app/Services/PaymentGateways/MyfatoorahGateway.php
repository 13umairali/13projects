<?php

namespace App\Services\PaymentGateways;

use App\Models\Central\TenantBillingPayment;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * MyFatoorah gateway (Kuwait / GCC — KNET, mada, cards, Apple Pay, STC Pay…).
 *
 * Uses the v2 SendPayment API: a server-side invoice-link create (Bearer API
 * token) returns an InvoiceId + hosted InvoiceURL to redirect the payer to.
 * The live API host depends on the account's country (api_country credential);
 * test_mode targets apitest.myfatoorah.com.
 *
 * Payment context travels in CustomerReference ('mf-p{payment}-r{registration}')
 * which is echoed on webhooks and GetPaymentStatus.
 *
 * Webhooks (TransactionsStatusChanged) carry a MyFatoorah-Signature header:
 * base64(HMAC-SHA256) over the Data object's fields ordered alphabetically
 * (case-insensitive) joined as "k=v,k=v". Regardless of the signature, a
 * SUCCESS event is always re-verified via GetPaymentStatus before being
 * treated as paid ("never act on the payload — always re-verify").
 */
class MyfatoorahGateway implements PaymentGatewayInterface
{
    use HasGatewaySettings;

    protected const TEST_BASE = 'https://apitest.myfatoorah.com';

    /** ISO country code → live API host. Other GCC accounts use the default. */
    protected const LIVE_HOSTS = [
        'SAU' => 'https://api-sa.myfatoorah.com',
        'QAT' => 'https://api-qa.myfatoorah.com',
        'EGY' => 'https://api-eg.myfatoorah.com',
    ];

    protected const DEFAULT_LIVE_BASE = 'https://api.myfatoorah.com';

    public function getKey(): string
    {
        return 'myfatoorah';
    }

    protected function getRequiredCredentialKeys(): array
    {
        return ['api_token'];
    }

    public function isAvailable(): bool
    {
        return $this->isGatewayAvailable();
    }

    public function getDisplayInfo(): array
    {
        return [
            'key'         => 'myfatoorah',
            'label'       => 'MyFatoorah',
            'description' => 'Accept KNET, mada, cards, Apple Pay and STC Pay across the GCC.',
            'icon'        => 'bi-wallet2',
            'color'       => '#3fb5a3',
        ];
    }

    protected function baseUrl(): string
    {
        if ($this->isTestMode()) {
            return self::TEST_BASE;
        }

        $country = strtoupper(trim($this->resolvedCredentials['api_country'] ?? ''));

        return self::LIVE_HOSTS[$country] ?? self::DEFAULT_LIVE_BASE;
    }

    protected function apiHeaders(): array
    {
        $this->loadSettings();

        return [
            'Authorization' => 'Bearer ' . ($this->resolvedCredentials['api_token'] ?? ''),
            'Content-Type'  => 'application/json',
        ];
    }

    // ──────────────────────────────────────────────
    //  Hosted checkout (SendPayment invoice link)
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
     * Create a hosted invoice link.
     *
     * Returns ['url' => <InvoiceURL>, 'session_id' => <InvoiceId>]. The
     * InvoiceId is the reference verifyTransaction() queries by.
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

        $reference = sprintf(
            'mf-p%d-r%d',
            (int) ($metadata['payment_id'] ?? 0),
            (int) ($metadata['registration_id'] ?? 0)
        );

        $response = Http::withHeaders($this->apiHeaders())
            ->timeout(30)
            ->post($this->baseUrl() . '/v2/SendPayment', [
                'NotificationOption' => 'LNK',
                'CustomerName'       => 'Subscriber',
                'InvoiceValue'       => round($amount, 3),
                'DisplayCurrencyIso' => strtoupper($currency ?: 'KWD'),
                'CallBackUrl'        => $successUrl,
                'ErrorUrl'           => $cancelUrl,
                'Language'           => 'en',
                'CustomerReference'  => $reference,
                'InvoiceItems'       => [[
                    'ItemName'  => mb_substr($productName . ' — ' . $description, 0, 250),
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

            throw new \RuntimeException('MyFatoorah: ' . (string) ($detail ?: ('HTTP ' . $response->status())));
        }

        return [
            'url'        => (string) $body['Data']['InvoiceURL'],
            'session_id' => (string) $body['Data']['InvoiceId'],
        ];
    }

    // ──────────────────────────────────────────────
    //  Transaction verification (GetPaymentStatus)
    // ──────────────────────────────────────────────

    /**
     * Verify an invoice server-side by our stored InvoiceId.
     *
     * @return array{success: bool, status: string, transaction_id: ?string, amount: ?float, currency: ?string}
     */
    public function verifyTransaction(string $invoiceId): array
    {
        $fail = ['success' => false, 'status' => '', 'transaction_id' => null, 'amount' => null, 'currency' => null];

        try {
            $body = Http::withHeaders($this->apiHeaders())
                ->timeout(30)
                ->post($this->baseUrl() . '/v2/GetPaymentStatus', [
                    'Key'     => $invoiceId,
                    'KeyType' => 'InvoiceId',
                ])->json() ?? [];
        } catch (\Throwable $e) {
            Log::warning("MyFatoorah GetPaymentStatus threw for invoice {$invoiceId}: {$e->getMessage()}");

            return $fail;
        }

        if (empty($body['IsSuccess']) || empty($body['Data'])) {
            $fail['status'] = (string) ($body['Message'] ?? '');

            return $fail;
        }

        $data   = $body['Data'];
        $status = strtoupper((string) ($data['InvoiceStatus'] ?? ''));
        $paid   = $status === 'PAID';

        // The successful transaction row carries the gateway's TransactionId.
        // MyFatoorah's API spells the success status "Succss".
        $transactionId = null;
        foreach ((array) ($data['InvoiceTransactions'] ?? []) as $txn) {
            if (in_array(strtoupper((string) ($txn['TransactionStatus'] ?? '')), ['SUCCSS', 'SUCCESS'], true)) {
                $transactionId = (string) ($txn['TransactionId'] ?? $txn['PaymentId'] ?? '');
                break;
            }
        }

        // InvoiceValue is in the invoice's own currency (DisplayCurrencyIso at
        // creation); the ISO code is the alpha token of InvoiceDisplayValue.
        $currency = null;
        if (preg_match('/([A-Z]{3})/', (string) ($data['InvoiceDisplayValue'] ?? ''), $m)) {
            $currency = $m[1];
        }

        return [
            'success'        => $paid,
            'status'         => $status,
            'transaction_id' => $transactionId,
            'amount'         => isset($data['InvoiceValue']) ? (float) $data['InvoiceValue'] : null,
            'currency'       => $currency,
        ];
    }

    public function verifyPaymentStatus(string $sessionId): array
    {
        $verification = $this->verifyTransaction($sessionId);

        $status = match (true) {
            $verification['success'] => 'paid',
            in_array($verification['status'], ['EXPIRED', 'CANCELED', 'CANCELLED', 'FAILED'], true) => 'failed',
            default => 'pending',
        };

        return [
            'status'             => $status,
            'gateway_payment_id' => $sessionId,
            'transaction_id'     => $verification['transaction_id'],
        ];
    }

    // ──────────────────────────────────────────────
    //  Webhook verification (MyFatoorah-Signature)
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

        $body = json_decode($payload, true);
        if (! is_array($body) || ! is_array($body['Data'] ?? null)) {
            return $result;
        }

        $data      = $body['Data'];
        $invoiceId = (string) ($data['InvoiceId'] ?? '');
        if ($invoiceId === '') {
            return $result;
        }

        // Signature gate — enforced whenever a webhook secret is configured.
        // Without one the payload is treated as an untrusted hint only (the
        // paid state below still comes from GetPaymentStatus, never the body).
        $secret = $this->getCredential('webhook_secret');
        if ($secret !== '' && ! $this->checkWebhookSignature($data, $signature, $secret)) {
            Log::error('MyFatoorah webhook rejected: signature mismatch', ['invoice_id' => $invoiceId]);

            return $result;
        }

        $eventStatus = strtoupper((string) ($data['TransactionStatus'] ?? ''));

        $result['valid']              = true;
        $result['event_type']         = 'myfatoorah.transaction.' . strtolower($eventStatus ?: 'unknown');
        $result['gateway_payment_id'] = $invoiceId;

        if (preg_match('/^mf-p(\d+)-r(\d+)/', (string) ($data['CustomerReference'] ?? ''), $m)) {
            $result['payment_id']      = (int) $m[1] ?: null;
            $result['registration_id'] = (int) $m[2] ?: null;
        }

        if (in_array($eventStatus, ['SUCCESS', 'SUCCSS', 'PAID'], true)) {
            // Never act on the posted status: re-confirm via GetPaymentStatus.
            $verification = $this->verifyTransaction($invoiceId);

            if ($verification['success']) {
                $result['status']         = 'paid';
                $result['transaction_id'] = (string) ($verification['transaction_id'] ?? '');
                $result['amount']         = $verification['amount'];
                $result['currency']       = $verification['currency'];
            }
        } elseif (in_array($eventStatus, ['FAILED', 'EXPIRED', 'CANCELED', 'CANCELLED'], true)) {
            $result['status'] = 'failed';
        }

        return $result;
    }

    /**
     * MyFatoorah's webhook signature: base64(HMAC-SHA256) over the event's
     * Data fields ordered alphabetically (case-insensitive), joined as
     * "key=value,key=value" with nulls as empty strings.
     */
    protected function checkWebhookSignature(array $data, string $signature, string $secret): bool
    {
        if (trim($signature) === '') {
            return false;
        }

        $fields = [];
        foreach ($data as $key => $value) {
            if (is_array($value) || is_object($value)) {
                continue;
            }
            $fields[$key] = $value === null ? '' : (is_bool($value) ? ($value ? 'true' : 'false') : (string) $value);
        }

        uksort($fields, 'strcasecmp');

        $pairs = [];
        foreach ($fields as $key => $value) {
            $pairs[] = $key . '=' . $value;
        }

        $computed = base64_encode(hash_hmac('sha256', implode(',', $pairs), $secret, true));

        return hash_equals($computed, trim($signature));
    }
}

<?php

namespace App\Services\Fbr;

use App\Models\FbrLog;
use App\Models\FbrSetting;
use Illuminate\Support\Facades\Http;

/**
 * Thin HTTP wrapper around the PRAL Digital Invoicing gateway
 * (gw.fbr.gov.pk). Every call is logged to fbr_logs; the bearer token is
 * never written to logs.
 */
class FbrApiClient
{
    public function __construct(protected FbrSetting $settings)
    {
    }

    /**
     * POST the invoice payload to postinvoicedata(_sb).
     *
     * @return array{ok: bool, status: int|null, json: array|null, body: string|null, error: string|null}
     */
    public function postInvoice(array $payload, ?int $documentId = null): array
    {
        return $this->send('post_invoice', $this->settings->postUrl(), $payload, $documentId);
    }

    /**
     * POST the invoice payload to validateinvoicedata(_sb) — validation
     * only, no invoice number is issued.
     */
    public function validateInvoice(array $payload, ?int $documentId = null): array
    {
        return $this->send('validate_invoice', $this->settings->validateUrl(), $payload, $documentId);
    }

    /**
     * GET one of the public reference (lookup) APIs, e.g. provinces, uom.
     */
    public function reference(string $path, array $query = []): array
    {
        $url = rtrim(config('fbr.base_url'), '/').$path;

        try {
            $response = Http::timeout((int) config('fbr.timeout', 30))
                ->acceptJson()
                ->withToken((string) $this->settings->bearer_token)
                ->get($url, $query);

            return [
                'ok' => $response->successful(),
                'status' => $response->status(),
                'json' => $response->json(),
                'body' => $response->successful() ? null : $response->body(),
                'error' => null,
            ];
        } catch (\Throwable $e) {
            return ['ok' => false, 'status' => null, 'json' => null, 'body' => null, 'error' => $e->getMessage()];
        }
    }

    protected function send(string $action, string $url, array $payload, ?int $documentId): array
    {
        $result = ['ok' => false, 'status' => null, 'json' => null, 'body' => null, 'error' => null];

        try {
            $response = Http::timeout((int) config('fbr.timeout', 30))
                ->acceptJson()
                ->withToken((string) $this->settings->bearer_token)
                ->post($url, $payload);

            $result['status'] = $response->status();
            $result['json'] = $response->json();
            $result['body'] = $response->body();
            $result['ok'] = $response->successful();
        } catch (\Throwable $e) {
            $result['error'] = $e->getMessage();
        }

        $this->log($action, $url, $result, $payload, $documentId);

        return $result;
    }

    protected function log(string $action, string $url, array $result, array $payload, ?int $documentId): void
    {
        try {
            FbrLog::create([
                'fbr_document_id' => $documentId,
                'action' => $action,
                'endpoint' => $url,
                'http_status' => $result['status'],
                'request_meta' => [
                    'environment' => $this->settings->environment,
                    'invoice_type' => $payload['invoiceType'] ?? null,
                    'invoice_date' => $payload['invoiceDate'] ?? null,
                    'items' => count($payload['items'] ?? []),
                ],
                'response_body' => $result['error'] ?: $result['body'],
            ]);
        } catch (\Throwable $e) {
            // Logging must never break a submission.
        }
    }
}

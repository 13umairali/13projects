<?php

namespace App\Services\Fbr;

use App\Models\FbrDocument;
use App\Models\FbrSetting;
use App\Models\Sale;
use App\Models\Setting;
use Illuminate\Database\Eloquent\Model;
use RuntimeException;

/**
 * Pakistan FBR Digital Invoicing (SRO 69(I)/2025) — maps a Stocky sale to
 * the PRAL DI JSON object model, submits it, and records the outcome as an
 * FbrDocument. Mirrors the ZATCA service structure used elsewhere in the
 * codebase.
 */
class FbrService
{
    /**
     * Submit a sale (or resubmit a failed one). Throws on configuration
     * problems; returns the document with status valid|invalid|error after
     * a gateway round-trip.
     */
    public function processDocument(Model $source): FbrDocument
    {
        $settings = FbrSetting::current();
        if (! $settings || ! $settings->isReady()) {
            throw new RuntimeException('FBR Digital Invoicing is not enabled or the seller profile/token is incomplete.');
        }

        $existing = FbrDocument::forSource($source);
        if ($existing && $existing->isAccepted()) {
            return $existing; // FBR invoice numbers are immutable — never resubmit an accepted invoice.
        }

        $payload = $this->buildPayload($source, $settings);

        $document = $existing ?: new FbrDocument([
            'source_type' => get_class($source),
            'source_id' => $source->id,
        ]);
        $document->fill([
            'invoice_ref' => $source->Ref ?? null,
            'invoice_type' => 'Sale Invoice',
            'environment' => $settings->environment,
            'status' => 'pending',
            'request_payload' => $payload,
        ])->save();

        $client = new FbrApiClient($settings);
        $result = $client->postInvoice($payload, $document->id);

        return $this->applyResult($document, $result);
    }

    /**
     * Non-throwing variant used by the post-sale hook: silently no-ops
     * unless the tenant enabled the integration with auto-submit.
     */
    public function processQuietly(Model $source): ?FbrDocument
    {
        try {
            $settings = FbrSetting::current();
            if (! $settings || ! $settings->enabled || ! $settings->auto_submit || ! $settings->isReady()) {
                return null;
            }

            // Only completed sales are tax invoices.
            if (($source->statut ?? null) !== 'completed') {
                return null;
            }

            return $this->processDocument($source);
        } catch (\Throwable $e) {
            \Log::warning('FBR submission failed (non-blocking): '.$e->getMessage(), [
                'source' => get_class($source).'#'.($source->id ?? '?'),
            ]);

            return null;
        }
    }

    /**
     * Build the DI object model for a sale, per the PRAL technical
     * specification (invoice header + items array).
     */
    public function buildPayload(Model $sale, FbrSetting $settings): array
    {
        $sale->loadMissing(['client', 'details.product']);

        $company = Setting::where('deleted_at', '=', null)->first();
        $client = $sale->client;

        $buyerNtn = trim((string) ($client->tax_number ?? ''));
        $registered = $buyerNtn !== '';

        $payload = [
            'invoiceType' => 'Sale Invoice',
            'invoiceDate' => substr((string) $sale->date, 0, 10),
            'sellerNTNCNIC' => (string) $settings->seller_ntn_cnic,
            'sellerBusinessName' => (string) ($settings->seller_business_name ?: ($company->CompanyName ?? '')),
            'sellerProvince' => (string) $settings->seller_province,
            'sellerAddress' => (string) ($settings->seller_address ?: ($company->CompanyAdress ?? '')),
            'buyerNTNCNIC' => $buyerNtn,
            'buyerBusinessName' => (string) ($client->name ?? 'Walk-in Customer'),
            'buyerProvince' => (string) (($client->state ?? null) ?: $settings->seller_province),
            'buyerAddress' => (string) (($client->adresse ?? null) ?: (($client->city ?? null) ?: 'Pakistan')),
            'buyerRegistrationType' => $registered ? 'Registered' : 'Unregistered',
            'invoiceRefNo' => '',
            'items' => [],
        ];

        // scenarioId is required by the sandbox and rejected in production.
        if ($settings->environment === 'sandbox') {
            $payload['scenarioId'] = (string) $settings->scenario_id;
        }

        foreach ($sale->details as $detail) {
            $payload['items'][] = $this->buildItem($detail, $settings);
        }

        if (empty($payload['items'])) {
            throw new RuntimeException('Sale has no line items to report.');
        }

        return $payload;
    }

    /**
     * Map one sale detail line. Monetary math mirrors the receipt/report
     * computations elsewhere in the app (per-unit discount then tax).
     */
    protected function buildItem($detail, FbrSetting $settings): array
    {
        $product = $detail->product;
        $quantity = (float) $detail->quantity;
        $price = (float) $detail->price;
        $taxRate = (float) $detail->TaxNet;

        // Per-unit discount: method '2' = fixed amount, otherwise percentage.
        $discountUnit = $detail->discount_method == '2'
            ? (float) $detail->discount
            : $price * (float) $detail->discount / 100;

        $taxUnit = $taxRate * (($price - $discountUnit) / 100);

        if ($detail->tax_method == '1') {
            // Exclusive: stored price is the net price.
            $netUnit = $price - $discountUnit;
        } else {
            // Inclusive: back the tax out of the stored price.
            $netUnit = $price - $discountUnit - $taxUnit;
        }

        $name = $product->name ?? 'Item';
        if (! empty($detail->pack_name)) {
            $name .= ' ['.$detail->pack_name.']';
        }

        $hsCode = ($product->hs_code ?? null) ?: $settings->default_hs_code;
        if (empty($hsCode)) {
            throw new RuntimeException('No HS code for product "'.$name.'" and no default HS code configured in FBR settings.');
        }

        return [
            'hsCode' => (string) $hsCode,
            'productDescription' => mb_substr($name, 0, 250),
            'rate' => rtrim(rtrim(number_format($taxRate, 2, '.', ''), '0'), '.').'%',
            'uoM' => (string) (($product->fbr_uom ?? null) ?: $settings->default_uom),
            'quantity' => $quantity,
            'totalValues' => round(($netUnit + $taxUnit) * $quantity, 2),
            'valueSalesExcludingST' => round($netUnit * $quantity, 2),
            'fixedNotifiedValueOrRetailPrice' => 0,
            'salesTaxApplicable' => round($taxUnit * $quantity, 2),
            'salesTaxWithheldAtSource' => 0,
            'extraTax' => '',
            'furtherTax' => 0,
            'sroScheduleNo' => '',
            'fedPayable' => 0,
            'discount' => round($discountUnit * $quantity, 2),
            'saleType' => (string) $settings->default_sale_type,
            'sroItemSerialNo' => '',
        ];
    }

    /**
     * Interpret the gateway response and persist the outcome.
     */
    protected function applyResult(FbrDocument $document, array $result): FbrDocument
    {
        $json = $result['json'];
        $validation = is_array($json) ? ($json['validationResponse'] ?? null) : null;

        if (! $result['ok'] || ! is_array($validation)) {
            $message = $result['error']
                ?: ($result['status'] === 401
                    ? 'Unauthorized — check the FBR security token.'
                    : 'FBR gateway error (HTTP '.($result['status'] ?? '?').').');

            $document->fill([
                'status' => 'error',
                'status_code' => null,
                'error' => $message,
                'response' => is_array($json) ? $json : null,
                'submitted_at' => now(),
            ])->save();

            return $document;
        }

        $statusCode = (string) ($validation['statusCode'] ?? '');
        $itemErrors = collect($validation['invoiceStatuses'] ?? [])
            ->filter(fn ($s) => ($s['statusCode'] ?? '00') !== '00')
            ->map(fn ($s) => 'Item '.($s['itemSNo'] ?? '?').': '.($s['error'] ?? 'invalid'))
            ->implode(' | ');
        $valid = $statusCode === '00'
            && strtolower((string) ($validation['status'] ?? '')) === 'valid'
            && ! empty($json['invoiceNumber']);

        $document->fill([
            'status' => $valid ? 'valid' : 'invalid',
            'status_code' => $statusCode ?: null,
            'fbr_invoice_number' => $valid ? $json['invoiceNumber'] : $document->fbr_invoice_number,
            'error' => $valid ? null : (trim(($validation['error'] ?? '').($itemErrors ? ' '.$itemErrors : '')) ?: 'Invoice rejected by FBR.'),
            'response' => $json,
            'submitted_at' => now(),
        ])->save();

        return $document;
    }
}

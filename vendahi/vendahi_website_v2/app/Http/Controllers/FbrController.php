<?php

namespace App\Http\Controllers;

use App\Models\FbrDocument;
use App\Models\FbrLog;
use App\Models\FbrSetting;
use App\Models\Sale;
use App\Models\Setting;
use App\Services\Fbr\FbrApiClient;
use App\Services\Fbr\FbrService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class FbrController extends Controller
{
    public function __construct(protected FbrService $service)
    {
    }

    /**
     * GET api/fbr/settings — configuration + lookup data for the UI.
     */
    public function settings(Request $request)
    {
        $this->authorizeForUser($request->user('api'), 'fbr_settings', Sale::class);

        $fbr = FbrSetting::current();
        if (! $fbr) {
            return response()->json(['message' => 'FBR tables are not migrated for this tenant.'], 409);
        }

        $setting = Setting::where('deleted_at', '=', null)->first();

        return response()->json([
            'settings' => $this->publicSettings($fbr),
            'company' => [
                'name' => $setting->CompanyName ?? null,
                'address' => $setting->CompanyAdress ?? null,
            ],
            'environments' => collect(config('fbr.environments'))
                ->map(fn ($env, $key) => ['value' => $key, 'text' => $env['label']])
                ->values(),
            'scenarios' => collect(config('fbr.scenarios'))
                ->map(fn ($label, $key) => ['value' => $key, 'text' => $key.' — '.$label])
                ->values(),
            'provinces' => config('fbr.provinces'),
        ]);
    }

    /**
     * POST api/fbr/settings — save configuration. An empty bearer_token
     * keeps the stored one.
     */
    public function saveSettings(Request $request)
    {
        $this->authorizeForUser($request->user('api'), 'fbr_settings', Sale::class);

        $fbr = FbrSetting::current();
        if (! $fbr) {
            return response()->json(['message' => 'FBR tables are not migrated for this tenant.'], 409);
        }

        $data = $request->validate([
            'enabled' => 'boolean',
            'auto_submit' => 'boolean',
            'environment' => 'in:sandbox,production',
            'bearer_token' => 'nullable|string|max:500',
            'seller_ntn_cnic' => 'nullable|string|max:20',
            'seller_business_name' => 'nullable|string|max:190',
            'seller_province' => 'nullable|string|max:60',
            'seller_address' => 'nullable|string|max:190',
            'scenario_id' => 'nullable|string|in:'.implode(',', array_keys(config('fbr.scenarios'))),
            'default_hs_code' => 'nullable|string|max:20',
            'default_uom' => 'nullable|string|max:100',
            'default_sale_type' => 'nullable|string|max:100',
        ]);

        if (array_key_exists('bearer_token', $data) && (string) $data['bearer_token'] === '') {
            unset($data['bearer_token']); // keep the stored token
        }

        $fbr->fill($data)->save();

        return response()->json(['success' => true, 'settings' => $this->publicSettings($fbr->fresh())]);
    }

    /**
     * POST api/fbr/test-connection — round-trip a sample invoice through
     * validateinvoicedata to prove token + seller profile work.
     */
    public function testConnection(Request $request)
    {
        $this->authorizeForUser($request->user('api'), 'fbr_settings', Sale::class);

        $fbr = FbrSetting::current();
        if (! $fbr || empty($fbr->bearer_token)) {
            return response()->json(['success' => false, 'message' => 'Save the FBR security token first.'], 422);
        }
        if (empty($fbr->seller_ntn_cnic) || empty($fbr->seller_province)) {
            return response()->json(['success' => false, 'message' => 'Complete the seller profile (NTN/CNIC and province) first.'], 422);
        }

        $payload = [
            'invoiceType' => 'Sale Invoice',
            'invoiceDate' => date('Y-m-d'),
            'sellerNTNCNIC' => (string) $fbr->seller_ntn_cnic,
            'sellerBusinessName' => (string) ($fbr->seller_business_name ?: 'Test Seller'),
            'sellerProvince' => (string) $fbr->seller_province,
            'sellerAddress' => (string) ($fbr->seller_address ?: 'Pakistan'),
            'buyerNTNCNIC' => '',
            'buyerBusinessName' => 'Walk-in Customer',
            'buyerProvince' => (string) $fbr->seller_province,
            'buyerAddress' => 'Pakistan',
            'buyerRegistrationType' => 'Unregistered',
            'invoiceRefNo' => '',
            'items' => [[
                'hsCode' => (string) ($fbr->default_hs_code ?: '0101.2100'),
                'productDescription' => 'Connection test item',
                'rate' => '18%',
                'uoM' => (string) $fbr->default_uom,
                'quantity' => 1,
                'totalValues' => 118,
                'valueSalesExcludingST' => 100,
                'fixedNotifiedValueOrRetailPrice' => 0,
                'salesTaxApplicable' => 18,
                'salesTaxWithheldAtSource' => 0,
                'extraTax' => '',
                'furtherTax' => 0,
                'sroScheduleNo' => '',
                'fedPayable' => 0,
                'discount' => 0,
                'saleType' => (string) $fbr->default_sale_type,
                'sroItemSerialNo' => '',
            ]],
        ];
        if ($fbr->environment === 'sandbox') {
            $payload['scenarioId'] = (string) $fbr->scenario_id;
        }

        $result = (new FbrApiClient($fbr))->validateInvoice($payload);

        if ($result['error']) {
            return response()->json(['success' => false, 'message' => 'Could not reach the FBR gateway: '.$result['error']]);
        }
        if ($result['status'] === 401) {
            return response()->json(['success' => false, 'message' => 'Unauthorized (HTTP 401) — the security token was rejected.']);
        }

        $validation = $result['json']['validationResponse'] ?? null;

        return response()->json([
            'success' => (bool) $result['ok'],
            'message' => $result['ok']
                ? 'Gateway reachable — token accepted. Validation status: '.($validation['status'] ?? 'unknown')
                : 'Gateway responded with HTTP '.$result['status'].'.',
            'validation' => $validation,
        ]);
    }

    /**
     * GET api/fbr/documents — submission history.
     */
    public function documents(Request $request)
    {
        $this->authorizeForUser($request->user('api'), 'fbr_settings', Sale::class);

        $query = FbrDocument::query()->orderBy('id', 'desc');
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $documents = $query->paginate((int) $request->get('limit', 20));
        $documents->getCollection()->transform(function (FbrDocument $doc) {
            return [
                'id' => $doc->id,
                'invoice_ref' => $doc->invoice_ref,
                'source_id' => $doc->source_id,
                'invoice_type' => $doc->invoice_type,
                'fbr_invoice_number' => $doc->fbr_invoice_number,
                'environment' => $doc->environment,
                'status' => $doc->status,
                'status_code' => $doc->status_code,
                'error' => $doc->error,
                'submitted_at' => optional($doc->submitted_at)->toDateTimeString(),
            ];
        });

        return response()->json($documents);
    }

    /**
     * POST api/fbr/sales/{id}/submit — submit or retry a sale.
     */
    public function submitSale(Request $request, $id)
    {
        // Submitting to the FBR gateway is a state-changing, government-
        // facing action: gate it like the rest of the FBR administration.
        $this->authorizeForUser($request->user('api'), 'fbr_settings', Sale::class);

        $sale = Sale::query()->where('deleted_at', '=', null)->findOrFail((int) $id);

        try {
            $document = $this->service->processDocument($sale);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }

        return response()->json([
            'success' => $document->isAccepted(),
            'status' => $document->status,
            'fbr_invoice_number' => $document->fbr_invoice_number,
            'error' => $document->error,
        ]);
    }

    /**
     * GET api/fbr/logs — recent gateway calls (debugging aid).
     */
    public function logs(Request $request)
    {
        $this->authorizeForUser($request->user('api'), 'fbr_settings', Sale::class);

        return response()->json(
            FbrLog::query()->orderBy('id', 'desc')->limit((int) $request->get('limit', 50))->get()
        );
    }

    /**
     * GET api/fbr/reference/{type} — cached proxy for the FBR lookup APIs
     * (provinces, uom, hscodes, doctypes, transtypes, sroitems). HS codes
     * support ?query= substring filtering.
     */
    public function reference(Request $request, string $type)
    {
        $this->authorizeForUser($request->user('api'), 'fbr_settings', Sale::class);

        $path = config('fbr.reference.'.$type);
        if (! $path) {
            return response()->json(['message' => 'Unknown reference type.'], 404);
        }

        $fbr = FbrSetting::current();
        if (! $fbr) {
            return response()->json(['message' => 'FBR tables are not migrated for this tenant.'], 409);
        }

        $data = Cache::remember('fbr_reference_'.$type, now()->addDay(), function () use ($fbr, $path) {
            $result = (new FbrApiClient($fbr))->reference($path);

            return $result['ok'] && is_array($result['json']) ? $result['json'] : null;
        });

        if ($data === null) {
            Cache::forget('fbr_reference_'.$type);

            return response()->json(['message' => 'Could not load reference data from the FBR gateway.'], 502);
        }

        // The HS code list is tens of thousands of rows — filter server-side.
        if ($type === 'hscodes' && $request->filled('query')) {
            $needle = mb_strtolower((string) $request->get('query'));
            $data = collect($data)
                ->filter(function ($row) use ($needle) {
                    return str_contains(mb_strtolower(json_encode($row)), $needle);
                })
                ->take(50)
                ->values()
                ->all();
        }

        return response()->json($data);
    }

    /**
     * Settings payload without the stored token value.
     */
    protected function publicSettings(FbrSetting $fbr): array
    {
        $token = (string) $fbr->bearer_token;

        return [
            'enabled' => (bool) $fbr->enabled,
            'auto_submit' => (bool) $fbr->auto_submit,
            'environment' => $fbr->environment,
            'has_token' => $token !== '',
            'token_last4' => $token !== '' ? substr($token, -4) : null,
            'seller_ntn_cnic' => $fbr->seller_ntn_cnic,
            'seller_business_name' => $fbr->seller_business_name,
            'seller_province' => $fbr->seller_province,
            'seller_address' => $fbr->seller_address,
            'scenario_id' => $fbr->scenario_id,
            'default_hs_code' => $fbr->default_hs_code,
            'default_uom' => $fbr->default_uom,
            'default_sale_type' => $fbr->default_sale_type,
        ];
    }
}

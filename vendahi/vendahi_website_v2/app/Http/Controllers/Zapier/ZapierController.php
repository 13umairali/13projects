<?php

namespace App\Http\Controllers\Zapier;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Expense;
use App\Models\PaymentSale;
use App\Models\Product;
use App\Models\Quotation;
use App\Models\Sale;
use App\Models\Setting;
use App\Models\Webhook;
use App\Services\Webhooks\Payload;
use App\Services\Webhooks\WebhookService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * The endpoints Zapier's app talks to (server-to-server, ZapierAuth middleware).
 *
 * Triggers use REST hooks: Zapier POSTs a target_url per Zap, we store it as a
 * row in the existing `webhooks` table (source=zapier) and the normal webhook
 * dispatcher delivers events to it. Samples feed Zapier's Zap editor preview.
 */
class ZapierController extends Controller
{
    /** Events Zapier may subscribe to (v1 trigger set). */
    public const TRIGGER_EVENTS = [
        'sale.created',
        'quotation.created',
        'client.created',
        'product.created',
        'expense.created',
        'payment.created',
        'product.low_stock',
    ];

    public function __construct(protected WebhookService $service)
    {
    }

    /** Connection test — Zapier uses this for the account label. */
    public function me(Request $request)
    {
        $setting = Setting::query()->first();

        return response()->json([
            'company' => $setting->CompanyName ?? tenant('id'),
            'user' => optional(Auth::guard('api')->user())->username
                ?? optional(Auth::guard('api')->user())->email,
        ]);
    }

    // ------------------------------ REST hooks ------------------------------

    public function subscribe(Request $request)
    {
        $data = $request->validate([
            'target_url' => 'required|url|max:2048',
            'event' => ['required', Rule::in(self::TRIGGER_EVENTS)],
        ]);

        $webhook = $this->service->create([
            'name' => 'Zapier: '.$data['event'],
            'url' => $data['target_url'],
            'events' => [$data['event']],
            'is_active' => true,
            'user_id' => optional(Auth::guard('api')->user())->id,
        ]);
        $webhook->forceFill(['source' => 'zapier'])->save();

        return response()->json(['id' => $webhook->id], 201);
    }

    public function unsubscribe(Request $request, $id)
    {
        Webhook::query()->where('source', 'zapier')->where('id', $id)->delete();

        // Zapier treats unsubscribe as best-effort; always succeed.
        return response()->json(['success' => true]);
    }

    // ------------------------------- Samples --------------------------------

    /**
     * Latest real rows for an event, shaped exactly like a live delivery's
     * `data` — Zapier shows these in the editor and uses them for field maps.
     */
    public function samples(Request $request, string $event)
    {
        if (! in_array($event, self::TRIGGER_EVENTS, true)) {
            return response()->json(['message' => 'Unknown trigger event.'], 404);
        }

        if ($event === 'product.low_stock') {
            $rows = DB::table('product_warehouse')
                ->join('products', 'products.id', '=', 'product_warehouse.product_id')
                ->whereNull('product_warehouse.deleted_at')
                ->whereNull('products.deleted_at')
                ->where('products.stock_alert', '>', 0)
                ->whereColumn('product_warehouse.qte', '<=', 'products.stock_alert')
                ->limit(3)
                ->get(['products.id', 'products.name', 'products.code', 'products.stock_alert', 'product_warehouse.warehouse_id', 'product_warehouse.qte']);

            $samples = $rows->map(fn ($r) => [
                'product_id' => (int) $r->id,
                'name' => $r->name,
                'code' => $r->code,
                'warehouse_id' => (int) $r->warehouse_id,
                'quantity' => (float) $r->qte,
                'stock_alert' => (float) $r->stock_alert,
                'sale_id' => 0,
            ])->values();

            return response()->json($samples->isEmpty() ? [$this->cannedLowStockSample()] : $samples);
        }

        $model = [
            'sale.created' => Sale::class,
            'quotation.created' => Quotation::class,
            'client.created' => Client::class,
            'product.created' => Product::class,
            'expense.created' => Expense::class,
            'payment.created' => PaymentSale::class,
        ][$event];

        $rows = $model::query()
            ->whereNull('deleted_at')
            ->orderByDesc('id')
            ->limit(3)
            ->get();

        return response()->json(
            $rows->map(fn ($row) => Payload::fromModel($row))->values()
        );
    }

    private function cannedLowStockSample(): array
    {
        return [
            'product_id' => 1,
            'name' => 'Sample product',
            'code' => 'SAMPLE-001',
            'warehouse_id' => 1,
            'quantity' => 2.0,
            'stock_alert' => 10.0,
            'sale_id' => 0,
        ];
    }

    // ------------------------------- Actions --------------------------------

    /** Create customer — mirrors ClientController@store's row shape. */
    public function createCustomer(Request $request)
    {
        $this->authorizeForUser($request->user('api'), 'create', Client::class);

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'nullable', 'email', 'max:255',
                Rule::unique('clients', 'email')->whereNull('deleted_at'),
                Rule::unique('ecommerce_clients', 'email')->whereNull('deleted_at'),
            ],
            'phone' => 'nullable|string|max:255',
            'adresse' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'tax_number' => 'nullable|string|max:255',
        ]);

        $last = DB::table('clients')->latest('id')->first();

        $client = Client::create($data + [
            'code' => $last ? $last->code + 1 : 1,
            'status' => 1,
        ]);

        return response()->json(Payload::fromModel($client), 201);
    }

    /** Create product — minimal single product; defaults mirror the app's. */
    public function createProduct(Request $request)
    {
        $this->authorizeForUser($request->user('api'), 'create', Product::class);

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'cost' => 'nullable|numeric|min:0',
            'code' => ['nullable', 'string', 'max:192', Rule::unique('products', 'code')->whereNull('deleted_at')],
            'stock_alert' => 'nullable|numeric|min:0',
            'note' => 'nullable|string|max:1000',
        ]);

        $categoryId = DB::table('categories')->whereNull('deleted_at')->orderBy('id')->value('id');
        $unitId = DB::table('units')->whereNull('deleted_at')->orderBy('id')->value('id');
        if (! $categoryId || ! $unitId) {
            return response()->json(['message' => 'Create at least one category and unit first.'], 422);
        }

        $product = Product::create([
            'type' => 'is_single',
            'name' => $data['name'],
            'code' => $data['code'] ?? strtoupper('ZAP-'.substr(uniqid(), -8)),
            'Type_barcode' => 'CODE128',
            'price' => $data['price'],
            'cost' => $data['cost'] ?? 0,
            'category_id' => $categoryId,
            'unit_id' => $unitId,
            'unit_sale_id' => $unitId,
            'unit_purchase_id' => $unitId,
            'TaxNet' => 0,
            'tax_method' => '1',
            'stock_alert' => $data['stock_alert'] ?? 0,
            'note' => $data['note'] ?? null,
            'image' => 'no-image.png',
            'is_active' => true,
        ]);

        return response()->json(Payload::fromModel($product), 201);
    }

    /** Find customers by email, phone or name (Zapier search action). */
    public function findCustomer(Request $request)
    {
        $this->authorizeForUser($request->user('api'), 'view', Client::class);

        $q = (string) $request->query('query', $request->query('email', ''));

        $clients = Client::query()
            ->whereNull('deleted_at')
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($inner) use ($q) {
                    $inner->where('email', $q)
                        ->orWhere('phone', $q)
                        ->orWhere('name', 'LIKE', "%{$q}%");
                });
            })
            ->orderByDesc('id')
            ->limit(10)
            ->get();

        return response()->json($clients->map(fn ($c) => Payload::fromModel($c))->values());
    }

    /** Find products by code, barcode-ish code or name (Zapier search action). */
    public function findProduct(Request $request)
    {
        $this->authorizeForUser($request->user('api'), 'view', Product::class);

        $q = (string) $request->query('query', $request->query('code', ''));

        $products = Product::query()
            ->whereNull('deleted_at')
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($inner) use ($q) {
                    $inner->where('code', $q)
                        ->orWhere('name', 'LIKE', "%{$q}%");
                });
            })
            ->orderByDesc('id')
            ->limit(10)
            ->get();

        return response()->json($products->map(fn ($p) => Payload::fromModel($p))->values());
    }
}

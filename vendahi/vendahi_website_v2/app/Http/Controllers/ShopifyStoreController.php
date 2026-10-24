<?php

namespace App\Http\Controllers;

use App\Models\ShopifyLink;
use App\Models\ShopifyLog;
use App\Models\ShopifyStore;
use App\Models\ShopifySyncRun;
use App\Models\UserWarehouse;
use App\Models\Warehouse;
use App\Services\Shopify\AutoSync;
use App\Services\Shopify\Client;
use App\Services\Shopify\WebhookManager;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Managing the connected shops.
 *
 * Access tokens go in and never come out: every response is built from
 * ShopifyStore::toPublicArray(), which omits the token and the webhook secret,
 * and an update that omits the token keeps the stored one rather than blanking
 * it. That is what lets the UI show and save a store without ever holding a
 * credential it could leak.
 */
class ShopifyStoreController extends BaseController
{
    public function index(Request $request)
    {
        $this->authorizeForUser($request->user('api'), 'view', ShopifyStore::class);

        $perPage = $request->limit ?: 10;
        $pageStart = \Request::get('page', 1);
        $offSet = ($pageStart * $perPage) - $perPage;
        $order = $request->SortField ?: 'id';
        $dir = strtolower((string) ($request->SortType ?: 'desc'));
        if (! in_array($dir, ['asc', 'desc'], true)) {
            $dir = 'desc';
        }

        $sortable = [
            'id' => 'id', 'name' => 'name', 'shop_domain' => 'shop_domain',
            'status' => 'status', 'last_sync_at' => 'last_sync_at',
        ];
        $order = $sortable[$order] ?? 'id';

        $query = ShopifyStore::whereNull('deleted_at')
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('search'), function ($q) use ($request) {
                $s = $request->search;

                return $q->where(function ($q) use ($s) {
                    $q->where('name', 'LIKE', "%{$s}%")
                        ->orWhere('shop_domain', 'LIKE', "%{$s}%")
                        ->orWhere('shop_name', 'LIKE', "%{$s}%");
                });
            });

        $totalRows = $query->count();
        if ($perPage == '-1') {
            $perPage = $totalRows ?: 1;
        }

        $stores = $query->with('warehouse')->offset($offSet)->limit($perPage)->orderBy($order, $dir)->get();

        // Counts per store in two queries rather than two per row.
        $ids = $stores->pluck('id');
        $linkCounts = ShopifyLink::whereIn('store_id', $ids)
            ->groupBy('store_id')->selectRaw('store_id, COUNT(*) as c')->pluck('c', 'store_id');
        $errorCounts = ShopifyLog::whereIn('store_id', $ids)->where('level', 'error')
            ->where('created_at', '>=', Carbon::now()->subDays(7))
            ->groupBy('store_id')->selectRaw('store_id, COUNT(*) as c')->pluck('c', 'store_id');

        $data = $stores->map(function ($store) use ($linkCounts, $errorCounts) {
            $row = $store->toPublicArray();
            $row['linked_records'] = (int) ($linkCounts[$store->id] ?? 0);
            $row['recent_errors'] = (int) ($errorCounts[$store->id] ?? 0);

            return $row;
        });

        return response()->json(['stores' => $data, 'totalRows' => $totalRows]);
    }

    public function show(Request $request, $id)
    {
        $this->authorizeForUser($request->user('api'), 'view', ShopifyStore::class);

        $store = ShopifyStore::whereNull('deleted_at')->with('warehouse')->findOrFail($id);

        return response()->json(['store' => $store->toPublicArray()]);
    }

    public function store(Request $request)
    {
        $this->authorizeForUser($request->user('api'), 'create', ShopifyStore::class);

        // Two ways in: a permanent token from a legacy custom app (no longer
        // creatable since January 2026), or the client id + secret of a Dev
        // Dashboard app, which the ERP exchanges for tokens itself.
        $request->validate([
            'name' => 'required|string|max:191',
            'shop_domain' => 'required|string|max:191',
            'auth_mode' => 'nullable|in:token,client_credentials',
            'access_token' => 'required_if:auth_mode,token|nullable|string',
            'client_id' => 'required_if:auth_mode,client_credentials|nullable|string|max:191',
            'client_secret' => 'required_if:auth_mode,client_credentials|nullable|string',
            'warehouse_id' => 'nullable|exists:warehouses,id',
            'price_field' => 'nullable|in:price,wholesale_price,min_price',
        ]);

        $credentials = $this->credentialsFrom($request);
        if ($credentials === null) {
            return response()->json([
                'success' => false,
                'message' => 'Enter either an Admin API access token, or the client ID and client secret of your Dev Dashboard app.',
            ], 422);
        }

        // The store syncs stock for the warehouse it points at.
        if ($request->filled('warehouse_id')) {
            $this->abortIfWarehouseDenied($request->warehouse_id);
        }

        $domain = ShopifyStore::normaliseDomain($request->shop_domain);
        if ($domain === '') {
            return response()->json(['success' => false, 'message' => 'That does not look like a Shopify domain.'], 422);
        }
        if (ShopifyStore::whereNull('deleted_at')->where('shop_domain', $domain)->exists()) {
            return response()->json(['success' => false, 'message' => 'That shop is already connected.'], 422);
        }

        $store = ShopifyStore::create(array_merge(
            $this->settingsFrom($request, true),
            $credentials,
            [
                'name' => $request->name,
                'shop_domain' => $domain,
                // Was dropped here until now: the connect form offers the
                // signing secret, and saving the store threw it away, so
                // webhooks were refused until someone re-entered it on an edit
                // — with nothing on screen explaining why.
                'webhook_secret' => $request->filled('webhook_secret')
                    ? trim((string) $request->webhook_secret)
                    : null,
                'status' => 'disconnected',
            ]
        ));

        // Verify immediately: a store that was never reachable should say so on
        // the very first screen, not the first time someone runs a sync.
        $this->verify($store);
        ShopifyLog::write($store->id, 'store.create', 'Store connected: '.$domain, 'info');

        return response()->json(['success' => true, 'store' => $store->fresh()->toPublicArray()], 200);
    }

    public function update(Request $request, $id)
    {
        $this->authorizeForUser($request->user('api'), 'update', ShopifyStore::class);

        $store = ShopifyStore::whereNull('deleted_at')->findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:191',
            'shop_domain' => 'required|string|max:191',
            'auth_mode' => 'nullable|in:token,client_credentials',
            'access_token' => 'nullable|string',
            'client_id' => 'nullable|string|max:191',
            'client_secret' => 'nullable|string',
            'warehouse_id' => 'nullable|exists:warehouses,id',
            'price_field' => 'nullable|in:price,wholesale_price,min_price',
        ]);

        // The store syncs stock for the warehouse it points at.
        if ($request->filled('warehouse_id')) {
            $this->abortIfWarehouseDenied($request->warehouse_id);
        }

        $domain = ShopifyStore::normaliseDomain($request->shop_domain);
        if ($domain === '') {
            return response()->json(['success' => false, 'message' => 'That does not look like a Shopify domain.'], 422);
        }
        if (ShopifyStore::whereNull('deleted_at')->where('shop_domain', $domain)->where('id', '!=', $store->id)->exists()) {
            return response()->json(['success' => false, 'message' => 'Another connection already uses that shop.'], 422);
        }

        $domainChanged = $domain !== $store->shop_domain;

        $payload = array_merge($this->settingsFrom($request), [
            'name' => $request->name,
            'shop_domain' => $domain,
        ]);

        // An empty credential field means "leave it alone", not "erase it".
        // Switching kinds is explicit: a token replaces any client pair, and a
        // client pair discards the pasted token — never both at once, or the
        // store could not say which one Shopify is actually rejecting.
        $credentials = $this->credentialsFrom($request, $store);
        $credentialsChanged = $credentials !== null;
        if ($credentialsChanged) {
            $payload = array_merge($payload, $credentials);
        }
        if ($request->filled('webhook_secret')) {
            // Trimmed: a secret pasted with a trailing space or newline
            // verifies nothing, and the only symptom is every delivery being
            // refused — which looks like the wrong secret entirely.
            $payload['webhook_secret'] = trim((string) $request->webhook_secret);
        } elseif ($request->boolean('clear_webhook_secret')) {
            // Blank means "keep", so removing a wrong secret needs its own
            // flag. For a Dev Dashboard app clearing it is the fix: the
            // client secret takes over signing.
            $payload['webhook_secret'] = null;
        }

        $store->update($payload);

        // Pointing a connection at a different shop invalidates every mapping:
        // the ids belong to the old shop and would overwrite unrelated records.
        if ($domainChanged) {
            $removed = ShopifyLink::where('store_id', $store->id)->delete();
            $store->update(['status' => 'disconnected', 'location_id' => null, 'last_sync_at' => null]);
            ShopifyLog::write($store->id, 'store.rebind',
                'Shop domain changed — cleared '.$removed.' record mappings so they cannot point at the wrong shop.',
                'warning', ['removed' => $removed]);
        }

        if ($credentialsChanged || $domainChanged) {
            $this->verify($store->fresh());
        }

        return response()->json(['success' => true, 'store' => $store->fresh()->toPublicArray()], 200);
    }

    public function destroy(Request $request, $id)
    {
        $this->authorizeForUser($request->user('api'), 'delete', ShopifyStore::class);

        $store = ShopifyStore::whereNull('deleted_at')->findOrFail($id);

        // Soft-delete the store but drop its mappings outright: leaving them
        // would silently re-attach if a shop with the same domain is reconnected.
        DB::transaction(function () use ($store) {
            ShopifyLink::where('store_id', $store->id)->delete();
            $store->update(['deleted_at' => Carbon::now(), 'status' => 'disconnected']);
        });

        ShopifyLog::write($store->id, 'store.delete', 'Store disconnected: '.$store->shop_domain, 'warning');

        return response()->json(['success' => true], 200);
    }

    /** Re-test the saved credentials and refresh the cached shop details. */
    public function testConnection(Request $request, $id)
    {
        $this->authorizeForUser($request->user('api'), 'view', ShopifyStore::class);

        $store = ShopifyStore::whereNull('deleted_at')->findOrFail($id);
        $result = $this->verify($store);

        return response()->json($result, $result['ok'] ? 200 : 422);
    }

    /** Shopify locations, so the user can pick which one holds this stock. */
    public function locations(Request $request, $id)
    {
        $this->authorizeForUser($request->user('api'), 'view', ShopifyStore::class);

        $store = ShopifyStore::whereNull('deleted_at')->findOrFail($id);
        $client = Client::forStore($store);

        try {
            $res = $client->get('locations');
        } catch (\Throwable $e) {
            return response()->json(['ok' => false, 'error' => $e->getMessage()], 422);
        }

        if (! $res->successful()) {
            return response()->json(['ok' => false, 'error' => $client->explain($res)], 422);
        }

        $locations = collect($res->json()['locations'] ?? [])->map(fn ($l) => [
            'id' => (string) ($l['id'] ?? ''),
            'name' => $l['name'] ?? '',
            'city' => $l['city'] ?? null,
            'country' => $l['country_name'] ?? $l['country'] ?? null,
            'active' => (bool) ($l['active'] ?? true),
        ])->values();

        return response()->json(['ok' => true, 'locations' => $locations]);
    }

    /**
     * What this shop is currently subscribed to.
     *
     * Reported as three lists rather than one: what exists, what is missing
     * for the entities this store syncs, and what of ours is left over. That
     * is the difference between "webhooks are set up" and "webhooks are set up
     * correctly", which is the whole question when orders stop arriving.
     */
    public function webhooks(Request $request, $id)
    {
        $this->authorizeForUser($request->user('api'), 'view', ShopifyStore::class);

        $store = ShopifyStore::whereNull('deleted_at')->findOrFail($id);
        $hooks = WebhookManager::for($store);
        $state = $hooks->inspect();

        return response()->json(array_merge($state, [
            'callback_url' => WebhookManager::callbackUrl(),
            'wanted' => $hooks->wantedTopics(),
            'has_webhook_secret' => $store->hasWebhookSecret(),
        ]), $state['ok'] ? 200 : 422);
    }

    /** Create the subscriptions this ERP can handle. Safe to repeat. */
    public function registerWebhooks(Request $request, $id)
    {
        $this->authorizeForUser($request->user('api'), 'update', ShopifyStore::class);

        $store = ShopifyStore::whereNull('deleted_at')->findOrFail($id);

        if ($store->status !== 'connected') {
            return response()->json([
                'ok' => false,
                'error' => 'This store is not connected. Test the connection before registering webhooks.',
            ], 422);
        }

        $result = WebhookManager::for($store)->register($request->boolean('prune'));

        return response()->json(array_merge($result, [
            'callback_url' => WebhookManager::callbackUrl(),
        ]), $result['error'] ? 422 : 200);
    }

    /** Remove one subscription — including one that is not ours, on request. */
    public function deleteWebhook(Request $request, $id, $webhookId)
    {
        $this->authorizeForUser($request->user('api'), 'update', ShopifyStore::class);

        $store = ShopifyStore::whereNull('deleted_at')->findOrFail($id);
        $result = WebhookManager::for($store)->delete((string) $webhookId);

        return response()->json($result, $result['ok'] ? 200 : 422);
    }

    /** Select options every Shopify page shares. */
    public function meta(Request $request)
    {
        $this->authorizeForUser($request->user('api'), 'view', ShopifyStore::class);

        $user = $request->user('api') ?: auth()->user();
        if ($user && $user->is_all_warehouses) {
            $warehouses = Warehouse::whereNull('deleted_at')->orderBy('name')->get(['id', 'name']);
        } else {
            $ids = UserWarehouse::where('user_id', $user ? $user->id : null)->pluck('warehouse_id')->toArray();
            $warehouses = Warehouse::whereNull('deleted_at')->whereIn('id', $ids)->orderBy('name')->get(['id', 'name']);
        }

        return response()->json([
            'warehouses' => $warehouses,
            // The Sync Centre reads the warehouse, the location and every
            // sync_* flag off these rows to decide what it may run, so they
            // have to travel with the option — a trimmed payload reads as an
            // unconfigured store no matter how the store is actually set up.
            'stores' => ShopifyStore::whereNull('deleted_at')->orderBy('name')
                ->get(array_merge(
                    ['id', 'name', 'shop_domain', 'status', 'warehouse_id', 'location_id'],
                    array_map(fn ($e) => 'sync_'.$e, ShopifyStore::ENTITIES)
                ))
                ->map(fn ($s) => array_merge([
                    'id' => $s->id,
                    'name' => $s->name,
                    'shop_domain' => $s->shop_domain,
                    'status' => $s->status,
                    'warehouse_id' => $s->warehouse_id,
                    'location_id' => $s->location_id,
                    'label' => $s->name.' — '.$s->shop_domain,
                    'ready' => $s->status === 'connected' && $s->warehouse_id && $s->location_id,
                ], collect(ShopifyStore::ENTITIES)
                    ->mapWithKeys(fn ($e) => ['sync_'.$e => (bool) $s->{'sync_'.$e}])
                    ->all())),
            'entities' => ShopifyStore::ENTITIES,
            // The public URL Shopify should post webhooks to.
            'webhook_url' => url('/api/shopify/webhook'),
            // Same cron lines the store page shows, so the standalone guide
            // can be read before any store exists.
            'cron' => $this->cronLines(),
        ]);
    }

    // ------------------------------------------------------------- internal --

    /**
     * Settings taken off the request.
     *
     * Booleans are only applied when the key is actually present. Reading an
     * absent checkbox as `false` would mean any partial update — a rename, a
     * warehouse change — silently switched every entity's sync off, which is a
     * failure nobody would connect back to the edit that caused it.
     *
     * @param  bool  $isNew  on create, absent flags fall back to sensible defaults
     */
    private function settingsFrom(Request $request, bool $isNew = false): array
    {
        $settings = [];

        if ($isNew || $request->has('api_version')) {
            $settings['api_version'] = $request->api_version ?: '2024-10';
        }
        if ($isNew || $request->has('warehouse_id')) {
            $settings['warehouse_id'] = $request->warehouse_id ?: null;
        }
        if ($isNew || $request->has('location_id')) {
            $settings['location_id'] = $request->location_id ?: null;
        }
        if ($isNew || $request->has('price_field')) {
            $settings['price_field'] = $request->price_field ?: 'price';
        }
        if ($isNew || $request->has('sync_interval_minutes')) {
            $settings['sync_interval_minutes'] = max(5, (int) ($request->sync_interval_minutes ?: 60));
        }
        // The automatic plan: which entities the scheduler moves, and which
        // way. Null (or an empty list) means the default — pull orders, push
        // inventory — rather than "automatic sync that does nothing".
        if ($isNew || $request->has('auto_sync_plan')) {
            $settings['auto_sync_plan'] = ShopifyStore::sanitisePlan($request->input('auto_sync_plan'));
        }

        // Defaults chosen so a freshly connected store is useful straight away
        // but never publishes anything it was not asked to.
        $booleans = [
            'create_missing_products' => true,
            'create_missing_customers' => true,
            'auto_sync' => false,
            'instant_stock_push' => false,
            'sync_products' => true,
            'sync_inventory' => true,
            'sync_customers' => true,
            'sync_orders' => true,
            'sync_collections' => true,
            'sync_fulfillments' => true,
        ];

        foreach ($booleans as $field => $default) {
            if ($request->has($field)) {
                $settings[$field] = $request->boolean($field);
            } elseif ($isNew) {
                $settings[$field] = $default;
            }
        }

        return $settings;
    }

    /**
     * The credential columns a request wants written, or null when it carries
     * no credentials at all (an edit that only touched settings).
     *
     * Legacy token: {access_token, client_id: null, client_secret: null}.
     * Dev Dashboard app: {client_id, client_secret, access_token: '',
     * token_expires_at: null} — the token cache is emptied so the next call
     * fetches one with the new pair instead of trusting a token that belonged
     * to the old one. A partial pair on an edit (only the secret rotated)
     * keeps the stored half.
     */
    private function credentialsFrom(Request $request, ?ShopifyStore $existing = null): ?array
    {
        $mode = (string) $request->input('auth_mode', '');
        if ($mode === '') {
            // Older clients that only know about tokens.
            $mode = $request->filled('client_id') || $request->filled('client_secret') ? 'client_credentials' : 'token';
        }

        if ($mode === 'token') {
            if (! $request->filled('access_token')) {
                return null;
            }

            return [
                'access_token' => trim((string) $request->access_token),
                'client_id' => null,
                'client_secret' => null,
                'token_expires_at' => null,
            ];
        }

        $clientId = $request->filled('client_id')
            ? trim((string) $request->client_id)
            : trim((string) ($existing->client_id ?? ''));
        $clientSecret = $request->filled('client_secret')
            ? trim((string) $request->client_secret)
            : trim((string) ($existing->client_secret ?? ''));

        if ($clientId === '' || $clientSecret === '') {
            return null;
        }
        if (! $request->filled('client_id') && ! $request->filled('client_secret')) {
            return null;    // nothing new was typed
        }

        return [
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'access_token' => '',
            'token_expires_at' => null,
        ];
    }

    /** Ask Shopify who it thinks we are, and record the answer on the store. */
    private function verify(ShopifyStore $store): array
    {
        // For a client-credentials store the exchange itself is the first
        // thing that can fail, and its message (wrong secret, app not in this
        // organisation) is more useful than the 401 the API call would give.
        if ($store->usesClientCredentials()) {
            try {
                \App\Services\Shopify\AccessToken::refresh($store);
            } catch (\Throwable $e) {
                $store->update(['status' => 'error', 'last_error' => $e->getMessage()]);
                ShopifyLog::write($store->id, 'store.connect', 'Token request failed: '.$e->getMessage(), 'error');

                return ['ok' => false, 'error' => $e->getMessage()];
            }
        }

        $client = Client::forStore($store);
        $result = $client->testConnection();

        if (! empty($result['ok'])) {
            $store->update([
                'status' => 'connected',
                'shop_name' => $result['shop']['name'] ?? null,
                'shop_email' => $result['shop']['email'] ?? null,
                'currency' => $result['shop']['currency'] ?? null,
                'last_connected_at' => Carbon::now(),
                'last_error' => null,
            ]);
        } else {
            $store->update([
                'status' => 'error',
                'last_error' => $result['error'] ?? 'Connection failed',
            ]);
            ShopifyLog::write($store->id, 'store.connect', 'Connection test failed: '.($result['error'] ?? ''), 'error', $result);
        }

        return $result;
    }

    /** Everything the store detail page shows in one call. */
    public function overview(Request $request, $id)
    {
        $this->authorizeForUser($request->user('api'), 'view', ShopifyStore::class);

        $store = ShopifyStore::whereNull('deleted_at')->with('warehouse')->findOrFail($id);

        $links = ShopifyLink::where('store_id', $store->id)
            ->groupBy('entity_type')
            ->selectRaw('entity_type, COUNT(*) as c')
            ->pluck('c', 'entity_type');

        $runs = ShopifySyncRun::where('store_id', $store->id)
            ->orderByDesc('id')->limit(10)->get()
            ->map(fn ($r) => $r->toPublicArray());

        $errors = ShopifyLog::where('store_id', $store->id)->where('level', 'error')
            ->orderByDesc('id')->limit(10)->get()
            ->map(fn ($l) => [
                'id' => $l->id,
                'action' => $l->action,
                'entity' => $l->entity,
                'message' => $l->message,
                'created_at' => $l->created_at ? $l->created_at->toDateTimeString() : null,
            ]);

        return response()->json([
            'store' => $store->toPublicArray(),
            // What the scheduler will do next, and what is standing in its way.
            // Shown whether or not auto sync is on, so switching it on is not a
            // guess about what would happen.
            'auto_sync' => [
                'enabled' => (bool) $store->auto_sync,
                'using_default_plan' => ShopifyStore::sanitisePlan($store->auto_sync_plan) === null,
                'steps' => AutoSync::schedule($store),
            ],
            'links' => [
                'product' => (int) ($links['product'] ?? 0),
                'variant' => (int) ($links['variant'] ?? 0),
                'customer' => (int) ($links['customer'] ?? 0),
                'order' => (int) ($links['order'] ?? 0),
                'collection' => (int) ($links['collection'] ?? 0),
                'inventory_item' => (int) ($links['inventory_item'] ?? 0),
            ],
            'runs' => $runs,
            'errors' => $errors,
            'cron' => $this->cronLines(),
        ]);
    }

    /**
     * What the guide needs to say about scheduling.
     *
     * On Stocky Cloud the answer is "nothing to do": `shopify:sync` is on the
     * platform's own schedule (app/Console/Kernel.php) and sweeps every active
     * tenant, and the platform's worker already drains the `webhooks` queue. A
     * tenant has no shell and no crontab, so printing a cron line at them would
     * be an instruction they cannot follow and a setup step they would wrongly
     * believe is outstanding.
     *
     * The lines are still built — tenant-scoped, so they are correct if the
     * operator ever runs one by hand — and `managed` tells the guide to present
     * them as reference rather than as a step.
     */
    private function cronLines(): array
    {
        $tenant = tenant();
        $scope = $tenant ? ' --tenant='.$tenant->id : '';

        return [
            'managed' => true,
            'base_path' => base_path(),
            'sync' => 'php artisan shopify:sync'.$scope,
            'queue' => 'php artisan queue:work database --once --queue=webhooks',
        ];
    }
}

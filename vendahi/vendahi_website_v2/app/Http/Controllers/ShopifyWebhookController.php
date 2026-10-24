<?php

namespace App\Http\Controllers;

use App\Models\ShopifyLink;
use App\Models\ShopifyLog;
use App\Models\ShopifyStore;
use App\Models\ShopifyWebhookEvent;
use App\Services\Shopify\StockWatcher;
use App\Services\Shopify\SyncService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

/**
 * Inbound Shopify webhooks — the only unauthenticated entry point in this
 * module, so it is the one that has to be careful.
 *
 * Three defences, in order:
 *  1. The shop header must match a connected store. Unknown shop, no work done.
 *  2. The HMAC must verify against that store's secret, compared in constant
 *     time. A store with no secret configured is refused rather than trusted —
 *     "no secret" must not mean "skip the check", or the endpoint is open.
 *  3. The event id makes handling idempotent. Shopify retries until it gets a
 *     2xx, so the same order arrives repeatedly; the unique index is what turns
 *     the second delivery into a no-op instead of a duplicate sale.
 */
class ShopifyWebhookController extends Controller
{
    /**
     * Topics that do something. Anything else is recorded and ignored.
     *
     * The list lives on the model, next to the entity switch each topic obeys,
     * so what registration subscribes to and what this accepts on arrival are
     * the same list and cannot drift.
     */
    private function handles(string $topic): bool
    {
        return array_key_exists($topic, ShopifyStore::WEBHOOK_TOPICS);
    }

    public function handle(Request $request)
    {
        $topic = (string) $request->header('X-Shopify-Topic', '');
        $domain = (string) $request->header('X-Shopify-Shop-Domain', '');
        $hmac = (string) $request->header('X-Shopify-Hmac-Sha256', '');
        $eventId = (string) $request->header('X-Shopify-Webhook-Id', '');
        $raw = $request->getContent();

        if ($topic === '' || $domain === '') {
            return response()->json(['ok' => false], 400);
        }

        $store = ShopifyStore::whereNull('deleted_at')
            ->where('shop_domain', ShopifyStore::normaliseDomain($domain))
            ->first();

        if (! $store) {
            // 200 on purpose: a 4xx makes Shopify retry for days over a shop we
            // do not know and never will.
            return response()->json(['ok' => true, 'ignored' => 'unknown shop'], 200);
        }

        if (! $this->verifySignature($store, $raw, $hmac)) {
            [$reason, $detail] = $this->rejectionDetail($store, $raw, $hmac);

            // Shopify retries a refused delivery for up to 48 hours (19 tries)
            // and keeps the same webhook id, so log a given rejection once —
            // nineteen identical rows told the reader nothing the first did
            // not, and buried the deliveries that were new.
            $firstSeen = $eventId === '' || \Illuminate\Support\Facades\Cache::add(
                'shopify:webhook-reject:'.$store->id.':'.$eventId, 1, now()->addHours(48)
            );

            if ($firstSeen) {
                // The API version is the one the subscription was created with,
                // so it separates deliveries from an older app's subscriptions
                // from the ones this connection registered.
                ShopifyLog::write($store->id, 'webhook.reject', 'Rejected '.$topic.' — '.$reason.'.', 'error',
                    array_merge([
                        'topic' => $topic,
                        'webhook_id' => $eventId,
                        'api_version' => (string) $request->header('X-Shopify-API-Version', ''),
                        'triggered_at' => (string) $request->header('X-Shopify-Triggered-At', ''),
                    ], $detail), null, 'webhooks');
            }

            return response()->json(['ok' => false], 401);
        }

        $payload = json_decode($raw, true);
        if (! is_array($payload)) {
            return response()->json(['ok' => false], 400);
        }

        // Fall back to the resource id when Shopify omits the webhook id, so
        // there is always something to deduplicate on.
        $eventId = $eventId !== '' ? $eventId : ($topic.':'.($payload['id'] ?? md5($raw)));

        $existing = ShopifyWebhookEvent::where('store_id', $store->id)->where('event_id', $eventId)->first();
        if ($existing) {
            return response()->json(['ok' => true, 'duplicate' => true], 200);
        }

        try {
            $event = ShopifyWebhookEvent::create([
                'store_id' => $store->id,
                'topic' => $topic,
                'event_id' => $eventId,
                'payload' => $payload,
                'status' => 'pending',
            ]);
        } catch (\Throwable $e) {
            // The unique index fired: another delivery of the same event beat us
            // to it. That is the index doing its job, not an error.
            return response()->json(['ok' => true, 'duplicate' => true], 200);
        }

        if (! $this->handles($topic)) {
            $event->update(['status' => 'ignored', 'processed_at' => now()]);

            return response()->json(['ok' => true, 'ignored' => true], 200);
        }

        try {
            $this->process($store, $topic, $payload);
            $event->markProcessed();
        } catch (\Throwable $e) {
            $event->markFailed($e->getMessage());
            ShopifyLog::write($store->id, 'webhook.error', $topic.': '.$e->getMessage(), 'error',
                ['topic' => $topic, 'event_id' => $eventId], null, 'webhooks');

            // Still 200: the event is stored and can be replayed from the UI.
            // Returning 500 would make Shopify hammer us and eventually disable
            // the subscription outright.
            return response()->json(['ok' => true, 'stored' => true], 200);
        }

        return response()->json(['ok' => true], 200);
    }

    /**
     * HMAC-SHA256 of the raw body, base64, compared in constant time.
     * Must run against the raw body — re-encoding the parsed JSON changes the
     * bytes and the digest will never match.
     */
    private function verifySignature(ShopifyStore $store, string $raw, string $hmac): bool
    {
        $secret = $store->webhookSigningSecret();

        if ($secret === '' || $hmac === '') {
            return false;
        }

        $expected = base64_encode(hash_hmac('sha256', $raw, $secret, true));

        return hash_equals($expected, $hmac);
    }

    /**
     * Why a delivery was refused, in terms someone can act on.
     *
     * "Signature did not verify" covered three different faults — no secret
     * stored, the access token pasted in place of the secret, and a genuine
     * mismatch — which is why it was possible to stare at the log and learn
     * nothing. The digest prefixes are safe to record: they are the first
     * bytes of a MAC that already failed, and the log is admin-only.
     */
    private function rejectionDetail(ShopifyStore $store, string $raw, string $hmac): array
    {
        $secret = $store->webhookSigningSecret();

        if ($secret === '') {
            return ['no signing secret is stored for this shop', []];
        }

        if ($hmac === '') {
            return ['the delivery carried no signature header', []];
        }

        if (str_starts_with($secret, 'shpat_') || str_starts_with($secret, 'shpca_')) {
            return ['the stored secret is an Admin API access token, not the app\'s API secret key', []];
        }

        $expected = base64_encode(hash_hmac('sha256', $raw, $secret, true));

        $explicit = trim((string) $store->webhook_secret);
        $usedExplicit = $explicit !== '';
        $detail = [
            'body_bytes' => strlen($raw),
            'expected_prefix' => substr($expected, 0, 8),
            'received_prefix' => substr($hmac, 0, 8),
            'secret_length' => strlen($secret),
            'secret_source' => $usedExplicit ? 'webhook_secret field' : 'client secret',
        ];

        // A pasted signing secret wins over the client secret. When the pasted
        // one fails but the client secret would have verified, the field is
        // the whole problem — say so instead of leaving it to be guessed.
        if ($usedExplicit && $store->usesClientCredentials()) {
            $viaClient = base64_encode(hash_hmac('sha256', $raw, trim((string) $store->client_secret), true));
            if (hash_equals($viaClient, $hmac)) {
                return [
                    'the pasted webhook signing secret is wrong, but the client secret verifies this delivery — clear the "Webhook signing secret" field on this store',
                    $detail,
                ];
            }
        }

        // Shopify signs each delivery with the secret of the app that created
        // the subscription. A shop reconnected through a new app keeps the
        // old app's subscriptions, which this app's token cannot list, so they
        // keep arriving here signed with a secret we no longer hold.
        $reason = 'the signature did not match the stored secret';
        if ($store->usesClientCredentials() && ! $usedExplicit) {
            $reason .= ' — the delivery was signed by a different app than the one connected here. If this shop was connected with another app before, uninstall that app in Shopify admin (Settings → Apps and sales channels) or delete its webhooks, then register webhooks again from this page';
        }

        return [$reason, $detail];
    }

    private function process(ShopifyStore $store, string $topic, array $payload): void
    {
        // Everything below writes local records, and some of it writes local
        // stock. Muted so the instant-push watcher does not send the shop back
        // the very numbers it just told us about.
        StockWatcher::muted(fn () => $this->dispatchTopic($store, $topic, $payload));
    }

    private function dispatchTopic(ShopifyStore $store, string $topic, array $payload): void
    {
        $sync = SyncService::for($store);

        switch ($topic) {
            case 'orders/create':
                if ($store->syncs('orders')) {
                    $this->importOrder($sync, $store, $payload);
                }
                break;

            case 'orders/updated':
            case 'orders/cancelled':
            case 'orders/fulfilled':
                if ($store->syncs('orders')) {
                    $this->refreshOrder($sync, $store, $payload);
                }
                break;

            case 'products/create':
            case 'products/update':
                if ($store->syncs('products')) {
                    $this->runSingle($store, 'products', 'pull', $payload);
                }
                break;

            case 'customers/create':
            case 'customers/update':
                if ($store->syncs('customers')) {
                    $this->runSingle($store, 'customers', 'pull', $payload);
                }
                break;

            case 'inventory_levels/update':
                if ($store->syncs('inventory')) {
                    $this->applyInventoryLevel($store, $payload);
                }
                break;
        }
    }

    /**
     * Import one order using the same code path a full sync uses, so a webhook
     * and a manual sync cannot produce differently-shaped sales.
     */
    private function importOrder(SyncService $sync, ShopifyStore $store, array $payload): void
    {
        $this->withRun($sync, $store, 'orders', 'pull', fn () => $sync->pullOrder($payload));
    }

    /** Update shipping/payment status on an order already imported. */
    private function refreshOrder(SyncService $sync, ShopifyStore $store, array $payload): void
    {
        $shopifyId = (string) ($payload['id'] ?? '');
        if ($shopifyId === '') {
            return;
        }

        $localId = ShopifyLink::localIdFor($store->id, 'order', $shopifyId);

        // Not imported yet — an update is the first we have heard of it.
        if (! $localId) {
            $this->importOrder($sync, $store, $payload);

            return;
        }

        $status = $sync->mapOrderStatus($payload);
        $sale = \App\Models\Sale::whereNull('deleted_at')->find($localId);
        if ($sale) {
            $sale->shipping_status = $status['shipping'];
            $sale->payment_statut = $status['payment'];
            $sale->save();
        }
    }

    private function runSingle(ShopifyStore $store, string $entity, string $direction, array $payload): void
    {
        $sync = SyncService::for($store);

        $this->withRun($sync, $store, $entity, $direction, function () use ($sync, $entity, $payload) {
            if ($entity === 'products') {
                $sync->pullProduct($payload);
            } else {
                $sync->pullCustomer($payload);
            }
        });
    }

    /** Give a single-record webhook handler a run row to write its counters to. */
    private function withRun(SyncService $sync, ShopifyStore $store, string $entity, string $direction, callable $work): void
    {
        $run = \App\Models\ShopifySyncRun::create([
            'store_id' => $store->id,
            'entity' => $entity,
            'direction' => $direction,
            'status' => 'running',
            'stage' => 'webhook',
            'total_items' => 1,
            'started_at' => now(),
            'heartbeat_at' => now(),
        ]);

        $sync->attachRun($run);

        try {
            $work();
            $run->refresh();
            $run->update([
                'status' => 'completed',
                'processed_items' => max(1, (int) $run->processed_items),
                'percentage' => 100,
                'finished_at' => now(),
            ]);
        } catch (\Throwable $e) {
            $run->update(['status' => 'failed', 'last_error' => $e->getMessage(), 'finished_at' => now()]);
            throw $e;
        }
    }

    private function applyInventoryLevel(ShopifyStore $store, array $payload): void
    {
        // Only the location this store is bound to; other locations are not ours.
        if ((string) ($payload['location_id'] ?? '') !== (string) $store->location_id) {
            return;
        }
        if (! $store->warehouse_id) {
            return;
        }

        $link = ShopifyLink::where('store_id', $store->id)
            ->whereIn('entity_type', ['inventory_item', 'variant'])
            ->where('secondary_id', (string) ($payload['inventory_item_id'] ?? ''))
            ->first();

        if (! $link) {
            return;
        }

        SyncService::for($store)
            ->writeLocalStock($link, (int) $store->warehouse_id, (float) ($payload['available'] ?? 0));
    }

    // ---------------------------------------------------------------- admin --

    /** Stored webhook deliveries, for the UI. */
    public function events(Request $request)
    {
        $this->authorizeStore($request);

        $perPage = $request->limit ?: 25;
        $pageStart = \Request::get('page', 1);
        $offSet = ($pageStart * $perPage) - $perPage;

        $query = ShopifyWebhookEvent::query()
            ->when($request->filled('store_id'), fn ($q) => $q->where('store_id', $request->store_id))
            ->when($request->filled('topic'), fn ($q) => $q->where('topic', $request->topic))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status));

        $totalRows = $query->count();
        if ($perPage == '-1') {
            $perPage = $totalRows ?: 1;
        }

        $rows = $query->offset($offSet)->limit($perPage)->orderByDesc('id')->get()
            ->map(fn ($e) => [
                'id' => $e->id,
                'store_id' => $e->store_id,
                'topic' => $e->topic,
                'event_id' => $e->event_id,
                'status' => $e->status,
                'error' => $e->error,
                'processed_at' => $e->processed_at ? $e->processed_at->toDateTimeString() : null,
                'created_at' => $e->created_at ? $e->created_at->toDateTimeString() : null,
            ]);

        return response()->json(['events' => $rows, 'totalRows' => $totalRows]);
    }

    /** Re-run a webhook that failed, without asking Shopify to resend it. */
    public function replay(Request $request, $id)
    {
        $this->authorizeStore($request);

        $event = ShopifyWebhookEvent::findOrFail($id);
        $store = ShopifyStore::whereNull('deleted_at')->find($event->store_id);

        if (! $store) {
            return response()->json(['success' => false, 'message' => 'That store is no longer connected.'], 422);
        }

        try {
            $this->process($store, $event->topic, $event->payload ?: []);
            $event->markProcessed();

            return response()->json(['success' => true]);
        } catch (\Throwable $e) {
            $event->markFailed($e->getMessage());

            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    private function authorizeStore(Request $request): void
    {
        app(\Illuminate\Contracts\Auth\Access\Gate::class)
            ->forUser($request->user('api'))
            ->authorize('view', ShopifyStore::class);
    }
}

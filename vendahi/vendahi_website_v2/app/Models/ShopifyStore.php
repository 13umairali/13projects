<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A connected Shopify shop. Several may exist at once; every sync, link and log
 * row is scoped to one of these.
 */
class ShopifyStore extends Model
{
    protected $table = 'shopify_stores';

    protected $dates = ['deleted_at'];

    protected $fillable = [
        'name',
        'shop_domain',
        'access_token',
        'client_id',
        'client_secret',
        'token_expires_at',
        'api_version',
        'webhook_secret',
        'warehouse_id',
        'location_id',
        'currency',
        'shop_name',
        'shop_email',
        'status',
        'last_error',
        'last_connected_at',
        'price_field',
        'create_missing_products',
        'create_missing_customers',
        'auto_sync',
        'sync_interval_minutes',
        'auto_sync_plan',
        'instant_stock_push',
        'sync_products',
        'sync_inventory',
        'sync_customers',
        'sync_orders',
        'sync_collections',
        'sync_fulfillments',
        'last_sync_at',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    /**
     * The token never leaves the server. Anything that serialises a store for
     * the UI goes through toPublicArray(), which omits it entirely.
     */
    protected $hidden = ['access_token', 'client_secret', 'webhook_secret'];

    protected $casts = [
        'create_missing_products' => 'boolean',
        'create_missing_customers' => 'boolean',
        'auto_sync' => 'boolean',
        'sync_products' => 'boolean',
        'sync_inventory' => 'boolean',
        'sync_customers' => 'boolean',
        'sync_orders' => 'boolean',
        'sync_collections' => 'boolean',
        'sync_fulfillments' => 'boolean',
        'sync_interval_minutes' => 'integer',
        'auto_sync_plan' => 'array',
        'instant_stock_push' => 'boolean',
        'last_sync_at' => 'datetime',
        'last_connected_at' => 'datetime',
        'token_expires_at' => 'datetime',
    ];

    /**
     * Whether this store was connected with a Dev Dashboard app (client id +
     * client secret, token fetched and renewed by the ERP) rather than a
     * permanent token pasted from a legacy custom app. See
     * App\Services\Shopify\AccessToken for what that changes.
     */
    public function usesClientCredentials(): bool
    {
        return trim((string) $this->client_id) !== '' && trim((string) $this->client_secret) !== '';
    }

    /**
     * The secret Shopify signs webhook deliveries with.
     *
     * For a legacy custom app it is the "API secret key" pasted into
     * webhook_secret. For a Dev Dashboard app it is the client secret, which
     * is already stored, so a store connected that way needs nothing extra —
     * unless someone pastes a webhook_secret explicitly, which then wins.
     */
    public function webhookSigningSecret(): string
    {
        $explicit = trim((string) $this->webhook_secret);
        if ($explicit !== '') {
            return $explicit;
        }

        return $this->usesClientCredentials() ? trim((string) $this->client_secret) : '';
    }

    public function hasWebhookSecret(): bool
    {
        return $this->webhookSigningSecret() !== '';
    }

    /** The entities this module knows how to move, in dependency order. */
    public const ENTITIES = ['products', 'inventory', 'customers', 'orders', 'collections', 'fulfillments'];

    /**
     * Orders come from the shop; pushing an ERP sale back would invent an order
     * that nobody placed. Kept here rather than in a controller so the API, the
     * scheduler and the plan validator cannot drift apart.
     */
    public const PULL_ONLY = ['orders'];

    /**
     * The webhook topics this ERP knows how to handle, each with the entity
     * switch that governs it. Registration subscribes only to the topics whose
     * entity is switched on, and the inbound handler accepts only what is
     * listed here — one list, so a topic can never be subscribed to and then
     * silently dropped on arrival.
     */
    public const WEBHOOK_TOPICS = [
        'orders/create' => 'orders',
        'orders/updated' => 'orders',
        'orders/cancelled' => 'orders',
        'orders/fulfilled' => 'orders',
        'products/create' => 'products',
        'products/update' => 'products',
        'customers/create' => 'customers',
        'customers/update' => 'customers',
        'inventory_levels/update' => 'inventory',
    ];

    /**
     * What a store syncs automatically when no plan has been chosen.
     *
     * Deliberately the two nobody has to think about: orders must come in or
     * the shop's sales never reach the ERP, and stock must go out or the shop
     * oversells. Everything else — pushing a catalogue, pulling customers —
     * changes records in bulk in a direction only the operator knows is right,
     * so it is opt-in.
     */
    public const DEFAULT_AUTO_PLAN = [
        ['entity' => 'orders', 'direction' => 'pull'],
        ['entity' => 'inventory', 'direction' => 'push'],
    ];

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    public function links()
    {
        return $this->hasMany(ShopifyLink::class, 'store_id');
    }

    public function runs()
    {
        return $this->hasMany(ShopifySyncRun::class, 'store_id');
    }

    /** Whether this store is allowed to sync the given entity at all. */
    public function syncs(string $entity): bool
    {
        $flag = 'sync_'.$entity;

        return (bool) ($this->$flag ?? false);
    }

    /** Whether an entity can travel this way at all, regardless of settings. */
    public static function supportsDirection(string $entity, string $direction): bool
    {
        if (! in_array($entity, self::ENTITIES, true) || ! in_array($direction, ['push', 'pull'], true)) {
            return false;
        }

        return ! ($direction === 'push' && in_array($entity, self::PULL_ONLY, true));
    }

    /**
     * Clean a plan submitted by the UI (or stored by an older version).
     *
     * Unknown entities, impossible directions and duplicate steps are dropped
     * rather than rejected: a plan is a preference, and refusing to save a
     * whole store because one stale row mentions an entity that no longer
     * exists would be the worse failure. An empty result becomes null, which
     * reads as "use the default" — the alternative, an auto-sync that is
     * switched on and silently does nothing, is a trap.
     */
    public static function sanitisePlan($input): ?array
    {
        if (is_string($input)) {
            $input = json_decode($input, true);
        }
        if (! is_array($input)) {
            return null;
        }

        $clean = [];

        foreach ($input as $entry) {
            if (! is_array($entry)) {
                continue;
            }

            $entity = (string) ($entry['entity'] ?? '');
            $direction = (string) ($entry['direction'] ?? '');

            if (! self::supportsDirection($entity, $direction)) {
                continue;
            }

            // One step per entity+direction: two "products push" steps would
            // race each other for the same link rows.
            $key = $entity.':'.$direction;
            if (isset($clean[$key])) {
                continue;
            }

            $step = ['entity' => $entity, 'direction' => $direction];

            // An interval below five minutes cannot be honoured — the scheduler
            // only ticks that often — and a week is as far out as it is worth
            // calling automatic.
            $every = (int) ($entry['every'] ?? 0);
            if ($every > 0) {
                $step['every'] = max(5, min($every, 10080));
            }

            $clean[$key] = $step;
        }

        return $clean === [] ? null : array_values($clean);
    }

    /**
     * The steps a scheduled run should actually perform, with every interval
     * resolved. Steps for an entity that is switched off are dropped here, so
     * turning "Products" off on the entities tab also stops the automatic
     * product step without anyone having to remember to edit the plan too.
     *
     * @return array<int, array{entity: string, direction: string, every: int}>
     */
    public function autoSyncPlan(): array
    {
        $plan = self::sanitisePlan($this->auto_sync_plan) ?: self::DEFAULT_AUTO_PLAN;
        $interval = max(5, (int) ($this->sync_interval_minutes ?: 60));

        $steps = [];

        foreach ($plan as $entry) {
            if (! $this->syncs($entry['entity'])) {
                continue;
            }

            $steps[] = [
                'entity' => $entry['entity'],
                'direction' => $entry['direction'],
                'every' => max(5, (int) ($entry['every'] ?? 0) ?: $interval),
            ];
        }

        return $steps;
    }

    /** Stores the scheduler should consider on this tick. */
    public function scopeAutoSyncable($query)
    {
        return $query->whereNull('deleted_at')
            ->where('auto_sync', 1)
            ->where('status', 'connected');
    }

    /**
     * Normalise whatever the user typed into a bare myshopify domain:
     * "https://acme.myshopify.com/admin" and "acme" both become
     * "acme.myshopify.com".
     */
    public static function normaliseDomain(?string $input): string
    {
        $value = trim((string) $input);
        if ($value === '') {
            return '';
        }

        $value = preg_replace('#^https?://#i', '', $value);
        $value = explode('/', $value)[0];
        $value = strtolower(trim($value));

        if ($value !== '' && ! str_contains($value, '.')) {
            $value .= '.myshopify.com';
        }

        return $value;
    }

    /** Safe representation for the API: no token, no secret. */
    public function toPublicArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'shop_domain' => $this->shop_domain,
            'api_version' => $this->api_version,
            'warehouse_id' => $this->warehouse_id,
            'warehouse_name' => $this->warehouse ? $this->warehouse->name : null,
            'location_id' => $this->location_id,
            'currency' => $this->currency,
            'shop_name' => $this->shop_name,
            'shop_email' => $this->shop_email,
            'status' => $this->status,
            'last_error' => $this->last_error,
            'last_connected_at' => $this->last_connected_at ? $this->last_connected_at->toDateTimeString() : null,
            'last_sync_at' => $this->last_sync_at ? $this->last_sync_at->toDateTimeString() : null,
            'price_field' => $this->price_field,
            'create_missing_products' => (bool) $this->create_missing_products,
            'create_missing_customers' => (bool) $this->create_missing_customers,
            'auto_sync' => (bool) $this->auto_sync,
            'sync_interval_minutes' => (int) $this->sync_interval_minutes,
            // What was chosen (null = "the default"), and what that resolves to
            // once intervals and switched-off entities are taken into account.
            'auto_sync_plan' => self::sanitisePlan($this->auto_sync_plan),
            'auto_sync_steps' => $this->autoSyncPlan(),
            'instant_stock_push' => (bool) $this->instant_stock_push,
            'sync_products' => (bool) $this->sync_products,
            'sync_inventory' => (bool) $this->sync_inventory,
            'sync_customers' => (bool) $this->sync_customers,
            'sync_orders' => (bool) $this->sync_orders,
            'sync_collections' => (bool) $this->sync_collections,
            'sync_fulfillments' => (bool) $this->sync_fulfillments,
            // So the UI can tell "no token yet" from "token saved" without ever
            // receiving the token.
            'has_token' => ! empty($this->access_token),
            'has_webhook_secret' => $this->hasWebhookSecret(),
            // Whether a secret was pasted by hand (as opposed to the client
            // secret standing in), so the form can offer to clear it.
            'has_explicit_webhook_secret' => trim((string) $this->webhook_secret) !== '',
            // Which kind of app this store was connected with. The client id
            // is not a secret (it is visible in the Dev Dashboard) so the UI
            // can show it back; the client secret never leaves the server.
            'auth_mode' => $this->usesClientCredentials() ? 'client_credentials' : 'token',
            'client_id' => $this->usesClientCredentials() ? $this->client_id : null,
            'has_client_secret' => trim((string) $this->client_secret) !== '',
            'token_expires_at' => $this->token_expires_at ? $this->token_expires_at->toDateTimeString() : null,
        ];
    }
}

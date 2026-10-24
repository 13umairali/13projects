# Shopify integration

Ported from single-tenant Stocky. The sync itself is byte-for-byte the same
logic; this note is only about what multi-tenancy changed, so a reader who
knows the Stocky module does not have to diff the whole thing.

## Shape

| File | What it is |
|---|---|
| `Client.php` / `Response.php` | Admin REST client: rate limiting, retries, cursor paging. |
| `SyncService.php` | The whole sync. One entity, one direction, one run row. |
| `AutoSync.php` | Reads a store's plan and answers "what is due?". |
| `StockWatcher.php` | Notices `product_warehouse` writes, queues instant stock pushes. |
| `WebhookManager.php` | Registers/lists/deletes Shopify webhooks for a store. |
| `../../Console/Commands/ShopifySync.php` | `php artisan shopify:sync` — the scheduled half. |
| `../../Http/Controllers/Shopify{Store,Sync,Webhook}Controller.php` | The API behind `resources/src/pages/shopify/`. |

Tables are per tenant (`database/migrations/tenant/2026_09_18_000001_create_shopify_tables.php`):
`shopify_stores`, `shopify_links`, `shopify_sync_runs`, `shopify_logs`,
`shopify_webhook_events`. A mapping is a row in `shopify_links`, never a column
on `products` — that is what lets one product live in several shops at once.

## What multi-tenancy changed

**Scheduling.** `shopify:sync` is on the platform schedule (`app/Console/Kernel.php`,
every five minutes). With no `--tenant` it sweeps every **active** tenant whose
plan carries the `shopify` feature, entering and leaving each one in turn; run
under `tenants:run`, or with `--tenant=<id>`, it stays in the one tenant and
behaves exactly like the single-tenant command. Naming a tenant skips the plan
check — that is the operator saying they know what they are doing.

```bash
php artisan shopify:sync --list                      # every tenant: what is due
php artisan shopify:sync --tenant=<id> --list        # one tenant's schedule
php artisan shopify:sync --tenant=<id> --store=3 --force
```

Each tenant takes its own lock (`Cache::lock('shopify:sync')` — the cache store
is tenant-prefixed), so one slow catalogue push blocks that tenant's next tick
and nobody else's.

**The stock watcher.** It keeps two things in process statics: which shops want
instant pushes, and which products have moved. Neither survives a tenant switch,
so `App\Listeners\ResetShopifyStockWatcher` flushes and clears it on
`InitializingTenancy` and `EndingTenancy` — both fire while the *outgoing*
context is still current, so the buffer is dispatched against the database that
filled it. Without this, a worker's first (central) lookup would cache "no shops
are listening" and instant push would silently never fire.

**Webhooks.** One public route, `POST /api/shopify/webhook`, inside the tenant
route group: the tenant is identified by host, and the shop by the
`X-Shopify-Shop-Domain` header within that tenant. So the URL registered with
Shopify must be the tenant's own host — `WebhookManager::callbackUrl()` uses the
request host in a web request (registration happens from the tenant's admin) and
`tenant()->getTenantUrl()` on the CLI, where `url()` would answer the central
domain.

**Gating.** Routes sit behind `tenant.feature:shopify`; the menu entries and the
Integrations Hub card repeat `feature: 'shopify'` on every *leaf*, because the
sidebars only run their visibility check on entries they render as links.
Permissions are `shopify_stores` (read), `shopify_sync` (write to a shop) and
`shopify_logs` — seeded in `PermissionsSeeder` and created for existing tenants
by `UpdateController` / `AutoUpdateController`.

**Images.** `products.image` is a filename under the tenant's own upload folder
(`upload_path('products')`), not a shared `public/images/products`, so the push
resolves file sources through `upload_public_path()`.

**Variant options.** Stocky's optional Color/Size layer (`ProductOptionService`)
is not here. `SyncService::shopifyOptions()` falls back to Shopify's single
"Variant" option carrying the variant names — which is also what the pull side
matches on — and picks the richer service up automatically if that module is
ever ported.

## The predecessor

There was an earlier one-shop-per-tenant integration (`ShopifyMapping`, a
differently shaped `shopify_stores`). Its tables are renamed to `*_v1` by the
migration rather than dropped, so a tenant's old access token is still readable
if a reconnection goes wrong. Nothing reads them; they can be cleared out once
every tenant is across.

## Credentials: token or client id + secret

Two kinds of store exist since 2026-09-18 (`AccessToken.php`), identical to Stocky:

- **Legacy custom app** (admin-created before 2026-01-01): a permanent `shpat_` token pasted
  into `access_token`. Nothing to refresh.
- **Dev Dashboard app** (the only kind Shopify creates now): `client_id` + `client_secret`
  on the store row. `AccessToken::for()` runs the client-credentials grant
  (`POST https://{shop}/admin/oauth/access_token`, `grant_type=client_credentials`), caches the
  24-hour token in `access_token` + `token_expires_at`, and refreshes it five minutes early.
  `Client::forStore($store)` resolves the token and retries once on 401, so build every
  client through it, never `new Client($store->access_token)`. The client secret also signs
  webhooks: read `$store->webhookSigningSecret()` / `hasWebhookSecret()`, not `webhook_secret`.
  The grant only works when the app and the shop belong to the same Shopify organisation.

Tenant migration: `database/migrations/tenant/2026_09_18_000002_add_client_credentials_to_shopify_stores.php`.
The token cache is per store row, so it is per tenant like everything else here.

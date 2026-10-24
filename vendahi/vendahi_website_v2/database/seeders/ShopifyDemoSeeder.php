<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Client;
use App\Models\Product;
use App\Models\product_warehouse;
use App\Models\ProductVariant;
use App\Models\Sale;
use App\Models\ShopifyLink;
use App\Models\ShopifyLog;
use App\Models\ShopifyStore;
use App\Models\ShopifySyncRun;
use App\Models\ShopifyWebhookEvent;
use App\Models\Unit;
use App\Models\Warehouse;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

/**
 * Demo data for the Shopify module: two connected shops with mappings, a
 * fortnight of sync history, logs and received webhooks, so every screen —
 * dashboard, stores, store detail, sync centre, mappings, logs — shows what it
 * looks like in use instead of an empty state.
 *
 *   php artisan tenants:run db:seed --tenants=<tenant> --argument="class=Database\Seeders\ShopifyDemoSeeder"
 *
 * It writes into ONE tenant's database, so it has to be run inside a tenant —
 * `db:seed` on its own would try the central connection, where none of these
 * tables exist.
 *
 * Two things this deliberately does NOT do:
 *
 *  - It does not switch automatic sync on. The demo domains are not real
 *    shops, so a cron tick would try to reach them, fail, and write an error
 *    every five minutes forever. Everything else about the plan is configured;
 *    turn the switch on for a shop that actually exists.
 *  - It does not invent credentials that could work. The tokens are obvious
 *    placeholders, so nothing here can talk to Shopify even by accident.
 *
 * Re-running it replaces its own data and touches nothing else. Products and
 * customers are mapped from whatever the install already has; on an empty
 * database it creates a small SH-* catalogue to map, which you can delete.
 */
class ShopifyDemoSeeder extends Seeder
{
    /** The shops this seeder owns — re-running clears exactly these. */
    private const DOMAINS = ['demo-shop.myshopify.com', 'demo-outlet-eu.myshopify.com'];

    public function run(): void
    {
        if (! Schema::hasTable('shopify_stores')) {
            $this->command?->warn('The Shopify tables are not there yet — run `php artisan migrate` first.');

            return;
        }

        $this->forget();

        $warehouse = Warehouse::whereNull('deleted_at')->orderBy('id')->first();
        if (! $warehouse) {
            $this->command?->warn('No warehouse exists, so there is nowhere for a shop to sync into. Seed the base data first.');

            return;
        }

        $products = $this->products();
        $clients = $this->clients();

        $main = $this->mainStore($warehouse);
        $outlet = $this->outletStore($warehouse);

        $linked = $this->mapRecords($main, $products, $clients);
        $this->mapRecords($outlet, $products->take(4), $clients->take(1), 'EU');

        $runs = $this->history($main);
        $this->outletHistory($outlet);
        $this->logs($main, $runs);
        $this->webhooks($main);

        $this->command?->info('Shopify demo data ready:');
        $this->command?->line('  • 2 shops — '.implode(', ', self::DOMAINS));
        $this->command?->line('  • '.$linked.' mappings, '.ShopifySyncRun::whereIn('store_id', [$main->id, $outlet->id])->count().' sync runs, '
            .ShopifyLog::whereIn('store_id', [$main->id, $outlet->id])->count().' log entries, '
            .ShopifyWebhookEvent::where('store_id', $main->id)->count().' webhook events');
        $this->command?->line('  • Open Shopify → Stores → Demo Storefront');
        $this->command?->warn('  Automatic sync is left OFF on purpose: these domains are not real shops, so scheduled runs would fail every five minutes.');
    }

    // ------------------------------------------------------------- cleanup --

    /** Remove a previous run of this seeder, and nothing else. */
    private function forget(): void
    {
        $ids = ShopifyStore::whereIn('shop_domain', self::DOMAINS)->pluck('id');
        if ($ids->isEmpty()) {
            return;
        }

        ShopifyLink::whereIn('store_id', $ids)->delete();
        ShopifySyncRun::whereIn('store_id', $ids)->delete();
        ShopifyLog::whereIn('store_id', $ids)->delete();
        ShopifyWebhookEvent::whereIn('store_id', $ids)->delete();
        ShopifyStore::whereIn('id', $ids)->forceDelete();
    }

    // -------------------------------------------------------- local records --

    /** Products to map: whatever exists, or a small catalogue of our own. */
    private function products()
    {
        $existing = Product::whereNull('deleted_at')->orderBy('id')->limit(12)->get();
        if ($existing->isNotEmpty()) {
            return $existing;
        }

        $unit = Unit::firstOrCreate(
            ['ShortName' => 'pc'],
            ['name' => 'Piece', 'base_unit' => null, 'operator' => '*', 'operator_value' => 1]
        );
        $category = Category::firstOrCreate(['name' => 'Shopify Demo'], ['code' => 'SH-DEMO']);
        $brand = Brand::firstOrCreate(['name' => 'Demo Brand'], ['description' => 'Created by the Shopify demo seeder']);
        $warehouse = Warehouse::whereNull('deleted_at')->orderBy('id')->first();

        $rows = [
            ['Classic Cotton T-Shirt', 12, 24.00],
            ['Canvas Tote Bag', 6, 15.00],
            ['Enamel Mug 350ml', 4, 11.50],
            ['Wool Beanie', 7, 18.00],
            ['Leather Card Holder', 9, 29.00],
            ['Stainless Water Bottle', 10, 26.00],
            ['Linen Tea Towel', 3, 9.50],
            ['Scented Soy Candle', 8, 21.00],
        ];

        $made = collect();

        foreach ($rows as $i => [$name, $cost, $price]) {
            $product = new Product;
            $product->forceFill([
                'code' => 'SH-'.str_pad((string) ($i + 1), 4, '0', STR_PAD_LEFT),
                'Type_barcode' => 'CODE128',
                'name' => $name,
                'cost' => $cost,
                'price' => $price,
                // Both are NOT NULL without a default on this table.
                'wholesale_price' => round($price * 0.85, 2),
                'min_price' => round($price * 0.75, 2),
                'unit_id' => $unit->id,
                'unit_sale_id' => $unit->id,
                'unit_purchase_id' => $unit->id,
                'stock_alert' => 5,
                'category_id' => $category->id,
                'brand_id' => $brand->id,
                'is_variant' => 0,
                'tax_method' => '1',
                'TaxNet' => 0,
                'type' => 'is_single',
                'is_active' => 1,
                'note' => 'Shopify demo product — safe to delete.',
                'created_at' => Carbon::now()->subDays(random_int(5, 60)),
                'updated_at' => Carbon::now(),
            ]);
            $product->save();

            if ($warehouse) {
                product_warehouse::create([
                    'product_id' => $product->id,
                    'warehouse_id' => $warehouse->id,
                    'qte' => random_int(8, 90),
                    'manage_stock' => 1,
                ]);
            }

            $made->push($product);
        }

        $this->command?->line('  (no products existed, so '.$made->count().' SH-* demo products were created to map)');

        return $made;
    }

    /** Customers to map: whatever exists, or three of our own. */
    private function clients()
    {
        $existing = Client::whereNull('deleted_at')->orderBy('id')->limit(6)->get();
        if ($existing->isNotEmpty()) {
            return $existing;
        }

        $made = collect();

        // `code` is a required integer on this table, so it is generated
        // rather than left to a default that does not exist.
        $nextCode = (int) (Client::max('code') ?: 0);

        foreach ([['Ada Lovelace', 'ada@example.com'], ['Grace Hopper', 'grace@example.com'], ['Alan Turing', 'alan@example.com']] as $i => [$name, $email]) {
            $made->push(Client::firstOrCreate(
                ['email' => $email],
                [
                    'name' => $name,
                    'code' => ++$nextCode,
                    'phone' => '+1 555 010'.$i,
                    'country' => 'US',
                    'city' => 'Springfield',
                ]
            ));
        }

        return $made;
    }

    // ---------------------------------------------------------------- shops --

    private function mainStore(Warehouse $warehouse): ShopifyStore
    {
        return ShopifyStore::create([
            'name' => 'Demo Storefront',
            'shop_domain' => self::DOMAINS[0],
            'access_token' => 'shpat_demo_seeder_not_a_real_token',
            'api_version' => '2024-10',
            'webhook_secret' => 'demo_seeder_not_a_real_secret',
            'warehouse_id' => $warehouse->id,
            'location_id' => '76543210987',
            'currency' => 'USD',
            'shop_name' => 'Demo Storefront',
            'shop_email' => 'owner@demo-shop.example',
            'status' => 'connected',
            'last_connected_at' => Carbon::now()->subMinutes(12),
            'price_field' => 'price',
            'create_missing_products' => 1,
            'create_missing_customers' => 1,
            // Off on purpose — see the class docblock.
            'auto_sync' => 0,
            'instant_stock_push' => 0,
            'sync_interval_minutes' => 60,
            // A plan worth looking at: orders often, stock regularly, the
            // catalogue twice a day.
            'auto_sync_plan' => [
                ['entity' => 'orders', 'direction' => 'pull', 'every' => 15],
                ['entity' => 'inventory', 'direction' => 'push', 'every' => 30],
                ['entity' => 'products', 'direction' => 'push', 'every' => 720],
            ],
            'sync_products' => 1,
            'sync_inventory' => 1,
            'sync_customers' => 1,
            'sync_orders' => 1,
            'sync_collections' => 1,
            'sync_fulfillments' => 1,
            'last_sync_at' => Carbon::now()->subMinutes(24),
        ]);
    }

    /**
     * A second shop that is not reachable — so the list shows both states, and
     * the detail page shows the error banner and what a blocked step looks
     * like (this one has no Shopify location set).
     */
    private function outletStore(Warehouse $warehouse): ShopifyStore
    {
        return ShopifyStore::create([
            'name' => 'Demo Outlet (EU)',
            'shop_domain' => self::DOMAINS[1],
            'access_token' => 'shpat_demo_seeder_expired_token',
            'api_version' => '2024-10',
            'warehouse_id' => $warehouse->id,
            'location_id' => null,
            'currency' => 'EUR',
            'shop_name' => 'Demo Outlet EU',
            'status' => 'error',
            'last_error' => 'Shopify rejected the access token (401). Check the token, and that it belongs to this shop.',
            'last_connected_at' => Carbon::now()->subDays(6),
            'price_field' => 'wholesale_price',
            'create_missing_products' => 0,
            'create_missing_customers' => 1,
            'auto_sync' => 0,
            'instant_stock_push' => 0,
            'sync_interval_minutes' => 120,
            'auto_sync_plan' => null,   // shows the "Default plan" tag
            'sync_products' => 1,
            'sync_inventory' => 1,
            'sync_customers' => 0,
            'sync_orders' => 1,
            'sync_collections' => 0,
            'sync_fulfillments' => 0,
            'last_sync_at' => Carbon::now()->subDays(6),
        ]);
    }

    // ------------------------------------------------------------- mappings --

    /** Link local records to plausible Shopify ids. */
    private function mapRecords(ShopifyStore $store, $products, $clients, string $salt = ''): int
    {
        $n = 0;
        $seed = $salt === '' ? 80000000000 : 90000000000;

        foreach ($products as $i => $product) {
            $remote = $seed + ($product->id * 1000) + $i;

            ShopifyLink::create([
                'store_id' => $store->id,
                'entity_type' => 'product',
                'local_id' => $product->id,
                'shopify_id' => (string) $remote,
                'shopify_handle' => $this->handle($product->name),
                'push_hash' => substr(md5($product->name.$store->id), 0, 32),
                'last_synced_at' => Carbon::now()->subHours(random_int(1, 40)),
            ]);
            $n++;

            // The inventory item id is what stock is actually written against.
            ShopifyLink::create([
                'store_id' => $store->id,
                'entity_type' => 'inventory_item',
                'local_id' => $product->id,
                'shopify_id' => (string) ($remote + 1),
                'secondary_id' => (string) ($remote + 2),
                'last_synced_at' => Carbon::now()->subHours(random_int(1, 12)),
            ]);
            $n++;

            foreach (ProductVariant::where('product_id', $product->id)->limit(3)->get() as $v => $variant) {
                ShopifyLink::create([
                    'store_id' => $store->id,
                    'entity_type' => 'variant',
                    'local_id' => $variant->id,
                    'shopify_id' => (string) ($remote + 100 + $v),
                    'secondary_id' => (string) ($remote + 200 + $v),
                    'last_synced_at' => Carbon::now()->subHours(random_int(1, 12)),
                ]);
                $n++;
            }
        }

        foreach ($clients as $i => $client) {
            ShopifyLink::create([
                'store_id' => $store->id,
                'entity_type' => 'customer',
                'local_id' => $client->id,
                'shopify_id' => (string) ($seed + 500000 + ($client->id * 10) + $i),
                'last_synced_at' => Carbon::now()->subDays(random_int(1, 9)),
            ]);
            $n++;
        }

        foreach (Sale::whereNull('deleted_at')->orderByDesc('id')->limit(5)->get() as $i => $sale) {
            ShopifyLink::create([
                'store_id' => $store->id,
                'entity_type' => 'order',
                'local_id' => $sale->id,
                'shopify_id' => (string) ($seed + 700000 + ($sale->id * 10) + $i),
                'shopify_handle' => '#'.(1000 + $i),
                'last_synced_at' => Carbon::now()->subHours(random_int(1, 70)),
            ]);
            $n++;
        }

        foreach (Category::orderBy('id')->limit(4)->get() as $i => $category) {
            ShopifyLink::create([
                'store_id' => $store->id,
                'entity_type' => 'collection',
                'local_id' => $category->id,
                'shopify_id' => (string) ($seed + 900000 + ($category->id * 10) + $i),
                'shopify_handle' => $this->handle($category->name),
                'last_synced_at' => Carbon::now()->subDays(random_int(1, 4)),
            ]);
            $n++;
        }

        return $n;
    }

    private function handle(string $name): string
    {
        return trim(preg_replace('/-+/', '-', preg_replace('/[^a-z0-9]+/', '-', strtolower($name))), '-');
    }

    // -------------------------------------------------------------- history --

    /**
     * A fortnight of runs: mostly quiet successes, one failure worth reading,
     * one cancelled halfway, one dry run.
     *
     * @return \Illuminate\Support\Collection<int, ShopifySyncRun>
     */
    private function history(ShopifyStore $store)
    {
        $rows = [
            // [entity, direction, status, minutes ago, total, created, updated, skipped, failed, seconds, error]
            ['orders', 'pull', 'completed', 24, 6, 4, 2, 0, 0, 11, null],
            ['inventory', 'push', 'completed', 39, 96, 0, 96, 0, 0, 52, null],
            ['orders', 'pull', 'completed', 84, 3, 3, 0, 0, 0, 7, null],
            ['inventory', 'push', 'completed', 99, 96, 0, 94, 2, 0, 50, null],
            ['products', 'push', 'completed', 420, 96, 12, 80, 4, 0, 214, null],
            ['customers', 'pull', 'completed', 900, 41, 6, 35, 0, 0, 33, null],
            ['collections', 'push', 'completed', 1500, 4, 1, 3, 0, 0, 6, null],
            ['fulfillments', 'push', 'completed', 2600, 9, 0, 9, 0, 0, 19, null],
            ['products', 'push', 'failed', 4300, 96, 31, 12, 0, 3, 88,
                'Shopify rate limit reached (429) and did not clear within the retry window.'],
            ['inventory', 'push', 'cancelled', 5900, 96, 0, 40, 0, 0, 27, null],
            ['products', 'pull', 'completed', 8600, 96, 0, 0, 96, 0, 61, null],   // dry run
        ];

        $runs = collect();

        // Oldest first, so ids ascend with time the way real history does.
        $rows = array_reverse($rows, true);

        foreach ($rows as $i => [$entity, $direction, $status, $ago, $total, $created, $updated, $skipped, $failed, $seconds, $error]) {
            $startedAt = Carbon::now()->subMinutes($ago);
            $processed = $created + $updated + $skipped + $failed;

            $runs->push(ShopifySyncRun::create([
                'store_id' => $store->id,
                'user_id' => null,
                'entity' => $entity,
                'direction' => $direction,
                'status' => $status,
                'dry_run' => $i === 10,
                'total_items' => $total,
                'processed_items' => $processed,
                'created_items' => $created,
                'updated_items' => $updated,
                'skipped_items' => $skipped,
                'failed_items' => $failed,
                'percentage' => $status === 'completed' ? 100 : (int) round($processed / max(1, $total) * 100),
                'stage' => $status === 'completed' ? 'done' : $entity.' '.$direction,
                'last_error' => $error,
                'started_at' => $startedAt,
                'finished_at' => (clone $startedAt)->addSeconds($seconds),
                'heartbeat_at' => (clone $startedAt)->addSeconds($seconds),
                'cancel_requested' => $status === 'cancelled',
                'created_at' => $startedAt,
                'updated_at' => (clone $startedAt)->addSeconds($seconds),
            ]));
        }

        return $runs;
    }

    /** The unreachable shop: one failure, so its Errors tab is not empty. */
    private function outletHistory(ShopifyStore $store): void
    {
        $startedAt = Carbon::now()->subDays(6);

        $run = ShopifySyncRun::create([
            'store_id' => $store->id,
            'entity' => 'products',
            'direction' => 'push',
            'status' => 'failed',
            'total_items' => 40,
            'processed_items' => 0,
            'percentage' => 0,
            'stage' => 'starting',
            'last_error' => 'Shopify rejected the access token (401). Check the token, and that it belongs to this shop.',
            'started_at' => $startedAt,
            'finished_at' => (clone $startedAt)->addSeconds(3),
            'heartbeat_at' => (clone $startedAt)->addSeconds(3),
            'created_at' => $startedAt,
            'updated_at' => $startedAt,
        ]);

        ShopifyLog::create([
            'store_id' => $store->id,
            'run_id' => $run->id,
            'entity' => 'products',
            'action' => 'products.push',
            'level' => 'error',
            'message' => 'Sync failed: Shopify rejected the access token (401). Check the token, and that it belongs to this shop.',
            'context' => ['status' => 401],
            'created_at' => $startedAt,
            'updated_at' => $startedAt,
        ]);
    }

    // ----------------------------------------------------------------- logs --

    private function logs(ShopifyStore $store, $runs): void
    {
        ShopifyLog::create([
            'store_id' => $store->id,
            'action' => 'store.create',
            'level' => 'info',
            'message' => 'Store connected: '.$store->shop_domain,
            'created_at' => Carbon::now()->subDays(21),
            'updated_at' => Carbon::now()->subDays(21),
        ]);

        ShopifyLog::create([
            'store_id' => $store->id,
            'entity' => 'webhooks',
            'action' => 'webhook.register',
            'level' => 'info',
            'message' => 'Webhooks registered: 9 created, 0 already present.',
            'context' => ['address' => url('/api/shopify/webhook')],
            'created_at' => Carbon::now()->subDays(21),
            'updated_at' => Carbon::now()->subDays(21),
        ]);

        foreach ($runs as $run) {
            ShopifyLog::create([
                'store_id' => $store->id,
                'run_id' => $run->id,
                'entity' => $run->entity,
                'action' => $run->entity.'.'.$run->direction,
                'level' => $run->status === 'failed' ? 'error' : 'info',
                'message' => $run->status === 'failed'
                    ? 'Sync failed: '.$run->last_error
                    : ucfirst($run->entity).' '.$run->direction.' '.$run->status.' — '
                        .$run->processed_items.' processed, '.$run->created_items.' created, '.$run->updated_items.' updated'
                        .($run->dry_run ? ' (dry run — nothing written)' : ''),
                'context' => ['dry_run' => (bool) $run->dry_run],
                'created_at' => $run->finished_at,
                'updated_at' => $run->finished_at,
            ]);
        }

        // A few things that are worth seeing in a log: a skipped record with a
        // reason, a rate-limit warning, and a rejected webhook.
        $extras = [
            ['warning', 'products', 'product.push', 'Skipped "Wool Beanie" — no SKU, and Shopify cannot match a product without one.', 300],
            ['warning', 'customers', 'customer.pull', 'Skipped a customer with no email address — matching on name would merge different people.', 910],
            ['warning', 'inventory', 'inventory.push', 'Rate limit is at 38 of 40 — slowing down.', 100],
            ['error', 'webhooks', 'webhook.reject', 'Rejected orders/create — signature did not verify.', 1600],
            ['info', 'inventory', 'stock.instant', 'Stock pushed on change: 3 item(s).', 26],
        ];

        foreach ($extras as [$level, $entity, $action, $message, $ago]) {
            ShopifyLog::create([
                'store_id' => $store->id,
                'entity' => $entity,
                'action' => $action,
                'level' => $level,
                'message' => $message,
                'created_at' => Carbon::now()->subMinutes($ago),
                'updated_at' => Carbon::now()->subMinutes($ago),
            ]);
        }
    }

    // ------------------------------------------------------------- webhooks --

    private function webhooks(ShopifyStore $store): void
    {
        $events = [
            ['orders/create', 'processed', 8, null],
            ['orders/updated', 'processed', 22, null],
            ['orders/fulfilled', 'processed', 46, null],
            ['products/update', 'processed', 74, null],
            ['inventory_levels/update', 'processed', 110, null],
            ['customers/create', 'processed', 260, null],
            ['orders/create', 'failed', 400, 'No ERP warehouse is set — an order cannot be filed without one.'],
            ['app/uninstalled', 'ignored', 900, null],
        ];

        foreach ($events as $i => [$topic, $status, $ago, $error]) {
            $at = Carbon::now()->subMinutes($ago);

            ShopifyWebhookEvent::create([
                'store_id' => $store->id,
                'topic' => $topic,
                'event_id' => 'demo-'.str_pad((string) ($i + 1), 4, '0', STR_PAD_LEFT).'-'.substr(md5($topic.$i), 0, 12),
                'payload' => $this->payloadFor($topic, $i),
                'status' => $status,
                'error' => $error,
                'processed_at' => $status === 'pending' ? null : $at,
                'created_at' => $at,
                'updated_at' => $at,
            ]);
        }
    }

    /** Enough of a payload to be recognisable, not a full Shopify fixture. */
    private function payloadFor(string $topic, int $i): array
    {
        $id = 5500000000000 + $i;

        if (str_starts_with($topic, 'orders/')) {
            return [
                'id' => $id,
                'name' => '#'.(1200 + $i),
                'financial_status' => 'paid',
                'fulfillment_status' => $topic === 'orders/fulfilled' ? 'fulfilled' : null,
                'total_price' => number_format(24.5 + $i * 7, 2, '.', ''),
                'currency' => 'USD',
                'customer' => ['id' => 6600000000000 + $i, 'email' => 'buyer'.$i.'@example.com'],
                'line_items' => [['id' => 7700000000000 + $i, 'sku' => 'SH-000'.(($i % 8) + 1), 'quantity' => 1 + ($i % 3)]],
            ];
        }

        if (str_starts_with($topic, 'products/')) {
            return ['id' => $id, 'title' => 'Classic Cotton T-Shirt', 'handle' => 'classic-cotton-t-shirt', 'status' => 'active'];
        }

        if (str_starts_with($topic, 'customers/')) {
            return ['id' => $id, 'email' => 'buyer'.$i.'@example.com', 'first_name' => 'Demo', 'last_name' => 'Buyer'];
        }

        if ($topic === 'inventory_levels/update') {
            return ['inventory_item_id' => $id, 'location_id' => 76543210987, 'available' => 12 + $i];
        }

        return ['id' => $id];
    }
}

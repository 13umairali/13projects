<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Demo Tenant Generator.
 *
 * Populates a freshly provisioned tenant with realistic sample data so the
 * dashboard, catalog, storefront and reports look alive out of the box: six
 * categories, six brands, 28 products with photos and stock, customers,
 * suppliers, plus example purchases and sales (each with payments).
 *
 * Product photos are Unsplash LINKS, not files on disk — the same approach the
 * themed storefront demos (Electronics/Grocery/Toys/RealEstate) use, and
 * product_image_url() passes any absolute URL straight through. Nothing to
 * copy into uploads, and a brand-new tenant's storefront has real imagery
 * instead of a wall of no-image.png placeholders.
 *
 * Run only when the super admin enables the "Demo Tenant Generator" toggle in
 * Super → General Settings. It uses raw inserts (no Eloquent events) and is
 * idempotent — it bails out if demo products already exist.
 *
 * Inventory stays consistent: each product's on-hand stock equals the total
 * purchased minus the total sold across the generated transactions.
 */
class DemoDataSeeder extends Seeder
{
    /** Default warehouse + admin user are created by DatabaseSeeder. */
    private const WAREHOUSE_ID = 1;
    private const USER_ID = 1;

    /**
     * Unsplash photo ids, keyed by what the photo actually shows.
     *
     * Heads-up if you go shopping for more in the themed seeders: several of
     * their IMG keys do not match their photo (GroceryDemoSeeder's 'cola_ice'
     * is broccoli, 'spray' is a bathroom, 'lemonade' is a cappuccino). Every id
     * below was checked by rendering it, not by trusting a key name.
     */
    private const PHOTO = [
        'orange_juice'      => '1600271886742-f049cd451bba',
        'coffee_cup'        => '1541167760496-1628856ab772',
        'berry_smoothie'    => '1553530666-ba11a7da3888',
        'milk'              => '1563636619-e9143da7973b',
        'crisp_packets'     => '1566478989037-eec170784d0b',
        'burger'            => '1571091718767-18b5b1457add',
        'layer_cake'        => '1582716401301-b2407dc7563d',
        'chocolate_dessert' => '1598511726623-d2e9996892f0',
        'croissants'        => '1555507036-ab1f4038808a',
        'rice_cakes'        => '1599490659213-e2b9527bd087',
        'bananas'           => '1571771894821-ce9b6c11b08e',
        'apples'            => '1567306226416-28f0efdc88ce',
        'strawberries'      => '1464965911861-746a04b4bca6',
        'peppers'           => '1601648764658-cf37e8c89b70',
        'broccoli'          => '1615485290382-441e4d049cb5',
        'tomatoes'          => '1592924357228-91a4daadcfea',
        'eggs'              => '1582722872445-44dc5f7e3c8f',
        'spaghetti'         => '1551462147-ff29053bfc14',
        'spray_bottle'      => '1563453392212-326f5e854473',
        'mouse'             => '1527864550417-7fd91fc51a46',
        'keyboard'          => '1618384887929-16ec33fab9ef',
        'headphones'        => '1546435770-a3e426bf472b',
        'speaker'           => '1608043152269-423dbba4e7e1',
        'power_bank'        => '1573739022854-abceaeb585dc',
        'usb_charger'       => '1583863788434-e58a36330cf0',
        'notebook'          => '1544947950-fa07a98d237f',
        'pencils'           => '1596464716127-f2a82984de30',
        'book_stack'        => '1512820790803-83ca734da794',
    ];

    /** Full-size product photo URL for a PHOTO key. */
    private static function img(string $key, int $w = 900): string
    {
        return 'https://images.unsplash.com/photo-'.self::PHOTO[$key].'?w='.$w.'&q=80&auto=format&fit=crop';
    }

    public function run(): void
    {
        // Idempotency guard — never duplicate demo data on re-provision.
        if (DB::table('products')->where('code', 'PR-DEMO-001')->exists()) {
            return;
        }

        DB::transaction(function () {
            $now = Carbon::now();

            $units      = $this->seedUnits();
            $categories = $this->seedCategories();
            $brands     = $this->seedBrands();
            $products   = $this->seedProducts($units, $categories, $brands, $now);
            $providers  = $this->seedProviders($now);
            $clients    = $this->seedClients($now);

            // Track on-hand stock per product as transactions are recorded.
            $onHand = array_fill_keys(array_keys($products), 0.0);

            $this->seedPurchases($products, $providers, $onHand, $now);
            $this->seedSales($products, $clients, $onHand, $now);

            $this->seedStock($products, $onHand, $now);
        });
    }

    /** @return array<string,int> short-name => id */
    private function seedUnits(): array
    {
        $now = Carbon::now();
        $rows = [
            'pc'  => ['name' => 'Piece',     'ShortName' => 'pc'],
            'pk'  => ['name' => 'Pack',      'ShortName' => 'pk'],
            'box' => ['name' => 'Box',       'ShortName' => 'box'],
            'kg'  => ['name' => 'Kilogram',  'ShortName' => 'kg'],
        ];

        $ids = [];
        foreach ($rows as $key => $row) {
            $ids[$key] = DB::table('units')->insertGetId($row + [
                'operator'       => '*',
                'operator_value' => 1,
                'created_at'     => $now,
                'updated_at'     => $now,
            ]);
        }

        return $ids;
    }

    /** @return array<string,int> key => id */
    private function seedCategories(): array
    {
        $now = Carbon::now();
        $rows = [
            'beverages' => ['code' => 'CAT-DEMO-BEV', 'name' => 'Beverages', 'icon' => 'cup'],
            'bakery' => ['code' => 'CAT-DEMO-BAK', 'name' => 'Bakery & Snacks', 'icon' => 'cookie'],
            'produce' => ['code' => 'CAT-DEMO-FRS', 'name' => 'Fresh Produce', 'icon' => 'apple-icon'],
            'pantry' => ['code' => 'CAT-DEMO-PAN', 'name' => 'Pantry & Household', 'icon' => 'basket'],
            'electronics' => ['code' => 'CAT-DEMO-ELC', 'name' => 'Electronics', 'icon' => 'cpu'],
            'stationery' => ['code' => 'CAT-DEMO-STN', 'name' => 'Stationery', 'icon' => 'book-open'],
        ];

        // Storefront-only columns; absent on older tenant schemas.
        $hasStoreFlag = Schema::hasColumn('categories', 'show_in_store');
        $hasIcon      = Schema::hasColumn('categories', 'icon');

        $ids = [];
        foreach ($rows as $key => $row) {
            $insert = [
                'code'       => $row['code'],
                'name'       => $row['name'],
                'created_at' => $now,
                'updated_at' => $now,
            ];
            if ($hasIcon) {
                $insert['icon'] = $row['icon'];
            }
            if ($hasStoreFlag) {
                $insert['show_in_store'] = 1;
            }

            $ids[$key] = DB::table('categories')->insertGetId($insert);
        }

        return $ids;
    }

    /** @return array<string,int> key => id */
    private function seedBrands(): array
    {
        $now = Carbon::now();
        $names = [
            'aquapure' => 'AquaPure',
            'crunchco' => 'CrunchCo',
            'freshfield' => 'FreshField',
            'pantrypro' => 'PantryPro',
            'technova' => 'TechNova',
            'writewell' => 'WriteWell',
        ];

        $ids = [];
        foreach ($names as $key => $name) {
            $ids[$key] = DB::table('brands')->insertGetId([
                'name'       => $name,
                'image'      => 'no-image.png',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        return $ids;
    }

    /**
     * @return array<string,array{id:int,cost:float,price:float,qty:int}> sku => meta
     */
    private function seedProducts(array $units, array $categories, array $brands, Carbon $now): array
    {
        // sku, name, category, brand, unit, cost, price, photo, purchase qty, featured
        $defs = [
            ['PR-DEMO-001', 'Orange Juice 1L',               'beverages',   'aquapure',   'pc',  1.10,  2.60, 'orange_juice',       120, 1],
            ['PR-DEMO-002', 'Ground Coffee 250g',            'beverages',   'aquapure',   'pc',  3.20,  6.90, 'coffee_cup',          60, 0],
            ['PR-DEMO-003', 'Berry Smoothie 330ml',          'beverages',   'aquapure',   'pc',  1.40,  3.20, 'berry_smoothie',      80, 0],
            ['PR-DEMO-004', 'Whole Milk 1L',                 'beverages',   'aquapure',   'pc',  0.85,  1.70, 'milk',               100, 0],

            ['PR-DEMO-005', 'Potato Crisps Multipack',       'bakery',      'crunchco',   'pk',  1.90,  4.20, 'crisp_packets',      150, 1],
            ['PR-DEMO-006', 'Gourmet Beef Burger',           'bakery',      'crunchco',   'pc',  2.40,  5.50, 'burger',              40, 0],
            ['PR-DEMO-007', 'Chocolate Layer Cake Slice',    'bakery',      'crunchco',   'pc',  1.60,  3.80, 'layer_cake',          30, 0],
            ['PR-DEMO-008', 'Chocolate Fondant Dessert',     'bakery',      'crunchco',   'pc',  1.75,  4.10, 'chocolate_dessert',   30, 0],
            ['PR-DEMO-009', 'Butter Croissants 4-pack',      'bakery',      'crunchco',   'pk',  1.30,  3.00, 'croissants',          90, 1],
            ['PR-DEMO-010', 'Rice Cakes 150g',               'bakery',      'crunchco',   'pc',  0.95,  2.20, 'rice_cakes',          70, 0],

            ['PR-DEMO-011', 'Bananas',                       'produce',     'freshfield', 'kg',  0.80,  1.60, 'bananas',            200, 0],
            ['PR-DEMO-012', 'Red Apples',                    'produce',     'freshfield', 'kg',  1.20,  2.40, 'apples',             180, 1],
            ['PR-DEMO-013', 'Strawberries 250g',             'produce',     'freshfield', 'pc',  1.60,  3.40, 'strawberries',        90, 0],
            ['PR-DEMO-014', 'Bell Peppers 500g',             'produce',     'freshfield', 'pc',  1.10,  2.30, 'peppers',            120, 0],
            ['PR-DEMO-015', 'Broccoli',                      'produce',     'freshfield', 'kg',  1.00,  2.10, 'broccoli',           100, 0],
            ['PR-DEMO-016', 'Vine Tomatoes 500g',            'produce',     'freshfield', 'pc',  1.05,  2.25, 'tomatoes',           140, 0],

            ['PR-DEMO-017', 'Free-Range Eggs 12-pack',       'pantry',      'pantrypro',  'pk',  1.80,  3.60, 'eggs',               120, 0],
            ['PR-DEMO-018', 'Spaghetti 500g',                'pantry',      'pantrypro',  'pc',  0.70,  1.60, 'spaghetti',          160, 0],
            ['PR-DEMO-019', 'Multi-Surface Cleaning Spray',  'pantry',      'pantrypro',  'pc',  1.80,  4.20, 'spray_bottle',        80, 0],

            ['PR-DEMO-020', 'Wireless Mouse',                'electronics', 'technova',   'pc',  6.00, 14.00, 'mouse',               25, 0],
            ['PR-DEMO-021', 'Mechanical Keyboard',           'electronics', 'technova',   'pc', 28.00, 59.00, 'keyboard',            20, 1],
            ['PR-DEMO-022', 'Noise-Cancelling Headphones',   'electronics', 'technova',   'pc', 42.00, 89.00, 'headphones',          18, 1],
            ['PR-DEMO-023', 'Bluetooth Speaker',             'electronics', 'technova',   'pc', 18.00, 39.00, 'speaker',             22, 0],
            ['PR-DEMO-024', 'Power Bank 20000mAh',           'electronics', 'technova',   'pc', 15.00, 34.00, 'power_bank',          30, 0],
            ['PR-DEMO-025', 'USB-C Fast Charger 20W',        'electronics', 'technova',   'pc',  5.00, 12.00, 'usb_charger',         60, 0],

            ['PR-DEMO-026', 'Hardcover Notebook A5',         'stationery',  'writewell',  'pc',  1.90,  4.50, 'notebook',           140, 0],
            ['PR-DEMO-027', 'Colouring Pencils 24-pack',     'stationery',  'writewell',  'pk',  2.40,  5.90, 'pencils',             90, 0],
            ['PR-DEMO-028', 'Business Book Set',             'stationery',  'writewell',  'pc',  6.50, 14.90, 'book_stack',          40, 0],
        ];

        $hasGallery  = Schema::hasTable('product_images');
        $hasFeatured = Schema::hasColumn('products', 'is_featured');

        $products = [];
        foreach ($defs as $i => [$sku, $name, $cat, $brand, $unit, $cost, $price, $photo, $qty, $featured]) {
            $image = self::img($photo);

            $row = [
                'type'             => 'is_single',
                'code'             => $sku,
                'Type_barcode'     => 'CODE128',
                'name'             => $name,
                'image'            => $image,
                'cost'             => $cost,
                'price'            => $price,
                'wholesale_price'  => round($price * 0.9, 2),
                'min_price'        => round($price * 0.8, 2),
                'points'           => 0,
                'category_id'      => $categories[$cat],
                'brand_id'         => $brands[$brand],
                'unit_id'          => $units[$unit],
                'unit_sale_id'     => $units[$unit],
                'unit_purchase_id' => $units[$unit],
                'TaxNet'           => 0,
                'tax_method'       => '1',
                'stock_alert'      => 10,
                'is_active'        => true,
                // Spread the creation dates so "New Arrivals" has an order.
                'created_at'       => $now->copy()->subDays(count($defs) - $i),
                'updated_at'       => $now,
            ];
            if ($hasFeatured) {
                $row['is_featured'] = $featured;
            }

            $id = DB::table('products')->insertGetId($row);

            if ($hasGallery) {
                DB::table('product_images')->insert([
                    'product_id' => $id,
                    'image_path' => $image,
                    'is_main'    => 1,
                    'sort_order' => 0,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            $products[$sku] = ['id' => $id, 'cost' => $cost, 'price' => $price, 'qty' => $qty];
        }

        return $products;
    }

    /** @return array<int,int> list of provider ids */
    private function seedProviders(Carbon $now): array
    {
        $defs = [
            ['Global Supplies Co',     'sales@globalsupplies.test',  '+1-202-555-0111'],
            ['FreshMart Distributors', 'orders@freshmart.test',      '+1-202-555-0122'],
            ['TechWorld Wholesale',    'hello@techworld.test',       '+1-202-555-0133'],
        ];

        $ids = [];
        foreach ($defs as $i => [$name, $email, $phone]) {
            $ids[] = DB::table('providers')->insertGetId([
                'name'       => $name,
                'code'       => $i + 1,
                'email'      => $email,
                'phone'      => $phone,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        return $ids;
    }

    /** @return array<int,int> list of client ids */
    private function seedClients(Carbon $now): array
    {
        $defs = [
            ['Walk-in Customer',  null,                       null],
            ['John Carter',       'john.carter@example.test',  '+1-202-555-0200'],
            ['Emily Stone',       'emily.stone@example.test',  '+1-202-555-0201'],
            ['Michael Reed',      'michael.reed@example.test', '+1-202-555-0202'],
            ['Sophia Bennett',    'sophia.bennett@example.test','+1-202-555-0203'],
        ];

        $ids = [];
        foreach ($defs as $i => [$name, $email, $phone]) {
            $ids[] = DB::table('clients')->insertGetId([
                'name'                => $name,
                'code'                => $i + 1,
                'email'               => $email,
                'phone'               => $phone,
                'is_royalty_eligible' => true,
                'points'              => 0,
                'opening_balance'     => 0,
                'credit_limit'        => 0,
                'created_at'          => $now,
                'updated_at'          => $now,
            ]);
        }

        return $ids;
    }

    /**
     * Record received purchases (which build up stock) with payments. One
     * purchase per supplier run, built from each product's own restock
     * quantity so the catalog and the stock stay in step automatically.
     *
     * @param array<string,array{id:int,cost:float,price:float,qty:int}> $products
     * @param array<int,int> $providers
     * @param array<string,float> $onHand  by ref — incremented per purchased unit
     */
    private function seedPurchases(array $products, array $providers, array &$onHand, Carbon $now): void
    {
        // Six deliveries of five products each, oldest first, so every product
        // is stocked before the sales below draw it back down.
        $batches      = array_chunk($products, 5, true);
        $paidFraction = [1.0, 1.0, 0.5, 1.0, 1.0, 0.5];

        foreach ($batches as $i => $items) {
            $date = $now->copy()->subDays(40 - ($i * 4));

            $grandTotal = 0.0;
            foreach ($items as $meta) {
                $grandTotal += $meta['qty'] * $meta['cost'];
            }
            $grandTotal = round($grandTotal, 2);
            $paid       = round($grandTotal * ($paidFraction[$i % count($paidFraction)]), 2);

            $ref = sprintf('PUR-DEMO-%04d', $i + 1);

            $purchaseId = DB::table('purchases')->insertGetId([
                'user_id'        => self::USER_ID,
                'Ref'            => $ref,
                'date'           => $date->toDateString(),
                'time'           => $date->toTimeString(),
                'provider_id'    => $providers[$i % count($providers)],
                'warehouse_id'   => self::WAREHOUSE_ID,
                'tax_rate'       => 0,
                'TaxNet'         => 0,
                'discount'       => 0,
                'shipping'       => 0,
                'GrandTotal'     => $grandTotal,
                'paid_amount'    => $paid,
                'statut'         => 'received',
                'payment_statut' => $this->paymentStatut($grandTotal, $paid),
                'created_at'     => $date,
                'updated_at'     => $date,
            ]);

            foreach ($items as $sku => $meta) {
                DB::table('purchase_details')->insert([
                    'cost'             => $meta['cost'],
                    'purchase_unit_id' => null,
                    'TaxNet'           => 0,
                    'tax_method'       => '1',
                    'discount'         => 0,
                    'discount_method'  => '1',
                    'purchase_id'      => $purchaseId,
                    'product_id'       => $meta['id'],
                    'total'            => round($meta['qty'] * $meta['cost'], 2),
                    'quantity'         => $meta['qty'],
                    'created_at'       => $date,
                    'updated_at'       => $date,
                ]);

                $onHand[$sku] += $meta['qty'];
            }

            if ($paid > 0) {
                DB::table('payment_purchases')->insert([
                    'user_id'     => self::USER_ID,
                    'date'        => $date->toDateString(),
                    'Ref'         => 'INV/'.$ref,
                    'purchase_id' => $purchaseId,
                    'montant'     => $paid,
                    'change'      => 0,
                    'created_at'  => $date,
                    'updated_at'  => $date,
                ]);
            }
        }
    }

    /**
     * Record completed sales (which draw down stock) with payments. Every
     * quantity here is smaller than what the purchases above brought in.
     *
     * @param array<string,array{id:int,cost:float,price:float,qty:int}> $products
     * @param array<int,int> $clients
     * @param array<string,float> $onHand  by ref — decremented per sold unit
     */
    private function seedSales(array $products, array $clients, array &$onHand, Carbon $now): void
    {
        // clientIndex, daysAgo, paidFraction, items => [sku => qty]
        $defs = [
            [1, 18, 1.0, ['PR-DEMO-001' => 12, 'PR-DEMO-005' => 8, 'PR-DEMO-011' => 10]],
            [2, 15, 1.0, ['PR-DEMO-020' => 2, 'PR-DEMO-025' => 3]],
            [3, 12, 0.5, ['PR-DEMO-002' => 6, 'PR-DEMO-009' => 10, 'PR-DEMO-017' => 12]],
            [4,  9, 1.0, ['PR-DEMO-012' => 15, 'PR-DEMO-013' => 6, 'PR-DEMO-016' => 10]],
            [1,  7, 1.0, ['PR-DEMO-021' => 1, 'PR-DEMO-022' => 1, 'PR-DEMO-023' => 2]],
            [2,  5, 1.0, ['PR-DEMO-026' => 20, 'PR-DEMO-027' => 8, 'PR-DEMO-018' => 14]],
            [0,  3, 0.0, ['PR-DEMO-006' => 4, 'PR-DEMO-007' => 3, 'PR-DEMO-008' => 2]],
            [3,  1, 1.0, ['PR-DEMO-003' => 9, 'PR-DEMO-004' => 12, 'PR-DEMO-019' => 5, 'PR-DEMO-024' => 2]],
        ];

        foreach ($defs as $i => [$clientIdx, $daysAgo, $paidFraction, $items]) {
            $date = $now->copy()->subDays($daysAgo);

            $grandTotal = 0.0;
            foreach ($items as $sku => $qty) {
                $grandTotal += $qty * $products[$sku]['price'];
            }
            $grandTotal = round($grandTotal, 2);
            $paid       = round($grandTotal * $paidFraction, 2);

            $ref = sprintf('SL-DEMO-%04d', $i + 1);

            $saleId = DB::table('sales')->insertGetId([
                'user_id'         => self::USER_ID,
                'date'            => $date->toDateString(),
                'time'            => $date->toTimeString(),
                'Ref'             => $ref,
                'is_pos'          => false,
                'client_id'       => $clients[$clientIdx],
                'warehouse_id'    => self::WAREHOUSE_ID,
                'tax_rate'        => 0,
                'TaxNet'          => 0,
                'discount'        => 0,
                'discount_Method' => '2',
                'shipping'        => 0,
                'GrandTotal'      => $grandTotal,
                'paid_amount'     => $paid,
                'payment_statut'  => $this->paymentStatut($grandTotal, $paid),
                'statut'          => 'completed',
                'created_at'      => $date,
                'updated_at'      => $date,
            ]);

            foreach ($items as $sku => $qty) {
                $price = $products[$sku]['price'];
                DB::table('sale_details')->insert([
                    'date'            => $date->toDateString(),
                    'sale_id'         => $saleId,
                    'product_id'      => $products[$sku]['id'],
                    'price'           => $price,
                    'sale_unit_id'    => null,
                    'TaxNet'          => 0,
                    'tax_method'      => '1',
                    'discount'        => 0,
                    'discount_method' => '1',
                    'price_type'      => 'retail',
                    'total'           => round($qty * $price, 2),
                    'quantity'        => $qty,
                    'created_at'      => $date,
                    'updated_at'      => $date,
                ]);

                $onHand[$sku] -= $qty;
            }

            if ($paid > 0) {
                DB::table('payment_sales')->insert([
                    'user_id'    => self::USER_ID,
                    'date'       => $date->toDateString(),
                    'Ref'        => 'INV/'.$ref,
                    'sale_id'    => $saleId,
                    'montant'    => $paid,
                    'change'     => 0,
                    'created_at' => $date,
                    'updated_at' => $date,
                ]);
            }
        }
    }

    /**
     * Write the resulting on-hand stock for each product into the default
     * warehouse.
     *
     * @param array<string,array{id:int,cost:float,price:float,qty:int}> $products
     * @param array<string,float> $onHand
     */
    private function seedStock(array $products, array $onHand, Carbon $now): void
    {
        foreach ($products as $sku => $meta) {
            DB::table('product_warehouse')->insert([
                'product_id'   => $meta['id'],
                'warehouse_id' => self::WAREHOUSE_ID,
                'qte'          => max(0, $onHand[$sku]),
                'manage_stock' => true,
                'created_at'   => $now,
                'updated_at'   => $now,
            ]);
        }
    }

    private function paymentStatut(float $total, float $paid): string
    {
        if ($paid <= 0) {
            return 'unpaid';
        }

        return $paid >= $total ? 'paid' : 'partial';
    }
}

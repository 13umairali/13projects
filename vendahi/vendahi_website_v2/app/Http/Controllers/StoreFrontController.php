<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\FlashSale;
use App\Models\ProductReview;
use App\Models\StorePage;
use App\Services\FlashSaleService;
use App\Models\Brand;
use App\Models\Collection;
use App\Models\Product;
use App\Models\StoreBanner;
use App\Models\StoreSetting;
use Illuminate\Support\Facades\Schema;
use DB;
use Illuminate\Http\Request;

class StoreFrontController extends Controller
{
    /**
     * Homepage — blocks driven by StoreSetting->homepage_lineup.
     */
    public function index(Request $request)
    {
        $s = StoreSetting::firstOrFail();

        // Theme switch: when the Real Estate theme is active, the storefront
        // homepage is served by the dedicated real estate controller. This keeps
        // the default eCommerce storefront untouched.
        if (($s->theme ?? 'default') === 'real_estate') {
            return app(RealEstateStoreController::class)->home($request);
        }
        if (($s->theme ?? 'default') === 'electronics') {
            return app(ElectronicsStoreController::class)->home($request);
        }
        if (($s->theme ?? 'default') === 'toys') {
            return app(ToysStoreController::class)->home($request);
        }
        if (($s->theme ?? 'default') === 'grocery') {
            return app(GroceryStoreController::class)->home($request);
        }

        // 1) Load lineup (already cast to array by StoreSetting::$casts)
        $lineup = is_array($s->homepage_lineup) ? $s->homepage_lineup : [];

        // 2) Legacy fallback (home_collections -> lineup)
        if (empty($lineup)) {
            $legacy = $s->home_collections ?? [];
            if (is_string($legacy)) {
                $legacy = json_decode($legacy, true) ?: [];
            }
            if ($legacy) {
                $rows = collect($legacy)
                    ->filter(fn ($r) => is_array($r) && ! empty($r['collection_id']) && (
                        ! array_key_exists('visible', $r)
                        || filter_var($r['visible'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) !== false
                    ))
                    ->sortBy(fn ($r) => (int) ($r['sort_order'] ?? 9999))
                    ->values();

                $ids = $rows->pluck('collection_id')->map(fn ($v) => (int) $v)->unique()->all();
                $idToSlug = $ids ? Collection::whereIn('id', $ids)->pluck('slug', 'id')->toArray() : [];

                $lineup = [];
                foreach ($rows as $r) {
                    $slug = (string) ($idToSlug[(int) $r['collection_id']] ?? '');
                    if ($slug === '') {
                        continue;
                    }
                    $limit = max(1, (int) ($r['limit'] ?? 8));
                    $lineup[] = [
                        'type' => 'collection',
                        'slug' => $slug,
                        'limit' => $limit,
                        'layout' => 'grid',
                        'title_override' => '',
                    ];
                }
            }
        }

        // ===== Shared price SQL (mirrors shop()) =====
        $minVariantSub = DB::table('product_variants')
            ->select('product_id', DB::raw('MIN(price) AS min_variant_price'))
            ->groupBy('product_id');

        // Base: if a product has variants, use MIN(variant.price); else use products.price
        $baseExpr = 'COALESCE(pvmin.min_variant_price, products.price)';

        // discount_method: '1' => percent, '2' => fixed
        $discValExpr = 'IFNULL(products.discount, 0)';
        $afterDiscountExpr = "GREATEST(0,
            CASE
                WHEN products.discount_method = '1' THEN $baseExpr - ($baseExpr * ($discValExpr/100))
                WHEN products.discount_method = '2' THEN $baseExpr - LEAST($discValExpr, $baseExpr)
                ELSE $baseExpr
            END
        )";

        // tax_method: '2' => Inclusive (leave as-is), otherwise treat as Exclusive and add tax
        $taxRateExpr = 'COALESCE(products.TaxNet, 0)';
        $finalExpr = "ROUND(
            CASE
                WHEN products.tax_method = '2' THEN $afterDiscountExpr
                ELSE $afterDiscountExpr * (1 + ($taxRateExpr/100))
            END, 2
        )";

        // 3) Build blocks
        $blocks = [];
        $defaultTaxRate = (float) ($s->default_tax_rate ?? 0);

        foreach ($lineup as $i => $item) {
            if (! is_array($item) || empty($item['type'])) {
                continue;
            }
            $type = strtolower((string) $item['type']);

            if ($type === 'hero') {
                $blocks[] = [
                    'type' => 'hero',
                    'title' => $s->hero_title ?? null,
                    'subtitle' => $s->hero_subtitle ?? null,
                    'image' => $s->hero_image_path ?? null,
                    'cfg' => ['index' => $i],
                ];

                continue;
            }

            if ($type === 'newsletter') {
                $blocks[] = [
                    'type' => 'newsletter',
                    'title' => __('Newsletter'),
                    'cfg' => ['index' => $i],
                ];

                continue;
            }

            // Curated product grids. Without these the block types saved by the
            // settings UI produced no block at all, and buildAutoSections()
            // rendered Best Sellers regardless of the merchant's toggle.
            if ($type === 'best_sellers' || $type === 'you_may_like') {
                $limit = (int) ($item['limit'] ?? 8);
                $limit = $limit >= 1 && $limit <= 24 ? $limit : 8;

                if ($type === 'best_sellers') {
                    $products = $this->buildBestSellerProducts(
                        $s, $baseExpr, $afterDiscountExpr, $finalExpr, $minVariantSub, $defaultTaxRate, $limit
                    );
                    // A store with few or no online orders has no sales history to
                    // rank by; top up with featured/priciest stock so an enabled
                    // block is never an empty grid (same fallback the automatic
                    // section has always used).
                    if ($products->count() < $limit) {
                        $have = $products->pluck('id')->all();
                        $products = $products->concat($this->hydrate(
                            $this->baseQuery()
                                ->when($have, fn ($q) => $q->whereNotIn('products.id', $have))
                                ->orderByDesc('products.is_featured')
                                ->orderByDesc('products.price'),
                            $s, $defaultTaxRate, $limit - $products->count()
                        ))->values();
                    }
                    $this->attachRatings($products);
                    $defaultTitle = __('messages.BestSellers');
                } else {
                    $products = $this->buildPickedProducts(
                        $s, $defaultTaxRate, $item['product_ids'] ?? [], $limit
                    );
                    $defaultTitle = __('messages.YouMayAlsoLike');
                }

                $this->attachStockToProducts($products, $s->default_warehouse_id);
                if ($s->hide_out_of_stock ?? false) {
                    $products = $products->filter(fn ($p) => $this->productHasStock($p))->values();
                }

                $blocks[] = [
                    'type' => $type,
                    'title' => trim((string) ($item['title'] ?? '')) ?: $defaultTitle,
                    'products' => $products,
                    'cfg' => ['limit' => $limit, 'index' => $i],
                ];

                continue;
            }

            if ($type === 'collection') {
                $slug = trim((string) ($item['slug'] ?? ($item['handle'] ?? '')));
                if ($slug === '') {
                    continue;
                }

                $limit = max(1, (int) ($item['limit'] ?? 8));
                $layout = in_array(($item['layout'] ?? 'grid'), ['grid', 'carousel'], true) ? $item['layout'] : 'grid';
                $titleOverride = trim((string) ($item['title_override'] ?? ''));

                $collection = Collection::where('slug', $slug)->first()
                    ?: (is_numeric($slug) ? Collection::find((int) $slug) : null);
                if (! $collection) {
                    continue;
                }

                $colTitle = $titleOverride !== '' ? $titleOverride : ($collection->title ?? $collection->name ?? $slug);

                // === Use the same SQL pipeline as shop(), scoped to this collection ===
                $products = Product::query()
                    ->where('products.is_active', 1)
                    ->where('products.hide_from_online_store', 0)
                    ->with([
                        'variants:id,product_id,name,price,image',
                        'images:id,product_id,image_path,is_main,sort_order',
                    ]) // QuickView / gallery + variant picker
                    ->join('collection_product', 'collection_product.product_id', '=', 'products.id')
                    ->where('collection_product.collection_id', $collection->id)
                    ->leftJoinSub($minVariantSub, 'pvmin', function ($join) {
                        $join->on('pvmin.product_id', '=', 'products.id');
                    })
                    ->addSelect(
                        'products.*',
                        DB::raw("$baseExpr AS base_price"),
                        DB::raw("$afterDiscountExpr AS after_discount"),
                        DB::raw("$finalExpr AS final_display_price")
                    )
                    ->orderBy('collection_product.sort_order')
                    ->orderBy('products.created_at', 'desc')
                    ->take($limit)
                    ->get();

                // Attach display_price to product (from SQL) AND compute each variant's display price (PHP)
                foreach ($products as $p) {
                    // Product display price from SQL
                    $p->display_price = (float) ($p->final_display_price ?? 0);

                    // Variant display prices computed with same rules as SQL
                    $taxRate = is_numeric($p->TaxNet) ? (float) $p->TaxNet : $defaultTaxRate;
                    $discVal = is_numeric($p->discount) ? (float) $p->discount : 0.0;
                    $isPercent = (string) $p->discount_method === '1';
                    $isInclusive = (string) $p->tax_method === '2';

                    if ($p->relationLoaded('variants') && $p->variants) {
                        foreach ($p->variants as $v) {
                            $price = (float) ($v->price ?? 0);
                            // discount
                            if ($discVal > 0) {
                                $price = $isPercent ? ($price - ($price * $discVal / 100)) : ($price - min($discVal, $price));
                                if ($price < 0) {
                                    $price = 0;
                                }
                            }
                            // tax
                            if (! $isInclusive && $taxRate > 0) {
                                $price = $price * (1 + $taxRate / 100);
                            }
                            $v->display_price = round($price, 2);
                        }
                    }
                }

                $this->attachStockToProducts($products, $s->default_warehouse_id);

                if ($s->hide_out_of_stock ?? false) {
                    $products = $products->filter(fn ($p) => $this->productHasStock($p));
                }

                $blocks[] = [
                    'type' => 'collection',
                    'title' => $colTitle,
                    'collection' => $collection,
                    'products' => $products, // each $p has display_price, stock; each variant has display_price, stock
                    'cfg' => [
                        'limit' => $limit,
                        'layout' => $layout,
                        'index' => $i,
                    ],
                ];
            }
        }

        // 3b) Flash sale block (auto-shown near the top while a sale is
        // running — but the hero keeps the very first spot when the lineup
        // starts with one).
        $flashProducts = $this->buildFlashProducts($s, $baseExpr, $afterDiscountExpr, $finalExpr, $minVariantSub, $defaultTaxRate, 12);
        if ($flashProducts->isNotEmpty()) {
            $sale = FlashSale::running()->orderBy('sort_order')->orderBy('ends_at')->first();
            $flashBlock = [
                'type' => 'flash_sale',
                'title' => $sale?->localizedName() ?: __('messages.FlashSale'),
                'ends_at' => optional($sale?->ends_at)->toIso8601String(),
                'products' => $flashProducts,
                'cfg' => ['layout' => 'grid'],
            ];
            $insertAt = ($blocks[0]['type'] ?? '') === 'hero' ? 1 : 0;
            array_splice($blocks, $insertAt, 0, [$flashBlock]);
        }

        // 4) Active banners
        $banners = StoreBanner::query()
            ->where('active', 1)
            ->whereIn('position', ['top_left', 'top_right', 'center_left', 'center_right', 'footer_left', 'footer_right'])
            ->orderBy('position')
            ->orderBy('updated_at', 'desc')
            ->get();

        foreach ($banners as $b) {
            $b->image_url = global_asset($b->image ?: upload_path('banners').'/no-image.png');
        }

        $categories = Category::with('subcategories')->where('show_in_store', 1)->orderBy('name')->get();

        // Automatic homepage sections (default theme) from live data.
        $auto = $this->buildAutoSections($s, $blocks, $categories, $defaultTaxRate, empty($lineup));

        $viewData = [
            's' => $s,
            'blocks' => $blocks,
            'categories' => $categories,
            'banners' => $banners,
            'auto' => $auto,
            'showCategoryBar' => true,
        ];

        return view('store.index', $viewData);
    }

    /**
     * Shop — products with filters.
     * Sorting/filters use base "effective_price" (min variant or product price).
     * UI shows final display price (discount + tax) computed per item after fetch.
     */

     public function shop(Request $request)
    {
        $s = StoreSetting::firstOrFail();

        $q = trim((string) $request->get('q', ''));
        $cat = $request->get('category');
        $subCat = $request->get('sub_category');
        $minPrice = $request->get('min');
        $maxPrice = $request->get('max');
        $sort = $request->get('sort', 'latest');   // latest|price_asc|price_desc
        $coll = $request->get('collection');       // id or slug

        // 1) Subquery: MIN(variant.price) per product
        $minVariantSub = DB::table('product_variants')
            ->select('product_id', DB::raw('MIN(price) AS min_variant_price'))
            ->groupBy('product_id');

        // 2) SQL price pipeline (MySQL-compatible)
        $baseExpr = 'COALESCE(pvmin.min_variant_price, products.price)';

        // discount_method: '1'=percent, '2'=fixed (varchar)
        $discValExpr = 'IFNULL(products.discount, 0)';
        $afterDiscountExpr = "GREATEST(0,
            CASE
                WHEN products.discount_method = '1' THEN $baseExpr - ($baseExpr * ($discValExpr/100))
                WHEN products.discount_method = '2' THEN $baseExpr - LEAST($discValExpr, $baseExpr)
                ELSE $baseExpr
            END
        )";

        // tax_method: '1'=Exclusive, '2'=Inclusive (varchar);  TaxNet
        $taxRateExpr = 'COALESCE(products.TaxNet, 0)';
        $finalExpr = "ROUND(
            CASE
                WHEN products.tax_method = '2' THEN $afterDiscountExpr
                ELSE $afterDiscountExpr * (1 + ($taxRateExpr/100))
            END, 2
        )";

        $productsQuery = Product::query()
            ->where('deleted_at', '=', null)
            ->where('is_active', 1)
            ->where('hide_from_online_store', 0)
            // Note: product_variants table doesn't have a `qty` column; stock comes from product_warehouse.qte
            ->with([
                'variants:id,product_id,name,price,image',
                'images:id,product_id,image_path,is_main,sort_order',
            ]) // Quick View / gallery + picker
            ->leftJoinSub($minVariantSub, 'pvmin', function ($join) {
                $join->on('pvmin.product_id', '=', 'products.id');
            })
            ->addSelect(
                'products.*',
                DB::raw("$baseExpr AS base_price"),
                DB::raw("$afterDiscountExpr AS after_discount"),
                DB::raw("$finalExpr AS final_display_price")   // <= final price for filter/sort/UI
            );

        if ($s->hide_out_of_stock && $s->default_warehouse_id) {
            $inStockIds = $this->getInStockProductIds((int) $s->default_warehouse_id);
            $productsQuery->whereIn('products.id', $inStockIds);
        }

        $products = $productsQuery
            // Search
            ->when($q !== '', function ($qb) use ($q) {
                $qb->where('products.name', 'like', "%{$q}%");
            })
            // Category (legacy column OR category_product pivot)
            ->when($cat, function ($qb) use ($cat) {
                $cid = (int) $cat;
                $qb->where(function ($q) use ($cid) {
                    $q->where('products.category_id', $cid);
                    if (Schema::hasTable('category_product')) {
                        $q->orWhereExists(function ($sub) use ($cid) {
                            $sub->select(DB::raw(1))
                                ->from('category_product')
                                ->whereColumn('category_product.product_id', 'products.id')
                                ->where('category_product.category_id', $cid);
                        });
                    }
                });
            })
            // Sub Category (legacy column OR product_subcategory pivot)
            ->when($subCat, function ($qb) use ($subCat) {
                $sid = (int) $subCat;
                $qb->where(function ($q) use ($sid) {
                    $q->where('products.sub_category_id', $sid);
                    if (Schema::hasTable('product_subcategory')) {
                        $q->orWhereExists(function ($sub) use ($sid) {
                            $sub->select(DB::raw(1))
                                ->from('product_subcategory')
                                ->whereColumn('product_subcategory.product_id', 'products.id')
                                ->where('product_subcategory.sub_category_id', $sid);
                        });
                    }
                });
            })
            // Price range (by final price)
            ->when(is_numeric($minPrice), function ($qb) use ($finalExpr, $minPrice) {
                $qb->whereRaw("$finalExpr >= ?", [(float) $minPrice]);
            })
            ->when(is_numeric($maxPrice), function ($qb) use ($finalExpr, $maxPrice) {
                $qb->whereRaw("$finalExpr <= ?", [(float) $maxPrice]);
            })
            // Collection: id or slug
            ->when($coll, function ($qb) use ($coll) {
                $qb->whereHas('collections', function ($rel) use ($coll) {
                    if (is_numeric($coll)) {
                        $rel->where('collections.id', (int) $coll);
                    } else {
                        $rel->where('collections.slug', (string) $coll);
                    }
                });
            });

        // Sort
        if ($sort === 'price_asc') {
            $products->orderByRaw("$finalExpr ASC");
        } elseif ($sort === 'price_desc') {
            $products->orderByRaw("$finalExpr DESC");
        } else {
            $products->orderBy('products.created_at', 'desc');
        }

        $products = $products->paginate(12)->withQueryString();
        $categories = Category::with('subcategories')->where('show_in_store', 1)->orderBy('name')->get(['id', 'name']);
        $collections = Collection::orderBy('title')
            ->get(['id', 'title', 'slug'])
            ->map(function ($c) {
                $c->title = $c->title ?: ($c->name ?? '');

                return $c;
            });

        // Attach display_price for the Blade (use SQL-computed final_display_price)
        foreach ($products as $p) {
            $p->display_price = (float) ($p->final_display_price ?? 0);
        }
        $this->attachStockToProducts($products, $s->default_warehouse_id);
        $this->attachRatings($products->getCollection());

        return view('store.shop', [
            's' => $s,
            'products' => $products,
            'categories' => $categories,
            'collections' => $collections,
            'q' => $q,
            'cat' => $cat,
            'min' => $minPrice,
            'max' => $maxPrice,
            'sort' => $sort,
            'collection' => $coll,
            'showCategoryBar' => true,
        ]);
    }
 

    /**
     * Admin-authored CMS page (About, Terms, FAQ…) at /pages/{slug}.
     */
    public function page(Request $request, $slug)
    {
        $s = StoreSetting::firstOrFail();

        $page = StorePage::where('slug', $slug)->where('published', true)->first();
        if (! $page) {
            abort(404);
        }

        $categories = $this->storeCategories();

        return view('store.page', [
            's' => $s,
            'page' => $page,
            'categories' => $categories,
            'showCategoryBar' => true,
        ]);
    }

    /**
     * Product detail page (/product/{id}).
     *
     * Ported from the standalone Stocky storefront. The features that page
     * carries but this product does not have — vehicle fitment, wholesale
     * quantity tiers, size guides, wishlist and compare — are left out rather
     * than stubbed; everything else (gallery, variants, pricing, stock,
     * reviews, related products, SEO) uses this codebase's own helpers.
     */
    public function productShow(Request $request, $id)
    {
        $s = StoreSetting::firstOrFail();

        [$minVariantSub, $baseExpr, $afterDiscountExpr, $finalExpr] = $this->priceSqlParts();
        $defaultTaxRate = (float) ($s->default_tax_rate ?? 0);

        $product = Product::query()
            ->whereNull('products.deleted_at')
            ->where('products.is_active', 1)
            ->where('products.hide_from_online_store', 0)
            ->where('products.id', (int) $id)
            ->with([
                'variants:id,product_id,name,price,image,code,qty',
                'images:id,product_id,image_path,is_main,sort_order',
                'brand:id,name,description,image',
                'category:id,name',
                'subCategory:id,name',
            ])
            ->leftJoinSub($minVariantSub, 'pvmin', function ($join) {
                $join->on('pvmin.product_id', '=', 'products.id');
            })
            ->addSelect(
                'products.*',
                DB::raw("$baseExpr AS base_price"),
                DB::raw("$afterDiscountExpr AS after_discount"),
                DB::raw("$finalExpr AS final_display_price")
            )
            ->first();

        if (! $product) {
            abort(404);
        }

        $bundle = collect([$product]);
        $this->applyDisplayPrices($bundle, $defaultTaxRate);
        $this->attachStockToProducts($bundle, $s->default_warehouse_id);
        $this->attachRatings($bundle);

        $related = $this->buildRelatedProducts(
            $s, $product, $baseExpr, $afterDiscountExpr, $finalExpr, $minVariantSub, $defaultTaxRate, 8
        );

        // Approved-review aggregate. The list itself is lazy-loaded from
        // store.product.reviews once the Reviews tab is opened.
        $reviewStats = ['count' => 0, 'average' => 0.0];
        if (Schema::hasTable('product_reviews')) {
            $approved = DB::table('product_reviews')
                ->where('product_id', $product->id)
                ->where('status', 'approved');
            $reviewStats['count'] = (clone $approved)->count();
            $reviewStats['average'] = round((float) (clone $approved)->avg('rating'), 1);
        }

        // ===== SEO: canonical, Open Graph, Product JSON-LD =====
        $canonical = route('store.product.show', $product->id);
        $imageUrls = collect($product->productGalleryFilenames())
            ->map(fn ($f) => product_image_url_or_null($f))
            ->filter()->values()->all();
        $metaDesc = trim(\Illuminate\Support\Str::limit(strip_tags((string) $product->note), 160))
            ?: ($product->name.' — '.($s->store_name ?? ''));

        $offer = array_filter([
            '@type' => 'Offer',
            'price' => (string) round((float) ($product->display_price ?? 0), 2),
            'priceCurrency' => strtoupper((string) (store_currency()['code'] ?? '')) ?: null,
            'availability' => 'https://schema.org/'.($this->productHasStock($product) ? 'InStock' : 'OutOfStock'),
            'url' => $canonical,
        ], fn ($v) => $v !== null);

        $productLd = array_filter([
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            'name' => $product->name,
            'sku' => $product->code ?: null,
            'description' => $metaDesc,
            'image' => $imageUrls ?: null,
            'brand' => $product->brand ? ['@type' => 'Brand', 'name' => $product->brand->name] : null,
            'category' => $product->category->name ?? null,
            'offers' => $offer,
        ], fn ($v) => $v !== null);

        if ($reviewStats['count'] > 0) {
            $productLd['aggregateRating'] = [
                '@type' => 'AggregateRating',
                'ratingValue' => (string) $reviewStats['average'],
                'reviewCount' => (int) $reviewStats['count'],
            ];
        }

        return view('store.product', [
            's' => $s,
            'product' => $product,
            'related' => $related,
            'reviewStats' => $reviewStats,
            'categories' => $this->storeCategories(),
            'showCategoryBar' => true,
            'seo' => [
                'title' => $product->name,
                'description' => $metaDesc,
                'canonical' => $canonical,
                'type' => 'product',
                'image' => $imageUrls[0] ?? null,
                'jsonld' => $productLd,
            ],
        ]);
    }

    /**
     * "You may also like" for the product page: newest products in the same
     * category, topped up with the newest of anything else when that category
     * is thin. The standalone Stocky lets an admin curate this list per product
     * (product_related); that table does not exist here, so the automatic rule
     * is the only one.
     */
    protected function buildRelatedProducts($s, $product, string $baseExpr, string $afterDiscountExpr, string $finalExpr, $minVariantSub, float $defaultTaxRate, int $limit)
    {
        $base = fn () => Product::query()
            ->whereNull('products.deleted_at')
            ->where('products.is_active', 1)
            ->where('products.hide_from_online_store', 0)
            ->where('products.id', '!=', $product->id)
            ->with([
                'variants:id,product_id,name,price,image,qty',
                'images:id,product_id,image_path,is_main,sort_order',
            ])
            ->leftJoinSub($minVariantSub, 'pvmin', function ($join) {
                $join->on('pvmin.product_id', '=', 'products.id');
            })
            ->addSelect(
                'products.*',
                DB::raw("$baseExpr AS base_price"),
                DB::raw("$afterDiscountExpr AS after_discount"),
                DB::raw("$finalExpr AS final_display_price")
            );

        $sameCat = $product->category_id
            ? $base()->where('products.category_id', $product->category_id)
                ->orderByDesc('products.created_at')->take($limit)->get()
            : collect();

        if ($sameCat->count() < $limit) {
            $exclude = $sameCat->pluck('id')->push($product->id)->all();
            $products = $sameCat->concat(
                $base()->whereNotIn('products.id', $exclude)
                    ->orderByDesc('products.created_at')
                    ->take($limit - $sameCat->count())->get()
            );
        } else {
            $products = $sameCat;
        }

        $this->applyDisplayPrices($products, $defaultTaxRate);
        $this->attachStockToProducts($products, $s->default_warehouse_id);
        $this->attachRatings($products);

        if ($s->hide_out_of_stock ?? false) {
            $products = $products->filter(fn ($p) => $this->productHasStock($p));
        }

        return $products->values();
    }

    /**
     * Dedicated storefront page listing every product in a running flash sale.
     */
    public function flashSales(Request $request)
    {
        $s = StoreSetting::firstOrFail();

        [$minVariantSub, $baseExpr, $afterDiscountExpr, $finalExpr] = $this->priceSqlParts();

        $products = $this->buildFlashProducts($s, $baseExpr, $afterDiscountExpr, $finalExpr, $minVariantSub, (float) ($s->default_tax_rate ?? 0), null);
        $sale = FlashSale::running()->orderBy('sort_order')->orderBy('ends_at')->first();
        $categories = $this->storeCategories();

        return view('store.flash-sale', [
            's' => $s,
            'products' => $products,
            'sale' => $sale,
            'categories' => $categories,
            'showCategoryBar' => true,
        ]);
    }

    /** Categories for the nav / category bar (hidden ones excluded when the column exists). */
    protected function storeCategories()
    {
        return Category::with('subcategories')
            ->when(Schema::hasColumn('categories', 'show_in_store'), fn ($q) => $q->where('show_in_store', 1))
            ->orderBy('name')
            ->get();
    }

    /**
     * Build the product collection for running flash sales, with flash prices
     * applied to the headline and each variant, plus a compare-at (original)
     * price for the strikethrough. Mirrors the collection price pipeline.
     */
    protected function buildFlashProducts($s, string $baseExpr, string $afterDiscountExpr, string $finalExpr, $minVariantSub, float $defaultTaxRate, ?int $limit)
    {
        if (! Schema::hasTable('flash_sales')) {
            return collect();
        }

        $rules = app(FlashSaleService::class)->runningRules(); // product_id => ['type','value']
        if (empty($rules)) {
            return collect();
        }

        $query = Product::query()
            ->where('products.is_active', 1)
            ->where('products.hide_from_online_store', 0)
            ->whereIn('products.id', array_keys($rules))
            ->with([
                'variants:id,product_id,name,price,image',
                'images:id,product_id,image_path,is_main,sort_order',
            ])
            ->leftJoinSub($minVariantSub, 'pvmin', function ($join) {
                $join->on('pvmin.product_id', '=', 'products.id');
            })
            ->addSelect(
                'products.*',
                DB::raw("$baseExpr AS base_price"),
                DB::raw("$afterDiscountExpr AS after_discount"),
                DB::raw("$finalExpr AS final_display_price")
            )
            ->orderBy('products.created_at', 'desc');

        if ($limit) {
            $query->take($limit);
        }

        $products = $query->get();

        foreach ($products as $p) {
            $rule = $rules[$p->id] ?? null;
            if (! $rule) {
                continue;
            }

            $isInclusive = (string) $p->tax_method === '2';
            $taxRate = is_numeric($p->TaxNet) ? (float) $p->TaxNet : $defaultTaxRate;

            $withTax = function (float $base) use ($isInclusive, $taxRate) {
                if (! $isInclusive && $taxRate > 0) {
                    $base = $base * (1 + $taxRate / 100);
                }

                return round($base, 2);
            };

            // Original (pre-flash) final price for the strikethrough.
            $p->compare_at_price = (float) ($p->final_display_price ?? 0);

            // Flash price replaces the base + any product discount.
            $rawBase = (float) ($p->base_price ?? $p->price);
            $flashBase = FlashSale::applyDiscount($rawBase, $rule['type'], $rule['value']);
            $p->display_price = $withTax($flashBase);
            $p->flash_discount = ['type' => $rule['type'], 'value' => $rule['value']];

            if ($p->relationLoaded('variants') && $p->variants) {
                foreach ($p->variants as $v) {
                    $vBase = FlashSale::applyDiscount((float) ($v->price ?? 0), $rule['type'], $rule['value']);
                    $v->display_price = $withTax($vBase);
                }
            }
        }

        $this->attachStockToProducts($products, $s->default_warehouse_id ? (int) $s->default_warehouse_id : null);

        if ($s->hide_out_of_stock ?? false) {
            $products = $products->filter(fn ($p) => $this->productHasStock($p))->values();
        }

        return $products;
    }

    public function contact()
    {
        $s = StoreSetting::first();

        return view('store.contact', compact('s'));
    }

    /**
     * Attach stock (qty) to each product and its variants from product_warehouse for the given warehouse.
     * Product without variants: $p->stock. Variants: $v->stock (fallback to $v->qty if no warehouse row).
     */
    protected function attachStockToProducts($products, ?int $warehouseId): void
    {
        if (! $warehouseId || ! $products) {
            foreach ($products as $p) {
                $p->stock = 0;
                if ($p->relationLoaded('variants') && $p->variants) {
                    foreach ($p->variants as $v) {
                        $v->stock = (float) ($v->qty ?? 0);
                    }
                }
            }

            return;
        }

        $items = $products instanceof \Illuminate\Pagination\AbstractPaginator ? $products->items() : $products;
        $productIds = collect($items)->pluck('id')->unique()->filter()->values()->all();
        if (empty($productIds)) {
            return;
        }

        $variantIds = [];
        foreach ($items as $p) {
            if ($p->relationLoaded('variants') && $p->variants) {
                foreach ($p->variants as $v) {
                    $variantIds[] = $v->id;
                }
            }
        }
        $variantIds = array_values(array_unique(array_filter($variantIds)));

        $q = DB::table('product_warehouse')
            ->where('warehouse_id', $warehouseId)
            ->whereIn('product_id', $productIds);
        if (count($variantIds) > 0) {
            $q->where(function ($qb) use ($variantIds) {
                $qb->whereNull('product_variant_id')
                    ->orWhereIn('product_variant_id', $variantIds);
            });
        } else {
            $q->whereNull('product_variant_id');
        }
        $rows = $q->when(Schema::hasColumn('product_warehouse', 'deleted_at'), fn ($qb) => $qb->whereNull('deleted_at'))
            ->select('product_id', 'product_variant_id', 'qte')
            ->get();

        $stockMap = [];
        foreach ($rows as $r) {
            $pid = (int) $r->product_id;
            $vid = $r->product_variant_id !== null ? (int) $r->product_variant_id : null;
            $key = $vid !== null ? "{$pid}:{$vid}" : "{$pid}:p";
            $stockMap[$key] = (float) $r->qte;
        }

        foreach ($items as $p) {
            $pid = (int) $p->id;
            if ($p->relationLoaded('variants') && $p->variants && $p->variants->isNotEmpty()) {
                // For products with variants, prefer variant-level stock rows.
                // If your DB only tracks product-level stock (product_variant_id NULL), use that as a fallback for each variant.
                $p->stock = null;
                $productFallback = $stockMap["{$pid}:p"] ?? null;
                foreach ($p->variants as $v) {
                    $key = "{$pid}:" . (int) $v->id;
                    if (array_key_exists($key, $stockMap)) {
                        $v->stock = (float) $stockMap[$key];
                    } elseif ($productFallback !== null) {
                        $v->stock = (float) $productFallback;
                    } else {
                        // Legacy fallback if a `qty` column exists on variants
                        $v->stock = (float) ($v->qty ?? 0);
                    }
                }
            } else {
                $p->stock = $stockMap["{$pid}:p"] ?? 0;
            }
        }
    }

    /**
     * Whether the product has at least one unit in stock (after attachStockToProducts).
     */
    protected function productHasStock($p): bool
    {
        // Pre-order and "Always in stock" products are always available.
        if ($p->is_preorder || ($p->always_in_stock ?? false)) {
            return true;
        }

        if ($p->relationLoaded('variants') && $p->variants && $p->variants->isNotEmpty()) {
            return $p->variants->contains(fn ($v) => (float) ($v->stock ?? 0) > 0);
        }

        return (float) ($p->stock ?? 0) > 0;
    }

    /**
     * Product IDs that have at least one unit in stock in the given warehouse.
     * Used when hide_out_of_stock is enabled.
     */
    private function getInStockProductIds(int $warehouseId): array
    {
        $q = DB::table('product_warehouse')
            ->where('warehouse_id', $warehouseId)
            ->where('qte', '>', 0);
        if (Schema::hasColumn('product_warehouse', 'deleted_at')) {
            $q->whereNull('deleted_at');
        }

        $inStockIds = $q->distinct()->pluck('product_id')->all();

        // Include pre-order and "Always in stock" products even when out of stock
        $preorderIds = DB::table('products')
            ->where(function ($q) {
                $q->where('is_preorder', true);
                if (Schema::hasColumn('products', 'always_in_stock')) {
                    $q->orWhere('always_in_stock', 1);
                }
            })
            ->where('is_active', 1)
            ->whereNull('deleted_at')
            ->pluck('id')
            ->all();

        return array_values(array_unique(array_merge($inStockIds, $preorderIds)));
    }
    /**
     * Search suggestions for autocomplete.
     */
    public function searchSuggestions(Request $request)
    {
        $q = trim((string) $request->get('q', ''));
        if (strlen($q) < 2) {
            return response()->json([]);
        }

        $s = StoreSetting::first();
        $warehouseId = $s->default_warehouse_id ?? null;

        $products = Product::query()
            ->where('is_active', 1)
            ->where('hide_from_online_store', 0)
            ->where(function ($query) use ($q) {
                $query->where('name', 'like', "%{$q}%")
                    ->orWhere('code', 'like', "%{$q}%")
                    ->orWhere('note', 'like', "%{$q}%");
            })
            ->take(8)
            ->get(['id', 'name', 'code', 'image', 'price', 'tax_method', 'TaxNet', 'discount', 'discount_method']);

        foreach ($products as $p) {
            $p->loadMissing(['images' => fn ($q) => $q->orderBy('sort_order')->orderBy('id')]);
            $fn = $p->primaryProductImageFilename();
            $p->image_url = product_image_url($fn);
            $p->display_price = $p->computeFinalPrice()['final'];
            $p->url = route('store.shop', ['q' => $p->name]); 
        }

        return response()->json($products);
    }

    /**
     * The price SQL fragments the homepage/shop pipelines share, so theme
     * controllers can hydrate product lists with identical pricing rules.
     *
     * @return array{0: \Illuminate\Database\Query\Builder, 1: string, 2: string, 3: string}
     */
    protected function priceSqlParts(): array
    {
        $minVariantSub = DB::table('product_variants')
            ->select('product_id', DB::raw('MIN(price) AS min_variant_price'))
            ->groupBy('product_id');

        $baseExpr = 'COALESCE(pvmin.min_variant_price, products.price)';
        $discValExpr = 'IFNULL(products.discount, 0)';
        $afterDiscountExpr = "GREATEST(0,
            CASE
                WHEN products.discount_method = '1' THEN $baseExpr - ($baseExpr * ($discValExpr/100))
                WHEN products.discount_method = '2' THEN $baseExpr - LEAST($discValExpr, $baseExpr)
                ELSE $baseExpr
            END
        )";
        $taxRateExpr = 'COALESCE(products.TaxNet, 0)';
        $finalExpr = "ROUND(
            CASE
                WHEN products.tax_method = '2' THEN $afterDiscountExpr
                ELSE $afterDiscountExpr * (1 + ($taxRateExpr/100))
            END, 2
        )";

        return [$minVariantSub, $baseExpr, $afterDiscountExpr, $finalExpr];
    }
    /**
     * Hand-picked products for the "You May Also Like" block, returned in the
     * order the merchant arranged them (SQL has no opinion about that).
     */
    protected function buildPickedProducts(StoreSetting $s, float $defaultTaxRate, $productIds, int $limit)
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', is_array($productIds) ? $productIds : []))));
        if (! $ids) {
            return collect();
        }

        $ids = array_slice($ids, 0, $limit);
        $products = $this->hydrate($this->baseQuery()->whereIn('products.id', $ids), $s, $defaultTaxRate, $limit);
        $order = array_flip($ids);
        $products = $products->sortBy(fn ($p) => $order[$p->id] ?? PHP_INT_MAX)->values();
        $this->attachRatings($products);

        return $products;
    }

    /**
     * Build the best-selling products (by total quantity sold across online orders).
     * Powers the admin-managed "Frequently viewed items" homepage section.
     */
    protected function buildBestSellerProducts($s, string $baseExpr, string $afterDiscountExpr, string $finalExpr, $minVariantSub, float $defaultTaxRate, ?int $limit)
    {
        if (! Schema::hasTable('online_order_items')) {
            return collect();
        }

        $soldSub = DB::table('online_order_items')
            ->join('online_orders', 'online_orders.id', '=', 'online_order_items.order_id')
            ->whereNotIn('online_orders.status', ['cancelled', 'canceled', 'refunded'])
            ->whereNotNull('online_order_items.product_id')
            ->select('online_order_items.product_id', DB::raw('SUM(online_order_items.qty) AS sold_qty'))
            ->groupBy('online_order_items.product_id');

        $query = Product::query()
            ->whereNull('products.deleted_at')
            ->where('products.is_active', 1)
            ->where('products.hide_from_online_store', 0)
            ->with([
                'variants:id,product_id,name,price,image',
                'images:id,product_id,image_path,is_main,sort_order',
            ])
            ->joinSub($soldSub, 'sold', function ($join) {
                $join->on('sold.product_id', '=', 'products.id');
            })
            ->leftJoinSub($minVariantSub, 'pvmin', function ($join) {
                $join->on('pvmin.product_id', '=', 'products.id');
            })
            ->addSelect(
                'products.*',
                DB::raw("$baseExpr AS base_price"),
                DB::raw("$afterDiscountExpr AS after_discount"),
                DB::raw("$finalExpr AS final_display_price"),
                'sold.sold_qty'
            )
            ->orderByDesc('sold.sold_qty')
            ->orderBy('products.created_at', 'desc');

        if ($limit) {
            $query->take($limit);
        }

        $products = $query->get();
        $this->applyDisplayPrices($products, $defaultTaxRate);
        $this->attachStockToProducts($products, $s->default_warehouse_id ? (int) $s->default_warehouse_id : null);

        if ($s->hide_out_of_stock ?? false) {
            $products = $products->filter(fn ($p) => $this->productHasStock($p))->values();
        }

        
        return $products;
    }
    /**
     * Attach the SQL-computed final price to each product and compute each variant's
     * display price with the same discount + tax rules (mirrors the collection pipeline).
     */
    protected function applyDisplayPrices($products, float $defaultTaxRate): void
    {
        foreach ($products as $p) {
            $p->display_price = (float) ($p->final_display_price ?? 0);

            $taxRate = is_numeric($p->TaxNet) ? (float) $p->TaxNet : $defaultTaxRate;
            $discVal = is_numeric($p->discount) ? (float) $p->discount : 0.0;
            $isPercent = (string) $p->discount_method === '1';
            $isInclusive = (string) $p->tax_method === '2';

            if ($p->relationLoaded('variants') && $p->variants) {
                foreach ($p->variants as $v) {
                    $price = (float) ($v->price ?? 0);
                    if ($discVal > 0) {
                        $price = $isPercent ? ($price - ($price * $discVal / 100)) : ($price - min($discVal, $price));
                        if ($price < 0) {
                            $price = 0;
                        }
                    }
                    if (! $isInclusive && $taxRate > 0) {
                        $price = $price * (1 + $taxRate / 100);
                    }
                    $v->display_price = round($price, 2);
                }
            }
        }
    }
    /** Storefront-visible products, with the relations the cards need. */
    protected function baseQuery()
    {
        return Product::query()
            ->whereNull('products.deleted_at')
            ->where('products.is_active', 1)
            ->where('products.hide_from_online_store', 0)
            ->with([
                'variants:id,product_id,name,price,image',
                'images:id,product_id,image_path,is_main,sort_order',
            ]);
    }
    /**
     * Run a product query through the shared pricing + stock pipeline and
     * attach review ratings, so the theme's cards show the same numbers as
     * the shop page.
     */
    protected function hydrate($query, StoreSetting $s, float $defaultTaxRate, int $limit)
    {
        [$minVariantSub, $baseExpr, $afterDiscountExpr, $finalExpr] = $this->priceSqlParts();

        $products = $query
            ->leftJoinSub($minVariantSub, 'pvmin', fn ($join) => $join->on('pvmin.product_id', '=', 'products.id'))
            ->addSelect(
                'products.*',
                DB::raw("$baseExpr AS base_price"),
                DB::raw("$afterDiscountExpr AS after_discount"),
                DB::raw("$finalExpr AS final_display_price")
            )
            ->take(max(1, $limit * 2)) // over-fetch: hide_out_of_stock / vehicle filters may drop rows
            ->get();

        $this->applyDisplayPrices($products, $defaultTaxRate);

        // Strike-through for discounted products (flash sales set their own).
        foreach ($products as $p) {
            if (isset($p->compare_at_price)) {
                continue;
            }
            if ((float) ($p->discount ?? 0) > 0) {
                $isInclusive = (string) $p->tax_method === '2';
                $taxRate = is_numeric($p->TaxNet) ? (float) $p->TaxNet : $defaultTaxRate;
                $original = (float) ($p->base_price ?? $p->price);
                if (! $isInclusive && $taxRate > 0) {
                    $original = $original * (1 + $taxRate / 100);
                }
                $original = round($original, 2);
                if ($original > (float) $p->display_price + 0.01) {
                    $p->compare_at_price = $original;
                }
            }
        }

        $this->attachStockToProducts($products, $s->default_warehouse_id ? (int) $s->default_warehouse_id : null);

        if ($s->hide_out_of_stock ?? false) {
            $products = $products->filter(fn ($p) => $this->productHasStock($p))->values();
        }

        $products = $products->take($limit)->values();
        $this->attachRatings($products);

        return $products;
    }
    /** product_id → avg rating / count for approved reviews. */
    protected function ratingSubquery()
    {
        if (! Schema::hasTable('product_reviews')) {
            return null;
        }

        return DB::table('product_reviews')
            ->where('status', 'approved')
            ->select('product_id', DB::raw('AVG(rating) AS rating_avg'), DB::raw('COUNT(*) AS rating_count'))
            ->groupBy('product_id');
    }
    protected function attachRatings($products): void
    {
        if ($products->isEmpty() || ! Schema::hasTable('product_reviews')) {
            return;
        }
        $ids = $products->pluck('id')->all();
        $rows = DB::table('product_reviews')
            ->where('status', 'approved')
            ->whereIn('product_id', $ids)
            ->select('product_id', DB::raw('AVG(rating) AS rating_avg'), DB::raw('COUNT(*) AS rating_count'))
            ->groupBy('product_id')
            ->get()
            ->keyBy('product_id');

        foreach ($products as $p) {
            $r = $rows[$p->id] ?? null;
            $p->rating_avg = $r ? round((float) $r->rating_avg, 1) : null;
            $p->rating_count = $r ? (int) $r->rating_count : 0;
        }
    }
    /**
     * Default theme homepage: data for the automatic sections.
     */
    protected function buildAutoSections(StoreSetting $s, array $blocks, $categories, float $defaultTaxRate, bool $lineupIsEmpty = true): array
    {
        $visible = Product::query()->where('is_active', 1)->where('hide_from_online_store', 0);
        $counts = (clone $visible)->select('category_id', DB::raw('COUNT(*) AS c'))->groupBy('category_id')->pluck('c', 'category_id');

        $tiles = $categories->map(function ($c) use ($counts) {
            $img = Product::query()->where('category_id', $c->id)->where('is_active', 1)->where('hide_from_online_store', 0)
                ->whereNotNull('image')->where('image', '!=', '')->where('image', '!=', 'no-image.png')
                ->orderByDesc('is_featured')->orderByDesc('created_at')->value('image');

            return [
                'id' => $c->id, 'name' => $c->name, 'count' => (int) ($counts[$c->id] ?? 0),
                'image_url' => $img ? product_image_url($img) : null,
                'url' => route('store.shop', ['category' => $c->id]),
            ];
        })->filter(fn ($t) => $t['count'] > 0)->sortByDesc('count')->take(12)->values();

        $blockTypes = collect($blocks)->pluck('type')->all();
        $seen = collect($blocks)->flatMap(fn ($b) => isset($b['products']) ? collect($b['products'])->pluck('id') : collect())->all();

        $newArrivals = $this->hydrate(
            $this->baseQuery()->when($seen, fn ($q) => $q->whereNotIn('products.id', $seen))->orderByDesc('products.created_at')->orderByDesc('products.id'),
            $s, $defaultTaxRate, 8
        );

        // Best Sellers is the one auto section with a toggle in Store Settings
        // -> Homepage blocks. Only fall back to rendering it automatically on a
        // store that has never configured a lineup; once one exists, the block
        // is shown when (and only when) the merchant enabled it, which the
        // lineup loop above turns into a 'best_sellers' block.
        $bestSellers = collect();
        if ($lineupIsEmpty && ! in_array('best_sellers', $blockTypes, true)) {
            [$minVariantSub, $baseExpr, $afterDiscountExpr, $finalExpr] = $this->priceSqlParts();
            $bestSellers = $this->buildBestSellerProducts($s, $baseExpr, $afterDiscountExpr, $finalExpr, $minVariantSub, $defaultTaxRate, 8);
            if ($bestSellers->count() < 4) {
                $have = array_merge($bestSellers->pluck('id')->all(), $newArrivals->pluck('id')->all(), $seen);
                $more = $this->hydrate(
                    $this->baseQuery()->when($have, fn ($q) => $q->whereNotIn('products.id', $have))->orderByDesc('products.is_featured')->orderByDesc('products.price'),
                    $s, $defaultTaxRate, 8 - $bestSellers->count()
                );
                $bestSellers = $bestSellers->concat($more)->values();
            }
            $this->attachRatings($bestSellers);
        }

        $flashIds = store_flash_ids();
        $onSale = $this->hydrate(
            $this->baseQuery()->where(function ($q) use ($flashIds) {
                $q->where('products.discount', '>', 0);
                if ($flashIds) {
                    $q->orWhereIn('products.id', $flashIds);
                }
            })->orderByDesc('products.discount')->orderByDesc('products.created_at'),
            $s, $defaultTaxRate, 8
        );

        // Promo tiles: the two biggest categories, using their newest product photo.
        $promos = $tiles->filter(fn ($t) => $t['image_url'])->take(2)->values()->map(fn ($t, $i) => [
            'kicker' => $i === 0 ? __('messages.Handpicked') : __('messages.JustListed'),
            'title' => $t['name'],
            'subtitle' => trans_choice('messages.products', $t['count'], ['count' => $t['count']]),
            'image' => $t['image_url'],
            'url' => $t['url'],
        ]);

        $testimonials = Schema::hasTable('product_reviews')
            ? ProductReview::query()->with('product:id,name')->where('status', 'approved')->where('rating', '>=', 4)
                ->whereNotNull('comment')->where('comment', '!=', '')->orderByDesc('created_at')->take(3)->get()
            : collect();

        $brandIds = (clone $visible)->whereNotNull('brand_id')->distinct()->pluck('brand_id');
        $brands = $brandIds->count() >= 3 ? Brand::whereIn('id', $brandIds)->orderBy('name')->take(12)->get() : collect();

        $trust = [
            ['icon' => 'truck', 'title' => __('messages.FastShipping'), 'subtitle' => __('messages.TrustShippingText')],
            ['icon' => 'shield-check', 'title' => __('messages.SecurePayment'), 'subtitle' => __('messages.TrustPaymentText')],
            ['icon' => 'refresh', 'title' => __('messages.ReturnsRefunds'), 'subtitle' => __('messages.TrustReturnsText')],
            ['icon' => 'message', 'title' => __('messages.Support'), 'subtitle' => __('messages.TrustSupportText')],
        ];

        $stats = [
            'products' => (clone $visible)->count(),
            'rating' => Schema::hasTable('product_reviews') ? round((float) (ProductReview::where('status', 'approved')->avg('rating') ?: 0), 1) : 0,
            'reviews' => Schema::hasTable('product_reviews') ? ProductReview::where('status', 'approved')->count() : 0,
        ];

        return compact('tiles', 'newArrivals', 'bestSellers', 'onSale', 'promos', 'testimonials', 'brands', 'trust', 'stats')
            + ['best_sellers' => $bestSellers, 'new_arrivals' => $newArrivals, 'on_sale' => $onSale];
    }
}

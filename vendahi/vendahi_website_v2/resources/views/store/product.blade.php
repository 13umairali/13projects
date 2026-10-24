@extends('layouts.store')

@section('content')
@php
  /** @var \App\Models\StoreSetting $s */
  /** @var \App\Models\Product $product */
  $p = $product;
  $currency = store_currency()['symbol'];
  $dec = \App\utils\helpers::price_decimals();

  // ----- Gallery -----
  $galleryUrls = collect($p->productGalleryFilenames())
    ->map(fn ($f) => product_image_url_or_null($f))
    ->filter()->values()->all();
  $imgUrl = product_image_url($p->primaryProductImageFilename());
  if (empty($galleryUrls)) { $galleryUrls = [$imgUrl]; }

  // ----- Pricing -----
  $minPrice = (float) ($p->display_price ?? ($p->price ?? 0));
  // Original (pre-discount) final price, for the "was" strikethrough.
  $isInclusive = (string) $p->tax_method === '2';
  $taxRate = is_numeric($p->TaxNet) ? (float) $p->TaxNet : (float) ($s->default_tax_rate ?? 0);
  $original = (float) ($p->base_price ?? $p->price);
  if (! $isInclusive && $taxRate > 0) { $original = $original * (1 + $taxRate / 100); }
  $original = round($original, 2);
  // Flash sales set compare_at_price; otherwise a plain product discount is
  // what makes the pre-discount price worth showing.
  $compareAt = isset($p->compare_at_price)
    ? (float) $p->compare_at_price
    : ($original > $minPrice + 0.01 ? $original : null);

  // ----- Variants (flat list; this codebase has no option matrix) -----
  $variants = collect($p->relationLoaded('variants') ? $p->variants : collect($p->variants ?? []));
  $variantPayload = $variants->map(function ($v) use ($currency) {
    $final = (float) ($v->display_price ?? ($v->price ?? 0));
    return [
      'id' => (int) ($v->id ?? 0),
      'name' => (string) ($v->name ?? ''),
      'price' => (float) ($v->price ?? 0),
      'display_price' => $final,
      'display_price_formatted' => store_money($final),
      'image' => product_image_url_or_null($v->image ?? null),
      'stock' => (int) max(0, $v->stock ?? $v->qty ?? 0),
    ];
  })->values();
  $productStock = $variants->isEmpty() ? (int) max(0, $p->stock ?? 0) : null;

  // ----- Availability (mirrors store.partials.product-card) -----
  $allowOverselling = (bool) ($s->allow_overselling ?? true);
  if ((bool) ($p->always_in_stock ?? false)) {
    $allowOverselling = true;
    $productStock = null;
    $variantPayload = $variantPayload->map(fn ($v) => ['stock' => null] + $v);
  }
  $isPreorder = (bool) ($p->is_preorder ?? false);
  $preorderDate = $p->preorder_available_date ? store_date($p->preorder_available_date) : null;
  $quoteOnly = (($p->type ?? '') === 'is_service');
  $hidePrices = ! Auth::guard('store')->check() && ($s->hide_prices_for_guests ?? false);

  if ($variants->isEmpty()) {
    $isAvailable = $allowOverselling || ($productStock !== null && $productStock > 0);
  } else {
    $isAvailable = $allowOverselling || $variantPayload->contains(fn ($v) => ($v['stock'] ?? 0) > 0);
  }
  $isPreorderActive = false;
  if ($isPreorder) {
    $outOfStock = $variants->isEmpty()
      ? ($productStock !== null && $productStock <= 0)
      : ! $variantPayload->contains(fn ($v) => ($v['stock'] ?? 0) > 0);
    if ($outOfStock) { $isPreorderActive = true; $isAvailable = true; }
  }

  // ----- Specification rows -----
  $specs = [];
  if ($p->code) $specs[] = [__('messages.SKU'), $p->code];
  if ($p->brand) $specs[] = [__('messages.Brand'), $p->brand->name];
  if ($p->category) $specs[] = [__('messages.Category'), $p->category->name];
  if ($p->subCategory) $specs[] = [__('messages.SubCategory'), $p->subCategory->name];
  if (is_numeric($p->weight) && $p->weight > 0) $specs[] = [__('messages.Weight'), rtrim(rtrim(number_format($p->weight, 3, '.', ''), '0'), '.')];
  $dims = array_filter([$p->length, $p->width, $p->height], fn ($d) => is_numeric($d) && $d > 0);
  if (count($dims) === 3) $specs[] = [__('messages.Dimensions'), $p->length.' × '.$p->width.' × '.$p->height];
  if ($p->warranty_period && $p->warranty_unit) $specs[] = [__('messages.Warranty'), $p->warranty_period.' '.$p->warranty_unit];
  if ($p->manufacturer) $specs[] = [__('messages.Manufacturer'), $p->manufacturer];
  if ($p->generic_name) $specs[] = [__('messages.GenericName'), $p->generic_name];
  if ($p->strength) $specs[] = [__('messages.Strength'), $p->strength];
  if ($p->dosage_form) $specs[] = [__('messages.DosageForm'), $p->dosage_form];
  if ($p->pack_size) $specs[] = [__('messages.PackSize'), $p->pack_size];

  $returnDays = (int) ($s->return_window_days ?? 14);

  // Shipping is priced per ORDER and per destination, not per product — so the
  // page previews the cheapest method reaching this shopper, and says
  // "calculated at checkout" while the destination is unknown (guest).
  $shipCountry = optional(Auth::guard('store')->user())->country;
  $shipMethods = $shipCountry
      ? app(\App\Services\CheckoutService::class)->availableShippingMethods($shipCountry)
      : collect();
  $shipCheapest = collect($shipMethods)->sortBy('price')->first();
  $shipAnyConfigured = \App\Models\ShippingMethod::where('active', true)->exists();

  $productUrl = route('store.product.show', $p->id);
@endphp

<div class="container py-6 lg:py-8">

  {{-- Breadcrumb --}}
  <nav class="text-sm text-fg-muted mb-4 flex items-center gap-1 flex-wrap">
    <a href="{{ route('store.index') }}" class="hover:text-accent-500">{{ __('messages.Home') }}</a>
    <x-store.icon name="chevron-right" class="w-4 h-4" />
    <a href="{{ route('store.shop') }}" class="hover:text-accent-500">{{ __('messages.Shop') }}</a>
    @if($p->category)
      <x-store.icon name="chevron-right" class="w-4 h-4" />
      <a href="{{ route('store.shop', ['category' => $p->category_id]) }}" class="hover:text-accent-500">{{ $p->category->name }}</a>
    @endif
    <x-store.icon name="chevron-right" class="w-4 h-4" />
    <span class="text-fg-secondary truncate">{{ $p->name }}</span>
  </nav>

  <div class="grid lg:grid-cols-2 gap-8 lg:gap-12">

    {{-- ===== Gallery ===== --}}
    <div>
      <div class="rounded-xl overflow-hidden border border-line-subtle bg-bg-surface">
        <img id="pdpMainImg" src="{{ $imgUrl }}" alt="{{ $p->name }}"
             class="w-full h-auto object-contain max-h-[520px] mx-auto cursor-zoom-in">
      </div>
      @if(count($galleryUrls) > 1)
        <div class="mt-3 flex gap-2 flex-wrap">
          @foreach($galleryUrls as $gi => $g)
            <button type="button"
                    class="pdp-thumb rounded-lg overflow-hidden border-2 {{ $gi === 0 ? 'border-accent-500' : 'border-line-subtle' }}"
                    data-full="{{ $g }}" style="width:72px;height:72px;">
              <img src="{{ $g }}" alt="" class="w-full h-full object-cover">
            </button>
          @endforeach
        </div>
      @endif
    </div>

    {{-- ===== Summary ===== --}}
    <div>
      @if($p->brand)
        <div class="text-sm text-fg-muted mb-1">{{ $p->brand->name }}</div>
      @endif
      <h1 class="text-2xl lg:text-3xl font-bold text-fg-primary mb-2">{{ $p->name }}</h1>

      {{-- Rating (server-rendered, refined by the reviews fetch) --}}
      <div class="flex items-center gap-2 mb-3 text-sm">
        @php $avg = (float) ($reviewStats['average'] ?? 0); $rc = (int) ($reviewStats['count'] ?? 0); @endphp
        <span id="pdpStars" style="color:#f5a623;letter-spacing:1px;">
          {!! str_repeat('★', (int) round($avg)).str_repeat('☆', 5 - (int) round($avg)) !!}
        </span>
        <a href="#pdpTabReviews" class="text-fg-muted hover:text-accent-500 js-tab-link" data-tab="reviews">
          <span id="pdpReviewMeta">@if($rc > 0){{ $avg }} · {{ $rc }} {{ __('messages.Reviews') }}@else{{ __('messages.NoReviewsYet') }}@endif</span>
        </a>
      </div>

      {{-- Price --}}
      @unless($hidePrices)
        <div class="flex items-center gap-3 mb-4 flex-wrap">
          <span id="pdpPrice" class="text-3xl font-bold text-fg-primary">{{ store_money($minPrice) }}</span>
          @if($compareAt && $compareAt > 0)
            <span id="pdpCompare" class="text-lg text-fg-muted line-through">{{ store_money($compareAt) }}</span>
            <span class="chip chip-danger text-xs">-{{ round(($compareAt - $minPrice) / $compareAt * 100) }}%</span>
          @endif
        </div>
      @endunless

      {{-- Short description --}}
      @if($p->note)
        <div class="text-sm text-fg-secondary mb-4 leading-relaxed">
          {{ \Illuminate\Support\Str::limit(strip_tags($p->note), 260) }}
        </div>
      @endif

      {{-- Variants --}}
      @if($variants->isNotEmpty())
        <div class="mb-4">
          <div class="text-sm font-medium text-fg-secondary mb-2">{{ __('messages.Options') }}</div>
          <div id="pdpVariants" class="flex flex-wrap gap-2">
            @foreach($variantPayload as $vi => $v)
              <button type="button"
                      class="pdp-variant btn btn-outline btn-sm {{ $vi === 0 ? 'is-selected' : '' }}"
                      data-variant-id="{{ $v['id'] }}">
                {{ $v['name'] }}
              </button>
            @endforeach
          </div>
        </div>
      @endif

      {{-- Availability --}}
      @if(($s->show_stock ?? true) && ! $quoteOnly)
        <div class="mb-4 text-sm flex items-center gap-2" id="pdpStockLine">
          @if($isPreorderActive)
            <span class="stock-dot stock-dot-warn"></span>
            <span>{{ $preorderDate ? __('messages.PreorderAvailableOn', ['date' => $preorderDate]) : __('messages.PreorderAvailable') }}</span>
          @elseif($isAvailable)
            <span class="stock-dot stock-dot-ok"></span>
            <span id="pdpStockText">@if($variants->isEmpty() && $productStock !== null && ! $allowOverselling){{ __('messages.X_in_stock', ['count' => $productStock]) }}@else{{ __('messages.InStock') }}@endif</span>
          @else
            <span class="stock-dot stock-dot-out"></span>
            <span>{{ __('messages.OutOfStock') }}</span>
          @endif
        </div>
      @endif

      {{-- Quote-only OR buy controls --}}
      @if($quoteOnly)
        <button type="button" class="btn btn-primary btn-lg js-request-quote" data-id="{{ $p->id }}" data-name="{{ e($p->name) }}">
          <x-store.icon name="mail" class="w-5 h-5" />{{ __('messages.RequestQuotation') }}
        </button>
      @elseif(! $hidePrices)
        <div class="flex items-stretch gap-3 mb-3 flex-wrap">
          {{-- Quantity stepper --}}
          <div class="inline-flex items-center border border-line-subtle rounded-lg overflow-hidden">
            <button type="button" id="pdpQtyMinus" class="px-3 py-2 hover:bg-bg-muted" aria-label="-"><x-store.icon name="minus" class="w-4 h-4" /></button>
            <input id="pdpQty" type="number" min="1" value="1" class="w-14 text-center border-0 focus:ring-0 bg-transparent">
            <button type="button" id="pdpQtyPlus" class="px-3 py-2 hover:bg-bg-muted" aria-label="+"><x-store.icon name="plus" class="w-4 h-4" /></button>
          </div>

          <button type="button" id="pdpAddBtn"
                  class="btn {{ $isPreorderActive ? 'btn-warning' : 'btn-primary' }} btn-lg flex-1 min-w-[160px]"
                  @unless($isAvailable) disabled @endunless>
            <x-store.icon name="{{ $isPreorderActive ? 'clock' : 'cart' }}" class="w-5 h-5" />
            <span id="pdpAddLabel">{{ $isPreorderActive ? __('messages.PreOrderNow') : __('messages.AddToCart') }}</span>
          </button>
        </div>

        <button type="button" id="pdpBuyBtn" class="btn btn-accent btn-lg btn-block mb-4"
                @unless($isAvailable) disabled @endunless>
          <x-store.icon name="lightning" class="w-5 h-5" />{{ __('messages.BuyNow') }}
        </button>
      @endif

      {{-- Meta: SKU / Category --}}
      <div class="border-t border-line-subtle pt-4 space-y-2 text-sm">
        @if($p->code)
          <div><span class="text-fg-muted">{{ __('messages.SKU') }}:</span> <span class="text-fg-secondary">{{ $p->code }}</span></div>
        @endif
        @if($p->category)
          <div>
            <span class="text-fg-muted">{{ __('messages.Category') }}:</span>
            <a href="{{ route('store.shop', ['category' => $p->category_id]) }}" class="text-accent-500 hover:underline">{{ $p->category->name }}</a>
            @if($p->subCategory)
              , <a href="{{ route('store.shop', ['category' => $p->category_id, 'sub_category' => $p->sub_category_id]) }}" class="text-accent-500 hover:underline">{{ $p->subCategory->name }}</a>
            @endif
          </div>
        @endif
      </div>

      {{-- Share --}}
      <div class="flex items-center gap-3 mt-4 text-sm">
        <span class="text-fg-muted">{{ __('messages.Share') }}:</span>
        @php $enc = urlencode($productUrl); $encName = urlencode($p->name); @endphp
        <a target="_blank" rel="noopener" href="https://www.facebook.com/sharer/sharer.php?u={{ $enc }}" aria-label="Facebook" class="text-fg-muted hover:text-accent-500"><x-store.icon name="facebook" class="w-4 h-4" /></a>
        <a target="_blank" rel="noopener" href="https://twitter.com/intent/tweet?url={{ $enc }}&text={{ $encName }}" aria-label="X" class="text-fg-muted hover:text-accent-500"><x-store.icon name="twitter-x" class="w-4 h-4" /></a>
        <a target="_blank" rel="noopener" href="https://api.whatsapp.com/send?text={{ $encName }}%20{{ $enc }}" aria-label="WhatsApp" class="text-fg-muted hover:text-accent-500"><x-store.icon name="whatsapp" class="w-4 h-4" /></a>
        <a target="_blank" rel="noopener" href="mailto:?subject={{ $encName }}&body={{ $enc }}" aria-label="Email" class="text-fg-muted hover:text-accent-500"><x-store.icon name="mail" class="w-4 h-4" /></a>
        <button type="button" id="pdpCopyLink" data-url="{{ $productUrl }}" aria-label="{{ __('messages.CopyLink') }}" class="text-fg-muted hover:text-accent-500"><x-store.icon name="link" class="w-4 h-4" /></button>
      </div>

      {{-- Trust badges --}}
      <div class="mt-5 rounded-lg border border-line-subtle p-4 space-y-2 text-sm text-fg-secondary">
        @if($preorderDate)
          <div class="flex items-center gap-2 text-fg-primary font-medium">
            <x-store.icon name="truck" class="w-4 h-4 text-accent-500" />{{ __('messages.EstimatedDelivery') }}: {{ $preorderDate }}
          </div>
        @endif
        <div class="flex items-center gap-2"><x-store.icon name="refresh" class="w-4 h-4 text-accent-500" />{{ __('messages.XDaysEasyReturns', ['days' => $returnDays]) }}</div>
        @if($shipAnyConfigured)
          <div class="flex items-center gap-2">
            <x-store.icon name="package" class="w-4 h-4 text-accent-500" />
            @if($shipCheapest)
              {{ __('messages.ShippingFrom', ['price' => store_money($shipCheapest->price)]) }}
            @else
              {{ __('messages.ShippingCalculatedAtCheckout') }}
            @endif
          </div>
        @endif
        <div class="flex items-center gap-2"><x-store.icon name="shield-check" class="w-4 h-4 text-accent-500" />{{ __('messages.GuaranteedSafeCheckout') }}</div>
      </div>
    </div>
  </div>

  {{-- ===== Tabs ===== --}}
  <div class="mt-12" id="pdpTabs">
    <div class="border-b border-line-subtle flex gap-6 overflow-x-auto">
      <button type="button" class="pdp-tab is-active py-3 border-b-2 border-accent-500 font-medium whitespace-nowrap" data-tab="description">{{ __('messages.Description') }}</button>
      @if(count($specs))<button type="button" class="pdp-tab py-3 border-b-2 border-transparent text-fg-muted whitespace-nowrap" data-tab="specification">{{ __('messages.ProductSpecification') }}</button>@endif
      <button type="button" class="pdp-tab py-3 border-b-2 border-transparent text-fg-muted whitespace-nowrap" data-tab="reviews" id="pdpTabReviews">{{ __('messages.Reviews') }} (<span id="pdpTabReviewCount">{{ $reviewStats['count'] ?? 0 }}</span>)</button>
      @if($p->brand)<button type="button" class="pdp-tab py-3 border-b-2 border-transparent text-fg-muted whitespace-nowrap" data-tab="vendor">{{ __('messages.Vendor') }}</button>@endif
    </div>

    <div class="py-6">
      {{-- Description --}}
      <div class="pdp-panel" data-panel="description">
        <div class="prose max-w-none text-fg-secondary leading-relaxed">
          {!! $p->note ? nl2br(e($p->note)) : '<span class="text-fg-muted">'.e(__('messages.NoDescription')).'</span>' !!}
        </div>
      </div>

      {{-- Specification --}}
      @if(count($specs))
        <div class="pdp-panel hidden" data-panel="specification">
          <table class="w-full text-sm max-w-2xl">
            <tbody>
              @foreach($specs as $row)
                <tr class="border-b border-line-subtle">
                  <td class="py-2 pr-4 text-fg-muted w-1/3">{{ $row[0] }}</td>
                  <td class="py-2 text-fg-secondary">{{ $row[1] }}</td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      @endif

      {{-- Reviews --}}
      <div class="pdp-panel hidden" data-panel="reviews">
        <div id="pdpReviewsSummary" class="mb-4"></div>
        <div id="pdpReviewsList" class="space-y-4"></div>
        <div id="pdpReviewsEmpty" class="text-fg-muted hidden">{{ __('messages.NoReviewsYet') }}</div>
      </div>

      {{-- Vendor --}}
      @if($p->brand)
        <div class="pdp-panel hidden" data-panel="vendor">
          <div class="flex items-start gap-4">
            @if($p->brand->image)
              <img src="{{ url($p->brand->image) }}" alt="{{ $p->brand->name }}" class="w-20 h-20 object-contain rounded-lg border border-line-subtle" onerror="this.style.display='none'">
            @endif
            <div>
              <div class="text-lg font-semibold text-fg-primary">{{ $p->brand->name }}</div>
              @if($p->brand->description)
                <div class="text-sm text-fg-secondary mt-1 leading-relaxed">{{ $p->brand->description }}</div>
              @endif
            </div>
          </div>
        </div>
      @endif
    </div>
  </div>

  {{-- ===== Related ("You may also like") ===== --}}
  @if($related->count())
    <section class="mt-12">
      <div class="flex items-end justify-between mb-6">
        <div>
          <span class="section-kicker">{{ __('messages.YouMayAlsoLike') }}</span>
          <h2 class="section-title mt-1">{{ __('messages.RelatedProducts') }}</h2>
        </div>
      </div>
      <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 lg:gap-6">
        @foreach($related as $rp)
          @include('store.partials.product-card', ['p' => $rp, 'currency' => $currency])
        @endforeach
      </div>
    </section>
  @endif
</div>

{{-- Reuse the shop's quick-view + variant picker + cart engine for the related cards --}}
@include('store.partials.shop-modals-scripts', ['currency' => $currency])

<script>
(function(){
  var PID = {{ (int) $p->id }};
  var CURRENCY = @json($currency);
  var SLUG = @json((string) $p->id);
  var NAME = @json($p->name);
  var IMG = @json($imgUrl);
  var VARIANTS = @json($variantPayload);
  var BASE_PRICE = {{ number_format($minPrice, $dec, '.', '') }};
  var ADDED = @json(__('messages.Added'));
  var CHECKOUT_URL = @json(route('checkout'));
  var REVIEWS_URL = @json($productUrl);
  var REVIEWS_ENDPOINT = @json(url('/'.store_path_to('products')));
  var IN_STOCK_LABEL = @json(__('messages.InStock'));
  var OUT_LABEL = @json(__('messages.OutOfStock'));
  var REVIEWS_LABEL = @json(__('messages.Reviews'));
  var IS_PREORDER = {{ $isPreorderActive ? 'true' : 'false' }};
  var ALLOW_OVERSELL = {{ $allowOverselling ? 'true' : 'false' }};

  var selected = { id: null, price: BASE_PRICE, stock: null, image: IMG, name: '' };
  if (VARIANTS && VARIANTS.length) {
    selected = { id: VARIANTS[0].id, price: VARIANTS[0].display_price, stock: VARIANTS[0].stock, image: VARIANTS[0].image || IMG, name: VARIANTS[0].name };
  }

  function fmt(v){ try { return (typeof fmtMoney === 'function') ? fmtMoney(v) : (CURRENCY + Number(v).toFixed(2)); } catch(e){ return CURRENCY + Number(v).toFixed(2); } }

  // ---- Gallery ----
  var mainImg = document.getElementById('pdpMainImg');
  document.querySelectorAll('.pdp-thumb').forEach(function(btn){
    btn.addEventListener('click', function(){
      var full = btn.getAttribute('data-full'); if (!full || !mainImg) return;
      mainImg.src = full;
      document.querySelectorAll('.pdp-thumb').forEach(function(b){ b.classList.remove('border-accent-500'); b.classList.add('border-line-subtle'); });
      btn.classList.add('border-accent-500'); btn.classList.remove('border-line-subtle');
    });
  });

  // ---- Variants ----
  var priceEl = document.getElementById('pdpPrice');
  var addBtn = document.getElementById('pdpAddBtn');
  var buyBtn = document.getElementById('pdpBuyBtn');
  var stockText = document.getElementById('pdpStockText');
  function refreshAvailability(){
    var avail = ALLOW_OVERSELL || IS_PREORDER || selected.stock === null || Number(selected.stock) > 0;
    if (addBtn){ addBtn.disabled = !avail; }
    if (buyBtn){ buyBtn.disabled = !avail; }
    if (stockText && selected.stock !== null){ stockText.textContent = avail ? IN_STOCK_LABEL : OUT_LABEL; }
  }
  function applyVariant(v){
    if (!v){
      selected.id = null; selected.name = '';
      if (addBtn){ addBtn.disabled = true; }
      if (buyBtn){ buyBtn.disabled = true; }
      return;
    }
    selected.id = Number(v.id);
    selected.price = Number(v.display_price);
    selected.stock = (v.stock === null || v.stock === undefined) ? null : Number(v.stock);
    selected.name = v.name || '';
    if (priceEl){ priceEl.textContent = v.display_price_formatted || fmt(selected.price); }
    if (v.image && v.image !== 'null' && mainImg){ mainImg.src = v.image; selected.image = v.image; }
    refreshAvailability();
  }
  document.querySelectorAll('.pdp-variant').forEach(function(btn){
    btn.addEventListener('click', function(){
      document.querySelectorAll('.pdp-variant').forEach(function(b){ b.classList.remove('is-selected'); });
      btn.classList.add('is-selected');
      var id = Number(btn.getAttribute('data-variant-id'));
      applyVariant(VARIANTS.find(function(v){ return Number(v.id) === id; }) || null);
    });
  });
  refreshAvailability();

  // ---- Quantity ----
  var qtyEl = document.getElementById('pdpQty');
  function getQty(){ var q = parseInt(qtyEl ? qtyEl.value : '1', 10); return (isNaN(q) || q < 1) ? 1 : q; }
  var minus = document.getElementById('pdpQtyMinus'), plus = document.getElementById('pdpQtyPlus');
  if (minus) minus.addEventListener('click', function(){ if(qtyEl){ qtyEl.value = Math.max(1, getQty() - 1); } });
  if (plus)  plus.addEventListener('click',  function(){ if(qtyEl){ qtyEl.value = getQty() + 1; } });

  // ---- Add to cart ----
  function buildItem(){
    var item = { name: NAME, price: selected.price, image: selected.image || IMG, slug: SLUG, currency: CURRENCY };
    if (selected.id){
      item.id = PID + ':' + selected.id;
      item.product_id = PID;
      item.product_variant_id = selected.id;
      item.variant_name = selected.name;
      item.name = NAME + (selected.name ? ' — ' + selected.name : '');
      if (selected.stock !== null) item.stock = selected.stock;
    } else {
      item.id = String(PID);
      @if($productStock !== null)item.stock = {{ (int) $productStock }};@endif
    }
    return item;
  }
  function addToCart(){
    if (!window.CartLS) return false;
    if (VARIANTS && VARIANTS.length && !selected.id){ return false; }
    window.CartLS.add(buildItem(), getQty());
    return true;
  }
  if (addBtn){
    addBtn.addEventListener('click', function(){
      if (addToCart()){
        var lbl = document.getElementById('pdpAddLabel');
        if (lbl){ var old = lbl.textContent; lbl.textContent = ADDED; setTimeout(function(){ lbl.textContent = old; }, 1400); }
        if (window.StoreUI){ window.StoreUI.open('miniCart'); }
      }
    });
  }
  if (buyBtn){
    buyBtn.addEventListener('click', function(){
      if (!addToCart()) return;
      if (window.__LOGGED_IN__){ window.location.href = CHECKOUT_URL; }
      else if (window.StoreUI){ window.StoreUI.open('authModal'); }
    });
  }

  // ---- Tabs ----
  function activateTab(name){
    document.querySelectorAll('.pdp-tab').forEach(function(t){
      var on = t.getAttribute('data-tab') === name;
      t.classList.toggle('is-active', on);
      t.classList.toggle('border-accent-500', on);
      t.classList.toggle('border-transparent', !on);
      t.classList.toggle('text-fg-muted', !on);
      t.classList.toggle('font-medium', on);
    });
    document.querySelectorAll('.pdp-panel').forEach(function(pnl){
      pnl.classList.toggle('hidden', pnl.getAttribute('data-panel') !== name);
    });
  }
  document.querySelectorAll('.pdp-tab').forEach(function(t){
    t.addEventListener('click', function(){ activateTab(t.getAttribute('data-tab')); });
  });
  document.querySelectorAll('.js-tab-link').forEach(function(l){
    l.addEventListener('click', function(e){ e.preventDefault(); activateTab('reviews'); document.getElementById('pdpTabs').scrollIntoView({behavior:'smooth'}); });
  });

  // ---- Reviews ----
  function starStr(n){ n = Math.round(n); return '★'.repeat(n) + '☆'.repeat(5 - n); }
  function esc(s){ var d = document.createElement('div'); d.textContent = (s == null ? '' : String(s)); return d.innerHTML; }
  (function loadReviews(){
    var list = document.getElementById('pdpReviewsList');
    var summary = document.getElementById('pdpReviewsSummary');
    var empty = document.getElementById('pdpReviewsEmpty');
    fetch(REVIEWS_ENDPOINT + '/' + PID + '/reviews', { headers:{'Accept':'application/json'} })
      .then(function(r){ return r.ok ? r.json() : null; })
      .then(function(d){
        if (!d) return;
        var cnt = document.getElementById('pdpTabReviewCount'); if (cnt) cnt.textContent = d.count;
        var meta = document.getElementById('pdpReviewMeta');
        var stars = document.getElementById('pdpStars');
        if (d.count > 0){
          if (stars) stars.textContent = starStr(d.average);
          if (meta) meta.textContent = d.average + ' · ' + d.count + ' ' + REVIEWS_LABEL;
          if (summary) summary.innerHTML = '<div class="text-3xl font-bold">' + d.average + ' <span style="color:#f5a623">' + starStr(d.average) + '</span></div><div class="text-sm text-fg-muted">' + d.count + ' ' + REVIEWS_LABEL + '</div>';
          if (list) list.innerHTML = d.reviews.map(function(rv){
            return '<div class="border-b border-line-subtle pb-3">'
              + '<div class="flex items-center gap-2"><span style="color:#f5a623">' + starStr(rv.rating) + '</span>'
              + '<span class="text-sm font-medium">' + esc(rv.name) + '</span>'
              + '<span class="text-xs text-fg-muted">' + esc(rv.date || '') + '</span></div>'
              + (rv.comment ? '<div class="text-sm text-fg-secondary mt-1">' + esc(rv.comment) + '</div>' : '')
              + '</div>';
          }).join('');
        } else if (empty){ empty.classList.remove('hidden'); }
      }).catch(function(){});
  })();

  // ---- Copy link ----
  var copyBtn = document.getElementById('pdpCopyLink');
  if (copyBtn){
    copyBtn.addEventListener('click', function(){
      var url = copyBtn.getAttribute('data-url');
      if (navigator.clipboard){ navigator.clipboard.writeText(url); }
      if (typeof window.showStockAlert === 'function'){ window.showStockAlert(@json(__('messages.LinkCopied'))); }
    });
  }

  // ---- Image zoom ----
  if (mainImg){ mainImg.addEventListener('click', function(){ window.open(mainImg.src, '_blank'); }); }
})();
</script>
@endsection

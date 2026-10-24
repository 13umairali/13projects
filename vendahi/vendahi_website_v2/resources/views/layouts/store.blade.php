{{-- layouts/store.blade.php — Tailwind + Alpine shell. Bootstrap 5 removed
     entirely; all dialogs, drawers, tabs, dropdowns use Alpine. --}}
@php
  use Illuminate\Support\Str;

  $categories = $categories ?? collect();
  // "Skin" themes reuse this layout and swap head/header/footer partials from
  // resources/views/store/<theme>/partials/ (see \App\Support\StoreThemes).
  $storeTheme = $s->theme ?? 'default';
  $skinTheme = in_array($storeTheme, \App\Support\StoreThemes::SKINS, true) ? $storeTheme : null;
  $isElectronics = $skinTheme !== null;
  $primary   = $s->primary_color   ?? '#3B82F6';
  $secondary = $s->secondary_color ?? '#22D3EE';
  $title     = $s->seo_meta_title  ?? ($s->store_name ?? __('messages.Store'));
  $desc      = $s->seo_meta_description ?? '';

  // Per-page SEO override. Pages that describe one thing (the product detail
  // page) pass $seo; every other page leaves it unset and keeps the store
  // defaults above.
  $seo       = is_array($seo ?? null) ? $seo : [];
  $title     = $seo['title'] ?? $title;
  $desc      = $seo['description'] ?? $desc;
  $ogType    = $seo['type'] ?? 'website';
  $canonical = $seo['canonical'] ?? url()->current();
  $jsonLd    = $seo['jsonld'] ?? null;

  // Social links — normalize to [{platform,url}]
  $social = $s->social_links ?? [];
  if (is_string($social)) {
      $d = json_decode($social, true);
      if (json_last_error() === JSON_ERROR_NONE) $social = $d;
  }
  if (!is_array($social)) { $social = []; }
  $isAssoc = !empty($social) && array_keys($social) !== range(0, count($social) - 1);
  if ($isAssoc) {
      $social = collect($social)->map(fn($u,$p)=>['platform'=>$p,'url'=>$u])->values()->all();
  }

  $assetPath = function ($p) {
      if (!$p) return '';
      return Str::startsWith($p, ['/','http://','https://']) ? $p : global_asset($p);
  };

  $client = Auth::guard('store')->user();

  $accountUrl   = store_base_url('account');
  $ordersUrl    = store_base_url('account/orders');
  // Customer auth lives under the store base path normally, but under
  // /customer/* in root-domain mode so it never shadows the staff
  // /login and /logout — store_auth_url() knows which, store_base_url()
  // does not (it would emit the staff routes at the host root).
  $logoutUrl    = store_auth_url('logout');
  $loginUrl     = store_auth_url('login');
  $registerUrl  = store_auth_url('register');
  $electronicsOpts   = $skinTheme ? \App\Support\StoreThemes::options($skinTheme, $s) : [];
  $currencyOptions   = Route::has('store.currency.switch') ? \App\Services\StoreCurrencyService::options() : collect();
  // ===== Menus (admin-managed in Store → Menus; fall back to defaults when empty) =====
  $menusData = is_array($s->menus ?? null) ? $s->menus : [];
  $menuItems = function ($key) use ($menusData) {
      $items = $menusData[$key] ?? [];
      if (! is_array($items)) return [];
      return collect($items)
          ->filter(fn ($it) => is_array($it) && trim((string) ($it['label'] ?? '')) !== '')
          ->map(fn ($it) => [
              'label' => \App\Models\StoreSetting::menuItemLabel($it),
              'url' => \App\Models\StoreSetting::menuItemUrl($it),
          ])
          ->values()->all();
  };
  $headerMenu        = $menuItems('header');
  $footerShopMenu    = $menuItems('footer_shop');
  $footerSupportMenu = $menuItems('footer_support');
  // Skin themes hide categories with nothing to sell.
  if ($skinTheme && $categories->count()) {
      $visibleCatIds = \App\Models\Product::query()->where('is_active', 1)->where('hide_from_online_store', 0)->distinct()->pluck('category_id')->all();
      $categories = $categories->filter(fn ($c) => in_array($c->id, $visibleCatIds))->values();
  }

  $displayName = $client ? ($client->username ?: ($client->email ?? __('messages.Account'))) : '';
  $initial     = $client ? Str::upper(Str::substr($displayName, 0, 1)) : '';
  $avatar      = $client ? ($client->avatar_path ?? $client->avatar_url ?? null) : null;
  $avatarSrc   = $avatar
                  ? (Str::startsWith($avatar, ['http://','https://','/']) ? $avatar : global_asset($avatar))
                  : null;

  // Storefront bundle: `npm run build:storefront` (vite.storefront.config.js) -> public/js + public/css.
  $cssStore = global_asset('css/storefront.css') . '?v=' . @filemtime(public_path('css/storefront.css'));
  $jsStore  = global_asset('js/storefront.min.js') . '?v=' . @filemtime(public_path('js/storefront.min.js'));

  $rtlLocales = ['ar','he','fa','ur'];
  $isRtl = in_array(app()->getLocale(), $rtlLocales);

  $hexToRgbTriplet = function ($hex) {
      $hex = ltrim((string) $hex, '#');
      if (strlen($hex) === 3) $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
      if (strlen($hex) !== 6 || !ctype_xdigit($hex)) return '59 130 246';
      return hexdec(substr($hex,0,2)) . ' ' . hexdec(substr($hex,2,2)) . ' ' . hexdec(substr($hex,4,2));
  };
  $accent500Rgb = $hexToRgbTriplet($primary);

  $modalRegEnabled = $s->registration_enabled ?? true;
  $modalInviteRequired = $s->require_invite_code ?? false;
  $hidePrices = !$client && ($s->hide_prices_for_guests ?? false);

  // Open Graph image must be an absolute URL
  $ogImage = $assetPath($s->logo_path ?? null);
  if ($ogImage && !Str::startsWith($ogImage, ['http://','https://'])) {
      $ogImage = url($ogImage);
  }
@endphp
<!doctype html>
<html lang="{{ str_replace('_','-', app()->getLocale() ?? 'en') }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
<head>
  <meta charset="utf-8" />
  <title>{{ $title }}</title>
  <meta name="description" content="{{ $desc }}" />
  <link rel="canonical" href="{{ $canonical }}" />
  <meta property="og:type" content="{{ $ogType }}" />
  <meta property="og:site_name" content="{{ $s->store_name ?? $title }}" />
  <meta property="og:title" content="{{ $title }}" />
  <meta property="og:description" content="{{ $desc }}" />
  <meta property="og:url" content="{{ $canonical }}" />
  @if($ogImage)
  <meta property="og:image" content="{{ $ogImage }}" />
  @endif
  <meta name="twitter:card" content="{{ $ogImage ? 'summary_large_image' : 'summary' }}" />
  <meta name="twitter:title" content="{{ $title }}" />
  <meta name="twitter:description" content="{{ $desc }}" />
  @if($ogImage)
  <meta name="twitter:image" content="{{ $ogImage }}" />
  @endif
  @if($jsonLd)
  <script type="application/ld+json">{!! json_encode($jsonLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
  @endif
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  {{-- Multi-Currency: the active display currency. Cart/data-* prices stay in
       the base currency; storefront.js multiplies by currency-rate at render. --}}
  @php $activeCurrency = store_currency(); @endphp
  <meta name="currency" content="{{ $activeCurrency['symbol'] ?: ($s->currency_code ?? '$') }}">
  <meta name="currency-rate" content="{{ $activeCurrency['rate'] }}">
  <meta name="currency-code" content="{{ $activeCurrency['code'] }}">
  <script>window.__LOGGED_IN__ = @json(Auth::guard('store')->check());</script>
  <script>window.__ALLOW_OVERSELLING__ = @json($s->allow_overselling ?? true);</script>
  <script>window.__HIDE_PRICES__ = @json($hidePrices);</script>
  <script>window.__SHOW_STOCK__ = @json($s->show_stock ?? true);</script>
  <script>
    window.__MSG_ONLY_X_STOCK__ = @json(__('messages.Only_x_available_in_stock'));
    window.__MSG_MAX_ADDED__    = @json(__('messages.Max_stock_added_to_cart'));
    window.__MSG_ALREADY_MAX__  = @json(__('messages.Already_max_in_cart'));
    window.__MSG_ADDED__        = @json(__('messages.Added'));
  </script>

  {{-- Theme bootstrap — runs before paint to avoid FOUC --}}
  <script>
    // Light-first: the dark class only applies when the visitor chose it
    // (skin themes additionally require the theme toggle to be enabled).
    (function () {
      try {
        var stored = localStorage.getItem('store.theme');
        var allowDark = @json(! $isElectronics || (bool) ($electronicsOpts['show_theme_toggle'] ?? false));
        if (stored === 'dark' && allowDark) document.documentElement.classList.add('dark');
        else document.documentElement.classList.remove('dark');
      } catch (e) { document.documentElement.classList.remove('dark'); }
    })();
  </script>

  @if(!empty($s->favicon_path))
    <link rel="icon" href="{{ $assetPath($s->favicon_path) }}" />
  @endif

  {{-- PWA --}}
  <link rel="manifest" href="/pwa/store.webmanifest">
  <meta name="theme-color" content="{{ $primary }}">
  <meta name="mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="default">
  <meta name="apple-mobile-web-app-title" content="{{ $s->store_name ?? 'Store' }}">
  <link rel="apple-touch-icon" href="{{ pwa_icon_url(192) }}">

  {{-- Fonts — Space Grotesk (display) + Inter (body) + JetBrains Mono (specs/prices) --}}
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;600;700&family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@500;700&display=swap">

  {{-- Storefront bundle --}}
  <link rel="stylesheet" href="{{ $cssStore }}">
  <style>
    /* Star ratings on the shared product card (used by every theme). */
    .df-stars { display: inline-flex; align-items: center; gap: 4px; font-size: 12px; color: rgb(var(--color-fg-muted)); margin-top: 2px; }
    .df-stars-icons { display: inline-flex; gap: 1px; color: #f59e0b; }
    .df-stars-icons svg { width: 12px; height: 12px; }
    .df-stars-icons .is-empty { color: rgb(var(--color-border-strong)); }
    .df-stars b { color: rgb(var(--color-fg-primary)); font-weight: 600; }
    .product-badge-sale { background: #e11d48; color: #fff; }
  </style>

  <style>
    :root {
      --color-accent-500: {{ $accent500Rgb }};
      --color-accent-glow: rgba({{ $accent500Rgb }}, 0.35);
    }
    .dark { --color-accent-glow: rgba({{ $accent500Rgb }}, 0.45); }

    body { font-family: {{ $s->font_family ?? 'Inter, system-ui, sans-serif' }}; }

    .mega-panel { box-shadow: 0 24px 48px -12px rgba(0,0,0,0.5); }

    /* Storefront icons (the x-store.icon component -- do not write the tag in
       this comment, Blade compiles component tags inside CSS comments too).
       An svg element is inline by default, so it
       sits on the text baseline and reads a couple of pixels low next to a
       label; this puts it on the optical centre instead and keeps flex parents
       from squashing it. Shared by every theme that extends this layout. */
    .store-ico { display: inline-block; vertical-align: middle; flex: 0 0 auto; }
    /* Zero-specificity floor: an svg with no width or height resolves to the
       replaced-element default (300x150), so if a size utility is ever missing
       from the build the icon still comes out 20px. Any width utility or theme
       rule still wins over :where(). */
    :where(.store-ico) { width: 1.25rem; height: 1.25rem; }
    /* Arrows, chevrons and the send/login glyphs point the other way in Arabic,
       Hebrew, Farsi and Urdu. Circular arrows and the magnifier keep their
       shape, and brand marks are never mirrored -- see $directional. */
    [dir="rtl"] .store-ico-flip { transform: scaleX(-1); }

    {!! $s->custom_css ?? '' !!}
  </style>
  @if($isElectronics)
    @include('store.'.$skinTheme.'.partials.head')
  @elseif($storeTheme === 'default')
    @include('store.partials.theme-default-style')
  @endif
</head>
<body x-data class="bg-bg-base text-fg-primary antialiased min-h-screen flex flex-col">

  {{-- Page loader --}}
  <div id="page-loader" x-data="pageLoader()"
       class="fixed inset-0 z-[2000] flex items-center justify-center bg-bg-base transition-opacity duration-300">
    <div class="w-10 h-10 border-2 border-line-subtle border-t-accent-500 rounded-full animate-spin"></div>
  </div>

  @if($isElectronics)
    @include('store.'.$skinTheme.'.partials.header')
  @else
  {{-- Announcement bar --}}
  <div class="announce-bar text-xs">
    <div class="container flex items-center justify-center gap-2.5 h-9">
      <span class="font-semibold text-fg-primary truncate">{{ $s->topbar_text_left ?? __('messages.TopbarLeft') }}</span>
      <span class="hidden md:inline-block w-1 h-1 rounded-full bg-line-strong shrink-0"></span>
      <span class="hidden md:inline text-fg-secondary truncate">{{ $s->topbar_text_right ?? __('messages.TopbarRight') }}</span>
      <a href="{{ route('store.shop') }}" class="hidden sm:inline font-semibold text-accent-500 hover:text-accent-400 shrink-0">{{ __('messages.ShopNow') }}</a>
    </div>
  </div>

  {{-- Header --}}
  <header class="sticky top-0 z-40 bg-bg-base/85 backdrop-blur-md border-b border-line-subtle">
    <div class="container">
      <div class="flex items-center gap-3 h-16">

        {{-- Mobile menu trigger --}}
        <button type="button"
                class="lg:hidden btn btn-ghost btn-icon"
                @click="window.StoreUI.open('mobileCategorySidebar')"
                aria-label="{{ __('messages.Menu') }}">
          <x-store.icon name="menu" class="w-6 h-6" />
        </button>

        {{-- Logo --}}
        <a href="{{ route('store.index') }}" class="flex items-center gap-2.5 shrink-0">
          @if(!empty($s->logo_path))
            <img src="{{ $assetPath($s->logo_path) }}" alt="{{ $s->store_name ?? 'Store' }}"
                 class="h-9 max-w-[160px] object-contain">
          @else
            <span class="logo-mark">{{ Str::upper(Str::substr($s->store_name ?? __('messages.Store'), 0, 1)) }}</span>
            <span class="logo-text">{{ $s->store_name ?? __('messages.Store') }}</span>
          @endif
        </a>

        {{-- Desktop nav (admin-managed main menu, else defaults) --}}
        <nav class="hidden lg:flex items-center ms-2">
          @if(!empty($headerMenu))
            @foreach($headerMenu as $mi)
              <a href="{{ $mi['url'] }}"
                 class="px-3 h-10 inline-flex items-center text-sm font-medium text-fg-secondary hover:text-fg-primary rounded-md transition-colors">
                {{ $mi['label'] }}
              </a>
            @endforeach
          @else
            <a href="{{ route('store.index') }}"
               class="px-3 h-10 inline-flex items-center text-sm font-medium text-fg-secondary hover:text-fg-primary rounded-md transition-colors">
              {{ __('messages.Home') }}
            </a>

            <a href="{{ route('store.shop') }}"
               class="px-3 h-10 inline-flex items-center text-sm font-medium text-fg-secondary hover:text-fg-primary rounded-md transition-colors">
              {{ __('messages.Shop') }}
            </a>

            @if(Route::has('store.flash_sales') && store_flash_ids())
              <a href="{{ route('store.flash_sales') }}"
                 class="px-3 h-10 inline-flex items-center gap-1 text-sm font-medium text-accent-500 hover:text-fg-primary rounded-md transition-colors">
                <x-store.icon name="lightning" class="w-4 h-4" />{{ __('messages.FlashSale') }}
              </a>
            @endif

            <a href="{{ route('store.contact') }}"
               class="px-3 h-10 inline-flex items-center text-sm font-medium text-fg-secondary hover:text-fg-primary rounded-md transition-colors">
              {{ __('messages.Support') }}
            </a>
          @endif
        </nav>

        {{-- Search (desktop) --}}
        <div class="hidden md:flex flex-1 max-w-xl mx-4 relative"
             x-data="searchBox('{{ route('store.search.suggestions') }}')"
             @click.outside="results = []">
          <form action="{{ route('store.shop') }}" method="GET" class="w-full relative">
            <x-store.icon name="search"
                          class="absolute start-3 top-1/2 -translate-y-1/2 w-5 h-5 text-fg-muted" />
            <input type="text" name="q" id="store-search-input"
                   class="input h-11 rounded-xl bg-bg-surface ps-10 pe-10"
                   autocomplete="off"
                   placeholder="{{ __('messages.SearchProducts') }}"
                   value="{{ request('q') }}"
                   x-model="q"
                   @input.debounce.250ms="fetch">
            <span class="kbd absolute end-3 top-1/2 -translate-y-1/2 pointer-events-none">/</span>
            <div x-show="results.length" x-cloak
                 class="absolute top-full left-0 right-0 mt-1 bg-bg-elevated border border-line-subtle rounded-md shadow-lg overflow-hidden max-h-96 overflow-y-auto z-50">
              <template x-for="p in results" :key="p.id">
                <a :href="p.url" class="flex items-center gap-3 px-3 py-2 hover:bg-bg-muted transition-colors">
                  <img :src="p.image_url" :alt="p.name" class="w-10 h-10 rounded object-cover">
                  <div class="flex-1 min-w-0">
                    <div class="text-sm font-medium truncate" x-text="p.name"></div>
                    <div class="text-xs text-fg-muted" x-text="p.code || ''"></div>
                    <div class="text-sm font-mono text-accent-500"
                         x-text="window.__HIDE_PRICES__ ? '' : ('{{ $s->currency_code ?? '$' }}' + p.display_price)"></div>
                  </div>
                </a>
              </template>
            </div>
          </form>
        </div>

        {{-- Actions cluster --}}
        <div class="ms-auto flex items-center gap-2">

          {{-- Mobile search --}}
          <button type="button"
                  class="md:hidden btn btn-ghost btn-icon"
                  @click="window.StoreUI.open('mobileSearchOverlay')"
                  aria-label="{{ __('messages.Search') }}">
            <x-store.icon name="search" class="w-5 h-5" />
          </button>

          {{-- Theme toggle --}}
          <button type="button"
                  class="btn btn-secondary btn-icon"
                  onclick="window.StoreTheme && window.StoreTheme.toggle()"
                  aria-label="{{ __('messages.ToggleTheme') }}"
                  title="{{ __('messages.ToggleTheme') }}">
            <x-store.icon name="moon" class="w-5 h-5 inline dark:hidden" />
            <x-store.icon name="sun"  class="w-5 h-5 hidden dark:inline" />
          </button>

          {{-- Language --}}
          <div class="relative hidden md:block" x-data="dropdown()" @click.outside="close">
            <button type="button" class="btn btn-ghost h-10 px-3 text-sm font-medium" @click="toggle">
              <x-store.icon name="globe" class="w-4 h-4 me-1" />{{ strtoupper(app()->getLocale()) }}
              <x-store.icon name="chevron-down" class="w-3 h-3 ms-1" />
            </button>
            <div x-show="open" x-cloak x-transition
                 class="absolute end-0 mt-1 w-36 bg-bg-elevated border border-line-subtle rounded-md shadow-lg py-1 z-50">
              <a class="block px-3 py-2 text-sm text-fg-primary hover:bg-bg-muted" href="{{ route('lang.switch', 'en') }}">English</a>
              <a class="block px-3 py-2 text-sm text-fg-primary hover:bg-bg-muted" href="{{ route('lang.switch', 'fr') }}">Français</a>
              <a class="block px-3 py-2 text-sm text-fg-primary hover:bg-bg-muted" href="{{ route('lang.switch', 'ar') }}">العربية</a>
              <a class="block px-3 py-2 text-sm text-fg-primary hover:bg-bg-muted" href="{{ route('lang.switch', 'es') }}">Español</a>
            </div>
          </div>

          {{-- Multi-Currency switcher (only when the module offers a choice) --}}
          @php $currencyOptions = \App\Services\StoreCurrencyService::options(); @endphp
          @if($currencyOptions->count() > 1)
            <div class="relative hidden md:block" x-data="dropdown()" @click.outside="close">
              <button type="button" class="btn btn-ghost h-10 px-3 text-sm font-medium" @click="toggle">
                {{ $activeCurrency['code'] }}
                <x-store.icon name="chevron-down" class="w-3 h-3 ms-1" />
              </button>
              <div x-show="open" x-cloak x-transition
                   class="absolute end-0 mt-1 w-44 bg-bg-elevated border border-line-subtle rounded-md shadow-lg py-1 z-50">
                @foreach($currencyOptions as $co)
                  <a class="block px-3 py-2 text-sm text-fg-primary hover:bg-bg-muted {{ (int) $co->id === (int) ($activeCurrency['id'] ?? 0) ? 'font-semibold' : '' }}"
                     href="{{ route('store.currency.switch', $co->id) }}">
                    {{ $co->code }} — {{ $co->symbol }}
                  </a>
                @endforeach
              </div>
            </div>
          @endif

          {{-- Account --}}
          @if($client)
            <div class="relative hidden md:block" x-data="dropdown()" @click.outside="close">
              <button type="button" class="btn btn-ghost h-10 px-2" @click="toggle">
                @if($avatarSrc)
                  <img src="{{ $avatarSrc }}" alt="" class="w-7 h-7 rounded-full object-cover">
                @else
                  <span class="w-7 h-7 rounded-full bg-accent-500 text-white text-xs font-bold inline-flex items-center justify-center">{{ $initial }}</span>
                @endif
                <span class="text-sm font-medium ms-2 max-w-[9rem] truncate">{{ Str::limit($displayName, 14) }}</span>
                <x-store.icon name="chevron-down" class="w-3 h-3 ms-1" />
              </button>
              <div x-show="open" x-cloak x-transition
                   class="absolute end-0 mt-1 w-52 bg-bg-elevated border border-line-subtle rounded-md shadow-lg py-1 z-50">
                <a class="flex items-center gap-2 px-3 py-2 text-sm text-fg-primary hover:bg-bg-muted" href="{{ $accountUrl }}">
                  <x-store.icon name="user" class="w-4 h-4" />{{ __('messages.Profile') }}
                </a>
                <a class="flex items-center gap-2 px-3 py-2 text-sm text-fg-primary hover:bg-bg-muted" href="{{ $ordersUrl }}">
                  <x-store.icon name="package" class="w-4 h-4" />{{ __('messages.Orders') }}
                </a>
                <a class="flex items-center gap-2 px-3 py-2 text-sm text-fg-primary hover:bg-bg-muted" href="{{ route('account.returns') }}">
                  <x-store.icon name="rotate-ccw" class="w-4 h-4" />{{ __('messages.MyReturns') }}
                </a>
                <div class="border-t border-line-subtle my-1"></div>
                <form method="POST" action="{{ $logoutUrl }}">
                  @csrf
                  <button type="submit" class="flex items-center gap-2 w-full text-start px-3 py-2 text-sm text-danger hover:bg-bg-muted">
                    <x-store.icon name="log-out" class="w-4 h-4" />{{ __('messages.Logout') }}
                  </button>
                </form>
              </div>
            </div>
          @else
            <a href="{{ $loginUrl }}" class="hidden md:inline-flex btn btn-secondary btn-sm">
              <x-store.icon name="user" class="w-4 h-4" />{{ __('messages.SignIn') }}
            </a>
          @endif

          {{-- Cart --}}
          <button type="button"
                  class="btn btn-primary h-10 px-4 relative"
                  @click="window.StoreUI.open('miniCart')"
                  aria-label="{{ __('messages.Cart') }}">
            <x-store.icon name="cart" class="w-5 h-5" />
            <span class="hidden sm:inline ms-1">{{ __('messages.Cart') }}</span>
            <span class="cart-count inline-flex items-center justify-center min-w-5 h-5 px-1.5 rounded-full bg-white text-accent-500 text-[11px] font-bold ms-1">0</span>
          </button>
        </div>
      </div>
    </div>

    {{-- Horizontal category bar (home & shop only) --}}
    @if(!empty($showCategoryBar) && $categories->count())
      <div class="hidden lg:block border-t border-line-subtle bg-bg-base">
        <div class="container">
          <ul class="flex flex-wrap items-center gap-1 py-2">
            @php $barCats = $categories->take(9); $moreCats = $categories->slice(9); @endphp
            @foreach($barCats as $category)
              <li class="relative group shrink-0">
                <a href="{{ route('store.shop', ['category' => $category->id]) }}"
                   class="px-3 h-9 inline-flex items-center gap-1 text-sm rounded-md transition-colors {{ request('category') == $category->id ? 'text-accent-500 bg-accent-500/10' : 'text-fg-secondary hover:text-fg-primary hover:bg-bg-muted' }}">
                  {{ $category->name }}
                  @if(($category->subcategories ?? collect())->count())
                    <x-store.icon name="chevron-down" class="w-3 h-3" />
                  @endif
                </a>
                @if(($category->subcategories ?? collect())->count())
                  <div class="hidden group-hover:block absolute top-full start-0 pt-1 z-30">
                    <div class="min-w-[14rem] bg-bg-elevated border border-line-subtle rounded-md shadow-lg py-1">
                      @foreach($category->subcategories as $sub)
                        <a href="{{ route('store.shop', ['category' => $category->id, 'sub_category' => $sub->id]) }}"
                           class="block px-3 py-2 text-sm text-fg-primary hover:bg-bg-muted hover:text-accent-500">
                          {{ $sub->name }}
                        </a>
                      @endforeach
                    </div>
                  </div>
                @endif
              </li>
            @endforeach
            @if($moreCats->count())
              <li class="df-more shrink-0">
                <a href="{{ route('store.shop') }}" class="px-3 h-9 inline-flex items-center gap-1 text-sm rounded-md text-fg-secondary hover:text-fg-primary hover:bg-bg-muted">
                  {{ __('messages.More') }}<x-store.icon name="chevron-down" class="w-3 h-3" />
                </a>
                <div class="df-more-menu">
                  @foreach($moreCats as $category)
                    <a href="{{ route('store.shop', ['category' => $category->id]) }}">{{ $category->name }}</a>
                  @endforeach
                </div>
              </li>
            @endif
          </ul>
        </div>
      </div>
    @endif
  </header>
  @endif

  {{-- Mobile Category Sidebar (Alpine drawer) --}}
  <div id="mobileCategorySidebar" x-data="drawer({ side: 'start' })" @keydown.window="onEsc">
    <template x-teleport="body">
      <div x-show="isOpen" x-cloak class="fixed inset-0 z-[60]">
        <div x-show="isOpen" x-transition.opacity
             class="drawer-backdrop" @click="close"></div>
        <aside x-show="isOpen"
               x-transition:enter="transition ease-smooth duration-base"
               x-transition:enter-start="-translate-x-full rtl:translate-x-full"
               x-transition:enter-end="translate-x-0"
               x-transition:leave="transition ease-smooth duration-fast"
               x-transition:leave-start="translate-x-0"
               x-transition:leave-end="-translate-x-full rtl:translate-x-full"
               class="drawer-panel drawer-start">
          <div class="drawer-header">
            <h5 class="font-semibold">{{ __('messages.Categories') }}</h5>
            <button type="button" class="btn btn-ghost btn-icon" @click="close" aria-label="{{ __('messages.Close') }}">
              <x-store.icon name="x" class="w-5 h-5" />
            </button>
          </div>

          <div class="p-4 border-b border-line-subtle">
            <div class="relative" x-data="searchBox('{{ route('store.search.suggestions') }}')"
                 @click.outside="results = []">
              <form action="{{ route('store.shop') }}" method="GET" class="relative">
                <x-store.icon name="search" class="absolute start-3 top-1/2 -translate-y-1/2 w-5 h-5 text-fg-muted" />
                <input type="text" name="q" class="input ps-10"
                       placeholder="{{ __('messages.SearchProducts') }}"
                       autocomplete="off"
                       x-model="q"
                       @input.debounce.250ms="fetch">
                <div x-show="results.length" x-cloak
                     class="absolute top-full left-0 right-0 mt-1 bg-bg-elevated border border-line-subtle rounded-md shadow-lg overflow-hidden max-h-96 overflow-y-auto z-50">
                  <template x-for="p in results" :key="p.id">
                    <a :href="p.url" class="flex items-center gap-3 px-3 py-2 hover:bg-bg-muted transition-colors">
                      <img :src="p.image_url" :alt="p.name" class="w-10 h-10 rounded object-cover">
                      <div class="flex-1 min-w-0">
                        <div class="text-sm font-medium truncate" x-text="p.name"></div>
                        <div class="text-sm font-mono text-accent-500"
                             x-text="window.__HIDE_PRICES__ ? '' : ('{{ $s->currency_code ?? '$' }}' + p.display_price)"></div>
                      </div>
                    </a>
                  </template>
                </div>
              </form>
            </div>
          </div>

          <div class="drawer-body p-0">
            <ul class="divide-y divide-line-subtle" x-data="sidebarMenu()">
              @foreach($categories as $category)
                <li>
                  <div class="flex items-center">
                    <a href="{{ route('store.shop', ['category' => $category->id]) }}"
                       class="flex-1 px-4 py-3 text-sm font-medium text-fg-primary hover:bg-bg-muted">
                      {{ $category->name }}
                    </a>
                    @if(($category->subcategories ?? collect())->count())
                      <button type="button" class="px-4 py-3 text-fg-muted"
                              @click="toggle({{ $category->id }})">
                        <x-store.icon name="plus" class="w-4 h-4" x-show="!is({{ $category->id }})" />
                        <x-store.icon name="minus" class="w-4 h-4" x-show="is({{ $category->id }})" x-cloak />
                      </button>
                    @endif
                  </div>
                  @if(($category->subcategories ?? collect())->count())
                    <ul x-show="is({{ $category->id }})" x-cloak x-collapse class="bg-bg-muted">
                      @foreach($category->subcategories as $sub)
                        <li>
                          <a href="{{ route('store.shop', ['category' => $category->id, 'sub_category' => $sub->id]) }}"
                             class="block ps-8 pe-4 py-2 text-sm text-fg-secondary hover:text-accent-500">
                            {{ $sub->name }}
                          </a>
                        </li>
                      @endforeach
                    </ul>
                  @endif
                </li>
              @endforeach
            </ul>
          </div>
        </aside>
      </div>
    </template>
  </div>

  {{-- Mobile search overlay (Alpine dialog) --}}
  <div id="mobileSearchOverlay" x-data="dialog()" @keydown.window="onEsc">
    <template x-teleport="body">
      <div x-show="isOpen" x-cloak x-transition.opacity class="fixed inset-0 z-[60] bg-bg-base md:hidden">
        <div class="container pt-4">
          <div class="flex items-center gap-2"
               x-data="searchBox('{{ route('store.search.suggestions') }}')"
               @click.outside="results = []">
            <form action="{{ route('store.shop') }}" method="GET" class="flex-1 relative">
              <x-store.icon name="search" class="absolute start-3 top-1/2 -translate-y-1/2 w-5 h-5 text-fg-muted" />
              <input type="text" name="q" class="input ps-10"
                     autocomplete="off"
                     placeholder="{{ __('messages.SearchProducts') }}"
                     x-model="q"
                     @input.debounce.250ms="fetch"
                     x-ref="field">
              <div x-show="results.length" x-cloak
                   class="absolute top-full left-0 right-0 mt-1 bg-bg-elevated border border-line-subtle rounded-md shadow-lg overflow-hidden max-h-[70vh] overflow-y-auto z-50">
                <template x-for="p in results" :key="p.id">
                  <a :href="p.url" class="flex items-center gap-3 px-3 py-2 hover:bg-bg-muted transition-colors">
                    <img :src="p.image_url" :alt="p.name" class="w-10 h-10 rounded object-cover">
                    <div class="flex-1 min-w-0">
                      <div class="text-sm font-medium truncate" x-text="p.name"></div>
                      <div class="text-sm font-mono text-accent-500"
                           x-text="window.__HIDE_PRICES__ ? '' : ('{{ $s->currency_code ?? '$' }}' + p.display_price)"></div>
                    </div>
                  </a>
                </template>
              </div>
            </form>
            <button type="button" class="btn btn-ghost h-10 px-3" @click="$root.dispatchEvent(new CustomEvent('ui:close'))">
              {{ __('messages.Cancel') }}
            </button>
          </div>
        </div>
      </div>
    </template>
  </div>

  {{-- Page content --}}
  <main class="flex-1 pb-20 lg:pb-0">
    @yield('content')
  </main>

  {{-- Mobile bottom tab bar --}}
  <nav class="lg:hidden fixed bottom-0 inset-x-0 z-30 bg-bg-base/95 backdrop-blur-md border-t border-line-subtle pb-[env(safe-area-inset-bottom)]">
    <div class="grid grid-cols-5 h-16">
      @php
        $isHome = request()->routeIs('store.index');
        $isShop = request()->routeIs('store.shop');
        $isAcct = request()->routeIs('store.account*') || str_contains(url()->current(), '/'.store_path_to('account'));
      @endphp
      <a href="{{ route('store.index') }}"
         class="flex flex-col items-center justify-center gap-0.5 {{ $isHome ? 'text-accent-500' : 'text-fg-secondary' }}">
        <x-store.icon name="home" class="w-5 h-5" />
        <span class="text-[10px] font-medium">{{ __('messages.Home') }}</span>
      </a>
      <a href="{{ route('store.shop') }}"
         class="flex flex-col items-center justify-center gap-0.5 {{ $isShop ? 'text-accent-500' : 'text-fg-secondary' }}">
        <x-store.icon name="grid" class="w-5 h-5" />
        <span class="text-[10px] font-medium">{{ __('messages.Shop') }}</span>
      </a>
      <button type="button"
              class="flex flex-col items-center justify-center gap-0.5 text-fg-secondary"
              @click="window.StoreUI.open('mobileSearchOverlay')">
        <x-store.icon name="search" class="w-5 h-5" />
        <span class="text-[10px] font-medium">{{ __('messages.Search') }}</span>
      </button>
      <button type="button"
              class="relative flex flex-col items-center justify-center gap-0.5 text-fg-secondary"
              @click="window.StoreUI.open('miniCart')">
        <x-store.icon name="cart" class="w-5 h-5" />
        <span class="cart-count absolute top-1 end-4 min-w-4 h-4 px-1 rounded-full bg-danger text-white text-[10px] font-bold inline-flex items-center justify-center">0</span>
        <span class="text-[10px] font-medium">{{ __('messages.Cart') }}</span>
      </button>
      @if($client)
        <a href="{{ $accountUrl }}"
           class="flex flex-col items-center justify-center gap-0.5 {{ $isAcct ? 'text-accent-500' : 'text-fg-secondary' }}">
          <x-store.icon name="user" class="w-5 h-5" />
          <span class="text-[10px] font-medium">{{ __('messages.Account') }}</span>
        </a>
      @else
        <a href="{{ $loginUrl }}" class="flex flex-col items-center justify-center gap-0.5 text-fg-secondary">
          <x-store.icon name="user" class="w-5 h-5" />
          <span class="text-[10px] font-medium">{{ __('messages.SignIn') }}</span>
        </a>
      @endif
    </div>
  </nav>

  {{-- Footer --}}
  @if($isElectronics)
    @include('store.'.$skinTheme.'.partials.footer')
  @else
  <footer class="store-footer mt-16 bg-bg-surface border-t border-line-subtle">
    <div class="container py-10">
      <div class="grid grid-cols-2 md:grid-cols-4 gap-8">
        <style>@media (min-width: 768px) { .store-footer .grid { grid-template-columns: 1.5fr 1fr 1fr 1.2fr !important; } }</style>
        <div class="col-span-2 md:col-span-1">
          <div class="flex items-center gap-2 mb-3">
            @if(!empty($s->logo_path))
              <img src="{{ $assetPath($s->logo_path) }}" alt="" class="h-8 max-w-[140px] object-contain">
            @else
              <span class="font-bold text-lg tracking-tight">{{ $s->store_name ?? __('messages.Store') }}</span>
            @endif
          </div>
          <p class="text-sm text-fg-secondary max-w-md">{{ $s->footer_text ?? __('messages.FooterAbout') }}</p>

          @if(!empty($social))
            <div class="flex items-center gap-2 mt-5">
              @foreach($social as $item)
                @php
                  $platform = is_array($item) ? ($item['platform'] ?? '') : (is_string($item) ? $item : '');
                  $url      = is_array($item) ? ($item['url'] ?? '#') : '#';
                  $iconName = strtolower(trim($platform));
                  if ($iconName === 'x') $iconName = 'twitter-x';
                @endphp
                @if($platform && $url)
                  <a href="{{ $url }}" target="_blank" rel="noopener"
                     aria-label="{{ ucfirst($platform) }}"
                     class="btn btn-secondary btn-icon">
                    <x-store.icon :name="$iconName" class="w-4 h-4" />
                  </a>
                @endif
              @endforeach
            </div>
          @endif
        </div>

        <div>
          <h6 class="text-xs font-semibold uppercase tracking-widest text-fg-muted mb-3">{{ __('messages.Shop') }}</h6>
          <ul class="space-y-2 text-sm">
            @if(!empty($footerShopMenu))
              @foreach($footerShopMenu as $mi)
                <li><a href="{{ $mi['url'] }}" class="text-fg-secondary hover:text-accent-500">{{ $mi['label'] }}</a></li>
              @endforeach
            @else
              <li><a href="{{ route('store.shop') }}" class="text-fg-secondary hover:text-accent-500">{{ __('messages.AllProducts') }}</a></li>
              <li><a href="{{ route('store.shop', ['deals' => 1]) }}" class="text-fg-secondary hover:text-accent-500">{{ __('messages.Deals') }}</a></li>
              @if(Route::has('store.flash_sales') && store_flash_ids())
                <li><a href="{{ route('store.flash_sales') }}" class="text-fg-secondary hover:text-accent-500">{{ __('messages.FlashSale') }}</a></li>
              @endif
            @endif
          </ul>
        </div>

        <div>
          <h6 class="text-xs font-semibold uppercase tracking-widest text-fg-muted mb-3">{{ __('messages.Support') }}</h6>
          <ul class="space-y-2 text-sm">
            @if(!empty($footerSupportMenu))
              @foreach($footerSupportMenu as $mi)
                <li><a href="{{ $mi['url'] }}" class="text-fg-secondary hover:text-accent-500">{{ $mi['label'] }}</a></li>
              @endforeach
            @else
              <li><a href="{{ route('store.contact') }}" class="text-fg-secondary hover:text-accent-500">{{ __('messages.ContactUs') }}</a></li>
              @if($client)
                <li><a href="{{ $ordersUrl }}" class="text-fg-secondary hover:text-accent-500">{{ __('messages.Orders') }}</a></li>
              @endif
              <li><a href="{{ $client ? route('account.returns') : $loginUrl }}" class="text-fg-secondary hover:text-accent-500">{{ __('messages.ReturnsRefunds') }}</a></li>
              <li><a href="{{ $client ? $ordersUrl : $loginUrl }}" class="text-fg-secondary hover:text-accent-500">{{ __('messages.TrackOrder') }}</a></li>
            @endif
          </ul>
        </div>

        <div class="col-span-2 md:col-span-1">
          <h6 class="text-xs font-semibold uppercase tracking-widest text-fg-muted mb-3">{{ __('messages.GetInTouch') }}</h6>
          <ul class="space-y-2 text-sm text-fg-secondary df-foot-contact">
            @if($s->contact_phone)<li><x-store.icon name="phone" /><a href="tel:{{ preg_replace('/\s+/', '', $s->contact_phone) }}">{{ $s->contact_phone }}</a></li>@endif
            @if($s->contact_email)<li><x-store.icon name="mail" /><a href="mailto:{{ $s->contact_email }}">{{ $s->contact_email }}</a></li>@endif
            @if($s->contact_address)<li><x-store.icon name="map-pin" /><span>{{ $s->contact_address }}</span></li>@endif
          </ul>
          <div class="df-pay mt-4" aria-label="{{ __('messages.SecurePayment') }}">
            <span>VISA</span>
            <span class="df-pay-mc"><i></i><i></i></span>
            <span class="df-pay-pp">PayPal</span>
            <span class="df-pay-ap"> Pay</span>
            <span class="df-pay-gp"><b>G</b>&nbsp;Pay</span>
          </div>
        </div>
      </div>

      <div class="mt-10 pt-6 border-t border-line-subtle flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-fg-muted">
        <span>© {{ date('Y') }} {{ $s->store_name ?? __('messages.Store') }}. {{ __('messages.AllRightsReserved') }}</span>
        <div class="flex items-center gap-3">
          <x-store.icon name="shield-check" class="w-4 h-4 text-success" />{{ __('messages.SecurePayment') }}
          <span class="text-line-subtle">•</span>
          <x-store.icon name="truck" class="w-4 h-4 text-info" />{{ __('messages.FastShipping') }}
        </div>
      </div>
    </div>
  </footer>
  @endif

  {{-- Mini Cart (Alpine drawer + miniCart renderer) --}}
  <div id="miniCart" x-data="drawer({ side: 'end' })" @keydown.window="onEsc">
    <template x-teleport="body">
      <div x-show="isOpen" x-cloak class="fixed inset-0 z-[60]">
        <div x-show="isOpen" x-transition.opacity class="drawer-backdrop" @click="close"></div>
        <aside x-show="isOpen"
               x-transition:enter="transition ease-smooth duration-base"
               x-transition:enter-start="translate-x-full rtl:-translate-x-full"
               x-transition:enter-end="translate-x-0"
               x-transition:leave="transition ease-smooth duration-fast"
               x-transition:leave-start="translate-x-0"
               x-transition:leave-end="translate-x-full rtl:-translate-x-full"
               class="drawer-panel drawer-end"
               x-data="miniCart()">
          <div class="drawer-header">
            <h5 class="font-semibold flex items-center gap-2">
              <x-store.icon name="cart" class="w-5 h-5" />{{ __('messages.YourCart') }}
            </h5>
            <button type="button" class="btn btn-ghost btn-icon" @click="close" aria-label="{{ __('messages.Close') }}">
              <x-store.icon name="x" class="w-5 h-5" />
            </button>
          </div>

          <div class="drawer-body">
            <template x-if="!items.length">
              <div class="empty-state">
                <x-store.icon name="bag" class="w-12 h-12 opacity-50" />
                <div class="mt-3 text-sm text-fg-muted">{{ __('messages.YourCartEmpty') }}</div>
              </div>
            </template>

            <div id="mc-list" class="divide-y divide-line-subtle">
              <template x-for="it in items" :key="it.id">
                <div class="flex items-center gap-3.5 py-4" :data-id="it.id">
                  <img :src="it.image || '{{ global_asset(upload_path('products') . '/no-image.png') }}'" :alt="it.name"
                       class="w-[72px] h-[72px] rounded-lg object-cover border border-line-subtle shrink-0">
                  <div class="flex-1 min-w-0">
                    <div class="text-sm font-semibold truncate" x-text="it.name"></div>
                    <div class="text-xs text-fg-muted font-mono" x-text="hidePrices ? '' : money(it.price)"></div>
                    <div class="qty-stepper mt-1.5">
                      <button type="button" @click="dec(it)"><x-store.icon name="minus" class="w-3 h-3" /></button>
                      <input type="number" class="text-center" :value="it.qty" min="1"
                             :max="maxFor(it)"
                             @change="setQty(it, $event.target.value)">
                      <button type="button" @click="inc(it)"><x-store.icon name="plus" class="w-3 h-3" /></button>
                      <span class="ms-auto text-sm font-semibold font-mono" x-text="lineTotal(it)"></span>
                      <button type="button" class="p-1 rounded hover:bg-bg-muted" @click="remove(it)" aria-label="{{ __('messages.Remove') }}">
                        <x-store.icon name="trash" class="w-4 h-4 text-danger" />
                      </button>
                    </div>
                  </div>
                </div>
              </template>
            </div>
          </div>

          <div class="drawer-footer">
            <div class="flex justify-between text-sm text-fg-secondary">
              <span>{{ __('messages.Subtotal') }}</span>
              <strong id="mc-subtotal" class="font-mono text-fg-primary" x-text="money(subtotal)">$0.00</strong>
            </div>
            <div class="flex justify-between mt-2">
              <span class="font-semibold">{{ __('messages.GrandTotal') }}</span>
              <strong id="mc-grand" class="font-mono text-lg text-fg-primary" x-text="money(grand)">$0.00</strong>
            </div>
            <div class="flex gap-2 mt-4">
              <button id="mc-clear" type="button" class="btn btn-secondary flex-1" @click="clear">{{ __('messages.Clear') }}</button>
              <button id="mc-checkout" type="button" class="btn btn-primary flex-1"
                      @click="checkout('{{ route('checkout') }}')">
                {{ __('messages.Checkout') }}
                <x-store.icon name="arrow-right" class="w-4 h-4" />
              </button>
            </div>
          </div>
        </aside>
      </div>
    </template>
  </div>

  {{-- Auth dialog (Alpine) --}}
  <div id="authModal" x-data="dialog()" @keydown.window="onEsc">
    <template x-teleport="body">
      <div x-show="isOpen" x-cloak class="fixed inset-0 z-[70] overflow-y-auto overscroll-contain">
        <div x-show="isOpen" x-transition.opacity class="fixed inset-0 bg-black/60 backdrop-blur-sm" @click="close"></div>
        <div class="relative min-h-full flex items-center justify-center p-4">
        <div x-show="isOpen"
             x-transition:enter="transition ease-smooth duration-base"
             x-transition:enter-start="opacity-0 translate-y-6 scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 scale-100"
             class="auth-panel" x-ref="panel" role="dialog" aria-modal="true"
             x-data="tabs('login')">

          {{-- Close button floating --}}
          <button type="button"
                  class="auth-close"
                  @click="close"
                  aria-label="{{ __('messages.Close') }}">
            <x-store.icon name="x" class="w-5 h-5" />
          </button>

          {{-- Hero strip --}}
          <div class="auth-hero">
            <div class="auth-hero-badge">
              <x-store.icon name="shield-check" class="w-6 h-6" />
            </div>
            <h3 class="auth-hero-title" x-text="is('login')
              ? @js(__('messages.WelcomeBack'))
              : @js(__('messages.JoinUs'))"></h3>
            <p class="auth-hero-sub" x-text="is('login')
              ? @js(__('messages.SignInContinueCheckout'))
              : @js(__('messages.CreateStoreAccountFaster'))"></p>
          </div>

          {{-- Tabs --}}
          @if($modalRegEnabled)
            <div class="auth-tabs" role="tablist">
              <button type="button" class="auth-tab" :class="is('login') && 'is-active'"
                      role="tab" :aria-selected="is('login')" @click="set('login')">
                <x-store.icon name="log-in" class="w-4 h-4" />
                <span>{{ __('messages.SignIn') }}</span>
              </button>
              <button type="button" class="auth-tab" :class="is('register') && 'is-active'"
                      role="tab" :aria-selected="is('register')" @click="set('register')">
                <x-store.icon name="user-plus" class="w-4 h-4" />
                <span>{{ __('messages.Register') }}</span>
              </button>
              <span class="auth-tab-indicator" :class="is('register') && 'right'"></span>
            </div>
          @endif

          {{-- Login --}}
          <div x-show="is('login')" class="auth-body">
            <form method="POST" action="{{ route('store.login') }}" class="space-y-4">
              @csrf
              <input type="hidden" name="redirect" value="{{ route('checkout') }}">

              <div class="auth-field">
                <label class="auth-label">{{ __('messages.Email') }}</label>
                <div class="auth-input-wrap">
                  <x-store.icon name="mail" class="auth-input-icon w-4 h-4" />
                  <input type="email" name="email" class="auth-input" required
                         autocomplete="email" placeholder="you@example.com">
                </div>
              </div>

              <div class="auth-field" x-data="{ show: false }">
                <label class="auth-label">{{ __('messages.Password') }}</label>
                <div class="auth-input-wrap">
                  <x-store.icon name="shield-check" class="auth-input-icon w-4 h-4" />
                  <input :type="show ? 'text' : 'password'" name="password" class="auth-input auth-input-pw"
                         required autocomplete="current-password" placeholder="••••••••">
                  <button type="button" class="auth-eye" @click="show = !show"
                          :aria-label="show ? 'Hide password' : 'Show password'" tabindex="-1">
                    <svg x-show="!show" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                         fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                         stroke-linejoin="round" class="w-4 h-4">
                      <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/>
                    </svg>
                    <svg x-show="show" x-cloak xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                         fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                         stroke-linejoin="round" class="w-4 h-4">
                      <path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"/>
                      <path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"/>
                      <path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"/>
                      <line x1="2" x2="22" y1="2" y2="22"/>
                    </svg>
                  </button>
                </div>
              </div>

              <div class="flex items-center justify-between text-sm">
                <label class="flex items-center gap-2 cursor-pointer select-none">
                  <input class="checkbox" type="checkbox" name="remember">
                  <span class="text-fg-secondary">{{ __('messages.RememberMe') }}</span>
                </label>
              </div>

              <button type="submit" class="btn btn-primary btn-block btn-lg auth-submit">
                <x-store.icon name="log-in" class="w-5 h-5" />
                <span>{{ __('messages.SignIn') }}</span>
              </button>
            </form>
          </div>

          {{-- Register --}}
          @if($modalRegEnabled)
            <div x-show="is('register')" x-cloak class="auth-body">
              <form method="POST" action="{{ route('store.register') }}" class="space-y-4">
                @csrf
                <input type="hidden" name="redirect" value="{{ route('checkout') }}">

                <div class="auth-field">
                  <label class="auth-label">{{ __('messages.FullName') }}</label>
                  <div class="auth-input-wrap">
                    <x-store.icon name="user" class="auth-input-icon w-4 h-4" />
                    <input type="text" name="name" class="auth-input" required autocomplete="name">
                  </div>
                </div>

                <div class="auth-field">
                  <label class="auth-label">{{ __('messages.Email') }}</label>
                  <div class="auth-input-wrap">
                    <x-store.icon name="mail" class="auth-input-icon w-4 h-4" />
                    <input type="email" name="email" class="auth-input" required autocomplete="email">
                  </div>
                </div>

                <div class="grid md:grid-cols-2 gap-3">
                  <div class="auth-field">
                    <label class="auth-label">{{ __('messages.Phone') }}</label>
                    <div class="auth-input-wrap">
                      <x-store.icon name="phone" class="auth-input-icon w-4 h-4" />
                      <input type="tel" name="phone" class="auth-input" required autocomplete="tel">
                    </div>
                  </div>
                  <div class="auth-field">
                    <label class="auth-label">{{ __('messages.Address') }}</label>
                    <div class="auth-input-wrap">
                      <x-store.icon name="map-pin" class="auth-input-icon w-4 h-4" />
                      <input type="text" name="address" class="auth-input" required autocomplete="street-address">
                    </div>
                  </div>
                </div>

                @if($modalInviteRequired)
                  <div class="auth-field">
                    <label class="auth-label">{{ __('messages.InviteCode') }}</label>
                    <div class="auth-input-wrap">
                      <x-store.icon name="ticket" class="auth-input-icon w-4 h-4" />
                      <input type="text" name="invite_code" class="auth-input" required
                             placeholder="{{ __('messages.EnterInviteCode') }}">
                    </div>
                    <div class="text-xs text-fg-muted mt-1">{{ __('messages.InviteCodeRequired') }}</div>
                  </div>
                @endif

                <div class="grid md:grid-cols-2 gap-3">
                  <div class="auth-field" x-data="{ show: false }">
                    <label class="auth-label">{{ __('messages.Password') }}</label>
                    <div class="auth-input-wrap">
                      <x-store.icon name="shield-check" class="auth-input-icon w-4 h-4" />
                      <input :type="show ? 'text' : 'password'" name="password"
                             class="auth-input auth-input-pw" required autocomplete="new-password">
                      <button type="button" class="auth-eye" @click="show = !show" tabindex="-1"
                              :aria-label="show ? 'Hide password' : 'Show password'">
                        <svg x-show="!show" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                             fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                             stroke-linejoin="round" class="w-4 h-4">
                          <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/>
                        </svg>
                        <svg x-show="show" x-cloak xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                             fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                             stroke-linejoin="round" class="w-4 h-4">
                          <path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"/>
                          <path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"/>
                          <path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"/>
                          <line x1="2" x2="22" y1="2" y2="22"/>
                        </svg>
                      </button>
                    </div>
                  </div>
                  <div class="auth-field">
                    <label class="auth-label">{{ __('messages.ConfirmPassword') }}</label>
                    <div class="auth-input-wrap">
                      <x-store.icon name="shield-check" class="auth-input-icon w-4 h-4" />
                      <input type="password" name="password_confirmation" class="auth-input" required
                             autocomplete="new-password">
                    </div>
                  </div>
                </div>

                <button type="submit" class="btn btn-primary btn-block btn-lg auth-submit">
                  <x-store.icon name="user-plus" class="w-5 h-5" />
                  <span>{{ __('messages.CreateAccount') }}</span>
                </button>
              </form>
            </div>
          @endif

          <div class="auth-foot">
            <span class="inline-flex items-center gap-1.5 text-xs text-fg-muted">
              <x-store.icon name="shield-check" class="w-3.5 h-3.5 text-success" />
              {{ __('messages.SecurePayment') }}
            </span>
          </div>
        </div>
        </div>
      </div>
    </template>
  </div>

  {{-- Stock toast --}}
  <div id="store-stock-toast"
       class="hidden fixed bottom-20 lg:bottom-6 left-1/2 -translate-x-1/2 z-[9999] max-w-[90%] bg-warning/15 border border-warning/40 text-warning px-4 py-3 rounded-md shadow-md text-sm pointer-events-none transition-opacity duration-300"
       role="alert"></div>

  {{-- Modal alert / confirm — window.StoreDialog replaces native alert()/confirm() --}}
  <style>
    .sd-root { position: fixed; inset: 0; z-index: 9998; opacity: 0; visibility: hidden; transition: opacity .2s ease, visibility .2s ease; }
    .sd-root.is-open { opacity: 1; visibility: visible; }
    .sd-backdrop { position: absolute; inset: 0; background: rgba(0, 0, 0, .6); backdrop-filter: blur(4px); }
    .sd-wrap { position: relative; min-height: 100%; display: flex; align-items: center; justify-content: center; padding: 1rem; }
    .sd-panel { width: 100%; max-width: 24rem; background: rgb(var(--color-bg-surface)); color: rgb(var(--color-fg-primary)); border: 1px solid rgb(var(--color-border-subtle)); border-radius: 1rem; box-shadow: 0 20px 50px rgba(0, 0, 0, .25); padding: 1.5rem; text-align: center; transform: translateY(12px) scale(.96); transition: transform .2s ease; }
    .sd-root.is-open .sd-panel { transform: none; }
    .sd-icon { width: 3rem; height: 3rem; border-radius: 9999px; margin: 0 auto 1rem; display: flex; align-items: center; justify-content: center; }
    .sd-icon svg { width: 1.5rem; height: 1.5rem; }
    .sd-icon.info    { background: rgb(var(--color-info) / .15);    color: rgb(var(--color-info)); }
    .sd-icon.success { background: rgb(var(--color-success) / .15); color: rgb(var(--color-success)); }
    .sd-icon.warning { background: rgb(var(--color-warning) / .15); color: rgb(var(--color-warning)); }
    .sd-icon.danger  { background: rgb(var(--color-danger) / .15);  color: rgb(var(--color-danger)); }
    .sd-title { font-size: 1.125rem; font-weight: 600; line-height: 1.4; }
    .sd-message { margin-top: .5rem; font-size: .875rem; line-height: 1.5; color: rgb(var(--color-fg-muted)); white-space: pre-line; overflow-wrap: anywhere; }
    .sd-actions { margin-top: 1.5rem; display: flex; gap: .75rem; justify-content: center; }
    .sd-actions .btn { min-width: 6.5rem; }
  </style>
  <div id="store-dialog" class="sd-root" role="dialog" aria-modal="true" aria-labelledby="store-dialog-title" aria-describedby="store-dialog-message">
    <div class="sd-backdrop" data-sd-backdrop></div>
    <div class="sd-wrap">
      <div class="sd-panel">
        <div class="sd-icon info" data-sd-icon></div>
        <h3 id="store-dialog-title" class="sd-title" data-sd-title></h3>
        <p id="store-dialog-message" class="sd-message" data-sd-message></p>
        <div class="sd-actions">
          <button type="button" class="btn btn-outline" data-sd-cancel>{{ __('messages.Cancel') }}</button>
          <button type="button" class="btn btn-primary" data-sd-ok>{{ __('messages.Close') }}</button>
        </div>
      </div>
    </div>
  </div>
  <script>
    (function () {
      var root = document.getElementById('store-dialog');
      if (!root) return;
      var iconEl   = root.querySelector('[data-sd-icon]');
      var titleEl  = root.querySelector('[data-sd-title]');
      var msgEl    = root.querySelector('[data-sd-message]');
      var okBtn    = root.querySelector('[data-sd-ok]');
      var cancelBtn = root.querySelector('[data-sd-cancel]');
      var LABELS = {
        close:   @json(__('messages.Close')),
        cancel:  @json(__('messages.Cancel')),
        confirm: @json(__('messages.Confirm'))
      };
      var ICONS = {
        info:    '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>',
        success: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="m9 11 3 3L22 4"/></svg>',
        warning: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><path d="M12 9v4"/><path d="M12 17h.01"/></svg>',
        danger:  '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="m15 9-6 6"/><path d="m9 9 6 6"/></svg>'
      };
      var pending = null;

      function settle(value) {
        if (!pending) return;
        var p = pending; pending = null;
        root.classList.remove('is-open');
        document.body.style.overflow = p.overflow;
        if (p.focus && typeof p.focus.focus === 'function') { try { p.focus.focus(); } catch (e) {} }
        p.resolve(value);
      }

      function open(message, opts, isConfirm) {
        opts = opts || {};
        if (pending) settle(false);
        var type = ICONS[opts.type] ? opts.type : 'info';
        iconEl.className = 'sd-icon ' + type;
        iconEl.innerHTML = ICONS[type];
        titleEl.textContent = opts.title || '';
        titleEl.style.display = opts.title ? '' : 'none';
        msgEl.textContent = (message == null) ? '' : String(message);
        okBtn.textContent = opts.okText || (isConfirm ? LABELS.confirm : LABELS.close);
        cancelBtn.textContent = opts.cancelText || LABELS.cancel;
        cancelBtn.style.display = isConfirm ? '' : 'none';
        return new Promise(function (resolve) {
          pending = { resolve: resolve, overflow: document.body.style.overflow, focus: document.activeElement };
          document.body.style.overflow = 'hidden';
          root.classList.add('is-open');
          setTimeout(function () { okBtn.focus(); }, 30);
        });
      }

      okBtn.addEventListener('click', function () { settle(true); });
      cancelBtn.addEventListener('click', function () { settle(false); });
      root.querySelector('[data-sd-backdrop]').addEventListener('click', function () { settle(false); });
      document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && pending) settle(false); });

      // StoreDialog.alert(msg, {type:'info'|'success'|'warning'|'danger', title, okText}) -> Promise<void>
      // StoreDialog.confirm(msg, {type, title, okText, cancelText})                       -> Promise<boolean>
      window.StoreDialog = {
        alert:   function (message, opts) { return open(message, opts, false); },
        confirm: function (message, opts) { return open(message, opts, true); }
      };
    })();
  </script>

  {{-- Storefront bundle (Alpine + theme + cart + UI components) --}}
  <script src="{{ $jsStore }}" defer></script>
  @if(!$isElectronics || empty($electronicsOpts['show_theme_toggle']))
    <script>
      // storefront.js boots dark-first when nothing is stored; undo that unless
      // the visitor explicitly picked dark (and the theme allows it).
      (function () { try { var allow = @json(! $isElectronics); if (!(allow && localStorage.getItem('store.theme') === 'dark')) { document.documentElement.classList.remove('dark'); document.addEventListener('DOMContentLoaded', function () { if (!(allow && localStorage.getItem('store.theme') === 'dark')) document.documentElement.classList.remove('dark'); }); } } catch (e) {} })();
    </script>
  @endif

  {{-- Admin-managed marketing popups + "Request a Quotation" modal --}}
  @if(\Illuminate\Support\Facades\Schema::hasTable('store_popups'))
    @include('store.partials.popups')
  @endif
  @include('store.partials.quote-request')

  {!! $s->custom_js ?? '' !!}
</body>
</html>

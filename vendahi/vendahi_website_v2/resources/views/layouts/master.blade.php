@php
    /**
     * Vue 3 + Ant Design admin, built by Vite (vite.config.js) from resources/src
     * into public/js/. The /{vue?} catch-all serves this layout for every admin
     * path; the SPA router owns the /app/... (legacy) and /next/... (migration)
     * redirects. The Vue 2 layout is parked in old_resources/views/layouts/.
     *
     * Asset URLs come from Vite's manifest and are content-hashed — do NOT add
     * a `?v=` cache-busting query to the module script. Lazy chunks import the
     * entry as "../app.js" (no query), and browsers key ES modules by full URL,
     * so a query would load a second copy of the app + Vue for chunk code and
     * break rendering ("Cannot read properties of null (reading 'ce')").
     */
    $nextBase = '/js/';
    $manifestPath = public_path('js/.vite/manifest.json');
    $entry = null;
    $cssFiles = [];

    // System version from version.txt (the same file the updater reads),
    // exposed as a meta tag so the app can show it without an API round-trip.
    $appVersion = is_file(base_path('version.txt'))
        ? trim(file_get_contents(base_path('version.txt')))
        : '';

    if (is_file($manifestPath)) {
        $viteManifest = json_decode(file_get_contents($manifestPath), true) ?: [];

        // The entry is keyed by its source path (e.g. "src/main.js").
        foreach ($viteManifest as $item) {
            if (!empty($item['isEntry'])) {
                $entry = $item;
                break;
            }
        }

        // Stylesheets: with cssCodeSplit disabled Vite emits one standalone
        // bundle under a "style.css" key instead of attaching it to the entry,
        // so handle both layouts.
        $cssFiles = $entry['css'] ?? [];
        if (!$cssFiles) {
            foreach ($viteManifest as $key => $item) {
                if (!empty($item['file']) && str_ends_with($item['file'], '.css')) {
                    $cssFiles[] = $item['file'];
                }
            }
        }
    }

    // Plan summary: sidebar feature gates + the limit-reached modal.
    $__planSummary = app(\App\Services\TenantLimitsService::class)->getPlanSummary();
@endphp
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="app-version" content="{{ $appVersion }}">
    <meta name="viewport" content="width=device-width,initial-scale=1.0" />
    @include('partials.tenant-font')
    <link rel="icon" href="{{ global_asset(upload_path('settings') . '/' . ($app_settings->favicon ?? 'favicon.ico')) }}">
    <title>{{ $app_settings->app_name ?? 'Stocky | Ultimate Inventory With POS' }}</title>

    {{-- PWA — Settings > PWA can switch the install prompt off entirely. --}}
    @if(($app_settings->pwa_enabled ?? true))
    <link rel="manifest" href="/pwa/app.webmanifest">
    <meta name="theme-color" content="{{ $app_settings->pwa_theme_color ?: \App\Http\Controllers\PwaManifestController::DEFAULT_THEME_COLOR }}">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="{{ $app_settings->pwa_short_name ?: ($app_settings->app_name ?? 'Stocky') }}">
    <link rel="apple-touch-icon" href="{{ pwa_icon_url(192) }}">
    @endif

    <script>
        window.__planSummary = @json($__planSummary);
        window.__uploadPath = '{{ upload_path() }}';
        window.__appName = @json($app_settings->app_name ?? 'Stocky');
        window.__pageTitleSuffix = @json($app_settings->page_title_suffix ?? 'Ultimate Inventory With POS');
    </script>

    @foreach ($cssFiles as $css)
      <link rel="stylesheet" href="{{ $nextBase }}{{ $css }}">
    @endforeach
  </head>
  <body>
    <noscript><strong>This page requires JavaScript.</strong></noscript>
    {{-- Boot loader: shown from first paint until the app mounts (Vue replaces
         the container's content, so it disappears on its own). main.js waits
         for translations + auth + the first route chunk before mounting, so
         this covers the whole refresh, not just the script download. --}}
    <div id="vue3-app">
      <style>
        .boot-loader {
          position: fixed;
          inset: 0;
          display: flex;
          flex-direction: column;
          align-items: center;
          justify-content: center;
          gap: 16px;
          background: #f5f5f5;
          font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
          color: rgba(0, 0, 0, 0.45);
          font-size: 14px;
        }
        .boot-loader__spinner {
          width: 40px;
          height: 40px;
          border: 3px solid rgba(109, 40, 217, 0.15);
          border-top-color: #6d28d9;
          border-radius: 50%;
          animation: boot-spin 0.8s linear infinite;
        }
        @keyframes boot-spin { to { transform: rotate(360deg); } }
      </style>
      <div class="boot-loader">
        <div class="boot-loader__spinner"></div>
        <div>{{ $app_settings->app_name ?? 'Stocky' }}</div>
      </div>
    </div>

    {{-- html5-qrcode global (Html5QrcodeScanner) — POS and barcode-page scanners. --}}
    <script src="/assets_setup/js/qrcode.js"></script>

    @if ($entry)
      <script type="module" src="{{ $nextBase }}{{ $entry['file'] }}"></script>
    @else
      <p style="font-family: sans-serif; padding: 24px">
        Front-end assets are not built yet. Run <code>npm install &amp;&amp; npm run build</code>.
      </p>
    @endif

    @include('partials.plan-upgrade-modal')
    @include('partials.subscription-reminder-banner')
    @include('partials.impersonation-banner')
  </body>
</html>

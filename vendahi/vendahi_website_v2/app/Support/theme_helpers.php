<?php

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Route;

/*
 * Storefront theme helpers. The theme templates were written against the
 * full storefront; these shims give the SaaS storefront the same call sites
 * while degrading gracefully where a feature does not exist here.
 */

if (! function_exists('store_locales')) {
    function store_locales(): array
    {
        $names = ['en' => 'English', 'fr' => 'Français', 'es' => 'Español', 'ar' => 'العربية', 'de' => 'Deutsch', 'pt' => 'Português', 'tr' => 'Türkçe', 'hi' => 'हिन्दी', 'bn' => 'বাংলা', 'ur' => 'اردو'];
        $out = [];
        foreach ($names as $code => $label) {
            if (is_dir(resource_path('lang/'.$code))) {
                $out[$code] = $label;
            }
        }

        return $out ?: ['en' => 'English'];
    }
}

if (! function_exists('store_date')) {
    function store_date($date, string $format = 'M d, Y'): string
    {
        try {
            return Carbon::parse($date)->format($format);
        } catch (\Throwable $e) {
            return (string) $date;
        }
    }
}

/*
 * Storefront base path (Online Store → Settings → Store URL path).
 *
 * The storefront lives at /online_store by default; a tenant may choose a
 * custom path such as /shop, or switch on root-domain mode so the store
 * answers at the tenant host root ("/"). Routes are registered per request on the
 * tenant host (RouteServiceProvider), before tenancy is initialized, so the
 * chosen path is mirrored onto the central tenant record (tenants.data) by
 * SettingsApiController::update() and read back from there — either via
 * tenant() once tenancy is up, or by resolving the request host against the
 * central domains table. Every lookup is memoized for the request; any
 * failure falls back to the default so the store never disappears.
 */

if (! function_exists('store_reserved_paths')) {
    /**
     * First URL segments the storefront base path may never claim: admin
     * app, auth, APIs, system surfaces and static folders.
     */
    function store_reserved_paths(): array
    {
        return [
            'api', 'app', 'pos', 'next', 'ping', 'setup', 'update', 'system-update', 'password',
            'login', 'logout', 'register', 'email', 'billing', 'impersonate', 'portal', 'recruit',
            'customer-display', 'quickbooks', 'salla', 'xero', 'google-sheets', 'google-calendar',
            'gateway', 'webhook', 'whatsapp', 'super', 'workspace', 'checkout', 'customer',
            'api-docs', 'csrf-token', 'session', 'invoice', 'dashboard-next',
            'pwa', 'manifest.webmanifest', 'sitemap.xml', 'robots.txt', 'sw.js', 'offline.html',
            'storage', 'vendor', 'images', 'css', 'js', 'fonts', 'pwa_images', 'assets_setup',
            'errors', 'online_store',
        ];
    }
}

if (! function_exists('store_sanitize_path')) {
    /**
     * Normalize + validate a storefront base path. Returns the clean path
     * ("shop", "my/shop") or null when the value is empty/invalid/reserved.
     */
    function store_sanitize_path($raw): ?string
    {
        $path = strtolower(trim((string) $raw));
        $path = str_replace('\\', '/', $path);
        // No query strings, fragments or encoded tricks; collapse duplicate slashes.
        $path = trim((string) preg_replace('#/+#', '/', $path), "/ \t\n\r\0\x0B");

        if ($path === '' || strlen($path) > 100 || str_contains($path, '?') || str_contains($path, '#')) {
            return null;
        }

        foreach (explode('/', $path) as $segment) {
            if (! preg_match('/^[a-z0-9][a-z0-9_-]*$/', $segment)) {
                return null;
            }
        }

        if (in_array(explode('/', $path)[0], store_reserved_paths(), true)) {
            return null;
        }

        return $path;
    }
}

if (! function_exists('store_tenant_for_host')) {
    /**
     * The tenant a host name belongs to, resolved from the central domains
     * table the same way InitializeTenancyByDomainOrSubdomain does (subdomain
     * of a central domain, otherwise the full custom domain). Null on central
     * domains, unknown hosts, or when the central DB is unreachable.
     */
    function store_tenant_for_host(?string $host)
    {
        $host = strtolower(trim((string) $host));
        if ($host === '') {
            return null;
        }
        $central = array_values(array_filter((array) config('tenancy.central_domains', [])));
        if (in_array($host, $central, true)) {
            return null;
        }
        $candidates = [$host];
        foreach ($central as $c) {
            if ($c !== '' && \Illuminate\Support\Str::endsWith($host, '.'.$c)) {
                $candidates[] = explode('.', $host)[0];
                break;
            }
        }
        try {
            $domainModel = config('tenancy.domain_model');
            $domain = $domainModel::query()->whereIn('domain', array_unique($candidates))->with('tenant')->first();

            return $domain?->tenant;
        } catch (\Throwable $e) {
            return null;
        }
    }
}

if (! function_exists('store_url_config')) {
    /**
     * The tenant's storefront URL configuration, read from the central tenant
     * record (mirrored there by SettingsApiController::update() because routes
     * are registered before tenancy boots). Memoized per request.
     *
     *   ['root' => bool, 'path' => string|null]
     */
    function store_url_config(bool $refresh = false): array
    {
        static $config = null;
        if ($refresh) {
            $config = null;
        }
        if ($config !== null) {
            return $config;
        }

        $cfg = ['root' => false, 'path' => null];
        try {
            $tenant = function_exists('tenant') ? tenant() : null;
            if (! $tenant && app()->bound('request')) {
                $tenant = store_tenant_for_host(request()->getHost());
            }
            $cfg = [
                'root' => (bool) ($tenant?->store_use_root_domain ?? false),
                'path' => $tenant?->store_url_path ?? null,
            ];
        } catch (\Throwable $e) {
            // Not installed yet / central DB unavailable: keep the defaults.
        }

        return $config = $cfg;
    }
}

if (! function_exists('store_path')) {
    /**
     * The storefront base path without slashes: 'online_store' by default,
     * the tenant's configured custom path, or '' when root-domain mode is on
     * (the store answers at the tenant host root). Memoized per request; pass
     * $refresh = true after saving the setting.
     */
    function store_path(bool $refresh = false): string
    {
        static $resolved = null;
        if ($refresh) {
            $resolved = null;
        }
        if ($resolved !== null) {
            return $resolved;
        }

        $cfg = store_url_config($refresh);

        if (! empty($cfg['root'])) {
            return $resolved = '';
        }

        return $resolved = (store_sanitize_path($cfg['path']) ?? 'online_store');
    }
}

if (! function_exists('store_is_root_domain')) {
    /** Whether the storefront is served from the tenant host root. */
    function store_is_root_domain(): bool
    {
        return store_path() === '';
    }
}

if (! function_exists('app_home_url')) {
    /**
     * Where a signed-in staff member belongs after login / password reset /
     * email verification, and where RedirectIfAuthenticated sends them.
     *
     * Normally '/' — the tenant root route bounces staff to /app/dashboard.
     * In root-domain mode that route is not registered at all (the storefront
     * claims '/'), so sending staff to '/' would drop them on the shop front
     * instead of the admin app. Point straight at the dashboard there.
     */
    function app_home_url(): string
    {
        return store_is_root_domain() ? '/app/dashboard' : '/';
    }
}

if (! function_exists('store_auth_path')) {
    /**
     * Base path of the customer auth pages (login / register / logout). In
     * root-domain mode they live under /customer/* so they never shadow the
     * staff /login and /logout; otherwise they sit under the store base path.
     */
    function store_auth_path(): string
    {
        return store_is_root_domain() ? 'customer' : store_path();
    }
}

if (! function_exists('store_auth_url')) {
    /** Absolute URL of a customer auth page, e.g. store_auth_url('login'). */
    function store_auth_url(string $rel = ''): string
    {
        return url('/'.trim(store_auth_path().'/'.ltrim($rel, '/'), '/'));
    }
}

if (! function_exists('store_path_to')) {
    /**
     * App-relative path for a storefront page: store_path_to('shop') =>
     * "online_store/shop", "<custom>/shop", or "shop" in root-domain mode.
     */
    function store_path_to(string $rel = ''): string
    {
        return trim(store_path().'/'.ltrim($rel, '/'), '/');
    }
}

if (! function_exists('store_base_url')) {
    /** Absolute URL of a storefront page under the configured base path. */
    function store_base_url(string $rel = ''): string
    {
        return url('/'.store_path_to($rel));
    }
}

if (! function_exists('is_store_request')) {
    /**
     * Whether a request targets the storefront. Path-based on purpose: it is
     * used by global middleware that runs before routing and before tenancy.
     */
    function is_store_request(\Illuminate\Http\Request $request): bool
    {
        $path = store_path();

        if ($path !== '') {
            return $request->is($path) || $request->is($path.'/*');
        }

        // Root-domain mode: the storefront and the admin SPA share the host
        // root, so match an explicit storefront whitelist. The opposite test
        // ("everything except a reserved prefix") cannot work here: the admin
        // SPA serves its pages from bare top-level paths (/reports/sales,
        // /purchases, /settings/...) that no fixed reserved list can cover,
        // and each one it missed was handed the store session cookie — which
        // logged staff out on every full page load.
        if ($request->is('/')) {
            return true;
        }

        foreach (store_front_patterns() as $pattern) {
            if ($request->is($pattern)) {
                return true;
            }
        }

        return false;
    }
}

if (! function_exists('store_front_patterns')) {
    /**
     * Every path the storefront answers on, relative to the tenant host root.
     *
     * Only consulted in root-domain mode, where the storefront and the admin
     * SPA share '/' and a request has to be attributed to one of them before
     * routing (see is_store_request()). Mirrors the store route group in
     * routes/tenant_web.php — add a pattern here when adding a route there.
     *
     * Two entries are deliberately narrow rather than whole-prefix, because
     * the admin SPA owns the same first segment:
     *   - 'products/{id}/reviews' — the admin's product pages are /products/*
     *   - the five 'store/*' checkout endpoints — the admin's Store module
     *     pages are also /store/*
     */
    function store_front_patterns(): array
    {
        return [
            // Catalog & content
            'shop', 'shop/*',
            'search/*',
            'collections/*',
            'pages/*',
            'flash-sales',
            'properties', 'properties/*',
            'product/*',
            'products/*/reviews',
            'contact', 'contact/*',
            'quote-request',
            'newsletter/*',
            'lang/*', 'currency/*',

            // Customer session, account area and checkout
            'customer', 'customer/*',
            'account', 'account/*',
            'my/*',
            'checkout',
            'thank-you',

            // Checkout AJAX endpoints (narrow: the admin owns /store/* pages)
            'store/orders',
            'store/payment-intent',
            'store/razorpay-order',
            'store/xpay-intent',
            'store/checkout-quote',

            // Payment gateway callbacks / webhooks
            'payfast/*', 'ligdicash/*', 'dodo/*', 'waafipay/*',
        ];
    }
}

if (! function_exists('store_url_config_clear')) {
    /** Forget the memoized base path (call after saving store settings). */
    function store_url_config_clear(): void
    {
        store_url_config(true);
        store_path(true);
    }
}

if (! function_exists('store_address_lines')) {
    function store_address_lines(array $parts): array
    {
        return array_values(array_filter(array_map(fn ($p) => trim((string) $p), $parts)));
    }
}

if (! function_exists('store_text')) {
    /** Admin-authored copy, translated when the model supports it. */
    function store_text($s, string $field)
    {
        if (! $s) {
            return null;
        }
        if (method_exists($s, 'localizedText')) {
            return $s->localizedText($field);
        }
        $v = $s->{$field} ?? null;

        return ($v === null || $v === '') ? null : $v;
    }
}

if (! function_exists('store_route_or')) {
    /** route() when the named route exists, otherwise a safe fallback. */
    function store_route_or(string $name, $params = [], ?string $fallback = null): string
    {
        if (Route::has($name)) {
            return route($name, $params);
        }

        return $fallback ?? route('store.shop');
    }
}

if (! function_exists('product_url')) {
    /** Product page URL, or "#" (the card opens its quick view) when there is no product page. */
    function product_url($product): string
    {
        $id = is_object($product) ? ($product->id ?? null) : $product;
        if ($id && Route::has('store.product.show')) {
            return route('store.product.show', $id);
        }

        return '#';
    }
}

if (! function_exists('store_product_badges')) {
    function store_product_badges($product): array
    {
        if (class_exists(\App\Services\ProductLabelService::class)) {
            return \App\Services\ProductLabelService::badges($product->labels ?? null);
        }

        return [];
    }
}

if (! function_exists('store_flash_ids')) {
    function store_flash_ids(): array
    {
        if (class_exists(\App\Services\FlashSaleService::class)) {
            try {
                return array_keys(app(\App\Services\FlashSaleService::class)->runningRules());
            } catch (\Throwable $e) {
                return [];
            }
        }

        return [];
    }
}

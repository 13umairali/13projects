<?php

/*
| The backend root URL exactly as the operator supplied it — no default. An
| unset or blank value means "not installed yet", which is what drives the
| setup wizard, so a fallback here would make a fresh install look configured
| and silently point the panel at a machine that is not there.
*/
$apiUrl = rtrim(trim((string) env('SAAS_API_URL', env('FRONTEND_API_URL', ''))), '/');

return [

    /*
    |--------------------------------------------------------------------------
    | Backend API
    |--------------------------------------------------------------------------
    |
    | The SaaS panel is purely an API client for the existing Mighty School
    | backend. All business logic lives there; this app only consumes REST
    | endpoints. Keep the base URL free of a trailing slash.
    |
    | 'configured' is the single source of truth for "has the installer run?".
    | Read it — never base_url — when deciding whether the panel is usable:
    | base_url keeps a localhost fallback so tests and local dev still resolve
    | somewhere sane, and that fallback would mask a missing FRONTEND_API_URL.
    | See App\Http\Middleware\EnsureBackendApiConfigured.
    |
    */

    'api' => [
        'configured' => $apiUrl !== '',
        'base_url' => $apiUrl ?: 'http://127.0.0.1:8000',
        'prefix' => env('SAAS_API_PREFIX', 'api'),
        'timeout' => (int) env('SAAS_API_TIMEOUT', 12),
        'connect_timeout' => (int) env('SAAS_API_CONNECT_TIMEOUT', 5),
        'retry_times' => (int) env('SAAS_API_RETRY', 2),
        'retry_sleep' => (int) env('SAAS_API_RETRY_SLEEP', 150),
    ],

    /*
    |--------------------------------------------------------------------------
    | Setup wizard
    |--------------------------------------------------------------------------
    |
    | First-run installer that captures the backend URL and writes it to .env.
    | It unlocks itself only while 'api.configured' is false, so a live panel
    | cannot have its backend repointed by anyone who reaches /setup.
    |
    | To re-run it after installation an administrator must explicitly set
    | SAAS_SETUP_ENABLED=true in .env — a deliberate, server-side act.
    |
    | 'env_key' is the variable the wizard writes. It is also the variable this
    | file reads above, so the two can never drift apart.
    |
    */

    'setup' => [
        'enabled' => filter_var(env('SAAS_SETUP_ENABLED', false), FILTER_VALIDATE_BOOLEAN),
        'env_key' => 'FRONTEND_API_URL',

        // Public, unauthenticated backend endpoint used to prove the URL points
        // at a real Mighty School API. It also verifies the backend's own DB
        // connection, so a 200 here means the stack behind it is actually up.
        'health_endpoint' => env('SAAS_SETUP_HEALTH_ENDPOINT', 'check-status'),

        // Probe budget. Deliberately shorter than the app-wide API timeouts and
        // with no retries: an installer must fail fast and tell the operator,
        // not hang a browser request while it retries a host that is not there.
        'timeout' => (int) env('SAAS_SETUP_TIMEOUT', 8),
        'connect_timeout' => (int) env('SAAS_SETUP_CONNECT_TIMEOUT', 5),
    ],

    /*
    |--------------------------------------------------------------------------
    | Images
    |--------------------------------------------------------------------------
    |
    | Where disk-relative image paths returned by the backend (e.g.
    | "storage/institute_image_settings/logo.png") are anchored. Defaults to the
    | API host, which is what serves /storage/*. Point it at a CDN if you ever
    | front the backend's public disk with one.
    |
    | The scheme (http vs https) is NEVER read from here — it is taken from the
    | incoming request, so the same value works on localhost, a LAN IP and a
    | production domain. See App\Support\ImageUrl.
    |
    */

    'images' => [
        'base_url' => rtrim(env('SAAS_IMAGE_BASE_URL', ''), '/') ?: null,

        /*
        | Placeholders shown when a record has no image, or when the file it
        | points at is gone (the backend keeps the DB row even if the upload is
        | lost). These are the ONLY bundled images we ever render — they stand in
        | for a missing asset, which is different from inventing content: an
        | empty list still renders an empty state, never fabricated rows.
        */
        'placeholders' => [
            'image' => 'images/no-image.png',  // generic: banners, cards, thumbnails
            'avatar' => 'images/user.png',      // people: testimonials, users
            'logo' => 'images/default.png',   // institute / site logos
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache
    |--------------------------------------------------------------------------
    |
    | Every backend response the panel reads — landing content AND admin lists —
    | is cached here, so pages render without waiting on the API. Writes bump a
    | generation counter that invalidates the whole cache at once, so a record you
    | just created, edited or deleted is never served stale. See App\Support\ApiCache.
    |
    | SAAS_CACHE_ENABLED is the single on/off switch. Set it to false while
    | developing so every page hit goes straight to the backend. Setting
    | SAAS_CACHE_TTL to 0 disables the cache too.
    |
    | SAAS_CACHE_TTL is clamped to [min_ttl, max_ttl] — 30 minutes to 1 day — so a
    | typo cannot silently reduce the cache to seconds or pin it there for a week.
    |
    | filter_var (not a (bool) cast) so the quoted string "false" is honoured
    | as false rather than a truthy non-empty string.
    |
    */

    'cache' => [
        'enabled' => filter_var(env('SAAS_CACHE_ENABLED', true), FILTER_VALIDATE_BOOLEAN),
        'ttl' => (int) env('SAAS_CACHE_TTL', 1800),      // seconds; 0 = off
        'min_ttl' => (int) env('SAAS_CACHE_MIN_TTL', 1800),   // 30 minutes
        'max_ttl' => (int) env('SAAS_CACHE_MAX_TTL', 86400),  // 1 day
        'prefix' => 'saas_content',
    ],

    /*
    |--------------------------------------------------------------------------
    | Authentication
    |--------------------------------------------------------------------------
    |
    | Session keys used to persist the bearer token / authenticated admin that
    | the backend returns from the saas-login endpoint.
    |
    */

    'auth' => [
        'token_key' => 'saas_api_token',
        'user_key' => 'saas_auth_user',
        'guard_prefix' => 'admin',
    ],

    /*
    |--------------------------------------------------------------------------
    | Branding defaults
    |--------------------------------------------------------------------------
    |
    | Fallback branding used before / when the backend settings are
    | unavailable so the marketing site always renders something sensible.
    |
    */

    'brand' => [
        'name' => env('SAAS_BRAND_NAME', 'Mighty School'),
        'tagline' => 'The all-in-one School Management & ERP platform',
        'description' => 'Run admissions, academics, finance, HR, exams, library, transport and more from a single modern platform built for institutions of every size.',
        'email' => env('SAAS_BRAND_EMAIL', 'hello@mightyschool.com'),
        'phone' => env('SAAS_BRAND_PHONE', '+1 (555) 000-1234'),
        'address' => 'Remote-first · Worldwide',
        'logo' => null,
        'favicon' => null,
        'primary' => '#6366f1',
        'accent' => '#8b5cf6',
        'socials' => [
            'facebook' => env('SAAS_SOCIAL_FACEBOOK', '#'),
            'twitter' => env('SAAS_SOCIAL_TWITTER', '#'),
            'linkedin' => env('SAAS_SOCIAL_LINKEDIN', '#'),
            'instagram' => env('SAAS_SOCIAL_INSTAGRAM', '#'),
            'youtube' => env('SAAS_SOCIAL_YOUTUBE', '#'),
        ],
    ],

];

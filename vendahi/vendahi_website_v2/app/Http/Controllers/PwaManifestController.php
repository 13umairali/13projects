<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;

/**
 * Serves per-tenant PWA manifests for each app surface (POS, online store,
 * customer display, client portal).
 *
 * These replace the static public/manifest-*.webmanifest files: the manifest
 * "name" comes from the tenant's own settings and the icons resolve to the
 * tenant's generated PWA icons (images/tenants/{id}/pwa/), falling back to the
 * shared defaults via pwa_icon_url(). Routes live under /pwa/*.webmanifest so
 * the web server doesn't shadow them with the old static files.
 */
class PwaManifestController extends Controller
{
    /** Fallbacks for the admin surface, shown as placeholders on the PWA settings page. */
    public const DEFAULT_THEME_COLOR = '#2f3640';

    public const DEFAULT_BACKGROUND_COLOR = '#ffffff';

    /**
     * Per-surface manifest metadata, mirrored from the original static
     * manifests so each installed surface keeps its own scope/theme/display.
     */
    protected array $surfaces = [
        'app' => [
            'short_name'       => 'Stocky',
            'description'      => 'Ultimate Inventory with POS',
            'start_url'        => '/',
            'scope'            => '/',
            'display'          => 'standalone',
            'orientation'      => 'any',
            'background_color' => '#ffffff',
            'theme_color'      => '#2f3640',
        ],
        'store' => [
            'short_name'       => 'Store',
            'description'      => 'Online Store',
            'start_url'        => '/online_store',
            'scope'            => '/online_store',
            'display'          => 'standalone',
            'orientation'      => 'any',
            'background_color' => '#ffffff',
            'theme_color'      => '#6c5ce7',
        ],
        'customer-display' => [
            'short_name'       => 'Display',
            'description'      => 'Customer-facing POS display',
            'start_url'        => '/customer-display',
            'scope'            => '/customer-display',
            'display'          => 'fullscreen',
            'orientation'      => 'landscape',
            'background_color' => '#0b0c10',
            'theme_color'      => '#0b0c10',
        ],
        'portal' => [
            'short_name'       => 'Portal',
            'description'      => 'Client Portal',
            'start_url'        => '/portal',
            'scope'            => '/portal',
            'display'          => 'standalone',
            'orientation'      => 'any',
            'background_color' => '#f1f5f9',
            'theme_color'      => '#2f3640',
        ],
    ];

    public function manifest(Request $request, string $type)
    {
        $surface = $this->surfaces[$type] ?? $this->surfaces['app'];
        if ($type === 'store') {
            // The storefront may live under a custom base path.
            $surface['start_url'] = '/'.store_path();
            $surface['scope'] = '/'.store_path();
        }

        $name = $this->appName($surface['short_name']);

        $manifest = [
            'name'             => $name,
            'short_name'       => $surface['short_name'],
            'description'      => $surface['description'],
            'start_url'        => $surface['start_url'],
            'scope'            => $surface['scope'],
            'display'          => $surface['display'],
            'orientation'      => $surface['orientation'],
            'background_color' => $surface['background_color'],
            'theme_color'      => $surface['theme_color'],
            'lang'             => 'en',
            'dir'              => 'ltr',
            'icons'            => $this->icons(),
        ];

        // Settings -> PWA configures the installed admin app. The store,
        // customer-display and portal surfaces keep their own scope and
        // chrome, so the overrides deliberately apply to 'app' only.
        if ($type === 'app') {
            $manifest = $this->applyOverrides($manifest, $name);
        }

        return response()
            ->json($manifest, 200, [], JSON_UNESCAPED_SLASHES)
            ->header('Content-Type', 'application/manifest+json');
    }

    /**
     * Overlay the tenant's saved PWA overrides. Each is optional: a NULL or
     * blank column keeps the value already built for the surface, which is what
     * the settings page communicates with its placeholders.
     */
    protected function applyOverrides(array $manifest, string $name): array
    {
        $setting = $this->setting();

        if (! $setting || ! \Schema::hasColumn('settings', 'pwa_enabled')) {
            return $manifest;
        }

        $override = function (string $column) use ($setting) {
            $value = trim((string) ($setting->{$column} ?? ''));

            return $value !== '' ? $value : null;
        };

        $manifest['name'] = $override('pwa_name') ?? $manifest['name'];

        // Home screens cut long names off, so the short name defaults to the
        // part of the app name before the "|" — mirrored in the page's preview.
        $manifest['short_name'] = $override('pwa_short_name')
            ?? (trim(explode('|', $manifest['name'])[0]) ?: $manifest['name']);

        $manifest['description'] = $override('pwa_description') ?? $manifest['name'];

        foreach ([
            'pwa_start_url'        => 'start_url',
            'pwa_display'          => 'display',
            'pwa_orientation'      => 'orientation',
            'pwa_theme_color'      => 'theme_color',
            'pwa_background_color' => 'background_color',
        ] as $column => $key) {
            if ($value = $override($column)) {
                $manifest[$key] = $value;
            }
        }

        return $manifest;
    }

    /** The tenant's settings row, or null when it is unavailable. */
    protected function setting()
    {
        try {
            return Setting::where('deleted_at', null)->first();
        } catch (\Throwable) {
            // Settings table may be unavailable (pre-setup, mid-migration).
            return null;
        }
    }

    /**
     * The tenant's configured application name, falling back to the company
     * name and finally the default product name.
     */
    protected function appName(string $shortName): string
    {
        try {
            $setting = Setting::where('deleted_at', null)->first();
            $name = $setting->app_name ?? $setting->CompanyName ?? null;
            if ($name) {
                return $name;
            }
        } catch (\Throwable) {
            // Settings table may be unavailable; fall through to the default.
        }

        return 'Stocky ' . $shortName;
    }

    protected function icons(): array
    {
        $icon192 = pwa_icon_url(192);
        $icon512 = pwa_icon_url(512);

        return [
            ['src' => $icon192, 'sizes' => '192x192', 'type' => 'image/png', 'purpose' => 'any'],
            ['src' => $icon512, 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'any'],
            ['src' => $icon192, 'sizes' => '192x192', 'type' => 'image/png', 'purpose' => 'maskable'],
            ['src' => $icon512, 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'maskable'],
        ];
    }
}

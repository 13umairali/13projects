<?php

namespace App\Support;

use Illuminate\Support\Str;

/**
 * The single place that turns a backend image reference into a URL the browser
 * can actually load. Views must never re-implement this — they call saas_image().
 *
 * What the backend actually gives us (see fileUploader() in the API's
 * app/Helpers/general.php): a BARE FILENAME, e.g. "2026-07-13-68a1f.png". The
 * directory is implicit in whichever call site saved it, and the file lives on
 * the API's `public` disk — so the real URL is {api}/storage/{dir}/{filename}.
 * That is why callers pass $dir; see the DIR_* constants for the ones we use.
 *
 * Scheme is taken from the incoming request, never hardcoded, so one database
 * row renders on http://127.0.0.1, http://192.168.x.x and https://domain.com.
 */
final class ImageUrl
{
    /**
     * Upload directories on the backend's public disk, matching the fileUploader()
     * call sites. Note institute/ (Institute.logo, written by InstituteRepository)
     * is NOT the same as institutes/ (Onboarding.institute_logo) — don't merge them.
     */
    public const DIR_IMAGE_SETTINGS = 'institute_image_settings';

    public const DIR_INSTITUTE = 'institute';

    public const DIR_ONBOARDING = 'institutes';

    public const DIR_LOGOS = 'logos';

    /**
     * fileUploader() returns this literal string when no file was given, so it
     * is stored in the database as if it were a real image. It is not — treat it
     * as "unset" and let the view render its own empty state.
     */
    private const NO_IMAGE = 'def.png';

    /**
     * @param  mixed  $path  Bare filename, disk-relative path, absolute URL or data URI.
     * @param  string|null  $dir  Upload directory on the backend's public disk.
     * @param  string|null  $fallback  Returned only when there is no usable image.
     */
    public static function resolve(mixed $path, ?string $dir = null, ?string $fallback = null): ?string
    {
        $path = is_string($path) ? trim($path) : '';

        if ($path === '' || $path === self::NO_IMAGE) {
            return $fallback;
        }

        // Already renderable.
        if (Str::startsWith($path, 'data:')) {
            return $path;
        }

        // Protocol-relative ("//cdn.example.com/x.png") — adopt our scheme.
        if (Str::startsWith($path, '//')) {
            return self::normalizeScheme(self::scheme().':'.$path);
        }

        if (Str::startsWith($path, ['http://', 'https://'])) {
            return self::normalizeScheme($path);
        }

        return self::normalizeScheme(self::base().'/'.self::storagePath($path, $dir));
    }

    /**
     * Place a stored value under the backend's public disk. A value that already
     * carries its own directory is trusted as-is; a bare filename gets $dir.
     */
    protected static function storagePath(string $path, ?string $dir): string
    {
        $path = ltrim($path, '/');

        if (Str::startsWith($path, 'storage/')) {
            return $path;
        }

        if (Str::contains($path, '/')) {
            return 'storage/'.$path;
        }

        $dir = trim((string) $dir, '/');

        return $dir === '' ? 'storage/'.$path : 'storage/'.$dir.'/'.$path;
    }

    /**
     * Where relative image paths are anchored: the API host, which is what
     * serves /storage/*. Override only to front that disk with a CDN.
     */
    protected static function base(): string
    {
        $base = config('saas.images.base_url')
            ?: config('saas.api.base_url')
            ?: config('app.url');

        return rtrim((string) $base, '/');
    }

    /**
     * Upgrade an http:// image to https:// when the page itself is served over
     * https — browsers block mixed content, so it would fail to load otherwise.
     * A blocked image is already broken, so upgrading can only help.
     */
    protected static function normalizeScheme(string $url): string
    {
        if (self::scheme() === 'https' && Str::startsWith($url, 'http://')) {
            return Str::replaceFirst('http://', 'https://', $url);
        }

        return $url;
    }

    protected static function scheme(): string
    {
        if ($request = request()) {
            return $request->isSecure() ? 'https' : 'http';
        }

        // Console (sitemap, queues): fall back to the configured app URL.
        return Str::startsWith((string) config('app.url'), 'https://') ? 'https' : 'http';
    }
}

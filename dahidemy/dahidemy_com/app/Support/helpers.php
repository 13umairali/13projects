<?php

use App\Support\ImageUrl;

if (! function_exists('saas_image')) {
    /**
     * Resolve a backend image reference to a browser-loadable URL.
     *
     * Returns $fallback (default null) when the backend has no image — including
     * when it stored its "def.png" placeholder.
     *
     * @param  string|null  $dir  Upload dir on the backend disk; see ImageUrl::DIR_*.
     */
    function saas_image(mixed $path, ?string $dir = null, ?string $fallback = null): ?string
    {
        return ImageUrl::resolve($path, $dir, $fallback);
    }
}

if (! function_exists('saas_placeholder')) {
    /**
     * URL of the bundled stand-in for a missing image.
     *
     * Deliberately prefixed: a bare placeholder() would be a landmine, since a
     * future package defining it first would silently win the function_exists
     * guard and hijack every call here.
     *
     * @param  string  $kind  image | avatar | logo
     */
    function saas_placeholder(string $kind = 'image'): string
    {
        $map = config('saas.images.placeholders', []);

        return asset($map[$kind] ?? $map['image'] ?? 'images/no-image.png');
    }
}

if (! function_exists('saas_image_or_placeholder')) {
    /**
     * Always returns a renderable URL: the record's image when it has one,
     * otherwise the placeholder for that kind. Prefer the <x-img> component in
     * views — it also swaps in the placeholder when the URL turns out to be dead.
     */
    function saas_image_or_placeholder(mixed $path, ?string $dir = null, string $kind = 'image'): string
    {
        return saas_image($path, $dir) ?? saas_placeholder($kind);
    }
}

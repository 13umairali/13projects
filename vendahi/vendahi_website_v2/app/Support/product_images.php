<?php

/*
 * Product image helpers. products.image / product_images.image_path hold either
 * a filename under the tenant's products upload folder or an absolute URL
 * (pasted link). Every storefront/admin consumer resolves through these.
 */

if (! function_exists('product_image_is_remote')) {
    function product_image_is_remote($value): bool
    {
        return (bool) preg_match('#^https?://#i', trim((string) $value));
    }
}

if (! function_exists('product_image_url_or_null')) {
    function product_image_url_or_null($value): ?string
    {
        $v = trim((string) $value);
        if ($v === '') {
            return null;
        }
        if (product_image_is_remote($v)) {
            return $v;
        }

        return global_asset(upload_path('products').'/'.ltrim($v, '/'));
    }
}

if (! function_exists('product_image_url')) {
    function product_image_url($value): string
    {
        $url = product_image_url_or_null($value);
        if ($url === null || str_ends_with($url, '/no-image.png')) {
            return global_asset(upload_path('products').'/no-image.png');
        }

        return $url;
    }
}

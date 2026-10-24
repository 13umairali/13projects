{{--
    The single way to render a backend image.

    Handles the two distinct failure modes:
      1. The record has no image at all  -> render the placeholder immediately.
      2. The record HAS an image but the file is gone (the backend keeps the DB
         row when an upload is lost, so the URL resolves but 403/404s) -> only
         the browser finds out, so onerror swaps the placeholder in.

    onerror is cleared first so a broken placeholder can't loop.

    Usage: <x-img :src="$row['image'] ?? null" dir="saas_banners" kind="image" class="..." />
--}}
@props([
    'src' => null,
    'dir' => null,
    'kind' => 'image',
    'alt' => '',
])

@php
    $fallback = saas_placeholder($kind);
    $url = saas_image($src, $dir) ?? $fallback;
@endphp

<img src="{{ $url }}" alt="{{ $alt }}" loading="lazy" decoding="async"
    onerror="this.onerror=null;this.src='{{ $fallback }}'"
    {{ $attributes->merge(['class' => 'object-cover']) }}>

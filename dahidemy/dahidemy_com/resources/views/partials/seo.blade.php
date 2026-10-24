@php
    $seo = array_merge([
        'title'       => $brand['meta_title'] ?? ($brand['name'] ?? config('saas.brand.name')),
        'description' => $brand['description'] ?? config('saas.brand.description'),
        'keywords'    => $brand['meta_keywords'] ?? 'school management system, school ERP, education software, student information system, LMS, SaaS for schools',
        'image'       => $brand['og_image'] ?? null,
        'type'        => 'website',
        'canonical'   => url()->current(),
    ], $seo ?? []);

    $siteName = $brand['name'] ?? config('saas.brand.name');
    $fullTitle = ($seo['title'] === $siteName)
        ? $siteName.' — '.($brand['tagline'] ?? config('saas.brand.tagline'))
        : $seo['title'].' · '.$siteName;
@endphp

<title>{{ $fullTitle }}</title>
<meta name="description" content="{{ $seo['description'] }}">
<meta name="keywords" content="{{ $seo['keywords'] }}">
<meta name="author" content="{{ $siteName }}">
<meta name="robots" content="index, follow, max-image-preview:large">
<link rel="canonical" href="{{ $seo['canonical'] }}">

{{-- Open Graph --}}
<meta property="og:site_name" content="{{ $siteName }}">
<meta property="og:type" content="{{ $seo['type'] }}">
<meta property="og:title" content="{{ $fullTitle }}">
<meta property="og:description" content="{{ $seo['description'] }}">
<meta property="og:url" content="{{ $seo['canonical'] }}">
@if (!empty($seo['image']))
    <meta property="og:image" content="{{ $seo['image'] }}">
@endif

{{-- Twitter --}}
<meta name="twitter:card" content="{{ !empty($seo['image']) ? 'summary_large_image' : 'summary' }}">
<meta name="twitter:title" content="{{ $fullTitle }}">
<meta name="twitter:description" content="{{ $seo['description'] }}">
@if (!empty($seo['image']))
    <meta name="twitter:image" content="{{ $seo['image'] }}">
@endif

{{-- Structured data (JSON-LD) --}}
<script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@type'    => 'SoftwareApplication',
    'name'     => $siteName,
    'applicationCategory' => 'BusinessApplication',
    'operatingSystem'     => 'Web',
    'description' => $seo['description'],
    'url'         => url('/'),
    'offers'      => [
        '@type'         => 'Offer',
        'price'         => '0',
        'priceCurrency' => 'USD',
    ],
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
</script>

@if (!empty($brand['gtm_id']))
    <script>
        (function(w, d, s, l, i) {
            w[l] = w[l] || [];
            w[l].push({'gtm.start': new Date().getTime(), event: 'gtm.js'});
            var f = d.getElementsByTagName(s)[0], j = d.createElement(s), dl = l != 'dataLayer' ? '&l=' + l : '';
            j.async = true;
            j.src = 'https://www.googletagmanager.com/gtm.js?id=' + i + dl;
            f.parentNode.insertBefore(j, f);
        })(window, document, 'script', 'dataLayer', '{{ $brand['gtm_id'] }}');
    </script>
@endif
@if (!empty($brand['ga4_id']))
    <script async src="https://www.googletagmanager.com/gtag/js?id={{ $brand['ga4_id'] }}"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag() { dataLayer.push(arguments); }
        gtag('js', new Date());
        gtag('config', '{{ $brand['ga4_id'] }}');
    </script>
@endif
@if (!empty($brand['meta_pixel_id']))
    <script>
        !function(f, b, e, v, n, t, s) {
            if (f.fbq) return; n = f.fbq = function() { n.callMethod ? n.callMethod.apply(n, arguments) : n.queue.push(arguments) };
            if (!f._fbq) f._fbq = n; n.push = n; n.loaded = !0; n.version = '2.0';
            n.queue = []; t = b.createElement(e); t.async = !0;
            t.src = v; s = b.getElementsByTagName(e)[0]; s.parentNode.insertBefore(t, s)
        }(window, document, 'script', 'https://connect.facebook.net/en_US/fbevents.js');
        fbq('init', '{{ $brand['meta_pixel_id'] }}');
        fbq('track', 'PageView');
    </script>
    <noscript><img height="1" width="1" style="display:none" src="https://www.facebook.com/tr?id={{ $brand['meta_pixel_id'] }}&ev=PageView&noscript=1"></noscript>
@endif

<!DOCTYPE html>
@php
    $isRtl = in_array(app()->getLocale(), ['ar', 'he', 'fa', 'ur']);
    $generalSettings = \App\Models\Central\GeneralSetting::instance();
    $appName = $generalSettings->app_name ?: 'Stocky';
    $logoUrl = $generalSettings->getLogoUrl();
    $faviconUrl = ($seo && $seo->favicon) ? asset($seo->favicon) : $generalSettings->getFaviconUrl();
@endphp
<html lang="{{ app()->getLocale() }}" @if($isRtl) dir="rtl" @endif>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @yield('meta')
    @if($faviconUrl)
        <link rel="icon" href="{{ $faviconUrl }}">
    @endif
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="{{ asset('assets_super/css/inter.css') }}" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Serif:ital@0;1&display=swap" rel="stylesheet">
    <link href="{{ asset('assets_super/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets_super/css/bootstrap-icons.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets_super/css/landing.css') }}" rel="stylesheet">
    <style>
        .blog-page { padding: 7.5rem 0 4rem; min-height: 70vh; }
        .blog-hero { text-align: center; margin-bottom: 3rem; }
        .blog-hero h1 { font-weight: 800; letter-spacing: -0.02em; }
        .blog-hero p { color: #6b7280; max-width: 560px; margin: 0.5rem auto 0; }
        .blog-cat-pills { display: flex; flex-wrap: wrap; gap: 0.5rem; justify-content: center; margin-bottom: 2.5rem; }
        .blog-cat-pill { display: inline-block; padding: 0.35rem 0.9rem; border-radius: 999px; border: 1px solid #e5e7eb; color: #374151; text-decoration: none; font-size: 0.875rem; font-weight: 600; transition: all .15s ease; }
        .blog-cat-pill:hover { border-color: #6366f1; color: #6366f1; }
        .blog-cat-pill.active { background: #6366f1; border-color: #6366f1; color: #fff; }
        .blog-card { border: 1px solid #e5e7eb; border-radius: 1rem; overflow: hidden; height: 100%; display: flex; flex-direction: column; background: #fff; transition: box-shadow .15s ease, transform .15s ease; }
        .blog-card:hover { box-shadow: 0 12px 32px rgba(15, 23, 42, .08); transform: translateY(-2px); }
        .blog-card-cover { aspect-ratio: 16 / 9; width: 100%; object-fit: cover; background: #f3f4f6; }
        .blog-card-cover--placeholder { display: flex; align-items: center; justify-content: center; color: #9ca3af; font-size: 2rem; }
        .blog-card-body { padding: 1.25rem; display: flex; flex-direction: column; flex-grow: 1; }
        .blog-card-title { font-size: 1.1rem; font-weight: 700; margin: 0.5rem 0; }
        .blog-card-title a { color: #111827; text-decoration: none; }
        .blog-card-title a:hover { color: #6366f1; }
        .blog-card-excerpt { color: #6b7280; font-size: 0.9rem; flex-grow: 1; }
        .blog-card-meta { color: #9ca3af; font-size: 0.8rem; margin-top: 0.75rem; }
        .blog-badge { display: inline-block; align-self: flex-start; font-size: 0.72rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em; color: #6366f1; background: #eef2ff; border-radius: 999px; padding: 0.2rem 0.65rem; text-decoration: none; }
        .blog-post { max-width: 760px; margin: 0 auto; }
        .blog-post-header { text-align: center; margin-bottom: 2rem; }
        .blog-post-header h1 { font-weight: 800; letter-spacing: -0.02em; }
        .blog-post-meta { color: #6b7280; font-size: 0.9rem; margin-top: 0.75rem; display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap; }
        .blog-post-cover { width: 100%; border-radius: 1rem; margin-bottom: 2rem; }
        .blog-post-content { font-size: 1.06rem; line-height: 1.8; color: #1f2937; }
        .blog-post-content h2, .blog-post-content h3 { margin-top: 2rem; margin-bottom: 0.75rem; font-weight: 700; }
        .blog-post-content img { max-width: 100%; height: auto; border-radius: 0.75rem; }
        .blog-post-content blockquote { border-inline-start: 4px solid #6366f1; padding-inline-start: 1rem; color: #4b5563; margin: 1.5rem 0; }
        .blog-post-content pre { background: #0f172a; color: #e2e8f0; padding: 1rem; border-radius: 0.75rem; overflow-x: auto; }
        .blog-related { margin-top: 4rem; }
        .blog-related h2 { font-size: 1.35rem; font-weight: 700; margin-bottom: 1.25rem; }
        .blog-footer { border-top: 1px solid #e5e7eb; padding: 1.5rem 0; }
        .blog-footer p { margin: 0; color: #6b7280; font-size: 0.875rem; text-align: center; }
        .blog-pagination { margin-top: 3rem; display: flex; justify-content: center; }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-landing fixed-top" id="mainNav">
        <div class="container">
            <a class="navbar-brand" href="{{ route('central.welcome') }}">
                <span class="brand-mark">
                    @if($logoUrl)
                        <img src="{{ $logoUrl }}" alt="{{ $appName }}">
                    @else
                        <span class="brand-icon">{{ strtoupper(substr($appName, 0, 1)) }}</span>
                    @endif
                </span>
                <span class="brand-text">{{ $appName }}</span>
            </a>
            <div class="d-flex align-items-center gap-2">
                <a class="nav-btn nav-btn-ghost" href="{{ route('central.blog.index') }}">
                    {{ __('landing.blog') }}
                </a>
                <a class="nav-btn nav-btn-ghost" href="{{ route('central.welcome') }}">
                    <i class="bi bi-arrow-left"></i> {{ __('landing.home') }}
                </a>
            </div>
        </div>
    </nav>

    <main class="blog-page">
        <div class="container">
            @yield('content')
        </div>
    </main>

    <footer class="blog-footer">
        <div class="container">
            <p>{{ $footer->copyright_text ?? '© ' . date('Y') . ' ' . $appName . '. All rights reserved.' }}</p>
        </div>
    </footer>

</body>
</html>

@extends('central.blog.layout')

@php
    $generalSettings = \App\Models\Central\GeneralSetting::instance();
    $appName = $generalSettings->app_name ?: 'Stocky';
    $metaTitle = $post->meta_title_resolved . ' — ' . $appName;
    $metaDescription = $post->meta_description_resolved;
    $ogImage = $post->cover_image ? asset($post->cover_image) : (($seo && $seo->og_image) ? asset($seo->og_image) : null);
    $logoUrl = $generalSettings->getLogoUrl();

    $jsonLd = [
        '@context' => 'https://schema.org',
        '@type' => 'Article',
        'headline' => $post->title,
        'description' => $metaDescription,
        'datePublished' => $post->published_at->toIso8601String(),
        'dateModified' => $post->updated_at->toIso8601String(),
        'mainEntityOfPage' => [
            '@type' => 'WebPage',
            '@id' => route('central.blog.show', $post->slug),
        ],
        'author' => $post->author
            ? ['@type' => 'Person', 'name' => $post->author->name]
            : ['@type' => 'Organization', 'name' => $appName],
        'publisher' => array_filter([
            '@type' => 'Organization',
            'name' => $appName,
            'logo' => $logoUrl ? ['@type' => 'ImageObject', 'url' => $logoUrl] : null,
        ]),
    ];
    if ($ogImage) {
        $jsonLd['image'] = [$ogImage];
    }
@endphp

@section('meta')
    <title>{{ $metaTitle }}</title>
    <meta name="description" content="{{ $metaDescription }}">
    <link rel="canonical" href="{{ route('central.blog.show', $post->slug) }}">
    <meta property="og:type" content="article">
    <meta property="og:site_name" content="{{ $appName }}">
    <meta property="og:title" content="{{ $post->meta_title_resolved }}">
    <meta property="og:description" content="{{ $metaDescription }}">
    <meta property="og:url" content="{{ route('central.blog.show', $post->slug) }}">
    @if($ogImage)
        <meta property="og:image" content="{{ $ogImage }}">
    @endif
    <meta property="article:published_time" content="{{ $post->published_at->toIso8601String() }}">
    <meta property="article:modified_time" content="{{ $post->updated_at->toIso8601String() }}">
    @if($post->category)
        <meta property="article:section" content="{{ $post->category->name }}">
    @endif
    <meta name="twitter:card" content="{{ $ogImage ? 'summary_large_image' : 'summary' }}">
    <meta name="twitter:title" content="{{ $post->meta_title_resolved }}">
    <meta name="twitter:description" content="{{ $metaDescription }}">
    @if($ogImage)
        <meta name="twitter:image" content="{{ $ogImage }}">
    @endif
    <script type="application/ld+json">{!! json_encode($jsonLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
@endsection

@section('content')
    <article class="blog-post">
        <header class="blog-post-header">
            @if($post->category)
                <a href="{{ route('central.blog.index', ['category' => $post->category->slug]) }}" class="blog-badge">{{ $post->category->name }}</a>
            @endif
            <h1>{{ $post->title }}</h1>
            <div class="blog-post-meta">
                <span>
                    <i class="bi bi-calendar3"></i>
                    <time datetime="{{ $post->published_at->toDateString() }}">{{ $post->published_at->format('F j, Y') }}</time>
                </span>
                @if($post->author)
                    <span><i class="bi bi-person"></i> {{ $post->author->name }}</span>
                @endif
            </div>
        </header>

        @if($post->cover_image)
            <img src="{{ asset($post->cover_image) }}" alt="{{ $post->title }}" class="blog-post-cover">
        @endif

        <div class="blog-post-content">
            {!! $post->content !!}
        </div>

        <div class="mt-5">
            <a href="{{ route('central.blog.index') }}" class="nav-btn nav-btn-ghost">
                <i class="bi bi-arrow-left"></i> {{ __('landing.blog_back') }}
            </a>
        </div>
    </article>

    @if($relatedPosts->isNotEmpty())
    <section class="blog-related">
        <h2>{{ __('landing.blog_related') }}</h2>
        <div class="row g-4">
            @foreach($relatedPosts as $related)
            <div class="col-md-6 col-lg-4">
                <article class="blog-card">
                    <a href="{{ route('central.blog.show', $related->slug) }}" tabindex="-1" aria-hidden="true">
                        @if($related->cover_image)
                            <img src="{{ asset($related->cover_image) }}" alt="{{ $related->title }}" class="blog-card-cover" loading="lazy">
                        @else
                            <div class="blog-card-cover blog-card-cover--placeholder"><i class="bi bi-journal-text"></i></div>
                        @endif
                    </a>
                    <div class="blog-card-body">
                        <h3 class="blog-card-title">
                            <a href="{{ route('central.blog.show', $related->slug) }}">{{ $related->title }}</a>
                        </h3>
                        <p class="blog-card-meta">
                            <i class="bi bi-calendar3"></i>
                            <time datetime="{{ $related->published_at->toDateString() }}">{{ $related->published_at->format('F j, Y') }}</time>
                        </p>
                    </div>
                </article>
            </div>
            @endforeach
        </div>
    </section>
    @endif
@endsection

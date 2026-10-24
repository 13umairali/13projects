@extends('central.blog.layout')

@php
    $appName = \App\Models\Central\GeneralSetting::instance()->app_name ?: 'Stocky';
    $pageTitle = ($activeCategory ? $activeCategory->name . ' — ' : '') . __('landing.blog') . ' — ' . $appName;
    $pageDescription = $activeCategory && $activeCategory->description
        ? $activeCategory->description
        : ($seo->meta_description ?? __('landing.blog_meta_description', ['app' => $appName]));
    // Category-filtered pages canonicalize to /blog to avoid thin duplicates; paginated pages self-canonicalize.
    $canonical = $activeCategory
        ? route('central.blog.index')
        : ($posts->currentPage() > 1 ? $posts->url($posts->currentPage()) : route('central.blog.index'));
@endphp

@section('meta')
    <title>{{ $pageTitle }}</title>
    <meta name="description" content="{{ $pageDescription }}">
    <link rel="canonical" href="{{ $canonical }}">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="{{ $appName }}">
    <meta property="og:title" content="{{ $pageTitle }}">
    <meta property="og:description" content="{{ $pageDescription }}">
    <meta property="og:url" content="{{ url()->current() }}">
    @if($seo && $seo->og_image)
        <meta property="og:image" content="{{ asset($seo->og_image) }}">
    @endif
    <meta name="twitter:card" content="summary">
    <meta name="twitter:title" content="{{ $pageTitle }}">
    <meta name="twitter:description" content="{{ $pageDescription }}">
@endsection

@section('content')
    <header class="blog-hero">
        <h1>{{ __('landing.blog') }}</h1>
        <p>{{ __('landing.blog_subtitle') }}</p>
    </header>

    @if($categories->isNotEmpty())
    <nav class="blog-cat-pills" aria-label="{{ __('landing.blog_categories') }}">
        <a href="{{ route('central.blog.index') }}" class="blog-cat-pill {{ $activeCategory ? '' : 'active' }}">{{ __('landing.blog_all_posts') }}</a>
        @foreach($categories as $cat)
            <a href="{{ route('central.blog.index', ['category' => $cat->slug]) }}" class="blog-cat-pill {{ ($activeCategory && $activeCategory->id === $cat->id) ? 'active' : '' }}">
                {{ $cat->name }}
            </a>
        @endforeach
    </nav>
    @endif

    @if($posts->isNotEmpty())
    <div class="row g-4">
        @foreach($posts as $post)
        <div class="col-md-6 col-lg-4">
            <article class="blog-card">
                <a href="{{ route('central.blog.show', $post->slug) }}" tabindex="-1" aria-hidden="true">
                    @if($post->cover_image)
                        <img src="{{ asset($post->cover_image) }}" alt="{{ $post->title }}" class="blog-card-cover" loading="lazy">
                    @else
                        <div class="blog-card-cover blog-card-cover--placeholder"><i class="bi bi-journal-text"></i></div>
                    @endif
                </a>
                <div class="blog-card-body">
                    @if($post->category)
                        <a href="{{ route('central.blog.index', ['category' => $post->category->slug]) }}" class="blog-badge">{{ $post->category->name }}</a>
                    @endif
                    <h2 class="blog-card-title">
                        <a href="{{ route('central.blog.show', $post->slug) }}">{{ $post->title }}</a>
                    </h2>
                    @if($post->excerpt)
                        <p class="blog-card-excerpt">{{ Str::limit($post->excerpt, 140) }}</p>
                    @endif
                    <p class="blog-card-meta">
                        <i class="bi bi-calendar3"></i>
                        <time datetime="{{ $post->published_at->toDateString() }}">{{ $post->published_at->format('F j, Y') }}</time>
                    </p>
                </div>
            </article>
        </div>
        @endforeach
    </div>

    @if($posts->hasPages())
    <div class="blog-pagination">
        {{ $posts->links() }}
    </div>
    @endif
    @else
    <div class="text-center py-5">
        <p class="text-muted fs-5 mb-0">{{ __('landing.blog_no_posts') }}</p>
    </div>
    @endif
@endsection

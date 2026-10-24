<!DOCTYPE html>
@php
    $isRtl = in_array(app()->getLocale(), ['ar', 'he', 'fa', 'ur']);
    $generalSettings = \App\Models\Central\GeneralSetting::instance();
    $appName = $generalSettings->app_name ?: 'Stocky';
    $logoUrl = $generalSettings->getLogoUrl();
@endphp

@php
    $l3FeatureItems = ($features['items'] ?? collect());
    $l3PlatformVisible = !empty($features['is_active']) && ($l3FeatureItems->isNotEmpty() || ($features['section'] ?? null));
@endphp

<html lang="{{ app()->getLocale() }}" @if($isRtl) dir="rtl" @endif>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

@if(isset($seo) && $seo)
    {{-- Search Engine Meta --}}
    <title>{{ $seo->meta_title ?? ($appName ?? 'Vendahi') }}</title>
    <meta name="description" content="{{ $seo->meta_description ?? '' }}">
    @if(!empty($seo->meta_keywords))
        <meta name="keywords" content="{{ $seo->meta_keywords }}">
    @endif
    <link rel="canonical" href="{{ url()->current() }}">

    {{-- Open Graph / Social Media --}}
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:site_name" content="{{ $appName ?? 'Vendahi' }}">
    <meta property="og:title" content="{{ $seo->meta_title ?? ($appName ?? 'Vendahi') }}">
    <meta property="og:description" content="{{ $seo->meta_description ?? '' }}">
    @if(!empty($seo->og_image))
        <meta property="og:image" content="{{ asset($seo->og_image) }}">
    @endif

    {{-- Twitter Cards --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $seo->meta_title ?? ($appName ?? 'Vendahi') }}">
    <meta name="twitter:description" content="{{ $seo->meta_description ?? '' }}">
    @if(!empty($seo->og_image))
        <meta name="twitter:image" content="{{ asset($seo->og_image) }}">
    @endif

    {{-- Favicon & Icons --}}
    @if(!empty($seo->favicon))
        <link rel="icon" type="image/x-icon" href="{{ asset($seo->favicon) }}">
        <link rel="shortcut icon" href="{{ asset($seo->favicon) }}">
        <link rel="apple-touch-icon" href="{{ asset($seo->favicon) }}">
    @else
        <link rel="icon" type="image/x-icon" href="{{ asset('images/super/settings/favicon.ico') }}">
    @endif
@else
    {{-- Fallback --}}
    <title>{{ $appName ?? 'Vendahi — All-in-One Business Platform' }}</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('images/super/settings/favicon.ico') }}">
@endif

<link href="{{ asset('assets_super/css/inter.css') }}" rel="stylesheet">
<link href="{{ asset('assets_super/css/instrument-serif.css') }}" rel="stylesheet">
<link href="{{ asset('assets_super/css/sora.css') }}" rel="stylesheet">
<link href="{{ asset('assets_super/css/source-serif-4.css') }}" rel="stylesheet">
<link href="{{ asset('assets_super/css/bootstrap.min.css') }}" rel="stylesheet">
<link href="{{ asset('assets_super/css/bootstrap-icons.min.css') }}" rel="stylesheet">
<link href="{{ asset('assets_super/css/landing.css') }}" rel="stylesheet">
<link href="{{ asset('assets_super/css/landing-two.css') }}" rel="stylesheet">
@include('central.partials.landing-font')

<style>
/* Clip the bottom portion of Tawk.to iframes to hide the footer branding */
iframe[src*="tawk.to"],
iframe[title*="chat"] {
    clip-path: inset(0px 0px 18px 0px) !important;
}
</style>

<!-- Google Analytics -->

<!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-B46STXR0SQ"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'G-B46STXR0SQ');
</script>

<!-- Google tag (gtag.js) --> <script async src="https://www.googletagmanager.com/gtag/js?id=AW-308828939"></script> <script> window.dataLayer = window.dataLayer || []; function gtag(){dataLayer.push(arguments);} gtag('js', new Date()); gtag('config', 'AW-308828939'); </script>

<!-- Google Tag -->

<!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=AW-308828939"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'AW-308828939');
</script>

<!-- Google Tag End -->


</head>
<body class="landing-v2">
    {{--  Navbar ───────────── --}}
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
                @if($generalSettings->show_site_name ?? true)
                    <span class="brand-text">{{ $appName }}</span>
                @endif
            </a>

            {{-- Desktop nav links  floating pill --}}
            <div class="nav-pill" id="navPill">
                <ul class="navbar-nav">
                    @if(!empty($features['is_active']))
                        <li class="nav-item"><a class="nav-link" href="#features">{{ __('landing.features') }}</a></li>
                    @endif
                    @if(!empty($pricing['is_active']))
                        <li class="nav-item"><a class="nav-link" href="#pricing">{{ __('landing.pricing') }}</a></li>
                    @endif
                    @if(!empty($howItWorks['is_active']))
                        <li class="nav-item"><a class="nav-link" href="#how-it-works">{{ __('landing.how_it_works') }}</a></li>
                    @endif
                    @if(!empty($testimonials))
                        <li class="nav-item"><a class="nav-link" href="#testimonials">{{ __('landing.testimonials') }}</a></li>
                    @endif
<!--                    @if(!empty($faqs))
                        <li class="nav-item"><a class="nav-link" href="#faq">{{ __('landing.faq') }}</a></li>
                    @endif
-->
                </ul>
                <span class="nav-indicator" id="navIndicator"></span>
            </div>

            <div class="navbar-actions">
                @if(isset($languages) && $languages->count() > 1)
                <div class="lang-switcher" id="langSwitcher">
                    <button type="button" class="lang-btn" id="langSwitcherBtn">
                        <i class="bi bi-globe2"></i>
                        {{ strtoupper($currentLocale ?? app()->getLocale()) }}
                        <i class="bi bi-chevron-down chevron-xs"></i>
                    </button>
                    <div class="lang-dropdown">
                        @foreach($languages as $lang)
                        <form method="POST" action="{{ route('central.locale', $lang->locale) }}">
                            @csrf
                            <button type="submit" class="{{ ($currentLocale ?? app()->getLocale()) === $lang->locale ? 'active' : '' }}">
                                @if($lang->flag)
                                    <img src="{{ asset('flags/' . $lang->flag) }}" alt="" class="flag-icon">
                                @endif
                                {{ $lang->name }}
                            </button>
                        </form>
                        @endforeach
                    </div>
                </div>
                @endif
                @if($footer->show_admin_login ?? false)
                    <a class="nav-btn nav-btn-ghost" href="{{ route('central.login') }}">{{ __('landing.admin_login') }}</a>
                @endif
                <a class="nav-btn nav-btn-primary" href="{{ route('central.register') }}">{{ __('landing.sign_up_free') }} <i class="bi bi-arrow-right"></i></a>
            </div>

            <button class="navbar-toggler" type="button" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon">
                    <span class="hamburger-line"></span>
                </span>
            </button>

            {{-- Mobile fullscreen menu --}}
            <div class="navbar-collapse" id="navbarMain">
                <ul class="navbar-nav">
                    @if(!empty($features['is_active']))
                        <li class="nav-item"><a class="nav-link" href="#features"><i class="bi bi-grid-3x3-gap"></i> {{ __('landing.features') }}</a></li>
                    @endif
                    @if(!empty($pricing['is_active']))
                        <li class="nav-item"><a class="nav-link" href="#pricing"><i class="bi bi-currency-dollar"></i> {{ __('landing.pricing') }}</a></li>
                    @endif
                    @if(!empty($howItWorks['is_active']))
                        <li class="nav-item"><a class="nav-link" href="#how-it-works"><i class="bi bi-diagram-3"></i> {{ __('landing.how_it_works') }}</a></li>
                    @endif
                    @if(!empty($testimonials))
                        <li class="nav-item"><a class="nav-link" href="#testimonials"><i class="bi bi-chat-quote"></i> {{ __('landing.testimonials') }}</a></li>
                    @endif
                    @if(!empty($faqs))
                        <li class="nav-item"><a class="nav-link" href="#faq"><i class="bi bi-question-circle"></i> {{ __('landing.faq') }}</a></li>
                    @endif
                </ul>
                <div class="mobile-nav-actions">
                    @if(isset($languages) && $languages->count() > 1)
                    <div class="lang-switcher" id="langSwitcherMobile">
                        <button type="button" class="lang-btn" id="langSwitcherMobileBtn">
                            <i class="bi bi-globe2"></i>
                            {{ strtoupper($currentLocale ?? app()->getLocale()) }}
                            <i class="bi bi-chevron-down chevron-xs"></i>
                        </button>
                        <div class="lang-dropdown">
                            @foreach($languages as $lang)
                            <form method="POST" action="{{ route('central.locale', $lang->locale) }}">
                                @csrf
                                <button type="submit" class="{{ ($currentLocale ?? app()->getLocale()) === $lang->locale ? 'active' : '' }}">
                                    @if($lang->flag)
                                        <img src="{{ asset('flags/' . $lang->flag) }}" alt="" class="flag-icon">
                                    @endif
                                    {{ $lang->name }}
                                </button>
                            </form>
                            @endforeach
                        </div>
                    </div>
                    @endif
                    @if($footer->show_admin_login ?? false)
                        <a class="btn-cta btn-cta-outline" href="{{ route('central.login') }}">{{ __('landing.admin_login') }}</a>
                    @endif
                    <a class="btn-cta btn-cta-accent" href="{{ route('central.register') }}">{{ __('landing.sign_up_free') }} <i class="bi bi-arrow-right"></i></a>
                </div>
            </div>
        </div>
    </nav>

    <main>
        {{--  Hero ─────────────── --}}
        @if($hero)
        <section id="hero" class="hero hero-clean @if($hero->background_image) hero-clean--photo bg-cover-center @endif" @if($hero->background_image) style="background-image: url('{{ asset($hero->background_image) }}')" @endif>
            <div class="hero-clean__bg" aria-hidden="true"></div>
            <div class="hero-clean__grid" aria-hidden="true"></div>
            <div class="hero-clean__wave" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 100" preserveAspectRatio="none">
                    <path fill="url(#heroWaveGrad)" d="M0,32 C240,92 480,8 720,44 S1200,4 1440,52 L1440,100 L0,100 Z"/>
                    <defs>
                        <linearGradient id="heroWaveGrad" x1="0" y1="0" x2="0" y2="1">
                            <stop offset="0%" stop-color="#0d9488" stop-opacity="0.06"/>
                            <stop offset="100%" stop-color="#0d9488" stop-opacity="0.02"/>
                        </linearGradient>
                    </defs>
                </svg>
            </div>
            <div class="container position-relative hero-clean__inner">
                <div class="row align-items-center g-4 g-lg-5 {{ $hero->hero_image ? '' : 'justify-content-center text-center' }}">
                    <div class="col-lg-6 {{ $hero->hero_image ? '' : 'col-xl-7' }}">
                        <div class="hero-copy">
                            @if($hero->subtitle)
                                <p class="hero-eyebrow">
                                    <span class="hero-eyebrow__rule" aria-hidden="true"></span>
                                    <span class="hero-eyebrow__label">{{ $hero->subtitle }}</span>
                                </p>
                            @endif
                            <h1 class="hero-title">{!! $hero->title !!}</h1>
                            @if($hero->description)
                                <p class="hero-lead">{{ $hero->description }}</p>
                            @endif
                            <div class="hero-actions">
                                <div class="hero-actions__cluster">
                                    @if($hero->primary_button_text)
                                        <a href="{{ $hero->primary_button_url ?? route('central.register') }}" class="btn-cta btn-cta-lg btn-cta-accent hero-btn-primary">
                                            {{ $hero->primary_button_text }} <i class="bi bi-arrow-right" aria-hidden="true"></i>
                                        </a>
                                    @endif
                                    @if($hero->secondary_button_text)
                                        <a href="{{ $hero->secondary_button_url ?? route('central.register') }}" class="btn-hero-secondary hero-btn-secondary">
                                            {{ $hero->secondary_button_text }}
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    @if($hero->hero_image)
                    <div class="col-lg-6">
                        <div class="hero-visual" role="presentation">
                            <div class="hero-visual__glow" aria-hidden="true"></div>
                            <div class="hero-visual__frame">
                                <img src="{{ asset($hero->hero_image) }}" alt="{{ strip_tags($hero->title) }}" decoding="async">
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
            @if(session('success') && session('tenant_url'))
                <div class="container mt-4">
                    <div class="alert alert-custom text-center">
                        {{ session('message') }}<br>
                        <a href="{{ session('tenant_url') }}">{{ session('tenant_url') }}</a>
                    </div>
                </div>
            @endif
        </section>
        @endif

        {{--  Trust Bar ───────────── --}}
        @if(!empty($stats) && $stats->isNotEmpty())
        <section class="trust-bar" aria-label="{{ __('landing.trust_bar_aria') }}">
            <div class="trust-bar__accent" aria-hidden="true"></div>
            <div class="container trust-bar__container">
                <p class="trust-bar__eyebrow">
                    <span class="trust-bar__eyebrow-rule" aria-hidden="true"></span>
                    <span class="trust-bar__eyebrow-text">{{ __('landing.trust_bar_eyebrow') }}</span>
                </p>
                <div class="trust-stats" role="list">
                    @foreach($stats as $i => $stat)
                    <div class="trust-stat fade-up stagger-{{ ($i % 4) + 1 }}" role="listitem">
                        <div class="trust-stat__value">{{ $stat->value }}</div>
                        <div class="trust-stat__label">{{ $stat->label }}</div>
                    </div>
                    @endforeach
                </div>
            </div>
        </section>
        @endif

        {{--  Features ──────────────────── --}}
        @if(!empty($features['is_active']) && $features['items']->isNotEmpty())
        <section id="features" class="features-section">
            <div class="features-section__accent" aria-hidden="true"></div>
            <div class="container">
                <header class="features-section__head text-center">
                    <p class="features-section__eyebrow">
                        <span class="features-section__eyebrow-rule" aria-hidden="true"></span>
                        <span class="features-section__eyebrow-text">{{ __('landing.features') }}</span>
                    </p>
                    @if($features['section'])
                        <h2 class="features-section__title fade-up">{{ $features['section']->section_title }}</h2>
                        <p class="features-section__lead fade-up">{{ $features['section']->section_subtitle }}</p>
                    @endif
                </header>
                <div class="row g-4 features-section__grid">
                    @foreach($features['items'] as $i => $feature)
                    <div class="col-md-6 col-lg-{{ count($features['items']) <= 3 ? '4' : '3' }}">
                        <article class="feature-card fade-up stagger-{{ ($i % 4) + 1 }}">
                            @if($feature->image)
                                <div class="feature-card__icon">
                                    <img src="{{ asset($feature->image) }}" alt="" class="landing-feature-img" decoding="async">
                                </div>
                            @elseif($feature->icon)
                                <div class="feature-card__icon" aria-hidden="true"><i class="{{ $feature->icon }}"></i></div>
                            @else
                                <div class="feature-card__icon" aria-hidden="true"><i class="bi bi-star"></i></div>
                            @endif
                            <h3 class="feature-card__title">{{ $feature->title }}</h3>
                            @if($feature->description)
                                <p class="feature-card__desc">{{ $feature->description }}</p>
                            @endif
                        </article>
                    </div>
                    @endforeach
                </div>
            </div>
        </section>
        @endif
        
        



        {{-- ─ Platform ──────────────── --}}
        @if($l3PlatformVisible)
        @php
            $pf0 = $l3FeatureItems->get(0);
            $pf1 = $l3FeatureItems->get(1);
            $pf2 = $l3FeatureItems->get(2);
            $pf3 = $l3FeatureItems->get(3);
        @endphp
        <section id="platform" class="platform-section">
            <div class="platform-section__glow platform-section__glow--1" aria-hidden="true"></div>
            <div class="platform-section__glow platform-section__glow--2" aria-hidden="true"></div>
            <div class="container position-relative">
                <div class="platform-section__head">
                    <h2 class="platform-section__title fade-up">One Toolkit, Every Task</h2>
                    <p class="platform-section__lead fade-up">From stock to sales, everything's covered.</p>
                </div>
                <div class="row g-4">
                    <div class="col-md-8 fade-up stagger-1">
                        <div class="platform-card platform-card--lg">
                            <div class="platform-card__body">
                                <h3 class="platform-card__title">{{ $pf0?->title ?? __('landing.feat_warehouse') }}</h3>
                                <p class="platform-card__desc">{{ $pf0?->description ?? __('landing.hiw_data_desc') }}</p>
                            </div>
                            <div class="platform-map">
                                <span class="platform-map__dot platform-map__dot--a"></span>
                                <span class="platform-map__dot platform-map__dot--b"></span>
                                <span class="platform-map__dot platform-map__dot--c"></span>
                                <span class="platform-map__arc platform-map__arc--a"></span>
                                <span class="platform-map__arc platform-map__arc--b"></span>
                                <div class="platform-map__tag">
                                    <span class="platform-map__tag-value">{{ __('landing.unlimited') }}</span> (NYC)
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 fade-up stagger-2">
                        <div class="platform-card">
                            <div class="platform-card__body">
                                <h3 class="platform-card__title">{{ $pf1?->title ?? __('landing.feat_pos') }}</h3>
                                <p class="platform-card__desc">{{ $pf1?->description ?? __('landing.feat_ready') }}</p>
                            </div>
                            <div class="platform-pos">
                                <div class="platform-pos__total">
                                    <span>Total</span>
                                    <span class="platform-pos__amount">{{ $currencySymbol }}142.50</span>
                                </div>
                                <div class="platform-pos__keys">
                                    @for($n = 1; $n <= 6; $n++)
                                    <div class="platform-pos__key">{{ $n }}</div>
                                    @endfor
                                    <div class="platform-pos__cta">{{ __('landing.get_started') }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 fade-up stagger-3">
                        <div class="platform-card">
                            <div class="platform-card__body">
                                <h3 class="platform-card__title">{{ $pf2?->title ?? __('landing.stat_analytics') }}</h3>
                                <p class="platform-card__desc">{{ $pf2?->description ?? __('landing.testimonials_subtitle') }}</p>
                            </div>
                            <div class="platform-stats">
                                @forelse(isset($stats) && $stats->isNotEmpty() ? $stats->take(3) : collect() as $st)
                                <div class="platform-stats__row">
                                    <span class="platform-stats__label"><i class="platform-stats__bullet"></i>{{ $st->label }}</span>
                                    <span class="platform-stats__value">{{ $st->value }}</span>
                                </div>
                                @empty
                                <div class="platform-stats__row">
                                    <span class="platform-stats__label"><i class="platform-stats__bullet"></i>{{ __('landing.stat_secure') }}</span>
                                    <span class="platform-stats__value">42.8%</span>
                                </div>
                                <div class="platform-stats__row">
                                    <span class="platform-stats__label"><i class="platform-stats__bullet"></i>{{ __('landing.yearly') }}</span>
                                    <span class="platform-stats__value">12 {{ __('landing.mo') }}</span>
                                </div>
                                <div class="platform-stats__row">
                                    <span class="platform-stats__label"><i class="platform-stats__bullet"></i>{{ __('landing.save') }}</span>
                                    <span class="platform-stats__value">1.2%</span>
                                </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                    <div class="col-md-8 fade-up stagger-1">
                        <div class="platform-card platform-card--lg platform-card--row">
                            <div class="platform-card__body">
                                <h3 class="platform-card__title">{{ $pf3?->title ?? __('landing.create_workspace') }}</h3>
                                <p class="platform-card__desc">{{ $pf3?->description ?? __('landing.reg_subtitle') }}</p>
                                <a href="{{ route('central.register') }}" class="platform-card__link">
                                    {{ __('landing.sign_up') }} <i class="bi bi-arrow-right" aria-hidden="true"></i>
                                </a>
                            </div>
                            <div class="platform-icons">
                                <span class="platform-icons__item"><i class="bi bi-cart3"></i></span>
                                <span class="platform-icons__item platform-icons__item--up"><i class="bi bi-wallet2"></i></span>
                                <span class="platform-icons__item"><i class="bi bi-envelope"></i></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        
        <style>
        .platform-section {
            position: relative;
            overflow: hidden;
            padding: clamp(4rem, 8vw, 6rem) 0;
            background: var(--dark, #1d1d1f);
            color: #fff;
        }
        .platform-section__glow {
            position: absolute;
            width: 480px;
            height: 480px;
            border-radius: 50%;
            filter: blur(120px);
            pointer-events: none;
        }
        .platform-section__glow--1 { top: -10%; right: -8%; background: rgba(13, 148, 136, .22); }
        .platform-section__glow--2 { bottom: -12%; left: -8%; background: rgba(13, 148, 136, .14); }
        .platform-section__head { position: relative; z-index: 1; max-width: 42rem; margin-bottom: 3rem; }
        .platform-section__title {
            font-family: var(--font-display, serif);
            font-size: clamp(1.85rem, 3.5vw, 2.75rem);
            font-weight: 400;
            letter-spacing: -.02em;
            margin: 0 0 .85rem;
            color: #fff;
        }
        .platform-section__lead { color: rgba(255,255,255,.62); font-size: 1.05rem; margin: 0; }
        
        .platform-card {
            height: 100%;
            padding: 1.75rem;
            border-radius: 20px;
            background: rgba(255,255,255,.05);
            border: 1px solid rgba(255,255,255,.09);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            gap: 1.5rem;
        }
        .platform-card--row { flex-direction: row; align-items: center; justify-content: space-between; gap: 2rem; flex-wrap: wrap; }
        .platform-card__title { font-size: 1.15rem; font-weight: 600; letter-spacing: -.01em; margin: 0 0 .4rem; color: #fff; }
        .platform-card__desc { font-size: .875rem; color: rgba(255,255,255,.55); margin: 0; max-width: 26rem; }
        .platform-card__link {
            display: inline-flex; align-items: center; gap: .35rem;
            margin-top: 1rem; font-size: .875rem; font-weight: 600;
            color: var(--primary-light, #2dd4bf); text-decoration: none;
        }
        .platform-card__link:hover { color: #fff; }
        
        .platform-map {
            position: relative; height: 200px; border-radius: 14px;
            background: rgba(0,0,0,.25); border: 1px solid rgba(255,255,255,.08);
            overflow: hidden;
        }
        .platform-map__dot { position: absolute; width: 10px; height: 10px; border-radius: 50%; }
        .platform-map__dot--a { top: 32px; left: 48px; background: #2dd4bf; box-shadow: 0 0 15px rgba(45,212,191,.8); }
        .platform-map__dot--b { top: 80px; left: 33%; background: #34d399; box-shadow: 0 0 15px rgba(52,211,153,.8); }
        .platform-map__dot--c { bottom: 48px; right: 25%; background: #60a5fa; box-shadow: 0 0 15px rgba(96,165,250,.8); }
        .platform-map__arc { position: absolute; border: dashed rgba(255,255,255,.18); opacity: .6; }
        .platform-map__arc--a { top: 36px; left: 56px; width: 128px; height: 48px; border-width: 2px 0 0 2px; border-radius: 100px 0 0 0; }
        .platform-map__arc--b { bottom: 56px; left: 33%; width: 160px; height: 80px; border-width: 0 2px 2px 0; border-radius: 0 0 100px 0; }
        .platform-map__tag {
            position: absolute; top: 64px; left: 128px;
            padding: .4rem .75rem; font-size: .75rem; border-radius: 8px;
            background: rgba(255,255,255,.08); border: 1px solid rgba(255,255,255,.1);
        }
        .platform-map__tag-value { color: #34d399; font-weight: 600; }
        
        .platform-pos__total { display: flex; justify-content: space-between; align-items: center; padding-bottom: .5rem; margin-bottom: 1rem; border-bottom: 1px solid rgba(255,255,255,.09); font-size: .9rem; }
        .platform-pos__amount { font-size: 1.15rem; font-weight: 700; color: #34d399; }
        .platform-pos__keys { display: grid; grid-template-columns: repeat(3, 1fr); gap: .5rem; }
        .platform-pos__key {
            padding: .5rem 0; text-align: center; border-radius: 6px;
            background: rgba(255,255,255,.06); font-size: .9rem; font-weight: 500;
            cursor: pointer; transition: background .2s ease;
        }
        .platform-pos__key:hover { background: rgba(255,255,255,.12); }
        .platform-pos__cta {
            grid-column: span 3; margin-top: .35rem; padding: .6rem 0; text-align: center;
            border-radius: 6px; background: var(--primary, #0d9488); color: #fff; font-weight: 600;
            cursor: pointer; transition: background .2s ease;
        }
        .platform-pos__cta:hover { background: var(--primary-dark, #0f766e); }
        
        .platform-stats__row { display: flex; justify-content: space-between; align-items: center; padding: .6rem .75rem; border-radius: 10px; background: rgba(0,0,0,.2); border: 1px solid rgba(255,255,255,.07); margin-bottom: .6rem; }
        .platform-stats__row:last-child { margin-bottom: 0; }
        .platform-stats__label { display: flex; align-items: center; gap: .5rem; font-size: .85rem; color: rgba(255,255,255,.75); }
        .platform-stats__bullet { width: 8px; height: 8px; border-radius: 50%; background: #2dd4bf; flex-shrink: 0; }
        .platform-stats__value { font-weight: 600; font-size: .9rem; }
        
        .platform-icons { display: flex; gap: 1rem; opacity: .5; flex-shrink: 0; }
        .platform-icons__item {
            width: 48px; height: 48px; border-radius: 14px; display: flex;
            align-items: center; justify-content: center; font-size: 1.3rem;
            background: rgba(0,0,0,.25); border: 1px solid rgba(255,255,255,.1);
        }
        .platform-icons__item--up { transform: translateY(-16px); }
        
        @media (max-width: 767px) {
            .platform-card--row { flex-direction: column; align-items: flex-start; }
        }
        </style>
        @endif


        

        {{-- ─ Pricing (Apple-minimal) ────────── --}}
        @if(!empty($pricing['is_active']))
        <section id="pricing" class="pricing-section">
            <div class="pricing-section__canvas" aria-hidden="true"></div>
            <div class="container">
                <header class="pricing-section__head text-center">
                    <p class="pricing-section__eyebrow">
                        <span class="pricing-section__eyebrow-rule" aria-hidden="true"></span>
                        <span class="pricing-section__eyebrow-text">{{ __('landing.pricing') }}</span>
                    </p>
                    @if($pricing['settings'])
                        <h2 class="pricing-section__title fade-up">{{ $pricing['settings']->section_title }}</h2>
                        <p class="pricing-section__lead fade-up">{{ $pricing['settings']->section_subtitle }}</p>
                    @endif
                </header>

                @if(!empty($pricing['plans']))
                    <div class="row g-4 g-lg-5 justify-content-center pricing-section__grid">
                        @foreach($pricing['plans'] as $i => $plan)
                        <div class="col-lg-3 col-md-6 d-flex fade-up stagger-{{ ($i % 4) + 1 }}">
                            <article class="pricing-tier {{ $i === 1 ? 'pricing-tier--featured' : '' }}">
                                @if($i === 1)
                                    <p class="pricing-tier__tag">{{ __('landing.most_popular') }}</p>
                                @endif
                                <h3 class="pricing-tier__name">{{ $plan->name }}</h3>
                                <p class="pricing-tier__hint">
                                    @if($plan->price == 0)
                                        {{ __('landing.perfect_to_start') ?? 'Perfect to get started' }}
                                    @else
                                        {{ __('landing.for_growing_teams') ?? 'For growing teams' }}
                                    @endif
                                </p>
                                <div class="pricing-tier__price-block">
                                    @if($plan->price == 0 && $plan->yearly_price == 0)
                                        <span class="pricing-tier__amount pricing-tier__amount--free">{{ __('landing.free') }}</span>
                                    @else
                                        <span class="pricing-tier__amount">
                                            <span class="pricing-tier__currency">{{ $currencySymbol }}</span>{{ $plan->price == floor($plan->price) ? number_format($plan->price, 0) : number_format($plan->price, 2) }}
                                        </span>
                                        <span class="pricing-tier__period">/ {{ __('landing.mo') }}</span>
                                    @endif
                                </div>
                                @if($plan->yearly_price > 0)
                                    <p class="pricing-tier__yearly fs-6 fw-medium my-2">
                                        {{ __('landing.or') }} {{ $currencySymbol }}{{ $plan->yearly_price == floor($plan->yearly_price) ? number_format($plan->yearly_price, 0) : number_format($plan->yearly_price, 2) }} / {{ __('landing.yr') }}
                                        @if($plan->getYearlySavingsPercent() > 0)
                                            <span class="pricing-tier__save badge bg-success text-white rounded-pill px-2 py-1 ms-1">
                                                {{ __('landing.save') }} {{ $plan->getYearlySavingsPercent() }}%
                                            </span>
                                        @endif
                                    </p>
                                @endif
                                @php
                                    $limits = $plan->limits ?? [];
                                    $planFeatures = $plan->features ?? [];
                                @endphp
                                <ul class="pricing-tier__features">
                                    @foreach($limits as $key => $value)
                                        @php $meta = \App\Models\Central\Plan::AVAILABLE_LIMITS[$key] ?? null; @endphp
                                        @if($meta)
                                            <li>
                                                <i class="bi bi-check-lg pricing-tier__check" aria-hidden="true"></i>
                                                <span>{{ $value == -1 ? __('landing.unlimited') : $value }} {{ $meta['label'] }}</span>
                                            </li>
                                        @endif
                                    @endforeach
                                    @foreach($planFeatures as $fKey)
                                        @php $fMeta = \App\Models\Central\Plan::AVAILABLE_FEATURES[$fKey] ?? null; @endphp
                                        @if($fMeta)
                                            <li>
                                                <i class="bi bi-check-lg pricing-tier__check" aria-hidden="true"></i>
                                                <span>{{ $fMeta['label'] }}</span>
                                            </li>
                                        @endif
                                    @endforeach
                                </ul>
                                <a href="{{ route('central.register', ['plan' => $plan->id]) }}" class="pricing-tier__cta">
                                    {{ $plan->isFree() ? __('landing.get_started') : ($plan->hasTrial() ? __('landing.start_free_trial') : __('landing.choose_plan')) }}
                                </a>
                                
                                {{-- 3. No Credit Card Required (ADD THIS BELOW THE BUTTON) --}}
                                <p class="text-muted text-center small mt-2 mb-0" style="font-size: 0.8125rem;">
                                    <i class="bi bi-shield-check text-success me-1" aria-hidden="true"></i> No credit card required
                                </p>
                                
                            </article>
                        </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </section>
        @endif

        {{-- ─ How It Works ───────────── --}}
        @if(!empty($howItWorks['is_active']))
        <section class="hiw-section" id="how-it-works">
            <div class="hiw-section__accent" aria-hidden="true"></div>
            <div class="container">
                <header class="hiw-section__head text-center">
                    <p class="hiw-section__eyebrow">
                        <span class="hiw-section__eyebrow-rule" aria-hidden="true"></span>
                        <span class="hiw-section__eyebrow-text">{{ $howItWorks['section']->section_label ?? __('landing.how_it_works') }}</span>
                    </p>
                    <h2 class="hiw-section__title fade-up">{{ $howItWorks['section']->section_title ?? __('landing.how_it_works_title') }}</h2>
                    <p class="hiw-section__lead fade-up">{{ $howItWorks['section']->section_subtitle ?? __('landing.how_it_works_subtitle') }}</p>
                </header>
                <div class="hiw-section__grid">
                    @forelse($howItWorks['steps'] as $i => $step)
                    <article class="hiw-card fade-up stagger-{{ ($i % 3) + 1 }}">
                        <span class="hiw-card__step">{{ str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT) }}</span>
                        <div class="hiw-card__icon" aria-hidden="true">
                            <i class="{{ $step->icon ?? 'bi bi-check-circle' }}"></i>
                        </div>
                        <h3 class="hiw-card__title">{{ $step->title }}</h3>
                        <p class="hiw-card__desc">{{ $step->description }}</p>
                    </article>
                    @empty
                    {{-- Fallback to translation strings if no steps in DB --}}
                    <article class="hiw-card fade-up stagger-1">
                        <span class="hiw-card__step">01</span>
                        <div class="hiw-card__icon" aria-hidden="true"><i class="bi bi-arrow-repeat"></i></div>
                        <h3 class="hiw-card__title">{{ __('landing.hiw_features_title') }}</h3>
                        <p class="hiw-card__desc">{{ __('landing.hiw_features_desc') }}</p>
                    </article>
                    <article class="hiw-card fade-up stagger-2">
                        <span class="hiw-card__step">02</span>
                        <div class="hiw-card__icon" aria-hidden="true"><i class="bi bi-credit-card"></i></div>
                        <h3 class="hiw-card__title">{{ __('landing.hiw_pricing_title') }}</h3>
                        <p class="hiw-card__desc">{{ __('landing.hiw_pricing_desc') }}</p>
                    </article>
                    <article class="hiw-card fade-up stagger-3">
                        <span class="hiw-card__step">03</span>
                        <div class="hiw-card__icon" aria-hidden="true"><i class="bi bi-shield-check"></i></div>
                        <h3 class="hiw-card__title">{{ __('landing.hiw_delete_title') }}</h3>
                        <p class="hiw-card__desc">{{ __('landing.hiw_delete_desc') }}</p>
                    </article>
                    <article class="hiw-card fade-up stagger-1">
                        <span class="hiw-card__step">04</span>
                        <div class="hiw-card__icon" aria-hidden="true"><i class="bi bi-database-lock"></i></div>
                        <h3 class="hiw-card__title">{{ __('landing.hiw_data_title') }}</h3>
                        <p class="hiw-card__desc">{{ __('landing.hiw_data_desc') }}</p>
                    </article>
                    <article class="hiw-card fade-up stagger-2">
                        <span class="hiw-card__step">05</span>
                        <div class="hiw-card__icon" aria-hidden="true"><i class="bi bi-clock-history"></i></div>
                        <h3 class="hiw-card__title">{{ __('landing.hiw_trial_title') }}</h3>
                        <p class="hiw-card__desc">{{ __('landing.hiw_trial_desc') }}</p>
                    </article>
                    <article class="hiw-card fade-up stagger-3">
                        <span class="hiw-card__step">06</span>
                        <div class="hiw-card__icon" aria-hidden="true"><i class="bi bi-arrow-up-circle"></i></div>
                        <h3 class="hiw-card__title">{{ __('landing.hiw_upgrade_title') }}</h3>
                        <p class="hiw-card__desc">{{ __('landing.hiw_upgrade_desc') }}</p>
                    </article>
                    @endforelse
                </div>
            </div>
        </section>
        
        <style>
        .hiw-section__grid {
            grid-template-columns: 1fr !important;
        }
        @media (min-width: 768px) {
            .hiw-section__grid { grid-template-columns: repeat(2, 1fr) !important; }
        }
        @media (min-width: 992px) {
            .hiw-section__grid { grid-template-columns: repeat(3, 1fr) !important; }
        }
        </style>
        @endif

        {{--  Testimonials ───────────── --}}
        @if(!empty($testimonials))
        <section id="testimonials" class="testimonials-section">
            <div class="testimonials-section__accent" aria-hidden="true"></div>
            <div class="container">
                <header class="testimonials-section__head text-center">
                    <p class="testimonials-section__eyebrow">
                        <span class="testimonials-section__eyebrow-rule" aria-hidden="true"></span>
                        <span class="testimonials-section__eyebrow-text">{{ __('landing.testimonials') }}</span>
                    </p>
                    <h2 class="testimonials-section__title fade-up">{{ __('landing.loved_by_businesses') }}</h2>
                    <p class="testimonials-section__lead fade-up">{{ __('landing.testimonials_subtitle') }}</p>
                </header>
                <div class="row g-4 testimonials-section__grid">
                    @foreach($testimonials as $i => $testimonial)
                    <div class="col-lg-4 col-md-6 d-flex fade-up stagger-{{ ($i % 3) + 1 }}">
                        <article class="testimonial-card">
                            @if($testimonial->rating)
                            <div class="testimonial-card__stars" aria-label="{{ $testimonial->rating }} / 5">
                                @for($s = 1; $s <= 5; $s++)
                                    <i class="bi bi-star{{ $s <= $testimonial->rating ? '-fill' : '' }}" aria-hidden="true"></i>
                                @endfor
                            </div>
                            @endif
                            <blockquote class="testimonial-card__quote">
                                <p>“{{ $testimonial->review }}</p>
                            </blockquote>
                            <footer class="testimonial-card__author">
                                <div class="testimonial-card__avatar">
                                    @if($testimonial->avatar)
                                        <img src="{{ asset($testimonial->avatar) }}" alt="" decoding="async">
                                    @else
                                        <span aria-hidden="true">{{ strtoupper(mb_substr($testimonial->client_name, 0, 1)) }}</span>
                                    @endif
                                </div>
                                <div class="testimonial-card__meta">
                                    <p class="testimonial-card__name">{{ $testimonial->client_name }}</p>
                                    @if($testimonial->company_name)
                                        <span class="testimonial-card__role">{{ $testimonial->company_name }}</span>
                                    @endif
                                </div>
                            </footer>
                        </article>
                    </div>
                    @endforeach
                </div>
            </div>
        </section>
        @endif
<!--
        {{-- ─ FAQ ────────────── --}}
        @if(!empty($faqs))
        <section id="faq" class="faq-section">
            <div class="faq-section__accent" aria-hidden="true"></div>
            <div class="container">
                <header class="faq-section__head text-center">
                    <p class="faq-section__eyebrow">
                        <span class="faq-section__eyebrow-rule" aria-hidden="true"></span>
                        <span class="faq-section__eyebrow-text">{{ __('landing.faq') ?? 'FAQ' }}</span>
                    </p>
                    <h2 class="faq-section__title fade-up">{{ __('landing.faq_title') }}</h2>
                    <p class="faq-section__lead fade-up">{{ __('landing.faq_subtitle') }}</p>
                </header>
                <div class="faq-section__wrap">
                    <div class="faq-section__panel">
                        @foreach($faqs as $i => $faq)
                        <details class="faq-item fade-up stagger-{{ ($i % 4) + 1 }}">
                            <summary>
                                <span class="faq-item__label">{{ $faq->question }}</span>
                                <i class="bi bi-chevron-down faq-item__chevron" aria-hidden="true"></i>
                            </summary>
                            <div class="faq-item__answer">{!! nl2br(e($faq->answer)) !!}</div>
                        </details>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>
        @endif
-->
        {{--  CTA ──────────────── --}}
        @if($cta)
        <section class="cta-section @if($cta->background_image) cta-section--photo bg-cover-center @endif" @if($cta->background_image) style="background-image: url('{{ asset($cta->background_image) }}')" @endif>
            <div class="cta-section__scrim" aria-hidden="true"></div>
            <div class="cta-section__glow" aria-hidden="true"></div>
            <div class="container position-relative">
                <div class="cta-section__content text-center mx-auto">
                    <h2 class="cta-section__title fade-up">{{ $cta->title }}</h2>
                    @if($cta->subtitle)
                        <p class="cta-section__lead fade-up">{{ $cta->subtitle }}</p>
                    @endif
                    @if($cta->button_text)
                        <a href="{{ $cta->button_url ?? route('central.register') }}" class="cta-section__btn fade-up">
                            {{ $cta->button_text }}
                            <i class="bi bi-arrow-right" aria-hidden="true"></i>
                        </a>
                    @endif
                </div>
            </div>
        </section>
        @endif
    </main>

    {{-- ─ Footer ─────────────── --}}
    <footer class="landing-footer">
        <div class="landing-footer__accent" aria-hidden="true"></div>
        <div class="container position-relative">
            <div class="row landing-footer__grid g-4 g-lg-5">
                <div class="col-lg-4 mb-2 mb-lg-0">
                    @if($generalSettings->show_site_name ?? true)
                        <a class="landing-footer__brand" href="{{ route('central.welcome') }}">{{ $appName }}</a>
                    @endif
                    @if($footer && $footer->footer_about)
                        <p class="landing-footer__about">{{ $footer->footer_about }}</p>
                    @endif
                    @if($footer)
                    <div class="landing-footer__social">
                        @foreach([
                            'facebook' => ['icon' => 'bi-facebook', 'label' => 'Facebook'],
                            'twitter' => ['icon' => 'bi-twitter-x', 'label' => 'X'],
                            'linkedin' => ['icon' => 'bi-linkedin', 'label' => 'LinkedIn'],
                            'instagram' => ['icon' => 'bi-instagram', 'label' => 'Instagram'],
                            'youtube' => ['icon' => 'bi-youtube', 'label' => 'YouTube'],
                        ] as $social => $meta)
                            @if($footer->$social)
                                <a href="{{ $footer->$social }}" class="landing-footer__social-link" target="_blank" rel="noopener noreferrer" aria-label="{{ $meta['label'] }}">
                                    <i class="bi {{ $meta['icon'] }}" aria-hidden="true"></i>
                                </a>
                            @endif
                        @endforeach
                    </div>
                    @endif
                </div>
                <div class="col-lg-4 col-md-6">
                    <p class="landing-footer__heading">{{ __('landing.quick_links') }}</p>
                    <nav class="landing-footer__nav" aria-label="{{ __('landing.quick_links') }}">
<!--                        <a href="{{ route('central.welcome') }}">{{ __('landing.home') }}</a>
                        <a href="#features">{{ __('landing.features') }}</a>
                        <a href="#pricing">{{ __('landing.pricing') }}</a>
                        <a href="{{ route('central.register') }}">{{ __('landing.sign_up') }}</a>
                        @if($footer->show_admin_login ?? false)
                        <a href="{{ route('central.login') }}">{{ __('landing.admin_login') }}</a>
                        @endif
-->                     
                    <a href="{{ route('central.privacy-policy') }}">{{ __('landing.privacy_policy') }}</a>
                        <a href="{{ route('central.terms-conditions') }}">{{ __('landing.terms_and_conditions') }}</a>
<!--                        <a href="#" id="cookiePreferencesLink">{{ __('landing.cookie_preferences_link') }}</a>
-->                    </nav>
                </div>
                <div class="col-lg-4 col-md-6">
                    @if($footer && ($footer->contact_email || $footer->contact_phone || $footer->address))
                    <p class="landing-footer__heading">{{ __('landing.contact') }}</p>
                    <div class="landing-footer__contact">
                        @if($footer->contact_email)
                            <a class="landing-footer__contact-item" href="mailto:{{ $footer->contact_email }}">
                                <i class="bi bi-envelope" aria-hidden="true"></i>
                                <span>{{ $footer->contact_email }}</span>
                            </a>
                        @endif
                        @if($footer->contact_phone)
                            <a class="landing-footer__contact-item" href="tel:{{ preg_replace('/[^0-9+]/', '', $footer->contact_phone) }}">
                                <i class="bi bi-telephone" aria-hidden="true"></i>
                                <span>{{ $footer->contact_phone }}</span>
                            </a>
                        @endif
                        @if($footer->address)
                            <p class="landing-footer__contact-item landing-footer__contact-item--static">
                                <i class="bi bi-geo-alt" aria-hidden="true"></i>
                                <span>{{ $footer->address }}</span>
                            </p>
                        @endif
                    </div>
                    @endif
                </div>
            </div>
            <div class="landing-footer__bottom">
                <p class="landing-footer__legal">{{ $footer->copyright_text ?? ' ' . date('Y') . ' ' . $appName . '. All rights reserved.' }}</p>
            </div>
        </div>
    </footer>

    <script src="{{ asset('assets_super/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets_super/js/landing-two.js') }}"></script>

    {{-- ─ Cookie Consent Banner ───────────── --}}
    <div class="cookie-consent" id="cookieConsent">
        <div class="cookie-consent-header">
            <i class="bi bi-shield-lock"></i>
            <h4>{{ __('landing.cookie_banner_title') }}</h4>
        </div>
        <p class="cookie-consent-text">
            {{ __('landing.cookie_banner_text') }}
            <a href="{{ route('central.privacy-policy') }}#cookies">{{ __('landing.privacy_policy') }}</a>
        </p>
        <div class="cookie-consent-actions">
            <button class="cb-btn cb-btn-accept" id="cookieAcceptBtn">{{ __('landing.cookie_accept_all') }}</button>
            <button class="cb-btn cb-btn-reject" id="cookieRejectBtn">{{ __('landing.cookie_reject_all') }}</button>
            <button class="cb-btn cb-btn-customize" id="cookieCustomizeBtn">{{ __('landing.cookie_customize') }}</button>
        </div>
        <div class="cookie-customize-panel" id="cookieCustomize">
            <div class="cookie-option">
                <div class="cookie-option-info">
                    <h5>{{ __('landing.cookie_necessary') }}</h5>
                    <p>{{ __('landing.cookie_necessary_desc') }}</p>
                </div>
                <label class="cookie-toggle">
                    <input type="checkbox" checked disabled>
                    <span class="slider"></span>
                </label>
            </div>
            <div class="cookie-option">
                <div class="cookie-option-info">
                    <h5>{{ __('landing.cookie_analytics') }}</h5>
                    <p>{{ __('landing.cookie_analytics_desc') }}</p>
                </div>
                <label class="cookie-toggle">
                    <input type="checkbox" id="cookieAnalytics">
                    <span class="slider"></span>
                </label>
            </div>
            <div class="cookie-option">
                <div class="cookie-option-info">
                    <h5>{{ __('landing.cookie_marketing') }}</h5>
                    <p>{{ __('landing.cookie_marketing_desc') }}</p>
                </div>
                <label class="cookie-toggle">
                    <input type="checkbox" id="cookieMarketing">
                    <span class="slider"></span>
                </label>
            </div>
            <div class="cookie-save-row">
                <button class="cb-btn" id="cookieSaveBtn">{{ __('landing.cookie_save_preferences') }}</button>
            </div>
        </div>
    </div>

<script>
(function () {
  var CSS_RULES = `
    /* minimized/pill badge */
    a[style*="border-radius: 100px"][style*="font-size: 12px"],
    /* expanded/fullscreen badge */
    a[style*="font-size: 12px"][style*="font-weight: 400"][style*="padding"] {
      display: none !important;
    }
  `;

  function injectStyle(doc) {
    try {
      if (!doc || doc.getElementById('hide-tawk-badge')) return;
      var style = doc.createElement('style');
      style.id = 'hide-tawk-badge';
      style.textContent = CSS_RULES;
      (doc.head || doc.documentElement).appendChild(style);
    } catch (e) {}
  }

  // JS fallback that doesn't rely on styling at all — catches anything CSS misses
  function textFallback(doc) {
    try {
      doc.querySelectorAll('a').forEach(function (a) {
        var t = (a.textContent || '').toLowerCase();
        var hasLogoSvg = a.querySelector('svg[viewBox="0 0 107 15"]');
        if (t.includes('powered by') || hasLogoSvg) {
          a.style.setProperty('display', 'none', 'important');
        }
      });
    } catch (e) {}
  }

  function process(doc) {
    injectStyle(doc);
    textFallback(doc);
  }

  process(document);

  function handleIframe(iframe) {
    var tryInject = function () {
      try {
        var doc = iframe.contentDocument || (iframe.contentWindow && iframe.contentWindow.document);
        process(doc);
      } catch (e) {}
    };
    tryInject();
    iframe.addEventListener('load', tryInject);
  }

  document.querySelectorAll('iframe').forEach(handleIframe);

  new MutationObserver(function (mutations) {
    mutations.forEach(function (m) {
      m.addedNodes.forEach(function (node) {
        if (node.nodeName === 'IFRAME') handleIframe(node);
        else if (node.querySelectorAll) node.querySelectorAll('iframe').forEach(handleIframe);
      });
    });
  }).observe(document.documentElement, { childList: true, subtree: true });

  // Poll for the first few seconds to catch late DOM writes / re-renders,
  // which mobile's expanded view seems to trigger more often
  var tick = setInterval(function () {
    process(document);
    document.querySelectorAll('iframe').forEach(function (f) {
      try {
        process(f.contentDocument || (f.contentWindow && f.contentWindow.document));
      } catch (e) {}
    });
  }, 100);
  setTimeout(function () { clearInterval(tick); }, 8000);
})();
</script>

<script type="text/javascript">
var Tawk_API = Tawk_API || {}, Tawk_LoadStart = new Date();
(function(){
  var s1 = document.createElement("script"), s0 = document.getElementsByTagName("script")[0];
  s1.async = true;
  s1.src = 'https://embed.tawk.to/6a6356de7f65281d4a48391a/1juut5dpj'; // Make sure your full Property ID URL is here
  s1.charset = 'UTF-8';
  s1.setAttribute('crossorigin', '*');
  s0.parentNode.insertBefore(s1, s0);
})();
</script>



<script>
(function () {
  var CSS_RULES = `
    /* "Powered by tawk.to" branding */
    a[style*="border-radius: 100px"][style*="font-size: 12px"],
    a[style*="font-size: 12px"][style*="font-weight: 400"][style*="padding"],

    /* "Add Chat to your website" menu item */
    a[href*="utm_source=tawk-messenger"],

    /* "Pop out widget" menu item */
    button:has(i.tawk-icon-popout),

    /* "Email transcript" menu item */
    button:has(i.tawk-icon-envelope) {
      display: none !important;
    }
  `;

  function injectStyle(doc) {
    try {
      if (!doc || doc.getElementById('hide-tawk-badge')) return;
      var style = doc.createElement('style');
      style.id = 'hide-tawk-badge';
      style.textContent = CSS_RULES;
      (doc.head || doc.documentElement).appendChild(style);
    } catch (e) {}
  }

  function textFallback(doc) {
    try {
      doc.querySelectorAll('a, button').forEach(function (el) {
        var t = (el.textContent || '').toLowerCase().trim();
        var hasLogoSvg = el.querySelector && el.querySelector('svg[viewBox="0 0 107 15"]');
        var isAddChat = t.includes('add chat to your website');
        var isPopout = t.includes('pop out widget');
        var isEmailTranscript = t.includes('email transcript');
        if (hasLogoSvg || isAddChat || isPopout || isEmailTranscript) {
          el.style.setProperty('display', 'none', 'important');
        }
      });
    } catch (e) {}
  }

  function process(doc) {
    injectStyle(doc);
    textFallback(doc);
  }

  process(document);

  function handleIframe(iframe) {
    var tryInject = function () {
      try {
        var doc = iframe.contentDocument || (iframe.contentWindow && iframe.contentWindow.document);
        process(doc);
      } catch (e) {}
    };
    tryInject();
    iframe.addEventListener('load', tryInject);
  }

  document.querySelectorAll('iframe').forEach(handleIframe);

  new MutationObserver(function (mutations) {
    mutations.forEach(function (m) {
      m.addedNodes.forEach(function (node) {
        if (node.nodeName === 'IFRAME') handleIframe(node);
        else if (node.querySelectorAll) node.querySelectorAll('iframe').forEach(handleIframe);
      });
    });
  }).observe(document.documentElement, { childList: true, subtree: true });

  var tick = setInterval(function () {
    process(document);
    document.querySelectorAll('iframe').forEach(function (f) {
      try {
        process(f.contentDocument || (f.contentWindow && f.contentWindow.document));
      } catch (e) {}
    });
  }, 100);
  setTimeout(function () { clearInterval(tick); }, 8000);
})();
</script>

<!-- End of Tawk.to Script -->




</body>
</html>

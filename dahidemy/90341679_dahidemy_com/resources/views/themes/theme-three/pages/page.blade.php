@extends('themes.' . config('frontend.active_theme', 'theme-three') . '.layouts.app')

@section('title', $page->title ?? 'Page')

@section('content')
    <section class="py-24" style="background: var(--paper-50);">
        <div class="max-w-3xl mx-auto px-4">
            <span class="eyebrow mb-4">Page</span>
            <h1 class="section-title mb-10">{{ $page->title }}</h1>

            <div class="prose max-w-none leading-8" style="color: var(--ink-700);">
                {!! $page->content !!}
            </div>
        </div>
    </section>
@endsection

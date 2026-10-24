@extends('themes.' . config('frontend.active_theme', 'theme-three') . '.layouts.app')

@php
    $policyTitle = match ($data->type ?? null) {
        1 => 'Privacy Policy',
        2 => 'Cookies Policy',
        3 => 'Terms & Conditions',
        4 => 'Refund Policy',
        default => 'Policy',
    };
@endphp

@section('title', $policyTitle)

@section('content')
    <section class="py-24" style="background: var(--paper-50);">
        <div class="max-w-3xl mx-auto px-4">
            <span class="eyebrow mb-4">Policy</span>
            <h1 class="section-title mb-10">{{ $policyTitle }}</h1>

            @if ($data && $data->description)
                <div class="prose max-w-none leading-8" style="color: var(--ink-700);">
                    {!! $data->description !!}
                </div>
            @else
                <p style="color: var(--slate-500);">This page hasn't been filled in yet.</p>
            @endif
        </div>
    </section>
@endsection

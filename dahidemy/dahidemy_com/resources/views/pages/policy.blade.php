@extends('layouts.landing')

@section('content')
    <x-landing.page-hero :title="$title" :subtitle="'Last updated '.now()->format('F j, Y')" />

    <section class="pb-24">
        <div class="container-page">
            <div class="reveal mx-auto max-w-3xl space-y-4 text-base leading-relaxed text-slate-600 [&_a]:font-semibold [&_a]:text-brand-600 [&_h2]:mt-8 [&_h2]:text-xl [&_h2]:font-semibold [&_h2]:text-slate-900 [&_li]:ml-5 [&_li]:list-disc dark:text-slate-300 dark:[&_h2]:text-white">

                @if (!empty($policy['description']))
                    {!! $policy['description'] !!}
                @else
                    <p class="text-slate-600 dark:text-slate-400">
                        This {{ strtolower($title) }} explains how {{ $brand['name'] ?? config('saas.brand.name') }} handles your
                        information and your rights. The full policy content is managed from the admin panel and will appear
                        here once published.
                    </p>
                    <p class="text-slate-600 dark:text-slate-400">
                        For any questions in the meantime, contact us at
                        <a href="mailto:{{ $brand['email'] ?? config('saas.brand.email') }}" class="font-semibold text-brand-600">{{ $brand['email'] ?? config('saas.brand.email') }}</a>.
                    </p>
                @endif
            </div>
        </div>
    </section>
@endsection

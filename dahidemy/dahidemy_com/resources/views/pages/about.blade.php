@extends('layouts.landing')

@section('content')
    <x-landing.page-hero
        eyebrow="About us"
        title='On a mission to modernize <span class="gradient-text">education management</span>'
        subtitle="We build the software that lets institutions spend less time on paperwork and more time on people." />

    <section class="py-16">
        <div class="container-page grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ([['Our mission', 'Give every institution enterprise-grade tools that are actually a joy to use.'], ['Our approach', 'One unified platform instead of a dozen disconnected point solutions.'], ['Our promise', 'Fast, secure and reliable — with support that genuinely cares.'], ['Our people', 'Educators and engineers building for the classrooms of tomorrow.']] as [$title, $body])
                <div class="reveal card">
                    <h3 class="font-semibold text-slate-900 dark:text-white">{{ $title }}</h3>
                    <p class="mt-2 text-sm leading-relaxed text-slate-600 dark:text-slate-400">{{ $body }}</p>
                </div>
            @endforeach
        </div>
    </section>

    <x-landing.stats :stats="$stats" :revenue="$revenue" />
    <x-landing.testimonials :feedback="$feedback" />
    <x-landing.cta :brand="$brand" />
@endsection

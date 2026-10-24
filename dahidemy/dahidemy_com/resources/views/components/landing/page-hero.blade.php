@props(['eyebrow' => null, 'title' => '', 'subtitle' => null])

<section class="relative overflow-hidden pt-32 pb-14 sm:pt-40">
    <div class="hero-aurora absolute inset-0 -z-10"></div>
    <div class="container-page text-center">
        @if ($eyebrow)
            <span class="eyebrow reveal">{{ $eyebrow }}</span>
        @endif
        <h1 class="reveal mx-auto mt-5 max-w-3xl text-4xl font-bold tracking-tight text-slate-900 sm:text-5xl dark:text-white">
            {!! $title !!}
        </h1>
        @if ($subtitle)
            <p class="reveal mx-auto mt-5 max-w-2xl text-lg leading-relaxed text-slate-600 dark:text-slate-300">{{ $subtitle }}</p>
        @endif
        {{ $slot }}
    </div>
</section>

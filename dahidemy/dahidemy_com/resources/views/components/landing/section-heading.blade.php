@props(['eyebrow' => null, 'title' => '', 'subtitle' => null, 'align' => 'center'])

<div @class([
    'reveal max-w-2xl',
    'mx-auto text-center' => $align === 'center',
])>
    @if ($eyebrow)
        <span class="eyebrow">
            <x-landing.icon name="sparkles" class="h-3.5 w-3.5" />
            {{ $eyebrow }}
        </span>
    @endif
    <h2 class="mt-4 text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl dark:text-white">
        {!! $title !!}
    </h2>
    @if ($subtitle)
        <p class="mt-4 text-lg leading-relaxed text-slate-600 dark:text-slate-400">{{ $subtitle }}</p>
    @endif
</div>

@props(['title' => '', 'subtitle' => null, 'back' => null])

<div class="mb-6 flex flex-wrap items-end justify-between gap-4">
    <div class="min-w-0">
        @if ($back)
            <a href="{{ $back }}" class="mb-1.5 inline-flex items-center gap-1 text-xs font-medium text-slate-500 transition hover:text-brand-600 dark:text-slate-400">
                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
                Back
            </a>
        @endif
        <h2 class="truncate text-xl font-bold tracking-tight text-slate-900 dark:text-white">{{ $title }}</h2>
        @if ($subtitle)
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ $subtitle }}</p>
        @endif
    </div>

    @if (! $slot->isEmpty())
        <div class="flex flex-shrink-0 items-center gap-2">{{ $slot }}</div>
    @endif
</div>

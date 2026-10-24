@props(['title' => 'Nothing here yet', 'subtitle' => null, 'icon' => 'M12 5v14M5 12h14'])

<div class="flex flex-col items-center justify-center px-6 py-16 text-center">
    <span class="grid h-12 w-12 place-items-center rounded-2xl bg-slate-100 text-slate-400 dark:bg-white/5 dark:text-slate-500">
        <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="{{ $icon }}"/></svg>
    </span>
    <p class="mt-4 text-sm font-semibold text-slate-700 dark:text-slate-200">{{ $title }}</p>
    @if ($subtitle)
        <p class="mt-1 max-w-sm text-sm text-slate-500 dark:text-slate-400">{{ $subtitle }}</p>
    @endif
    @if (! $slot->isEmpty())
        <div class="mt-5">{{ $slot }}</div>
    @endif
</div>

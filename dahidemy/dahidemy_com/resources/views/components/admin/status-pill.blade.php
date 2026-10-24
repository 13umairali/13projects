@props(['active' => false, 'activeLabel' => 'Active', 'inactiveLabel' => 'Inactive'])

{{-- `active` takes the raw backend value: only 1 is active, so an institute
     stored as 0 OR 2 both read as inactive. See App\Support\Status. --}}
@php $active = \App\Support\Status::isActive($active); @endphp

<span @class([
    'inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset',
    'bg-emerald-50 text-emerald-700 ring-emerald-600/20 dark:bg-emerald-500/10 dark:text-emerald-300 dark:ring-emerald-400/20' => $active,
    'bg-rose-50 text-rose-700 ring-rose-600/20 dark:bg-rose-500/10 dark:text-rose-300 dark:ring-rose-400/20' => !$active,
])>
    @if ($active)
        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
    @else
        {{-- A crossed circle, so "inactive" is legible without relying on colour alone. --}}
        <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
            <circle cx="12" cy="12" r="9" />
            <path d="M5.6 5.6l12.8 12.8" />
        </svg>
    @endif
    {{ $active ? $activeLabel : $inactiveLabel }}
</span>

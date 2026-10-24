@props(['label' => '', 'value' => 0, 'icon' => 'chart', 'tone' => 'brand'])

@php
    $tones = [
        'brand'   => 'bg-brand-50 text-brand-600 dark:bg-brand-500/10 dark:text-brand-300',
        'emerald' => 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400',
        'rose'    => 'bg-rose-50 text-rose-600 dark:bg-rose-500/10 dark:text-rose-400',
        'amber'   => 'bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-400',
    ];
@endphp

<div class="card">
    <div class="flex items-center justify-between">
        <p class="text-sm font-medium text-slate-500 dark:text-slate-400">{{ $label }}</p>
        <span class="grid h-10 w-10 place-items-center rounded-xl {{ $tones[$tone] ?? $tones['brand'] }}">
            <x-landing.icon :name="$icon" class="h-5 w-5" />
        </span>
    </div>
    <p class="mt-3 text-3xl font-bold tracking-tight text-slate-900 dark:text-white">{{ number_format((float) $value) }}</p>
</div>

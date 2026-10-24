@props(['name', 'label' => null, 'value' => null, 'help' => null])

@php
    $label = $label ?? \Illuminate\Support\Str::headline($name);
    // The text input is the one that submits: an unset colour stays unset rather
    // than being saved as the picker's default black.
    $current = (string) old($name, $value ?? '');

    $base =
        'w-full rounded-xl border bg-white px-3 py-2.5 text-sm text-slate-900 outline-none transition ' .
        'focus:border-brand-500 focus:ring-2 focus:ring-brand-500/30 dark:bg-slate-900 dark:text-white ' .
        ($errors->has($name) ? 'border-rose-400 dark:border-rose-500/50' : 'border-slate-200 dark:border-white/10');
@endphp

<div x-data="{ color: @js($current) }">
    <label for="field-{{ $name }}" class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">
        {{ $label }}
    </label>

    <div class="flex items-center gap-2">
        <input type="color" aria-label="{{ $label }} picker" :value="color || '#000000'"
            @input="color = $event.target.value"
            class="h-10 w-12 flex-none cursor-pointer rounded-xl border border-slate-200 bg-white p-1 dark:border-white/10 dark:bg-slate-900">
        <input id="field-{{ $name }}" name="{{ $name }}" type="text" x-model="color" maxlength="7"
            placeholder="Not set" class="{{ $base }} font-mono">
    </div>

    @error($name)
        <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
    @else
        @if ($help)
            <p class="mt-1 text-xs text-slate-400 dark:text-slate-500">{{ $help }}</p>
        @endif
    @enderror
</div>

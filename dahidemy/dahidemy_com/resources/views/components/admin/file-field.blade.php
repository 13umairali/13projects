@props([
    'name',
    'label' => null,
    'help' => null,
    'accept' => 'image/jpeg,image/png,image/webp',
    'current' => null,
    'kind' => 'image',
])

@php
    $label = $label ?? \Illuminate\Support\Str::headline($name);
    $ph = saas_placeholder($kind);
    $saved = $current ?: $ph;
@endphp

{{-- File input with a live client-side image preview (Alpine). Falls back to the
     placeholder both when nothing is saved and when the saved file has vanished
     from the backend disk (onerror). --}}
<div x-data="{ saved: @js($saved), preview: @js($saved) }">
    <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">{{ $label }}</label>
    <div class="flex items-center gap-4">
        <span class="grid h-16 w-16 flex-none place-items-center overflow-hidden rounded-xl border border-slate-200 bg-slate-50 text-slate-300 dark:border-white/10 dark:bg-white/5">
            <img :src="preview" alt="" class="h-full w-full object-cover"
                onerror="this.onerror=null;this.src='{{ $ph }}'">
        </span>
        <div class="min-w-0 flex-1">
            <input type="file" name="{{ $name }}" accept="{{ $accept }}"
                @change="const f = $event.target.files[0]; preview = f ? URL.createObjectURL(f) : saved"
                class="block w-full text-sm text-slate-500 file:mr-3 file:cursor-pointer file:rounded-lg file:border-0 file:bg-brand-50 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-brand-600 hover:file:bg-brand-100 dark:text-slate-400 dark:file:bg-brand-500/10 dark:file:text-brand-300">
            @error($name)
                <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
            @else
                @if ($help)
                    <p class="mt-1 text-xs text-slate-400 dark:text-slate-500">{{ $help }}</p>
                @endif
            @enderror
        </div>
    </div>
</div>

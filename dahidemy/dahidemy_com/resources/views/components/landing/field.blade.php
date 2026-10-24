@props(['name', 'label' => '', 'type' => 'text', 'value' => '', 'required' => false, 'placeholder' => ''])

<div>
    <label for="{{ $name }}" class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">
        {{ $label }} @if ($required)<span class="text-rose-500">*</span>@endif
    </label>
    <input id="{{ $name }}" name="{{ $name }}" type="{{ $type }}" value="{{ $value }}"
        placeholder="{{ $placeholder }}" @required($required)
        {{ $attributes->merge(['class' => 'w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-500/30 dark:border-white/10 dark:bg-slate-900 dark:text-white']) }}>
    @error($name)<p class="mt-1 text-xs text-rose-500">{{ $message }}</p>@enderror
</div>

@props(['field' => [], 'value' => null, 'storage' => null])

@php
    $name = $field['name'];
    $label = $field['label'] ?? \Illuminate\Support\Str::headline($name);
    $type = $field['type'] ?? 'text';
    $required = in_array('required', $field['rules'] ?? [], true);
    $current = old($name, $value ?? ($field['default'] ?? ''));
    $help = $field['help'] ?? null;

    // For an image field, $value is the stored filename — resolve it to a URL so
    // the admin sees what is currently saved. A file input can never be
    // repopulated from old(), so on a validation bounce we still show the
    // existing image rather than implying the upload survived.
    $imageKind = $field['kind'] ?? 'image';
    $currentImage = $type === 'image' ? saas_image($value, $storage) : null;
    $imagePlaceholder = $type === 'image' ? saas_placeholder($imageKind) : null;

    // Optional numeric input constraints (step for decimals, min/max bounds).
    $step = $field['step'] ?? null;
    $min = $field['min'] ?? null;
    $max = $field['max'] ?? null;

    $base = 'w-full rounded-xl border bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition '
        .'focus:border-brand-500 focus:ring-2 focus:ring-brand-500/30 dark:bg-slate-900 dark:text-white '
        .($errors->has($name)
            ? 'border-rose-400 dark:border-rose-500/50'
            : 'border-slate-200 dark:border-white/10');
@endphp

<div>
    <label for="field-{{ $name }}" class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">
        {{ $label }} @if ($required)<span class="text-rose-500">*</span>@endif
    </label>

    @if ($type === 'image')
        {{-- `saved` is the stored image (or the placeholder when there is none);
             `preview` swaps to the newly chosen file. onerror covers a row whose
             file has gone missing on the backend disk. --}}
        <div x-data="{ saved: @js($currentImage ?? $imagePlaceholder), preview: @js($currentImage ?? $imagePlaceholder) }"
            class="flex items-start gap-4">
            <div
                class="grid h-20 w-28 flex-none place-items-center overflow-hidden rounded-xl border border-dashed border-slate-300 bg-slate-50 dark:border-white/15 dark:bg-white/5">
                <img :src="preview" alt="" class="h-full w-full object-cover"
                    onerror="this.onerror=null;this.src='{{ $imagePlaceholder }}'">
            </div>

            <div class="min-w-0 flex-1">
                <input id="field-{{ $name }}" name="{{ $name }}" type="file"
                    accept="{{ $field['accept'] ?? 'image/jpeg,image/png,image/jpg,image/webp' }}"
                    @required($required && !$currentImage)
                    @change="const f = $event.target.files[0]; preview = f ? URL.createObjectURL(f) : saved"
                    class="w-full cursor-pointer rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-600 file:mr-3 file:rounded-lg file:border-0 file:bg-brand-50 file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-brand-700 hover:file:bg-brand-100 dark:border-white/10 dark:bg-slate-900 dark:text-slate-300 dark:file:bg-brand-500/10 dark:file:text-brand-300">
                <p class="mt-1 text-xs text-slate-400">
                    {{ $currentImage ? 'Leave empty to keep the current image.' : 'No image yet — a placeholder is shown until you upload one.' }}
                </p>
            </div>
        </div>
    @elseif ($type === 'textarea')
        <textarea id="field-{{ $name }}" name="{{ $name }}" rows="4" @required($required)
            class="{{ $base }}">{{ $current }}</textarea>
    @elseif ($type === 'select')
        <select id="field-{{ $name }}" name="{{ $name }}" @required($required) class="{{ $base }}">
            @unless ($required)
                <option value="">— Select —</option>
            @endunless
            @foreach (($field['options'] ?? []) as $optValue => $optLabel)
                <option value="{{ $optValue }}" @selected((string) $current === (string) $optValue)>{{ $optLabel }}</option>
            @endforeach
        </select>
    @else
        <input id="field-{{ $name }}" name="{{ $name }}" type="{{ $type }}" value="{{ $current }}"
            @if (! is_null($step)) step="{{ $step }}" @endif
            @if (! is_null($min)) min="{{ $min }}" @endif
            @if (! is_null($max)) max="{{ $max }}" @endif
            @required($required) class="{{ $base }}">
    @endif

    @error($name)
        <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
    @else
        @if ($help)
            <p class="mt-1 text-xs text-slate-400 dark:text-slate-500">{{ $help }}</p>
        @endif
    @enderror
</div>

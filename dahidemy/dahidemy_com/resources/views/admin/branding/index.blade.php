@extends('layouts.admin')

@section('title', 'Logos & branding')

@section('content')
    @php
        use App\Support\ImageUrl;

        $dir = ImageUrl::DIR_IMAGE_SETTINGS;

        $slots = [
            [
                'name' => 'header_logo_light_theme',
                'label' => 'Header logo — light theme',
                'help' => 'Shown in the site navbar and admin sidebar in light mode. Use a dark-coloured logo.',
                'dark' => false,
            ],
            [
                'name' => 'header_logo_dark_theme',
                'label' => 'Header logo — dark theme',
                'help' => 'Shown in the navbar and sidebar in dark mode. Use a light-coloured logo.',
                'dark' => true,
            ],
            [
                'name' => 'footer_logo_light_theme',
                'label' => 'Footer logo — light theme',
                'help' => 'Shown in the site footer in light mode.',
                'dark' => false,
            ],
            [
                'name' => 'footer_logo_dark_theme',
                'label' => 'Footer logo — dark theme',
                'help' => 'Shown in the site footer in dark mode.',
                'dark' => true,
            ],
            [
                'name' => 'banner_image',
                'label' => 'Banner image',
                'help' => 'Large hero image for the marketing site.',
                'dark' => false,
                'wide' => true,
            ],
        ];

        $exists = filled($settings['id'] ?? null);
    @endphp

    <x-admin.page-header title="Logos & branding"
        subtitle="Every logo in the system comes from here — admin sidebar, landing navbar and footer, in both light and dark themes." />

    @unless ($exists)
        <div
            class="mb-6 flex items-start gap-2.5 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-300">
            <svg class="mt-0.5 h-4 w-4 flex-none" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <circle cx="12" cy="12" r="10" />
                <path d="M12 8v4M12 16h.01" />
            </svg>
            <span>No branding images have been set up yet. Upload at least one below to create them — the site is
                falling back to its built-in mark until you do.</span>
        </div>
    @endunless

    <form method="POST" action="{{ route('admin.branding.update') }}" enctype="multipart/form-data" class="card space-y-6">
        @csrf

        <div class="grid gap-6 lg:grid-cols-2">
            @foreach ($slots as $slot)
                @php
                    $saved = saas_image($settings[$slot['name']] ?? null, $dir);
                    $ph = saas_placeholder($slot['name'] === 'banner_image' ? 'image' : 'logo');
                @endphp

                <div @class(['space-y-2', 'lg:col-span-2' => $slot['wide'] ?? false])>
                    <label for="f-{{ $slot['name'] }}" class="block text-sm font-medium text-slate-700 dark:text-slate-300">
                        {{ $slot['label'] }}
                    </label>

                    <div x-data="{ saved: @js($saved ?? $ph), preview: @js($saved ?? $ph) }" class="flex items-start gap-4">
                        {{-- Dark-theme logos preview on a dark plate: a white logo is
                             invisible on white, so this is the only honest preview. --}}
                        <div @class([
                            'grid flex-none place-items-center overflow-hidden rounded-xl border p-2',
                            'h-20 w-36' => !($slot['wide'] ?? false),
                            'h-28 w-48' => $slot['wide'] ?? false,
                            'border-slate-800 bg-slate-900' => $slot['dark'],
                            'border-slate-200 bg-slate-50 dark:border-white/10' => !$slot['dark'],
                        ])>
                            <img :src="preview" alt="" class="max-h-full max-w-full object-contain"
                                onerror="this.onerror=null;this.src='{{ $ph }}'">
                        </div>

                        <div class="min-w-0 flex-1">
                            <input id="f-{{ $slot['name'] }}" type="file" name="{{ $slot['name'] }}"
                                accept="image/png,image/jpeg,image/jpg,image/webp"
                                @change="const f = $event.target.files[0]; preview = f ? URL.createObjectURL(f) : saved"
                                class="w-full cursor-pointer rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-600 file:mr-3 file:rounded-lg file:border-0 file:bg-brand-50 file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-brand-700 hover:file:bg-brand-100 dark:border-white/10 dark:bg-slate-900 dark:text-slate-300 dark:file:bg-brand-500/10 dark:file:text-brand-300">

                            @error($slot['name'])
                                <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                            @else
                                <p class="mt-1 text-xs text-slate-400 dark:text-slate-500">{{ $slot['help'] }}</p>
                            @enderror

                            <p class="mt-1 text-xs text-slate-400 dark:text-slate-500">
                                PNG, JPG or WEBP · max 2MB ·
                                {{ filled($settings[$slot['name']] ?? null) ? 'leave empty to keep the current image' : 'not set yet' }}
                            </p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="flex items-center gap-3 border-t border-slate-100 pt-5 dark:border-white/10">
            <button type="submit" class="btn-primary px-5 py-2.5">
                {{ $exists ? 'Save branding' : 'Create branding' }}
            </button>
            <p class="text-xs text-slate-400">Changes appear on the marketing site immediately.</p>
        </div>
    </form>
@endsection

@props(['brand' => [], 'nav' => [], 'socials' => []])

@php
    $year = now()->year;
    $productLinks = [
        ['label' => 'Features', 'route' => 'features'],
        ['label' => 'Pricing', 'route' => 'pricing'],
        ['label' => 'FAQ', 'route' => 'faq'],
    ];
    $companyLinks = [['label' => 'About', 'route' => 'about'], ['label' => 'Contact', 'route' => 'contact']];
    $legalLinks = [
        ['label' => 'Privacy Policy', 'route' => 'policy', 'param' => 'privacy'],
        ['label' => 'Terms & Conditions', 'route' => 'policy', 'param' => 'terms'],
        ['label' => 'Cookie Policy', 'route' => 'policy', 'param' => 'cookie'],
    ];
    $socialIcons = [
        'facebook' => 'M18 2h-3a5 5 0 00-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 011-1h3z',
        'twitter' =>
            'M22 5.8a8.5 8.5 0 01-2.4.7 4.2 4.2 0 001.8-2.3 8.4 8.4 0 01-2.6 1A4.2 4.2 0 0011.5 9 12 12 0 013 4.5a4.2 4.2 0 001.3 5.6A4.1 4.1 0 012.4 9.6v.1a4.2 4.2 0 003.4 4.1 4.2 4.2 0 01-1.9.1 4.2 4.2 0 003.9 2.9A8.5 8.5 0 012 18.6a12 12 0 006.3 1.8c7.6 0 11.8-6.3 11.8-11.8v-.5A8.4 8.4 0 0022 5.8z',
        'linkedin' =>
            'M4.98 3.5A2.5 2.5 0 002.5 6 2.5 2.5 0 005 8.5 2.5 2.5 0 007.5 6 2.5 2.5 0 004.98 3.5zM3 9h4v12H3zM10 9h3.8v1.7h.05c.53-1 1.8-2 3.7-2 4 0 4.7 2.6 4.7 6V21h-4v-5.3c0-1.3 0-3-1.8-3s-2.1 1.4-2.1 2.9V21h-4z',
        'instagram' =>
            'M12 2.2c3.2 0 3.6 0 4.9.07 3.3.15 4.8 1.7 4.95 4.95.06 1.3.07 1.7.07 4.9s0 3.6-.07 4.9c-.15 3.2-1.65 4.8-4.95 4.95-1.3.06-1.7.07-4.9.07s-3.6 0-4.9-.07c-3.3-.15-4.8-1.7-4.95-4.95C2.2 15.6 2.2 15.2 2.2 12s0-3.6.07-4.9C2.42 3.9 3.92 2.35 7.1 2.27 8.4 2.2 8.8 2.2 12 2.2zm0 3.3a6.3 6.3 0 100 12.6 6.3 6.3 0 000-12.6zm0 10.4a4.1 4.1 0 110-8.2 4.1 4.1 0 010 8.2zm6.5-10.9a1.47 1.47 0 100 2.94 1.47 1.47 0 000-2.94z',
        'youtube' =>
            'M23 12s0-3.2-.4-4.7a2.5 2.5 0 00-1.7-1.7C19.4 5.2 12 5.2 12 5.2s-7.4 0-8.9.4a2.5 2.5 0 00-1.7 1.7C1 8.8 1 12 1 12s0 3.2.4 4.7a2.5 2.5 0 001.7 1.7c1.5.4 8.9.4 8.9.4s7.4 0 8.9-.4a2.5 2.5 0 001.7-1.7C23 15.2 23 12 23 12zM9.8 15.3V8.7l5.7 3.3z',
        'whatsapp' =>
            'M12 2a10 10 0 00-8.6 15L2 22l5.2-1.4A10 10 0 1012 2zm0 18.2a8.2 8.2 0 01-4.2-1.1l-.3-.2-3 .8.8-2.9-.2-.3A8.2 8.2 0 1112 20.2zm4.5-6.1c-.2-.1-1.4-.7-1.7-.8s-.4-.1-.5.1-.6.8-.8 1-.3.2-.5 0a6.7 6.7 0 01-2-1.2 7.4 7.4 0 01-1.3-1.7c-.2-.3 0-.4.1-.5l.4-.5.2-.4v-.4l-.8-1.8c-.2-.5-.4-.4-.5-.4h-.5a.9.9 0 00-.7.3 2.8 2.8 0 00-.9 2.1 4.9 4.9 0 001 2.6 11.2 11.2 0 004.3 3.8 8.4 8.4 0 001.5.5 3.4 3.4 0 001.6.1 2.6 2.6 0 001.7-1.2 2.1 2.1 0 00.2-1.2c-.1-.1-.2-.2-.4-.3z',
    ];
@endphp

<footer class="border-t border-slate-200 bg-slate-50 dark:border-white/10 dark:bg-slate-950">
    <div class="container-page py-14">
        <div class="grid gap-10 lg:grid-cols-[1.4fr_1fr_1fr_1fr]">
            {{-- Brand column --}}
            <div class="max-w-sm">
                <a href="{{ route('index') }}" class="flex items-center gap-2.5 font-bold">
                    @php
                        $footerLight = $brand['footer_logo_light'] ?? null;
                        $footerDark = $brand['footer_logo_dark'] ?? $footerLight;
                    @endphp
                    @if ($footerLight)
                        <img src="{{ $footerLight }}" alt="{{ $brand['name'] ?? '' }}" class="h-9 w-auto dark:hidden"
                            onerror="this.remove()">
                        <img src="{{ $footerDark }}" alt="{{ $brand['name'] ?? '' }}"
                            class="hidden h-9 w-auto dark:block" onerror="this.remove()">
                    @else
                        <span
                            class="grid h-9 w-9 place-items-center rounded-xl bg-gradient-to-br from-brand-500 to-accent-500 text-white">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2">
                                <path d="M22 10L12 5 2 10l10 5 10-5z" />
                                <path d="M6 12v5c0 1 3 3 6 3s6-2 6-3v-5" />
                            </svg>
                        </span>
                    @endif
                    {{-- <span class="text-lg">{{ $brand['name'] ?? config('saas.brand.name') }}</span> --}}
                </a>
                <p class="mt-4 text-sm leading-relaxed text-slate-500 dark:text-slate-400">
                    {{ $brand['description'] ?? config('saas.brand.description') }}
                </p>
                @if (!empty($socials))
                    <div class="mt-5 flex gap-2">
                        @foreach ($socials as $key => $url)
                            @if (isset($socialIcons[$key]))
                                <a href="{{ $url }}" target="_blank" rel="noopener"
                                    aria-label="{{ ucfirst($key) }}"
                                    class="grid h-9 w-9 place-items-center rounded-lg border border-slate-200 text-slate-500 transition hover:border-brand-400 hover:text-brand-600 dark:border-white/10 dark:text-slate-400 dark:hover:text-brand-300">
                                    <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="{{ $socialIcons[$key] }}" />
                                    </svg>
                                </a>
                            @endif
                        @endforeach
                    </div>
                @endif
            </div>

            <x-landing.footer-column title="Product" :links="$productLinks" />
            <x-landing.footer-column title="Company" :links="$companyLinks" />
            <x-landing.footer-column title="Legal" :links="$legalLinks" />
        </div>

        <div
            class="mt-12 flex flex-col items-center justify-between gap-3 border-t border-slate-200 pt-6 text-sm text-slate-500 sm:flex-row dark:border-white/10 dark:text-slate-400">
            <p>
                {{ $brand['copyright'] ?? '© ' . $year . ' ' . ($brand['name'] ?? config('saas.brand.name')) . '. All rights reserved.' }}
            </p>
            <p class="flex items-center gap-2">
                <span class="inline-flex h-2 w-2 rounded-full bg-emerald-500"></span>
                All systems operational
            </p>
        </div>
    </div>
</footer>

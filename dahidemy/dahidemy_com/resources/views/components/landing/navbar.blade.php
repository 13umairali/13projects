@props(['brand' => [], 'nav' => [], 'authed' => false])

<header x-data="{ open: false, scrolled: false }" @scroll.window="scrolled = window.scrollY > 12"
    class="fixed inset-x-0 top-0 z-40 transition-all duration-300"
    :class="scrolled
        ?
        'border-b border-slate-200/70 bg-white/80 backdrop-blur-xl dark:border-white/10 dark:bg-slate-950/80' :
        'border-b border-transparent'">
    <nav class="container-page flex h-16 items-center justify-between gap-4">
        {{-- Brand --}}
        <a href="{{ route('index') }}" class="flex items-center gap-2.5 font-bold">
            @php
                $logoLight = $brand['header_logo_light'] ?? null;
                $logoDark = $brand['header_logo_dark'] ?? $logoLight;
            @endphp
            @if ($logoLight)
                {{-- onerror: a logo row can outlive its file; fall back to the wordmark, never a broken icon. --}}
                <img src="{{ $logoLight }}" alt="{{ $brand['name'] }}" class="h-8 w-auto dark:hidden"
                    onerror="this.remove()">
                <img src="{{ $logoDark }}" alt="{{ $brand['name'] }}" class="hidden h-8 w-auto dark:block"
                    onerror="this.remove()">
            @else
                <span
                    class="grid h-9 w-9 place-items-center rounded-xl bg-gradient-to-br from-brand-500 to-accent-500 text-white shadow-lg shadow-brand-600/30">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M22 10L12 5 2 10l10 5 10-5z" />
                        <path d="M6 12v5c0 1 3 3 6 3s6-2 6-3v-5" />
                    </svg>
                </span>
            @endif
            {{-- <span class="text-lg tracking-tight">{{ $brand['name'] ?? config('saas.brand.name') }}</span> --}}
        </a>

        {{-- Desktop nav --}}
        <div class="hidden items-center gap-1 lg:flex">
            @foreach ($nav as $item)
                <a href="{{ route($item['route']) . (!empty($item['fragment']) ? '#' . $item['fragment'] : '') }}"
                    class="rounded-lg px-3.5 py-2 text-sm font-medium text-slate-600 transition hover:bg-slate-100 hover:text-slate-900 dark:text-slate-300 dark:hover:bg-white/5 dark:hover:text-white">
                    {{ $item['label'] }}
                </a>
            @endforeach
        </div>

        {{-- Actions --}}
        <div class="flex items-center gap-2">
            <x-landing.theme-toggle />

            @if ($authed)
                <a href="{{ route('admin.dashboard') }}" class="btn-primary hidden sm:inline-flex">Dashboard</a>
            @else
<!--
                <a href="{{ route('login') }}"
                    class="hidden rounded-lg px-3.5 py-2 text-sm font-semibold text-slate-600 transition hover:text-slate-900 sm:inline-flex dark:text-slate-300 dark:hover:text-white">
                    Sign in
                </a>
-->
                <a href="{{ route('register.show') }}" class="btn-primary hidden sm:inline-flex">Register</a>
            @endif

            {{-- Mobile toggle --}}
            <button type="button" @click="open = !open"
                class="grid h-10 w-10 place-items-center rounded-lg text-slate-600 hover:bg-slate-100 lg:hidden dark:text-slate-300 dark:hover:bg-white/5"
                aria-label="Toggle menu">
                <svg x-show="!open" class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2"
                    viewBox="0 0 24 24">
                    <path d="M4 6h16M4 12h16M4 18h16" />
                </svg>
                <svg x-show="open" x-cloak class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2"
                    viewBox="0 0 24 24">
                    <path d="M6 6l12 12M18 6L6 18" />
                </svg>
            </button>
        </div>
    </nav>

    {{-- Mobile menu --}}
    <div x-show="open" x-cloak x-collapse class="lg:hidden">
        <div
            class="container-page space-y-1 border-t border-slate-200/70 bg-white/95 pb-6 pt-3 backdrop-blur dark:border-white/10 dark:bg-slate-950/95">
            @foreach ($nav as $item)
                <a href="{{ route($item['route']) . (!empty($item['fragment']) ? '#' . $item['fragment'] : '') }}"
                    class="block rounded-lg px-3 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-100 dark:text-slate-200 dark:hover:bg-white/5">
                    {{ $item['label'] }}
                </a>
            @endforeach
            <div class="flex flex-col gap-2 pt-3">
<!--
                <a href="{{ route('login') }}" class="btn-ghost">Sign in</a>
-->
                <a href="{{ route('register.show') }}" class="btn-primary">Register</a>
            </div>
        </div>
    </div>
</header>

<style>
    [x-cloak] {
        display: none !important
    }
</style>

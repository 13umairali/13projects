<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="light">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script>
        (function () {
            try {
                var t = localStorage.getItem('theme') || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
                document.documentElement.setAttribute('data-theme', t);
            } catch (e) { document.documentElement.setAttribute('data-theme', 'light'); }
        })();
    </script>
    <title>{{ $title ?? 'Sign in' }} · {{ $brand['name'] ?? config('saas.brand.name') }}</title>
    <meta name="robots" content="noindex, nofollow">
    @if (!empty($brand['favicon']))
        <link rel="icon" href="{{ $brand['favicon'] }}">
    @endif
    @include('partials.vite-assets')
</head>

<body class="min-h-screen bg-slate-50 text-slate-900 dark:bg-slate-950 dark:text-slate-100">
    <div class="grid min-h-screen lg:grid-cols-2">
        {{-- Brand panel --}}
        <div class="relative hidden overflow-hidden bg-gradient-to-br from-brand-600 to-accent-600 p-12 lg:flex lg:flex-col lg:justify-between">
            <div class="pointer-events-none absolute inset-0 opacity-30"
                style="background-image: radial-gradient(circle at 15% 15%, rgba(255,255,255,.3), transparent 40%), radial-gradient(circle at 85% 75%, rgba(255,255,255,.2), transparent 45%);"></div>
            {{-- The auth panel is always dark, so it uses the DARK header logo. --}}
            <a href="{{ route('index') }}" class="relative flex items-center gap-2.5 font-bold text-white">
                @if (!empty($brand['header_logo_dark']))
                    <img src="{{ $brand['header_logo_dark'] }}" alt="{{ $brand['name'] ?? '' }}" class="h-9 w-auto"
                        onerror="this.onerror=null;this.remove()">
                @else
                    <span class="grid h-9 w-9 flex-none place-items-center rounded-xl bg-white/15 backdrop-blur">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 10L12 5 2 10l10 5 10-5z"/><path d="M6 12v5c0 1 3 3 6 3s6-2 6-3v-5"/></svg>
                    </span>
                @endif
                <span class="text-lg">{{ $brand['name'] ?? config('saas.brand.name') }}</span>
            </a>
            <div class="relative">
                <h2 class="text-3xl font-bold leading-tight text-white">Run your entire institution from one platform.</h2>
                <p class="mt-4 max-w-md text-white/80">Admissions, academics, finance, HR and more — beautifully unified for administrators, teachers and parents.</p>
            </div>
            <p class="relative text-sm text-white/70">&copy; {{ now()->year }} {{ $brand['name'] ?? config('saas.brand.name') }}</p>
        </div>

        {{-- Form panel --}}
        <div class="flex flex-col">
            <div class="flex items-center justify-between p-6">
                <a href="{{ route('index') }}" class="inline-flex items-center gap-1.5 text-sm font-medium text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
                    Back to site
                </a>
                <x-landing.theme-toggle />
            </div>
            <div class="flex flex-1 items-center justify-center px-6 pb-16">
                <div class="w-full max-w-sm">
                    @yield('content')
                </div>
            </div>
        </div>
    </div>
    <style>[x-cloak]{display:none!important}</style>
</body>

</html>

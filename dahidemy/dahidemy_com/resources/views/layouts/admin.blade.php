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
    <title>@yield('title', 'Dashboard') · {{ $brand['name'] ?? config('saas.brand.name') }} Admin</title>
    <meta name="robots" content="noindex, nofollow">
    @if (!empty($brand['favicon']))
        <link rel="icon" href="{{ $brand['favicon'] }}">
    @endif
    @include('partials.vite-assets')
</head>

<body class="min-h-screen bg-slate-50 text-slate-900 dark:bg-slate-950 dark:text-slate-100"
    x-data="{ sidebar: false }">

    @php $authUser = (array) session(config('saas.auth.user_key')); @endphp

    {{-- Mobile overlay --}}
    <div x-show="sidebar" x-cloak @click="sidebar = false" class="fixed inset-0 z-30 bg-slate-900/50 lg:hidden"></div>

    <x-admin.sidebar :brand="$brand" />

    <div class="lg:pl-64">
        <x-admin.topbar :user="$authUser">@yield('title', 'Dashboard')</x-admin.topbar>

        <main class="container-admin py-8">
            @if (session('success'))
                <div class="mb-6 flex items-start gap-2.5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-300">
                    <svg class="mt-0.5 h-4 w-4 flex-none" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M22 11.1V12a10 10 0 11-5.9-9.1"/><path d="M22 4L12 14.01l-3-3"/></svg>
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="mb-6 flex items-start gap-2.5 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-700 dark:border-rose-500/30 dark:bg-rose-500/10 dark:text-rose-300">
                    <svg class="mt-0.5 h-4 w-4 flex-none" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg>
                    {{ session('error') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-6 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700 dark:border-rose-500/30 dark:bg-rose-500/10 dark:text-rose-300">
                    <p class="font-semibold">Please fix the following:</p>
                    <ul class="mt-1 list-inside list-disc space-y-0.5">
                        @foreach ($errors->all() as $message)
                            <li>{{ $message }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        </main>
    </div>

    <style>[x-cloak]{display:none!important}</style>
</body>

</html>

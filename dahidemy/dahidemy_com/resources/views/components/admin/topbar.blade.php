@props(['user' => []])

@php
    $name = $user['name'] ?? $user['first_name'] ?? 'Admin';
    $email = $user['email'] ?? '';
    $initials = \Illuminate\Support\Str::of($name)->explode(' ')->map(fn ($w) => \Illuminate\Support\Str::substr($w, 0, 1))->take(2)->implode('');
@endphp

<header class="sticky top-0 z-20 flex h-16 items-center gap-4 border-b border-slate-200 bg-white/80 px-5 backdrop-blur-xl dark:border-white/10 dark:bg-slate-950/80">
    <button type="button" @click="sidebar = !sidebar"
        class="grid h-10 w-10 place-items-center rounded-lg text-slate-600 hover:bg-slate-100 lg:hidden dark:text-slate-300 dark:hover:bg-white/5" aria-label="Toggle sidebar">
        <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
    </button>

    <h1 class="text-lg font-semibold text-slate-900 dark:text-white">{{ $slot }}</h1>

    <div class="ml-auto flex items-center gap-2">
        {{-- One button, both caches: this panel's cached API responses and the
             backend's own. Writes clear the panel cache by themselves, so this is
             for when the backend changed underneath us. --}}
        <form method="POST" action="{{ route('admin.cache.clear') }}" x-data="{ clearing: false }"
            @submit="clearing = true">
            @csrf
            <button type="submit" :disabled="clearing" title="Clear cached data — panel and backend"
                class="inline-flex items-center gap-2 rounded-lg border border-slate-200 px-2.5 py-1.5 text-sm font-medium text-slate-600 transition hover:bg-slate-100 hover:text-slate-900 disabled:cursor-not-allowed disabled:opacity-60 sm:px-3 dark:border-white/10 dark:text-slate-300 dark:hover:bg-white/5 dark:hover:text-white">
                <svg class="h-4 w-4 flex-none" :class="clearing && 'animate-spin'" fill="none" stroke="currentColor"
                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                    <path d="M21 2v6h-6" />
                    <path d="M3 12a9 9 0 0115-6.7L21 8" />
                    <path d="M3 22v-6h6" />
                    <path d="M21 12a9 9 0 01-15 6.7L3 16" />
                </svg>
                <span class="hidden sm:inline" x-text="clearing ? 'Clearing…' : 'Clear cache'">Clear cache</span>
            </button>
        </form>

        {{-- Jump to the public marketing site in a new tab --}}
        <a href="{{ route('index') }}" target="_blank" rel="noopener"
            title="Open the public website in a new tab"
            class="inline-flex items-center gap-2 rounded-lg border border-slate-200 px-2.5 py-1.5 text-sm font-medium text-slate-600 transition hover:bg-slate-100 hover:text-slate-900 sm:px-3 dark:border-white/10 dark:text-slate-300 dark:hover:bg-white/5 dark:hover:text-white">
            <svg class="h-4 w-4 flex-none" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2h6M15 3h6v6M10 14L21 3"/></svg>
            <span class="hidden sm:inline">Visit website</span>
        </a>

        <x-landing.theme-toggle />

        <div class="relative" x-data="{ open: false }">
            <button type="button" @click="open = !open" class="flex items-center gap-2 rounded-lg py-1.5 pl-1.5 pr-2 hover:bg-slate-100 dark:hover:bg-white/5">
                <span class="grid h-8 w-8 place-items-center rounded-full bg-gradient-to-br from-brand-500 to-accent-500 text-xs font-bold text-white">{{ $initials }}</span>
                <span class="hidden text-sm font-medium text-slate-700 sm:block dark:text-slate-200">{{ $name }}</span>
                <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 9l6 6 6-6"/></svg>
            </button>
            <div x-show="open" x-cloak @click.outside="open = false" x-transition
                class="absolute right-0 mt-2 w-56 rounded-xl border border-slate-200 bg-white p-2 shadow-lg dark:border-white/10 dark:bg-slate-900">
                <div class="px-3 py-2">
                    <p class="text-sm font-semibold text-slate-900 dark:text-white">{{ $name }}</p>
                    @if ($email)<p class="truncate text-xs text-slate-500 dark:text-slate-400">{{ $email }}</p>@endif
                </div>
                <hr class="my-1 border-slate-200 dark:border-white/10">
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-left text-sm text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-500/10">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4M16 17l5-5-5-5M21 12H9"/></svg>
                        Sign out
                    </button>
                </form>
            </div>
        </div>
    </div>
</header>

<button type="button" @click="$store.theme.toggle()"
    class="grid h-10 w-10 place-items-center rounded-lg text-slate-600 transition hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-white/5"
    :aria-label="$store.theme.isDark ? 'Switch to light mode' : 'Switch to dark mode'">
    {{-- Sun (shown in dark mode) --}}
    <svg x-show="$store.theme.isDark" x-cloak class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <circle cx="12" cy="12" r="4" />
        <path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4" />
    </svg>
    {{-- Moon (shown in light mode) --}}
    <svg x-show="!$store.theme.isDark" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path d="M21 12.8A9 9 0 1111.2 3a7 7 0 009.8 9.8z" />
    </svg>
</button>

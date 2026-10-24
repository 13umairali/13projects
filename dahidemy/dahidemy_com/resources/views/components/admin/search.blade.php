@props([
    'action',
    'value' => '',
    'placeholder' => 'Search…',
    'perPage' => 10,
    'hidden' => [],
])

{{--
    Reusable GET search + per-page toolbar for admin index tables. Extra query
    state (sort column/order) is carried through as hidden inputs so searching
    never drops the active sort.
--}}
<form method="GET" action="{{ $action }}" class="mb-5 flex flex-wrap items-center gap-3">
    @foreach ($hidden as $key => $val)
        <input type="hidden" name="{{ $key }}" value="{{ $val }}">
    @endforeach

    <div class="relative min-w-[15rem] flex-1">
        <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <circle cx="11" cy="11" r="8" /><path d="M21 21l-4.3-4.3" />
        </svg>
        <input type="search" name="search" value="{{ $value }}" placeholder="{{ $placeholder }}"
            class="w-full rounded-xl border border-slate-200 bg-white py-2.5 pl-10 pr-4 text-sm text-slate-900 outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-500/30 dark:border-white/10 dark:bg-slate-900 dark:text-white">
    </div>

    <select name="per_page" onchange="this.form.submit()"
        class="rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-500/30 dark:border-white/10 dark:bg-slate-900 dark:text-slate-200">
        @foreach ([10, 25, 50, 100] as $n)
            <option value="{{ $n }}" @selected((int) $perPage === $n)>{{ $n }} / page</option>
        @endforeach
    </select>

    <button type="submit" class="btn-primary px-4 py-2.5">Search</button>

    @if (filled($value))
        <a href="{{ $action }}" class="btn-ghost px-4 py-2.5">Clear</a>
    @endif

    {{ $slot }}
</form>

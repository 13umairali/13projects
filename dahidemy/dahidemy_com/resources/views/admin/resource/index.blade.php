@extends('layouts.admin')

@section('title', $def['plural'])

@section('content')
    <x-admin.page-header :title="$def['plural']" :subtitle="$def['description'] ?? null">
        <a href="{{ route('admin.content.create', $resource) }}" class="btn-primary px-4 py-2.5">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M12 5v14M5 12h14" />
            </svg>
            New {{ \Illuminate\Support\Str::lower($def['singular']) }}
        </a>
    </x-admin.page-header>

    @unless ($reachable)
        <div
            class="mb-6 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-300">
            <span class="font-semibold">Live data unavailable.</span>
            The backend API could not be reached — check <code
                class="rounded bg-amber-100 px-1 dark:bg-amber-500/20">SAAS_API_URL</code> and that you are signed in.
        </div>
    @endunless

    <div class="card overflow-hidden p-0">
        @if (count($records))
            <div class="overflow-x-auto">
                <table class="w-full min-w-[36rem] text-left text-sm">
                    <thead>
                        <tr
                            class="border-b border-slate-200 text-xs uppercase tracking-wider text-slate-400 dark:border-white/10">
                            @foreach ($columns as $col)
                                <th class="px-5 py-3 font-semibold">{{ $col['label'] ?? $col['name'] }}</th>
                            @endforeach
                            <th class="px-5 py-3 text-right font-semibold">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-white/5">
                        @foreach ($records as $record)
                            <tr class="transition hover:bg-slate-50/70 dark:hover:bg-white/[0.02]">

                                @foreach ($columns as $col)
                                    @php
                                        $raw = $record[$col['name']] ?? null;

                                        $display =
                                            isset($col['options']) && $raw !== null && $raw !== ''
                                                ? $col['options'][$raw] ?? $raw
                                                : $raw;
                                    @endphp

                                    <td @class([
                                        'px-5 py-3 align-top',
                                        'font-semibold text-slate-900 dark:text-white' => $col['primary'] ?? false,
                                        'text-slate-600 dark:text-slate-300' => !($col['primary'] ?? false),
                                    ])>

                                        @if (($col['type'] ?? '') === 'image')
                                            @php
                                                $dir = $def['storage'] ?? $resource;
                                                $image = saas_image($display, $dir);
                                            @endphp

                                            <a href="{{ $image ?: saas_placeholder($col['kind'] ?? 'image') }}" target="_blank">
                                                <x-img :src="$display" :dir="$dir" :kind="$col['kind'] ?? 'image'"
                                                    :alt="$def['singular']"
                                                    class="h-14 w-20 rounded-lg border border-slate-200 shadow-sm transition hover:scale-105 dark:border-white/10" />
                                            </a>
                                        @else
                                            {{ $display !== null && $display !== '' ? \Illuminate\Support\Str::limit((string) $display, 90) : '—' }}
                                        @endif

                                    </td>
                                @endforeach

                                <td class="px-5 py-3 text-right whitespace-nowrap">
                                    <div class="inline-flex items-center gap-1">

                                        <a href="{{ route('admin.content.edit', [$resource, $record['id'] ?? null]) }}"
                                            class="grid h-8 w-8 place-items-center rounded-lg text-slate-500 transition hover:bg-slate-100 hover:text-brand-600 dark:text-slate-400 dark:hover:bg-white/5"
                                            title="Edit">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8"
                                                viewBox="0 0 24 24">
                                                <path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7" />
                                                <path d="M18.5 2.5a2.1 2.1 0 013 3L12 15l-4 1 1-4z" />
                                            </svg>
                                        </a>

                                        <form method="POST"
                                            action="{{ route('admin.content.destroy', [$resource, $record['id'] ?? null]) }}"
                                            onsubmit="return confirm('Delete this {{ \Illuminate\Support\Str::lower($def['singular']) }}? This cannot be undone.');">
                                            @csrf
                                            @method('DELETE')

                                            <button type="submit"
                                                class="grid h-8 w-8 place-items-center rounded-lg text-slate-500 transition hover:bg-rose-50 hover:text-rose-600 dark:text-slate-400 dark:hover:bg-rose-500/10"
                                                title="Delete">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8"
                                                    viewBox="0 0 24 24">
                                                    <path
                                                        d="M3 6h18M8 6V4a2 2 0 012-2h4a2 2 0 012 2v2M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6M10 11v6M14 11v6" />
                                                </svg>
                                            </button>

                                        </form>

                                    </div>
                                </td>

                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <x-admin.empty :title="'No ' . \Illuminate\Support\Str::lower($def['plural']) . ' yet'" subtitle="Create your first one to see it on the marketing site.">
                <a href="{{ route('admin.content.create', $resource) }}" class="btn-primary px-4 py-2.5">
                    New {{ \Illuminate\Support\Str::lower($def['singular']) }}
                </a>
            </x-admin.empty>
        @endif
    </div>

    @if (count($records))
        <p class="mt-4 text-xs text-slate-400 dark:text-slate-500">
            {{ count($records) }}
            {{ \Illuminate\Support\Str::plural(\Illuminate\Support\Str::lower($def['singular']), count($records)) }}
        </p>
    @endif
@endsection

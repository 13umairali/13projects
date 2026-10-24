@extends('layouts.admin')

@section('title', 'Institutes')

@section('content')
    @php
        // Sortable column header — toggles asc/desc and resets to page 1.
        $sortLink = function (string $col, string $label) use ($filters) {
            $isActive = $filters['order_by'] === $col;
            $nextOrder = $isActive && $filters['order'] === 'asc' ? 'desc' : 'asc';
            $url = request()->fullUrlWithQuery(['order_by' => $col, 'order' => $nextOrder, 'page' => 1]);
            $arrow = $isActive ? ($filters['order'] === 'asc' ? '&uarr;' : '&darr;') : '';
            return '<a href="' .
                $url .
                '" class="inline-flex items-center gap-1 transition hover:text-brand-600">' .
                e($label) .
                ' <span class="text-[10px]">' .
                $arrow .
                '</span></a>';
        };
    @endphp

    <x-admin.page-header title="Institutes" subtitle="Tenant schools on the platform — accounts, domains, plans and status.">
        <a href="{{ route('admin.institutes.create') }}" class="btn-primary px-4 py-2.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M12 5v14M5 12h14" />
            </svg>
            New institute
        </a>
    </x-admin.page-header>

    <x-admin.search :action="route('admin.institutes.index')" :value="$filters['search']" placeholder="Search name, email, domain, phone…" :per-page="$filters['per_page']"
        :hidden="['order_by' => $filters['order_by'], 'order' => $filters['order']]">
        <select name="status" onchange="this.form.submit()"
            class="rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-500/30 dark:border-white/10 dark:bg-slate-900 dark:text-slate-200">
            @foreach (['' => 'All statuses', '1' => 'Active only', '0' => 'Inactive only'] as $value => $label)
                <option value="{{ $value }}" @selected((string) $filters['status'] === (string) $value)>{{ $label }}</option>
            @endforeach
        </select>
    </x-admin.search>

    @unless ($reachable)
        <div
            class="px-4 py-3 mb-6 text-sm border rounded-xl border-amber-200 bg-amber-50 text-amber-800 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-300">
            <span class="font-semibold">Live data unavailable.</span>
            The backend API could not be reached — check <code
                class="px-1 rounded bg-amber-100 dark:bg-amber-500/20">SAAS_API_URL</code> and that you are signed in.
        </div>
    @endunless

    <div class="p-0 overflow-hidden card">
        @if ($institutes->isNotEmpty())
            <div class="overflow-x-auto">
                <table class="w-full min-w-[52rem] text-left text-sm">
                    <thead>
                        <tr
                            class="text-xs tracking-wider uppercase border-b border-slate-200 text-slate-400 dark:border-white/10">
                            <th class="px-5 py-3 font-semibold">{!! $sortLink('name', 'Institute') !!}</th>
                            <th class="px-5 py-3 font-semibold">Domain</th>
                            <th class="px-5 py-3 font-semibold">Branch</th>
                            <th class="px-5 py-3 font-semibold">Type</th>
                            <th class="px-5 py-3 font-semibold">Owner</th>
                            <th class="px-5 py-3 font-semibold">Plan</th>
                            <th class="px-5 py-3 font-semibold">{!! $sortLink('status', 'Status') !!}</th>
                            <th class="px-5 py-3 font-semibold">{!! $sortLink('created_at', 'Created') !!}</th>
                            <th class="px-5 py-3 font-semibold text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-white/5">
                        @foreach ($institutes as $inst)
                            @php
                                $name = $inst['name'] ?? '—';
                                $isActive = \App\Support\Status::isActive($inst['status'] ?? 0);
                            @endphp
                            {{-- An inactive tenant is tinted and edge-marked, so it is obvious at a
                                 glance which rows are switched off — not just from the pill. --}}
                            <tr @class([
                                'border-l-4 transition',
                                'border-l-transparent hover:bg-slate-50/70 dark:hover:bg-white/[0.02]' => $isActive,
                                'border-l-rose-400 bg-rose-50/40 hover:bg-rose-50/70 dark:bg-rose-500/[0.04] dark:hover:bg-rose-500/[0.07]' => !$isActive,
                            ])>
                                <td class="px-5 py-3">
                                    <div class="flex items-center gap-3">
                                        <span
                                            class="grid h-9 w-9 flex-none place-items-center overflow-hidden rounded-lg bg-brand-50 text-sm font-bold text-brand-600 dark:bg-brand-500/10 dark:text-brand-300 {{ $isActive ? '' : 'opacity-50 grayscale' }}">
                                            <x-img :src="$inst['logo'] ?? null" :dir="\App\Support\ImageUrl::DIR_INSTITUTE" kind="logo" :alt="$name"
                                                class="w-full h-full" />
                                        </span>
                                        <div class="min-w-0">
                                            <p @class([
                                                'truncate font-semibold',
                                                'text-slate-900 dark:text-white' => $isActive,
                                                'text-slate-500 dark:text-slate-400' => !$isActive,
                                            ])>
                                                {{ $name }}
                                            </p>
                                            <p class="text-xs truncate text-slate-400">{{ $inst['email'] ?? '' }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-3 text-slate-600 dark:text-slate-300">{{ $inst['domain'] ?? '—' }}</td>
                                <td class="px-5 py-3 text-slate-600 dark:text-slate-300">
                                    {{ count($inst['branches'] ?? []) }}
                                </td>
                                <td class="px-5 py-3 text-slate-600 dark:text-slate-300">
                                    {{ $inst['institute_type'] ?? '—' }}</td>
                                <td class="px-5 py-3 text-slate-600 dark:text-slate-300">{{ $inst['owner_name'] ?? '—' }}
                                </td>
                                <td class="px-5 py-3 text-slate-600 dark:text-slate-300">{{ $inst['plan_name'] ?? '—' }}
                                </td>
                                <td class="px-5 py-3">
                                    <x-admin.status-pill :active="$inst['status'] ?? 0" />
                                </td>
                                <td class="px-5 py-3 text-slate-500 dark:text-slate-400">
                                    {{ !empty($inst['created_at']) ? \Illuminate\Support\Carbon::parse($inst['created_at'])->format('d M Y') : '—' }}
                                </td>
                                <td class="px-5 py-3 text-right whitespace-nowrap">
                                    <div class="inline-flex items-center gap-1">
                                        <form method="POST"
                                            action="{{ route('admin.institutes.toggle-status', $inst['id']) }}"
                                            title="{{ $isActive ? 'Deactivate' : 'Activate' }}">
                                            @csrf
                                            <button type="submit"
                                                class="grid w-8 h-8 transition rounded-lg place-items-center text-slate-500 hover:bg-slate-100 hover:text-amber-600 dark:text-slate-400 dark:hover:bg-white/5">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8"
                                                    viewBox="0 0 24 24">
                                                    <path d="M18.36 6.64A9 9 0 1120.77 15M12 8v4" />
                                                    <path d="M12 12l3 2" />
                                                </svg>
                                            </button>
                                        </form>
                                        {{-- Login as institute: opens the impersonated session in a NEW tab so
                                             the SaaS admin's own panel session stays untouched in this one. The
                                             backend re-checks that the caller is a platform admin and mints a
                                             single-use, short-lived link — no institute credentials are involved. --}}
                                        @php $safeImpersonateName = str_replace("'", '', $name); @endphp
                                        <form method="POST"
                                            action="{{ route('admin.institutes.impersonate', $inst['id']) }}"
                                            target="_blank" title="Login as institute (opens in a new tab)"
                                            onsubmit="return confirm('Log in as {{ $safeImpersonateName }}? You will open a secure impersonation session in a new tab. Your SaaS Admin session here stays active.');">
                                            @csrf
                                            <button type="submit" aria-label="Login as institute"
                                                class="grid w-8 h-8 transition rounded-lg place-items-center text-slate-500 hover:bg-slate-100 hover:text-emerald-600 dark:text-slate-400 dark:hover:bg-white/5">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8"
                                                    viewBox="0 0 24 24">
                                                    <path
                                                        d="M15 3h4a2 2 0 012 2v14a2 2 0 01-2 2h-4M10 17l5-5-5-5M15 12H3" />
                                                </svg>
                                            </button>
                                        </form>
                                        <a href="{{ route('admin.institutes.show', $inst['id']) }}"
                                            class="grid w-8 h-8 transition rounded-lg place-items-center text-slate-500 hover:bg-slate-100 hover:text-brand-600 dark:text-slate-400 dark:hover:bg-white/5"
                                            title="View">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8"
                                                viewBox="0 0 24 24">
                                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                                                <circle cx="12" cy="12" r="3" />
                                            </svg>
                                        </a>
                                        <a href="{{ route('admin.institutes.edit', $inst['id']) }}"
                                            class="grid w-8 h-8 transition rounded-lg place-items-center text-slate-500 hover:bg-slate-100 hover:text-brand-600 dark:text-slate-400 dark:hover:bg-white/5"
                                            title="Edit">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8"
                                                viewBox="0 0 24 24">
                                                <path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7" />
                                                <path d="M18.5 2.5a2.1 2.1 0 013 3L12 15l-4 1 1-4z" />
                                            </svg>
                                        </a>
                                        @php
                                            $safeName = str_replace("'", '', $name);
                                        @endphp

                                        <form method="POST" action="{{ route('admin.institutes.destroy', $inst['id']) }}"
                                            onsubmit="return confirm('Permanently delete {{ $safeName }}? This wipes the school and ALL of its data — branches, users, students, subscriptions, settings. It cannot be undone.');">
                                            @csrf
                                            @method('DELETE')

                                            <button type="submit"
                                                class="grid w-8 h-8 transition rounded-lg place-items-center text-slate-500 hover:bg-rose-50 hover:text-rose-600 dark:text-slate-400 dark:hover:bg-rose-500/10"
                                                title="Delete">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8"
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
            <x-admin.empty title="No institutes found" :subtitle="filled($filters['search'])
                ? 'No institutes match your search.'
                : 'Onboard your first tenant to get started.'">
                <a href="{{ route('admin.institutes.create') }}" class="btn-primary px-4 py-2.5">New institute</a>
            </x-admin.empty>
        @endif
    </div>

    @if ($institutes->hasPages())
        <div class="mt-5">
            {{ $institutes->onEachSide(1)->links() }}
        </div>
    @endif

    @if ($institutes->isNotEmpty())
        <p class="mt-3 text-xs text-slate-400 dark:text-slate-500">
            Showing {{ $institutes->firstItem() }}–{{ $institutes->lastItem() }} of {{ $institutes->total() }} institutes
        </p>
    @endif
@endsection

@extends('layouts.admin')

@section('title', 'Payment gateways')

@section('content')
    <x-admin.page-header title="Payment gateways"
        subtitle="Configure the gateways institutes can pay through. Credentials are stored securely on the backend." />

    @unless ($reachable)
        <div class="mb-6 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-300">
            <span class="font-semibold">Live data unavailable.</span>
            The backend API could not be reached — check <code class="rounded bg-amber-100 px-1 dark:bg-amber-500/20">SAAS_API_URL</code> and that you are signed in.
        </div>
    @endunless

    <div class="card overflow-hidden p-0">
        @if ($gateways->isNotEmpty())
            <div class="overflow-x-auto">
                <table class="w-full min-w-[40rem] text-left text-sm">
                    <thead>
                        <tr class="border-b border-slate-200 text-xs uppercase tracking-wider text-slate-400 dark:border-white/10">
                            <th class="px-5 py-3 font-semibold">Gateway</th>
                            <th class="px-5 py-3 font-semibold">Mode</th>
                            <th class="px-5 py-3 font-semibold">Status</th>
                            <th class="px-5 py-3 text-right font-semibold">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-white/5">
                        @foreach ($gateways as $row)
                            @php $active = (string) ($row['status'] ?? '0') === '1'; @endphp
                            <tr class="transition hover:bg-slate-50/70 dark:hover:bg-white/[0.02]">
                                <td class="px-5 py-3 font-semibold capitalize text-slate-900 dark:text-white">
                                    {{ str_replace('_', ' ', $row['name'] ?? '—') }}
                                </td>
                                <td class="px-5 py-3">
                                    <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold uppercase tracking-wide text-slate-600 dark:bg-white/5 dark:text-slate-300">
                                        {{ $row['mode'] ?? 'test' }}
                                    </span>
                                </td>
                                <td class="px-5 py-3"><x-admin.status-pill :active="$active" /></td>
                                <td class="px-5 py-3 text-right whitespace-nowrap">
                                    <div class="inline-flex items-center gap-1">
                                        <form method="POST" action="{{ route('admin.payments.toggle-status', $row['id'] ?? null) }}"
                                            title="{{ $active ? 'Deactivate' : 'Activate' }}">
                                            @csrf
                                            <button type="submit" class="grid h-8 w-8 place-items-center rounded-lg text-slate-500 transition hover:bg-slate-100 hover:text-amber-600 dark:text-slate-400 dark:hover:bg-white/5">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M18.36 6.64A9 9 0 1120.77 15M12 8v4"/><path d="M12 12l3 2"/></svg>
                                            </button>
                                        </form>
                                        <a href="{{ route('admin.payments.edit', $row['id'] ?? null) }}"
                                            class="grid h-8 w-8 place-items-center rounded-lg text-slate-500 transition hover:bg-slate-100 hover:text-brand-600 dark:text-slate-400 dark:hover:bg-white/5" title="Edit">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.1 2.1 0 013 3L12 15l-4 1 1-4z"/></svg>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <x-admin.empty title="No payment gateways configured"
                subtitle="Gateway configurations from the backend will appear here." />
        @endif
    </div>
@endsection

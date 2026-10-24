@extends('layouts.admin')

@section('title', 'Upgrade requests')

@section('content')
    <x-admin.page-header title="Upgrade requests"
        subtitle="Pending subscription upgrades raised by institutes. Approving activates the new plan.">
        <a href="{{ route('admin.subscriptions.upgrade') }}" class="btn-ghost px-4 py-2.5">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
            Manual upgrade
        </a>
    </x-admin.page-header>

    <x-admin.search :action="route('admin.subscriptions.requests')" :value="$search"
        placeholder="Search institute name, email, phone…" :per-page="$perPage" />

    @unless ($reachable)
        <div class="mb-6 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-300">
            <span class="font-semibold">Live data unavailable.</span>
            The backend API could not be reached — check <code class="rounded bg-amber-100 px-1 dark:bg-amber-500/20">SAAS_API_URL</code> and that you are signed in.
        </div>
    @endunless

    <div class="card overflow-hidden p-0">
        @if ($requests->isNotEmpty())
            <div class="overflow-x-auto">
                <table class="w-full min-w-[48rem] text-left text-sm">
                    <thead>
                        <tr class="border-b border-slate-200 text-xs uppercase tracking-wider text-slate-400 dark:border-white/10">
                            <th class="px-5 py-3 font-semibold">Institute</th>
                            <th class="px-5 py-3 font-semibold">Requested plan</th>
                            <th class="px-5 py-3 font-semibold">Amount</th>
                            <th class="px-5 py-3 font-semibold">Extra days</th>
                            <th class="px-5 py-3 font-semibold">Requested</th>
                            <th class="px-5 py-3 text-right font-semibold">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-white/5">
                        @foreach ($requests as $row)
                            <tr class="transition hover:bg-slate-50/70 dark:hover:bg-white/[0.02]">
                                <td class="px-5 py-3">
                                    <p class="font-semibold text-slate-900 dark:text-white">{{ data_get($row, 'institute.name', '—') }}</p>
                                    <p class="text-xs text-slate-400">{{ data_get($row, 'institute.email', '') }}</p>
                                </td>
                                <td class="px-5 py-3 text-slate-600 dark:text-slate-300">{{ data_get($row, 'plan.name', '—') }}</td>
                                <td class="px-5 py-3 text-slate-600 dark:text-slate-300">{{ number_format((float) ($row['amount_paid'] ?? 0), 2) }}</td>
                                <td class="px-5 py-3 text-slate-600 dark:text-slate-300">{{ $row['extra_days'] ?? 0 }}</td>
                                <td class="px-5 py-3 text-slate-500 dark:text-slate-400">
                                    {{ !empty($row['created_at']) ? \Illuminate\Support\Carbon::parse($row['created_at'])->format('d M Y') : '—' }}
                                </td>
                                <td class="px-5 py-3 text-right whitespace-nowrap">
                                    <form method="POST" action="{{ route('admin.subscriptions.requests.approve', $row['id'] ?? null) }}"
                                        onsubmit="return confirm('Approve this upgrade request?');">
                                        @csrf
                                        <button type="submit" class="btn-primary px-3 py-2 text-xs">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5"/></svg>
                                            Approve
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <x-admin.empty title="No pending upgrade requests"
                subtitle="Requests raised by institutes will appear here for approval." />
        @endif
    </div>

    @if ($requests->hasPages())
        <div class="mt-5">{{ $requests->onEachSide(1)->links() }}</div>
    @endif
@endsection

@extends('layouts.admin')

@section('title', 'Onboarding')

@section('content')
    <x-admin.page-header title="Onboarding queue"
        subtitle="Institutions awaiting approval. Approving provisions the tenant, owner login and trial subscription." />

    @unless ($reachable)
        <div class="mb-6 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-300">
            <span class="font-semibold">Live data unavailable.</span>
            The backend API could not be reached — check <code class="rounded bg-amber-100 px-1 dark:bg-amber-500/20">SAAS_API_URL</code> and that you are signed in.
        </div>
    @endunless

    <div class="card overflow-hidden p-0">
        @if ($onboardings->isNotEmpty())
            <div class="overflow-x-auto">
                <table class="w-full min-w-[52rem] text-left text-sm">
                    <thead>
                        <tr class="border-b border-slate-200 text-xs uppercase tracking-wider text-slate-400 dark:border-white/10">
                            <th class="px-5 py-3 font-semibold">Institute</th>
                            <th class="px-5 py-3 font-semibold">Domain</th>
                            <th class="px-5 py-3 font-semibold">Type</th>
                            <th class="px-5 py-3 font-semibold">Owner</th>
                            <th class="px-5 py-3 font-semibold">Requested</th>
                            <th class="px-5 py-3 text-right font-semibold">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-white/5">
                        @foreach ($onboardings as $row)
                            @php $d = (array) ($row['collected_data'] ?? []); @endphp
                            <tr class="transition hover:bg-slate-50/70 dark:hover:bg-white/[0.02]">
                                <td class="px-5 py-3">
                                    <p class="font-semibold text-slate-900 dark:text-white">{{ $d['institute_name'] ?? '—' }}</p>
                                    <p class="text-xs text-slate-400">{{ $d['institute_email'] ?? '' }} · {{ $d['institute_phone'] ?? '' }}</p>
                                </td>
                                <td class="px-5 py-3 text-slate-600 dark:text-slate-300">{{ $d['institute_domain'] ?? '—' }}</td>
                                <td class="px-5 py-3 text-slate-600 dark:text-slate-300">{{ $d['institute_type'] ?? '—' }}</td>
                                <td class="px-5 py-3 text-slate-600 dark:text-slate-300">
                                    <p>{{ $d['user_name'] ?? '—' }}</p>
                                    <p class="text-xs text-slate-400">{{ $d['user_email'] ?? '' }} · {{ $d['user_phone'] ?? '' }}</p>
                                </td>
                                <td class="px-5 py-3 text-slate-500 dark:text-slate-400">
                                    {{ !empty($row['created_at']) ? \Illuminate\Support\Carbon::parse($row['created_at'])->format('d M Y') : '—' }}
                                </td>
                                <td class="px-5 py-3 text-right whitespace-nowrap">
                                    <form method="POST" action="{{ route('admin.onboarding.approve', $row['id'] ?? null) }}"
                                        onsubmit="return confirm('Approve {{ str_replace("'", '', $d['institute_name'] ?? 'this institute') }}? This provisions the tenant and cannot be undone.');">
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
            <x-admin.empty title="No pending onboardings"
                subtitle="New institution registrations will appear here for review." />
        @endif
    </div>

    @if ($onboardings->hasPages())
        <div class="mt-5">{{ $onboardings->onEachSide(1)->links() }}</div>
    @endif
@endsection

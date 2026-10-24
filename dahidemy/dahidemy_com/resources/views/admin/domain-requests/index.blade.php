@extends('layouts.admin')

@section('title', 'Domain requests')

@section('content')
    <x-admin.page-header title="Domain requests"
        subtitle="Custom domains submitted by schools. Approving writes the domain onto the institute immediately." />

    @unless ($reachable)
        <div class="mb-6 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-300">
            <span class="font-semibold">Live data unavailable.</span>
            The backend API could not be reached — check <code class="rounded bg-amber-100 px-1 dark:bg-amber-500/20">SAAS_API_URL</code> and that you are signed in.
        </div>
    @endunless

    <div class="mb-5 flex gap-2 border-b border-slate-200 dark:border-white/10">
        @foreach (['pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected'] as $value => $label)
            <a href="{{ route('admin.domain-requests.index', ['status' => $value]) }}"
                class="border-b-2 px-4 py-2 text-sm font-semibold transition
                    {{ $status === $value
                        ? 'border-indigo-600 text-indigo-600 dark:border-indigo-400 dark:text-indigo-400'
                        : 'border-transparent text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>

    <div class="card overflow-hidden p-0">
        @if ($requests->isNotEmpty())
            <div class="overflow-x-auto">
                <table class="w-full min-w-[52rem] text-left text-sm">
                    <thead>
                        <tr class="border-b border-slate-200 text-xs uppercase tracking-wider text-slate-400 dark:border-white/10">
                            <th class="px-5 py-3 font-semibold">School</th>
                            <th class="px-5 py-3 font-semibold">Requested domain</th>
                            <th class="px-5 py-3 font-semibold">Note</th>
                            <th class="px-5 py-3 font-semibold">Submitted</th>
                            @if ($status === 'pending')
                                <th class="px-5 py-3 text-right font-semibold">Actions</th>
                            @else
                                <th class="px-5 py-3 font-semibold">Reviewed by</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-white/5">
                        @foreach ($requests as $row)
                            <tr class="transition hover:bg-slate-50/70 dark:hover:bg-white/[0.02]">
                                <td class="px-5 py-3">
                                    <p class="font-semibold text-slate-900 dark:text-white">{{ $row['institute']['name'] ?? '—' }}</p>
                                    <p class="text-xs text-slate-400">current: {{ $row['institute']['domain'] ?? '—' }}</p>
                                </td>
                                <td class="px-5 py-3 text-slate-600 dark:text-slate-300">{{ $row['requested_domain'] ?? '—' }}</td>
                                <td class="px-5 py-3 text-slate-500 dark:text-slate-400">{{ $row['note'] ?? '—' }}</td>
                                <td class="px-5 py-3 text-slate-500 dark:text-slate-400">
                                    {{ !empty($row['created_at']) ? \Illuminate\Support\Carbon::parse($row['created_at'])->format('d M Y') : '—' }}
                                </td>
                                @if ($status === 'pending')
                                    <td class="px-5 py-3 text-right whitespace-nowrap">
                                        <div class="inline-flex gap-2">
                                            <form method="POST" action="{{ route('admin.domain-requests.approve', $row['id']) }}"
                                                onsubmit="return confirm('Approve {{ str_replace("'", '', $row['requested_domain'] ?? 'this domain') }}? This updates the school\'s live domain immediately.');">
                                                @csrf
                                                <button type="submit" class="btn-primary px-3 py-2 text-xs">Approve</button>
                                            </form>
                                            <form method="POST" action="{{ route('admin.domain-requests.reject', $row['id']) }}">
                                                @csrf
                                                <button type="submit" class="rounded-lg border border-slate-300 px-3 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-50 dark:border-white/10 dark:text-slate-300 dark:hover:bg-white/5">Reject</button>
                                            </form>
                                        </div>
                                    </td>
                                @else
                                    <td class="px-5 py-3 text-slate-500 dark:text-slate-400">{{ $row['reviewer']['name'] ?? '—' }}</td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <x-admin.empty title="No {{ $status }} domain requests"
                subtitle="School-submitted custom domain requests will appear here." />
        @endif
    </div>

    @if ($requests->hasPages())
        <div class="mt-5">{{ $requests->onEachSide(1)->links() }}</div>
    @endif
@endsection

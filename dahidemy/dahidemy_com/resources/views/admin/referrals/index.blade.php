@extends('layouts.admin')

@section('title', 'Referrals')

@section('content')
    <x-admin.page-header title="Referrals"
        subtitle="Institutes and individual users (students, parents, teachers, accountants, staff...) who referred a new institute. Approving applies the configured reward.">
        <a href="{{ route('admin.referrals.settings') }}" class="btn-ghost px-4 py-2.5">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 15a3 3 0 100-6 3 3 0 000 6z"/><path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 11-2.83 2.83l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 11-4 0v-.09a1.65 1.65 0 00-1-1.51 1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 11-2.83-2.83l.06-.06a1.65 1.65 0 00.33-1.82 1.65 1.65 0 00-1.51-1H3a2 2 0 110-4h.09a1.65 1.65 0 001.51-1 1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 112.83-2.83l.06.06a1.65 1.65 0 001.82.33H9a1.65 1.65 0 001-1.51V3a2 2 0 114 0v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 112.83 2.83l-.06.06a1.65 1.65 0 00-.33 1.82V9a1.65 1.65 0 001.51 1H21a2 2 0 110 4h-.09a1.65 1.65 0 00-1.51 1z"/></svg>
            Reward settings
        </a>
    </x-admin.page-header>

    <div class="mb-6 flex gap-2">
        @foreach (['' => 'All', 'converted' => 'Awaiting Approval', 'rewarded' => 'Rewarded', 'rejected' => 'Rejected'] as $value => $label)
            <a href="{{ route('admin.referrals.index', array_filter(['status' => $value])) }}"
                class="{{ $status === $value ? 'btn-primary' : 'btn-ghost' }} px-4 py-2 text-sm">
                {{ $label }}
            </a>
        @endforeach
    </div>

    @unless ($reachable)
        <div class="mb-6 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-300">
            <span class="font-semibold">Live data unavailable.</span>
            The backend API could not be reached — check <code class="rounded bg-amber-100 px-1 dark:bg-amber-500/20">SAAS_API_URL</code> and that you are signed in.
        </div>
    @endunless

    <div class="card overflow-hidden p-0">
        @if ($referrals->isNotEmpty())
            <div class="overflow-x-auto">
                <table class="w-full min-w-[56rem] text-left text-sm">
                    <thead>
                        <tr class="border-b border-slate-200 text-xs uppercase tracking-wider text-slate-400 dark:border-white/10">
                            <th class="px-5 py-3 font-semibold">Referrer</th>
                            <th class="px-5 py-3 font-semibold">Role</th>
                            <th class="px-5 py-3 font-semibold">Referred Institute</th>
                            <th class="px-5 py-3 font-semibold">Status</th>
                            <th class="px-5 py-3 font-semibold">Reward</th>
                            <th class="px-5 py-3 font-semibold">Date</th>
                            <th class="px-5 py-3 text-right font-semibold">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-white/5">
                        @foreach ($referrals as $row)
                            <tr class="transition hover:bg-slate-50/70 dark:hover:bg-white/[0.02]">
                                <td class="px-5 py-3">
                                    <p class="font-semibold text-slate-900 dark:text-white">{{ $row['referrer_name'] ?? '—' }}</p>
                                    @if (! empty($row['referrer_email']))
                                        <p class="text-xs text-slate-500">{{ $row['referrer_email'] }}</p>
                                    @endif
                                    <p class="text-xs text-slate-400">Code: {{ $row['referral_code_used'] ?? '' }}</p>
                                </td>
                                <td class="px-5 py-3 text-slate-600 dark:text-slate-300">
                                    <span class="inline-flex rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-medium capitalize dark:bg-white/5">
                                        {{ $row['referrer_role'] ?? '—' }}
                                    </span>
                                </td>
                                <td class="px-5 py-3 text-slate-600 dark:text-slate-300">{{ $row['referred_institute'] ?? '—' }}</td>
                                <td class="px-5 py-3">
                                    @php $rowStatus = $row['status'] ?? 'pending'; @endphp
                                    @if ($rowStatus === 'rewarded')
                                        <span class="inline-flex rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-semibold text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300">Rewarded</span>
                                    @elseif ($rowStatus === 'converted')
                                        <span class="inline-flex rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-semibold text-blue-700 dark:bg-blue-500/10 dark:text-blue-300">Awaiting Approval</span>
                                    @elseif ($rowStatus === 'rejected')
                                        <span class="inline-flex rounded-full bg-rose-100 px-2.5 py-0.5 text-xs font-semibold text-rose-700 dark:bg-rose-500/10 dark:text-rose-300">Rejected</span>
                                    @else
                                        <span class="inline-flex rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-semibold text-slate-600 dark:bg-white/5 dark:text-slate-300">Pending</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3 text-slate-600 dark:text-slate-300">
                                    @if (! empty($row['reward_type']))
                                        {{ $row['reward_value'] ?? 0 }}
                                        {{ match(true) {
                                            $row['reward_type'] === 'days' => 'days',
                                            $row['reward_type'] === 'cash' => 'USD',
                                            // Once actually rewarded, reward_value has already been
                                            // converted from the raw percentage into the calculated
                                            // dollar amount (see ReferralService::approveReward) - so
                                            // it reads as USD from that point on, not as "X% of...".
                                            $row['reward_type'] === 'percentage' && ($row['status'] ?? '') === 'rewarded' => 'USD',
                                            $row['reward_type'] === 'percentage' => '% of package (pending)',
                                            default => 'points',
                                        } }}
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="px-5 py-3 text-slate-500 dark:text-slate-400">
                                    {{ !empty($row['created_at']) ? \Illuminate\Support\Carbon::parse($row['created_at'])->format('d M Y') : '—' }}
                                </td>
                                <td class="px-5 py-3 text-right whitespace-nowrap">
                                    @if (($row['status'] ?? '') === 'converted')
                                        <div class="flex justify-end gap-2">
                                            <form method="POST" action="{{ route('admin.referrals.reject', $row['id'] ?? null) }}"
                                                onsubmit="return confirm('Reject this referral? No reward will be given.');">
                                                @csrf
                                                <button type="submit" class="btn-ghost px-3 py-2 text-xs">Reject</button>
                                            </form>
                                            <form method="POST" action="{{ route('admin.referrals.approve', $row['id'] ?? null) }}"
                                                onsubmit="return confirm('Approve this referral and apply the reward?');">
                                                @csrf
                                                <button type="submit" class="btn-primary px-3 py-2 text-xs">
                                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5"/></svg>
                                                    Approve
                                                </button>
                                            </form>
                                        </div>
                                    @else
                                        <span class="text-xs text-slate-400">—</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <x-admin.empty title="No referrals yet"
                subtitle="Referrals made using anyone's referral code — institute or individual user — will appear here." />
        @endif
    </div>

    @if ($referrals->hasPages())
        <div class="mt-5">{{ $referrals->onEachSide(1)->links() }}</div>
    @endif
@endsection
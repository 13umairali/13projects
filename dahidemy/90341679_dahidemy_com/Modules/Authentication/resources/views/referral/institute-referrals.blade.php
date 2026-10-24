@extends('layouts.backend')

@section('page_title_breadcrumb')
    Institute Referrals
@endsection

@section('content')

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="alert alert-light-info d-flex align-items-center mb-5">
        <i class="fa fa-info-circle fs-3 me-3"></i>
        <div>
            Referrals made by your own students, parents, teachers, accountants, and staff, where a reward rule you
            configured applies. Approving here only affects the reward YOUR institute owes them - the platform's own
            separate reward (if any) is reviewed by the SaaS admin independently.
            <a href="{{ route('institute-referral-settings.index') }}">Manage reward rules</a>.
        </div>
    </div>

    <div class="mb-4 d-flex gap-2">
        @foreach (['' => 'All', 'pending' => 'Awaiting Approval', 'rewarded' => 'Rewarded', 'rejected' => 'Rejected'] as $value => $label)
            <a href="{{ route('institute-referrals.index', array_filter(['status' => $value])) }}"
                class="btn btn-sm {{ $status === $value ? 'btn-primary' : 'btn-outline-secondary' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>

    <div class="card">
        <div class="card-body">
            @if ($referrals->isEmpty())
                <p class="text-muted mb-0">No referrals with an institute-level reward yet.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-row-bordered align-middle">
                        <thead>
                            <tr class="text-muted">
                                <th>Referrer</th>
                                <th>Role</th>
                                <th>Referred Institute</th>
                                <th>Reward</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($referrals as $referral)
                                <tr>
                                    <td>{{ $referral->referrer()?->name ?? '(deleted)' }}</td>
                                    <td class="text-capitalize">{{ $referral->referrer_role }}</td>
                                    <td>{{ $referral->referredInstitute?->name ?? '—' }}</td>
                                    <td>
                                        {{ $referral->institute_reward_value }}
                                        {{ match($referral->institute_reward_type) {
                                            'fee_discount' => 'Fee Discount',
                                            'points' => 'Points',
                                            'cash_withdrawal' => 'Cash',
                                            default => '',
                                        } }}
                                    </td>
                                    <td>
                                        @if ($referral->institute_reward_status === 'rewarded')
                                            <span class="badge bg-success">Rewarded</span>
                                        @elseif ($referral->institute_reward_status === 'rejected')
                                            <span class="badge bg-danger">Rejected</span>
                                        @else
                                            <span class="badge bg-secondary">Pending</span>
                                        @endif
                                    </td>
                                    <td>{{ $referral->created_at->format('d M Y') }}</td>
                                    <td class="text-end">
                                        @if ($referral->institute_reward_status === 'pending')
                                            <form method="POST" action="{{ route('institute-referrals.reject', $referral->id) }}" class="d-inline"
                                                onsubmit="return confirm('Reject this reward?');">
                                                @csrf
                                                <button class="btn btn-sm btn-outline-danger">Reject</button>
                                            </form>
                                            <form method="POST" action="{{ route('institute-referrals.approve', $referral->id) }}" class="d-inline"
                                                onsubmit="return confirm('Approve and mark this reward as given?');">
                                                @csrf
                                                <button class="btn btn-sm btn-primary">Approve</button>
                                            </form>
                                        @else
                                            <span class="text-muted small">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">{{ $referrals->links() }}</div>
            @endif
        </div>
    </div>
@endsection
@extends('layouts.backend')

@section('page_title_breadcrumb')
    Member Referrals
@endsection

@section('content')

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="alert alert-light-info d-flex align-items-center mb-5">
        <i class="fa fa-info-circle fs-3 me-3"></i>
        <div>
            Everyone who referred a new person into your institute - a student referring a student, a teacher
            referring a colleague, and so on. Full contact details for both people are shown here so you can verify
            and follow up before approving a reward.
            <a href="{{ route('institute-referral-settings.index') }}">Manage reward rules</a>.
        </div>
    </div>

    <div class="mb-4 d-flex gap-2">
        @foreach (['' => 'All', 'enrolled' => 'Awaiting Approval', 'rewarded' => 'Rewarded', 'rejected' => 'Rejected'] as $value => $label)
            <a href="{{ route('internal-referrals.index', array_filter(['status' => $value])) }}"
                class="btn btn-sm {{ $status === $value ? 'btn-primary' : 'btn-outline-secondary' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>

    <div class="card">
        <div class="card-body">
            @if ($referrals->isEmpty())
                <p class="text-muted mb-0">No member referrals yet.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-row-bordered align-middle">
                        <thead>
                            <tr class="text-muted">
                                <th>Referrer</th>
                                <th>Referred Person</th>
                                <th>Code Used</th>
                                <th>Reward</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($referrals as $referral)
                                <tr>
                                    <td>
                                        <p class="fw-bold mb-0">{{ $referral->referrer?->name ?? '(deleted)' }}</p>
                                        <p class="text-muted small mb-0">{{ $referral->referrer?->email }}</p>
                                        <p class="text-muted small mb-0">{{ $referral->referrer?->phone }}</p>
                                        <span class="badge bg-light text-dark text-capitalize mt-1">{{ $referral->referrer_user_type }}</span>
                                    </td>
                                    <td>
                                        <p class="fw-bold mb-0">{{ $referral->referredUser?->name ?? '(deleted)' }}</p>
                                        <p class="text-muted small mb-0">{{ $referral->referredUser?->email }}</p>
                                        <p class="text-muted small mb-0">{{ $referral->referredUser?->phone }}</p>
                                        <span class="badge bg-light text-dark text-capitalize mt-1">{{ $referral->referred_user_type }}</span>
                                        @if ($referral->referred_class_label)
                                            <span class="badge bg-light-primary text-primary mt-1">{{ $referral->referred_class_label }}</span>
                                        @endif
                                    </td>
                                    <td><code>{{ $referral->referral_code_used }}</code></td>
                                    <td>
                                        @if ($referral->reward_type)
                                            {{ $referral->reward_value }}
                                            {{ match($referral->reward_type) {
                                                'fee_discount' => 'Fee Discount',
                                                'points' => 'Points',
                                                'cash_withdrawal' => 'Cash',
                                                default => '',
                                            } }}
                                        @else
                                            <span class="text-muted">No rule configured</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($referral->status === 'rewarded')
                                            <span class="badge bg-success">Rewarded</span>
                                        @elseif ($referral->status === 'rejected')
                                            <span class="badge bg-danger">Rejected</span>
                                        @else
                                            <span class="badge bg-secondary">Awaiting Approval</span>
                                        @endif
                                    </td>
                                    <td>{{ $referral->created_at->format('d M Y') }}</td>
                                    <td class="text-end">
                                        @if ($referral->status === 'enrolled' && $referral->reward_type)
                                            <form method="POST" action="{{ route('internal-referrals.reject', $referral->id) }}" class="d-inline"
                                                onsubmit="return confirm('Reject this reward?');">
                                                @csrf
                                                <button class="btn btn-sm btn-outline-danger">Reject</button>
                                            </form>
                                            <form method="POST" action="{{ route('internal-referrals.approve', $referral->id) }}" class="d-inline"
                                                onsubmit="return confirm('Approve and apply this reward?');">
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
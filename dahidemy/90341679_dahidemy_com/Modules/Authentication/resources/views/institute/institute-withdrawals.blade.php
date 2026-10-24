@extends('layouts.backend')

@section('page_title_breadcrumb')
    Withdrawal Requests
@endsection

@section('content')

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="mb-4 d-flex gap-2">
        @foreach (['' => 'All', 'pending' => 'Pending', 'approved' => 'Approved', 'paid' => 'Paid', 'rejected' => 'Rejected'] as $value => $label)
            <a href="{{ route('institute-withdrawals.index', array_filter(['status' => $value])) }}"
                class="btn btn-sm {{ $status === $value ? 'btn-primary' : 'btn-outline-secondary' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>

    <div class="card">
        <div class="card-body">
            @if ($requests->isEmpty())
                <p class="text-muted mb-0">No withdrawal requests yet.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-row-bordered align-middle">
                        <thead>
                            <tr class="text-muted">
                                <th>Requested By</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($requests as $withdrawal)
                                <tr>
                                    <td>{{ $withdrawal->user?->name ?? '(deleted)' }}</td>
                                    <td>${{ number_format($withdrawal->amount, 2) }}</td>
                                    <td>
                                        @if ($withdrawal->status === 'paid')
                                            <span class="badge bg-success">Paid</span>
                                        @elseif ($withdrawal->status === 'approved')
                                            <span class="badge bg-info">Approved</span>
                                        @elseif ($withdrawal->status === 'rejected')
                                            <span class="badge bg-danger">Rejected</span>
                                        @else
                                            <span class="badge bg-secondary">Pending</span>
                                        @endif
                                    </td>
                                    <td>{{ $withdrawal->created_at->format('d M Y') }}</td>
                                    <td class="text-end">
                                        @if ($withdrawal->status === 'pending')
                                            <form method="POST" action="{{ route('institute-withdrawals.reject', $withdrawal->id) }}" class="d-inline"
                                                onsubmit="return confirm('Reject this withdrawal request?');">
                                                @csrf
                                                <button class="btn btn-sm btn-outline-danger">Reject</button>
                                            </form>
                                            <form method="POST" action="{{ route('institute-withdrawals.approve', $withdrawal->id) }}" class="d-inline"
                                                onsubmit="return confirm('Approve this withdrawal request?');">
                                                @csrf
                                                <button class="btn btn-sm btn-primary">Approve</button>
                                            </form>
                                        @elseif ($withdrawal->status === 'approved')
                                            <form method="POST" action="{{ route('institute-withdrawals.mark-paid', $withdrawal->id) }}" class="d-inline"
                                                onsubmit="return confirm('Confirm you have actually sent this payment?');">
                                                @csrf
                                                <button class="btn btn-sm btn-success">Mark Paid</button>
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

                <div class="mt-4">{{ $requests->links() }}</div>
            @endif
        </div>
    </div>
@endsection
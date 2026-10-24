@extends('layouts.backend')

@section('page_title_breadcrumb')
    My Referrals
@endsection

@section('content')

    <div class="row g-5 mb-5">
        <div class="col-lg-5">
            <div class="card h-100">
                <div class="card-header">
                    <h5>Your Referral Code</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted">
                        Share this code or link with anyone — another school's admin, a fellow teacher, a parent,
                        anyone. If they sign up a new institute using it, you may earn a reward once it's approved.
                    </p>

                    <label class="form-label fw-bold">Your Code</label>
                    <div class="input-group mb-4">
                        <input type="text" class="form-control fw-bold" value="{{ $code }}" id="referralCode" readonly>
                        <button class="btn btn-outline-primary" type="button" onclick="copyField('referralCode')">Copy</button>
                    </div>

                    <label class="form-label fw-bold">Shareable Link</label>
                    <div class="input-group">
                        <input type="text" class="form-control" value="{{ $referralLink }}" id="referralLink" readonly>
                        <button class="btn btn-outline-primary" type="button" onclick="copyField('referralLink')">Copy</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card h-100">
                <div class="card-header">
                    <h5>Your Referral History</h5>
                </div>
                <div class="card-body">
                    @if ($history->isEmpty())
                        <p class="text-muted mb-0">You haven't referred anyone yet — share your code above to get started.</p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-row-bordered align-middle">
                                <thead>
                                    <tr class="text-muted">
                                        <th>Referred Institute</th>
                                        <th>Date</th>
                                        <th>Status</th>
                                        <th>Platform Reward</th>
                                        <th>Institute Reward</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($history as $referral)
                                        <tr>
                                            <td>{{ $referral->referredInstitute?->name ?? '—' }}</td>
                                            <td>{{ $referral->created_at->format('d M Y') }}</td>
                                            <td>
                                                @if ($referral->status === 'rewarded')
                                                    <span class="badge bg-success">Rewarded</span>
                                                @elseif ($referral->status === 'converted')
                                                    <span class="badge bg-info">Awaiting Approval</span>
                                                @elseif ($referral->status === 'rejected')
                                                    <span class="badge bg-danger">Rejected</span>
                                                @else
                                                    <span class="badge bg-secondary">Pending</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if ($referral->reward_type)
                                                    {{ $referral->reward_value }}
                                                    {{ match(true) {
                                                        $referral->reward_type === 'days' => 'days',
                                                        $referral->reward_type === 'cash' => 'USD',
                                                        // Once actually rewarded, reward_value has
                                                        // already been converted from the raw
                                                        // percentage into the calculated dollar
                                                        // amount - so it reads as USD from that
                                                        // point on, not as "X% of...".
                                                        $referral->reward_type === 'percentage' && $referral->status === 'rewarded' => 'USD',
                                                        $referral->reward_type === 'percentage' => '% of package (pending)',
                                                        default => 'points',
                                                    } }}
                                                @else
                                                    —
                                                @endif
                                            </td>
                                            <td>
                                                @if ($referral->institute_reward_type)
                                                    {{ $referral->institute_reward_value }}
                                                    {{ match($referral->institute_reward_type) {
                                                        'fee_discount' => 'Fee Discount',
                                                        'cash_withdrawal' => 'USD (Withdrawable)',
                                                        default => 'Points',
                                                    } }}
                                                    @if ($referral->institute_reward_status === 'pending')
                                                        <span class="badge bg-secondary ms-1">Pending</span>
                                                    @elseif ($referral->institute_reward_status === 'rejected')
                                                        <span class="badge bg-danger ms-1">Rejected</span>
                                                    @endif
                                                @else
                                                    —
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Withdrawal balance & requests - only meaningful once at least one
         cash_withdrawal-type institute reward has actually been approved,
         but always shown so the person knows this exists. --}}
    <div class="row g-5">
        <div class="col-lg-5">
            <div class="card h-100">
                <div class="card-header">
                    <h5>Withdrawable Balance</h5>
                </div>
                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                    @if (session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif

                    <p class="text-muted mb-2">Available to withdraw</p>
                    <h2 class="mb-1">${{ number_format($availableBalance, 2) }}</h2>
                    <p class="text-muted small mb-4">
                        @if ($withdrawalSettings['min_amount'] > 0)
                            Minimum ${{ number_format($withdrawalSettings['min_amount'], 2) }}
                        @endif
                        @if ($withdrawalSettings['max_amount'])
                            {{ $withdrawalSettings['min_amount'] > 0 ? ' · ' : '' }}Maximum ${{ number_format($withdrawalSettings['max_amount'], 2) }} per request
                        @endif
                    </p>

                    <form method="POST" action="{{ route('my-referrals.request-withdrawal') }}">
                        @csrf
                        <label class="form-label">Amount to withdraw</label>
                        <div class="input-group mb-3">
                            <span class="input-group-text">$</span>
                            <input type="number" step="0.01"
                                min="{{ $withdrawalSettings['min_amount'] > 0 ? $withdrawalSettings['min_amount'] : '0.01' }}"
                                max="{{ $withdrawalSettings['max_amount'] ? min($withdrawalSettings['max_amount'], $availableBalance) : $availableBalance }}"
                                name="amount" value="{{ old('amount') }}"
                                class="form-control" placeholder="0.00" {{ $availableBalance <= 0 ? 'disabled' : '' }} required>
                        </div>

                        <label class="form-label">How should we send it?</label>
                        <select name="payout_method" id="payoutMethod" class="form-select mb-3"
                            {{ $availableBalance <= 0 ? 'disabled' : '' }} required onchange="showPayoutFields()">
                            <option value="">— Select —</option>
                            @foreach (['bank_transfer' => 'Bank Transfer', 'paypal' => 'PayPal', 'mobile_money' => 'Mobile Money', 'other' => 'Other'] as $value => $label)
                                @if (in_array($value, $withdrawalSettings['enabled_methods']))
                                    <option value="{{ $value }}" {{ old('payout_method') === $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endif
                            @endforeach
                        </select>

                        {{-- One block per method - only the selected one is shown, but all
                             are rendered so their values submit correctly regardless. --}}
                        <div class="payout-fields" data-method="bank_transfer">
                            <div class="mb-2">
                                <input type="text" name="account_holder_name" value="{{ old('account_holder_name') }}"
                                    class="form-control" placeholder="Account holder name">
                            </div>
                            <div class="mb-2">
                                <input type="text" name="bank_name" value="{{ old('bank_name') }}"
                                    class="form-control" placeholder="Bank name">
                            </div>
                            <div class="mb-2">
                                <input type="text" name="account_number" value="{{ old('account_number') }}"
                                    class="form-control" placeholder="Account number">
                            </div>
                            <div class="mb-3">
                                <input type="text" name="routing_code" value="{{ old('routing_code') }}"
                                    class="form-control" placeholder="Routing / IFSC / SWIFT code">
                            </div>
                        </div>

                        <div class="payout-fields" data-method="paypal">
                            <div class="mb-3">
                                <input type="email" name="paypal_email" value="{{ old('paypal_email') }}"
                                    class="form-control" placeholder="PayPal email address">
                            </div>
                        </div>

                        <div class="payout-fields" data-method="mobile_money">
                            <div class="mb-2">
                                <input type="text" name="provider_name" value="{{ old('provider_name') }}"
                                    class="form-control" placeholder="Provider (e.g. bKash, M-Pesa)">
                            </div>
                            <div class="mb-3">
                                <input type="text" name="phone_number" value="{{ old('phone_number') }}"
                                    class="form-control" placeholder="Phone number">
                            </div>
                        </div>

                        <div class="payout-fields" data-method="other">
                            <div class="mb-3">
                                <textarea name="description" class="form-control" rows="2"
                                    placeholder="Describe how you would like to be paid">{{ old('description') }}</textarea>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100" {{ $availableBalance <= 0 ? 'disabled' : '' }}>
                            Request Withdrawal
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card h-100">
                <div class="card-header">
                    <h5>Withdrawal Requests</h5>
                </div>
                <div class="card-body">
                    @if ($withdrawalHistory->isEmpty())
                        <p class="text-muted mb-0">No withdrawal requests yet.</p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-row-bordered align-middle">
                                <thead>
                                    <tr class="text-muted">
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th>Requested</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($withdrawalHistory as $request)
                                        <tr>
                                            <td>${{ number_format($request->amount, 2) }}</td>
                                            <td>
                                                @if ($request->status === 'paid')
                                                    <span class="badge bg-success">Paid</span>
                                                @elseif ($request->status === 'approved')
                                                    <span class="badge bg-info">Approved</span>
                                                @elseif ($request->status === 'rejected')
                                                    <span class="badge bg-danger">Rejected</span>
                                                @else
                                                    <span class="badge bg-secondary">Pending</span>
                                                @endif
                                            </td>
                                            <td>{{ $request->created_at->format('d M Y') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <style>
        .payout-fields { display: none; }
        .payout-fields.active { display: block; }
    </style>

    <script>
        function copyField(id) {
            var field = document.getElementById(id);
            field.select();
            field.setSelectionRange(0, 99999);
            navigator.clipboard.writeText(field.value);
        }

        function showPayoutFields() {
            var method = document.getElementById('payoutMethod').value;
            document.querySelectorAll('.payout-fields').forEach(function (block) {
                block.classList.toggle('active', block.dataset.method === method);
            });
        }

        // Restores the correct block on page reload after a validation
        // error redirect (old('payout_method') is already selected server
        // side - this just makes the matching fields visible again).
        document.addEventListener('DOMContentLoaded', showPayoutFields);
    </script>
@endsection
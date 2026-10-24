@extends('layouts.backend')

@section('page_title_breadcrumb')
    My Subscription
@endsection

@section('content')

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="row g-5">
        {{-- Current plan --}}
        <div class="col-lg-5">
            <div class="card h-100">
                <div class="card-header">
                    <h5>Current Plan</h5>
                </div>
                <div class="card-body">
                    @if ($currentPlan)
                        <h3 class="mb-1">{{ $currentPlan->name }}</h3>
                        <p class="text-muted mb-4">
                            @if ($currentPlan->is_free)
                                Free Trial
                            @elseif ($currentPlan->is_custom)
                                Custom Pricing
                            @else
                                ${{ number_format($currentPlan->price, 2) }} / {{ $currentPlan->duration_days ?? 30 }} days
                            @endif
                        </p>

                        <table class="table table-borderless mb-0">
                            <tr>
                                <td class="text-muted">Student Limit</td>
                                <td class="text-end fw-bold">{{ number_format($currentPlan->student_limit) }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Branch Limit</td>
                                <td class="text-end fw-bold">{{ number_format($currentPlan->branch_limit) }}</td>
                            </tr>
                            @if ($subscription)
                                <tr>
                                    <td class="text-muted">Started</td>
                                    <td class="text-end fw-bold">{{ $subscription->start_date?->format('d M Y') }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Expires</td>
                                    <td class="text-end fw-bold">{{ $subscription->end_date?->format('d M Y') }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Status</td>
                                    <td class="text-end">
                                        @if ($subscription->isActive())
                                            <span class="badge bg-success">Active</span>
                                        @else
                                            <span class="badge bg-danger">Expired</span>
                                        @endif
                                    </td>
                                </tr>
                                @if ($daysRemaining !== null)
                                    <tr>
                                        <td class="text-muted">Days Remaining</td>
                                        <td class="text-end fw-bold {{ $daysRemaining <= 7 ? 'text-danger' : '' }}">
                                            {{ max(0, $daysRemaining) }}
                                        </td>
                                    </tr>
                                @endif
                            @endif
                        </table>
                    @else
                        <p class="text-muted">No subscription found for your institute.</p>
                    @endif
                </div>
            </div>
        </div>

        {{-- Change / renew --}}
        <div class="col-lg-7">
            <div class="card h-100">
                <div class="card-header">
                    <h5>Change Plan or Renew</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('my-subscription.checkout') }}" method="POST">
                        @csrf

                        <div class="row g-3 mb-4">
                            @foreach ($availablePlans as $plan)
                                <div class="col-md-4">
                                    <label class="w-100">
                                        <input type="radio" name="plan_id" value="{{ $plan->id }}" class="d-none plan-radio"
                                            @checked($currentPlan && $currentPlan->id === $plan->id)>
                                        <div class="border rounded p-3 plan-card {{ $currentPlan && $currentPlan->id === $plan->id ? 'border-primary bg-light-primary' : '' }}">
                                            @if ($plan->is_free)
                                                <span class="badge bg-success mb-2">Free Trial</span>
                                            @endif
                                            <div class="fw-bold">{{ $plan->name }}</div>
                                            <div class="fs-4 fw-bold">
                                                @if ($plan->is_custom)
                                                    Custom
                                                @elseif ($plan->is_free)
                                                    $0
                                                @else
                                                    ${{ number_format($plan->price, 0) }}
                                                @endif
                                            </div>
                                            <div class="text-muted small">Up to {{ number_format($plan->student_limit) }} students, {{ $plan->branch_limit }} {{ Str::plural('branch', $plan->branch_limit) }}</div>
                                            @if ($currentPlan && $currentPlan->id === $plan->id)
                                                <span class="badge bg-primary mt-2">Current Plan</span>
                                            @endif
                                        </div>
                                    </label>
                                </div>
                            @endforeach
                        </div>

                        <button type="submit" class="btn btn-primary">
                            Switch / Renew Plan
                        </button>
                        <p class="text-muted small mt-3 mb-0">
                            Free plans apply immediately. Paid plans take you to a secure checkout - your new limits
                            (students, branches, etc.) apply the moment payment is confirmed, no waiting for approval.
                            To renew your current plan for another cycle, just select it again above.
                        </p>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <style>
        .plan-card { cursor: pointer; transition: all .15s ease; }
        .plan-radio:checked + .plan-card { border-color: var(--bs-primary) !important; box-shadow: 0 0 0 2px var(--bs-primary); }
    </style>
@endsection
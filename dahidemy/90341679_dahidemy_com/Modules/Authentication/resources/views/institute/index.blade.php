@extends('layouts.backend')

@section('page_title_breadcrumb')
    Institute
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12">

            <div class="card">

                <div class="card-body">

                    <div class="table-responsive">

                        <table class="table table-bordered align-middle">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Institute Name</th>
                                    <th>Type</th>
                                    <th>Owner</th>
                                    <th>Phone</th>
                                    <th>Domain</th>
                                    <th>Package</th>
                                    <th>Subscription</th>
                                    <th>Status</th>
                                    <th>Created</th>
                                    <th width="180">Action</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse($institutes as $key => $institute)
                                    <tr>
                                        <td>
                                            {{ $institutes->firstItem() + $key }}
                                        </td>

                                        <td>
                                            {{ $institute->name }}
                                        </td>

                                        <td>
                                            {{ $institute->institute_type }}
                                        </td>

                                        <td>
                                            {{ $institute->owner->name ?? '-' }}
                                        </td>

                                        <td>
                                            {{ $institute->phone ?? '-' }}
                                        </td>

                                        <td>
                                            {{ $institute->domain ?? '-' }}
                                        </td>

                                        <td>
                                            {{ $institute->subscription->plan->name ?? 'No Package' }}
                                        </td>

                                        <td>
                                            @if ($institute->subscription)
                                                <span class="badge bg-success">
                                                    {{ ucfirst($institute->subscription->status) }}
                                                </span>
                                                <br>
                                                <small>
                                                    {{ $institute->subscription->start_date }}
                                                    -
                                                    {{ $institute->subscription->end_date }}
                                                </small>
                                            @else
                                                <span class="badge bg-secondary">
                                                    No Subscription
                                                </span>
                                            @endif
                                        </td>

                                        <td>
                                            @if ($institute->status == 1)
                                                <span class="badge bg-success">
                                                    Active
                                                </span>
                                            @else
                                                <span class="badge bg-danger">
                                                    Inactive
                                                </span>
                                            @endif
                                        </td>

                                        <td>
                                            {{ $institute->created_at->format('d M Y') }}
                                        </td>

                                        <td>
                                            <!-- Subscription Update Button -->
                                            {{-- <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal"
                                                data-bs-target="#subscriptionModal{{ $institute->id }}">
                                                Update Subscription
                                            </button> --}}

                                            <a href="{{ route('institutes.edit', $institute->id) }}"
                                                class="btn btn-sm btn-warning mt-2">
                                                Edit
                                            </a>

                                            <form action="{{ route('institutes.toggle-status', $institute->id) }}" method="POST"
                                                class="d-inline">
                                                @csrf
                                                @if ($institute->status == 1)
                                                    <button class="btn btn-sm btn-outline-secondary mt-2"
                                                        onclick="return confirm('Deactivate this institute? Its users will be unable to log in, and visitors to its website will see a temporarily-unavailable page instead of the normal site. Nothing is deleted — you can reactivate it at any time.')">
                                                        Deactivate
                                                    </button>
                                                @else
                                                    <button class="btn btn-sm btn-success mt-2"
                                                        onclick="return confirm('Reactivate this institute? Its users will be able to log in again, and its website will become visible to visitors again.')">
                                                        Activate
                                                    </button>
                                                @endif
                                            </form>

                                            <form action="{{ route('institutes.destroy', $institute->id) }}" method="POST"
                                                class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-sm btn-danger mt-2"
                                                    onclick="return confirm('PERMANENTLY delete \'' + '{{ $institute->name }}' + '\'? This cannot be undone: the institute record, its subdomain, and ALL of its data — students, staff, courses, exams, fees, everything — will be deleted forever. If you only want to disable access temporarily, use Deactivate instead.')">
                                                    Delete
                                                </button>
                                            </form>

                                            <!-- Subscription Modal -->
                                            <div class="modal fade" id="subscriptionModal{{ $institute->id }}"
                                                tabindex="-1">
                                                <div class="modal-dialog">
                                                    <form
                                                        action="{{ route('institutes.subscription.update', $institute->id) }}"
                                                        method="POST">
                                                        @csrf

                                                        <div class="modal-content">

                                                            <div class="modal-header">
                                                                <h5 class="modal-title">
                                                                    Upgrade Subscription
                                                                </h5>
                                                                <button type="button" class="btn-close"
                                                                    data-bs-dismiss="modal">
                                                                </button>
                                                            </div>

                                                            <div class="modal-body">
                                                                {{-- Select Package --}}
                                                                <div class="mb-3">
                                                                    <label>
                                                                        Select Package
                                                                    </label>

                                                                    <select name="plan_id" class="form-control" required>

                                                                        <option value="">
                                                                            Select Plan
                                                                        </option>

                                                                        @foreach ($plans as $plan)
                                                                            <option value="{{ $plan->id }}">
                                                                                {{ $plan->name }}
                                                                                -
                                                                                {{ $plan->price }}
                                                                                ({{ $plan->duration_days }} Days)
                                                                            </option>
                                                                        @endforeach

                                                                    </select>
                                                                </div>

                                                                {{-- Start Date --}}
                                                                <div class="mb-3">

                                                                    <label>
                                                                        Start Date
                                                                    </label>

                                                                    <input type="date" name="start_date"
                                                                        class="form-control" value="{{ date('Y-m-d') }}"
                                                                        required>
                                                                </div>

                                                                {{-- Payment Amount --}}
                                                                <div class="mb-3">

                                                                    <label>
                                                                        Amount Paid
                                                                    </label>

                                                                    <input type="number" name="amount_paid"
                                                                        class="form-control" step="0.01" value="0">

                                                                </div>

                                                                {{-- Payment Method --}}
                                                                <div class="mb-3">

                                                                    <label>
                                                                        Payment Method
                                                                    </label>

                                                                    <select name="payment_method" class="form-control">

                                                                        <option value="manual">
                                                                            Manual
                                                                        </option>
                                                                        <option value="bank">
                                                                            Bank
                                                                        </option>
                                                                        <option value="cash">
                                                                            Cash
                                                                        </option>

                                                                    </select>

                                                                </div>
                                                            </div>

                                                            <div class="modal-footer">

                                                                <button class="btn btn-success">
                                                                    Upgrade Subscription
                                                                </button>

                                                            </div>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="12" class="text-center">
                                            No institute found
                                        </td>
                                    </tr>
                                @endforelse

                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">
                        {{ $institutes->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
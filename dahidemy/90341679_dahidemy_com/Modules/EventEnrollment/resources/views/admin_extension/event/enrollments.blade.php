@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Enrollments') }} — {{ $event->name }}
@endsection

@section('create-button')
    <a href="{{ route('events.edit', $id) }}" class="btn btn-sm fw-bold btn-primary">
        {{ _lang('Back to Event') }}
    </a>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12">

            <div class="alert alert-light-info d-flex align-items-center mb-5">
                <i class="fa fa-info-circle fs-3 me-3"></i>
                <div>
                    {{ $event->is_paid ? _lang('Paid event — enrollments below are confirmed once payment succeeds.') : _lang('Free event — every enrollment below is confirmed immediately.') }}
                </div>
            </div>

            <div class="table-responsive">
                <table class="table align-middle table-row-dashed fs-6 gy-5">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>{{ _lang('Ticket') }}</th>
                            <th>{{ _lang('Name') }}</th>
                            <th>{{ _lang('Contact') }}</th>
                            @if ($event->is_paid)
                                <th>{{ _lang('Amount') }}</th>
                            @endif
                            <th>{{ _lang('Status') }}</th>
                            <th>{{ _lang('Registered') }}</th>
                            @if ($event->is_paid)
                                <th>{{ _lang('Action') }}</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($enrollments as $enrollment)
                            <tr>
                                <td>{{ $enrollment->id }}</td>
                                <td><span class="fw-bold">{{ $enrollment->ticket_code }}</span></td>
                                <td>{{ $enrollment->name }}</td>
                                <td>
                                    @if ($enrollment->email)<div>{{ $enrollment->email }}</div>@endif
                                    @if ($enrollment->phone)<div class="text-muted fs-8">{{ $enrollment->phone }}</div>@endif
                                </td>
                                @if ($event->is_paid)
                                    <td>{{ number_format((float) $enrollment->amount_paid, 2) }} {{ strtoupper((string) $enrollment->currency_code) }}</td>
                                @endif
                                <td>
                                    @php
                                        $badgeClass = match ($enrollment->statusLabel()) {
                                            'Confirmed' => 'badge-light-success',
                                            'Payment Pending' => 'badge-light-warning',
                                            'Payment Failed' => 'badge-light-danger',
                                            default => 'badge-light-secondary',
                                        };
                                    @endphp
                                    <span class="badge {{ $badgeClass }}">{{ _lang($enrollment->statusLabel()) }}</span>
                                </td>
                                <td>{{ $enrollment->created_at->format('d M Y, h:i A') }}</td>
                                @if ($event->is_paid)
                                    <td>
                                        @if ($enrollment->payment_status == \Modules\EventEnrollment\Models\EventEnrollment::STATUS_PAID)
                                            <form action="{{ route('events.enrollments.refund-note', [$event->id, $enrollment->id]) }}" method="POST" onsubmit="return confirm('{{ _lang('Mark this enrollment as refunded/cancelled? This does not process an actual refund through the payment gateway.') }}')">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn btn-sm btn-light-danger">{{ _lang('Mark Refunded') }}</button>
                                            </form>
                                        @endif
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-10 text-muted">{{ _lang('No enrollments yet.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                {{ $enrollments->links() }}
            </div>
        </div>
    </div>
@endsection

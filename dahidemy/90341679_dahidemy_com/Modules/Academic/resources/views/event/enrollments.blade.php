@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Enrollments') }} — {{ $event->name }}
@endsection

@section('create-button')
    <a href="{{ route('events.edit', $event->id) }}" class="btn btn-sm fw-bold btn-primary">
        {{ _lang('Back to Event') }}
    </a>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12">

            <div class="alert alert-light-info d-flex align-items-center mb-5">
                <i class="fa fa-info-circle fs-3 me-3"></i>
                <div>
                    {{ $event->is_paid ? _lang('This is a paid event — enrollments show as Payment Pending until the gateway confirms payment.') : _lang('This is a free event — enrollments are confirmed instantly.') }}
                </div>
            </div>

            <div class="table-responsive">
                <table class="table align-middle table-row-dashed fs-6 gy-5">
                    <thead>
                        <tr>
                            <th>{{ _lang('Ticket Code') }}</th>
                            <th>{{ _lang('Name') }}</th>
                            <th>{{ _lang('Contact') }}</th>
                            <th>{{ _lang('Amount') }}</th>
                            <th>{{ _lang('Status') }}</th>
                            <th>{{ _lang('Registered') }}</th>
                            <th>{{ _lang('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($enrollments as $enrollment)
                            <tr>
                                <td><span class="fw-bold">{{ $enrollment->ticket_code }}</span></td>
                                <td>{{ $enrollment->name }}</td>
                                <td>
                                    @if ($enrollment->email)<div>{{ $enrollment->email }}</div>@endif
                                    @if ($enrollment->phone)<div class="text-muted fs-8">{{ $enrollment->phone }}</div>@endif
                                </td>
                                <td>
                                    @if ($event->is_paid)
                                        {{ number_format((float) $enrollment->amount_paid, 2) }} {{ strtoupper((string) $enrollment->currency_code) }}
                                    @else
                                        &mdash;
                                    @endif
                                </td>
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
                                <td>
                                    @if ($enrollment->payment_status == \Modules\EventEnrollment\Models\EventEnrollment::STATUS_PAID)
                                        <form action="{{ route('events.enrollments.refund-note', [$event->id, $enrollment->id]) }}" method="POST"
                                            onsubmit="return confirm('{{ _lang('Mark this enrollment as refunded/cancelled? This only adds a note here — process the actual refund on your payment gateway separately.') }}')">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-sm btn-light-danger">
                                                {{ _lang('Mark Refunded') }}
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-10 text-muted">
                                    {{ _lang('No one has enrolled yet.') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                {{ $enrollments->links() }}
            </div>
        </div>
    </div>
@endsection

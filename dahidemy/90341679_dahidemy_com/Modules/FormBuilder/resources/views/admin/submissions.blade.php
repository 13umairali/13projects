@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Submissions') }} — {{ $form->name }}
@endsection

@section('create-button')
    <a href="{{ route('formbuilder.forms.edit', $form->id) }}" class="btn btn-sm fw-bold btn-primary">
        {{ _lang('Back to Form') }}
    </a>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="table-responsive">
                <table class="table align-middle table-row-dashed fs-6 gy-5">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>{{ _lang('Summary') }}</th>
                            <th>{{ _lang('Submitted') }}</th>
                            <th>{{ _lang('IP Address') }}</th>
                            <th>{{ _lang('Status') }}</th>
                            <th>{{ _lang('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($submissions as $submission)
                            <tr>
                                <td>{{ $submission->id }}</td>
                                <td>
                                    @php
                                        $preview = collect($submission->data)->filter(fn ($v) => is_string($v))->take(2)->implode(' &middot; ');
                                    @endphp
                                    {!! $preview ?: '<span class="text-muted">' . _lang('View details') . '</span>' !!}
                                </td>
                                <td>{{ $submission->created_at->format('d M Y, h:i A') }}</td>
                                <td>{{ $submission->ip_address }}</td>
                                <td>
                                    @php
                                        $badgeClass = match ($submission->statusLabel()) {
                                            'Approved' => 'badge-light-success',
                                            'Rejected' => 'badge-light-danger',
                                            default => 'badge-light-warning',
                                        };
                                    @endphp
                                    <span class="badge {{ $badgeClass }}">{{ _lang($submission->statusLabel()) }}</span>
                                </td>
                                <td>
                                    <a href="{{ route('formbuilder.forms.submissions.show', [$form->id, $submission->id]) }}" class="btn btn-sm btn-light-primary">
                                        <i class="fa fa-eye me-1"></i> {{ _lang('View') }}
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-10 text-muted">
                                    {{ _lang('No submissions yet.') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                {{ $submissions->links() }}
            </div>
        </div>
    </div>
@endsection

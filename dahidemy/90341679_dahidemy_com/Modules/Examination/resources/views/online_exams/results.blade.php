@extends('layouts.backend')
@section('page_title_breadcrumb')
    {{ _lang('Online Exam Results') }}
@endsection
@section('content')
    <div class="d-flex gap-2 mb-3">
        <a class="btn btn-success" target="_blank" href="{{ route('online-exams.results-print', $onlineExam->id) }}">{{ _lang('Print') }}</a>
        <a class="btn btn-primary" href="{{ route('online-exams.transfer', $onlineExam->id) }}">{{ _lang('Transfer to Mark Input') }}</a>
    </div>
    <div class="card">
        <div class="card-header"><h3 class="card-title">{{ $onlineExam->name }}</h3></div>
        <div class="card-body table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>{{ _lang('Student') }}</th>
                        <th>{{ _lang('Score') }}</th>
                        <th>{{ _lang('Converted') }}</th>
                        <th>{{ _lang('Status') }}</th>
                        <th>{{ _lang('Submitted') }}</th>
                        @if ($onlineExam->proctoring_enabled)
                            <th>{{ _lang('Integrity') }}</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @foreach ($attempts as $attempt)
                        <tr class="{{ $attempt->violation_count > 0 ? 'table-warning' : '' }}">
                            <td>{{ $attempt->student?->first_name }} {{ $attempt->student?->last_name }}</td>
                            <td>{{ $attempt->score }}</td>
                            <td>{{ $attempt->converted_score }}</td>
                            <td>{{ $attempt->is_passed ? _lang('Pass') : _lang('Fail') }} / {{ $attempt->status }}</td>
                            <td>{{ optional($attempt->submitted_at)->format('d M Y H:i') }}</td>
                            @if ($onlineExam->proctoring_enabled)
                                <td>
                                    @if ($attempt->violation_count > 0)
                                        <span class="badge bg-warning text-dark" data-bs-toggle="tooltip"
                                            title="{{ collect($attempt->integrity_violations)->pluck('type')->implode(', ') }}">
                                            <i class="fa fa-triangle-exclamation"></i> {{ $attempt->violation_count }} {{ _lang('flagged') }}
                                        </span>
                                    @else
                                        <span class="badge bg-light text-muted">{{ _lang('Clean') }}</span>
                                    @endif
                                </td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection

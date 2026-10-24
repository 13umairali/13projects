@extends('layouts.backend')
@section('page_title_breadcrumb')
    {{ _lang('Exam Result') }}
@endsection
@section('content')
    <div class="card mb-5">
        <div class="card-header">
            <h3 class="card-title">{{ _lang('Filter Results') }}</h3>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('exam-result-view') }}" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">{{ _lang('Class') }}</label>
                    <select name="class_id" id="class_id_select" class="form-select" required>
                        <option value="">{{ _lang('Select Class') }}</option>
                        {!! create_option('classes', 'id', 'class_name', $filters['class_id'] ?? '') !!}
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">{{ _lang('Section') }}</label>
                    <select name="section_id" id="section_id_select" class="form-select" data-selected="{{ $filters['section_id'] ?? '' }}">
                        <option value="">{{ _lang('All Sections') }}</option>
                        @foreach ($sections as $section)
                            <option value="{{ $section->id }}" @selected(($filters['section_id'] ?? '') == $section->id)>{{ $section->section_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">{{ _lang('Exam / Term') }}</label>
                    <select name="exam_id" class="form-select" required>
                        <option value="">{{ _lang('Select Exam') }}</option>
                        @foreach ($exams as $examItem)
                            <option value="{{ $examItem->id }}" @selected(($filters['exam_id'] ?? '') == $examItem->id)>{{ $examItem->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button class="btn btn-primary">{{ _lang('Generate') }}</button>
                </div>
            </form>
        </div>
    </div>

    @if (!empty($results['rows']))
        <div class="card">
            <div class="card-header d-flex justify-content-between">
                <h3 class="card-title">{{ $exam?->name }} — {{ $results['class_name'] }}</h3>
                <a class="btn btn-sm btn-light" href="{{ route('tabulation-sheet', request()->query()) }}">{{ _lang('Broad Sheet') }}</a>
                @if (request()->filled('class_id') && request()->filled('exam_id'))
                    <a class="btn btn-sm btn-light" href="{{ route('exam-result-pdf', request()->query()) }}">{{ _lang('Export PDF') }}</a>
                    <a class="btn btn-sm btn-light" href="{{ route('exam-result-excel', request()->query()) }}">{{ _lang('Export Excel') }}</a>
                @endif
            </div>
            <div class="card-body table-responsive">
                <table class="table table-row-dashed table-striped">
                    <thead>
                        <tr>
                            <th>{{ _lang('Roll') }}</th>
                            <th>{{ _lang('Student') }}</th>
                            <th>{{ _lang('Total') }}</th>
                            <th>{{ _lang('GPA') }}</th>
                            <th>{{ _lang('Grade') }}</th>
                            <th>{{ _lang('Status') }}</th>
                            <th>{{ _lang('Position') }}</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($results['rows'] as $row)
                            <tr>
                                <td>{{ $row['roll'] }}</td>
                                <td>{{ $row['student_name'] }}</td>
                                <td>{{ number_format((float) $row['total_marks'], 2) }}</td>
                                <td>{{ number_format((float) $row['gpa'], 2) }}</td>
                                <td>{{ $row['grade'] }}</td>
                                <td>{{ strtoupper($row['status']) }}</td>
                                <td>{{ $row['position'] ?? '-' }}</td>
                                <td class="text-end">
                                    <a class="btn btn-sm btn-primary" href="{{ route('exam-result-card', ['student_session_id' => $row['student_session']->id, 'exam_id' => request('exam_id')]) }}">{{ _lang('Result Card') }}</a>
                                    <a class="btn btn-sm btn-light" href="{{ route('student-mark-view', ['student_session_id' => $row['student_session']->id, 'exam_id' => request('exam_id')]) }}">{{ _lang('Marks') }}</a>
                                    <a class="btn btn-sm btn-light" href="{{ route('annual-result-card', ['student_session_id' => $row['student_session']->id]) }}">{{ _lang('Annual') }}</a>
                                    <a class="btn btn-sm btn-success" href="{{ route('exam-result-card-pdf', ['student_session_id' => $row['student_session']->id, 'exam_id' => request('exam_id')]) }}">{{ _lang('PDF') }}</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @elseif(request()->filled('class_id'))
        <div class="alert alert-info">{{ _lang('No results found for the selected filters.') }}</div>
    @endif
@endsection


@extends('layouts.backend')
@section('page_title_breadcrumb')
    {{ _lang('Online Exam Result') }}
@endsection
@section('content')
    <div class="card">
        <div class="card-header"><h3 class="card-title">{{ $attempt->onlineExam->name }}</h3></div>
        <div class="card-body">
            <p>{{ _lang('Score') }}: <strong>{{ $attempt->score }}</strong></p>
            <p>{{ _lang('Converted') }}: {{ $attempt->converted_score }}</p>
            <p>{{ _lang('Correct') }}: {{ $attempt->correct_count }} | {{ _lang('Wrong') }}: {{ $attempt->incorrect_count }} | {{ _lang('Skipped') }}: {{ $attempt->skipped_count }}</p>
            <p>{{ _lang('Result') }}: {{ $attempt->is_passed ? _lang('Pass') : _lang('Fail') }}</p>
            <p>{{ _lang('Status') }}: {{ $attempt->status }}</p>
        </div>
    </div>
@endsection

@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('My Results') }}
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12">
            @if ($publications->isEmpty())
                <div class="text-center py-15 text-muted">
                    <i class="fa fa-file-circle-question fs-2x mb-3 d-block"></i>
                    {{ _lang('No results have been published for your class yet.') }}
                </div>
            @else
                <div class="row g-5">
                    @foreach ($publications as $publication)
                        <div class="col-md-6">
                            <div class="card h-100">
                                <div class="card-body">
                                    <h4 class="mb-2">{{ $publication->title }}</h4>
                                    <p class="text-muted mb-3">
                                        {{ $publication->exam?->name }}
                                        &middot;
                                        {{ $publication->subject?->subject_name ?? _lang('All Subjects') }}
                                    </p>

                                    @if ($publication->description)
                                        <p class="mb-4">{{ $publication->description }}</p>
                                    @endif

                                    @if ($publication->type === 'online' && $publication->onlineExam)
                                        <a href="{{ route('student.online-exams.index') }}" class="btn btn-primary btn-sm">
                                            <i class="fa fa-arrow-right me-1"></i> {{ _lang('View My Online Exam Result') }}
                                        </a>
                                    @elseif ($publication->type === 'physical' && $publication->file)
                                        <a href="{{ asset('storage/result_publications/' . $publication->file) }}" target="_blank" class="btn btn-primary btn-sm">
                                            <i class="fa fa-download me-1"></i> {{ _lang('View / Download Result') }}
                                        </a>
                                    @endif

                                    <p class="text-muted fs-8 mt-4 mb-0">{{ _lang('Published') }} {{ optional($publication->published_at)->format('d M Y') }}</p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
@endsection

@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ $submission->exists ? _lang('Edit Submission') : _lang('Submit a Result') }}
@endsection

@section('create-button')
    <a href="{{ route('resultpublication.teacher.index') }}" class="btn btn-sm fw-bold btn-primary">
        {{ _lang('My Submissions') }}
    </a>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    @if ($submission->exists && $submission->status == \Modules\ResultPublication\Models\ResultPublication::STATUS_REJECTED && $submission->remarks)
                        <div class="alert alert-danger">
                            <strong>{{ _lang('This was rejected with the following note:') }}</strong> {{ $submission->remarks }}
                        </div>
                    @endif

                    <form method="POST"
                        action="{{ $submission->exists ? route('resultpublication.teacher.update', $submission->id) : route('resultpublication.teacher.store') }}"
                        enctype="multipart/form-data">
                        @csrf
                        @if ($submission->exists)
                            @method('PUT')
                        @endif

                        <div class="mb-5">
                            <label class="form-label required">{{ _lang('Title') }}</label>
                            <input type="text" name="title" class="form-control" required
                                value="{{ old('title', $submission->title) }}" placeholder="e.g. Mid-Term Mathematics Result">
                        </div>

                        <div class="row g-4 mb-5">
                            <div class="col-md-4">
                                <label class="form-label required">{{ _lang('Class') }}</label>
                                <select name="class_id" class="form-select" required>
                                    <option value="">{{ _lang('Select Class') }}</option>
                                    @foreach ($classes as $class)
                                        <option value="{{ $class->id }}" @selected(old('class_id', $submission->class_id) == $class->id)>{{ $class->class_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">{{ _lang('Section (optional)') }}</label>
                                <select name="section_id" class="form-select">
                                    <option value="">{{ _lang('Whole Class') }}</option>
                                    @foreach ($sections as $section)
                                        <option value="{{ $section->id }}" @selected(old('section_id', $submission->section_id) == $section->id)>{{ $section->section_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">{{ _lang('Subject (optional)') }}</label>
                                <select name="subject_id" class="form-select">
                                    <option value="">{{ _lang('All Subjects / Combined') }}</option>
                                    @foreach ($subjects as $subject)
                                        <option value="{{ $subject->id }}" @selected(old('subject_id', $submission->subject_id) == $subject->id)>{{ $subject->subject_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="mb-5">
                            <label class="form-label required">{{ _lang('Exam') }}</label>
                            <select name="exam_id" class="form-select" required>
                                <option value="">{{ _lang('Select Exam') }}</option>
                                @foreach ($exams as $exam)
                                    <option value="{{ $exam->id }}" @selected(old('exam_id', $submission->exam_id) == $exam->id)>{{ $exam->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-5">
                            <label class="form-label required">{{ _lang('Result Type') }}</label>
                            <select name="type" id="resultType" class="form-select" onchange="toggleResultFields()">
                                @foreach (\Modules\ResultPublication\Models\ResultPublication::TYPE_OPTIONS as $value => $label)
                                    <option value="{{ $value }}" @selected(old('type', $submission->type) == $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-5" id="onlineExamField" style="display:none">
                            <label class="form-label">{{ _lang('Which Online Exam?') }}</label>
                            <select name="online_exam_id" class="form-select">
                                <option value="">{{ _lang('Select Online Exam') }}</option>
                                @foreach ($onlineExams as $onlineExam)
                                    <option value="{{ $onlineExam->id }}" @selected(old('online_exam_id', $submission->online_exam_id) == $onlineExam->id)>{{ $onlineExam->name }}</option>
                                @endforeach
                            </select>
                            <div class="form-text">{{ _lang('Students will be shown a link straight to their own result for this exam.') }}</div>
                        </div>

                        <div class="mb-5" id="fileField" style="display:none">
                            <label class="form-label">{{ _lang('Upload Result (PDF or image)') }}</label>
                            @if ($submission->file)
                                <div class="mb-2">
                                    <a href="{{ asset('storage/result_publications/' . $submission->file) }}" target="_blank">{{ _lang('View current file') }}</a>
                                </div>
                            @endif
                            <input type="file" name="file" class="form-control" accept=".pdf,image/*">
                        </div>

                        <div class="mb-5">
                            <label class="form-label">{{ _lang('Description / Notes for students & parents (optional)') }}</label>
                            <textarea name="description" class="form-control" rows="3">{{ old('description', $submission->description) }}</textarea>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-paper-plane me-1"></i> {{ $submission->exists ? _lang('Resubmit for Approval') : _lang('Submit for Approval') }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        function toggleResultFields() {
            const type = document.getElementById('resultType').value;
            document.getElementById('onlineExamField').style.display = type === 'online' ? 'block' : 'none';
            document.getElementById('fileField').style.display = type === 'physical' ? 'block' : 'none';
        }
        document.addEventListener('DOMContentLoaded', toggleResultFields);
    </script>
@endsection

@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ $submission->exists ? _lang('Edit Result') : _lang('Create Result') }}
@endsection

@section('create-button')
    <a href="{{ route('resultpublication.admin.index') }}" class="btn btn-sm fw-bold btn-primary">
        {{ _lang('All Submissions') }}
    </a>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <form method="POST"
                        action="{{ $submission->exists ? route('resultpublication.admin.update', $submission->id) : route('resultpublication.admin.store') }}"
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

                        @if (! $submission->exists)
                            <div class="mb-5">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="publish_immediately" value="1" id="publishNow">
                                    <label class="form-check-label" for="publishNow">
                                        {{ _lang('Publish immediately (skips Pending — notifies students & parents right away)') }}
                                    </label>
                                </div>
                            </div>
                        @elseif (! $submission->isApproved())
                            <div class="mb-5">
                                <label class="form-label">{{ _lang('Status') }}</label>
                                <select name="status" class="form-select">
                                    <option value="{{ \Modules\ResultPublication\Models\ResultPublication::STATUS_PENDING }}" @selected($submission->status == \Modules\ResultPublication\Models\ResultPublication::STATUS_PENDING)>{{ _lang('Pending') }}</option>
                                    <option value="{{ \Modules\ResultPublication\Models\ResultPublication::STATUS_APPROVED }}" @selected($submission->status == \Modules\ResultPublication\Models\ResultPublication::STATUS_APPROVED)>{{ _lang('Approved (publishes & notifies)') }}</option>
                                    <option value="{{ \Modules\ResultPublication\Models\ResultPublication::STATUS_REJECTED }}" @selected($submission->status == \Modules\ResultPublication\Models\ResultPublication::STATUS_REJECTED)>{{ _lang('Rejected') }}</option>
                                </select>
                            </div>
                        @else
                            <input type="hidden" name="status" value="{{ \Modules\ResultPublication\Models\ResultPublication::STATUS_APPROVED }}">
                            <p class="text-muted fs-8 mb-5">{{ _lang('Already published — editing here updates the details students/parents see, without sending a duplicate notification.') }}</p>
                        @endif

                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-save me-1"></i> {{ $submission->exists ? _lang('Update Result') : _lang('Create Result') }}
                        </button>

                        @if ($submission->exists)
                            <a href="#" onclick="document.getElementById('deleteForm').submit(); return false;" class="btn btn-light-danger ms-2">
                                <i class="fa fa-trash me-1"></i> {{ _lang('Delete') }}
                            </a>
                        @endif
                    </form>

                    @if ($submission->exists)
                        <form id="deleteForm" action="{{ route('resultpublication.admin.destroy', $submission->id) }}" method="POST" class="d-none"
                            onsubmit="return confirm('{{ _lang('Delete this result publication? This cannot be undone.') }}')">
                            @csrf
                            @method('DELETE')
                        </form>
                    @endif
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

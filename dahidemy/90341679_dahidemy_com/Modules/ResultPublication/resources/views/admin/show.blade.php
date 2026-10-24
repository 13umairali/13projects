@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Review Submission') }}
@endsection

@section('create-button')
    <a href="{{ route('resultpublication.admin.index') }}" class="btn btn-sm fw-bold btn-primary">
        {{ _lang('All Submissions') }}
    </a>
    <a href="{{ route('resultpublication.admin.edit', $submission->id) }}" class="btn btn-sm fw-bold btn-warning ms-2">
        <i class="fa fa-pen me-1"></i> {{ _lang('Edit') }}
    </a>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h3 class="card-title">{{ $submission->title }}</h3>
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-3 text-muted">{{ _lang('Class / Section') }}</dt>
                        <dd class="col-sm-9">{{ $submission->class?->class_name }} {{ $submission->section?->section_name ?: '(' . _lang('Whole Class') . ')' }}</dd>

                        <dt class="col-sm-3 text-muted">{{ _lang('Subject') }}</dt>
                        <dd class="col-sm-9">{{ $submission->subject?->subject_name ?? _lang('All Subjects / Combined') }}</dd>

                        <dt class="col-sm-3 text-muted">{{ _lang('Exam') }}</dt>
                        <dd class="col-sm-9">{{ $submission->exam?->name }}</dd>

                        <dt class="col-sm-3 text-muted">{{ _lang('Type') }}</dt>
                        <dd class="col-sm-9">{{ \Modules\ResultPublication\Models\ResultPublication::TYPE_OPTIONS[$submission->type] ?? $submission->type }}</dd>

                        @if ($submission->type === 'online' && $submission->onlineExam)
                            <dt class="col-sm-3 text-muted">{{ _lang('Linked Online Exam') }}</dt>
                            <dd class="col-sm-9">
                                {{ $submission->onlineExam->name }}
                                <a href="{{ route('online-exams.results', $submission->onlineExam->id) }}" class="ms-2" target="_blank">{{ _lang('View Results') }}</a>
                            </dd>
                        @endif

                        @if ($submission->type === 'physical' && $submission->file)
                            <dt class="col-sm-3 text-muted">{{ _lang('Uploaded File') }}</dt>
                            <dd class="col-sm-9">
                                <a href="{{ asset('storage/result_publications/' . $submission->file) }}" target="_blank">{{ _lang('View File') }}</a>
                            </dd>
                        @endif

                        @if ($submission->description)
                            <dt class="col-sm-3 text-muted">{{ _lang('Description') }}</dt>
                            <dd class="col-sm-9">{{ $submission->description }}</dd>
                        @endif
                    </dl>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-body">
                    @php
                        $badgeClass = match ($submission->statusLabel()) {
                            'Approved' => 'badge-light-success',
                            'Rejected' => 'badge-light-danger',
                            default => 'badge-light-warning',
                        };
                    @endphp
                    <p class="mb-2"><strong>{{ _lang('Status') }}:</strong><br><span class="badge {{ $badgeClass }} fs-7">{{ _lang($submission->statusLabel()) }}</span></p>
                    <p class="mb-0"><strong>{{ _lang('Submitted') }}:</strong><br>{{ $submission->created_at->format('d M Y, h:i A') }}</p>
                    @if ($submission->remarks)
                        <hr>
                        <p class="mb-0"><strong>{{ _lang('Review Note') }}:</strong><br>{{ $submission->remarks }}</p>
                    @endif
                </div>
            </div>

            @if ($submission->isPending())
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">{{ _lang('Review') }}</h3>
                    </div>
                    <div class="card-body">
                        <p class="text-muted fs-8 mb-3">
                            {{ _lang('On approval, every student (and their parent) in this class/section will be notified by email, SMS, and/or WhatsApp automatically.') }}
                        </p>

                        <div class="mb-4">
                            <label class="form-label">{{ _lang('Note (optional, shown to the teacher — and to students/parents if approved)') }}</label>
                            <textarea id="reviewRemarks" class="form-control" rows="3"></textarea>
                        </div>

                        <div class="d-flex gap-2">
                            <form action="{{ route('resultpublication.admin.approve', $submission->id) }}" method="POST" onsubmit="return fillRemarks(this)" class="flex-fill">
                                @csrf
                                <input type="hidden" name="remarks">
                                <button type="submit" class="btn btn-success w-100" onclick="return confirm('{{ _lang('Approve and publish this result? Students and parents will be notified immediately.') }}')">
                                    <i class="fa fa-check me-1"></i> {{ _lang('Approve & Publish') }}
                                </button>
                            </form>
                            <form action="{{ route('resultpublication.admin.reject', $submission->id) }}" method="POST" onsubmit="return fillRemarks(this)" class="flex-fill">
                                @csrf
                                <input type="hidden" name="remarks">
                                <button type="submit" class="btn btn-danger w-100" onclick="return confirm('{{ _lang('Reject this submission?') }}')">
                                    <i class="fa fa-xmark me-1"></i> {{ _lang('Reject') }}
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        function fillRemarks(form) {
            form.querySelector('input[name="remarks"]').value = document.getElementById('reviewRemarks').value;
            return true;
        }
    </script>
@endsection

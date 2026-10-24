@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Submission') }} #{{ $submission->id }}
@endsection

@section('create-button')
    <a href="{{ route('formbuilder.forms.submissions', $form->id) }}" class="btn btn-sm fw-bold btn-primary">
        {{ _lang('Back to Submissions') }}
    </a>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{ $form->name }}</h3>
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        @foreach ($form->fields as $field)
                            @php $value = $submission->data[$field->field_key] ?? null; @endphp
                            <dt class="col-sm-4 text-muted">{{ $field->label }}</dt>
                            <dd class="col-sm-8">
                                @if ($field->type === 'file' && $value)
                                    <a href="{{ asset('storage/form_submissions/' . $value) }}" target="_blank">{{ _lang('View uploaded file') }}</a>
                                @elseif (is_array($value))
                                    {{ implode(', ', $value) }}
                                @else
                                    {{ $value ?: '—' }}
                                @endif
                            </dd>
                        @endforeach
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
                    <p class="mb-2"><strong>{{ _lang('Status') }}:</strong><br>
                        <span class="badge {{ $badgeClass }} fs-7">{{ _lang($submission->statusLabel()) }}</span>
                    </p>
                    <p class="mb-2"><strong>{{ _lang('Submitted At') }}:</strong><br>{{ $submission->created_at->format('d M Y, h:i A') }}</p>
                    <p class="mb-0"><strong>{{ _lang('IP Address') }}:</strong><br>{{ $submission->ip_address }}</p>

                    @if ($submission->remarks)
                        <hr>
                        <p class="mb-0"><strong>{{ _lang('Reviewer Note') }}:</strong><br>{{ $submission->remarks }}</p>
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
                            {{ _lang('The applicant will be notified by email, SMS, and/or WhatsApp (whichever contact details this form collected).') }}
                        </p>

                        <div class="mb-4">
                            <label class="form-label">{{ _lang('Note (optional, included in the notification)') }}</label>
                            <textarea id="reviewRemarks" class="form-control" rows="3" placeholder="{{ _lang('e.g. reason for rejection, or next steps') }}"></textarea>
                        </div>

                        <div class="d-flex gap-2">
                            <form action="{{ route('formbuilder.forms.submissions.approve', [$form->id, $submission->id]) }}" method="POST" onsubmit="return fillRemarks(this)" class="flex-fill">
                                @csrf
                                <input type="hidden" name="remarks">
                                <button type="submit" class="btn btn-success w-100" onclick="return confirm('{{ _lang('Approve this submission?') }}')">
                                    <i class="fa fa-check me-1"></i> {{ _lang('Approve') }}
                                </button>
                            </form>
                            <form action="{{ route('formbuilder.forms.submissions.reject', [$form->id, $submission->id]) }}" method="POST" onsubmit="return fillRemarks(this)" class="flex-fill">
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

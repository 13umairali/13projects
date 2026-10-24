@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ $popup->exists ? _lang('Edit Popup') : _lang('Add Popup') }}
@endsection

@section('create-button')
    <a href="{{ route('popupbroadcaster.index') }}" class="btn btn-sm fw-bold btn-primary">
        {{ _lang('All Popups') }}
    </a>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <form method="POST"
                        action="{{ $popup->exists ? route('popupbroadcaster.update', $popup->id) : route('popupbroadcaster.store') }}"
                        enctype="multipart/form-data">
                        @csrf
                        @if ($popup->exists)
                            @method('PUT')
                        @endif

                        <div class="mb-5">
                            <label class="form-label required">{{ _lang('Title') }}</label>
                            <input type="text" name="title" class="form-control" required
                                value="{{ old('title', $popup->title) }}" placeholder="e.g. Admissions Open for 2027">
                        </div>

                        <div class="mb-5">
                            <label class="form-label">{{ _lang('Message') }}</label>
                            <textarea name="message" class="form-control" rows="3">{{ old('message', $popup->message) }}</textarea>
                        </div>

                        <div class="mb-5">
                            <label class="form-label">{{ _lang('Image (optional)') }}</label>
                            @if ($popup->image)
                                <div class="mb-2">
                                    <img src="{{ asset('storage/popups/' . $popup->image) }}" width="120" class="rounded">
                                </div>
                            @endif
                            <input type="file" name="image" class="form-control" accept="image/*">
                        </div>

                        <div class="row g-4 mb-5">
                            <div class="col-md-6">
                                <label class="form-label">{{ _lang('Button Text (optional)') }}</label>
                                <input type="text" name="button_text" class="form-control"
                                    value="{{ old('button_text', $popup->button_text) }}" placeholder="e.g. Apply Now">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">{{ _lang('Button Link (optional)') }}</label>
                                <input type="text" name="button_link" class="form-control"
                                    value="{{ old('button_link', $popup->button_link) }}" placeholder="/admission-form-page or https://...">
                            </div>
                        </div>

                        <div class="row g-4 mb-5">
                            <div class="col-md-6">
                                <label class="form-label">{{ _lang('Audience') }}</label>
                                <select name="audience" class="form-select">
                                    @foreach (\Modules\PopupBroadcaster\Models\Popup::AUDIENCE_OPTIONS as $value => $label)
                                        <option value="{{ $value }}" @selected(old('audience', $popup->audience ?: 'everyone') == $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">{{ _lang('Show') }}</label>
                                <select name="display_frequency" class="form-select">
                                    @foreach (\Modules\PopupBroadcaster\Models\Popup::FREQUENCY_OPTIONS as $value => $label)
                                        <option value="{{ $value }}" @selected(old('display_frequency', $popup->display_frequency ?: 'once_per_session') == $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="row g-4 mb-5">
                            <div class="col-md-4">
                                <label class="form-label">{{ _lang('Start Showing (optional)') }}</label>
                                <input type="datetime-local" name="start_at" class="form-control"
                                    value="{{ old('start_at', optional($popup->start_at)->format('Y-m-d\TH:i')) }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">{{ _lang('Stop Showing (optional)') }}</label>
                                <input type="datetime-local" name="end_at" class="form-control"
                                    value="{{ old('end_at', optional($popup->end_at)->format('Y-m-d\TH:i')) }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">{{ _lang('Priority') }}</label>
                                <input type="number" name="priority" class="form-control" value="{{ old('priority', $popup->priority ?: 0) }}">
                                <div class="form-text">{{ _lang('If more than one popup is active, the highest priority shows first.') }}</div>
                            </div>
                        </div>

                        <div class="mb-5">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="status" value="1" id="statusToggle"
                                    {{ old('status', $popup->exists ? $popup->isActive() : true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="statusToggle">{{ _lang('Active') }}</label>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-save me-1"></i> {{ $popup->exists ? _lang('Update Popup') : _lang('Create Popup') }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

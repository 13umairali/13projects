@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Event') }}
@endsection
@section('create-button')
    <a class="btn btn-sm fw-bold btn-primary" href="{{ route('events.index') }}">
        {{ _lang('Events List') }}
    </a>
@endsection
@section('content')
    <div class="row">
        <div class="col-lg-12">
            <form method="post" action="{{ route('events.update', $id) }}" enctype="multipart/form-data">
                @csrf
                @method('PATCH')

                <div class="row">
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label class="control-label my-2">{{ _lang('Start Date') }}</label>
                            <input type="text" class="form-control datetimepicker" name="start_date"
                                value="{{ $event->start_date }}" required>
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="form-group">
                            <label class="control-label my-2">{{ _lang('End Date') }}</label>
                            <input type="text" class="form-control datetimepicker" name="end_date"
                                value="{{ $event->end_date }}" required>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label class="control-label my-2">{{ _lang('Name') }}</label>
                            <input type="text" class="form-control" name="name" value="{{ $event->name }}" required>
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="form-group">
                            <label class="control-label my-2">{{ _lang('Details') }}</label>
                            <textarea class="form-control summernote" name="details" required>{{ $event->details }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-12">
                        <div class="form-group">
                            <label class="control-label my-2">{{ _lang('Location') }}</label>
                            <input type="text" class="form-control" name="location" value="{{ $event->location }}"
                                required>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-12">
                        <div class="card mb-4">
                            <div class="card-header">
                                <h3 class="card-title">{{ _lang('Enrollment & Pricing') }}</h3>
                            </div>
                            <div class="card-body row g-4">
                                <div class="col-lg-3">
                                    <div class="form-check form-switch mt-2">
                                        <input class="form-check-input" type="checkbox" name="is_paid" value="1" id="isPaidToggle"
                                            {{ old('is_paid', $event->is_paid) ? 'checked' : '' }} onchange="document.getElementById('priceFields').style.display = this.checked ? 'flex' : 'none';">
                                        <label class="form-check-label" for="isPaidToggle">{{ _lang('This is a paid event') }}</label>
                                    </div>
                                </div>

                                <div class="col-lg-3">
                                    <div class="form-check form-switch mt-2">
                                        <input class="form-check-input" type="checkbox" name="enrollment_enabled" value="1" id="enrollmentEnabled"
                                            {{ old('enrollment_enabled', $event->enrollment_enabled) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="enrollmentEnabled">{{ _lang('Allow online enrollment') }}</label>
                                    </div>
                                </div>

                                <div class="col-lg-3">
                                    <div class="form-check form-switch mt-2">
                                        <input class="form-check-input" type="checkbox" name="is_public" value="1" id="isPublicToggle"
                                            {{ old('is_public', $event->is_public) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="isPublicToggle">{{ _lang('Display on Website') }}</label>
                                    </div>
                                </div>

                                <div class="col-lg-3">
                                    <label class="control-label mb-1">{{ _lang('Max Seats (optional)') }}</label>
                                    <input type="number" min="1" class="form-control" name="max_seats" value="{{ old('max_seats', $event->max_seats) }}" placeholder="Unlimited">
                                </div>

                                <div class="col-lg-3">
                                    <label class="control-label mb-1">{{ _lang('Registration Deadline (optional)') }}</label>
                                    <input type="date" class="form-control" name="registration_deadline" value="{{ old('registration_deadline', $event->registration_deadline) }}">
                                </div>

                                <div id="priceFields" class="row g-4" style="display: {{ old('is_paid', $event->is_paid) ? 'flex' : 'none' }};">
                                    <div class="col-lg-3">
                                        <label class="control-label mb-1">{{ _lang('Price') }}</label>
                                        <input type="number" step="0.01" min="0" class="form-control" name="price" value="{{ old('price', $event->price) }}" placeholder="0.00">
                                    </div>
                                    <div class="col-lg-3">
                                        <label class="control-label mb-1">{{ _lang('Currency Code') }}</label>
                                        <input type="text" class="form-control" name="currency_code" value="{{ old('currency_code', $event->currency_code ?: 'usd') }}" placeholder="usd">
                                    </div>
                                </div>

                                <div class="col-lg-12">
                                    <a href="{{ route('events.enrollments', $event->id) }}" class="btn btn-sm btn-light-primary">
                                        <i class="fa fa-users me-1"></i> {{ _lang('View Enrollments') }}
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-12">
                        <div class="form-group">

                            @if ($event->image)
                                <img src="{{ $event->image ? asset('storage/events/' . $event->image) : asset('assets/media/svg/files/blank-image.svg') }}"
                                    class="me-3 my-5" alt="" width="50px">
                            @endif

                            <label class="control-label my-2">{{ _lang('Image') }}</label>
                            <input type="file" class="form-control" name="image">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-12">
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary mt-5">{{ _lang('Update') }}</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

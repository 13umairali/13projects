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
            <form method="post" action="{{ route('events.store') }}" enctype="multipart/form-data" data-disable-on-submit>
                @csrf

                <div class="row">
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label class="control-label my-2">{{ _lang('Start Date') }}</label>
                            <input type="date" class="form-control" name="start_date" value="{{ old('start_date', date('Y-m-d')) }}"
                                required>
                            @error('start_date')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="form-group">
                            <label class="control-label my-2">{{ _lang('End Date') }}</label>
                            <input type="date" class="form-control" name="end_date" value="{{ old('end_date', date('Y-m-d')) }}" required>
                            @error('end_date')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-12">
                        <div class="form-group">
                            <label class="control-label my-2">{{ _lang('Title') }}</label>
                            <input type="text" class="form-control" name="name" value="{{ old('name') }}"
                                placeholder="Title" required>
                            @error('name')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-lg-12">
                        <div class="form-group">
                            <label class="control-label my-2">{{ _lang('Details') }}</label>
                            <textarea class="form-control summernote" name="details" placeholder="Details" required>{{ old('details') }}</textarea>
                            @error('details')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-12">
                        <div class="form-group">
                            <label class="control-label my-2">{{ _lang('Location') }}</label>
                            <input type="text" class="form-control" name="location" placeholder="Location"
                                value="{{ old('location') }}" required>
                            @error('location')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
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
                                            {{ old('is_paid') ? 'checked' : '' }} onchange="document.getElementById('priceFields').style.display = this.checked ? 'flex' : 'none';">
                                        <label class="form-check-label" for="isPaidToggle">{{ _lang('This is a paid event') }}</label>
                                    </div>
                                </div>

                                <div class="col-lg-3">
                                    <div class="form-check form-switch mt-2">
                                        <input class="form-check-input" type="checkbox" name="enrollment_enabled" value="1" id="enrollmentEnabled"
                                            {{ old('enrollment_enabled', true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="enrollmentEnabled">{{ _lang('Allow online enrollment') }}</label>
                                    </div>
                                </div>

                                <div class="col-lg-3">
                                    <div class="form-check form-switch mt-2">
                                        <input class="form-check-input" type="checkbox" name="is_public" value="1" id="isPublicToggle"
                                            {{ old('is_public', true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="isPublicToggle">{{ _lang('Display on Website') }}</label>
                                    </div>
                                </div>

                                <div class="col-lg-3">
                                    <label class="control-label mb-1">{{ _lang('Max Seats (optional)') }}</label>
                                    <input type="number" min="1" class="form-control" name="max_seats" value="{{ old('max_seats') }}" placeholder="Unlimited">
                                </div>

                                <div class="col-lg-3">
                                    <label class="control-label mb-1">{{ _lang('Registration Deadline (optional)') }}</label>
                                    <input type="date" class="form-control" name="registration_deadline" value="{{ old('registration_deadline') }}">
                                </div>

                                <div id="priceFields" class="row g-4" style="display: {{ old('is_paid') ? 'flex' : 'none' }};">
                                    <div class="col-lg-3">
                                        <label class="control-label mb-1">{{ _lang('Price') }}</label>
                                        <input type="number" step="0.01" min="0" class="form-control" name="price" value="{{ old('price') }}" placeholder="0.00">
                                        @error('price')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-lg-3">
                                        <label class="control-label mb-1">{{ _lang('Currency Code') }}</label>
                                        <input type="text" class="form-control" name="currency_code" value="{{ old('currency_code', 'usd') }}" placeholder="usd">
                                        <div class="form-text">{{ _lang('Must match a currency your configured payment gateway supports.') }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-12">
                        <div class="form-group">
                            <label class="control-label my-2">{{ _lang('Image') }}</label>
                            <input type="file" class="form-control" name="image">
                            @error('image')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-12">
                        <div class="form-group">
                            <button type="reset" class="btn btn-danger mt-4">{{ _lang('Reset') }}</button>
                            <button type="submit" class="btn btn-primary mt-4">{{ _lang('Save') }}</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

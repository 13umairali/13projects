@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ $course->exists ? _lang('Edit Course') : _lang('Create Course') }}
@endsection

@section('create-button')
    <a href="{{ route('elearning.admin.courses.index') }}" class="btn btn-sm fw-bold btn-primary">
        {{ _lang('All Courses') }}
    </a>
    @if ($course->exists)
        <a href="{{ route('elearning.admin.courses.builder', $course->id) }}" class="btn btn-sm btn-light-success ms-2">
            {{ _lang('Manage Content') }}
        </a>
    @endif
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-9">
            <div class="card">
                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger mb-6">
                            <strong>{{ _lang('Please fix the following before saving:') }}</strong>
                            <ul class="mb-0 mt-2">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST"
                        action="{{ $course->exists ? route('elearning.admin.courses.update', $course->id) : route('elearning.admin.courses.store') }}"
                        enctype="multipart/form-data">
                        @csrf
                        @if ($course->exists)
                            @method('PUT')
                        @endif

                        <div class="mb-5">
                            <label class="form-label required">{{ _lang('Title') }}</label>
                            <input type="text" name="title" class="form-control" required value="{{ old('title', $course->title) }}">
                        </div>

                        <div class="row g-4 mb-5">
                            <div class="col-md-6">
                                <label class="form-label required">{{ _lang('Category') }}</label>
                                <select name="course_category_id" class="form-select" required>
                                    <option value="" disabled @selected(!old('course_category_id', $course->course_category_id))>{{ _lang('Select a category') }}</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}" @selected(old('course_category_id', $course->course_category_id) == $category->id)>{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label required">{{ _lang('Type') }}</label>
                                <select name="type" class="form-select" required>
                                    <option value="single" @selected(old('type', $course->type) === 'single')>{{ _lang('Single') }}</option>
                                    <option value="bundle" @selected(old('type', $course->type) === 'bundle')>{{ _lang('Bundle') }}</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label required">{{ _lang('Status') }}</label>
                                <select name="status" class="form-select" required>
                                    <option value="draft" @selected(old('status', $course->status) === 'draft')>{{ _lang('Draft') }}</option>
                                    <option value="published" @selected(old('status', $course->status) === 'published')>{{ _lang('Published') }}</option>
                                    <option value="schedule" @selected(old('status', $course->status) === 'schedule')>{{ _lang('Scheduled') }}</option>
                                    <option value="private" @selected(old('status', $course->status) === 'private')>{{ _lang('Private') }}</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-5">
                            <label class="form-label">{{ _lang('Course Image') }}</label>
                            @if ($course->image)
                                <div class="mb-2"><img src="{{ asset('storage/courses/' . $course->image) }}" width="120" class="rounded"></div>
                            @endif
                            <input type="file" name="image" class="form-control" accept="image/*">
                        </div>

                        <div class="mb-5">
                            <label class="form-label">{{ _lang('Description') }}</label>
                            <textarea name="description" class="form-control" rows="5">{{ old('description', $course->description) }}</textarea>
                        </div>

                        <hr class="my-6">
                        <h5 class="mb-4">{{ _lang('Pricing') }}</h5>

                        <div class="mb-5">
                            <label class="form-label required">{{ _lang('Payment Type') }}</label>
                            <select name="payment_type" id="paymentType" class="form-select" required onchange="togglePriceFields()">
                                <option value="free" @selected(old('payment_type', $course->payment_type) === 'free')>{{ _lang('Free') }}</option>
                                <option value="one_time" @selected(old('payment_type', $course->payment_type) === 'one_time')>{{ _lang('One-Time Payment') }}</option>
                                <option value="recurring_payment" @selected(old('payment_type', $course->payment_type) === 'recurring_payment')>{{ _lang('Recurring Subscription') }}</option>
                            </select>
                            <div class="form-text">
                                {{ _lang('Free and One-Time courses can be purchased directly on your website. Recurring Subscription courses show on the website but direct visitors to contact you — the public checkout does not support recurring billing yet.') }}
                            </div>
                        </div>

                        <div class="row g-4 mb-5" id="priceFields" style="display:none">
                            <div class="col-md-4">
                                <label class="form-label">{{ _lang('Regular Price') }}</label>
                                <input type="number" step="0.01" min="0" name="regular_price" class="form-control" value="{{ old('regular_price', $course->regular_price) }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">{{ _lang('Offer Price (optional)') }}</label>
                                <input type="number" step="0.01" min="0" name="offer_price" class="form-control" value="{{ old('offer_price', $course->offer_price) }}">
                            </div>
                            <div class="col-md-4" id="recurringFields" style="display:none">
                                <label class="form-label">{{ _lang('Billing Cycle') }}</label>
                                <input type="text" name="payment_duration" class="form-control" value="{{ old('payment_duration', $course->payment_duration) }}" placeholder="{{ _lang('e.g. monthly') }}">
                            </div>
                        </div>

                        <hr class="my-6">
                        <h5 class="mb-4">{{ _lang('Intro Video (optional)') }}</h5>

                        <div class="row g-4 mb-5">
                            <div class="col-md-4">
                                <label class="form-label">{{ _lang('Video Type') }}</label>
                                <select name="video_type" class="form-select">
                                    <option value="none" @selected(old('video_type', $course->video_type ?: 'none') === 'none')>{{ _lang('None') }}</option>
                                    <option value="youtube" @selected(old('video_type', $course->video_type) === 'youtube')>{{ _lang('YouTube') }}</option>
                                    <option value="vimeo" @selected(old('video_type', $course->video_type) === 'vimeo')>{{ _lang('Vimeo') }}</option>
                                    <option value="embed" @selected(old('video_type', $course->video_type) === 'embed')>{{ _lang('Embed Code URL') }}</option>
                                </select>
                            </div>
                            <div class="col-md-8">
                                <label class="form-label">{{ _lang('Video URL') }}</label>
                                <input type="text" name="video_url" class="form-control" value="{{ old('video_url', $course->video_url) }}" placeholder="https://...">
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-save me-1"></i> {{ $course->exists ? _lang('Update Course') : _lang('Create & Continue') }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        function togglePriceFields() {
            const type = document.getElementById('paymentType').value;
            document.getElementById('priceFields').style.display = type === 'free' ? 'none' : 'flex';
            document.getElementById('recurringFields').style.display = type === 'recurring_payment' ? 'block' : 'none';
        }
        document.addEventListener('DOMContentLoaded', togglePriceFields);
    </script>
@endsection

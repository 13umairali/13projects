@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang("Director's Message") }}
@endsection

@section('create-button')
    <a href="{{ route('homepagebuilder.admin.sections.index') }}" class="btn btn-sm fw-bold btn-primary">
        {{ _lang('Homepage Sections') }}
    </a>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-8">
            <div class="alert alert-light-info d-flex align-items-center mb-5">
                <i class="fa fa-info-circle fs-3 me-3"></i>
                <div>
                    {{ _lang('This appears as a homepage section once it has a message — leave it empty to keep it hidden. You can also turn the whole section on/off from Homepage Sections regardless of content.') }}
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger mb-6">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('homepagebuilder.admin.director-message.update') }}" enctype="multipart/form-data">
                        @csrf

                        <div class="row g-4 mb-5">
                            <div class="col-md-6">
                                <label class="form-label">{{ _lang('Director / Principal Name') }}</label>
                                <input type="text" name="director_name" class="form-control" value="{{ old('director_name', $message->director_name) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">{{ _lang('Designation') }}</label>
                                <input type="text" name="designation" class="form-control" value="{{ old('designation', $message->designation) }}" placeholder="{{ _lang('e.g. Principal, Chairman') }}">
                            </div>
                        </div>

                        <div class="mb-5">
                            <label class="form-label">{{ _lang('Photo') }}</label>
                            @if ($message->photo)
                                <div class="mb-2">
                                    <img src="{{ asset('storage/director_messages/' . $message->photo) }}" width="120" class="rounded">
                                </div>
                            @endif
                            <input type="file" name="photo" class="form-control" accept="image/*">
                        </div>

                        <div class="mb-5">
                            <label class="form-label">{{ _lang('Message') }}</label>
                            <textarea name="message" class="form-control" rows="8">{{ old('message', $message->message) }}</textarea>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-save me-1"></i> {{ _lang('Save') }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

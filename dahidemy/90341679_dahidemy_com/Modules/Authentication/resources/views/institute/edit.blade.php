@extends('layouts.backend')
@section('page_title_breadcrumb')
    {{ ucfirst('institute') }}
@endsection

@section('create-button')
    <a href="{{ route('institutes.index') }}" class="btn btn-sm fw-bold btn-primary">
        {{ ucfirst('institute') }} List
    </a>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <form action="{{ route('institutes.update', $institute->id) }}" enctype="multipart/form-data" method="post">
                @csrf
                @method('PATCH')

                <div class="row">
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label class="control-label my-2">{{ _lang('Name') }} <span class='text-danger'>*</span></label>
                            <input type="text" class="form-control" name="name" value="{{ $institute->name }}"
                                placeholder="Name" required>

                            @error('name')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="form-group">
                            <label class="control-label my-2">{{ _lang('Email') }}
                                <span class='text-danger'>*</span>
                            </label>
                            <input type="email" class="form-control" name="email" value="{{ $institute->email }}"
                                placeholder="Email" required>
                            @error('name')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label class="control-label my-2">{{ _lang('Address') }}
                            </label>
                            <input type="text" class="form-control" name="address" value="{{ $institute->address }}"
                                placeholder="Address">
                            @error('name')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="form-group">
                            <label class="control-label my-2">{{ _lang('Phone') }}
                                <span class='text-danger'>*</span>
                            </label>
                            {{-- pattern="[0-9]{10,15}" --}}
                            <input type="text" class="form-control" name="phone" value="{{ $institute->phone }}"
                                placeholder="Phone Number" required>
                            @error('name')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">

                    <div class="col-lg-6">
                        <div class="form-group">
                            <label class="control-label my-2">{{ _lang('Domain') }}
                                <span class='text-danger'>*</span>
                            </label>
                            <input type="text" class="form-control" name="domain" value="{{ $institute->domain }}"
                                placeholder="Domain Number" required>
                            @error('domain')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>


        </div>

        <div class="form-group">
            <div class="col-sm-offset-4 col-sm-9">
                <button type="submit" class="btn btn-primary mt-5">{{ _lang('Update') }}</button>
            </div>
        </div>
        </form>
    </div>
    </div>
@endsection

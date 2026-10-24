@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ $category->exists ? _lang('Edit Category') : _lang('Add Category') }}
@endsection

@section('create-button')
    <a href="{{ route('blog.admin.categories.index') }}" class="btn btn-sm fw-bold btn-primary">
        {{ _lang('All Categories') }}
    </a>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-8">
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

                    <form method="POST" action="{{ $category->exists ? route('blog.admin.categories.update', $category->id) : route('blog.admin.categories.store') }}">
                        @csrf
                        @if ($category->exists)
                            @method('PUT')
                        @endif

                        <div class="mb-5">
                            <label class="form-label required">{{ _lang('Name') }}</label>
                            <input type="text" name="name" class="form-control" required value="{{ old('name', $category->name) }}">
                        </div>

                        <div class="mb-5">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="status" value="1" id="statusToggle"
                                    {{ old('status', $category->exists ? $category->isActive() : true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="statusToggle">{{ _lang('Active') }}</label>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-save me-1"></i> {{ $category->exists ? _lang('Update') : _lang('Create') }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

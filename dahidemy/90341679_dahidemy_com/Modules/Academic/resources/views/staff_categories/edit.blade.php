@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Edit Staff Category') }}
@endsection
@section('content')
    <div class="row">
        <div class="col-lg-6">
            <div class="table-responsive border p-5">
                <form action="{{ route('staff-categories.update', $staffCategory->id) }}" method="post">
                    @csrf
                    @method('PUT')

                    <div class="form-group">
                        <div class="col-sm-12">
                            <label class="control-label my-2">{{ _lang('Category Name') }}</label>
                            <input type="text" class="form-control" name="name" required
                                value="{{ old('name', $staffCategory->name) }}">
                            @error('name')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="col-sm-12">
                            <label class="control-label my-2">{{ _lang('Sort Order') }}</label>
                            <input type="number" class="form-control" name="sort_order" min="0"
                                value="{{ old('sort_order', $staffCategory->sort_order) }}">
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="col-sm-5">
                            <button type="submit" class="btn btn-primary mt-5">{{ _lang('Update Category') }}</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

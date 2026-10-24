@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Staff Categories') }}
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <span class="card-title">{{ _lang('Add Category') }}</span>
                </div>
                <div class="card-body">
                    <form action="{{ route('staff-categories.store') }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <label class="control-label my-2">{{ _lang('Name') }}</label>
                            <input type="text" class="form-control" name="name" placeholder="e.g. Administration"
                                value="{{ old('name') }}" required>
                            @error('name')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label class="control-label my-2">{{ _lang('Sort Order') }}</label>
                            <input type="number" class="form-control" name="sort_order" min="0"
                                value="{{ old('sort_order', 100) }}">
                            <div class="form-text">{{ _lang('Lower numbers appear first.') }}</div>
                        </div>
                        <button type="submit" class="btn btn-primary mt-3">{{ _lang('Save') }}</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <span class="card-title">{{ _lang('Categories') }}</span>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>{{ _lang('Name') }}</th>
                                    <th>{{ _lang('Sort Order') }}</th>
                                    <th>{{ _lang('Staff Count') }}</th>
                                    <th>{{ _lang('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($staffCategories as $category)
                                    <tr>
                                        <td>{{ $category->name }}</td>
                                        <td>{{ $category->sort_order }}</td>
                                        <td>{{ $category->staffs_count }}</td>
                                        <td>
                                            <a href="{{ route('staff-categories.edit', $category->id) }}"
                                                class="btn btn-sm btn-primary">
                                                {{ _lang('Edit') }}
                                            </a>
                                            <form action="{{ route('staff-categories.destroy', $category->id) }}"
                                                method="POST" class="d-inline"
                                                onsubmit="return confirm('{{ _lang('Delete this category?') }}');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger">
                                                    {{ _lang('Delete') }}
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4">{{ _lang('No categories yet.') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{ $staffCategories->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection

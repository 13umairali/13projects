@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Course Categories') }}
@endsection

@section('create-button')
    <a href="{{ route('elearning.admin.categories.create') }}" class="btn btn-sm fw-bold btn-primary">
        {{ _lang('Add Category') }}
    </a>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="table-responsive">
                <table class="table align-middle table-row-dashed fs-6 gy-5">
                    <thead>
                        <tr>
                            <th>{{ _lang('Name') }}</th>
                            <th>{{ _lang('Parent') }}</th>
                            <th>{{ _lang('Courses') }}</th>
                            <th>{{ _lang('Homepage') }}</th>
                            <th>{{ _lang('Status') }}</th>
                            <th>{{ _lang('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($categories as $category)
                            <tr>
                                <td><strong>{{ $category->name }}</strong></td>
                                <td>{{ $category->parentCategory?->name ?? '—' }}</td>
                                <td>{{ $category->courses()->count() }}</td>
                                <td>
                                    <span class="badge {{ $category->enable_homepage ? 'badge-light-success' : 'badge-light-secondary' }}">
                                        {{ $category->enable_homepage ? _lang('Shown') : _lang('Hidden') }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge {{ $category->status ? 'badge-light-success' : 'badge-light-secondary' }}">
                                        {{ $category->status ? _lang('Active') : _lang('Inactive') }}
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ route('elearning.admin.categories.edit', $category->id) }}" class="btn btn-sm btn-icon btn-light-primary"><i class="fa fa-pen"></i></a>
                                    <form action="{{ route('elearning.admin.categories.destroy', $category->id) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ _lang('Delete this category?') }}')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-icon btn-light-danger"><i class="fa fa-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-10 text-muted">{{ _lang('No categories yet.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

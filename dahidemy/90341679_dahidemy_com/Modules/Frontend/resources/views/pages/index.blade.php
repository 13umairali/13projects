@extends('layouts.backend')

@section('create-button')
    <a href="{{ route('pages.create') }}" class="btn btn-sm fw-bold btn-primary">
        {{ _lang('Add Page') }}
    </a>
@endsection

@section('content')
    <div class="card">
        <div class="card-body">
            <table class="table table-bordered">
                <tr>
                    <th>Title</th>
                    <th>Slug</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>

                @foreach ($pages as $page)
                    <tr>
                        <td>{{ $page->title }}</td>
                        <td>{{ $page->slug }}</td>
                        <td>
                            {{-- Was $page->page_status, a column that has never
                                 existed -- always blank. See the Status fix in
                                 this same changelog. --}}
                            @if ($page->status)
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-secondary">Inactive</span>
                            @endif
                        </td>
                        <td>
                            {{-- There was previously no way to see where a --}}
                            {{-- created page actually lives -- only Edit and --}}
                            {{-- Delete. --}}
                            <a href="{{ route('website.page', $page->slug) }}" target="_blank"
                                class="btn btn-info btn-sm">View</a>

                            <form action="{{ route('pages.destroy', $page->id) }}" method="POST">
                                <a href="{{ route('pages.edit', $page->id) }}" class="btn btn-warning btn-sm">Edit</a>

                                @csrf
                                @method('DELETE')

                                <button class="btn btn-danger btn-sm">Delete</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </table>
        </div>
    </div>
@endsection

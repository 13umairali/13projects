@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Policies') }}
@endsection

@section('create-button')
    <a href="{{ route('policies.create') }}" class="btn btn-sm fw-bold btn-primary">
        {{ _lang('Add New Policy') }}
    </a>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12">

            <div class="table-responsive">
                <table class="table align-middle table-row-dashed fs-6 gy-5 data-table">

                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Description</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($policies as $policy)
                            <tr>
                                <td>{{ $policy->id }}</td>

                                <td>
                                    @if ($policy->type == 1)
                                        Privacy Policy
                                    @elseif($policy->type == 2)
                                        Cookie Policy
                                    @else
                                        Terms & Conditions
                                    @endif
                                </td>

                                <td>
                                    @if ($policy->status == 1)
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-danger">Inactive</span>
                                    @endif
                                </td>

                                <td>
                                    {{ Str::limit(strip_tags($policy->description), 60) }}
                                </td>

                                <td>
                                    <form action="{{ route('policies.destroy', $policy->id) }}" method="POST">

                                        <a href="{{ route('policies.edit', $policy->id) }}" class="btn btn-warning btn-sm">
                                            Edit
                                        </a>

                                        @csrf
                                        @method('DELETE')

                                        <button class="btn btn-danger btn-sm">
                                            Delete
                                        </button>

                                    </form>
                                </td>

                            </tr>
                        @endforeach
                    </tbody>

                </table>
            </div>

        </div>
    </div>
@endsection

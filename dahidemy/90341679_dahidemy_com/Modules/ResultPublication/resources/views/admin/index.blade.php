@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Result Submissions') }}
@endsection

@section('create-button')
    <a href="{{ route('resultpublication.admin.create') }}" class="btn btn-sm fw-bold btn-primary">
        {{ _lang('Create Result') }}
    </a>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12">

            <div class="d-flex gap-2 mb-5">
                @foreach (['pending' => 'Pending Review', 'approved' => 'Approved', 'rejected' => 'Rejected'] as $key => $label)
                    <a href="{{ route('resultpublication.admin.index', ['status' => $key]) }}"
                        class="btn btn-sm {{ $status === $key ? 'btn-primary' : 'btn-light' }}">
                        {{ _lang($label) }}
                    </a>
                @endforeach
            </div>

            <div class="table-responsive">
                <table class="table align-middle table-row-dashed fs-6 gy-5">
                    <thead>
                        <tr>
                            <th>{{ _lang('Title') }}</th>
                            <th>{{ _lang('Class / Section') }}</th>
                            <th>{{ _lang('Subject') }}</th>
                            <th>{{ _lang('Exam') }}</th>
                            <th>{{ _lang('Type') }}</th>
                            <th>{{ _lang('Submitted') }}</th>
                            <th>{{ _lang('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($submissions as $submission)
                            <tr>
                                <td><strong>{{ $submission->title }}</strong></td>
                                <td>{{ $submission->class?->class_name }} {{ $submission->section?->section_name }}</td>
                                <td>{{ $submission->subject?->subject_name ?? _lang('All Subjects') }}</td>
                                <td>{{ $submission->exam?->name }}</td>
                                <td>{{ \Modules\ResultPublication\Models\ResultPublication::TYPE_OPTIONS[$submission->type] ?? $submission->type }}</td>
                                <td>{{ $submission->created_at->format('d M Y') }}</td>
                                <td>
                                    <a href="{{ route('resultpublication.admin.show', $submission->id) }}" class="btn btn-sm btn-light-primary">
                                        <i class="fa fa-eye me-1"></i> {{ _lang('Review') }}
                                    </a>
                                    <a href="{{ route('resultpublication.admin.edit', $submission->id) }}" class="btn btn-sm btn-icon btn-light-warning" title="{{ _lang('Edit') }}">
                                        <i class="fa fa-pen"></i>
                                    </a>
                                    <form action="{{ route('resultpublication.admin.destroy', $submission->id) }}" method="POST" class="d-inline"
                                        onsubmit="return confirm('{{ _lang('Delete this result publication? This cannot be undone.') }}')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-icon btn-light-danger" title="{{ _lang('Delete') }}">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-10 text-muted">{{ _lang('Nothing here.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                {{ $submissions->links() }}
            </div>
        </div>
    </div>
@endsection

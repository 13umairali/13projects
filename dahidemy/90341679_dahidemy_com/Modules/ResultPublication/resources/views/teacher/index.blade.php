@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('My Result Submissions') }}
@endsection

@section('create-button')
    <a href="{{ route('resultpublication.teacher.create') }}" class="btn btn-sm fw-bold btn-primary">
        {{ _lang('Submit a Result') }}
    </a>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="table-responsive">
                <table class="table align-middle table-row-dashed fs-6 gy-5">
                    <thead>
                        <tr>
                            <th>{{ _lang('Title') }}</th>
                            <th>{{ _lang('Class / Section') }}</th>
                            <th>{{ _lang('Subject') }}</th>
                            <th>{{ _lang('Exam') }}</th>
                            <th>{{ _lang('Status') }}</th>
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
                                <td>
                                    @php
                                        $badgeClass = match ($submission->statusLabel()) {
                                            'Approved' => 'badge-light-success',
                                            'Rejected' => 'badge-light-danger',
                                            default => 'badge-light-warning',
                                        };
                                    @endphp
                                    <span class="badge {{ $badgeClass }}">{{ _lang($submission->statusLabel()) }}</span>
                                    @if ($submission->status == \Modules\ResultPublication\Models\ResultPublication::STATUS_REJECTED && $submission->remarks)
                                        <div class="text-danger fs-8 mt-1">{{ $submission->remarks }}</div>
                                    @endif
                                </td>
                                <td>
                                    @if ($submission->isPending())
                                        <a href="{{ route('resultpublication.teacher.edit', $submission->id) }}" class="btn btn-sm btn-icon btn-light-primary"><i class="fa fa-pen"></i></a>
                                        <form action="{{ route('resultpublication.teacher.destroy', $submission->id) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ _lang('Delete this submission?') }}')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-icon btn-light-danger"><i class="fa fa-trash"></i></button>
                                        </form>
                                    @else
                                        <span class="text-muted fs-8">{{ _lang('Already reviewed') }}</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-10 text-muted">{{ _lang("You haven't submitted any results yet.") }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                {{ $submissions->links() }}
            </div>
        </div>
    </div>
@endsection

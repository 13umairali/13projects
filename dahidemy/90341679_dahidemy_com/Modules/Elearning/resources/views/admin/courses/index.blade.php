@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Courses') }}
@endsection

@section('create-button')
    <a href="{{ route('elearning.admin.categories.index') }}" class="btn btn-sm btn-light-primary me-2">
        {{ _lang('Categories') }}
    </a>
    <a href="{{ route('elearning.admin.courses.create') }}" class="btn btn-sm fw-bold btn-primary">
        {{ _lang('Add Course') }}
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
                            <th>{{ _lang('Category') }}</th>
                            <th>{{ _lang('Price') }}</th>
                            <th>{{ _lang('Enrolled') }}</th>
                            <th>{{ _lang('Status') }}</th>
                            <th>{{ _lang('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($courses as $course)
                            <tr>
                                <td><strong>{{ $course->title }}</strong></td>
                                <td>{{ $course->courseCategory?->name ?? '—' }}</td>
                                <td>
                                    @if ($course->payment_type === 'free')
                                        <span class="badge badge-light-success">{{ _lang('Free') }}</span>
                                    @else
                                        {{ number_format((float) ($course->offer_price ?: $course->regular_price), 2) }}
                                        <span class="text-muted fs-8">({{ $course->payment_type === 'recurring_payment' ? _lang('subscription') : _lang('one-time') }})</span>
                                    @endif
                                </td>
                                <td>{{ $course->enrollments()->where('status', 'active')->count() }}</td>
                                <td>
                                    @php
                                        $badgeClass = match ($course->status) {
                                            'published' => 'badge-light-success',
                                            'draft' => 'badge-light-secondary',
                                            'schedule' => 'badge-light-warning',
                                            default => 'badge-light-secondary',
                                        };
                                    @endphp
                                    <span class="badge {{ $badgeClass }}">{{ _lang(ucfirst($course->status)) }}</span>
                                </td>
                                <td>
                                    <a href="{{ route('elearning.admin.courses.builder', $course->id) }}" class="btn btn-sm btn-light-success" title="{{ _lang('Manage chapters, lessons, features & FAQs') }}">
                                        <i class="fa fa-list-ul me-1"></i> {{ _lang('Content') }}
                                    </a>
                                    <a href="{{ route('elearning.admin.courses.edit', $course->id) }}" class="btn btn-sm btn-icon btn-light-primary"><i class="fa fa-pen"></i></a>
                                    <form action="{{ route('elearning.admin.courses.destroy', $course->id) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ _lang('Delete this course?') }}')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-icon btn-light-danger"><i class="fa fa-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-10 text-muted">{{ _lang('No courses yet.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                {{ $courses->links() }}
            </div>
        </div>
    </div>
@endsection

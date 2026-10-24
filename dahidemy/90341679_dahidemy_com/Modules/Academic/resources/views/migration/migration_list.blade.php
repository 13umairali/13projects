@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Migration List') }}
@endsection

@section('create-button')
    <a href="{{ route('migrated-list.export-pdf', request()->query()) }}" class="btn btn-sm fw-bold btn-light-danger">
        {{ _lang('Export PDF') }}
    </a>
    <a href="{{ route('migrated-list.export-excel', request()->query()) }}" class="btn btn-sm fw-bold btn-light-success">
        {{ _lang('Export Excel') }}
    </a>
@endsection

@section('content')
    <!--begin:: Main Content Start-->
    <div class="row">
        <div class="col-lg-12">
            <form class="" method="post" action="{{ route('migrated-list.search') }}" autocomplete="off">
                @csrf

                <input type="hidden" name="form_type" value="section_wise">

                <div class="row">
                    <div class="col-lg-4">
                        <div class="form-group">
                            <label class="control-label my-2">{{ _lang('Academic Year') }}</label>
                            <select class="form-select form-select-sm form-select-solid" data-control="select2"
                                name="academic_year_id" required>
                                {!! create_option('academic_years', 'id', 'session', $academicYearId) !!}
                            </select>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="form-group">
                            <label class="control-label my-2">{{ _lang('Section') }}</label>
                            <select class="form-select form-select-sm form-select-solid" data-control="select2"
                                name="section_id" required>
                                <option value="all" {{ $sectionId == 'all' ? 'selected' : '' }}>All
                                </option>
                                {!! create_option('sections', 'id', 'section_name', $sectionId) !!}
                            </select>
                        </div>
                    </div>

                    <div class="col-lg-4 d-flex align-items-end custom-margin">
                        <div class="form-group w-100">
                            <button type="submit" class="btn btn-primary">{{ _lang('Search') }}</button>
                        </div>
                    </div>

                </div>
            </form>
        </div>


        <div class="col-lg-12">
            @if (isset($migratedLists) && count($migratedLists) > 0)
                <div class="table-responsive mt-5">
                    <div class="row">
                        <div class="col-lg-12">
                            <table class="table align-middle table-row-dashed fs-6 gy-5 data-table">
                                <thead>
                                    <tr class="text-start text-gray-400 fw-bold fs-7 text-uppercase gs-0">
                                        <th>{{ _lang('Student ID') }}</th>
                                        <th>{{ _lang('Type') }}</th>
                                        <th>{{ _lang('Roll') }}</th>
                                        <th>{{ _lang('Name') }}</th>
                                        <th>{{ _lang('Migrated From') }}</th>
                                        <th>{{ _lang('Migrated To') }}</th>
                                        <th>{{ _lang('School (if external transfer)') }}</th>
                                    </tr>
                                </thead>

                                <tbody class="fw-semibold text-gray-600">
                                    @if (isset($migratedLists))
                                        @foreach ($migratedLists as $migratedList)
                                            <tr>
                                                <td>
                                                    {{ $migratedList->student_id }}
                                                </td>
                                                <td>
                                                    {{ $migratedList->type }}
                                                </td>
                                                <td>
                                                    {{ $migratedList->roll }}
                                                </td>
                                                <td>
                                                    <span class="text-capitalize">{{ $migratedList->first_name }}</span>
                                                    <span class="text-capitalize">{{ $migratedList->last_name }}</span>
                                                </td>
                                                <td>
                                                    {{ optional($migratedList->class)->class_name }} / {{ optional($migratedList->section)->section_name }}
                                                </td>
                                                <td>
                                                    {{ optional($migratedList->migratedToClass)->class_name ?? '-' }} / {{ optional($migratedList->migratedToSection)->section_name ?? '-' }}
                                                </td>
                                                <td>
                                                    {{ $migratedList->transferred_to_school_name ?? '-' }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr>
                                            <td colspan="7" class="text-danger">No Student Found</td>
                                        </tr>
                                    @endif

                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection

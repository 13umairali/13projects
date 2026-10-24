@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Homepage Sections') }}
@endsection

@section('create-button')
    <a href="{{ route('homepagebuilder.admin.director-message.edit') }}" class="btn btn-sm fw-bold btn-primary">
        {{ _lang("Edit Director's Message") }}
    </a>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-9">
            <div class="alert alert-light-info d-flex align-items-center mb-5">
                <i class="fa fa-info-circle fs-3 me-3"></i>
                <div>
                    {{ _lang('Turn sections on or off, and use the arrows to reorder them — this is exactly the order they will appear on your public homepage. Sections with no content to show (e.g. no events, no courses) stay hidden automatically even when turned on.') }}
                </div>
            </div>

            <div class="card">
                <div class="table-responsive">
                    <table class="table align-middle table-row-dashed fs-6 gy-4">
                        <thead>
                            <tr>
                                <th style="width: 90px">{{ _lang('Order') }}</th>
                                <th>{{ _lang('Section') }}</th>
                                <th>{{ _lang('Status') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($sections as $section)
                                <tr>
                                    <td>
                                        <div class="d-flex gap-1">
                                            <form action="{{ route('homepagebuilder.admin.sections.move-up', $section->id) }}" method="POST">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn btn-sm btn-icon btn-light"><i class="fa fa-arrow-up"></i></button>
                                            </form>
                                            <form action="{{ route('homepagebuilder.admin.sections.move-down', $section->id) }}" method="POST">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn btn-sm btn-icon btn-light"><i class="fa fa-arrow-down"></i></button>
                                            </form>
                                        </div>
                                    </td>
                                    <td><strong>{{ $registry[$section->section_key]['label'] ?? $section->section_key }}</strong></td>
                                    <td>
                                        <form action="{{ route('homepagebuilder.admin.sections.toggle', $section->id) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-sm {{ $section->is_enabled ? 'btn-light-success' : 'btn-light-secondary' }}">
                                                <i class="fa {{ $section->is_enabled ? 'fa-toggle-on' : 'fa-toggle-off' }} me-1"></i>
                                                {{ $section->is_enabled ? _lang('Shown') : _lang('Hidden') }}
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
    </div>
@endsection

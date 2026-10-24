@extends('install.layouts.master')

@section('title', _lang('install_requirements'))

@section('container')
    <div class="install-requirements-container">
        <h2 class="mb-4">{{ _lang('System Requirements') }}</h2>

        <ul class="list-group mb-4">
            @foreach ($requirements as $extension => $enabled)
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    {{ $extension }}
                    @if ($enabled)
                        <span class="badge bg-success">
                            <i class="fa fa-check"></i> OK
                        </span>
                    @else
                        <span class="badge bg-danger">
                            <i class="fa fa-times"></i> Missing
                        </span>
                    @endif
                </li>
            @endforeach
        </ul>

        <div class="d-flex justify-content-end">
            @if ($next)
                <a href="{{ url('install/permissions') }}" class="btn btn-primary">
                    {{ _lang('install_next') }}
                </a>
            @else
                <div class="alert alert-danger me-3">
                    {{ _lang('Some required extensions are missing. Please install them.') }}
                </div>
                <a href="{{ Request::url() }}" class="btn btn-warning">
                    <i class="fa fa-refresh"></i> {{ _lang('refresh') }}
                </a>
            @endif
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .install-requirements-container {
            max-width: 700px;
            margin: 40px auto;
            padding: 30px;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .list-group-item {
            font-size: 1rem;
            font-weight: 500;
        }

        .badge {
            font-size: 0.9rem;
            padding: 0.4em 0.8em;
        }
    </style>
@endpush

@extends('layouts.backend')
@section('page_title_breadcrumb')
    {{ _lang('Notice') }}
@endsection
@section('create-button')
    <a class="btn btn-sm fw-bold btn-light-danger" href="{{ route('notices.export-pdf', $notice->id) }}">
        {{ _lang('Export PDF') }}
    </a>
    <a class="btn btn-sm fw-bold btn-primary" href="{{ route('notices.index') }}">
        {{ _lang('Notice List') }}
    </a>
@endsection
@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card mb-5">
                <div class="card-body">
                    <form action="{{ route('notices.email', $notice->id) }}" method="POST" class="d-flex gap-2">
                        @csrf
                        <input type="email" name="email" class="form-control" placeholder="{{ _lang('Recipient email address') }}" required>
                        <button type="submit" class="btn btn-success text-nowrap">{{ _lang('Send through Email') }}</button>
                    </form>
                </div>
            </div>
            <table class="table table-bordered">
                <tr>
                    <td>
                        <h4>{{ $notice->heading }}</h4>
                    </td>
                </tr>
                <tr>
                    <td class="details-td">{!! $notice->content !!}</td>
                </tr>
                <tr>
                    <td>{{ _lang('Notice Can See Only') }} : {{ object_to_string($notice->userType, 'user_type') }}
                    </td>
                </tr>

            </table>
        </div>
    </div>
@endsection

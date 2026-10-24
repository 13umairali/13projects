@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Create Policy') }}
@endsection

@section('create-button')
    <a href="{{ route('policies.index') }}" class="btn btn-sm fw-bold btn-primary">
        {{ _lang('All Policies') }}
    </a>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12">

            <form method="POST" action="{{ route('policies.store') }}">
                @csrf

                <div class="form-group">
                    <label>Type</label>
                    <select name="type" class="form-control" required>
                        <option value="1">Privacy Policy</option>
                        <option value="2">Cookie Policy</option>
                        <option value="3">Terms & Conditions</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" class="form-control" rows="6"></textarea>
                </div>

                <div class="form-group">
                    <label>Status</label>
                    <select name="status" class="form-control">
                        <option value="1">Active</option>
                        <option value="2">Inactive</option>
                    </select>
                </div>

                <button class="btn btn-primary mt-3">
                    Save
                </button>

            </form>

        </div>
    </div>
@endsection

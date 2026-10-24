@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Edit Policy') }}
@endsection

@section('create-button')
    <a href="{{ route('policies.index') }}" class="btn btn-sm fw-bold btn-primary">
        {{ _lang('All Policies') }}
    </a>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12">

            <form method="POST" action="{{ route('policies.update', $id) }}">
                @csrf
                @method('PATCH')

                <div class="form-group">
                    <label>Type</label>
                    <select name="type" class="form-control">
                        <option value="1" {{ $policy->type == 1 ? 'selected' : '' }}>Privacy Policy</option>
                        <option value="2" {{ $policy->type == 2 ? 'selected' : '' }}>Cookie Policy</option>
                        <option value="3" {{ $policy->type == 3 ? 'selected' : '' }}>Terms & Conditions</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" class="form-control" rows="6">{{ $policy->description }}</textarea>
                </div>

                <div class="form-group">
                    <label>Status</label>
                    <select name="status" class="form-control">
                        <option value="1" {{ $policy->status == 1 ? 'selected' : '' }}>Active</option>
                        <option value="2" {{ $policy->status == 2 ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>

                <button class="btn btn-primary mt-3">
                    Update
                </button>

            </form>

        </div>
    </div>
@endsection

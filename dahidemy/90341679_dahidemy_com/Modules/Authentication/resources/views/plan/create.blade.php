@extends('layouts.backend')

@section('page_title_breadcrumb')
    Create Plan
@endsection

@section('create-button')
    <a href="{{ route('plans.index') }}" class="btn btn-sm fw-bold btn-primary">
        Plan List
    </a>
@endsection

@section('content')
    <div class="card">
        <div class="card-body">

            <form action="{{ route('plans.store') }}" method="POST">

                @csrf

                <div class="row">

                    <div class="col-md-6 mb-3">
                        <label>Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label>Price</label>
                        <input type="number" step="0.01" name="price" class="form-control"
                            value="{{ old('price', 0) }}">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label>Student Limit</label>
                        <input type="number" name="student_limit" class="form-control"
                            value="{{ old('student_limit', 0) }}">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label>Branch Limit</label>
                        <input type="number" name="branch_limit" class="form-control" value="{{ old('branch_limit', 0) }}">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="fw-bold">Trial / Subscription Duration (days) <span class="text-danger">*</span></label>
                        <input type="number" name="duration_days" class="form-control" min="1"
                            placeholder="e.g. 14 for a two-week trial, 365 for a yearly plan"
                            value="{{ old('duration_days') }}">
                        <small class="text-muted">
                            How many days this plan lasts before it expires and the institute must renew. Set a
                            short number (e.g. 14) for a free trial plan, or a full billing cycle (e.g. 30, 365) for a paid plan.
                        </small>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label>Status</label>
                        <select name="status" class="form-control">
                            <option value="1">Active</option>
                            <option value="2">Inactive</option>
                        </select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="fw-bold">Trial Days</label>
                        <input type="number" name="trial_days" class="form-control" min="0"
                            placeholder="e.g. 14"
                            value="{{ old('trial_days') }}">
                        <small class="text-muted">
                            How many days a new sign-up on this plan gets free before it's billed - separate from
                            the plan's own Duration above. Leave blank or 0 for no trial period.
                        </small>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="fw-bold">Is this a Free Trial plan? <span class="text-danger">*</span></label>
                        <select name="is_free" class="form-control">
                            <option value="0">No - this is a paid plan</option>
                            <option value="1">Yes - this is a free trial plan</option>
                        </select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label>Custom Plan</label>
                        <select name="is_custom" class="form-control">
                            <option value="0">No</option>
                            <option value="1">Yes</option>
                        </select>
                    </div>

                    <div class="col-md-12 mb-3">
                        <label>Description</label>
                        <textarea name="description" rows="5" class="form-control">{{ old('description') }}</textarea>
                    </div>

                    <div class="col-md-12">
                        <button type="submit" class="btn btn-primary">
                            Save Plan
                        </button>
                    </div>

                </div>
            </form>

        </div>
    </div>
@endsection
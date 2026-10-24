@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Create About Us') }}
@endsection

@section('create-button')
    <a href="{{ route('about-us.index') }}" class="btn btn-sm fw-bold btn-primary">
        {{ _lang('All About Us') }}
    </a>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12">

            <form method="POST" action="{{ route('about-us.store') }}" enctype="multipart/form-data">

                @csrf

                <!-- Title -->
                <div class="form-group">
                    <label class="my-2">Title</label>
                    <input type="text" name="title" class="form-control" required>
                </div>

                <!-- Description -->
                <div class="form-group">
                    <label class="my-2">Description</label>
                    <textarea name="description" class="form-control" rows="4"></textarea>
                </div>

                <!-- Image -->
                <div class="form-group">
                    <label class="my-2">Image</label>
                    <input type="file" name="image" class="form-control">
                </div>

                <button class="btn btn-primary mt-4">
                    Save About Us
                </button>

            </form>

        </div>
    </div>
@endsection

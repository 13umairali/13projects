@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Edit About Us') }}
@endsection

@section('create-button')
    <a href="{{ route('about-us.index') }}" class="btn btn-sm fw-bold btn-primary">
        {{ _lang('All About Us') }}
    </a>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12">

            <form method="POST" action="{{ route('about-us.update', $id) }}" enctype="multipart/form-data">
                @csrf
                @method('PATCH')

                <!-- Title -->
                <div class="form-group">
                    <label class="my-2">Title</label>
                    <input type="text" name="title" class="form-control" value="{{ $aboutUs->title }}" required>
                </div>

                <!-- Description -->
                <div class="form-group">
                    <label class="my-2">Description</label>
                    <textarea name="description" class="form-control" rows="4">{{ $aboutUs->description }}</textarea>
                </div>

                <!-- Image -->
                <div class="form-group">
                    <label class="my-2">Image</label>
                    <input type="file" name="image" class="form-control">

                    @if ($aboutUs->image)
                        <img src="{{ asset('storage/about_us/' . $aboutUs->image) }}" width="120" class="mb-2 rounded">
                    @endif
                </div>

                <button class="btn btn-primary mt-4">
                    Update About Us
                </button>

            </form>

        </div>
    </div>
@endsection

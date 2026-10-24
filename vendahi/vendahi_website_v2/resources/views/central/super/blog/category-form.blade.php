@extends('central.super.layout')

@section('title', $blogCategory ? __('super.blog.edit_category_title', ['name' => $blogCategory->name]) : __('super.blog.new_category_title'))

@section('content')
<div class="breadcrumb-custom">
    <a href="{{ route('super.blog.index') }}">{{ __('super.blog.title') }}</a>
    <span class="separator">/</span>
    <span class="current">{{ $blogCategory ? __('super.blog.edit_category') : __('super.blog.new_category_title') }}</span>
</div>

<div class="page-header">
    <h1>{{ $blogCategory ? __('super.blog.edit_category') : __('super.blog.new_category_title') }}</h1>
    <p class="page-subtitle">{{ $blogCategory ? __('super.blog.update_category_desc') : __('super.blog.new_category_desc') }}</p>
</div>

<form method="POST" action="{{ $blogCategory ? route('super.blog.categories.update', $blogCategory) : route('super.blog.categories.store') }}">
    @csrf
    @if($blogCategory) @method('PUT') @endif

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="content-card">
                <div class="card-header-custom">
                    <h2><i class="bi bi-folder2 me-2 text-muted"></i>{{ __('super.blog.category_details') }}</h2>
                </div>
                <div class="card-body-custom">
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">{{ __('super.blog.name') }}</label>
                                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $blogCategory->name ?? '') }}" required placeholder="e.g. Product Updates">
                                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">{{ __('super.blog.slug') }}</label>
                                <input type="text" name="slug" class="form-control @error('slug') is-invalid @enderror" value="{{ old('slug', $blogCategory->slug ?? '') }}" required placeholder="product-updates">
                                @error('slug') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>

                    <div class="form-group mb-4">
                        <label class="form-label">{{ __('super.blog.description') }} <span class="text-muted fw-normal">({{ __('super.blog.optional') }})</span></label>
                        <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="3" placeholder="{{ __('super.blog.description_placeholder') }}">{{ old('description', $blogCategory->description ?? '') }}</textarea>
                        @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="form-group mb-4">
                        <label class="form-label">{{ __('super.blog.sort_order') }}</label>
                        <input type="number" name="sort_order" class="form-control max-w-120 @error('sort_order') is-invalid @enderror" value="{{ old('sort_order', $blogCategory->sort_order ?? 0) }}" min="0">
                        @error('sort_order') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="d-flex align-items-center gap-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg"></i> {{ $blogCategory ? __('super.blog.save_changes') : __('super.blog.create_category') }}
                        </button>
                        <a href="{{ route('super.blog.index') }}" class="back-link">{{ __('super.blog.cancel') }}</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="content-card sticky-sidebar">
                <div class="card-header-custom">
                    <h2><i class="bi bi-info-circle me-2 text-muted"></i>{{ __('super.blog.tips') }}</h2>
                </div>
                <div class="card-body-custom">
                    <div class="d-flex flex-column gap-3 sidebar-tips-list">
                        <div class="d-flex gap-2">
                            <i class="bi bi-folder2 flex-shrink-0 tip-icon-primary"></i>
                            <span>{{ __('super.blog.tip_categories') }}</span>
                        </div>
                        <div class="d-flex gap-2">
                            <i class="bi bi-link-45deg flex-shrink-0 tip-icon-info"></i>
                            <span>{!! __('super.blog.tip_slug') !!}</span>
                        </div>
                        <div class="d-flex gap-2">
                            <i class="bi bi-sort-numeric-down flex-shrink-0 tip-icon-success"></i>
                            <span>{{ __('super.blog.tip_sort') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

@push('scripts')
<script>
{{-- kb-category-form.js is generic (name → slug by input name); reuse it. --}}
window.KbCategoryData = { isNew: {{ $blogCategory ? 'false' : 'true' }} };
</script>
<script src="{{ asset('assets_super/js/kb-category-form.js') }}"></script>
@endpush
@endsection

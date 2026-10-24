@extends('central.super.layout')

@section('title', $post ? __('super.blog.edit_post_title', ['name' => $post->title]) : __('super.blog.new_post_title'))

@section('content')
<div class="breadcrumb-custom">
    <a href="{{ route('super.blog.index') }}">{{ __('super.blog.title') }}</a>
    <span class="separator">/</span>
    <span class="current">{{ $post ? __('super.blog.edit_post') : __('super.blog.new_post_title') }}</span>
</div>

<div class="page-header">
    <h1>{{ $post ? __('super.blog.edit_post') : __('super.blog.new_post_title') }}</h1>
    <p class="page-subtitle">{{ $post ? __('super.blog.update_desc') : __('super.blog.new_post_desc') }}</p>
</div>

<form method="POST" action="{{ $post ? route('super.blog.posts.update', $post) : route('super.blog.posts.store') }}" enctype="multipart/form-data">
    @csrf
    @if($post) @method('PUT') @endif

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="content-card mb-4">
                <div class="card-header-custom">
                    <h2><i class="bi bi-file-earmark-text me-2 text-muted"></i>{{ __('super.blog.post_details') }}</h2>
                </div>
                <div class="card-body-custom">
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">{{ __('super.blog.post_title') }}</label>
                                <input type="text" name="title" id="postTitle" class="form-control @error('title') is-invalid @enderror" value="{{ old('title', $post->title ?? '') }}" required placeholder="e.g. 10 Tips for Better Inventory Management">
                                @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">{{ __('super.blog.slug') }}</label>
                                <input type="text" name="slug" id="postSlug" class="form-control @error('slug') is-invalid @enderror" value="{{ old('slug', $post->slug ?? '') }}" required placeholder="10-tips-for-better-inventory-management">
                                @error('slug') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>

                    <div class="form-group mb-4">
                        <label class="form-label">{{ __('super.blog.excerpt') }} <span class="text-muted fw-normal">({{ __('super.blog.optional') }})</span></label>
                        <textarea name="excerpt" class="form-control @error('excerpt') is-invalid @enderror" rows="2" placeholder="{{ __('super.blog.excerpt_placeholder') }}">{{ old('excerpt', $post->excerpt ?? '') }}</textarea>
                        <small class="text-muted">{{ __('super.blog.excerpt_hint') }}</small>
                        @error('excerpt') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                    </div>

                    <div class="form-group mb-4">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <label class="form-label mb-0">{{ __('super.blog.content') }}</label>
                            <button type="button" id="toggleEditor" class="btn btn-sm btn-outline btn-toggle-editor">
                                <i class="bi bi-eye"></i> <span id="toggleLabel">{{ __('super.blog.visual_preview') }}</span>
                            </button>
                        </div>
                        <div id="codeEditor">
                            <textarea
                                name="content"
                                id="postContent"
                                class="form-control textarea-code @error('content') is-invalid @enderror"
                                rows="18"
                                placeholder="{{ __('super.blog.content_placeholder') }}"
                            >{{ old('content', $post->content ?? '') }}</textarea>
                        </div>
                        <div id="visualPreview">
                            <div id="previewContent" class="form-control preview-pane"></div>
                        </div>
                        @error('content') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        <small class="text-muted">{{ __('super.blog.content_hint') }}</small>
                    </div>
                </div>
            </div>

            <div class="content-card mb-4">
                <div class="card-header-custom">
                    <h2><i class="bi bi-search me-2 text-muted"></i>{{ __('super.blog.seo') }}</h2>
                </div>
                <div class="card-body-custom">
                    <div class="form-group mb-4">
                        <label class="form-label">{{ __('super.blog.meta_title') }} <span class="text-muted fw-normal">({{ __('super.blog.optional') }})</span></label>
                        <input type="text" name="meta_title" id="metaTitle" class="form-control @error('meta_title') is-invalid @enderror" value="{{ old('meta_title', $post->meta_title ?? '') }}" maxlength="190" placeholder="{{ __('super.blog.meta_title_placeholder') }}">
                        <small class="text-muted"><span id="metaTitleCount">0</span>/70 — {{ __('super.blog.meta_title_hint') }}</small>
                        @error('meta_title') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">{{ __('super.blog.meta_description') }} <span class="text-muted fw-normal">({{ __('super.blog.optional') }})</span></label>
                        <textarea name="meta_description" id="metaDescription" class="form-control @error('meta_description') is-invalid @enderror" rows="2" maxlength="300" placeholder="{{ __('super.blog.meta_description_placeholder') }}">{{ old('meta_description', $post->meta_description ?? '') }}</textarea>
                        <small class="text-muted"><span id="metaDescriptionCount">0</span>/160 — {{ __('super.blog.meta_description_hint') }}</small>
                        @error('meta_description') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="content-card mb-4 sticky-sidebar">
                <div class="card-header-custom">
                    <h2><i class="bi bi-sliders me-2 text-muted"></i>{{ __('super.blog.settings') }}</h2>
                </div>
                <div class="card-body-custom">
                    <div class="form-group mb-4">
                        <label class="d-flex align-items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="is_published" value="1" class="form-check-input form-check-lg" {{ old('is_published', $post->is_published ?? false) ? 'checked' : '' }}>
                            <span class="fw-600 fs-sm4">{{ __('super.blog.published_label') }}</span>
                        </label>
                        <small class="text-muted d-block mt-1">{{ __('super.blog.unpublished_hint') }}</small>
                    </div>

                    <div class="form-group mb-4">
                        <label class="form-label">{{ __('super.blog.published_at') }} <span class="text-muted fw-normal">({{ __('super.blog.optional') }})</span></label>
                        <input type="datetime-local" name="published_at" class="form-control @error('published_at') is-invalid @enderror" value="{{ old('published_at', isset($post->published_at) ? $post->published_at->format('Y-m-d\TH:i') : '') }}">
                        <small class="text-muted">{{ __('super.blog.published_at_hint') }}</small>
                        @error('published_at') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                    </div>

                    <div class="form-group mb-4">
                        <label class="form-label">{{ __('super.blog.category') }}</label>
                        <select name="blog_category_id" class="form-control @error('blog_category_id') is-invalid @enderror">
                            <option value="">{{ __('super.blog.no_category') }}</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ old('blog_category_id', $post->blog_category_id ?? $preselectedCategory ?? '') == $cat->id ? 'selected' : '' }}>
                                    {{ $cat->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('blog_category_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="form-group mb-4">
                        <label class="form-label">{{ __('super.blog.cover_image') }} <span class="text-muted fw-normal">({{ __('super.blog.optional') }})</span></label>
                        <input type="file" name="cover_image" id="coverImageInput" class="form-control @error('cover_image') is-invalid @enderror" accept="image/jpeg,image/png,image/webp">
                        <small class="text-muted">{{ __('super.blog.cover_image_hint') }}</small>
                        @error('cover_image') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror

                        <img id="coverImagePreview" src="{{ ($post && $post->cover_image) ? asset($post->cover_image) : '' }}" alt="" class="img-fluid rounded mt-2 {{ ($post && $post->cover_image) ? '' : 'd-none' }}">

                        @if($post && $post->cover_image)
                        <label class="d-flex align-items-center gap-2 cursor-pointer mt-2">
                            <input type="checkbox" name="remove_cover_image" value="1" class="form-check-input">
                            <span class="fs-sm3 text-muted">{{ __('super.blog.remove_cover_image') }}</span>
                        </label>
                        @endif
                    </div>

                    @if($post)
                    <div class="divider"></div>
                    <div class="d-flex flex-column gap-2 article-meta-list">
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">{{ __('super.blog.created') }}</span>
                            <span class="fw-600">{{ $post->created_at->format('M d, Y') }}</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">{{ __('super.blog.updated') }}</span>
                            <span class="fw-600">{{ $post->updated_at->diffForHumans() }}</span>
                        </div>
                        @if($post->author)
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">{{ __('super.blog.author') }}</span>
                            <span class="fw-600">{{ $post->author->name }}</span>
                        </div>
                        @endif
                    </div>
                    @endif

                    <div class="d-flex flex-column gap-2 mt-4">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-check-lg"></i> {{ $post ? __('super.blog.save_changes') : __('super.blog.create_post') }}
                        </button>
                        <a href="{{ route('super.blog.index') }}" class="btn btn-outline w-100">{{ __('super.blog.cancel') }}</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

@push('scripts')
<script>
window.BlogPostData = {
    isNew: {{ $post ? 'false' : 'true' }},
    trans: {
        labelVisual: @json(__('super.blog.visual_preview')),
        labelSource: @json(__('super.blog.source_code')),
    },
};
</script>
<script src="{{ asset('assets_super/js/blog-post-form.js') }}"></script>
@endpush
@endsection

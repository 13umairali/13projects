@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ $post->exists ? _lang('Edit Post') : _lang('Write New Post') }}
@endsection

@section('create-button')
    <a href="{{ route('blog.admin.posts.index') }}" class="btn btn-sm fw-bold btn-primary">
        {{ _lang('All Posts') }}
    </a>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-9">
            <div class="card">
                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger mb-6">
                            <strong>{{ _lang('Please fix the following before saving:') }}</strong>
                            <ul class="mb-0 mt-2">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST"
                        action="{{ $post->exists ? route('blog.admin.posts.update', $post->id) : route('blog.admin.posts.store') }}"
                        enctype="multipart/form-data">
                        @csrf
                        @if ($post->exists)
                            @method('PUT')
                        @endif

                        <div class="row g-4 mb-5">
                            <div class="col-md-6">
                                <div class="p-4 rounded border border-2 h-100 status-toggle-card {{ $post->exists && $post->isPublished() ? 'border-success bg-light-success' : 'border-secondary bg-light' }}"
                                    id="statusCard">
                                    <div class="form-check form-switch mb-1">
                                        <input class="form-check-input" type="checkbox" name="status" value="1" id="statusToggle"
                                            onchange="updateToggleCard('statusCard', 'statusCaption', this.checked, 'Live at {{ url('/blog/' . ($post->slug ?: '...')) }}', 'Currently in Draft — not visible to visitors')"
                                            {{ old('status', $post->exists ? $post->isPublished() : false) ? 'checked' : '' }}>
                                        <label class="form-check-label fw-bold fs-5" for="statusToggle">{{ _lang('Live') }}</label>
                                    </div>
                                    <p class="text-muted fs-8 mb-0" id="statusCaption">
                                        @if ($post->exists && $post->isPublished())
                                            {{ _lang('Live at') }} {{ url('/blog/' . $post->slug) }}
                                        @else
                                            {{ _lang('Currently in Draft — not visible to visitors') }}
                                        @endif
                                    </p>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="p-4 rounded border border-2 h-100 bg-light-info border-info">
                                    <p class="fw-bold fs-5 mb-1">{{ _lang('Views') }}</p>
                                    <p class="text-muted fs-8 mb-0">{{ $post->views ?? 0 }} {{ _lang('so far') }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="mb-5">
                            <label class="form-label required">{{ _lang('Title') }}</label>
                            <input type="text" name="title" class="form-control" required value="{{ old('title', $post->title) }}">
                        </div>

                        <div class="row g-4 mb-5">
                            <div class="col-md-6">
                                <label class="form-label">{{ _lang('Category (optional)') }}</label>
                                <select name="category_id" class="form-select">
                                    <option value="">{{ _lang('— None —') }}</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}" @selected(old('category_id', $post->category_id) == $category->id)>{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">{{ _lang('Author Name (optional)') }}</label>
                                <input type="text" name="author_name" class="form-control" value="{{ old('author_name', $post->author_name) }}">
                            </div>
                        </div>

                        <div class="mb-5">
                            <label class="form-label">{{ _lang('Featured Image') }}</label>
                            @if ($post->featured_image)
                                <div class="mb-2"><img src="{{ asset('storage/blog_posts/' . $post->featured_image) }}" width="160" class="rounded"></div>
                            @endif
                            <input type="file" name="featured_image" class="form-control" accept="image/*">
                        </div>

                        <div class="mb-5">
                            <label class="form-label">{{ _lang('Excerpt (short summary for the blog list, optional)') }}</label>
                            <textarea name="excerpt" class="form-control" rows="2" maxlength="500">{{ old('excerpt', $post->excerpt) }}</textarea>
                        </div>

                        <div class="mb-5">
                            <label class="form-label required">{{ _lang('Content') }}</label>
                            <textarea name="content" id="postContent" class="form-control" rows="14">{{ old('content', $post->content) }}</textarea>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-save me-1"></i> {{ $post->exists ? _lang('Update Post') : _lang('Save Post') }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        function updateToggleCard(cardId, captionId, isOn, onText, offText) {
            const card = document.getElementById(cardId);
            if (isOn) {
                card.classList.remove('border-secondary', 'bg-light');
                card.classList.add('border-success', 'bg-light-success');
            } else {
                card.classList.remove('border-success', 'bg-light-success');
                card.classList.add('border-secondary', 'bg-light');
            }
            if (captionId) {
                document.getElementById(captionId).textContent = isOn ? onText : offText;
            }
        }
    </script>
@endsection

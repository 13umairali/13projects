@extends('central.super.layout')

@section('title', __('super.blog.title'))

@section('content')
<div class="page-header d-flex flex-wrap align-items-center justify-content-between gap-3">
    <div>
        <h1>{{ __('super.blog.title') }}</h1>
        <p class="page-subtitle">{{ __('super.blog.subtitle') }}</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('central.blog.index') }}" target="_blank" class="btn btn-outline">
            <i class="bi bi-box-arrow-up-right"></i> {{ __('super.blog.view_blog') }}
        </a>
        <a href="{{ route('super.blog.categories.create') }}" class="btn btn-outline">
            <i class="bi bi-folder-plus"></i> {{ __('super.blog.new_category') }}
        </a>
        <a href="{{ route('super.blog.posts.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> {{ __('super.blog.new_post') }}
        </a>
    </div>
</div>

{{-- Stats --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="content-card kb-stat-card">
            <div class="d-flex align-items-center gap-3">
                <div class="stat-icon stat-icon-info kb-stat-icon">
                    <i class="bi bi-folder2"></i>
                </div>
                <div>
                    <p class="mb-0 fw-800 kb-stat-value">{{ $categories->count() }}</p>
                    <p class="mb-0 text-muted text-uppercase-label-sm">{{ __('super.blog.categories') }}</p>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="content-card kb-stat-card">
            <div class="d-flex align-items-center gap-3">
                <div class="stat-icon stat-icon-success kb-stat-icon">
                    <i class="bi bi-file-earmark-text"></i>
                </div>
                <div>
                    <p class="mb-0 fw-800 kb-stat-value">{{ $totalPosts }}</p>
                    <p class="mb-0 text-muted text-uppercase-label-sm">{{ __('super.blog.total_posts') }}</p>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="content-card kb-stat-card">
            <div class="d-flex align-items-center gap-3">
                <div class="stat-icon kb-stat-icon kb-stat-icon-published">
                    <i class="bi bi-check-circle"></i>
                </div>
                <div>
                    <p class="mb-0 fw-800 kb-stat-value">{{ $publishedPosts }}</p>
                    <p class="mb-0 text-muted text-uppercase-label-sm">{{ __('super.blog.published') }}</p>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="content-card kb-stat-card">
            <div class="d-flex align-items-center gap-3">
                <div class="stat-icon stat-icon-warning kb-stat-icon">
                    <i class="bi bi-eye-slash"></i>
                </div>
                <div>
                    <p class="mb-0 fw-800 kb-stat-value">{{ $totalPosts - $publishedPosts }}</p>
                    <p class="mb-0 text-muted text-uppercase-label-sm">{{ __('super.blog.not_published') }}</p>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Categories --}}
@if($categories->isNotEmpty())
<div class="content-card mb-4">
    <div class="card-header-custom d-flex align-items-center justify-content-between flex-wrap gap-2">
        <h2 class="mb-0"><i class="bi bi-folder2 me-2 text-muted"></i>{{ __('super.blog.categories') }}</h2>
        <a href="{{ route('super.blog.categories.create') }}" class="btn btn-sm btn-outline">
            <i class="bi bi-folder-plus"></i> {{ __('super.blog.new_category') }}
        </a>
    </div>
    <div class="card-body-custom p-0">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th>{{ __('super.blog.name') }}</th>
                    <th>{{ __('super.blog.slug') }}</th>
                    <th>{{ __('super.blog.posts_col') }}</th>
                    <th>{{ __('super.blog.order') }}</th>
                    <th class="action-cell"></th>
                </tr>
            </thead>
            <tbody>
                @foreach($categories as $cat)
                <tr>
                    <td><span class="fw-600 fs-sm4">{{ $cat->name }}</span></td>
                    <td><span class="text-muted fs-sm3">{{ $cat->slug }}</span></td>
                    <td><span class="badge badge-active badge-sm">{{ $cat->posts_count }}</span></td>
                    <td><span class="text-muted fs-sm3">{{ $cat->sort_order }}</span></td>
                    <td class="action-cell">
                        <div class="d-flex gap-1 justify-content-end">
                            <a href="{{ route('super.blog.categories.edit', $cat) }}" class="btn btn-sm btn-outline">
                                <i class="bi bi-pencil"></i>
                            </a>
                            @php $catDeleteConfig = json_encode(["title" => __("super.blog.delete_category"), "text" => __("super.blog.delete_category_text", ["name" => $cat->name]), "icon" => "warning", "confirmButtonText" => __("super.blog.yes_delete"), "confirmButtonColor" => "#ef4444"]); @endphp
                            <form method="POST" action="{{ route('super.blog.categories.destroy', $cat) }}" class="swal-confirm-form" data-swal-confirm='{{ $catDeleteConfig }}'>
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline btn-outline-danger-sm">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

{{-- Posts --}}
<div class="content-card">
    <div class="card-header-custom">
        <h2 class="mb-0"><i class="bi bi-file-earmark-text me-2 text-muted"></i>{{ __('super.blog.posts') }}</h2>
    </div>
    @if($posts->isNotEmpty())
    <div class="card-body-custom p-0">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th>{{ __('super.blog.title_col') }}</th>
                    <th>{{ __('super.blog.category') }}</th>
                    <th>{{ __('super.blog.status') }}</th>
                    <th>{{ __('super.blog.published_at') }}</th>
                    <th class="action-cell"></th>
                </tr>
            </thead>
            <tbody>
                @foreach($posts as $post)
                <tr>
                    <td>
                        <span class="fw-600 fs-sm4">{{ $post->title }}</span>
                        <span class="d-block text-muted fs-xs2">{{ $post->slug }}</span>
                    </td>
                    <td>
                        @if($post->category)
                            <span class="badge badge-active badge-sm">{{ $post->category->name }}</span>
                        @else
                            <span class="text-muted fs-sm3">—</span>
                        @endif
                    </td>
                    <td>
                        @if($post->is_published && $post->published_at && $post->published_at->isFuture())
                            <span class="badge badge-pending badge-sm">{{ __('super.blog.scheduled') }}</span>
                        @elseif($post->is_published && $post->published_at)
                            <a href="{{ route('central.blog.show', $post->slug) }}" target="_blank" class="badge badge-active badge-sm">{{ __('super.blog.published') }}</a>
                        @else
                            <span class="badge badge-suspended badge-sm">{{ __('super.blog.draft') }}</span>
                        @endif
                    </td>
                    <td>
                        <span class="text-muted fs-sm2">{{ $post->published_at ? $post->published_at->format('M d, Y H:i') : '—' }}</span>
                    </td>
                    <td class="action-cell">
                        <div class="d-flex gap-1 justify-content-end">
                            <a href="{{ route('super.blog.posts.edit', $post) }}" class="btn btn-sm btn-outline">
                                <i class="bi bi-pencil"></i>
                            </a>
                            @php $postDeleteConfig = json_encode(["title" => __("super.blog.delete_post"), "text" => __("super.blog.delete_post_text", ["name" => $post->title]), "icon" => "warning", "confirmButtonText" => __("super.blog.delete"), "confirmButtonColor" => "#ef4444"]); @endphp
                            <form method="POST" action="{{ route('super.blog.posts.destroy', $post) }}" class="swal-confirm-form" data-swal-confirm='{{ $postDeleteConfig }}'>
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline btn-outline-danger-sm">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @if($posts->hasPages())
    <div class="card-body-custom border-top">
        {{ $posts->links() }}
    </div>
    @endif
    @else
    <div class="card-body-custom">
        <div class="empty-state">
            <div class="empty-icon"><i class="bi bi-journal-text"></i></div>
            <h3>{{ __('super.blog.no_posts') }}</h3>
            <p>{{ __('super.blog.no_posts_desc') }}</p>
            <a href="{{ route('super.blog.posts.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg"></i> {{ __('super.blog.create_first_post') }}
            </a>
        </div>
    </div>
    @endif
</div>

@push('scripts')
<script src="{{ asset('assets_super/js/admins-index.js') }}"></script>
@endpush

@endsection

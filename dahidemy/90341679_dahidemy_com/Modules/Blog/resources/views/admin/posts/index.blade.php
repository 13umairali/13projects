@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Blog Posts') }}
@endsection

@section('create-button')
    <a href="{{ route('blog.admin.categories.index') }}" class="btn btn-sm btn-light-primary me-2">
        {{ _lang('Categories') }}
    </a>
    <a href="{{ route('blog.admin.posts.create') }}" class="btn btn-sm fw-bold btn-primary">
        {{ _lang('Write New Post') }}
    </a>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="table-responsive">
                <table class="table align-middle table-row-dashed fs-6 gy-5">
                    <thead>
                        <tr>
                            <th>{{ _lang('Title') }}</th>
                            <th>{{ _lang('Category') }}</th>
                            <th>{{ _lang('Views') }}</th>
                            <th>{{ _lang('Status') }}</th>
                            <th>{{ _lang('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($posts as $post)
                            <tr>
                                <td>
                                    <strong>{{ $post->title }}</strong>
                                    <br><span class="text-muted fs-8">/blog/{{ $post->slug }}</span>
                                </td>
                                <td>{{ $post->category?->name ?? '—' }}</td>
                                <td>{{ $post->views }}</td>
                                <td>
                                    <form action="{{ route('blog.admin.posts.toggle-status', $post->id) }}" method="POST"
                                        onsubmit="return confirm('{{ $post->isPublished() ? _lang('Move this post back to Draft?') : _lang('Publish this post? It will become publicly visible.') }}')">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-sm {{ $post->isPublished() ? 'btn-light-success' : 'btn-warning' }}">
                                            <i class="fa {{ $post->isPublished() ? 'fa-toggle-on' : 'fa-toggle-off' }} me-1"></i>
                                            {{ $post->isPublished() ? _lang('Live') : _lang('Draft — click to publish') }}
                                        </button>
                                    </form>
                                </td>
                                <td>
                                    <a href="{{ route('blog.admin.posts.edit', $post->id) }}" class="btn btn-sm btn-icon btn-light-primary"><i class="fa fa-pen"></i></a>
                                    <form action="{{ route('blog.admin.posts.destroy', $post->id) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ _lang('Delete this post?') }}')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-icon btn-light-danger"><i class="fa fa-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-10 text-muted">{{ _lang("You haven't written any posts yet.") }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                {{ $posts->links() }}
            </div>
        </div>
    </div>
@endsection

@extends('themes.' . config('frontend.active_theme', 'theme-three') . '.layouts.app')

@section('title', $post->title)

@section('content')
    <section class="py-20 bg-white">
        <div class="max-w-3xl mx-auto px-4">
            <a href="{{ route('blog-page.index') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-slate-500 hover:text-brand mb-8">
                <i class="fa-solid fa-arrow-left"></i> All Articles
            </a>

            @if ($post->category)
                <span class="eyebrow mb-4">{{ $post->category->name }}</span>
            @endif

            <h1 class="section-title mb-5">{{ $post->title }}</h1>

            <div class="flex flex-wrap items-center gap-4 text-sm text-slate-400 mb-10">
                @if ($post->author_name)
                    <span><i class="fa-regular fa-user"></i> {{ $post->author_name }}</span>
                @endif
                <span><i class="fa-regular fa-calendar"></i> {{ optional($post->published_at)->format('d M Y') }}</span>
                <span><i class="fa-regular fa-clock"></i> {{ $post->readingTimeMinutes() }} min read</span>
                <span><i class="fa-regular fa-eye"></i> {{ $post->views }} views</span>
            </div>

            @if ($post->featured_image)
                <div class="rounded-[28px] overflow-hidden mb-10">
                    <img src="{{ asset('storage/blog_posts/' . $post->featured_image) }}" class="w-full h-auto object-cover" alt="{{ $post->title }}">
                </div>
            @endif

            <div class="prose max-w-none text-slate-600 leading-8">
                {!! $post->content !!}
            </div>

            @if ($relatedPosts->isNotEmpty())
                <div class="mt-16 pt-10 border-t border-slate-100">
                    <h3 class="text-lg font-bold mb-6 font-display">More Articles</h3>
                    <div class="grid sm:grid-cols-3 gap-6">
                        @foreach ($relatedPosts as $related)
                            <a href="{{ route('blog-page.show', $related->slug) }}" class="group">
                                <div class="rounded-2xl overflow-hidden h-32 mb-3">
                                    @if ($related->featured_image)
                                        <img src="{{ asset('storage/blog_posts/' . $related->featured_image) }}" class="w-full h-full object-cover group-hover:scale-105 transition" alt="{{ $related->title }}">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center text-white" style="background: linear-gradient(135deg, var(--brand-600), var(--accent-500));">
                                            <i class="fa-solid fa-newspaper"></i>
                                        </div>
                                    @endif
                                </div>
                                <p class="text-sm font-semibold text-slate-700 group-hover:text-brand line-clamp-2">{{ $related->title }}</p>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </section>
@endsection

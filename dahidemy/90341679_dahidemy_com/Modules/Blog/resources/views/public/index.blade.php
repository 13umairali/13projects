@extends('themes.' . config('frontend.active_theme', 'theme-three') . '.layouts.app')

@section('title', 'Blog')

@section('content')
    <section class="py-20 bg-white">
        <div class="max-w-6xl mx-auto px-4">
            <div class="text-center mb-14">
                <span class="eyebrow mb-5">Our Blog</span>
                <h1 class="section-title">{{ $category ? $category->name : 'Latest Articles' }}</h1>
            </div>

            @if ($categories->isNotEmpty())
                <div class="flex flex-wrap justify-center gap-2 mb-14">
                    <a href="{{ route('blog-page.index') }}"
                        class="px-5 py-2.5 rounded-full text-sm font-semibold transition {{ ! $category ? 'bg-brand text-white shadow-lg' : 'bg-white text-slate-600 border border-slate-200 hover:border-brand' }}">
                        All
                    </a>
                    @foreach ($categories as $cat)
                        <a href="{{ route('blog-page.index', ['category' => $cat->slug]) }}"
                            class="px-5 py-2.5 rounded-full text-sm font-semibold transition {{ $category?->id === $cat->id ? 'bg-brand text-white shadow-lg' : 'bg-white text-slate-600 border border-slate-200 hover:border-brand' }}">
                            {{ $cat->name }} ({{ $cat->posts_count }})
                        </a>
                    @endforeach
                </div>
            @endif

            @if ($posts->isEmpty())
                <div class="text-center py-20 text-slate-400">
                    <i class="fa-solid fa-newspaper text-4xl mb-4"></i>
                    <p>No posts published yet.</p>
                </div>
            @else
                <div class="grid md:grid-cols-2 xl:grid-cols-3 gap-8">
                    @foreach ($posts as $post)
                        <a href="{{ route('blog-page.show', $post->slug) }}" class="card-elevated group overflow-hidden">
                            <div class="overflow-hidden relative h-48">
                                @if ($post->featured_image)
                                    <img src="{{ asset('storage/blog_posts/' . $post->featured_image) }}" class="w-full h-full object-cover group-hover:scale-110 transition duration-700" alt="{{ $post->title }}">
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-white text-3xl" style="background: linear-gradient(135deg, var(--brand-600), var(--accent-500));">
                                        <i class="fa-solid fa-newspaper"></i>
                                    </div>
                                @endif
                            </div>
                            <div class="p-6">
                                @if ($post->category)
                                    <p class="text-xs text-brand font-semibold uppercase tracking-wide mb-2">{{ $post->category->name }}</p>
                                @endif
                                <h3 class="text-lg font-bold mb-3 font-display line-clamp-2">{{ $post->title }}</h3>
                                <p class="text-slate-500 text-sm leading-6 mb-4 line-clamp-2">
                                    {{ $post->excerpt ?: Str::limit(strip_tags($post->content ?? ''), 100) }}
                                </p>
                                <p class="text-xs text-slate-400">
                                    {{ optional($post->published_at)->format('d M Y') }} &middot; {{ $post->readingTimeMinutes() }} {{ 'min read' }}
                                </p>
                            </div>
                        </a>
                    @endforeach
                </div>

                <div class="mt-12">
                    {{ $posts->links() }}
                </div>
            @endif
        </div>
    </section>
@endsection

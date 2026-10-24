{{-- =========================================================
    Latest blog posts, for the homepage. Uses $data['latestBlogPosts']
    fetched by WebsiteController. Renders nothing if empty.
========================================================= --}}
@if (isset($data['latestBlogPosts']) && $data['latestBlogPosts']->count() > 0)
    <section class="py-24 bg-slate-50">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-14" data-aos="fade-up">
                <div>
                    <span class="eyebrow mb-4">From the Blog</span>
                    <h2 class="section-title">Latest Articles</h2>
                </div>
                @if (Route::has('blog-page.index'))
                    <a href="{{ route('blog-page.index') }}" class="inline-flex items-center gap-2 font-semibold text-brand shrink-0">
                        View All Articles <i class="fa-solid fa-arrow-right-long"></i>
                    </a>
                @endif
            </div>

            <div class="grid sm:grid-cols-2 xl:grid-cols-3 gap-8">
                @foreach ($data['latestBlogPosts'] as $post)
                    <a href="{{ Route::has('blog-page.show') ? route('blog-page.show', $post->slug) : '#' }}"
                        class="card-elevated group overflow-hidden" data-aos="fade-up" data-aos-delay="{{ $loop->iteration * 80 }}">
                        <div class="overflow-hidden relative h-44">
                            @if ($post->featured_image)
                                <img src="{{ asset('storage/blog_posts/' . $post->featured_image) }}" class="w-full h-full object-cover group-hover:scale-110 transition duration-700" alt="{{ $post->title }}">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-white text-2xl" style="background: linear-gradient(135deg, var(--brand-600), var(--accent-500));">
                                    <i class="fa-solid fa-newspaper"></i>
                                </div>
                            @endif
                        </div>
                        <div class="p-6">
                            <h3 class="font-bold mb-2 font-display line-clamp-2">{{ $post->title }}</h3>
                            <p class="text-sm text-slate-500 line-clamp-2 mb-3">
                                {{ $post->excerpt ?: Str::limit(strip_tags($post->content ?? ''), 90) }}
                            </p>
                            <p class="text-xs text-slate-400">{{ optional($post->published_at)->format('d M Y') }}</p>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    </section>
@endif

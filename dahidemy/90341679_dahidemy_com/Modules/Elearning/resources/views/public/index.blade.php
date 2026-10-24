@extends('themes.' . config('frontend.active_theme', 'theme-three') . '.layouts.app')

@section('title', 'Courses')

@section('content')
    <section class="py-20 bg-white">
        <div class="max-w-6xl mx-auto px-4">
            <div class="text-center mb-14">
                <span class="eyebrow mb-5">Learn With Us</span>
                <h1 class="section-title">Our Courses</h1>
            </div>

            @if ($courses->isEmpty())
                <div class="text-center py-20 text-slate-400">
                    <i class="fa-solid fa-graduation-cap text-4xl mb-4"></i>
                    <p>No courses are available right now.</p>
                </div>
            @else
                <div class="grid md:grid-cols-2 xl:grid-cols-3 gap-8">
                    @foreach ($courses as $course)
                        <a href="{{ route('courses-page.show', $course->slug) }}" class="card-elevated group overflow-hidden">
                            <div class="overflow-hidden relative h-48">
                                @if ($course->image)
                                    <img src="{{ asset('storage/courses/' . $course->image) }}" class="w-full h-full object-cover group-hover:scale-110 transition duration-700" alt="{{ $course->title }}">
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-white text-3xl" style="background: linear-gradient(135deg, var(--brand-600), var(--accent-500));">
                                        <i class="fa-solid fa-graduation-cap"></i>
                                    </div>
                                @endif

                                @if ($course->payment_type === 'free')
                                    <span class="absolute top-4 right-4 bg-emerald-500 text-white text-xs font-semibold px-3 py-1.5 rounded-full">Free</span>
                                @else
                                    <span class="absolute top-4 right-4 bg-slate-900/80 text-white text-xs font-semibold px-3 py-1.5 rounded-full">
                                        {{ number_format((float) ($course->offer_price ?: $course->regular_price), 2) }}
                                    </span>
                                @endif
                            </div>

                            <div class="p-6">
                                @if ($course->courseCategory)
                                    <p class="text-xs text-brand font-semibold uppercase tracking-wide mb-2">{{ $course->courseCategory->name }}</p>
                                @endif
                                <h3 class="text-lg font-bold mb-3 font-display line-clamp-2">{{ $course->title }}</h3>
                                <p class="text-slate-500 text-sm leading-6 mb-4 line-clamp-2">{{ Str::limit(strip_tags($course->description ?? ''), 100) }}</p>
                                <span class="inline-flex items-center gap-2 text-brand font-semibold text-sm">
                                    View Course <i class="fa-solid fa-arrow-right group-hover:translate-x-1 transition-transform"></i>
                                </span>
                            </div>
                        </a>
                    @endforeach
                </div>

                <div class="mt-12">
                    {{ $courses->links() }}
                </div>
            @endif
        </div>
    </section>
@endsection

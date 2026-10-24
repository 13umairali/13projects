@extends('themes.' . config('frontend.active_theme', 'theme-three') . '.layouts.app')

@section('title', $course->title)

@section('content')
    <section class="py-20 bg-white">
        <div class="max-w-5xl mx-auto px-4">
            <a href="{{ route('courses-page.index') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-slate-500 hover:text-brand mb-8">
                <i class="fa-solid fa-arrow-left"></i> All Courses
            </a>

            <div class="grid lg:grid-cols-5 gap-10">
                {{-- LEFT: details --}}
                <div class="lg:col-span-3">
                    <div class="rounded-[28px] overflow-hidden h-64 mb-8">
                        @if ($course->image)
                            <img src="{{ asset('storage/courses/' . $course->image) }}" class="w-full h-full object-cover" alt="{{ $course->title }}">
                        @else
                            <div class="w-full h-full flex items-center justify-center text-white text-5xl" style="background: linear-gradient(135deg, var(--brand-600), var(--accent-500));">
                                <i class="fa-solid fa-graduation-cap"></i>
                            </div>
                        @endif
                    </div>

                    @if ($course->courseCategory)
                        <span class="eyebrow mb-4">{{ $course->courseCategory->name }}</span>
                    @endif

                    <h1 class="section-title mb-5">{{ $course->title }}</h1>

                    <div class="flex flex-wrap gap-6 mb-8 text-sm text-slate-600">
                        @if ($course->total_classes)
                            <div class="flex items-center gap-2"><i class="fa-solid fa-chalkboard text-brand"></i> {{ $course->total_classes }} Classes</div>
                        @endif
                        @if ($course->total_exams)
                            <div class="flex items-center gap-2"><i class="fa-solid fa-file-pen text-brand"></i> {{ $course->total_exams }} Exams</div>
                        @endif
                        @if ($course->total_notes)
                            <div class="flex items-center gap-2"><i class="fa-solid fa-book text-brand"></i> {{ $course->total_notes }} Notes</div>
                        @endif
                    </div>

                    <div class="prose max-w-none text-slate-600 leading-8 mb-10">
                        {!! $course->description !!}
                    </div>

                    @if ($course->courseFeatures->isNotEmpty())
                        <h3 class="text-lg font-bold mb-4 font-display">What's Included</h3>
                        <div class="grid sm:grid-cols-2 gap-3 mb-10">
                            @foreach ($course->courseFeatures as $feature)
                                <div class="flex items-center gap-3 text-sm text-slate-600">
                                    <span class="w-6 h-6 rounded-full bg-brand-50 text-brand flex items-center justify-center shrink-0"><i class="fa-solid fa-check text-xs"></i></span>
                                    {{ $feature->name }}
                                </div>
                            @endforeach
                        </div>
                    @endif

                    @if ($course->courseFaqs->isNotEmpty())
                        <h3 class="text-lg font-bold mb-4 font-display">Frequently Asked Questions</h3>
                        <div class="space-y-3">
                            @foreach ($course->courseFaqs as $faq)
                                <details class="faq-item bg-slate-50 border border-slate-100 rounded-2xl px-5 py-4">
                                    <summary class="font-semibold text-slate-800">{{ $faq->question }}</summary>
                                    <p class="pt-3 text-sm text-slate-500 leading-6">{{ $faq->answer }}</p>
                                </details>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- RIGHT: enroll card --}}
                <div class="lg:col-span-2">
                    <div class="sticky top-24 card-elevated p-8">
                        <div class="flex items-center justify-between mb-6">
                            <span class="text-sm text-slate-400">Price</span>
                            @if ($isFree)
                                <span class="text-2xl font-black font-display text-emerald-600">Free</span>
                            @else
                                <div class="text-right">
                                    @if ($course->offer_price && $course->offer_price < $course->regular_price)
                                        <span class="block text-xs text-slate-400 line-through">{{ number_format((float) $course->regular_price, 2) }}</span>
                                    @endif
                                    <span class="text-2xl font-black font-display text-brand">{{ number_format($price, 2) }}</span>
                                </div>
                            @endif
                        </div>

                        @if (session('error'))
                            <div class="mb-5 px-4 py-3 rounded-xl border border-red-200 bg-red-50 text-red-700 text-sm">
                                {{ session('error') }}
                            </div>
                        @endif

                        @if ($alreadyEnrolled)
                            <div class="text-center py-6">
                                <i class="fa-solid fa-circle-check text-3xl text-emerald-500 mb-3"></i>
                                <p class="text-sm text-slate-600">You're already enrolled in this course.</p>
                            </div>
                        @elseif (! $canSelfCheckout)
                            <div class="text-center py-6">
                                <i class="fa-solid fa-headset text-3xl text-slate-400 mb-3"></i>
                                <p class="text-sm text-slate-500">This is a subscription course — please contact us to enroll.</p>
                                <a href="{{ route('contact') }}" class="btn-cta mt-4 justify-center w-full">Contact Us</a>
                            </div>
                        @elseif (auth()->check())
                            <form method="POST" action="{{ route('courses-page.enroll', $course->slug) }}">
                                @csrf
                                <button type="submit" class="btn-cta w-full justify-center py-4">
                                    {{ $isFree ? 'Enroll For Free' : 'Enroll Now' }} <i class="fa-solid fa-arrow-right"></i>
                                </button>
                            </form>
                        @else
                            <a href="{{ url('/login') }}" class="btn-cta w-full justify-center py-4">
                                Login to Enroll <i class="fa-solid fa-arrow-right"></i>
                            </a>
                            <p class="text-xs text-slate-400 mt-3 text-center">You'll need an account to enroll in this course.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

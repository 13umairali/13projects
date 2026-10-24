{{-- =========================================================
    COURSES — replaces the mesh-gradient background, glowing
    blob cards, and gradient-highlighted headline word with a
    clean navy field and bordered cards.
========================================================= --}}
@if ($data['courses']->count() > 0)
    <section id="courses" class="relative py-24 text-white" style="background: var(--surface-dark);">
        <div class="max-w-7xl mx-auto px-4 relative z-10">
            <div class="max-w-2xl mb-16" data-aos="fade-up">
                <span class="eyebrow on-dark mb-5">Academic Courses</span>
                <h2 class="text-4xl md:text-5xl font-display">
                    Explore our courses
                </h2>
            </div>

            <div class="grid md:grid-cols-2 xl:grid-cols-3 gap-8">
                @foreach ($data['courses'] as $course)
                    <div class="border overflow-hidden" style="border-color: rgba(255,255,255,.12); border-radius: var(--radius-md);">
                        <div class="overflow-hidden relative">
                            <img src="{{ asset('storage/courses/' . $course->image) }}" class="w-full h-56 object-cover" alt="{{ $course->title }}">
                            @if (!empty($course->payment_type))
                                <span class="absolute top-4 left-4 px-3 py-1.5 text-xs font-semibold" style="background: var(--brass-500); border-radius: var(--radius-sm);">
                                    {{ ucfirst($course->payment_type) }}
                                </span>
                            @endif
                        </div>

                        <div class="p-7">
                            <h3 class="text-xl font-display mb-3">{{ $course->title }}</h3>
                            <p class="leading-7 mb-6 text-sm" style="color: rgba(255,255,255,.6);">{{ Str::limit(strip_tags($course->description ?? ''), 110) }}</p>

                            <div class="grid grid-cols-3 gap-3 mb-7 text-center border-t border-b py-4" style="border-color: rgba(255,255,255,.12);">
                                <div>
                                    <h4 class="font-display text-xl">{{ $course->total_classes ?? 0 }}</h4>
                                    <p class="text-[11px]" style="color: rgba(255,255,255,.5);">Classes</p>
                                </div>
                                <div>
                                    <h4 class="font-display text-xl">{{ $course->total_exams ?? 0 }}</h4>
                                    <p class="text-[11px]" style="color: rgba(255,255,255,.5);">Exams</p>
                                </div>
                                <div>
                                    <h4 class="font-display text-xl">{{ $course->total_notes ?? 0 }}</h4>
                                    <p class="text-[11px]" style="color: rgba(255,255,255,.5);">Notes</p>
                                </div>
                            </div>

                            <a href="{{ Route::has('courses-page.show') ? route('courses-page.show', $course->slug) : '#' }}" class="btn-cta w-full justify-center">
                                Enroll Now
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endif
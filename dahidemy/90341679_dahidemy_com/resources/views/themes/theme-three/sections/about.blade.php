{{-- =====================================================
        WHY CHOOSE US — editorial masthead header (left-aligned
        label + rule, no centered gradient headline), feature
        list in a bordered grid instead of glowing icon blobs.
====================================================== --}}
@if ($data['whyChooseUs']->count() > 0)
    <section class="py-24 md:py-28 bg-white relative">
        <div class="max-w-7xl mx-auto px-4">
            <div class="max-w-2xl mb-16" data-aos="fade-up">
                <span class="eyebrow mb-4">Why We Stand Out</span>
                <h2 class="section-title">Why families choose us</h2>
            </div>

            <div class="grid sm:grid-cols-2 lg:grid-cols-4 border-t border-l" style="border-color: var(--line-200);">
                @foreach ($data['whyChooseUs'] as $item)
                    <div class="card-elevated p-9 border-r border-b" style="border-radius:0; border-color: var(--line-200); box-shadow:none;">
                        <div class="w-14 h-14 flex items-center justify-center mb-6" style="background: var(--brass-50); border-radius: var(--radius-sm);">
                            @if (!empty($item->icon))
                                <img src="{{ asset('storage/why_choose_us/' . $item->icon) }}" alt="{{ $item->title ?? 'Icon' }}" class="w-full h-full object-cover" style="border-radius: var(--radius-sm);">
                            @else
                                <i class="fa-solid fa-award text-xl" style="color: var(--brass-600);"></i>
                            @endif
                        </div>
                        <h3 class="text-lg font-display mb-3">{{ $item->title }}</h3>
                        <p class="leading-7 text-sm" style="color: var(--slate-500);">{{ $item->description }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endif

{{-- =====================================================
        ABOUT — asymmetric editorial layout: framed image with
        a bordered stat callout (not an overlapping floating
        blob card), serif headline on the other side.
====================================================== --}}
@if ($data['about'])
    <section id="about" class="py-24 md:py-28" style="background: var(--paper-100);">
        <div class="max-w-7xl mx-auto grid lg:grid-cols-2 gap-16 items-center px-4">
            <div class="relative" data-aos="fade-right">
                <img src="{{ asset('storage/about_us/' . $data['about']->image) }}" class="w-full object-cover border" alt="About us" style="border-radius: var(--radius-md); border-color: var(--line-200);">

                @if ($data['history'])
                    <div class="absolute -bottom-6 -right-6 bg-white p-6 hidden md:block border" style="border-radius: var(--radius-md); border-color: var(--line-200);">
                        <p class="text-3xl font-display" style="color: var(--navy-800);">Since {{ $data['history']->year }}</p>
                        <p class="text-xs mt-1 max-w-[160px]" style="color: var(--slate-500);">{{ $data['history']->title ?? 'A legacy of academic excellence' }}</p>
                    </div>
                @endif
            </div>

            <div data-aos="fade-left">
                <span class="eyebrow mb-4">About Us</span>
                <h2 class="section-title mb-6">{{ $data['about']->title }}</h2>
                <div class="leading-8 prose max-w-none" style="color: var(--ink-700);">{!! $data['about']->description !!}</div>

                <div class="mt-8 flex flex-wrap gap-4 items-center">
                    <a href="{{ route('contact') }}" class="btn-cta">Get In Touch</a>
                    <a href="{{ route('academic-images') }}" class="inline-flex items-center gap-2 font-semibold" style="color: var(--navy-800);">
                        View Gallery <i class="fa-solid fa-arrow-right-long text-sm"></i>
                    </a>
                </div>
            </div>
        </div>
    </section>
@endif

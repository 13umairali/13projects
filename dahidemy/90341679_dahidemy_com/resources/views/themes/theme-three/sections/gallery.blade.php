{{-- =========================================================
    NEW SECTION — campus gallery preview on the homepage,
    linking through to the full gallery page.
========================================================= --}}
@if ($data['academicImages'] && count($data['academicImages']) > 0)
    <section class="py-24 bg-slate-50">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-12" data-aos="fade-up">
                <div>
                    <span class="eyebrow mb-4">Campus Life</span>
                    <h2 class="section-title">A Glimpse Of Our Campus</h2>
                </div>
                <a href="{{ route('academic-images') }}" class="inline-flex items-center gap-2 font-semibold text-brand shrink-0">
                    View Full Gallery <i class="fa-solid fa-arrow-right-long"></i>
                </a>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-5">
                @foreach ($data['academicImages']->take(8) as $index => $image)
                    <div class="gallery-tile {{ $index === 0 ? 'col-span-2 row-span-2' : '' }}" data-aos="zoom-in" data-aos-delay="{{ $loop->iteration * 60 }}">
                        <img src="{{ asset('storage/academic_images/' . $image->image) }}"
                            class="w-full h-full object-cover {{ $index === 0 ? 'aspect-square md:aspect-auto md:h-full' : 'aspect-square' }}"
                            alt="{{ $image->title ?? 'Campus photo' }}">
                        <div class="overlay flex items-end p-4">
                            <p class="text-white text-sm font-semibold truncate">{{ $image->title ?? $image->heading }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endif

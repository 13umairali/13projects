@if ($data['testimonials'] && count($data['testimonials']) > 0)
    <section class="py-24 bg-white border-y" style="border-color:var(--nl-line)">
        <div class="max-w-7xl mx-auto px-4">
            <div class="text-center max-w-2xl mx-auto mb-16" data-aos="fade-up">
                <span class="eyebrow justify-center">Testimonials</span>
                <h2 class="font-display text-4xl mt-4">What families say</h2>
            </div>

            <div class="grid md:grid-cols-2 xl:grid-cols-3 gap-6">
                @foreach ($data['testimonials'] as $testimonial)
                    <div class="nl-card p-8" data-aos="fade-up" data-aos-delay="{{ $loop->iteration * 80 }}">
                        <div class="nl-quote"></div>
                        <p class="leading-7 italic mb-6" style="color:var(--nl-muted)">
                            {{ Str::limit($testimonial->description, 160) }}
                        </p>
                        <div class="flex gap-1 mb-6" style="color:var(--nl-accent)">
                            @for ($i = 1; $i <= 5; $i++)
                                <i class="fas fa-star" style="{{ $i <= $testimonial->ratting ? '' : 'opacity:.25' }}"></i>
                            @endfor
                        </div>
                        <div class="flex items-center gap-4">
                            <img src="{{ $testimonial->thumbnail_image && file_exists(public_path('storage/testimonials/' . $testimonial->thumbnail_image))
                                ? asset('storage/testimonials/' . $testimonial->thumbnail_image)
                                : asset('images/user.png') }}"
                                class="w-12 h-12 rounded-full object-cover">
                            <div>
                                <h4 class="font-semibold">{{ $testimonial->name }}</h4>
                                <p class="text-sm" style="color:var(--nl-muted)">{{ $testimonial->note }}</p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endif

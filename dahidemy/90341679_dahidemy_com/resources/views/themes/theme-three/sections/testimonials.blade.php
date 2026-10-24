@if ($data['testimonials'] && count($data['testimonials']) > 0)
    <section id="testimonials" class="relative py-28 overflow-hidden bg-[#0d1326] text-white border-t border-white/5">
        {{-- distinct from the stats section above: a single soft accent glow bottom-right, warm tone to match the star ratings --}}
        <div class="absolute -bottom-32 -right-32 w-[500px] h-[500px] bg-amber-500/10 blur-[130px] rounded-full pointer-events-none"></div>

        <div class="max-w-7xl mx-auto px-4 relative z-10">
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-16" data-aos="fade-up">
                <div>
                    <span class="eyebrow on-dark mb-5">Testimonials</span>
                    <h2 class="text-4xl md:text-5xl font-black font-display">
                        What Students & <span class="text-transparent bg-clip-text bg-gradient-to-r from-amber-300 to-orange-400">Parents Say</span>
                    </h2>
                </div>
                <div class="hidden md:flex gap-3">
                    <button id="testimonialPrev" class="w-11 h-11 rounded-full bg-white/10 hover:bg-white/20 flex items-center justify-center transition"><i class="fa-solid fa-arrow-left"></i></button>
                    <button id="testimonialNext" class="w-11 h-11 rounded-full bg-white/10 hover:bg-white/20 flex items-center justify-center transition"><i class="fa-solid fa-arrow-right"></i></button>
                </div>
            </div>

            <div id="testimonialRow" class="snap-row">
                @foreach ($data['testimonials'] as $testimonial)
                    <div class="w-[320px] sm:w-[380px]" data-aos="fade-up" data-aos-delay="{{ $loop->iteration * 80 }}">
                        <div class="h-full bg-white/[0.06] border border-white/10 hover:border-amber-400/30 backdrop-blur-xl rounded-[28px] p-8 transition-colors duration-500">
                            <div class="text-5xl text-amber-400 mb-5 font-display">&#10078;</div>
                            <p class="text-slate-300 leading-8 mb-7 italic">&ldquo;{{ Str::limit($testimonial->description, 160) }}&rdquo;</p>

                            <div class="flex gap-1 text-amber-400 mb-7">
                                @for ($i = 1; $i <= 5; $i++)
                                    <i class="fa-{{ $i <= $testimonial->ratting ? 'solid' : 'regular' }} fa-star text-sm"></i>
                                @endfor
                            </div>

                            <div class="flex items-center gap-4">
                                <img src="{{ $testimonial->thumbnail_image && file_exists(public_path('storage/testimonials/' . $testimonial->thumbnail_image))
                                    ? asset('storage/testimonials/' . $testimonial->thumbnail_image)
                                    : asset('images/user.png') }}"
                                    class="w-14 h-14 rounded-full object-cover border-2 border-white/20" alt="{{ $testimonial->name }}">
                                <div>
                                    <h4 class="font-bold font-display">{{ $testimonial->name }}</h4>
                                    <p class="text-slate-400 text-sm">{{ $testimonial->note }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const row = document.getElementById('testimonialRow');
            const scrollAmount = 400;
            document.getElementById('testimonialPrev')?.addEventListener('click', () => row.scrollBy({ left: -scrollAmount, behavior: 'smooth' }));
            document.getElementById('testimonialNext')?.addEventListener('click', () => row.scrollBy({ left: scrollAmount, behavior: 'smooth' }));
        });
    </script>
@endif

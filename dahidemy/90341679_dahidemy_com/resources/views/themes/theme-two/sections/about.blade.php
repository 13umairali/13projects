@if ($data['whyChooseUs']->count() > 0)
    <section class="py-24">
        <div class="max-w-7xl mx-auto px-4">
            <div class="text-center max-w-2xl mx-auto mb-16" data-aos="fade-up">
                <span class="eyebrow justify-center">Why Choose Us</span>
                <h2 class="font-display text-4xl mt-4">A better way to learn</h2>
            </div>

            <div class="grid md:grid-cols-4 gap-6">
                @foreach ($data['whyChooseUs'] as $item)
                    <div class="nl-card p-8" data-aos="fade-up" data-aos-delay="{{ $loop->iteration * 80 }}">
                        <div class="w-14 h-14 rounded-full overflow-hidden flex items-center justify-center mb-6"
                            style="background:color-mix(in srgb, var(--nl-primary) 12%, white)">
                            @if (!empty($item->icon))
                                <img src="{{ asset('storage/why_choose_us/' . $item->icon) }}"
                                    class="w-full h-full object-cover">
                            @else
                                <i class="fa fa-check text-brand"></i>
                            @endif
                        </div>
                        <h3 class="font-display text-xl mb-3">{{ $item->title }}</h3>
                        <p class="leading-7" style="color:var(--nl-muted)">{{ $item->description }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endif

@if ($data['about'])
    <section id="about" class="py-24 bg-white border-y" style="border-color:var(--nl-line)">
        <div class="max-w-7xl mx-auto px-4 grid lg:grid-cols-2 gap-16 items-center">

            <div data-aos="fade-right" class="relative">
                <div class="absolute -inset-4 border rounded-lg hidden md:block" style="border-color:var(--nl-line)">
                </div>
                <img src="{{ asset('storage/about_us/' . $data['about']->image) }}"
                    class="relative rounded-lg w-full object-cover aspect-[4/3]">
            </div>

            <div data-aos="fade-left">
                <span class="eyebrow">About Us</span>
                <h2 class="font-display text-4xl mt-4 mb-6">{{ $data['about']->title }}</h2>
                <div class="leading-8" style="color:var(--nl-muted)">
                    {!! $data['about']->description !!}
                </div>
            </div>
        </div>
    </section>
@endif

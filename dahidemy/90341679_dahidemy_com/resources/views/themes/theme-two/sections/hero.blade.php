<section class="border-b" style="border-color:var(--nl-line)">
    @if ($data['banners']->count())
        @php $banner = $data['banners']->first(); @endphp

        <div class="max-w-7xl mx-auto px-4 py-16 md:py-24 grid lg:grid-cols-2 gap-16 items-center">

            <div data-aos="fade-up">
                <span class="eyebrow">{{ $settings['school_name'] ?? 'Welcome' }}</span>

                <h1 class="font-display text-4xl md:text-6xl leading-tight mt-5 mb-6">
                    {{ $banner->title }}
                </h1>

                @if ($banner->description)
                    <p class="text-lg leading-8 mb-8" style="color:var(--nl-muted)">
                        {{ $banner->description }}
                    </p>
                @endif

                <div class="flex flex-wrap gap-4">
                    @if ($banner->button_name && $banner->button_link)
                        <a href="{{ $banner->button_link }}" class="btn-nl">
                            {{ $banner->button_name }}
                            <i class="fa fa-arrow-right"></i>
                        </a>
                    @endif

                    @if (($settings['admission_display_status'] ?? 'no') === 'yes')
                        <a href="{{ route('admission-form-page') }}" class="btn-nl-outline">
                            Apply for Admission
                        </a>
                    @endif
                </div>
            </div>

            <div data-aos="fade-left" class="relative">
                <div class="absolute -inset-4 border rounded-lg hidden md:block" style="border-color:var(--nl-line)">
                </div>
                <img src="{{ asset('storage/banners/' . $banner->image) }}"
                    class="relative w-full rounded-lg object-cover aspect-[4/3]">
            </div>
        </div>

        @if ($data['banners']->count() > 1)
            <div class="max-w-7xl mx-auto px-4 pb-10 flex gap-2">
                @foreach ($data['banners'] as $index => $b)
                    <span class="h-1 flex-1 rounded"
                        style="background:{{ $index === 0 ? 'var(--nl-primary)' : 'var(--nl-line)' }}"></span>
                @endforeach
            </div>
        @endif
    @else
        <div class="max-w-7xl mx-auto px-4 py-24 text-center">
            <span class="eyebrow justify-center">{{ $settings['school_name'] ?? 'Welcome' }}</span>
            <h1 class="font-display text-4xl md:text-6xl mt-5">
                {{ $settings['site_title'] ?? 'A place to learn, grow and lead' }}
            </h1>
        </div>
    @endif
</section>

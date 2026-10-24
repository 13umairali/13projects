<style>
    .hero-slider { height: 700px; overflow: hidden; }
    .hero-track { display: flex; height: 100%; transition: transform 1s cubic-bezier(.65,0,.35,1); }
    .hero-slide { min-width: 100%; height: 100%; position: relative; }
    .hero-slide img { width: 100%; height: 100%; object-fit: cover; transition: transform 8s ease; }
    .hero-slide.active img { transform: scale(1.08); }
    .hero-overlay {
        position: absolute; inset: 0;
        background: linear-gradient(100deg, rgba(16,26,46,.92) 10%, rgba(27,42,74,.6) 55%, rgba(16,26,46,.3));
    }
    @media (max-width:1024px) { .hero-slider { height: 560px; } }
    @media (max-width:640px) { .hero-slider { height: 520px; } }
</style>

<section id="home">
    @if ($data['banners']->count())
        <div x-data="{
                active: 0, total: {{ $data['banners']->count() }}, timer: null, pause: false,
                start() { this.timer = setInterval(() => { if (!this.pause) this.active = (this.active + 1) % this.total }, 5500) },
                next() { this.active = (this.active + 1) % this.total },
                prev() { this.active = (this.active - 1 + this.total) % this.total }
            }" x-init="start()" @mouseenter="pause=true" @mouseleave="pause=false"
            class="hero-slider relative bg-slate-950">

            <div class="hero-track" :style="`transform:translateX(-${active*100}%)`">
                @foreach ($data['banners'] as $index => $banner)
                    <div class="hero-slide" :class="active == {{ $index }} ? 'active' : ''">
                        <img src="{{ asset('storage/banners/' . $banner->image) }}" alt="{{ $banner->title }}">
                        <div class="hero-overlay"></div>

                        <div class="absolute inset-0 flex items-center">
                            <div class="px-5 md:px-10 max-w-7xl mx-auto w-full">
                                <div class="max-w-2xl text-white" data-aos="fade-up">
                                    @if (($settings['admission_display_status'] ?? null) == 'yes')
                                        <span class="eyebrow on-dark mb-6">
                                            <i class="fa-solid fa-graduation-cap"></i> Admissions Open
                                        </span>
                                    @endif

                                    <h1 class="text-4xl md:text-6xl font-display leading-[1.1]">
                                        {{ $banner->title }}
                                    </h1>

                                    @if ($banner->description)
                                        <p class="mt-5 text-slate-200 md:text-lg leading-8 max-w-xl">
                                            {{ $banner->description }}
                                        </p>
                                    @endif

                                    <div class="mt-8 flex flex-wrap gap-4">
                                        @if ($banner->button_name && $banner->button_link)
                                            <a href="{{ $banner->button_link }}" class="btn-cta">
                                                {{ $banner->button_name }} <i class="fa-solid fa-arrow-right"></i>
                                            </a>
                                        @endif
                                        <a href="{{ route('contact') }}" class="btn-outline">
                                            <i class="fa-solid fa-phone"></i> Talk To Us
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            @if ($data['banners']->count() > 1)
                <button @click="prev()" class="absolute left-4 top-1/2 -translate-y-1/2 bg-white/15 hover:bg-white/25 text-white rounded-full w-11 h-11 backdrop-blur transition">
                    <i class="fa-solid fa-chevron-left"></i>
                </button>
                <button @click="next()" class="absolute right-4 top-1/2 -translate-y-1/2 bg-white/15 hover:bg-white/25 text-white rounded-full w-11 h-11 backdrop-blur transition">
                    <i class="fa-solid fa-chevron-right"></i>
                </button>

                <div class="absolute bottom-6 left-1/2 -translate-x-1/2 flex gap-2">
                    <template x-for="i in total" :key="i">
                        <button @click="active = i - 1" class="h-2 rounded-full transition-all"
                            :class="active == i - 1 ? 'w-8 bg-white' : 'w-2 bg-white/40'"></button>
                    </template>
                </div>
            @endif
        </div>
    @else
        {{-- Graceful fallback so the homepage never renders blank before a banner is uploaded --}}
        <div class="relative py-32 text-white text-center overflow-hidden" style="background: var(--navy-900);">
            <div class="relative max-w-3xl mx-auto px-4" data-aos="fade-up">
                <span class="eyebrow on-dark mb-6"><i class="fa-solid fa-graduation-cap"></i> Welcome</span>
                <h1 class="text-4xl md:text-6xl font-display leading-tight">
                    {{ $settings['school_name'] ?? 'Learn Today, Lead Tomorrow' }}
                </h1>
                <p class="mt-5 text-slate-300 md:text-lg">
                    Empowering students with knowledge, values and skills to succeed in a changing world.
                </p>
                <a href="{{ route('contact') }}" class="btn-cta mt-8">Get In Touch <i class="fa-solid fa-arrow-right"></i></a>
            </div>
        </div>
    @endif

    {{-- QUICK INFO STRIP --}}
    <div class="relative z-10 -mt-8 md:-mt-10 max-w-6xl mx-auto px-4">
        <div class="card-elevated grid grid-cols-2 md:grid-cols-4 divide-x divide-[var(--line-200)] text-center overflow-hidden" style="border-radius: var(--radius-md);">
            @if (!empty($settings['phone']))
                <a href="tel:{{ $settings['phone'] }}" class="p-5 md:p-7 hover:bg-slate-50 transition">
                    <i class="fa-solid fa-phone text-brand mb-2"></i>
                    <p class="text-xs text-slate-400">Call Us</p>
                    <p class="font-semibold text-sm">{{ $settings['phone'] }}</p>
                </a>
            @endif
            @if (!empty($settings['email']))
                <a href="mailto:{{ $settings['email'] }}" class="p-5 md:p-7 hover:bg-slate-50 transition">
                    <i class="fa-solid fa-envelope text-brand mb-2"></i>
                    <p class="text-xs text-slate-400">Email</p>
                    <p class="font-semibold text-sm truncate">{{ $settings['email'] }}</p>
                </a>
            @endif
            @if (($settings['admission_display_status'] ?? null) == 'yes')
                <a href="{{ route('admission-form-page') }}" class="p-5 md:p-7 hover:bg-slate-50 transition">
                    <i class="fa-solid fa-graduation-cap text-brand mb-2"></i>
                    <p class="text-xs text-slate-400">Admission</p>
                    <p class="font-semibold text-sm">Apply Online</p>
                </a>
            @endif
            <a href="{{ route('academic-images') }}" class="p-5 md:p-7 hover:bg-slate-50 transition">
                <i class="fa-solid fa-images text-brand mb-2"></i>
                <p class="text-xs text-slate-400">Explore</p>
                <p class="font-semibold text-sm">Campus Gallery</p>
            </a>
        </div>
    </div>
</section>

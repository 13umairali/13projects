<footer id="contact" class="relative pt-2 pb-10 text-white" style="background: var(--surface-dark);">

    {{-- NEWSLETTER STRIP — a vivid gradient card, deliberately NOT dark glass like the
         sections above it, so it reads as its own moment rather than more of the same. --}}
    <div class="relative z-10 max-w-5xl mx-auto px-4 -mt-16 mb-20" data-aos="fade-up">
        <div class="p-8 md:p-12 flex flex-col md:flex-row items-center justify-between gap-8 border" style="background: var(--surface-dark-2); border-radius: var(--radius-md); border-color: var(--navy-700);">
            <div>
                <h3 class="text-2xl md:text-3xl font-display mb-2">Stay in the loop</h3>
                <p class="text-white/70">Get admission updates, event news and results straight to your inbox.</p>
            </div>
            <form class="flex w-full md:w-auto gap-3" onsubmit="return false;">
                <input type="email" required placeholder="Your email address"
                    class="flex-1 md:w-72 px-5 py-4 bg-white border border-white/20 placeholder-slate-400 text-slate-900 focus:outline-none focus:ring-2 focus:ring-[var(--brass-500)]" style="border-radius: var(--radius-sm);">
                <button type="submit" class="btn-cta whitespace-nowrap">Subscribe</button>
            </form>
        </div>
    </div>

    <div class="absolute top-0 inset-x-0 h-px bg-gradient-to-r from-transparent via-white/10 to-transparent"></div>

    <div class="relative grid gap-12 px-4 mx-auto max-w-7xl md:grid-cols-4">

        {{-- BRAND --}}
        <div data-aos="fade-up">
            @if (!empty($instituteImage?->header_logo_light_theme))
                <img src="{{ asset('storage/institute_image_settings/' . $instituteImage->header_logo_light_theme) }}"
                    alt="" class="h-12 w-auto max-w-[190px] object-contain mb-5">
            @else
                <img src="{{ asset('assets/media/logos/default.png') }}" alt="" class="h-12 w-auto max-w-[190px] object-contain mb-5">
            @endif

            <h3 class="mb-3 text-2xl font-bold font-display">{{ $settings['school_name'] ?? 'Institute Pro' }}</h3>

            <p class="mb-6 leading-7 text-slate-400">
                Modern SaaS-based institute management system for schools, colleges, and training centers.
            </p>

            <div class="flex gap-3 text-lg">
                @if (!empty($settings['facebook_link']))
                    <a href="{{ $settings['facebook_link'] }}" class="w-10 h-10 flex items-center justify-center hover:bg-[var(--brass-500)] transition" style="background:rgba(255,255,255,.08); border-radius: var(--radius-sm);"><i class="fab fa-facebook-f"></i></a>
                @endif
                @if (!empty($settings['youtube_link']))
                    <a href="{{ $settings['youtube_link'] }}" class="w-10 h-10 flex items-center justify-center hover:bg-[var(--brass-500)] transition" style="background:rgba(255,255,255,.08); border-radius: var(--radius-sm);"><i class="fab fa-youtube"></i></a>
                @endif
                @if (!empty($settings['whats_app_link']))
                    <a href="{{ $settings['whats_app_link'] }}" class="w-10 h-10 flex items-center justify-center hover:bg-[var(--brass-500)] transition" style="background:rgba(255,255,255,.08); border-radius: var(--radius-sm);"><i class="fab fa-whatsapp"></i></a>
                @endif
                @if (!empty($settings['linkedin_link']))
                    <a href="{{ $settings['linkedin_link'] }}" class="w-10 h-10 flex items-center justify-center hover:bg-[var(--brass-500)] transition" style="background:rgba(255,255,255,.08); border-radius: var(--radius-sm);"><i class="fab fa-linkedin-in"></i></a>
                @endif
            </div>
        </div>

        {{-- CONTACT --}}
        <div data-aos="fade-up" data-aos-delay="100">
            <h3 class="flex items-center gap-2 mb-5 text-lg font-bold font-display">
                <span class="w-8 h-8 rounded-lg bg-white/10 flex items-center justify-center text-sm"><i class="fas fa-address-book"></i></span>
                Contact Info
            </h3>
            <div class="space-y-4 text-sm text-slate-300">
                @if (!empty($settings['phone']))
                    <div class="flex items-center gap-3"><span class="w-8 h-8 rounded-lg bg-white/10 flex items-center justify-center text-xs"><i class="fas fa-phone-alt"></i></span><p>{{ $settings['phone'] }}</p></div>
                @endif
                @if (!empty($settings['email']))
                    <div class="flex items-center gap-3"><span class="w-8 h-8 rounded-lg bg-white/10 flex items-center justify-center text-xs"><i class="fas fa-envelope"></i></span><p>{{ $settings['email'] }}</p></div>
                @endif
                @if (!empty($settings['address']))
                    <div class="flex items-start gap-3"><span class="w-8 h-8 rounded-lg bg-white/10 flex items-center justify-center text-xs mt-0.5"><i class="fas fa-map-marker-alt"></i></span><p>{{ $settings['address'] }}</p></div>
                @endif
            </div>
        </div>

        {{-- QUICK LINKS --}}
        <div data-aos="fade-up" data-aos-delay="200">
            <h3 class="mb-5 text-lg font-bold font-display">Quick Links</h3>
            <ul class="space-y-3 text-slate-400">
                <li><a href="#home" class="hover:text-white transition">Home</a></li>
                @if (($settings['admission_display_status'] ?? null) == 'yes')
                    <li><a href="{{ route('admission-form-page') }}" class="hover:text-white transition">Admission</a></li>
                @endif
                @if (($settings['exam_result_status'] ?? null) == 'yes')
                    <li><a href="{{ route('result-page') }}" class="hover:text-white transition">Exam Results</a></li>
                @endif
                <li><a href="{{ route('academic-images') }}" class="hover:text-white transition">Galleries</a></li>
                <li><a href="{{ route('contact') }}" class="hover:text-white transition">Contact Us</a></li>
            </ul>
        </div>

        {{-- LEGAL + APP --}}
        <div data-aos="fade-up" data-aos-delay="300">
            <h3 class="mb-5 text-lg font-bold font-display">Legal</h3>
            <ul class="space-y-3 text-slate-400 mb-8">
                <li><a href="{{ route('privacy-policy') }}" class="hover:text-white transition">Privacy Policy</a></li>
                <li><a href="{{ route('terms-conditions') }}" class="hover:text-white transition">Terms & Conditions</a></li>
                <li><a href="{{ route('refund-policy') }}" class="hover:text-white transition">Refund Policy</a></li>
                <li><a href="{{ route('cookies-policy') }}" class="hover:text-white transition">Cookies Policy</a></li>
            </ul>

            @if (!empty($settings['play_store_link']) || !empty($settings['app_store_link']))
                <h4 class="flex items-center gap-2 mb-3 font-semibold text-white text-sm">
                    <span class="w-7 h-7 rounded-lg bg-white/10 flex items-center justify-center text-xs"><i class="fas fa-mobile-alt"></i></span>
                    Get the App
                </h4>
                <div class="space-y-3">
                    @if (!empty($settings['play_store_link']))
                        <a href="{{ $settings['play_store_link'] }}" class="flex items-center gap-3 p-3 text-sm rounded-xl bg-white/10 hover:bg-white/20 transition">
                            <span class="w-9 h-9 rounded-lg bg-green-500/20 text-green-400 flex items-center justify-center"><i class="fab fa-google-play"></i></span>
                            <span><span class="block text-[11px] text-slate-400">Get it on</span><span class="block font-semibold">Google Play</span></span>
                        </a>
                    @endif
                    @if (!empty($settings['app_store_link']))
                        <a href="{{ $settings['app_store_link'] }}" class="flex items-center gap-3 p-3 text-sm rounded-xl bg-white/10 hover:bg-white/20 transition">
                            <span class="w-9 h-9 rounded-lg bg-white/20 text-white flex items-center justify-center"><i class="fab fa-apple"></i></span>
                            <span><span class="block text-[11px] text-slate-400">Download on the</span><span class="block font-semibold">App Store</span></span>
                        </a>
                    @endif
                </div>
            @endif
        </div>
    </div>

    <div class="relative flex flex-col md:flex-row items-center justify-between gap-3 px-4 mx-auto mt-16 pt-6 border-t max-w-7xl border-white/10 text-sm text-slate-500">
        <p>{!! $settings['copyright_text'] ?? '&copy; ' . date('Y') . ' All Rights Reserved.' !!}</p>
        <p class="flex items-center gap-2"><i class="fa-solid fa-shield-halved text-brand"></i> Secure & verified institute</p>
    </div>
</footer>

{{-- FLOATING ACTIONS --}}
<div class="float-stack">
    @if (!empty($settings['whats_app_link']))
        <a href="{{ $settings['whats_app_link'] }}" target="_blank" class="float-btn bg-green-500">
            <i class="fab fa-whatsapp"></i>
        </a>
    @endif
    <button id="backToTop" class="float-btn bg-brand opacity-0 pointer-events-none">
        <i class="fa-solid fa-arrow-up"></i>
    </button>
</div>
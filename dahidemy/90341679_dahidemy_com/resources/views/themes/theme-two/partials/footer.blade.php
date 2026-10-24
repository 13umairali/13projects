<footer id="contact" class="bg-ink text-white pt-20 pb-8">
    <div class="max-w-7xl mx-auto px-4 grid gap-12 md:grid-cols-4">

        {{-- BRAND --}}
        <div>
            @if (!empty($instituteImage?->footer_logo_dark_theme))
                <img src="{{ asset('storage/institute_image_settings/' . $instituteImage->footer_logo_dark_theme) }}"
                    class="h-10 w-auto object-contain mb-5 bg-white/5 p-1 rounded">
            @endif

            <h3 class="font-display text-2xl mb-4">{{ $settings['school_name'] ?? 'Institute' }}</h3>

            <p class="text-white/60 leading-7 mb-6">
                A modern institute management platform for schools, colleges, and training centers.
            </p>

            <div class="flex gap-4 text-lg">
                @if (!empty($settings['facebook_link']))
                    <a href="{{ $settings['facebook_link'] }}" class="text-white/60 hover:text-white transition"><i
                            class="fab fa-facebook-f"></i></a>
                @endif
                @if (!empty($settings['youtube_link']))
                    <a href="{{ $settings['youtube_link'] }}" class="text-white/60 hover:text-white transition"><i
                            class="fab fa-youtube"></i></a>
                @endif
                @if (!empty($settings['whats_app_link']))
                    <a href="{{ $settings['whats_app_link'] }}" class="text-white/60 hover:text-white transition"><i
                            class="fab fa-whatsapp"></i></a>
                @endif
                @if (!empty($settings['twitter_link']))
                    <a href="{{ $settings['twitter_link'] }}" class="text-white/60 hover:text-white transition"><i
                            class="fab fa-twitter"></i></a>
                @endif
            </div>
        </div>

        {{-- QUICK LINKS --}}
        <div>
            <h4 class="uppercase text-sm tracking-wide text-white/50 mb-5">Explore</h4>
            <ul class="space-y-3 text-white/80">
                <li><a href="{{ url('/') }}#about" class="hover:text-white transition">About Us</a></li>
                <li><a href="{{ url('/') }}#faculty" class="hover:text-white transition">Our Faculty</a></li>
                <li><a href="{{ url('/') }}#events" class="hover:text-white transition">Events</a></li>
                <li><a href="{{ route('contact') }}" class="hover:text-white transition">Contact</a></li>
            </ul>
        </div>

        {{-- LEGAL --}}
        <div>
            <h4 class="uppercase text-sm tracking-wide text-white/50 mb-5">Legal</h4>
            <ul class="space-y-3 text-white/80">
                <li><a href="{{ route('privacy-policy') }}" class="hover:text-white transition">Privacy Policy</a>
                </li>
                <li><a href="{{ route('terms-conditions') }}" class="hover:text-white transition">Terms &amp;
                        Conditions</a></li>
                <li><a href="{{ route('refund-policy') }}" class="hover:text-white transition">Refund Policy</a></li>
                <li><a href="{{ route('cookies-policy') }}" class="hover:text-white transition">Cookie Policy</a>
                </li>
            </ul>
        </div>

        {{-- CONTACT --}}
        <div>
            <h4 class="uppercase text-sm tracking-wide text-white/50 mb-5">Contact</h4>
            <ul class="space-y-3 text-white/80">
                @if (!empty($settings['address']))
                    <li class="flex gap-3"><i class="fa fa-location-dot mt-1"></i> {{ $settings['address'] }}</li>
                @endif
                @if (!empty($settings['phone']))
                    <li class="flex gap-3"><i class="fa fa-phone mt-1"></i> {{ $settings['phone'] }}</li>
                @endif
                @if (!empty($settings['email']))
                    <li class="flex gap-3"><i class="fa fa-envelope mt-1"></i> {{ $settings['email'] }}</li>
                @endif
            </ul>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 mt-16 pt-6 border-t border-white/10 text-white/50 text-sm">
        {!! $settings['copyright_text'] ?? '&copy; Copyright ' . date('Y') !!}
    </div>
</footer>

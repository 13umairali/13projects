<header class="sticky top-0 z-50 bg-white/95 backdrop-blur border-b" style="border-color:var(--nl-line)">
    <div class="max-w-7xl mx-auto px-4">
        <div class="flex items-center justify-between h-20">

            {{-- LOGO --}}
            <a href="{{ url('/') }}" class="flex items-center gap-3">
                @if (!empty($instituteImage?->header_logo_light_theme))
                    <img src="{{ asset('storage/institute_image_settings/' . $instituteImage->header_logo_light_theme) }}"
                        class="h-10 w-auto object-contain">
                @else
                    <img src="{{ asset('assets/media/logos/default.png') }}" class="h-10 w-auto object-contain">
                @endif
                <span class="font-display text-xl font-semibold hidden sm:block">
                    {{ $settings['school_name'] ?? 'Institute' }}
                </span>
            </a>

            {{-- DESKTOP NAV --}}
            <nav class="hidden lg:flex items-center gap-10">
                <a href="{{ url('/') }}" class="nl-nav-link">Home</a>
                <a href="#about" class="nl-nav-link">About</a>
                <a href="#faculty" class="nl-nav-link">Faculty</a>
                <a href="#events" class="nl-nav-link">Events</a>
                @if (($settings['admission_display_status'] ?? 'no') === 'yes')
                    <a href="{{ route('admission-form-page') }}" class="nl-nav-link">Admission</a>
                @endif
                <a href="{{ route('contact') }}" class="nl-nav-link">Contact</a>
            </nav>

            <div class="hidden lg:flex items-center gap-6">
                @if (!empty($settings['phone']))
                    <a href="tel:{{ $settings['phone'] }}" class="text-sm text-right">
                        <span class="block text-xs uppercase tracking-wide" style="color:var(--nl-muted)">Call
                            Us</span>
                        <span class="font-semibold">{{ $settings['phone'] }}</span>
                    </a>
                @endif
                <a href="{{ url('/login') }}" class="btn-nl">Login</a>
            </div>

            {{-- MOBILE TOGGLE --}}
            <button onclick="document.getElementById('nlMobileNav').classList.toggle('hidden')"
                class="lg:hidden w-10 h-10 flex items-center justify-center border rounded"
                style="border-color:var(--nl-line)">
                <i class="fa fa-bars"></i>
            </button>
        </div>

        {{-- MOBILE NAV --}}
        <nav id="nlMobileNav" class="hidden lg:hidden pb-6 flex flex-col gap-4">
            <a href="{{ url('/') }}" class="nl-nav-link">Home</a>
            <a href="#about" class="nl-nav-link">About</a>
            <a href="#faculty" class="nl-nav-link">Faculty</a>
            <a href="#events" class="nl-nav-link">Events</a>
            @if (($settings['admission_display_status'] ?? 'no') === 'yes')
                <a href="{{ route('admission-form-page') }}" class="nl-nav-link">Admission</a>
            @endif
            <a href="{{ route('contact') }}" class="nl-nav-link">Contact</a>
            <a href="{{ url('/login') }}" class="btn-nl w-fit">Login</a>
        </nav>
    </div>
</header>

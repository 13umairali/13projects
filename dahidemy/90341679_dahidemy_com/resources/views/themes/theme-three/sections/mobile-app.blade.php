{{-- =========================================================
    NEW SECTION — MobileAppSection content is manageable from
    the admin panel but was never rendered by the old theme.
========================================================= --}}
@if ($data['mobileAppSections'] && count($data['mobileAppSections']) > 0)
    @foreach ($data['mobileAppSections'] as $app)
        <section class="py-24 bg-slate-50 overflow-hidden">
            <div class="max-w-7xl mx-auto px-4 grid lg:grid-cols-2 gap-16 items-center">
                <div class="order-2 lg:order-1" data-aos="fade-right">
                    @if (!empty($app->image))
                        <img src="{{ asset('storage/mobile_app_sections/' . $app->image) }}" class="w-full max-w-md mx-auto drop-shadow-2xl" alt="{{ $app->title }}">
                    @endif
                </div>

                <div class="order-1 lg:order-2" data-aos="fade-left">
                    <span class="eyebrow mb-4">{{ $app->title ?? 'Mobile App' }}</span>
                    <h2 class="section-title mb-6">{{ $app->heading ?? 'Take Your School Everywhere' }}</h2>
                    <p class="text-slate-600 leading-8 mb-8">{{ $app->description }}</p>

                    <div class="grid sm:grid-cols-1 gap-3 mb-9">
                        @foreach ([$app->feature_one, $app->feature_two, $app->feature_three] as $feature)
                            @if (!empty($feature))
                                <div class="flex items-center gap-3">
                                    <span class="w-7 h-7 rounded-full bg-brand-50 text-brand flex items-center justify-center text-xs shrink-0">
                                        <i class="fa-solid fa-check"></i>
                                    </span>
                                    <span class="text-slate-700 text-sm">{{ $feature }}</span>
                                </div>
                            @endif
                        @endforeach
                    </div>

                    <div class="flex flex-wrap gap-4">
                        @if (!empty($app->play_store_link))
                            <a href="{{ $app->play_store_link }}" class="flex items-center gap-3 px-5 py-3 rounded-2xl bg-slate-900 text-white hover:-translate-y-1 transition">
                                <i class="fab fa-google-play text-xl"></i>
                                <span class="text-left leading-tight"><span class="block text-[10px] text-slate-300">Get it on</span><span class="block font-semibold text-sm">Google Play</span></span>
                            </a>
                        @endif
                        @if (!empty($app->app_store_link))
                            <a href="{{ $app->app_store_link }}" class="flex items-center gap-3 px-5 py-3 rounded-2xl bg-slate-900 text-white hover:-translate-y-1 transition">
                                <i class="fab fa-apple text-xl"></i>
                                <span class="text-left leading-tight"><span class="block text-[10px] text-slate-300">Download on the</span><span class="block font-semibold text-sm">App Store</span></span>
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </section>
    @endforeach
@endif

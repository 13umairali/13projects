@if ($data['events'] && count($data['events']) > 0)
    <section id="events" class="relative py-28 bg-white overflow-hidden">
        <div class="max-w-7xl mx-auto px-4">
            <div class="text-center mb-16" data-aos="fade-up">
                <span class="eyebrow mb-4">Upcoming Activities</span>
                <h2 class="section-title">School Events & Programs</h2>
            </div>

            <div class="grid md:grid-cols-2 xl:grid-cols-3 gap-8">
                @foreach ($data['events'] as $event)
                    <div class="group relative" data-aos="fade-up" data-aos-delay="{{ $loop->iteration * 100 }}">
                        <div class="card-elevated overflow-hidden">
                            <div class="overflow-hidden relative">
                                <img src="{{ asset('storage/events/' . $event->image) }}" class="w-full h-60 object-cover group-hover:scale-110 transition duration-700" alt="{{ $event->name }}">
                                <div class="absolute top-4 left-4 bg-white text-slate-900 px-4 py-3 rounded-xl shadow-lg text-center leading-none">
                                    <span class="block text-2xl font-black font-display">{{ \Carbon\Carbon::parse($event->start_date)->format('d') }}</span>
                                    <span class="block text-[11px] font-semibold uppercase mt-1">{{ \Carbon\Carbon::parse($event->start_date)->format('M') }}</span>
                                </div>
                                @if (isset($event->is_paid))
                                    @if ($event->is_paid)
                                        <span class="absolute top-4 right-4 bg-slate-900/80 text-white text-xs font-semibold px-3 py-1.5 rounded-full">
                                            {{ number_format((float) $event->price, 2) }} {{ strtoupper((string) $event->currency_code) }}
                                        </span>
                                    @else
                                        <span class="absolute top-4 right-4 bg-emerald-500 text-white text-xs font-semibold px-3 py-1.5 rounded-full">Free</span>
                                    @endif
                                @endif
                            </div>
                            <div class="p-7">
                                <p class="text-xs text-slate-400 mb-3">
                                    <i class="fa fa-location-dot text-brand"></i> {{ $event->location }}
                                </p>
                                <h3 class="text-xl font-bold mb-3 font-display">{{ $event->name }}</h3>
                                <p class="text-slate-500 text-sm leading-7 mb-6">{{ Str::limit(strip_tags($event->details), 110) }}</p>
                                <a href="{{ Route::has('eventenrollment.show') ? route('eventenrollment.show', $event->id) : route('event-details', $event->id) }}" class="inline-flex items-center gap-2 text-brand font-semibold text-sm">
                                    Explore Event <i class="fa fa-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endif

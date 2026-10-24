@if ($data['events'] && count($data['events']) > 0)
    <section id="events" class="py-24">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex items-end justify-between mb-16" data-aos="fade-up">
                <div>
                    <span class="eyebrow">Upcoming</span>
                    <h2 class="font-display text-4xl mt-4">Events & programs</h2>
                </div>
            </div>

            <div class="grid md:grid-cols-2 xl:grid-cols-3 gap-6">
                @foreach ($data['events'] as $event)
                    <a href="{{ route('event-details', $event->id) }}" class="nl-card overflow-hidden block"
                        data-aos="fade-up" data-aos-delay="{{ $loop->iteration * 80 }}">
                        <div class="relative">
                            <img src="{{ asset('storage/events/' . $event->image) }}"
                                class="w-full h-48 object-cover">
                            <div class="absolute top-4 left-4 bg-white px-3 py-2 rounded text-center shadow">
                                <div class="font-display text-xl leading-none">
                                    {{ \Carbon\Carbon::parse($event->start_date)->format('d') }}
                                </div>
                                <div class="text-xs uppercase" style="color:var(--nl-muted)">
                                    {{ \Carbon\Carbon::parse($event->start_date)->format('M') }}
                                </div>
                            </div>
                        </div>
                        <div class="p-6">
                            <p class="text-xs uppercase tracking-wide mb-2" style="color:var(--nl-muted)">
                                <i class="fa fa-location-dot text-brand"></i> {{ $event->location }}
                            </p>
                            <h3 class="font-display text-xl mb-3">{{ $event->name }}</h3>
                            <p class="leading-7 text-sm" style="color:var(--nl-muted)">
                                {{ Str::limit(strip_tags($event->details), 100) }}
                            </p>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    </section>
@endif

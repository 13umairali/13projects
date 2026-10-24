@extends('themes.' . config('frontend.active_theme', 'theme-three') . '.layouts.app')

@section('title', 'Events')

@section('content')
    <section class="py-20 bg-white">
        <div class="max-w-6xl mx-auto px-4">
            <div class="text-center mb-10">
                <span class="eyebrow mb-5">Events</span>
                <h1 class="section-title">{{ $settings['school_name'] ?? $institute->name ?? 'Our' }} Events</h1>
            </div>

            {{-- TABS --}}
            <div class="flex justify-center gap-2 mb-12 flex-wrap">
                @foreach (['upcoming' => 'Upcoming', 'ongoing' => 'Happening Now', 'past' => 'Past'] as $key => $label)
                    <a href="{{ route('eventenrollment.index', ['tab' => $key]) }}"
                        class="px-5 py-2.5 rounded-full text-sm font-semibold transition {{ $tab === $key ? 'bg-brand text-white shadow-lg' : 'bg-white text-slate-600 border border-slate-200 hover:border-brand' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </div>

            @if ($eventCards->isEmpty())
                <div class="text-center py-20 text-slate-400">
                    <i class="fa-solid fa-calendar-xmark text-4xl mb-4"></i>
                    <p>No {{ $tab }} events right now — check back soon.</p>
                </div>
            @else
                <div class="grid md:grid-cols-2 xl:grid-cols-3 gap-8">
                    @foreach ($eventCards as $card)
                        @php $event = $card['event']; @endphp
                        <a href="{{ route('eventenrollment.show', $event->id) }}" class="card-elevated group overflow-hidden">
                            <div class="relative overflow-hidden h-52">
                                @if ($event->image)
                                    <img src="{{ asset('storage/events/' . $event->image) }}" class="w-full h-full object-cover group-hover:scale-110 transition duration-700" alt="{{ $event->name }}">
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-white text-3xl" style="background: linear-gradient(135deg, var(--brand-600), var(--accent-500));">
                                        <i class="fa-solid fa-calendar-days"></i>
                                    </div>
                                @endif

                                <div class="absolute top-4 left-4 bg-white text-slate-900 px-4 py-2 rounded-xl shadow text-center leading-none">
                                    <span class="block text-lg font-black font-display">{{ \Carbon\Carbon::parse($event->start_date)->format('d') }}</span>
                                    <span class="block text-[10px] font-semibold uppercase">{{ \Carbon\Carbon::parse($event->start_date)->format('M') }}</span>
                                </div>

                                @if ($event->is_paid)
                                    <span class="absolute top-4 right-4 bg-slate-900/80 text-white text-xs font-semibold px-3 py-1.5 rounded-full">
                                        {{ number_format((float) $event->price, 2) }} {{ strtoupper((string) $event->currency_code) }}
                                    </span>
                                @else
                                    <span class="absolute top-4 right-4 bg-emerald-500 text-white text-xs font-semibold px-3 py-1.5 rounded-full">Free</span>
                                @endif
                            </div>

                            <div class="p-6">
                                <p class="text-xs text-slate-400 mb-2"><i class="fa fa-location-dot text-brand"></i> {{ $event->location }}</p>
                                <h3 class="text-lg font-bold mb-3 font-display line-clamp-2">{{ $event->name }}</h3>

                                @if ($card['enrollmentOpen'])
                                    <span class="inline-flex items-center gap-2 text-brand font-semibold text-sm">
                                        View & Enroll <i class="fa-solid fa-arrow-right group-hover:translate-x-1 transition-transform"></i>
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-2 text-slate-400 font-semibold text-sm">
                                        <i class="fa-solid fa-lock"></i> Enrollment Closed
                                    </span>
                                @endif

                                @if ($card['seatsRemaining'] !== null)
                                    <p class="text-xs text-slate-400 mt-2">{{ $card['seatsRemaining'] }} seats left</p>
                                @endif
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </section>
@endsection
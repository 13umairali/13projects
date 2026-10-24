@extends('themes.' . config('frontend.active_theme', 'theme-three') . '.layouts.app')

@section('title', $data->name ?? 'Event')

@section('content')
    <section class="py-24" style="background: var(--paper-50);">
        <div class="max-w-4xl mx-auto px-4">
            @if (! $data)
                <div class="text-center py-20" style="color: var(--slate-500);">
                    <p>This event could not be found.</p>
                </div>
            @else
                @if ($data->image)
                    <img src="{{ asset('storage/events/' . $data->image) }}" class="w-full h-72 md:h-96 object-cover mb-10" style="border-radius: var(--radius-md);" alt="{{ $data->name }}">
                @endif

                <span class="eyebrow mb-4">Event</span>
                <h1 class="section-title mb-6">{{ $data->name }}</h1>

                <div class="flex flex-wrap gap-6 mb-10 text-sm" style="color: var(--slate-500);">
                    <span><i class="fa-regular fa-calendar"></i> {{ \Carbon\Carbon::parse($data->start_date)->format('d M Y, h:i A') }} — {{ \Carbon\Carbon::parse($data->end_date)->format('d M Y, h:i A') }}</span>
                    @if ($data->location)
                        <span><i class="fa-solid fa-location-dot"></i> {{ $data->location }}</span>
                    @endif
                </div>

                <div class="prose max-w-none leading-8" style="color: var(--ink-700);">
                    {!! $data->details !!}
                </div>
            @endif
        </div>
    </section>
@endsection

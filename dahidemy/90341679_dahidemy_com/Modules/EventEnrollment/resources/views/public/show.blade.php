@extends('themes.' . config('frontend.active_theme', 'theme-three') . '.layouts.app')

@section('title', $event->name)

@section('content')
    <section class="py-20 bg-white">
        <div class="max-w-5xl mx-auto px-4">
            <a href="{{ route('eventenrollment.index') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-slate-500 hover:text-brand mb-8">
                <i class="fa-solid fa-arrow-left"></i> All Events
            </a>

            <div class="grid lg:grid-cols-5 gap-10">

                {{-- LEFT: details --}}
                <div class="lg:col-span-3">
                    <div class="rounded-[28px] overflow-hidden h-64 mb-8">
                        @if ($event->image)
                            <img src="{{ asset('storage/events/' . $event->image) }}" class="w-full h-full object-cover" alt="{{ $event->name }}">
                        @else
                            <div class="w-full h-full flex items-center justify-center text-white text-5xl" style="background: linear-gradient(135deg, var(--brand-600), var(--accent-500));">
                                <i class="fa-solid fa-calendar-days"></i>
                            </div>
                        @endif
                    </div>

                    <span class="eyebrow mb-4 {{ $status === 'ongoing' ? '!text-emerald-600 !bg-emerald-50 !border-emerald-100' : ($status === 'past' ? '!text-slate-500 !bg-slate-100 !border-slate-200' : '') }}">
                        {{ $status === 'ongoing' ? 'Happening Now' : ucfirst($status) }}
                    </span>

                    <h1 class="section-title mb-5">{{ $event->name }}</h1>

                    <div class="flex flex-wrap gap-6 mb-8 text-sm text-slate-600">
                        <div class="flex items-center gap-2">
                            <i class="fa-solid fa-calendar text-brand"></i>
                            {{ \Carbon\Carbon::parse($event->start_date)->format('d M Y, h:i A') }}
                        </div>
                        <div class="flex items-center gap-2">
                            <i class="fa-solid fa-location-dot text-brand"></i>
                            {{ $event->location }}
                        </div>
                        @if ($seatsRemaining !== null)
                            <div class="flex items-center gap-2">
                                <i class="fa-solid fa-chair text-brand"></i>
                                {{ $seatsRemaining }} seats left
                            </div>
                        @endif
                    </div>

                    <div class="prose max-w-none text-slate-600 leading-8">
                        {!! $event->details !!}
                    </div>
                </div>

                {{-- RIGHT: enroll card --}}
                <div class="lg:col-span-2">
                    <div class="sticky top-24 card-elevated p-8">
                        <div class="flex items-center justify-between mb-6">
                            <span class="text-sm text-slate-400">Registration</span>
                            @if ($event->is_paid)
                                <span class="text-2xl font-black font-display text-brand">
                                    {{ number_format((float) $event->price, 2) }} {{ strtoupper((string) $event->currency_code) }}
                                </span>
                            @else
                                <span class="text-2xl font-black font-display text-emerald-600">Free</span>
                            @endif
                        </div>

                        @if (session('error'))
                            <div class="mb-5 px-4 py-3 rounded-xl border border-red-200 bg-red-50 text-red-700 text-sm">
                                {{ session('error') }}
                            </div>
                        @endif

                        @if ($errors->any())
                            <div class="mb-5 px-4 py-3 rounded-xl border border-red-200 bg-red-50 text-red-700 text-sm">
                                <ul class="list-disc pl-4">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        @if (! $enrollmentOpen)
                            <div class="text-center py-8 text-slate-400">
                                <i class="fa-solid fa-lock text-3xl mb-3"></i>
                                <p class="text-sm">Enrollment for this event is currently closed.</p>
                            </div>
                        @else
                            <form method="POST" action="{{ route('eventenrollment.enroll', $event->id) }}" class="space-y-4">
                                @csrf

                                <div>
                                    <label class="block mb-1.5 text-sm font-semibold text-slate-700">Full Name</label>
                                    <input type="text" name="name" required value="{{ old('name') }}"
                                        class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:border-brand focus:ring-4 focus:ring-brand-100 outline-none transition">
                                </div>

                                <div>
                                    <label class="block mb-1.5 text-sm font-semibold text-slate-700">Email</label>
                                    <input type="email" name="email" value="{{ old('email') }}"
                                        class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:border-brand focus:ring-4 focus:ring-brand-100 outline-none transition">
                                </div>

                                <div>
                                    <label class="block mb-1.5 text-sm font-semibold text-slate-700">Phone</label>
                                    <input type="tel" name="phone" value="{{ old('phone') }}"
                                        class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:border-brand focus:ring-4 focus:ring-brand-100 outline-none transition">
                                </div>

                                <p class="text-xs text-slate-400">Please provide at least an email or a phone number so we can confirm your spot.</p>

                                <button type="submit" class="btn-cta w-full justify-center py-4">
                                    {{ $event->is_paid ? 'Continue to Payment' : 'Reserve My Free Spot' }} <i class="fa-solid fa-arrow-right"></i>
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

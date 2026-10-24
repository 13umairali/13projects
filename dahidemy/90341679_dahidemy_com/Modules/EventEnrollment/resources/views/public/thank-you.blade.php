@extends('themes.' . config('frontend.active_theme', 'theme-three') . '.layouts.app')

@section('title', 'Registration ' . ($enrollment->isConfirmed() ? 'Confirmed' : 'Update'))

@section('content')
    <section class="py-20 bg-white">
        <div class="max-w-2xl mx-auto px-4">
            <div class="card-elevated p-10 text-center">

                @if ($enrollment->isConfirmed())
                    <div class="w-20 h-20 rounded-full bg-emerald-50 text-emerald-500 flex items-center justify-center text-4xl mx-auto mb-6">
                        <i class="fa-solid fa-circle-check"></i>
                    </div>
                    <h1 class="section-title mb-3">You're all set, {{ $enrollment->name }}!</h1>
                    <p class="text-slate-500 mb-8">You're registered for <strong>{{ $event->name }}</strong>.</p>
                @elseif ($enrollment->payment_status == \Modules\EventEnrollment\Models\EventEnrollment::STATUS_FAILED)
                    <div class="w-20 h-20 rounded-full bg-red-50 text-red-500 flex items-center justify-center text-4xl mx-auto mb-6">
                        <i class="fa-solid fa-circle-xmark"></i>
                    </div>
                    <h1 class="section-title mb-3">Payment didn't go through</h1>
                    <p class="text-slate-500 mb-8">Your registration for <strong>{{ $event->name }}</strong> wasn't completed. No amount was charged — please try again.</p>
                    <a href="{{ route('eventenrollment.show', $event->id) }}" class="btn-cta">
                        Try Again <i class="fa-solid fa-arrow-right"></i>
                    </a>
                @else
                    <div class="w-20 h-20 rounded-full bg-amber-50 text-amber-500 flex items-center justify-center text-4xl mx-auto mb-6">
                        <i class="fa-solid fa-hourglass-half"></i>
                    </div>
                    <h1 class="section-title mb-3">Payment still processing</h1>
                    <p class="text-slate-500 mb-8">We're confirming your payment for <strong>{{ $event->name }}</strong>. This page will update automatically — refresh in a moment, or check your email.</p>
                @endif

                @if ($enrollment->isConfirmed())
                    <div class="bg-slate-50 rounded-2xl p-6 text-left space-y-3 mb-8">
                        <div class="flex justify-between text-sm">
                            <span class="text-slate-400">Ticket Code</span>
                            <span class="font-bold font-display">{{ $enrollment->ticket_code }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-slate-400">Event</span>
                            <span class="font-semibold">{{ $event->name }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-slate-400">Date</span>
                            <span class="font-semibold">{{ \Carbon\Carbon::parse($event->start_date)->format('d M Y, h:i A') }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-slate-400">Location</span>
                            <span class="font-semibold">{{ $event->location }}</span>
                        </div>
                        @if ($event->is_paid)
                            <div class="flex justify-between text-sm">
                                <span class="text-slate-400">Amount Paid</span>
                                <span class="font-semibold">{{ number_format((float) $enrollment->amount_paid, 2) }} {{ strtoupper((string) $enrollment->currency_code) }}</span>
                            </div>
                        @endif
                    </div>

                    <p class="text-xs text-slate-400">A confirmation has also been sent to your email/phone, if provided.</p>
                @endif
            </div>
        </div>
    </section>
@endsection

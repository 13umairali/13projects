@extends('themes.' . config('frontend.active_theme', 'theme-three') . '.layouts.app')

@section('title', 'Enrollment ' . ($enrollment && $enrollment->status === 'active' ? 'Confirmed' : 'Update'))

@section('content')
    <section class="py-20 bg-white">
        <div class="max-w-2xl mx-auto px-4">
            <div class="card-elevated p-10 text-center">
                @if ($enrollment && $enrollment->status === 'active')
                    <div class="w-20 h-20 rounded-full bg-emerald-50 text-emerald-500 flex items-center justify-center text-4xl mx-auto mb-6">
                        <i class="fa-solid fa-circle-check"></i>
                    </div>
                    <h1 class="section-title mb-3">You're enrolled!</h1>
                    <p class="text-slate-500 mb-8">Welcome to <strong>{{ $course->title }}</strong>. Check your email for details on how to get started.</p>
                @elseif ($enrollment && $enrollment->status === 'blocked')
                    <div class="w-20 h-20 rounded-full bg-red-50 text-red-500 flex items-center justify-center text-4xl mx-auto mb-6">
                        <i class="fa-solid fa-circle-xmark"></i>
                    </div>
                    <h1 class="section-title mb-3">Payment didn't go through</h1>
                    <p class="text-slate-500 mb-8">Your enrollment in <strong>{{ $course->title }}</strong> wasn't completed. No amount was charged — please try again.</p>
                    <a href="{{ route('courses-page.show', $course->slug) }}" class="btn-cta">Try Again <i class="fa-solid fa-arrow-right"></i></a>
                @else
                    <div class="w-20 h-20 rounded-full bg-amber-50 text-amber-500 flex items-center justify-center text-4xl mx-auto mb-6">
                        <i class="fa-solid fa-hourglass-half"></i>
                    </div>
                    <h1 class="section-title mb-3">Payment still processing</h1>
                    <p class="text-slate-500 mb-8">We're confirming your payment for <strong>{{ $course->title }}</strong>. Refresh in a moment, or check your email.</p>
                @endif
            </div>
        </div>
    </section>
@endsection

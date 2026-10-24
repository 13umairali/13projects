@extends('themes.' . config('frontend.active_theme', 'theme-three') . '.layouts.app')

@section('title', 'Checkout — ' . $course->title)

@section('content')
    <section class="py-20 bg-white">
        <div class="max-w-2xl mx-auto px-4">
            <a href="{{ route('courses-page.show', $course->slug) }}" class="inline-flex items-center gap-2 text-sm font-semibold text-slate-500 hover:text-brand mb-8">
                <i class="fa-solid fa-arrow-left"></i> Back
            </a>

            <div class="card-elevated overflow-hidden">
                <div class="p-8 md:p-10 text-white" style="background: linear-gradient(135deg, var(--brand-600), var(--accent-500));">
                    <span class="text-sm text-white/80">Enrolling in</span>
                    <h1 class="text-2xl md:text-3xl font-extrabold mt-1 font-display">{{ $course->title }}</h1>
                    <p class="mt-4 text-3xl font-black font-display">{{ number_format($price, 2) }}</p>
                </div>

                <div class="p-8 md:p-10">
                    @if (session('error'))
                        <div class="mb-6 px-5 py-4 rounded-2xl border border-red-200 bg-red-50 text-red-700 text-sm">
                            {{ session('error') }}
                        </div>
                    @endif

                    <h2 class="text-lg font-bold font-display mb-5">Choose a payment method</h2>

                    @if (empty($gateways))
                        <div class="text-center py-8 text-slate-400">
                            <i class="fa-solid fa-triangle-exclamation text-3xl mb-3"></i>
                            <p class="text-sm">No payment method is currently configured for this institute.<br>Please contact us directly to complete your enrollment.</p>
                        </div>
                    @else
                        <div class="space-y-3">
                            @foreach ($gateways as $gateway)
                                <form method="POST" action="{{ route('courses-page.checkout.pay', [$course->slug, $gateway['key']]) }}">
                                    @csrf
                                    <button type="submit" class="w-full flex items-center justify-between px-6 py-4 rounded-2xl border border-slate-200 hover:border-brand hover:bg-brand-50 transition group">
                                        <span class="font-semibold text-slate-700 group-hover:text-brand">{{ $gateway['label'] }}</span>
                                        <i class="fa-solid fa-arrow-right text-slate-400 group-hover:text-brand"></i>
                                    </button>
                                </form>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>
@endsection

@extends('layouts.landing')

@section('content')
    <x-landing.page-hero
        eyebrow="Help center"
        title='Frequently asked <span class="gradient-text">questions</span>'
        subtitle="Can’t find what you’re looking for? Our team is a message away." />

    <x-landing.faq :faqs="$faqs" />

    <div class="container-page pb-24">
        <div class="reveal mx-auto max-w-2xl rounded-2xl border border-slate-200 bg-slate-50 p-8 text-center dark:border-white/10 dark:bg-white/[0.02]">
            <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Still have questions?</h3>
            <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">We’re happy to help you find the right fit for your institution.</p>
            <a href="{{ route('contact') }}" class="btn-primary mt-5">Contact support</a>
        </div>
    </div>
@endsection

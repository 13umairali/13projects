@extends('layouts.landing')

@section('content')
    <x-landing.page-hero
        eyebrow="Pricing"
        title='Straightforward pricing for <span class="gradient-text">every size</span>'
        subtitle="Start free and scale as you grow. Every plan includes the core academic modules." />

    <x-landing.pricing :packages="$packages" :compact="true" />
    <x-landing.faq :faqs="$faqs" />
    <x-landing.cta :brand="$brand" />
@endsection

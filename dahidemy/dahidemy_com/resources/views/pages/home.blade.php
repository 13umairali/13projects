@extends('layouts.landing')

@section('content')
    <x-landing.hero :brand="$brand" :banner="$banner" />
<!--
    <x-landing.stats :stats="$stats" :revenue="$revenue" />
-->
    <x-landing.feature-grid :cores="$cores" />
    <x-landing.solutions :solutions="$solutions" />
    <x-landing.benefits :benefits="$benefits" />
    <x-landing.panels :panels="$panels" />
    <x-landing.pricing :packages="$packages" />
<!--
    <x-landing.testimonials :feedback="$feedback" />
    <x-landing.faq :faqs="$faqs" />
-->
    <x-landing.cta :brand="$brand" />
    <x-landing.newsletter />
@endsection

@extends('layouts.landing')

@section('content')
    <x-landing.page-hero
        eyebrow="Features"
        title='Every module your institution needs, <span class="gradient-text">beautifully connected</span>'
        subtitle="Explore the platform that replaces a dozen disconnected tools with one modern, unified system." />

    <x-landing.feature-grid :cores="$cores" />
    <x-landing.solutions :solutions="$solutions" />
    <x-landing.benefits :benefits="$benefits" />
    <x-landing.panels :panels="$panels" />
    <x-landing.cta :brand="$brand" />
@endsection

@extends('themes.theme-three.layouts.app')

@section('content')
    @include('themes.theme-three.sections.hero')

    @if (isset($data['homepageSectionOrder']) && count($data['homepageSectionOrder']) > 0)
        {{-- Admin-controlled order (Modules\HomepageBuilder). Each entry is a
             fully-qualified view name; @includeIf skips it quietly if that
             view/module doesn't exist, so a disabled or removed module can
             never break the homepage. --}}
        @foreach ($data['homepageSectionOrder'] as $sectionView)
            @includeIf($sectionView)
        @endforeach
    @else
        {{-- Fallback: the original fixed order, used only when
             HomepageBuilder isn't installed yet. Nothing changes for an
             institute until an admin actually visits the section manager. --}}
        @include('themes.theme-three.sections.about')
        @include('themes.theme-three.sections.stats')
        @include('themes.theme-three.sections.cta-admission')
        @include('themes.theme-three.sections.dynamic-forms')
        @include('themes.theme-three.sections.courses')
        @include('themes.theme-three.sections.notices')
        @include('themes.theme-three.sections.gallery')
        @include('themes.theme-three.sections.teachers')
        @include('themes.theme-three.sections.staffs')
        @include('themes.theme-three.sections.mobile-app')
        @include('themes.theme-three.sections.events')
        @include('themes.theme-three.sections.upcoming-exams')
        @include('themes.theme-three.sections.result-announcements')
        @include('themes.theme-three.sections.testimonials')
        @include('themes.theme-three.sections.faq')
    @endif
@endsection
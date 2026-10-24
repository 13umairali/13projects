<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#4338ca">
    <meta name="description"
        content="{{ $settings['meta_description'] ?? ($settings['school_name'] ?? 'A modern school delivering quality education.') }}">

    <title>@yield('title', $settings['site_title'] ?? ($settings['school_name'] ?? 'School Website'))</title>

    {{-- CSS --}}
    @include('themes.theme-three.partials.styles')
</head>

<body class="antialiased">

    @include('themes.theme-three.partials.notice-bar')
    @include('themes.theme-three.partials.header')

    <main>
        @yield('content')
    </main>

    @include('themes.theme-three.partials.footer')
    @include('themes.theme-three.partials.scripts')

    {{-- Popups (Modules\PopupBroadcaster) — defensive include so nothing
         breaks if that module isn't installed. --}}
    @if (view()->exists('popupbroadcaster::partials.widget'))
        @include('popupbroadcaster::partials.widget')
    @endif

</body>

</html>

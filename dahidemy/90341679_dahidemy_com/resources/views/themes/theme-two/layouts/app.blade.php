<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>@yield('title', $settings['site_title'] ?? $settings['school_name'] ?? 'Institute')</title>

    {{-- CSS --}}
    @include('themes.theme-two.partials.styles')
</head>

<body>
    @include('themes.theme-two.partials.notice-bar')
    @include('themes.theme-two.partials.header')

    <main>
        @yield('content')
    </main>

    @include('themes.theme-two.partials.footer')
    @include('themes.theme-two.partials.scripts')
</body>

</html>

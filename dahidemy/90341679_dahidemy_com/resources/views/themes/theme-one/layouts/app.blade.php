<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>@yield('title', $settings['site_title'] ?? 'Mighty School')</title>

    {{-- CSS --}}
    @include('themes.theme-one.partials.styles')
</head>

<body>
    @include('themes.theme-one.partials.notice-bar')
    @include('themes.theme-one.partials.header')

    {{-- MAIN CONTENT --}}
    <main>
        @yield('content')
    </main>

    @include('themes.theme-one.partials.footer')
    @include('themes.theme-one.partials.scripts')

</body>

</html>

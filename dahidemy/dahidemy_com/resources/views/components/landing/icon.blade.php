{{-- Renders either a built-in named SVG or an image the admin uploaded.
     The backend validates `icon` as an image file, but older rows (and the
     defaults below) carry an icon NAME, so both have to work. --}}
@props(['name' => 'sparkles', 'class' => 'h-6 w-6', 'storage' => null])

@php
    $icons = [
        'user-plus'    => '<path d="M16 21v-2a4 4 0 00-4-4H6a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M19 8v6M22 11h-6"/>',
        'book-open'    => '<path d="M2 3h6a4 4 0 014 4v14a3 3 0 00-3-3H2z"/><path d="M22 3h-6a4 4 0 00-4 4v14a3 3 0 013-3h7z"/>',
        'check-circle' => '<path d="M22 11.1V12a10 10 0 11-5.9-9.1"/><path d="M22 4L12 14.01l-3-3"/>',
        'clipboard'    => '<path d="M16 4h2a2 2 0 012 2v14a2 2 0 01-2 2H6a2 2 0 01-2-2V6a2 2 0 012-2h2"/><rect x="8" y="2" width="8" height="4" rx="1"/>',
        'wallet'       => '<path d="M21 12V7H5a2 2 0 010-4h14v4"/><path d="M3 5v14a2 2 0 002 2h16v-5"/><path d="M18 12a2 2 0 000 4h4v-4z"/>',
        'chat'         => '<path d="M21 11.5a8.38 8.38 0 01-.9 3.8 8.5 8.5 0 01-7.6 4.7 8.38 8.38 0 01-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 01-.9-3.8 8.5 8.5 0 014.7-7.6 8.38 8.38 0 013.8-.9h.5a8.48 8.48 0 018 8v.5z"/>',
        'shield'       => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>',
        'bolt'         => '<path d="M13 2L3 14h9l-1 8 10-12h-9z"/>',
        'chart'        => '<path d="M3 3v18h18"/><path d="M18 17V9M13 17V5M8 17v-3"/>',
        'bus'          => '<path d="M8 6v6M16 6v6M2 12h20M18 18h1a1 1 0 001-1V8a4 4 0 00-4-4H8a4 4 0 00-4 4v9a1 1 0 001 1h1"/><circle cx="7" cy="18" r="2"/><circle cx="17" cy="18" r="2"/>',
        'home'         => '<path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><path d="M9 22V12h6v10"/>',
        'library'      => '<path d="M4 19.5A2.5 2.5 0 016.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 014 19.5v-15A2.5 2.5 0 016.5 2z"/>',
        'globe'        => '<circle cx="12" cy="12" r="10"/><path d="M2 12h20M12 2a15.3 15.3 0 010 20 15.3 15.3 0 010-20z"/>',
        'sparkles'     => '<path d="M12 3l1.9 4.6L18.5 9l-4.6 1.4L12 15l-1.9-4.6L5.5 9l4.6-1.4z"/><path d="M19 15l.9 2.1L22 18l-2.1.9L19 21l-.9-2.1L16 18l2.1-.9z"/>',
    ];
    // An uploaded icon is a filename, not one of the keys above.
    $uploaded = $name && ! isset($icons[$name]) && preg_match('/\.(png|jpe?g|webp|svg|gif)$/i', (string) $name)
        ? saas_image($name, $storage)
        : null;

    $path = $icons[$name] ?? $icons['sparkles'];
@endphp

@if ($uploaded)
    {{-- An uploaded icon whose file has gone missing falls back to the built-in
         SVG below, which reads far better in a small tile than a "no image" box. --}}
    <img src="{{ $uploaded }}" alt="" loading="lazy" decoding="async"
        {{ $attributes->merge(['class' => $class . ' object-contain']) }}
        onerror="this.onerror=null;this.style.display='none';if(this.nextElementSibling)this.nextElementSibling.style.display='';">
    <svg style="display:none" {{ $attributes->merge(['class' => $class]) }} viewBox="0 0 24 24" fill="none"
        stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
        {!! $path !!}
    </svg>
@else
    <svg {{ $attributes->merge(['class' => $class]) }} viewBox="0 0 24 24" fill="none" stroke="currentColor"
        stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
        {!! $path !!}
    </svg>
@endif

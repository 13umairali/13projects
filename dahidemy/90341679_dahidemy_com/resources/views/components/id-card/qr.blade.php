@props([
    'value',
    'size' => 220,
])

@php
    // Rendered at a high module count and scaled down by CSS, so the code stays
    // crisp on a 300dpi card printer. Emitted as a base64 data-URI <img> instead
    // of a raw inline <svg>: html2canvas (used by the "Download PDF" button)
    // ignores CSS sizing on inline SVG and would blow the card layout apart.
    $payload = trim((string) $value) !== '' ? (string) $value : 'N/A';
    $svg = (string) QrCode::size((int) $size)
        ->margin(0)
        ->errorCorrection('M')
        ->generate($payload);
@endphp

<img {{ $attributes->merge(['class' => 'idc-qr']) }} alt="{{ _lang('QR code') }}"
    src="data:image/svg+xml;base64,{{ base64_encode($svg) }}">

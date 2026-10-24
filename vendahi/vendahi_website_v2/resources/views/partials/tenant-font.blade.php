{{--
    Tenant dashboard font override.
    Applies the tenant-selected font (System Settings → Dashboard font family)
    across the whole tenant SPA. A curated Google family is loaded from the
    Google Fonts CDN; any other stored value is treated as a raw system-font
    stack (legacy dropdown values). Renders nothing when no font is chosen.
    The stored value is validated against the whitelist on save AND re-checked
    here (defence-in-depth) so it is always safe to interpolate below.
--}}
@php
    $tenantFont = trim((string) ($app_settings->dashboard_font_family ?? ''));
    $tenantFontIsGoogle = \App\Models\Setting::isDashboardGoogleFont($tenantFont);
    $tenantFontIsSystem = array_key_exists($tenantFont, \App\Models\Setting::DASHBOARD_SYSTEM_FONTS);
    if (! $tenantFontIsGoogle && ! $tenantFontIsSystem) {
        $tenantFont = '';
    }

    // Pre-computed here rather than inline in @json() below: Blade splits @json
    // arguments on commas (they map to json_encode's $flags/$depth), so a stack
    // containing commas breaks the compiled PHP.
    $tenantFontName = $tenantFontIsGoogle ? $tenantFont : null;
    $tenantFontStack = ($tenantFont !== '' && $tenantFont !== 'inherit')
        ? ($tenantFontIsGoogle ? "'" . $tenantFont . "', 'Segoe UI', Tahoma, sans-serif" : $tenantFont)
        : null;
@endphp
@if($tenantFontStack !== null)
    @if($tenantFontIsGoogle)
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family={{ str_replace(' ', '+', $tenantFont) }}:wght@400;500;600;700&display=swap" rel="stylesheet">
    @endif
    <style id="tenant-font">
        {{-- Raw output on purpose: {{ }} would HTML-encode the double quotes in
             legacy stacks (e.g. "Times New Roman", ...) and break the CSS. The
             value is whitelisted above so this cannot carry untrusted markup. --}}
        :root {
            --tenant-font: {!! $tenantFontStack !!};
        }
        body,
        button, input, textarea, select,
        .btn, .form-control, .dropdown-menu,
        table, .table, .modal, .tooltip, .popover {
            font-family: var(--tenant-font) !important;
        }
    </style>
@endif
<script>
    // Google family name (null when a system stack / no font is selected) and
    // the full CSS stack — consumed by the POS print windows, which build
    // fresh documents that do not inherit this page's fonts.
    window.__tenantFont = @json($tenantFontName);
    window.__tenantFontStack = @json($tenantFontStack);
</script>

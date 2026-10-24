{{-- TAILWIND (utility engine — no build step required) --}}
<script src="https://cdn.tailwindcss.com"></script>

{{-- FONT AWESOME --}}
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

{{-- AOS SCROLL ANIMATION — kept for the one deliberate reveal moment on the
     hero only; section-by-section fade-ins were removed elsewhere, since
     scattered scroll animation on every card is a generic template tell. --}}
<link href="https://unpkg.com/aos@2.3.4/dist/aos.css" rel="stylesheet">

{{-- GOOGLE FONTS — Newsreader (serif, editorial gravitas) for headlines,
     IBM Plex Sans (clean, technical, trustworthy) for body/UI. Deliberately
     not the generic Playfair+Inter pairing. --}}
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Newsreader:ital,wght@0,400;0,500;0,600;0,700;1,400;1,500&family=IBM+Plex+Sans:wght@400;500;600;700&display=swap"
    rel="stylesheet">

@if (!empty($instituteImage?->favicon))
    <link rel="shortcut icon" href="{{ asset('storage/institute_image_settings/' . $instituteImage->favicon) }}" />
@endif

<style>
    /* =========================================================
       DESIGN TOKENS — institutional / editorial direction.
       One confident accent (brass), used sparingly. Small,
       consistent corner radius — not bouncy pill shapes. Hairline
       borders instead of soft drop-shadow blobs.
    ========================================================= */
    :root {
        --navy-900: #101a2e;
        --navy-800: #1b2a4a;
        --navy-700: #253a63;
        --ink-900: #14181f;
        --ink-700: #3a3f4a;
        --slate-500: #5b6472;
        --paper-50: #fbfaf7;
        --paper-100: #f3f1ea;
        --line-200: #e4e1d8;
        --brass-500: #b08d57;
        --brass-600: #96723f;
        --brass-50: #f6efe2;

        --radius-sm: 4px;
        --radius-md: 8px;
        --shadow-card: 0 1px 2px rgba(20, 24, 31, .04);
    }

    * {
        font-family: 'IBM Plex Sans', sans-serif;
    }

    h1, h2, h3, h4, .font-display {
        font-family: 'Newsreader', serif;
        font-weight: 500;
        letter-spacing: -0.01em;
    }

    html {
        scroll-behavior: smooth;
    }

    body {
        background: var(--paper-50);
        color: var(--ink-900);
        overflow-x: hidden;
    }

    @media (prefers-reduced-motion: reduce) {
        html { scroll-behavior: auto; }
        * { animation-duration: 0.01ms !important; animation-iteration-count: 1 !important; transition-duration: 0.01ms !important; }
    }

    /* ============ COLOR UTILITIES ============ */
    .bg-brand { background: var(--navy-800); }
    .text-brand { color: var(--navy-800); }
    .border-brand { border-color: var(--navy-800); }
    .bg-accent { background: var(--brass-500); }
    .text-accent { color: var(--brass-600); }

    /* ============ SECTION LABEL ============
       Replaces the old all-caps pill "eyebrow" badge (a known generic
       AI-template tell) with a quiet, editorial section marker: a short
       rule plus a plain-case word, in the brass accent. */
    .eyebrow {
        display: inline-flex;
        align-items: center;
        gap: .6rem;
        font-size: .95rem;
        font-weight: 600;
        color: var(--brass-600);
    }

    .eyebrow::before {
        content: '';
        display: inline-block;
        width: 28px;
        height: 2px;
        background: var(--brass-500);
    }

    .eyebrow.on-dark {
        color: #d9c4a0;
    }

    .eyebrow.on-dark::before {
        background: #d9c4a0;
    }

    .section-title {
        font-size: clamp(1.9rem, 3vw, 2.75rem);
        font-weight: 500;
        line-height: 1.2;
        letter-spacing: -0.01em;
        color: var(--ink-900);
    }

    /* ============ BUTTONS ============
       Solid navy, small consistent radius — not a gradient pill. Brass is
       reserved for the one primary admissions action per view. */
    .btn-cta {
        display: inline-flex;
        align-items: center;
        gap: .6rem;
        background: var(--brass-500);
        color: #fff;
        padding: .85rem 1.75rem;
        border-radius: var(--radius-sm);
        font-weight: 600;
        transition: background .2s ease;
    }

    .btn-cta:hover {
        background: var(--brass-600);
    }

    .btn-secondary {
        display: inline-flex;
        align-items: center;
        gap: .6rem;
        background: var(--navy-800);
        color: #fff;
        padding: .85rem 1.75rem;
        border-radius: var(--radius-sm);
        font-weight: 600;
        transition: background .2s ease;
    }

    .btn-secondary:hover {
        background: var(--navy-900);
    }

    .btn-outline {
        display: inline-flex;
        align-items: center;
        gap: .6rem;
        border: 1.5px solid rgba(255, 255, 255, .4);
        color: #fff;
        padding: .8rem 1.7rem;
        border-radius: var(--radius-sm);
        font-weight: 600;
        transition: all .2s ease;
    }

    .btn-outline:hover {
        background: rgba(255, 255, 255, .08);
        border-color: #fff;
    }

    /* ============ CARDS ============
       Hairline border, minimal shadow, small radius — not the identical
       soft-shadow rounded-card kit. */
    .card-elevated {
        background: #fff;
        border-radius: var(--radius-md);
        box-shadow: var(--shadow-card);
        border: 1px solid var(--line-200);
        transition: border-color .2s ease;
    }

    .card-elevated:hover {
        border-color: var(--brass-500);
    }

    /* ============ NOTICE MARQUEE ============ */
    .marquee { overflow: hidden; white-space: nowrap; }
    .marquee-track { display: inline-block; padding-left: 100%; animation: marquee 26s linear infinite; }
    .marquee:hover .marquee-track { animation-play-state: paused; }
    @keyframes marquee {
        0% { transform: translateX(0%); }
        100% { transform: translateX(-100%); }
    }

    /* ============ COUNTER ============ */
    .counter-value { font-variant-numeric: tabular-nums; font-family: 'Newsreader', serif; }

    /* ============ FAQ ============ */
    details summary { list-style: none; cursor: pointer; }
    details summary::-webkit-details-marker { display: none; }
    .faq-item[open] .faq-icon { transform: rotate(45deg); }
    .faq-icon { transition: transform .3s ease; }

    /* ============ FORM CONTROLS ============
       Every input/select/textarea gets both text color and background
       declared together, plus color-scheme:light, so a dark-mode OS/browser
       can't silently make typed text invisible against a light field. */
    input, select, textarea {
        color: var(--ink-900);
        color-scheme: light;
    }

    input::placeholder, textarea::placeholder {
        color: var(--slate-500);
        opacity: 1;
    }

    select option {
        color: var(--ink-900);
        background: #ffffff;
    }

    /* ============ SCROLLBAR ============ */
    ::-webkit-scrollbar { width: 9px; }
    ::-webkit-scrollbar-thumb { background: var(--navy-800); border-radius: 99px; }

    /* ============ TESTIMONIAL SCROLLER ============ */
    .snap-row {
        display: flex;
        gap: 1.75rem;
        overflow-x: auto;
        scroll-snap-type: x mandatory;
        padding-bottom: .5rem;
        scrollbar-width: none;
    }
    .snap-row::-webkit-scrollbar { display: none; }
    .snap-row > * { scroll-snap-align: start; flex: 0 0 auto; }

    /* ============ GALLERY ============ */
    .gallery-tile { position: relative; overflow: hidden; border-radius: var(--radius-md); }
    .gallery-tile img { transition: transform .5s ease; }
    .gallery-tile:hover img { transform: scale(1.06); }
    .gallery-tile .overlay {
        position: absolute; inset: 0;
        background: linear-gradient(180deg, transparent 40%, rgba(16,26,46,.9));
        opacity: 0; transition: opacity .3s ease;
    }
    .gallery-tile:hover .overlay { opacity: 1; }

    /* ============ FLOATING ACTIONS ============ */
    .float-stack { position: fixed; right: 1.5rem; bottom: 1.5rem; z-index: 60; display: flex; flex-direction: column; gap: .75rem; }
    .float-btn {
        width: 52px; height: 52px; border-radius: var(--radius-md); display: flex; align-items: center; justify-content: center;
        font-size: 1.3rem; color: #fff; background: var(--navy-800); box-shadow: 0 4px 16px rgba(16,26,46,.25); transition: background .2s ease;
    }
    .float-btn:hover { background: var(--navy-900); }

    /* ============ FOCUS VISIBILITY ============ */
    a:focus-visible, button:focus-visible, input:focus-visible, select:focus-visible, textarea:focus-visible {
        outline: 2px solid var(--brass-500);
        outline-offset: 2px;
    }
</style>

@php
    // Wires up the "Appearance Settings" tab (System Settings ->
    // Primary/Secondary Color). Deliberately does NOT use get_option()
    // here — that helper resolves the institute via the *logged-in user*
    // (get_institute_id() -> auth()->user()->institute_id), which is null
    // for an anonymous visitor on the public site. That mismatch is why
    // the saved color never appeared here even though it saved correctly.
    // This resolves the institute the same way the rest of the public
    // site does: from the domain.
    $colorInstitute = \App\Helpers\InstituteHelper::resolveInstituteFromRequest(request());
    $primaryColor = '#1b2a4a';
    $secondaryColor = '#b08d57';

    // Extended Theme Designer fields (header/footer/button/text/hover) -
    // same institute-resolved lookup, since get_option() can't see a
    // logged-out visitor's institute at all.
    $themeSetting = function (string $name, string $default) use (&$colorInstitute) {
        if (! $colorInstitute) {
            return $default;
        }

        $value = \Illuminate\Support\Facades\DB::table('settings')
            ->where('institute_id', $colorInstitute->id)
            ->where('name', $name)
            ->value('value');

        return $value ?: $default;
    };

    if ($colorInstitute) {
        $savedPrimary = \Illuminate\Support\Facades\DB::table('settings')
            ->where('institute_id', $colorInstitute->id)
            ->where('name', 'primary_color')
            ->value('value');
        $savedSecondary = \Illuminate\Support\Facades\DB::table('settings')
            ->where('institute_id', $colorInstitute->id)
            ->where('name', 'secondary_color')
            ->value('value');

        $primaryColor = $savedPrimary ?: $primaryColor;
        $secondaryColor = $savedSecondary ?: $secondaryColor;
    }

    // The newer Theme Designer's own primary/secondary (if set) take
    // precedence over the legacy single-color fields above.
    $primaryColor = $themeSetting('theme_primary_color', $primaryColor);
    $secondaryColor = $themeSetting('theme_secondary_color', $secondaryColor);

    $themePrimaryType = $themeSetting('theme_primary_type', 'solid');
    $themePrimary2 = $themeSetting('theme_primary_color_2', $primaryColor);
    $themeSecondaryType = $themeSetting('theme_secondary_type', 'solid');
    $themeSecondary2 = $themeSetting('theme_secondary_color_2', $secondaryColor);

    $themeHeaderType = $themeSetting('theme_header_type', 'solid');
    $themeHeaderColor = $themeSetting('theme_header_color', '#fbfaf7');
    $themeHeaderColor2 = $themeSetting('theme_header_color_2', '#f3f1ea');

    $themeFooterType = $themeSetting('theme_footer_type', 'solid');
    $themeFooterColor = $themeSetting('theme_footer_color', '#101a2e');
    $themeFooterColor2 = $themeSetting('theme_footer_color_2', '#1b2a4a');

    $themeButtonType = $themeSetting('theme_button_type', 'solid');
    $themeButtonColor = $themeSetting('theme_button_color', $secondaryColor);
    $themeButtonColor2 = $themeSetting('theme_button_color_2', $secondaryColor);

    $themeTextColor = $themeSetting('theme_text_color', '#14181f');
    $themeHoverColor = $themeSetting('theme_hover_color', $secondaryColor);

    $bgFor = fn (string $type, string $c1, string $c2) => $type === 'gradient'
        ? "linear-gradient(135deg, {$c1}, {$c2})"
        : $c1;
@endphp
<style>
    :root {
        --navy-800: {{ $bgFor($themePrimaryType, $primaryColor, $themePrimary2) }};
        --navy-900: color-mix(in srgb, {{ $primaryColor }} 80%, black);
        --navy-700: color-mix(in srgb, {{ $primaryColor }} 85%, white);
        --brass-500: {{ $bgFor($themeSecondaryType, $secondaryColor, $themeSecondary2) }};
        --brass-600: color-mix(in srgb, {{ $secondaryColor }} 80%, black);
        --brass-50: color-mix(in srgb, {{ $secondaryColor }} 12%, white);
        --ink-900: {{ $themeTextColor }};

        /* Large dark section backgrounds (Courses, CTA panels, Hero
           fallback, Footer) deliberately do NOT follow Primary directly -
           Primary is meant for buttons/links/small accents and can
           reasonably be a bright, saturated color, which looks wrong
           stretched across an entire section. These follow the Footer
           color instead, which is already meant to be a dark background
           choice, independent of whatever Primary is set to. */
        --surface-dark: {{ $bgFor($themeFooterType, $themeFooterColor, $themeFooterColor2) }};
        --surface-dark-2: color-mix(in srgb, {{ $themeFooterColor }} 85%, white);
    }

    /* Theme Designer: header/footer/button/hover */
    header {
        background: {{ $bgFor($themeHeaderType, $themeHeaderColor, $themeHeaderColor2) }} !important;
    }

    footer {
        background: {{ $bgFor($themeFooterType, $themeFooterColor, $themeFooterColor2) }} !important;
    }

    .btn-cta {
        background: {{ $bgFor($themeButtonType, $themeButtonColor, $themeButtonColor2) }} !important;
    }

    a:hover, .hover\:text-brand:hover {
        color: {{ $themeHoverColor }} !important;
    }
</style>
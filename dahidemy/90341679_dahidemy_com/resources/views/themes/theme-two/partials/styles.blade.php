{{-- TAILWIND --}}
<script src="https://cdn.tailwindcss.com"></script>

{{-- FONT AWESOME --}}
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

{{-- AOS ANIMATION (used sparingly here — Northline favours stillness over motion) --}}
<link href="https://unpkg.com/aos@2.3.4/dist/aos.css" rel="stylesheet">

@if ($instituteImage?->favicon)
    <link rel="shortcut icon" href="{{ asset('storage/institute_image_settings/' . $instituteImage->favicon) }}" />
@endif

{{-- GOOGLE FONT — a serif display face paired with a workhorse sans, the
     classic "editorial" combination that reads as considered rather than
     templated. --}}
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Fraunces:opsz,wght@9..144,500;9..144,600;9..144,700&display=swap"
    rel="stylesheet">

<style>
    :root {
        --nl-primary: {{ $settings['website_primary_color'] ?? '#0f766e' }};
        --nl-secondary: {{ $settings['website_secondary_color'] ?? '#0b1220' }};
        --nl-accent: {{ $settings['website_accent_color'] ?? '#f59e0b' }};

        --nl-ink: #14181f;
        --nl-muted: #5b6470;
        --nl-line: #e5e7eb;
        --nl-paper: #fbfaf7;
    }

    * {
        font-family: 'Inter', sans-serif;
    }

    .font-display {
        font-family: 'Fraunces', serif;
    }

    html {
        scroll-behavior: smooth;
    }

    body {
        color: var(--nl-ink);
        background: var(--nl-paper);
    }

    .text-brand {
        color: var(--nl-primary);
    }

    .bg-brand {
        background: var(--nl-primary);
    }

    .border-brand {
        border-color: var(--nl-primary);
    }

    .bg-ink {
        background: var(--nl-secondary);
    }

    .btn-nl {
        display: inline-flex;
        align-items: center;
        gap: .5rem;
        padding: 13px 26px;
        border-radius: 4px;
        font-weight: 600;
        background: var(--nl-primary);
        color: #fff;
        transition: all .25s ease;
        border: 1px solid var(--nl-primary);
    }

    .btn-nl:hover {
        background: transparent;
        color: var(--nl-primary);
    }

    .btn-nl-outline {
        display: inline-flex;
        align-items: center;
        gap: .5rem;
        padding: 12px 25px;
        border-radius: 4px;
        font-weight: 600;
        border: 1px solid var(--nl-line);
        color: var(--nl-ink);
        transition: all .25s ease;
    }

    .btn-nl-outline:hover {
        border-color: var(--nl-ink);
    }

    .eyebrow {
        display: inline-flex;
        align-items: center;
        gap: .5rem;
        font-size: 13px;
        font-weight: 600;
        letter-spacing: .12em;
        text-transform: uppercase;
        color: var(--nl-primary);
    }

    .eyebrow::before {
        content: '';
        width: 24px;
        height: 2px;
        background: var(--nl-accent);
        display: inline-block;
    }

    .nl-card {
        background: #fff;
        border: 1px solid var(--nl-line);
        border-radius: 6px;
        transition: border-color .25s ease, transform .25s ease;
    }

    .nl-card:hover {
        border-color: var(--nl-primary);
        transform: translateY(-4px);
    }

    .nl-divider {
        height: 1px;
        background: var(--nl-line);
    }

    .nl-nav-link {
        position: relative;
        color: var(--nl-ink);
        font-weight: 500;
    }

    .nl-nav-link::after {
        content: '';
        position: absolute;
        left: 0;
        bottom: -6px;
        width: 0;
        height: 2px;
        background: var(--nl-primary);
        transition: width .25s ease;
    }

    .nl-nav-link:hover::after {
        width: 100%;
    }

    .nl-quote::before {
        content: '\201C';
        font-family: 'Fraunces', serif;
        font-size: 64px;
        line-height: 1;
        color: var(--nl-accent);
        display: block;
    }

    details.nl-faq summary {
        list-style: none;
        cursor: pointer;
    }

    details.nl-faq summary::-webkit-details-marker {
        display: none;
    }

    details.nl-faq[open] summary .nl-faq-icon {
        transform: rotate(45deg);
    }

    .nl-faq-icon {
        transition: transform .2s ease;
    }

    ::-webkit-scrollbar {
        width: 8px;
    }

    ::-webkit-scrollbar-thumb {
        background: var(--nl-primary);
    }
</style>

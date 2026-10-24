{{--
    Production (cPanel / no Node): reads public/build/manifest.json + fonts-manifest.json
    and emits static <link>/<script> tags. Requires /build/* routed in root .htaccess.
    Development: run `npm run dev` — do not ship public/hot to production.
--}}
@fonts
@vite(['resources/css/app.css', 'resources/js/app.js'])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    {{-- Responsive --}}
    <meta name="viewport" content="width=device-width, initial-scale=1">

    {{-- CSRF --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>
        @yield('title', config('app.name')) | {{ config('app.name') }}
    </title>

    {{-- SEO --}}
    <meta name="description" content="{{ config('app.name') }}">
    <meta name="author" content="{{ config('app.name') }}">

    {{-- Favicon --}}
    <link rel="icon" type="image/png" href="{{ get_logo() }}">

    {{-- Google Font --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap"
        rel="stylesheet">

    {{-- Font Awesome --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

    {{-- Tailwind CSS CDN --}}
    <script src="https://cdn.tailwindcss.com"></script>

    {{-- Tailwind Config --}}
    <script>
        tailwind.config = {
            theme: {
                extend: {

                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },

                    colors: {
                        primary: '#2563eb',
                        secondary: '#4f46e5',
                    },

                    boxShadow: {
                        glow: '0 0 40px rgba(37,99,235,0.35)',
                    },

                    animation: {
                        float: 'float 6s ease-in-out infinite',
                        pulseSlow: 'pulse 4s infinite',
                    },

                    keyframes: {
                        float: {
                            '0%, 100%': {
                                transform: 'translateY(0px)'
                            },
                            '50%': {
                                transform: 'translateY(-12px)'
                            },
                        }
                    }
                }
            }
        }
    </script>

    {{-- Global Styles --}}
    <style>
        * {
            font-family: 'Inter', sans-serif;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            margin: 0;
            padding: 0;
            overflow-x: hidden;
            background: #020617;
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #0f172a;
        }

        ::-webkit-scrollbar-thumb {
            background: linear-gradient(to bottom, #2563eb, #4f46e5);
            border-radius: 50px;
        }

        ::selection {
            background: #2563eb;
            color: white;
        }

        /* Autofill Fix */
        input:-webkit-autofill,
        input:-webkit-autofill:hover,
        input:-webkit-autofill:focus,
        textarea:-webkit-autofill,
        select:-webkit-autofill {
            -webkit-text-fill-color: #fff !important;
            transition: background-color 5000s ease-in-out 0s;
        }

        /* Remove Number Input Arrow */
        input[type=number]::-webkit-inner-spin-button,
        input[type=number]::-webkit-outer-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        /* Glass Effect */
        .glass {
            background: rgba(255, 255, 255, 0.06);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }

        /* Gradient Text */
        .gradient-text {
            background: linear-gradient(to right, #60a5fa, #818cf8);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
    </style>

    {{-- Page Styles --}}
    @yield('styles')
</head>

<body class="font-sans antialiased text-white">

    {{-- App --}}
    <div id="app">

        {{-- Main Content --}}
        <main class="min-h-screen">
            @yield('content')
        </main>

    </div>

    {{-- JQuery --}}
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    {{-- Alpine JS --}}
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    {{-- Custom Scripts --}}
    @yield('scripts')

</body>

</html>

<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', config('app.name', 'Arun Naturals'))</title>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Nunito:300,400,600,700" rel="stylesheet">

    <!-- Scripts / Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body {
            background: radial-gradient(circle at top, #fdfaf5 0%, #f5f2ec 55%, #f1eee8 100%);
            font-family: 'Nunito', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            color: #1f2933;
        }

        .store-navbar {
            backdrop-filter: blur(16px);
            background: rgba(255, 255, 255, 0.9);
            border-bottom: 1px solid rgba(148, 163, 184, 0.25);
        }

        .brand-pill {
            border-radius: 999px;
            padding: 0.35rem 0.85rem;
            background: rgba(16, 185, 129, 0.06);
            border: 1px solid rgba(16, 185, 129, 0.2);
            color: #047857;
            font-size: 0.75rem;
            letter-spacing: .08em;
            text-transform: uppercase;
        }

        .trust-badge {
            border-radius: 999px;
            padding: 0.25rem 0.8rem;
            background: #ffffff;
            border: 1px solid rgba(148, 163, 184, 0.35);
            font-size: 0.75rem;
            color: #4b5563;
        }

        .card-soft {
            border-radius: 1.5rem;
            border: 1px solid rgba(148, 163, 184, 0.3);
            background: rgba(255, 255, 255, 0.96);
            box-shadow:
                0 36px 80px rgba(15, 23, 42, 0.12),
                0 0 0 1px rgba(148, 163, 184, 0.25);
        }

        .btn-whatsapp-main {
            background: linear-gradient(135deg, #22c55e, #16a34a);
            border-color: transparent;
            color: #f9fafb;
        }

        .btn-whatsapp-main:hover {
            background: linear-gradient(135deg, #16a34a, #15803d);
            color: #f9fafb;
        }

        .btn-outline-soft {
            border-color: rgba(148, 163, 184, 0.6);
            color: #4b5563;
        }

        .btn-outline-soft:hover {
            border-color: #16a34a;
            color: #14532d;
            background: rgba(187, 247, 208, 0.3);
        }

        .section-label {
            font-size: 0.75rem;
            letter-spacing: .09em;
            text-transform: uppercase;
            color: #6b7280;
        }
    </style>
</head>
<body>
    <div id="store-app">
        <nav class="store-navbar navbar navbar-expand-md sticky-top py-3">
            <div class="container">
                <a class="navbar-brand d-flex align-items-center" href="/">
                    <div class="rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 40px; height: 40px; background: linear-gradient(135deg, #22c55e, #86efac);">
                        <span class="fw-bold text-white">A</span>
                    </div>
                    <div class="d-flex flex-column">
                        <span class="fw-semibold">Arun Naturals</span>
                        <small class="text-muted" style="font-size: 0.7rem;">Calm, honest self‑care</small>
                    </div>
                </a>
                <div class="ms-auto d-flex align-items-center gap-3">
                    <span class="trust-badge d-none d-md-inline-flex align-items-center">
                        <span class="me-1">✓</span> Genuine, small‑batch formulations
                    </span>
                </div>
            </div>
        </nav>

        <main class="py-5">
            @yield('content')
        </main>

        <footer class="py-4 border-top" style="background: rgba(249, 250, 251, 0.9);">
            <div class="container d-flex flex-column flex-md-row justify-content-between align-items-center">
                <p class="mb-1 text-muted">&copy; {{ date('Y') }} Arun Naturals. All rights reserved.</p>
                <p class="mb-0 text-muted" style="font-size: 0.8rem;">Formulations described on this page are for general wellness; always patch test first.</p>
            </div>
        </footer>
    </div>
</body>
</html>


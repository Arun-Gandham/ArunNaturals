<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @php
        $appName = optional($siteSettings ?? null)->site_name ?? config('app.name', 'Laravel');
        $metaTitle = trim($__metaTitle ?? (optional($siteSettings ?? null)->meta_title ?? $appName));
        $metaDescription = trim($__metaDescription ?? (optional($siteSettings ?? null)->meta_description ?? ''));
        $metaKeywords = trim($__metaKeywords ?? (optional($siteSettings ?? null)->meta_keywords ?? ''));
    @endphp

    <title>{{ $metaTitle }}</title>
    <meta name="description" content="{{ $metaDescription }}">
    @if($metaKeywords)
        <meta name="keywords" content="{{ $metaKeywords }}">
    @endif

    <meta property="og:title" content="{{ $metaTitle }}">
    <meta property="og:description" content="{{ $metaDescription }}">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="{{ $appName }}">

    <link rel="icon" type="image/x-icon" href="{{ optional($siteSettings ?? null)->favicon_url ? url(optional($siteSettings ?? null)->favicon_url) : asset('favicon.ico') }}">

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Font Awesome for modern sidebar icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <style>
        body {
            background-color: #f3f4f6;
        }

        #app {
            min-height: 100vh;
        }

        .sidebar {
            background: #0f172a;
            color: #e5e7eb;
            border-radius: 0 24px 24px 0;
            box-shadow: 4px 0 24px rgba(15, 23, 42, 0.45);
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            width: 220px;
            z-index: 1040;
            display: flex;
            flex-direction: column;
        }

        .main-layout {
            margin-left: 220px;
            min-height: 100vh;
            padding-top: 72px; /* space for fixed header */
        }

        .sidebar-brand-title {
            font-weight: 800;
            letter-spacing: 0.03em;
            font-size: 1.1rem;
        }

        .sidebar-nav .sidebar-link {
            border-radius: 999px;
            padding: 0.55rem 0.9rem;
            color: #cbd5f5;
            display: flex;
            align-items: center;
            gap: 0.65rem;
            font-size: 0.95rem;
            transition: background-color 0.15s ease, color 0.15s ease, transform 0.05s ease;
        }

        .sidebar-nav .sidebar-link .sidebar-icon {
            width: 1.75rem;
            height: 1.75rem;
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: rgba(148, 163, 184, 0.18);
            color: #e5e7eb;
            font-size: 1rem;
        }

        .sidebar-nav .sidebar-link:hover {
            text-decoration: none;
            background: rgba(248, 250, 252, 0.06);
            transform: translateX(2px);
        }

        .sidebar-nav .sidebar-link.active {
            background: #f9fafb;
            color: #0f172a;
        }

        .sidebar-nav .sidebar-link.active .sidebar-icon {
            background: #0f172a;
            color: #f9fafb;
        }

        .sidebar-section-label {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.12em;
            color: #6b7280;
            margin-bottom: 0.5rem;
        }

        .sidebar-divider {
            border-top: 1px solid rgba(148, 163, 184, 0.35);
            margin: 1rem 0;
        }

        .app-header {
            background: rgba(249, 250, 251, 0.9);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(209, 213, 219, 0.6);
            position: fixed;
            top: 0;
            left: 220px;
            right: 0;
            z-index: 1030;
        }

        .app-header-inner {
            max-width: 1200px;
            margin: 0 auto;
        }

        .app-header-search {
            background: #e5e7eb;
            border-radius: 999px;
            padding: 0.25rem 0.75rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            flex: 1;
        }

        .app-header-search input {
            border: none;
            background: transparent;
            outline: none;
            font-size: 0.9rem;
        }

        .app-header-search i {
            color: #6b7280;
        }

        .app-header-actions .icon-btn {
            width: 36px;
            height: 36px;
            border-radius: 999px;
            border: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #e5e7eb;
            color: #4b5563;
            margin-right: 0.35rem;
        }

        .app-header-actions .icon-btn:hover {
            background: #d1d5db;
        }

        /* Sidebar: keep scroll functional but hide scrollbar */
        .sidebar-menu-scroll {
            scrollbar-width: none; /* Firefox */
        }
        .sidebar-menu-scroll::-webkit-scrollbar {
            width: 0;
            height: 0;
        }
    </style>
</head>
<body>
    <div id="app">
        <!-- Sidebar -->
        <nav class="sidebar text-white p-3">
            <div class="mb-4 px-1 d-flex flex-column align-items-center text-center gap-2 flex-shrink-0">
                <a href="/" class="d-inline-flex align-items-center justify-content-center bg-white" style="width: 40px; height: 40px; border-radius: 14px;">
                    @if(optional($siteSettings ?? null)->logo_url)
                        <img src="{{ url(optional($siteSettings ?? null)->logo_url) }}" alt="Logo" style="width: 28px; height: 28px; border-radius: 10px;">
                    @else
                        <img src="https://ui-avatars.com/api/?name=AN&background=0D8ABC&color=fff" alt="Logo" style="width: 28px; height: 28px; border-radius: 10px;">
                    @endif
                </a>
                <div>
                    <div class="sidebar-brand-title">{{ $appName }}</div>
                    <small class="text-muted" style="font-size: 0.75rem;">Admin Console</small>
                </div>
            </div>

            <div class="flex-grow-1 sidebar-menu-scroll" style="overflow-y: auto; padding-bottom: 2.5rem;">
                <div class="sidebar-divider"></div>

                <div class="sidebar-section-label px-1">Main</div>
                <ul class="nav flex-column sidebar-nav mb-3">
                    <li class="nav-item mb-1">
                        <a href="{{ route('admin.dashboard') }}"
                           class="nav-link sidebar-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                            <span class="sidebar-icon">
                                <i class="fa-solid fa-table-columns"></i>
                            </span>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li class="nav-item mb-1">
                        <a href="{{ route('admin.orders.index') }}"
                           class="nav-link sidebar-link {{ request()->routeIs('admin.orders.*') ? 'active' : '' }}">
                            <span class="sidebar-icon">
                                <i class="fa-solid fa-bag-shopping"></i>
                            </span>
                            <span>Orders</span>
                        </a>
                    </li>
                    <li class="nav-item mb-1">
                        <a href="{{ route('admin.products.index') }}"
                           class="nav-link sidebar-link {{ request()->routeIs('admin.products.*') ? 'active' : '' }}">
                            <span class="sidebar-icon">
                                <i class="fa-solid fa-box-open"></i>
                            </span>
                            <span>Products</span>
                        </a>
                    </li>
                    <li class="nav-item mb-1">
                        <a href="{{ route('admin.coupons.index') }}"
                           class="nav-link sidebar-link {{ request()->routeIs('admin.coupons.*') ? 'active' : '' }}">
                            <span class="sidebar-icon">
                                <i class="fa-solid fa-ticket-simple"></i>
                            </span>
                            <span>Coupons</span>
                        </a>
                    </li>
                    <li class="nav-item mb-1">
                        <a href="{{ route('admin.categories.index') }}"
                           class="nav-link sidebar-link {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}">
                            <span class="sidebar-icon">
                                <i class="fa-solid fa-tags"></i>
                            </span>
                            <span>Categories</span>
                        </a>
                    </li>
                    <li class="nav-item mb-1">
                        <a href="{{ route('admin.users.index') }}"
                           class="nav-link sidebar-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                            <span class="sidebar-icon">
                                <i class="fa-solid fa-users"></i>
                            </span>
                            <span>Users</span>
                        </a>
                    </li>
                    <li class="nav-item mb-1">
                        <a href="{{ route('admin.insights') }}"
                           class="nav-link sidebar-link {{ request()->routeIs('admin.insights') ? 'active' : '' }}">
                            <span class="sidebar-icon">
                                <i class="fa-solid fa-chart-line"></i>
                            </span>
                            <span>Insights</span>
                        </a>
                    </li>
                    <li class="nav-item mb-1">
                        <a href="{{ route('admin.whatsapp.campaigns.index') }}"
                           class="nav-link sidebar-link {{ request()->routeIs('admin.whatsapp.campaigns.*') ? 'active' : '' }}">
                            <span class="sidebar-icon">
                                <i class="fa-brands fa-whatsapp"></i>
                            </span>
                            <span>WhatsApp Offers</span>
                        </a>
                    </li>
                </ul>

                <div class="sidebar-section-label px-1">Delivery</div>
                <ul class="nav flex-column sidebar-nav">
                    <li class="nav-item mb-1">
                        <a href="{{ route('admin.delivery.shipments') }}"
                           class="nav-link sidebar-link {{ request()->routeIs('admin.delivery.shipments') ? 'active' : '' }}">
                            <span class="sidebar-icon">
                                <i class="fa-solid fa-truck"></i>
                            </span>
                            <span>Shipments</span>
                        </a>
                    </li>
                    <li class="nav-item mb-1">
                        <a href="{{ route('admin.delivery.pickups') }}"
                           class="nav-link sidebar-link {{ request()->routeIs('admin.delivery.pickups') ? 'active' : '' }}">
                            <span class="sidebar-icon">
                                <i class="fa-solid fa-truck-ramp-box"></i>
                            </span>
                            <span>Pickups</span>
                        </a>
                    </li>
                    <li class="nav-item mb-1">
                        <a href="{{ route('admin.delivery.serviceability') }}"
                           class="nav-link sidebar-link {{ request()->routeIs('admin.delivery.serviceability') ? 'active' : '' }}">
                            <span class="sidebar-icon">
                                <i class="fa-solid fa-location-dot"></i>
                            </span>
                            <span>Serviceability</span>
                        </a>
                    </li>
                    <li class="nav-item mb-1 mt-2">
                        <a href="{{ route('admin.settings.edit') }}"
                           class="nav-link sidebar-link {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
                            <span class="sidebar-icon">
                                <i class="fa-solid fa-gear"></i>
                            </span>
                            <span>Settings</span>
                        </a>
                    </li>
                </ul>
            </div>
        </nav>

        <!-- Main Content -->
        <div class="main-layout d-flex flex-column">
            <!-- Header -->
            <header class="app-header py-3 px-4">
                <div class="app-header-inner d-flex align-items-center justify-content-between gap-3">
                    <div class="app-header-search">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        <input type="search" placeholder="Search orders, products, users..." aria-label="Search">
                    </div>
                    <div class="d-flex align-items-center gap-2 app-header-actions">
                        <button type="button" class="icon-btn" title="Notifications">
                            <i class="fa-regular fa-bell"></i>
                        </button>
                        <button type="button" class="icon-btn" title="Help">
                            <i class="fa-regular fa-circle-question"></i>
                        </button>
                        <div class="dropdown">
                            @guest
                                @if (Route::has('login'))
                                    <a class="btn btn-sm btn-outline-secondary me-2" href="{{ route('login') }}">Login</a>
                                @endif
                                @if (Route::has('register'))
                                    <a class="btn btn-sm btn-primary" href="{{ route('register') }}">Register</a>
                                @endif
                            @else
                                <button class="btn btn-sm btn-light d-flex align-items-center gap-2" type="button" id="userMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                                    <span class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center" style="width: 28px; height: 28px; font-size: 0.8rem;">
                                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                                    </span>
                                    <span style="font-size: 0.9rem;">{{ Auth::user()->name }}</span>
                                    <i class="fa-solid fa-chevron-down" style="font-size: 0.65rem;"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userMenuButton">
                                    <li class="px-3 py-2 small text-muted">{{ Auth::user()->email }}</li>
                                    <li><hr class="dropdown-divider"></li>
                                    @if (Route::has('profile.show'))
                                        <li>
                                            <a class="dropdown-item" href="{{ route('profile.show') }}">
                                                <i class="fa-regular fa-user me-1"></i> Profile
                                            </a>
                                        </li>
                                    @endif
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <a class="dropdown-item text-danger" href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                            <i class="fa-solid fa-arrow-right-from-bracket me-1"></i> Logout
                                        </a>
                                    </li>
                                </ul>
                                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">@csrf</form>
                            @endguest
                        </div>
                    </div>
                </div>
            </header>
            <main class="flex-grow-1 p-4">
                @yield('content')
            </main>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const userMenuButton = document.getElementById('userMenuButton');
            const userMenu = document.querySelector('.dropdown-menu[aria-labelledby="userMenuButton"]');

            if (!userMenuButton || !userMenu) {
                return;
            }

            userMenuButton.addEventListener('click', function (event) {
                event.preventDefault();
                event.stopPropagation();
                userMenu.classList.toggle('show');
            });

            document.addEventListener('click', function () {
                userMenu.classList.remove('show');
            });

            // Auto-scroll sidebar to show active menu item
            const sidebarScroll = document.querySelector('.sidebar-menu-scroll');
            if (sidebarScroll) {
                const activeLink = sidebarScroll.querySelector('.sidebar-link.active');
                if (activeLink) {
                    activeLink.scrollIntoView({ block: 'center' });
                }
            }
        });
    </script>
</body>
</html>

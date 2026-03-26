<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <div id="app" class="d-flex" style="min-height: 100vh;">
        <!-- Sidebar -->
        <nav class="sidebar bg-dark text-white p-3" style="width: 240px; min-height: 100vh;">
            <div class="mb-4 text-center">
                <a href="/" class="d-block mb-2">
                    <img src="https://ui-avatars.com/api/?name=Arun+Naturals&background=0D8ABC&color=fff" alt="Logo" style="width: 60px; height: 60px; border-radius: 50%;">
                </a>
                <h4 style="font-weight: bold;">Arun Naturals</h4>
            </div>
            <ul class="nav flex-column">
                <li class="nav-item mb-2"><a href="{{ route('admin.orders.index') }}" class="nav-link text-white">Orders</a></li>
                <li class="nav-item mb-2"><a href="{{ route('admin.users.index') }}" class="nav-link text-white">Users</a></li>
                <li class="nav-item mb-2"><a href="{{ route('admin.insights') }}" class="nav-link text-white">Insights</a></li>
                <li class="nav-item mb-2"><a class="nav-link text-white" href="{{ route('admin.products.index') }}">Products</a></li>

                <li class="nav-item mb-2">
                    <div class="dropdown">
                        <a class="nav-link text-white dropdown-toggle" href="#" id="deliveryDropdown" data-bs-toggle="dropdown" aria-expanded="false">Delivery</a>
                        <ul class="dropdown-menu bg-dark" aria-labelledby="deliveryDropdown">
                            <li><a class="dropdown-item text-white bg-dark" href="#">Create</a></li>
                            <li><a class="dropdown-item text-white bg-dark" href="#">Update</a></li>
                            <li><a class="dropdown-item text-white bg-dark" href="#">Cancel</a></li>
                            <li><a class="dropdown-item text-white bg-dark" href="#">Pickup</a></li>
                        </ul>
                    </div>
                </li>
            </ul>
        </nav>

        <!-- Main Content -->
        <div class="flex-grow-1 d-flex flex-column">
            <!-- Header -->
            <header class="bg-white shadow-sm p-3 d-flex align-items-center justify-content-between">
                <form class="d-flex" style="max-width: 400px; width: 100%;">
                    <input class="form-control me-2" type="search" placeholder="Search..." aria-label="Search">
                    <button class="btn btn-outline-primary" type="submit">Search</button>
                </form>
                <div>
                    @guest
                    @if (Route::has('login'))
                    <a class="btn btn-outline-secondary me-2" href="{{ route('login') }}">Login</a>
                    @endif
                    @if (Route::has('register'))
                    <a class="btn btn-primary" href="{{ route('register') }}">Register</a>
                    @endif
                    @else
                    <span class="me-3">{{ Auth::user()->name }}</span>
                    <a class="btn btn-outline-danger" href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Logout</a>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">@csrf</form>
                    @endguest
                </div>
            </header>
            <main class="flex-grow-1 p-4">
                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>

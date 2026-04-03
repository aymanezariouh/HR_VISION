<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'HRVision')</title>
    <link rel="stylesheet" href="{{ asset('css/hrvision.css') }}?v={{ filemtime(public_path('css/hrvision.css')) }}">
</head>
<body>
    @auth
        @php
            $currentUser = auth()->user();

            $menuLinks = [
                [
                    'label' => 'Dashboard',
                    'short' => 'DB',
                    'url' => route('dashboard'),
                    'active' => request()->routeIs('dashboard'),
                ],
                [
                    'label' => 'Salaries',
                    'short' => '$',
                    'url' => route('blade.salaries.index'),
                    'active' => request()->routeIs('blade.salaries.*'),
                ],
            ];

            if ($currentUser->hasAnyRole(['admin', 'hr'])) {
                array_splice($menuLinks, 1, 0, [[
                    'label' => 'Employees',
                    'short' => 'EM',
                    'url' => route('blade.employees.index'),
                    'active' => request()->routeIs('blade.employees.*'),
                ]]);
            }

            $menuLinks[] = [
                'label' => 'Expenses',
                'short' => 'EX',
                'url' => $currentUser->hasAnyRole(['admin', 'hr'])
                    ? route('blade.expenses.pending')
                    : route('blade.expenses.index'),
                'active' => request()->routeIs('blade.expenses.*'),
            ];

            $menuLinks[] = [
                'label' => 'Documents',
                'short' => 'DC',
                'url' => $currentUser->hasAnyRole(['admin', 'hr'])
                    ? route('blade.documents.index')
                    : route('blade.documents.mine'),
                'active' => request()->routeIs('blade.documents.*'),
            ];

            if ($currentUser->hasRole('admin')) {
                $menuLinks[] = [
                    'label' => 'Admin',
                    'short' => 'AD',
                    'url' => route('admin.index'),
                    'active' => request()->routeIs('admin.index', 'blade.admin.*'),
                ];
            }
        @endphp

        <div class="drawer-backdrop" id="drawerBackdrop"></div>

        <aside class="side-drawer" id="sideDrawer" aria-hidden="true">
            <div class="drawer-header">
                <a class="drawer-brand" href="{{ route('dashboard') }}">
                    <img src="{{ asset('images/HR-VISION.png') }}" alt="HRVision" class="drawer-logo">
                </a>

                <button type="button" class="drawer-close" id="closeDrawerButton" aria-label="Close menu">
                    &times;
                </button>
            </div>

            <nav class="drawer-nav" aria-label="Main navigation">
                @foreach($menuLinks as $menuLink)
                    <a href="{{ $menuLink['url'] }}" class="drawer-link {{ $menuLink['active'] ? 'active-link' : '' }}">
                        <span class="drawer-link-icon">{{ $menuLink['short'] }}</span>
                        <span>{{ $menuLink['label'] }}</span>
                    </a>
                @endforeach
            </nav>
        </aside>

        <header class="topbar">
            <div class="topbar-inner">
                <div class="brand-wrap">
                    <button type="button" class="menu-toggle" id="openDrawerButton" aria-label="Open menu">
                        <span></span>
                        <span></span>
                        <span></span>
                    </button>

                    <a class="brand" href="{{ route('dashboard') }}">
                        <img src="{{ asset('images/HR-VISION.png') }}" alt="HRVision" class="brand-logo">
                    </a>
                </div>

                <nav class="nav-links">
                    @foreach($menuLinks as $menuLink)
                        <a href="{{ $menuLink['url'] }}" class="{{ $menuLink['active'] ? 'active-link' : '' }}">
                            {{ $menuLink['label'] }}
                        </a>
                    @endforeach
                </nav>

                <div class="topbar-actions">
                    <div class="user-chip">
                        <div class="user-avatar">{{ strtoupper(substr($currentUser->name, 0, 1)) }}</div>
                        <div class="user-meta">
                            <strong>{{ $currentUser->name }}</strong>
                            <span>{{ ucfirst($currentUser->role) }}</span>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="logout-button">Logout</button>
                    </form>
                </div>
            </div>
        </header>
    @endauth

    <main class="page-wrap">
        @if(session('success'))
            <div class="success-box">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="error-box">
                {{ session('error') }}
            </div>
        @endif

        @yield('content')
    </main>

    @auth
        <script>
            const body = document.body;
            const openDrawerButton = document.getElementById('openDrawerButton');
            const closeDrawerButton = document.getElementById('closeDrawerButton');
            const drawerBackdrop = document.getElementById('drawerBackdrop');
            const sideDrawer = document.getElementById('sideDrawer');

            function openDrawer() {
                body.classList.add('drawer-open');
                sideDrawer.setAttribute('aria-hidden', 'false');
            }

            function closeDrawer() {
                body.classList.remove('drawer-open');
                sideDrawer.setAttribute('aria-hidden', 'true');
            }

            openDrawerButton.addEventListener('click', openDrawer);
            closeDrawerButton.addEventListener('click', closeDrawer);
            drawerBackdrop.addEventListener('click', closeDrawer);
        </script>
    @endauth
</body>
</html>

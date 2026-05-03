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

            $menuSections = [
                [
                    'label' => 'Workspace',
                    'items' => [[
                        'label' => 'Dashboard',
                        'icon' => 'dashboard',
                        'url' => route('dashboard'),
                        'active' => request()->routeIs('dashboard'),
                    ]],
                ],
                [
                    'label' => 'People',
                    'items' => [],
                ],
                [
                    'label' => 'Operations',
                    'items' => [[
                        'label' => 'Salaries',
                        'icon' => 'wallet',
                        'url' => route('blade.salaries.index'),
                        'active' => request()->routeIs('blade.salaries.*'),
                    ], [
                        'label' => 'Expenses',
                        'icon' => 'receipt',
                        'url' => $currentUser->hasAnyRole(['admin', 'hr'])
                            ? route('blade.expenses.pending')
                            : route('blade.expenses.index'),
                        'active' => request()->routeIs('blade.expenses.*'),
                    ], [
                        'label' => 'Documents',
                        'icon' => 'folder',
                        'url' => $currentUser->hasAnyRole(['admin', 'hr'])
                            ? route('blade.documents.index')
                            : route('blade.documents.mine'),
                        'active' => request()->routeIs('blade.documents.*'),
                    ]],
                ],
            ];

            if ($currentUser->hasAnyRole(['admin', 'hr'])) {
                $menuSections[1]['items'][] = [
                    'label' => 'Employees',
                    'icon' => 'users',
                    'url' => route('blade.employees.index'),
                    'active' => request()->routeIs('blade.employees.*'),
                ];
            }

            if ($currentUser->hasRole('admin')) {
                $menuSections[] = [
                    'label' => 'Administration',
                    'items' => [[
                        'label' => 'Admin',
                        'icon' => 'settings',
                        'url' => route('admin.index'),
                        'active' => request()->routeIs('admin.index', 'blade.admin.*'),
                    ]],
                ];
            }

            $topbarLinks = collect($menuSections)
                ->flatMap(fn ($section) => $section['items'])
                ->values()
                ->all();
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
                @foreach($menuSections as $menuSection)
                    @continue(empty($menuSection['items']))

                    <div class="drawer-group">
                        <p class="drawer-group-label">{{ $menuSection['label'] }}</p>

                        <div class="drawer-group-links">
                            @foreach($menuSection['items'] as $menuLink)
                                <a href="{{ $menuLink['url'] }}" class="drawer-link {{ $menuLink['active'] ? 'active-link' : '' }}">
                                    <span class="drawer-link-icon" aria-hidden="true">
                                        @switch($menuLink['icon'])
                                            @case('dashboard')
                                                <svg viewBox="0 0 24 24" fill="none">
                                                    <path d="M4 4h7v7H4V4Zm9 0h7v5h-7V4ZM4 13h7v7H4v-7Zm9-2h7v9h-7v-9Z" />
                                                </svg>
                                                @break
                                            @case('users')
                                                <svg viewBox="0 0 24 24" fill="none">
                                                    <path d="M8 11a3 3 0 1 0 0-6 3 3 0 0 0 0 6Zm8 1a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5ZM3 19a5 5 0 0 1 10 0M14 19a4 4 0 0 1 7 0" />
                                                </svg>
                                                @break
                                            @case('wallet')
                                                <svg viewBox="0 0 24 24" fill="none">
                                                    <path d="M4 7.5A2.5 2.5 0 0 1 6.5 5H18a2 2 0 0 1 2 2v2H6.5A2.5 2.5 0 0 0 4 11.5v-4ZM4 12.5A2.5 2.5 0 0 1 6.5 10H20v7a2 2 0 0 1-2 2H6.5A2.5 2.5 0 0 1 4 16.5v-4Zm12 2.5h2" />
                                                </svg>
                                                @break
                                            @case('receipt')
                                                <svg viewBox="0 0 24 24" fill="none">
                                                    <path d="M7 4h10v16l-2-1.5L12 20l-3-1.5L7 20V4Zm3 5h4M10 12h4" />
                                                </svg>
                                                @break
                                            @case('folder')
                                                <svg viewBox="0 0 24 24" fill="none">
                                                    <path d="M4 7.5A2.5 2.5 0 0 1 6.5 5H10l2 2h5.5A2.5 2.5 0 0 1 20 9.5v7A2.5 2.5 0 0 1 17.5 19h-11A2.5 2.5 0 0 1 4 16.5v-9Z" />
                                                </svg>
                                                @break
                                            @default
                                                <svg viewBox="0 0 24 24" fill="none">
                                                    <path d="M12 3v4M12 17v4M4 12H8M16 12h4M6.5 6.5l2.8 2.8M14.7 14.7l2.8 2.8M17.5 6.5l-2.8 2.8M9.3 14.7l-2.8 2.8" />
                                                </svg>
                                        @endswitch
                                    </span>
                                    <span>{{ $menuLink['label'] }}</span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </nav>

            <div class="drawer-footer">
                <span>{{ $currentUser->isSuperAdmin() ? 'Super Admin Workspace' : ucfirst($currentUser->role).' Workspace' }}</span>
                <strong>{{ $currentUser->email }}</strong>
            </div>
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
                    @foreach($topbarLinks as $menuLink)
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
                            <span>{{ $currentUser->isSuperAdmin() ? 'Super Admin' : ucfirst($currentUser->role) }}</span>
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

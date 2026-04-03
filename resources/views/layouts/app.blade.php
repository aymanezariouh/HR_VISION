<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'HRVision')</title>
    <link rel="stylesheet" href="{{ asset('css/hrvision.css') }}">
</head>
<body>
    @auth
        @php($currentUser = auth()->user())
        <header class="topbar">
            <a class="brand" href="{{ route('dashboard') }}">HRVision</a>

            <nav class="nav-links">
                <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active-link' : '' }}">Dashboard</a>

                @if($currentUser->hasAnyRole(['admin', 'hr']))
                    <a href="{{ route('blade.employees.index') }}" class="{{ request()->routeIs('blade.employees.*') ? 'active-link' : '' }}">Employees</a>
                @endif

                <a href="{{ route('blade.salaries.index') }}" class="{{ request()->routeIs('blade.salaries.*') ? 'active-link' : '' }}">Salaries</a>

                @if($currentUser->hasAnyRole(['admin', 'hr']))
                    <a href="{{ route('blade.expenses.pending') }}" class="{{ request()->routeIs('blade.expenses.*') ? 'active-link' : '' }}">Expenses</a>
                @else
                    <a href="{{ route('blade.expenses.index') }}" class="{{ request()->routeIs('blade.expenses.*') ? 'active-link' : '' }}">Expenses</a>
                @endif

                @if($currentUser->hasAnyRole(['admin', 'hr']))
                    <a href="{{ route('blade.documents.index') }}" class="{{ request()->routeIs('blade.documents.*') ? 'active-link' : '' }}">Documents</a>
                @else
                    <a href="{{ route('blade.documents.mine') }}" class="{{ request()->routeIs('blade.documents.*') ? 'active-link' : '' }}">Documents</a>
                @endif

                @if($currentUser->hasRole('admin'))
                    <a href="{{ route('admin.index') }}" class="{{ request()->routeIs('admin.index', 'blade.admin.*') ? 'active-link' : '' }}">Admin</a>
                @endif
            </nav>

            <div class="topbar-actions">
                <div class="user-chip">
                    <strong>{{ $currentUser->name }}</strong>
                    <span>{{ ucfirst($currentUser->role) }}</span>
                </div>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="logout-button">Logout</button>
                </form>
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
</body>
</html>

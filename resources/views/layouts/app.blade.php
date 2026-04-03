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
                <a href="{{ route('dashboard') }}">Dashboard</a>

                @if($currentUser->hasAnyRole(['admin', 'hr']))
                    <a href="{{ route('blade.employees.index') }}">Employees</a>
                @endif

                <a href="{{ route('blade.salaries.index') }}">Salaries</a>

                @if($currentUser->hasAnyRole(['admin', 'hr']))
                    <a href="{{ route('blade.expenses.pending') }}">Expenses</a>
                @else
                    <a href="{{ route('blade.expenses.index') }}">Expenses</a>
                @endif

                <a href="{{ route('documents.index') }}">Documents</a>

                @if($currentUser->hasRole('admin'))
                    <a href="{{ route('admin.index') }}">Admin</a>
                @endif
            </nav>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="logout-button">Logout</button>
            </form>
        </header>
    @endauth

    <main class="page-wrap">
        @if(session('success'))
            <div class="success-box">
                {{ session('success') }}
            </div>
        @endif

        @yield('content')
    </main>
</body>
</html>

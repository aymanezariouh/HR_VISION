@extends('layouts.app')

@section('title', 'Dashboard | HRVision')

@section('content')
    <section class="dashboard-hero">
        <div>
            <p class="small-label">Dashboard</p>
            <h1>Welcome back, {{ $user->name }}</h1>
            <p class="muted-text">Your role: {{ ucfirst($user->role) }}. Use the shortcuts below to continue your work.</p>
        </div>
    </section>

    <section class="stats-grid">
        @foreach($stats as $card)
            <article class="stat-card">
                <span>{{ $card['label'] }}</span>
                <strong>{{ $card['value'] }}</strong>
                <p>{{ $card['note'] }}</p>
            </article>
        @endforeach
    </section>

    <section class="content-card">
        <h2>Quick Access</h2>
        <div class="button-row">
            @if($user->hasAnyRole(['admin', 'hr']))
                <a href="{{ route('blade.employees.index') }}" class="main-button button-link">Employees</a>
            @endif

            <a href="{{ route('blade.salaries.index') }}" class="light-button button-link">Salaries</a>

            @if($user->hasAnyRole(['admin', 'hr']))
                <a href="{{ route('blade.expenses.pending') }}" class="light-button button-link">Expenses</a>
            @else
                <a href="{{ route('blade.expenses.index') }}" class="light-button button-link">Expenses</a>
            @endif

            @if($user->hasAnyRole(['admin', 'hr']))
                <a href="{{ route('blade.documents.index') }}" class="light-button button-link">Documents</a>
            @else
                <a href="{{ route('blade.documents.mine') }}" class="light-button button-link">Documents</a>
            @endif

            @if($user->hasRole('admin'))
                <a href="{{ route('admin.index') }}" class="light-button button-link">Admin</a>
            @endif
        </div>
    </section>
@endsection

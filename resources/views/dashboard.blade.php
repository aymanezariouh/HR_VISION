@extends('layouts.app')

@section('title', 'Dashboard | HRVision')

@section('content')
    <section class="dashboard-hero">
        <div>
            <p class="small-label">Dashboard</p>
            <h1>Welcome back, {{ $user->name }}</h1>
            <p class="muted-text">Your role: {{ ucfirst($user->role) }}. Use the shortcuts below to continue your work.</p>
        </div>

        <div class="hero-panel">
            <span class="hero-label">Workspace</span>
            <strong>{{ ucfirst($user->role) }} Portal</strong>
            <p>Everything you need for daily HR operations in one place.</p>
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
        <div class="section-heading">
            <div>
                <h2>Quick Access</h2>
                <p class="muted-text">Jump directly to the modules you use most.</p>
            </div>
        </div>

        <div class="quick-links-grid">
            @if($user->hasAnyRole(['admin', 'hr']))
                <a href="{{ route('blade.employees.index') }}" class="quick-link-card">
                    <span>Employees</span>
                    <strong>Manage employee records</strong>
                </a>
            @endif

            <a href="{{ route('blade.salaries.index') }}" class="quick-link-card">
                <span>Salaries</span>
                <strong>Review payroll history</strong>
            </a>

            @if($user->hasAnyRole(['admin', 'hr']))
                <a href="{{ route('blade.expenses.pending') }}" class="quick-link-card">
                    <span>Expenses</span>
                    <strong>Approve pending requests</strong>
                </a>
            @else
                <a href="{{ route('blade.expenses.index') }}" class="quick-link-card">
                    <span>Expenses</span>
                    <strong>Track submitted expenses</strong>
                </a>
            @endif

            @if($user->hasAnyRole(['admin', 'hr']))
                <a href="{{ route('blade.documents.index') }}" class="quick-link-card">
                    <span>Documents</span>
                    <strong>Open employee documents</strong>
                </a>
            @else
                <a href="{{ route('blade.documents.mine') }}" class="quick-link-card">
                    <span>Documents</span>
                    <strong>Download your documents</strong>
                </a>
            @endif

            @if($user->hasRole('admin'))
                <a href="{{ route('admin.index') }}" class="quick-link-card">
                    <span>Admin</span>
                    <strong>Manage users and settings</strong>
                </a>
            @endif
        </div>
    </section>
@endsection

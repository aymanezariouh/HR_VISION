@extends('layouts.app')

@section('title', 'Dashboard | HRVision')

@section('content')
    <section class="page-head">
        <div>
            <p class="small-label">Dashboard</p>
            <h1>Welcome back, {{ $user->name }}</h1>
            <p class="muted-text">Your role: {{ ucfirst($user->role) }}</p>
        </div>
    </section>

    <section class="stats-grid">
        <article class="stat-card">
            <span>Employees</span>
            <strong>{{ $stats['employees'] }}</strong>
        </article>

        <article class="stat-card">
            <span>Departments</span>
            <strong>{{ $stats['departments'] }}</strong>
        </article>

        <article class="stat-card">
            <span>Salaries</span>
            <strong>{{ $stats['salaries'] }}</strong>
        </article>

        <article class="stat-card">
            <span>Expenses</span>
            <strong>{{ $stats['expenses'] }}</strong>
        </article>

        <article class="stat-card">
            <span>Documents</span>
            <strong>{{ $stats['documents'] }}</strong>
        </article>
    </section>

    <section class="content-card">
        <h2>Quick Access</h2>
        <div class="button-row">
            @if($user->hasAnyRole(['admin', 'hr']))
                <a href="{{ route('blade.employees.index') }}" class="main-button button-link">Employees</a>
            @endif

            <a href="{{ route('blade.salaries.index') }}" class="light-button button-link">Salaries</a>
            <a href="{{ route('expenses.index') }}" class="light-button button-link">Expenses</a>
            <a href="{{ route('documents.index') }}" class="light-button button-link">Documents</a>

            @if($user->hasRole('admin'))
                <a href="{{ route('admin.index') }}" class="light-button button-link">Admin</a>
            @endif
        </div>
    </section>
@endsection

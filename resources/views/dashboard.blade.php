@extends('layouts.app')

@section('title', 'Dashboard | HRVision')

@section('content')
    <section class="page-head dashboard-head">
        <div>
            <div class="breadcrumb-row">
                <a href="{{ route('dashboard') }}">Home</a>
                <span>/</span>
                <span>Dashboard</span>
            </div>

            <p class="small-label">Dashboard</p>
            <h1>Welcome back, {{ $user->name }}</h1>
            <p class="muted-text">Your role: {{ $user->isSuperAdmin() ? 'Super Admin' : ucfirst($user->role) }}. Use the shortcuts below to continue your work.</p>
        </div>

        <div class="dashboard-head-status">
            <span class="small-label">Live workspace</span>
            <strong>{{ count($stats) }} active modules</strong>
            <p>{{ $user->hasAnyRole(['admin', 'hr']) ? 'Operations and team modules are available.' : 'Your personal payroll, expense, and document space is ready.' }}</p>
        </div>
    </section>

    <section class="kpi-grid">
        @foreach($stats as $card)
            <article class="kpi-card tone-{{ ($loop->index % 4) + 1 }}">
                <div class="kpi-top">
                    <div class="kpi-meta">
                        <strong>{{ $card['value'] }}</strong>
                        <span>{{ $card['label'] }}</span>
                    </div>
                </div>

                <p>{{ $card['note'] }}</p>
            </article>
        @endforeach
    </section>

    <section class="dashboard-grid">
        <article class="dashboard-panel overview-panel">
            <div class="section-heading">
                <div>
                    <h2>Workspace Overview</h2>
                    <p class="muted-text">A direct summary of the modules currently available in your workspace.</p>
                </div>
            </div>

            <div class="overview-list">
                @foreach($stats as $card)
                    <div class="overview-row">
                        <div class="overview-copy">
                            <span>{{ $card['label'] }}</span>
                            <p>{{ $card['note'] }}</p>
                        </div>
                        <strong>{{ $card['value'] }}</strong>
                    </div>
                @endforeach
            </div>

            <div class="dashboard-note">
                <strong>{{ $user->hasAnyRole(['admin', 'hr']) ? 'Admin and HR tools are grouped here for daily operations.' : 'This workspace focuses on your employee documents, expenses, and salary history.' }}</strong>
                <p>Use the quick access cards on the right to open the exact section you need.</p>
            </div>
        </article>

        <aside class="dashboard-side">
            <section class="content-card compact-panel">
                <div class="section-heading">
                    <div>
                        <h2>Workspace</h2>
                        <p class="muted-text">Current access level</p>
                    </div>
                </div>

                <div class="workspace-role">
                    <span class="small-label">{{ $user->isSuperAdmin() ? 'Super Admin' : ucfirst($user->role) }}</span>
                    <strong>{{ $user->name }}</strong>
                    <p>{{ $user->email }}</p>
                </div>

                <div class="workspace-checklist">
                    <div>
                        <span>Authentication</span>
                        <strong>Protected</strong>
                    </div>
                    <div>
                        <span>Role Scope</span>
                        <strong>{{ $user->isSuperAdmin() ? 'Full Control' : ucfirst($user->role) }}</strong>
                    </div>
                </div>
            </section>

            <section class="content-card compact-panel">
                <div class="section-heading">
                    <div>
                        <h2>Quick Access</h2>
                        <p class="muted-text">Jump directly to the modules you use most.</p>
                    </div>
                </div>

                <div class="quick-links-grid dashboard-links">
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

            <section class="content-card compact-panel">
                <div class="section-heading">
                    <div>
                        <h2>System Notes</h2>
                        <p class="muted-text">Workspace rules that apply to your current role.</p>
                    </div>
                </div>

                <div class="system-notes">
                    <div class="system-note">
                        <span>Security</span>
                        <strong>Authenticated session required for every protected route.</strong>
                    </div>

                    <div class="system-note">
                        <span>Permissions</span>
                        <strong>{{ $user->isSuperAdmin() ? 'You can manage users, roles, and admin settings.' : 'Your access is limited to modules assigned to your role.' }}</strong>
                    </div>
                </div>
            </section>
        </aside>
    </section>
@endsection

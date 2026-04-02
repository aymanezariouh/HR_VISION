@extends('layouts.app')

@section('title', 'Employees | HRVision')

@section('content')
    <section class="page-head">
        <div>
            <p class="small-label">Employee Management</p>
            <h1>Employees</h1>
            <p class="muted-text">Search, filter, and edit employee records.</p>
        </div>

        <a href="{{ route('blade.employees.create') }}" class="main-button button-link">Add Employee</a>
    </section>

    <section class="content-card">
        <form method="GET" action="{{ route('blade.employees.index') }}" class="filter-grid">
            <label class="field-block">
                <span>Search</span>
                <input type="text" name="search" value="{{ $filters['search'] }}" placeholder="name, email, or phone">
            </label>

            <label class="field-block">
                <span>Department</span>
                <select name="department_id">
                    <option value="">All departments</option>
                    @foreach($departments as $department)
                        <option value="{{ $department->id }}" @selected((string) $filters['department_id'] === (string) $department->id)>
                            {{ $department->name }}
                        </option>
                    @endforeach
                </select>
            </label>

            <label class="field-block">
                <span>Status</span>
                <select name="status">
                    <option value="">All statuses</option>
                    <option value="active" @selected($filters['status'] === 'active')>active</option>
                    <option value="inactive" @selected($filters['status'] === 'inactive')>inactive</option>
                </select>
            </label>

            <div class="button-row filter-actions">
                <button type="submit" class="main-button">Apply Filters</button>
                <a href="{{ route('blade.employees.index') }}" class="light-button button-link">Reset</a>
            </div>
        </form>
    </section>

    <section class="content-card">
        @if($employees->count() === 0)
            <p class="muted-text">No employees found.</p>
        @else
            <div class="employee-list">
                @foreach($employees as $employee)
                    <article class="employee-card">
                        <div class="employee-top">
                            <div>
                                <h2>{{ $employee->name }}</h2>
                                <p>{{ $employee->professional_email }}</p>
                            </div>

                            <span class="status-badge {{ $employee->status }}">{{ $employee->status }}</span>
                        </div>

                        <div class="employee-details">
                            <p><strong>User:</strong> {{ $employee->user?->email ?? 'N/A' }}</p>
                            <p><strong>Phone:</strong> {{ $employee->phone }}</p>
                            <p><strong>Department:</strong> {{ $employee->department?->name ?? 'N/A' }}</p>
                            <p><strong>Position:</strong> {{ $employee->position }}</p>
                            <p><strong>Hire Date:</strong> {{ $employee->hire_date?->toDateString() }}</p>
                            <p><strong>Contract:</strong> {{ $employee->contract_type }}</p>
                        </div>

                        <div class="card-actions">
                            <a href="{{ route('blade.employees.edit', $employee) }}" class="light-button button-link">Edit</a>

                            <form method="POST" action="{{ route('blade.employees.deactivate', $employee) }}">
                                @csrf
                                @method('PATCH')
                                <button
                                    type="submit"
                                    class="danger-button"
                                    @disabled($employee->status === 'inactive')
                                >
                                    Deactivate
                                </button>
                            </form>
                        </div>
                    </article>
                @endforeach
            </div>

            <div class="pagination-wrap">
                {{ $employees->links() }}
            </div>
        @endif
    </section>
@endsection

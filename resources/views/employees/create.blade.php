@extends('layouts.app')

@section('title', 'Create Employee | HRVision')

@section('content')
    <section class="page-head">
        <div>
            <p class="small-label">Employee Management</p>
            <h1>Create Employee</h1>
            <p class="muted-text">Create a new employee record and link it to an employee user account.</p>
        </div>

        <a href="{{ route('blade.employees.index') }}" class="light-button button-link">Back</a>
    </section>

    <section class="content-card">
        @if($employeeUsers->count() === 0)
            <div class="error-box">
                No available employee users found. Create an employee user account first.
            </div>
        @endif

        <form method="POST" action="{{ route('blade.employees.store') }}" class="employee-form">
            @csrf
            @include('employees.form', [
                'employee' => null,
                'departments' => $departments,
                'employeeUsers' => $employeeUsers,
            ])

            <div class="button-row">
                <button type="submit" class="main-button" @disabled($employeeUsers->count() === 0)>Create Employee</button>
                <a href="{{ route('blade.employees.index') }}" class="light-button button-link">Cancel</a>
            </div>
        </form>
    </section>
@endsection

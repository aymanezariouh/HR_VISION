@extends('layouts.app')

@section('title', 'Edit Employee | HRVision')

@section('content')
    <section class="page-head">
        <div>
            <p class="small-label">Employee Management</p>
            <h1>Edit Employee</h1>
            <p class="muted-text">Update the employee details for {{ $employee->name }}.</p>
        </div>

        <a href="{{ route('blade.employees.index') }}" class="light-button button-link">Back</a>
    </section>

    <section class="content-card">
        <div class="linked-user-box">
            <strong>Linked User:</strong>
            <span>{{ $employee->user?->name }} ({{ $employee->user?->email }})</span>
        </div>

        <form method="POST" action="{{ route('blade.employees.update', $employee) }}" class="employee-form">
            @csrf
            @method('PUT')

            @include('employees.form', [
                'employee' => $employee,
                'departments' => $departments,
                'employeeUsers' => collect(),
            ])

            <div class="button-row">
                <button type="submit" class="main-button">Save Changes</button>
                <a href="{{ route('blade.employees.index') }}" class="light-button button-link">Cancel</a>
            </div>
        </form>
    </section>
@endsection

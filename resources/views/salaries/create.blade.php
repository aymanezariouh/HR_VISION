@extends('layouts.app')

@section('title', 'Create Salary | HRVision')

@section('content')
    <section class="page-head">
        <div>
            <p class="small-label">Salary Management</p>
            <h1>Create Salary</h1>
            <p class="muted-text">Add a monthly salary record for an employee.</p>
        </div>

        <a href="{{ route('blade.salaries.index') }}" class="light-button button-link">Back</a>
    </section>

    <section class="content-card">
        @if($employees->count() === 0)
            <div class="error-box">
                No employees found. Please create an employee first.
            </div>
        @endif

        <form method="POST" action="{{ route('blade.salaries.store') }}" class="employee-form">
            @csrf

            <label class="field-block full-field">
                <span>Employee</span>
                <select name="employee_id">
                    <option value="">Select employee</option>
                    @foreach($employees as $employee)
                        <option value="{{ $employee->id }}" @selected((string) old('employee_id') === (string) $employee->id)>
                            {{ $employee->name }} - {{ $employee->professional_email }}
                        </option>
                    @endforeach
                </select>
                @error('employee_id')
                    <small class="field-error">{{ $message }}</small>
                @enderror
            </label>

            <label class="field-block">
                <span>Base Salary</span>
                <input type="number" step="0.01" min="0" name="base_salary" value="{{ old('base_salary') }}">
                @error('base_salary')
                    <small class="field-error">{{ $message }}</small>
                @enderror
            </label>

            <label class="field-block">
                <span>Bonuses</span>
                <input type="number" step="0.01" min="0" name="bonuses" value="{{ old('bonuses', 0) }}">
                @error('bonuses')
                    <small class="field-error">{{ $message }}</small>
                @enderror
            </label>

            <label class="field-block">
                <span>Deductions</span>
                <input type="number" step="0.01" min="0" name="deductions" value="{{ old('deductions', 0) }}">
                @error('deductions')
                    <small class="field-error">{{ $message }}</small>
                @enderror
            </label>

            <label class="field-block">
                <span>Month</span>
                <select name="month">
                    <option value="">Select month</option>
                    @for($month = 1; $month <= 12; $month++)
                        <option value="{{ $month }}" @selected((string) old('month') === (string) $month)>
                            {{ $month }}
                        </option>
                    @endfor
                </select>
                @error('month')
                    <small class="field-error">{{ $message }}</small>
                @enderror
            </label>

            <label class="field-block">
                <span>Year</span>
                <input type="number" min="2000" max="2100" name="year" value="{{ old('year', now()->year) }}">
                @error('year')
                    <small class="field-error">{{ $message }}</small>
                @enderror
            </label>

            <div class="button-row full-field">
                <button type="submit" class="main-button" @disabled($employees->count() === 0)>Save Salary</button>
                <a href="{{ route('blade.salaries.index') }}" class="light-button button-link">Cancel</a>
            </div>
        </form>
    </section>
@endsection

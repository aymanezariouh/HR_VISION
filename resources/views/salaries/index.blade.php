@extends('layouts.app')

@section('title', 'Salaries | HRVision')

@section('content')
    <section class="page-head">
        <div>
            <p class="small-label">Salary Management</p>
            <h1>Salary History</h1>
            <p class="muted-text">View salary records and filter by month or year.</p>
        </div>

        @if($currentUser->hasAnyRole(['admin', 'hr']))
            <a href="{{ route('blade.salaries.create') }}" class="main-button button-link">Add Salary</a>
        @endif
    </section>

    <section class="content-card">
        <form method="GET" action="{{ route('blade.salaries.index') }}" class="salary-filter-grid">
            @if($currentUser->hasAnyRole(['admin', 'hr']))
                <label class="field-block">
                    <span>Employee</span>
                    <select name="employee_id">
                        <option value="">Select employee</option>
                        @foreach($employees as $employee)
                            <option value="{{ $employee->id }}" @selected((string) $filters['employee_id'] === (string) $employee->id)>
                                {{ $employee->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('employee_id')
                        <small class="field-error">{{ $message }}</small>
                    @enderror
                </label>
            @endif

            <label class="field-block">
                <span>Month</span>
                <select name="month">
                    <option value="">All months</option>
                    @for($month = 1; $month <= 12; $month++)
                        <option value="{{ $month }}" @selected((string) $filters['month'] === (string) $month)>
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
                <input type="number" name="year" min="2000" max="2100" value="{{ $filters['year'] }}">
                @error('year')
                    <small class="field-error">{{ $message }}</small>
                @enderror
            </label>

            <div class="button-row filter-actions">
                <button type="submit" class="main-button">Apply Filters</button>
                <a href="{{ route('blade.salaries.index') }}" class="light-button button-link">Reset</a>
            </div>
        </form>
    </section>

    <section class="content-card">
        @if(!$selectedEmployee)
            <p class="muted-text">No employee selected.</p>
        @else
            <div class="linked-user-box">
                <strong>Employee:</strong>
                <span>{{ $selectedEmployee->name }} ({{ $selectedEmployee->professional_email }})</span>
            </div>

            @if($salaries->count() === 0)
                <p class="muted-text">No salary records found for this employee.</p>
            @else
                <div class="salary-table-wrap">
                    <table class="salary-table">
                        <thead>
                            <tr>
                                <th>Month</th>
                                <th>Year</th>
                                <th>Base Salary</th>
                                <th>Bonuses</th>
                                <th>Deductions</th>
                                <th>Net Salary</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($salaries as $salary)
                                <tr>
                                    <td>{{ $salary->month }}</td>
                                    <td>{{ $salary->year }}</td>
                                    <td>{{ number_format($salary->base_salary, 2) }}</td>
                                    <td>{{ number_format($salary->bonuses, 2) }}</td>
                                    <td>{{ number_format($salary->deductions, 2) }}</td>
                                    <td><strong>{{ number_format($salary->net_salary, 2) }}</strong></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="pagination-wrap">
                    {{ $salaries->links() }}
                </div>
            @endif
        @endif
    </section>
@endsection

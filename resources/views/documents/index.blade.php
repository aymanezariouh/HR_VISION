@extends('layouts.app')

@section('title', 'Employee Documents | HRVision')

@section('content')
    <section class="page-head">
        <div>
            <p class="small-label">Document Management</p>
            <h1>Employee Documents</h1>
            <p class="muted-text">Select an employee and view uploaded documents.</p>
        </div>

        <a href="{{ route('blade.documents.create') }}" class="main-button button-link">Upload Document</a>
    </section>

    <section class="content-card">
        <form method="GET" action="{{ route('blade.documents.index') }}" class="salary-filter-grid">
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
            </label>

            <div class="button-row filter-actions">
                <button type="submit" class="main-button">View Documents</button>
            </div>
        </form>
    </section>

    <section class="content-card">
        @if(!$selectedEmployee)
            <p class="muted-text">No employee selected.</p>
        @elseif($documents->count() === 0)
            <p class="muted-text">No documents found for {{ $selectedEmployee->name }}.</p>
        @else
            <div class="employee-list">
                @foreach($documents as $document)
                    <article class="employee-card">
                        <div class="employee-top">
                            <div>
                                <h2>{{ $document->title }}</h2>
                                <p>{{ $document->type }}</p>
                            </div>
                        </div>

                        <div class="employee-details">
                            <p><strong>Employee:</strong> {{ $selectedEmployee->name }}</p>
                            <p><strong>Uploaded:</strong> {{ $document->uploaded_at?->format('Y-m-d H:i') }}</p>
                        </div>

                        <a href="{{ route('blade.documents.download', $document) }}" class="main-button button-link">Download</a>
                    </article>
                @endforeach
            </div>

            <div class="pagination-wrap">
                {{ $documents->links() }}
            </div>
        @endif
    </section>
@endsection

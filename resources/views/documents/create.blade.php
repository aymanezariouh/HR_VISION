@extends('layouts.app')

@section('title', 'Upload Document | HRVision')

@section('content')
    <section class="page-head">
        <div>
            <p class="small-label">Document Management</p>
            <h1>Upload Document</h1>
            <p class="muted-text">Upload an administrative document for an employee.</p>
        </div>

        <a href="{{ route('blade.documents.index') }}" class="light-button button-link">Back</a>
    </section>

    <section class="content-card">
        @if($employees->count() === 0)
            <div class="error-box">
                No employees found. Please create an employee first.
            </div>
        @endif

        <form method="POST" action="{{ route('blade.documents.store') }}" enctype="multipart/form-data" class="employee-form">
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
                <span>Title</span>
                <input type="text" name="title" value="{{ old('title') }}">
                @error('title')
                    <small class="field-error">{{ $message }}</small>
                @enderror
            </label>

            <label class="field-block">
                <span>Type</span>
                <input type="text" name="type" value="{{ old('type') }}" placeholder="contract, certificate, attestation">
                @error('type')
                    <small class="field-error">{{ $message }}</small>
                @enderror
            </label>

            <label class="field-block full-field">
                <span>File</span>
                <input type="file" name="file" accept=".pdf,.jpg,.jpeg,.png,.gif,.webp">
                @error('file')
                    <small class="field-error">{{ $message }}</small>
                @enderror
            </label>

            <div class="button-row full-field">
                <button type="submit" class="main-button" @disabled($employees->count() === 0)>Upload Document</button>
                <a href="{{ route('blade.documents.index') }}" class="light-button button-link">Cancel</a>
            </div>
        </form>
    </section>
@endsection

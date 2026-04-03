@extends('layouts.app')

@section('title', 'Create Department | HRVision')

@section('content')
    <section class="page-head">
        <div>
            <p class="small-label">Admin</p>
            <h1>Create Department</h1>
            <p class="muted-text">Add a new department to HRVision.</p>
        </div>

        <a href="{{ route('blade.admin.departments.index') }}" class="light-button button-link">Back</a>
    </section>

    @include('admin.partials.nav')

    <section class="content-card">
        <form method="POST" action="{{ route('blade.admin.departments.store') }}" class="employee-form">
            @csrf

            <label class="field-block full-field">
                <span>Department Name</span>
                <input type="text" name="name" value="{{ old('name') }}">
                @error('name')
                    <small class="field-error">{{ $message }}</small>
                @enderror
            </label>

            <div class="button-row full-field">
                <button type="submit" class="main-button">Create Department</button>
                <a href="{{ route('blade.admin.departments.index') }}" class="light-button button-link">Cancel</a>
            </div>
        </form>
    </section>
@endsection

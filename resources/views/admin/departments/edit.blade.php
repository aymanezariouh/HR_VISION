@extends('layouts.app')

@section('title', 'Edit Department | HRVision')

@section('content')
    <section class="page-head">
        <div>
            <p class="small-label">Admin</p>
            <h1>Edit Department</h1>
            <p class="muted-text">Update the department name.</p>
        </div>

        <a href="{{ route('blade.admin.departments.index') }}" class="light-button button-link">Back</a>
    </section>

    @include('admin.partials.nav')

    <section class="content-card">
        <form method="POST" action="{{ route('blade.admin.departments.update', $department) }}" class="employee-form">
            @csrf
            @method('PUT')

            <label class="field-block full-field">
                <span>Department Name</span>
                <input type="text" name="name" value="{{ old('name', $department->name) }}">
                @error('name')
                    <small class="field-error">{{ $message }}</small>
                @enderror
            </label>

            <div class="button-row full-field">
                <button type="submit" class="main-button">Save Department</button>
                <a href="{{ route('blade.admin.departments.index') }}" class="light-button button-link">Cancel</a>
            </div>
        </form>
    </section>
@endsection

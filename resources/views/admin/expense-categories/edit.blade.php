@extends('layouts.app')

@section('title', 'Edit Expense Category | HRVision')

@section('content')
    <section class="page-head">
        <div>
            <p class="small-label">Admin</p>
            <h1>Edit Expense Category</h1>
            <p class="muted-text">Update the expense category name.</p>
        </div>

        <a href="{{ route('blade.admin.expense-categories.index') }}" class="light-button button-link">Back</a>
    </section>

    @include('admin.partials.nav')

    <section class="content-card">
        <form method="POST" action="{{ route('blade.admin.expense-categories.update', $expenseCategory) }}" class="employee-form">
            @csrf
            @method('PUT')

            <label class="field-block full-field">
                <span>Category Name</span>
                <input type="text" name="name" value="{{ old('name', $expenseCategory->name) }}">
                @error('name')
                    <small class="field-error">{{ $message }}</small>
                @enderror
            </label>

            <div class="button-row full-field">
                <button type="submit" class="main-button">Save Category</button>
                <a href="{{ route('blade.admin.expense-categories.index') }}" class="light-button button-link">Cancel</a>
            </div>
        </form>
    </section>
@endsection

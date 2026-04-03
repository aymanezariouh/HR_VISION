@extends('layouts.app')

@section('title', 'Submit Expense | HRVision')

@section('content')
    <section class="page-head">
        <div>
            <p class="small-label">Expense Management</p>
            <h1>Submit Expense</h1>
            <p class="muted-text">Send a new expense request with your receipt file.</p>
        </div>

        <a href="{{ route('blade.expenses.index') }}" class="light-button button-link">Back</a>
    </section>

    <section class="content-card">
        @if($categories->count() === 0)
            <div class="error-box">
                No active expense categories found.
            </div>
        @endif

        <form method="POST" action="{{ route('blade.expenses.store') }}" enctype="multipart/form-data" class="employee-form">
            @csrf

            <label class="field-block">
                <span>Amount</span>
                <input type="number" step="0.01" min="0.01" name="amount" value="{{ old('amount') }}">
                @error('amount')
                    <small class="field-error">{{ $message }}</small>
                @enderror
            </label>

            <label class="field-block">
                <span>Category</span>
                <select name="category_id">
                    <option value="">Select category</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" @selected((string) old('category_id') === (string) $category->id)>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
                @error('category_id')
                    <small class="field-error">{{ $message }}</small>
                @enderror
            </label>

            <label class="field-block full-field">
                <span>Description</span>
                <textarea name="description" rows="4">{{ old('description') }}</textarea>
                @error('description')
                    <small class="field-error">{{ $message }}</small>
                @enderror
            </label>

            <label class="field-block full-field">
                <span>Receipt File</span>
                <input type="file" name="receipt" accept=".jpg,.jpeg,.png,.pdf">
                @error('receipt')
                    <small class="field-error">{{ $message }}</small>
                @enderror
            </label>

            <div class="button-row full-field">
                <button type="submit" class="main-button" @disabled($categories->count() === 0)>Submit Expense</button>
                <a href="{{ route('blade.expenses.index') }}" class="light-button button-link">Cancel</a>
            </div>
        </form>
    </section>
@endsection

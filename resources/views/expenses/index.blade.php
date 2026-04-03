@extends('layouts.app')

@section('title', 'My Expenses | HRVision')

@section('content')
    <section class="page-head">
        <div>
            <p class="small-label">Expense Management</p>
            <h1>My Expenses</h1>
            <p class="muted-text">View your submitted expenses and their review status.</p>
        </div>

        <a href="{{ route('blade.expenses.create') }}" class="main-button button-link">Submit Expense</a>
    </section>

    <section class="content-card">
        @if(!$employee)
            <p class="muted-text">No employee profile found for your account.</p>
        @elseif($expenses->count() === 0)
            <p class="muted-text">No expenses submitted yet.</p>
        @else
            <div class="employee-list">
                @foreach($expenses as $expense)
                    <article class="employee-card">
                        <div class="employee-top">
                            <div>
                                <h2>{{ number_format($expense->amount, 2) }}</h2>
                                <p>{{ $expense->category?->name ?? 'No category' }}</p>
                            </div>

                            <span class="status-badge {{ $expense->status }}">{{ $expense->status }}</span>
                        </div>

                        <div class="employee-details">
                            <p><strong>Description:</strong> {{ $expense->description ?: 'No description' }}</p>
                            <p><strong>Submitted:</strong> {{ $expense->submitted_at?->format('Y-m-d H:i') }}</p>
                            @if($expense->receipt_path)
                                <p>
                                    <strong>Receipt:</strong>
                                    <a href="{{ asset('storage/'.$expense->receipt_path) }}" target="_blank" class="text-link">Open file</a>
                                </p>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>

            <div class="pagination-wrap">
                {{ $expenses->links() }}
            </div>
        @endif
    </section>
@endsection

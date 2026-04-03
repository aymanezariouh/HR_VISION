@extends('layouts.app')

@section('title', 'Pending Expenses | HRVision')

@section('content')
    <section class="page-head">
        <div>
            <p class="small-label">Expense Review</p>
            <h1>Pending Expenses</h1>
            <p class="muted-text">Review employee expenses and approve or reject them.</p>
        </div>
    </section>

    <section class="content-card">
        @if($expenses->count() === 0)
            <p class="muted-text">No pending expenses.</p>
        @else
            <div class="employee-list">
                @foreach($expenses as $expense)
                    <article class="employee-card">
                        <div class="employee-top">
                            <div>
                                <h2>{{ number_format($expense->amount, 2) }}</h2>
                                <p>{{ $expense->employee?->name ?? 'No employee' }}</p>
                            </div>

                            <span class="status-badge {{ $expense->status }}">{{ $expense->status }}</span>
                        </div>

                        <div class="employee-details">
                            <p><strong>Category:</strong> {{ $expense->category?->name ?? 'No category' }}</p>
                            <p><strong>Description:</strong> {{ $expense->description ?: 'No description' }}</p>
                            <p><strong>Submitted:</strong> {{ $expense->submitted_at?->format('Y-m-d H:i') }}</p>
                            @if($expense->receipt_path)
                                <p>
                                    <strong>Receipt:</strong>
                                    <a href="{{ asset('storage/'.$expense->receipt_path) }}" target="_blank" class="text-link">Open file</a>
                                </p>
                            @endif
                        </div>

                        <div class="card-actions">
                            <form method="POST" action="{{ route('blade.expenses.approve', $expense) }}">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="main-button">Approve</button>
                            </form>

                            <form method="POST" action="{{ route('blade.expenses.reject', $expense) }}">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="danger-button">Reject</button>
                            </form>
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

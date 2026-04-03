@extends('layouts.app')

@section('title', 'Admin Expense Categories | HRVision')

@section('content')
    <section class="page-head">
        <div>
            <p class="small-label">Admin</p>
            <h1>Expense Categories</h1>
            <p class="muted-text">Manage the categories employees use when submitting expenses.</p>
        </div>

        <a href="{{ route('blade.admin.expense-categories.create') }}" class="main-button button-link">Create Category</a>
    </section>

    @include('admin.partials.nav')

    <section class="content-card">
        @if($categories->count() === 0)
            <p class="muted-text">No expense categories found.</p>
        @else
            <div class="salary-table-wrap">
                <table class="salary-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Expenses</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($categories as $category)
                            <tr>
                                <td>{{ $category->name }}</td>
                                <td>{{ $category->expenses_count }}</td>
                                <td>
                                    <span class="status-badge {{ $category->is_active ? 'active' : 'inactive' }}">
                                        {{ $category->is_active ? 'active' : 'inactive' }}
                                    </span>
                                </td>
                                <td>
                                    <div class="admin-actions">
                                        <a href="{{ route('blade.admin.expense-categories.edit', $category) }}" class="light-button button-link">Edit</a>

                                        <form method="POST" action="{{ route('blade.admin.expense-categories.deactivate', $category) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="danger-button" @disabled(!$category->is_active)>
                                                Deactivate
                                            </button>
                                        </form>

                                        <form method="POST" action="{{ route('blade.admin.expense-categories.destroy', $category) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="danger-button">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="pagination-wrap">
                {{ $categories->links() }}
            </div>
        @endif
    </section>
@endsection

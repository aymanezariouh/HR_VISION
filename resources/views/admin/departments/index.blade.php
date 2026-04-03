@extends('layouts.app')

@section('title', 'Admin Departments | HRVision')

@section('content')
    <section class="page-head">
        <div>
            <p class="small-label">Admin</p>
            <h1>Departments</h1>
            <p class="muted-text">Create, update, deactivate, or delete departments.</p>
        </div>

        <a href="{{ route('blade.admin.departments.create') }}" class="main-button button-link">Create Department</a>
    </section>

    @include('admin.partials.nav')

    <section class="content-card">
        @if($departments->count() === 0)
            <p class="muted-text">No departments found.</p>
        @else
            <div class="salary-table-wrap">
                <table class="salary-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Employees</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($departments as $department)
                            <tr>
                                <td>{{ $department->name }}</td>
                                <td>{{ $department->employees_count }}</td>
                                <td>
                                    <span class="status-badge {{ $department->is_active ? 'active' : 'inactive' }}">
                                        {{ $department->is_active ? 'active' : 'inactive' }}
                                    </span>
                                </td>
                                <td>
                                    <div class="admin-actions">
                                        <a href="{{ route('blade.admin.departments.edit', $department) }}" class="light-button button-link">Edit</a>

                                        <form method="POST" action="{{ route('blade.admin.departments.deactivate', $department) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="danger-button" @disabled(!$department->is_active)>
                                                Deactivate
                                            </button>
                                        </form>

                                        <form method="POST" action="{{ route('blade.admin.departments.destroy', $department) }}">
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
                {{ $departments->links() }}
            </div>
        @endif
    </section>
@endsection

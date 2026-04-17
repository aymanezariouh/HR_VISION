@extends('layouts.app')

@section('title', 'Admin Users | HRVision')

@section('content')
    <section class="page-head">
        <div>
            <p class="small-label">Admin</p>
            <h1>User Management</h1>
            <p class="muted-text">Manage user roles and deactivate user accounts.</p>
        </div>
    </section>

    @include('admin.partials.nav')

    <section class="content-card">
        @if($users->count() === 0)
            <p class="muted-text">No users found.</p>
        @else
            <div class="salary-table-wrap">
                <table class="salary-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $user)
                            <tr>
                                <td>{{ $user->name }}</td>
                                <td>{{ $user->email }}</td>
                                <td>{{ $user->phone ?: 'N/A' }}</td>
                                <td>{{ $user->isSuperAdmin() ? 'super_admin' : $user->role }}</td>
                                <td>
                                    <span class="status-badge {{ $user->is_active ? 'active' : 'inactive' }}">
                                        {{ $user->is_active ? 'active' : 'inactive' }}
                                    </span>
                                </td>
                                <td>
                                    <div class="admin-actions">
                                        <a href="{{ route('blade.admin.users.edit', $user) }}" class="light-button button-link">Edit</a>

                                        <form method="POST" action="{{ route('blade.admin.users.deactivate', $user) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button
                                                type="submit"
                                                class="danger-button"
                                                @disabled(!$user->is_active || auth()->id() === $user->id || $user->isSuperAdmin())
                                            >
                                                Deactivate
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="pagination-wrap">
                {{ $users->links() }}
            </div>
        @endif
    </section>
@endsection

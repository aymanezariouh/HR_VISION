@extends('layouts.app')

@section('title', 'Edit User | HRVision')

@section('content')
    <section class="page-head">
        <div>
            <p class="small-label">Admin</p>
            <h1>Edit User Role</h1>
            <p class="muted-text">Update the role assigned to this account.</p>
        </div>

        <a href="{{ route('blade.admin.users.index') }}" class="light-button button-link">Back</a>
    </section>

    @include('admin.partials.nav')

    <section class="content-card">
        <div class="linked-user-box">
            <p><strong>Name:</strong> {{ $user->name }}</p>
            <p><strong>Email:</strong> {{ $user->email }}</p>
            <p><strong>Phone:</strong> {{ $user->phone ?: 'N/A' }}</p>
            <p><strong>Current Role:</strong> {{ $user->isSuperAdmin() ? 'super_admin' : $user->role }}</p>
            <p><strong>Current Status:</strong> {{ $user->is_active ? 'active' : 'inactive' }}</p>
            <p><strong>Linked Employee:</strong> {{ $user->employee?->name ?? 'N/A' }}</p>
        </div>

        @if($user->isSuperAdmin())
            <div class="error-box">
                This account is the super admin. Its role cannot be changed.
            </div>
        @else
            <form method="POST" action="{{ route('blade.admin.users.update', $user) }}" class="employee-form">
                @csrf
                @method('PATCH')

                <label class="field-block full-field">
                    <span>Role</span>
                    <select name="role">
                        @foreach($roles as $role)
                            <option value="{{ $role }}" @selected(old('role', $user->role) === $role)>
                                {{ $role }}
                            </option>
                        @endforeach
                    </select>
                    @error('role')
                        <small class="field-error">{{ $message }}</small>
                    @enderror
                </label>

                <div class="button-row full-field">
                    <button type="submit" class="main-button">Save Role</button>
                    <a href="{{ route('blade.admin.users.index') }}" class="light-button button-link">Cancel</a>
                </div>
            </form>
        @endif
    </section>
@endsection

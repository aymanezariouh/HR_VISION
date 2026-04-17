<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateUserRoleRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class BladeAdminUserController extends Controller
{
    public function index(): View
    {
        return view('admin.users.index', [
            'users' => User::query()
                ->with('employee')
                ->orderBy('name')
                ->paginate(10),
        ]);
    }

    public function edit(User $user): View
    {
        return view('admin.users.edit', [
            'user' => $user->loadMissing('employee'),
            'roles' => [
                User::ROLE_ADMIN,
                User::ROLE_HR,
                User::ROLE_EMPLOYEE,
            ],
        ]);
    }

    public function update(UpdateUserRoleRequest $request, User $user): RedirectResponse
    {
        if (! $request->user()->isSuperAdmin()) {
            return redirect()
                ->route('admin.index')
                ->with('error', 'Only the super admin can change user roles.');
        }

        if ($user->isRootAdmin()) {
            return redirect()
                ->route('blade.admin.users.edit', $user)
                ->with('error', 'Admin #1 role cannot be changed.');
        }

        if ($user->is($request->user()) && $request->validated('role') !== User::ROLE_ADMIN) {
            return redirect()
                ->route('blade.admin.users.edit', $user)
                ->with('error', 'You cannot remove your own admin role.');
        }

        $user->update($request->validated());

        return redirect()
            ->route('blade.admin.users.index')
            ->with('success', 'User role updated successfully.');
    }

    public function deactivate(User $user): RedirectResponse
    {
        if (! request()->user()->isSuperAdmin()) {
            return redirect()
                ->route('admin.index')
                ->with('error', 'Only the super admin can deactivate admin-managed users.');
        }

        if ($user->isRootAdmin()) {
            return redirect()
                ->route('blade.admin.users.index')
                ->with('error', 'Admin #1 cannot be deactivated.');
        }

        if ($user->is(request()->user())) {
            return redirect()
                ->route('blade.admin.users.index')
                ->with('error', 'You cannot deactivate your own account.');
        }

        if ($user->is_active) {
            $user->update(['is_active' => false]);
            $user->tokens()->delete();
        }

        return redirect()
            ->route('blade.admin.users.index')
            ->with('success', 'User account deactivated successfully.');
    }
}

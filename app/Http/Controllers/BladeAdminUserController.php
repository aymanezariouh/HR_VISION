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
        $user->update($request->validated());

        return redirect()
            ->route('blade.admin.users.index')
            ->with('success', 'User role updated successfully.');
    }

    public function deactivate(User $user): RedirectResponse
    {
        if ($user->is_active) {
            $user->update(['is_active' => false]);
            $user->tokens()->delete();
        }

        return redirect()
            ->route('blade.admin.users.index')
            ->with('success', 'User account deactivated successfully.');
    }
}

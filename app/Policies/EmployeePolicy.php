<?php

namespace App\Policies;

use App\Models\Employee;
use App\Models\User;

class EmployeePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole([User::ROLE_ADMIN, User::ROLE_HR]);
    }

    public function view(User $user, Employee $employee): bool
    {
        if ($user->hasAnyRole([User::ROLE_ADMIN, User::ROLE_HR])) {
            return true;
        }

        return $user->hasRole(User::ROLE_EMPLOYEE)
            && ($user->employee?->is($employee) ?? false);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole([User::ROLE_ADMIN, User::ROLE_HR]);
    }

    public function update(User $user, Employee $employee): bool
    {
        return $user->hasAnyRole([User::ROLE_ADMIN, User::ROLE_HR]);
    }

    public function deactivate(User $user, Employee $employee): bool
    {
        return $this->update($user, $employee);
    }

    public function delete(User $user, Employee $employee): bool
    {
        return false;
    }
}

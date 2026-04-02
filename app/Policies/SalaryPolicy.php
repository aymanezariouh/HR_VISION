<?php

namespace App\Policies;

use App\Models\Employee;
use App\Models\Salary;
use App\Models\User;

class SalaryPolicy
{
    public function create(User $user): bool
    {
        return $user->hasAnyRole([User::ROLE_ADMIN, User::ROLE_HR]);
    }

    public function viewEmployeeHistory(User $user, Employee $employee): bool
    {
        if ($user->hasAnyRole([User::ROLE_ADMIN, User::ROLE_HR])) {
            return true;
        }

        return $user->hasRole(User::ROLE_EMPLOYEE)
            && ($user->employee?->is($employee) ?? false);
    }
}

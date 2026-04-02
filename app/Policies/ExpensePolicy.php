<?php

namespace App\Policies;

use App\Models\Expense;
use App\Models\User;

class ExpensePolicy
{
    public function create(User $user): bool
    {
        return $user->hasRole(User::ROLE_EMPLOYEE);
    }

    public function viewOwnHistory(User $user): bool
    {
        return $user->hasRole(User::ROLE_EMPLOYEE);
    }

    public function viewPending(User $user): bool
    {
        return $user->hasAnyRole([User::ROLE_ADMIN, User::ROLE_HR]);
    }

    public function approve(User $user, Expense $expense): bool
    {
        return $user->hasAnyRole([User::ROLE_ADMIN, User::ROLE_HR]);
    }

    public function reject(User $user, Expense $expense): bool
    {
        return $user->hasAnyRole([User::ROLE_ADMIN, User::ROLE_HR]);
    }
}

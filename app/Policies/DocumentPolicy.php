<?php

namespace App\Policies;

use App\Models\Document;
use App\Models\Employee;
use App\Models\User;

class DocumentPolicy
{
    public function create(User $user): bool
    {
        return $user->hasAnyRole([User::ROLE_ADMIN, User::ROLE_HR]);
    }

    public function viewEmployeeDocuments(User $user, Employee $employee): bool
    {
        if ($user->hasAnyRole([User::ROLE_ADMIN, User::ROLE_HR])) {
            return true;
        }

        return $user->hasRole(User::ROLE_EMPLOYEE)
            && ($user->employee?->is($employee) ?? false);
    }

    public function viewOwnDocuments(User $user): bool
    {
        return $user->hasRole(User::ROLE_EMPLOYEE);
    }

    public function download(User $user, Document $document): bool
    {
        if ($user->hasAnyRole([User::ROLE_ADMIN, User::ROLE_HR])) {
            return true;
        }

        return $user->hasRole(User::ROLE_EMPLOYEE)
            && ($user->employee?->is($document->employee) ?? false);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Document;
use App\Models\Employee;
use App\Models\Expense;
use App\Models\Salary;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BladeDashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        return view('dashboard', [
            'user' => $user,
            'stats' => $this->dashboardStats($user),
        ]);
    }

    private function dashboardStats(User $user): array
    {
        if ($user->hasAnyRole([User::ROLE_ADMIN, User::ROLE_HR])) {
            return [
                ['label' => 'Total Employees', 'value' => Employee::query()->count(), 'note' => 'All employee records'],
                ['label' => 'Pending Expenses', 'value' => Expense::query()->pending()->count(), 'note' => 'Waiting for review'],
                ['label' => 'Departments', 'value' => Department::query()->count(), 'note' => 'Configured departments'],
                ['label' => 'Documents', 'value' => Document::query()->count(), 'note' => 'Uploaded employee files'],
            ];
        }

        $employee = $user->employee;

        return [
            ['label' => 'Salary Records', 'value' => $employee?->salaries()->count() ?? 0, 'note' => 'Your salary history'],
            ['label' => 'Pending Expenses', 'value' => $employee?->expenses()->pending()->count() ?? 0, 'note' => 'Your requests in review'],
            ['label' => 'Total Expenses', 'value' => $employee?->expenses()->count() ?? 0, 'note' => 'Your submitted expenses'],
            ['label' => 'Documents', 'value' => $employee?->documents()->count() ?? 0, 'note' => 'Your personal files'],
        ];
    }
}

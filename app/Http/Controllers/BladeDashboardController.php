<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Document;
use App\Models\Employee;
use App\Models\Expense;
use App\Models\Salary;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BladeDashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        return view('dashboard', [
            'user' => $user,
            'stats' => [
                'employees' => Employee::query()->count(),
                'departments' => Department::query()->count(),
                'salaries' => Salary::query()->count(),
                'expenses' => Expense::query()->count(),
                'documents' => Document::query()->count(),
            ],
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSalaryRequest;
use App\Models\Employee;
use App\Models\Salary;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class BladeSalaryController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'employee_id' => ['nullable', 'integer', 'exists:employees,id'],
            'month' => ['nullable', 'integer', 'between:1,12'],
            'year' => ['nullable', 'integer', 'min:2000', 'max:2100'],
        ]);

        $user = $request->user();
        $employees = $this->salaryEmployees($user);
        $selectedEmployee = $this->selectedEmployee($user, $employees, $filters['employee_id'] ?? null);

        if ($selectedEmployee) {
            $this->authorize('viewEmployeeHistory', [Salary::class, $selectedEmployee]);
        }

        $salaries = $selectedEmployee
            ? $selectedEmployee->salaries()
                ->forMonth($filters['month'] ?? null)
                ->forYear($filters['year'] ?? null)
                ->latest('year')
                ->latest('month')
                ->paginate(10)
                ->withQueryString()
            : Salary::query()
                ->whereRaw('1 = 0')
                ->paginate(10)
                ->withQueryString();

        return view('salaries.index', [
            'currentUser' => $user,
            'employees' => $employees,
            'selectedEmployee' => $selectedEmployee,
            'salaries' => $salaries,
            'filters' => [
                'employee_id' => $selectedEmployee?->id ?? '',
                'month' => $filters['month'] ?? '',
                'year' => $filters['year'] ?? '',
            ],
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Salary::class);

        return view('salaries.create', [
            'employees' => Employee::query()
                ->orderBy('name')
                ->get(['id', 'name', 'professional_email']),
        ]);
    }

    public function store(StoreSalaryRequest $request): RedirectResponse
    {
        $salaryData = $request->validated();
        $salaryData['net_salary'] = Salary::calculateNetSalary(
            (float) $salaryData['base_salary'],
            (float) $salaryData['bonuses'],
            (float) $salaryData['deductions']
        );

        $salary = Salary::query()->create($salaryData);

        return redirect()
            ->route('blade.salaries.index', [
                'employee_id' => $salary->employee_id,
                'month' => $salary->month,
                'year' => $salary->year,
            ])
            ->with('success', 'Salary record created successfully.');
    }

    private function salaryEmployees(User $user): Collection
    {
        if ($user->hasAnyRole([User::ROLE_ADMIN, User::ROLE_HR])) {
            return Employee::query()
                ->orderBy('name')
                ->get(['id', 'name', 'professional_email']);
        }

        return $user->employee ? collect([$user->employee]) : collect();
    }

    private function selectedEmployee(User $user, Collection $employees, null|int|string $employeeId): ?Employee
    {
        if ($user->hasAnyRole([User::ROLE_ADMIN, User::ROLE_HR])) {
            if ($employeeId) {
                return $employees->firstWhere('id', (int) $employeeId);
            }

            return $employees->first();
        }

        return $user->employee;
    }
}

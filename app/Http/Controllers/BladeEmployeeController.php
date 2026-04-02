<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEmployeeRequest;
use App\Http\Requests\UpdateEmployeeRequest;
use App\Models\Department;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BladeEmployeeController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Employee::class);

        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'department_id' => ['nullable', 'integer', 'exists:departments,id'],
            'status' => ['nullable', 'string', 'in:active,inactive'],
        ]);

        $employees = Employee::query()
            ->with(['department', 'user'])
            ->search($filters['search'] ?? null)
            ->forDepartment($filters['department_id'] ?? null)
            ->withStatus($filters['status'] ?? null)
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('employees.index', [
            'employees' => $employees,
            'departments' => Department::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get(),
            'filters' => [
                'search' => $filters['search'] ?? '',
                'department_id' => $filters['department_id'] ?? '',
                'status' => $filters['status'] ?? '',
            ],
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Employee::class);

        return view('employees.create', [
            'departments' => $this->activeDepartments(),
            'employeeUsers' => $this->availableEmployeeUsers(),
        ]);
    }

    public function store(StoreEmployeeRequest $request): RedirectResponse
    {
        Employee::query()->create($request->validated());

        return redirect()
            ->route('blade.employees.index')
            ->with('success', 'Employee created successfully.');
    }

    public function edit(Employee $employee): View
    {
        $this->authorize('update', $employee);

        return view('employees.edit', [
            'employee' => $employee->loadMissing(['user', 'department']),
            'departments' => $this->activeDepartments(),
        ]);
    }

    public function update(UpdateEmployeeRequest $request, Employee $employee): RedirectResponse
    {
        $employee->update($request->validated());

        return redirect()
            ->route('blade.employees.index')
            ->with('success', 'Employee updated successfully.');
    }

    public function deactivate(Employee $employee): RedirectResponse
    {
        $this->authorize('deactivate', $employee);

        if ($employee->status !== Employee::STATUS_INACTIVE) {
            $employee->update(['status' => Employee::STATUS_INACTIVE]);
        }

        return redirect()
            ->route('blade.employees.index')
            ->with('success', 'Employee deactivated successfully.');
    }

    private function activeDepartments()
    {
        return Department::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    private function availableEmployeeUsers()
    {
        return User::query()
            ->where('role', User::ROLE_EMPLOYEE)
            ->where('is_active', true)
            ->whereDoesntHave('employee')
            ->orderBy('name')
            ->get();
    }
}

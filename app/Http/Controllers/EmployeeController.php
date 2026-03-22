<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EmployeeController extends Controller
{
    public function index(): JsonResponse
    {
        $this->authorize('viewAny', Employee::class);

        $employees = Employee::query()
            ->with(['department', 'user'])
            ->latest()
            ->paginate(15);

        return response()->json($employees);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', Employee::class);

        $validated = $request->validate([
            'user_id' => [
                'required',
                'integer',
                'unique:employees,user_id',
                Rule::exists('users', 'id')->where(
                    fn ($query) => $query->where('role', User::ROLE_EMPLOYEE)
                ),
            ],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:employees,email'],
            'phone' => ['required', 'string', 'max:30'],
            'address' => ['required', 'string'],
            'position' => ['required', 'string', 'max:255'],
            'department_id' => ['required', 'integer', Rule::exists('departments', 'id')],
            'hire_date' => ['required', 'date'],
            'contract_type' => ['required', 'string', 'max:255'],
            'status' => ['sometimes', Rule::in([Employee::STATUS_ACTIVE, Employee::STATUS_INACTIVE])],
        ]);

        $employee = Employee::create($validated);

        return response()->json($employee->load(['department', 'user']), 201);
    }

    public function show(Employee $employee): JsonResponse
    {
        $this->authorize('view', $employee);

        return response()->json($employee->load(['department', 'user']));
    }

    public function update(Request $request, Employee $employee): JsonResponse
    {
        $this->authorize('update', $employee);

        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'email' => [
                'sometimes',
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('employees', 'email')->ignore($employee->id),
            ],
            'phone' => ['sometimes', 'required', 'string', 'max:30'],
            'address' => ['sometimes', 'required', 'string'],
            'position' => ['sometimes', 'required', 'string', 'max:255'],
            'department_id' => ['sometimes', 'required', 'integer', Rule::exists('departments', 'id')],
            'hire_date' => ['sometimes', 'required', 'date'],
            'contract_type' => ['sometimes', 'required', 'string', 'max:255'],
            'status' => ['sometimes', 'required', Rule::in([Employee::STATUS_ACTIVE, Employee::STATUS_INACTIVE])],
        ]);

        $employee->update($validated);

        return response()->json($employee->fresh()->load(['department', 'user']));
    }

    public function destroy(Employee $employee): JsonResponse
    {
        $this->authorize('delete', $employee);

        $employee->delete();

        return response()->json([
            'message' => 'Employee deleted successfully.',
        ]);
    }
}

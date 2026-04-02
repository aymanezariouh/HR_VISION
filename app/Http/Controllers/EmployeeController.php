<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEmployeeRequest;
use App\Http\Requests\UpdateEmployeeRequest;
use App\Http\Resources\EmployeeResource;
use App\Models\Employee;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EmployeeController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Employee::class);

        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'department_id' => ['nullable', 'integer', 'exists:departments,id'],
            'status' => ['nullable', 'string', Rule::in([Employee::STATUS_ACTIVE, Employee::STATUS_INACTIVE])],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $employees = Employee::query()
            ->with(['department', 'user'])
            ->search($filters['search'] ?? null)
            ->forDepartment($filters['department_id'] ?? null)
            ->withStatus($filters['status'] ?? null)
            ->orderBy('name')
            ->paginate($filters['per_page'] ?? 15)
            ->withQueryString();

        return $this->successResponse([
            'items' => EmployeeResource::collection($employees->getCollection())->resolve(),
            'pagination' => [
                'current_page' => $employees->currentPage(),
                'last_page' => $employees->lastPage(),
                'per_page' => $employees->perPage(),
                'total' => $employees->total(),
                'from' => $employees->firstItem(),
                'to' => $employees->lastItem(),
            ],
            'filters' => [
                'search' => $filters['search'] ?? null,
                'department_id' => isset($filters['department_id']) ? (int) $filters['department_id'] : null,
                'status' => $filters['status'] ?? null,
            ],
        ], 'Employees retrieved successfully.');
    }

    public function store(StoreEmployeeRequest $request): JsonResponse
    {
        $employee = Employee::query()->create($request->validated());

        return $this->employeeResponse($employee, 'Employee created successfully.', 201);
    }

    public function show(Employee $employee): JsonResponse
    {
        $this->authorize('view', $employee);

        return $this->employeeResponse($employee, 'Employee retrieved successfully.');
    }

    public function update(UpdateEmployeeRequest $request, Employee $employee): JsonResponse
    {
        $employee->update($request->validated());

        return $this->employeeResponse($employee->fresh(), 'Employee updated successfully.');
    }

    public function deactivate(Employee $employee): JsonResponse
    {
        $this->authorize('deactivate', $employee);

        if ($employee->status === Employee::STATUS_INACTIVE) {
            return $this->employeeResponse($employee, 'Employee is already inactive.');
        }

        $employee->update([
            'status' => Employee::STATUS_INACTIVE,
        ]);

        return $this->employeeResponse($employee->fresh(), 'Employee deactivated successfully.');
    }

    private function employeeResponse(Employee $employee, string $message, int $status = 200): JsonResponse
    {
        return $this->successResponse(
            EmployeeResource::make($employee->loadMissing(['department', 'user']))->resolve(),
            $message,
            $status
        );
    }
}

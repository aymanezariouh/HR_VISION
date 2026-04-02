<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDepartmentRequest;
use App\Http\Requests\UpdateDepartmentRequest;
use App\Http\Resources\DepartmentResource;
use App\Models\Department;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminDepartmentController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $departments = Department::query()
            ->withCount('employees')
            ->orderBy('name')
            ->paginate($filters['per_page'] ?? 15)
            ->withQueryString();

        return $this->successResponse([
            'items' => DepartmentResource::collection($departments->getCollection())->resolve(),
            'pagination' => [
                'current_page' => $departments->currentPage(),
                'last_page' => $departments->lastPage(),
                'per_page' => $departments->perPage(),
                'total' => $departments->total(),
                'from' => $departments->firstItem(),
                'to' => $departments->lastItem(),
            ],
        ], 'Departments retrieved successfully.');
    }

    public function store(StoreDepartmentRequest $request): JsonResponse
    {
        $department = Department::query()->create($request->validated());

        return $this->departmentResponse($department, 'Department created successfully.', 201);
    }

    public function update(UpdateDepartmentRequest $request, Department $department): JsonResponse
    {
        $department->update($request->validated());

        return $this->departmentResponse($department->fresh(), 'Department updated successfully.');
    }

    public function deactivate(Department $department): JsonResponse
    {
        if (! $department->is_active) {
            return $this->departmentResponse($department, 'Department is already inactive.');
        }

        $department->update([
            'is_active' => false,
        ]);

        return $this->departmentResponse($department->fresh(), 'Department deactivated successfully.');
    }

    public function destroy(Department $department): JsonResponse
    {
        if ($department->employees()->exists()) {
            return $this->errorResponse(
                'Department cannot be deleted because it has employees. Deactivate it instead.',
                null,
                422
            );
        }

        $department->delete();

        return $this->successResponse(null, 'Department deleted successfully.');
    }

    private function departmentResponse(Department $department, string $message, int $status = 200): JsonResponse
    {
        return $this->successResponse(
            DepartmentResource::make($department->loadCount('employees'))->resolve(),
            $message,
            $status
        );
    }
}

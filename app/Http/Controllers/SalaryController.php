<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSalaryRequest;
use App\Http\Resources\SalaryResource;
use App\Models\Employee;
use App\Models\Salary;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SalaryController extends Controller
{
    public function index(Request $request, Employee $employee): JsonResponse
    {
        $this->authorize('viewEmployeeHistory', [Salary::class, $employee]);

        $filters = $request->validate([
            'month' => ['nullable', 'integer', 'between:1,12'],
            'year' => ['nullable', 'integer', 'min:2000', 'max:2100'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $salaries = $employee->salaries()
            ->forMonth($filters['month'] ?? null)
            ->forYear($filters['year'] ?? null)
            ->latest('year')
            ->latest('month')
            ->paginate($filters['per_page'] ?? 15)
            ->withQueryString();

        return $this->successResponse([
            'employee' => [
                'id' => $employee->id,
                'name' => $employee->name,
                'professional_email' => $employee->professional_email,
            ],
            'items' => SalaryResource::collection($salaries->getCollection())->resolve(),
            'pagination' => [
                'current_page' => $salaries->currentPage(),
                'last_page' => $salaries->lastPage(),
                'per_page' => $salaries->perPage(),
                'total' => $salaries->total(),
                'from' => $salaries->firstItem(),
                'to' => $salaries->lastItem(),
            ],
            'filters' => [
                'month' => isset($filters['month']) ? (int) $filters['month'] : null,
                'year' => isset($filters['year']) ? (int) $filters['year'] : null,
            ],
        ], 'Salary history retrieved successfully.');
    }

    public function store(StoreSalaryRequest $request): JsonResponse
    {
        $salaryData = $request->validated();
        $salaryData['net_salary'] = Salary::calculateNetSalary(
            (float) $salaryData['base_salary'],
            (float) $salaryData['bonuses'],
            (float) $salaryData['deductions']
        );

        $salary = Salary::query()->create($salaryData);

        return $this->successResponse(
            SalaryResource::make($salary->loadMissing('employee'))->resolve(),
            'Salary record created successfully.',
            201
        );
    }
}

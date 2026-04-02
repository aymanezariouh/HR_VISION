<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreExpenseCategoryRequest;
use App\Http\Requests\UpdateExpenseCategoryRequest;
use App\Http\Resources\ExpenseCategoryResource;
use App\Models\ExpenseCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminExpenseCategoryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $categories = ExpenseCategory::query()
            ->withCount('expenses')
            ->orderBy('name')
            ->paginate($filters['per_page'] ?? 15)
            ->withQueryString();

        return $this->successResponse([
            'items' => ExpenseCategoryResource::collection($categories->getCollection())->resolve(),
            'pagination' => [
                'current_page' => $categories->currentPage(),
                'last_page' => $categories->lastPage(),
                'per_page' => $categories->perPage(),
                'total' => $categories->total(),
                'from' => $categories->firstItem(),
                'to' => $categories->lastItem(),
            ],
        ], 'Expense categories retrieved successfully.');
    }

    public function store(StoreExpenseCategoryRequest $request): JsonResponse
    {
        $category = ExpenseCategory::query()->create($request->validated());

        return $this->categoryResponse($category, 'Expense category created successfully.', 201);
    }

    public function update(UpdateExpenseCategoryRequest $request, ExpenseCategory $expenseCategory): JsonResponse
    {
        $expenseCategory->update($request->validated());

        return $this->categoryResponse($expenseCategory->fresh(), 'Expense category updated successfully.');
    }

    public function deactivate(ExpenseCategory $expenseCategory): JsonResponse
    {
        if (! $expenseCategory->is_active) {
            return $this->categoryResponse($expenseCategory, 'Expense category is already inactive.');
        }

        $expenseCategory->update([
            'is_active' => false,
        ]);

        return $this->categoryResponse($expenseCategory->fresh(), 'Expense category deactivated successfully.');
    }

    public function destroy(ExpenseCategory $expenseCategory): JsonResponse
    {
        if ($expenseCategory->expenses()->exists()) {
            return $this->errorResponse(
                'Expense category cannot be deleted because it has expenses. Deactivate it instead.',
                null,
                422
            );
        }

        $expenseCategory->delete();

        return $this->successResponse(null, 'Expense category deleted successfully.');
    }

    private function categoryResponse(ExpenseCategory $expenseCategory, string $message, int $status = 200): JsonResponse
    {
        return $this->successResponse(
            ExpenseCategoryResource::make($expenseCategory->loadCount('expenses'))->resolve(),
            $message,
            $status
        );
    }
}

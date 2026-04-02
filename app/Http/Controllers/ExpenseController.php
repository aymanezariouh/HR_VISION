<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreExpenseRequest;
use App\Http\Resources\ExpenseResource;
use App\Models\Expense;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    public function store(StoreExpenseRequest $request): JsonResponse
    {
        $employee = $request->user()?->employee;

        if (! $employee) {
            return $this->errorResponse('Employee record not found.', null, 404);
        }

        $expense = Expense::query()->create([
            'employee_id' => $employee->id,
            'category_id' => $request->validated('category_id'),
            'amount' => $request->validated('amount'),
            'description' => $request->validated('description'),
            'receipt_path' => $request->file('receipt')->store('expense-receipts', 'public'),
            'status' => Expense::STATUS_PENDING,
            'submitted_at' => now(),
        ]);

        return $this->expenseResponse($expense, 'Expense submitted successfully.', 201);
    }

    public function myExpenses(Request $request): JsonResponse
    {
        $this->authorize('viewOwnHistory', Expense::class);

        $employee = $request->user()?->employee;

        if (! $employee) {
            return $this->errorResponse('Employee record not found.', null, 404);
        }

        $filters = $request->validate([
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $expenses = $employee->expenses()
            ->with('category')
            ->latest('submitted_at')
            ->paginate($filters['per_page'] ?? 15)
            ->withQueryString();

        return $this->successResponse([
            'employee' => [
                'id' => $employee->id,
                'name' => $employee->name,
                'professional_email' => $employee->professional_email,
            ],
            'items' => ExpenseResource::collection($expenses->getCollection())->resolve(),
            'pagination' => [
                'current_page' => $expenses->currentPage(),
                'last_page' => $expenses->lastPage(),
                'per_page' => $expenses->perPage(),
                'total' => $expenses->total(),
                'from' => $expenses->firstItem(),
                'to' => $expenses->lastItem(),
            ],
        ], 'Expense history retrieved successfully.');
    }

    public function pending(Request $request): JsonResponse
    {
        $this->authorize('viewPending', Expense::class);

        $filters = $request->validate([
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $expenses = Expense::query()
            ->with(['employee', 'category'])
            ->pending()
            ->latest('submitted_at')
            ->paginate($filters['per_page'] ?? 15)
            ->withQueryString();

        return $this->successResponse([
            'items' => ExpenseResource::collection($expenses->getCollection())->resolve(),
            'pagination' => [
                'current_page' => $expenses->currentPage(),
                'last_page' => $expenses->lastPage(),
                'per_page' => $expenses->perPage(),
                'total' => $expenses->total(),
                'from' => $expenses->firstItem(),
                'to' => $expenses->lastItem(),
            ],
        ], 'Pending expenses retrieved successfully.');
    }

    public function approve(Expense $expense): JsonResponse
    {
        $this->authorize('approve', $expense);

        if ($expense->status === Expense::STATUS_APPROVED) {
            return $this->expenseResponse($expense, 'Expense is already approved.');
        }

        $expense->update([
            'status' => Expense::STATUS_APPROVED,
        ]);

        return $this->expenseResponse($expense->fresh(), 'Expense approved successfully.');
    }

    public function reject(Expense $expense): JsonResponse
    {
        $this->authorize('reject', $expense);

        if ($expense->status === Expense::STATUS_REJECTED) {
            return $this->expenseResponse($expense, 'Expense is already rejected.');
        }

        $expense->update([
            'status' => Expense::STATUS_REJECTED,
        ]);

        return $this->expenseResponse($expense->fresh(), 'Expense rejected successfully.');
    }

    private function expenseResponse(Expense $expense, string $message, int $status = 200): JsonResponse
    {
        return $this->successResponse(
            ExpenseResource::make($expense->loadMissing(['employee', 'category']))->resolve(),
            $message,
            $status
        );
    }
}

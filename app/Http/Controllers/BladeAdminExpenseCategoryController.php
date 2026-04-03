<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreExpenseCategoryRequest;
use App\Http\Requests\UpdateExpenseCategoryRequest;
use App\Models\ExpenseCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class BladeAdminExpenseCategoryController extends Controller
{
    public function index(): View
    {
        return view('admin.expense-categories.index', [
            'categories' => ExpenseCategory::query()
                ->withCount('expenses')
                ->orderBy('name')
                ->paginate(10),
        ]);
    }

    public function create(): View
    {
        return view('admin.expense-categories.create');
    }

    public function store(StoreExpenseCategoryRequest $request): RedirectResponse
    {
        ExpenseCategory::query()->create($request->validated());

        return redirect()
            ->route('blade.admin.expense-categories.index')
            ->with('success', 'Expense category created successfully.');
    }

    public function edit(ExpenseCategory $expenseCategory): View
    {
        return view('admin.expense-categories.edit', [
            'expenseCategory' => $expenseCategory,
        ]);
    }

    public function update(UpdateExpenseCategoryRequest $request, ExpenseCategory $expenseCategory): RedirectResponse
    {
        $expenseCategory->update($request->validated());

        return redirect()
            ->route('blade.admin.expense-categories.index')
            ->with('success', 'Expense category updated successfully.');
    }

    public function deactivate(ExpenseCategory $expenseCategory): RedirectResponse
    {
        if ($expenseCategory->is_active) {
            $expenseCategory->update(['is_active' => false]);
        }

        return redirect()
            ->route('blade.admin.expense-categories.index')
            ->with('success', 'Expense category deactivated successfully.');
    }

    public function destroy(ExpenseCategory $expenseCategory): RedirectResponse
    {
        if ($expenseCategory->expenses()->exists()) {
            return redirect()
                ->route('blade.admin.expense-categories.index')
                ->with('error', 'Expense category cannot be deleted because it has expenses. Deactivate it instead.');
        }

        $expenseCategory->delete();

        return redirect()
            ->route('blade.admin.expense-categories.index')
            ->with('success', 'Expense category deleted successfully.');
    }
}

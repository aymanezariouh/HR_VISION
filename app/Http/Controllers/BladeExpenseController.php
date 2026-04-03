<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreExpenseRequest;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BladeExpenseController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewOwnHistory', Expense::class);

        $employee = $request->user()?->employee;
        $expenses = $employee
            ? $employee->expenses()
                ->with('category')
                ->latest('submitted_at')
                ->paginate(10)
                ->withQueryString()
            : Expense::query()
                ->whereRaw('1 = 0')
                ->paginate(10);

        return view('expenses.index', [
            'employee' => $employee,
            'expenses' => $expenses,
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Expense::class);

        return view('expenses.create', [
            'categories' => ExpenseCategory::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name']),
        ]);
    }

    public function store(StoreExpenseRequest $request): RedirectResponse
    {
        $employee = $request->user()?->employee;

        if (! $employee) {
            return redirect()
                ->route('blade.expenses.index')
                ->with('success', 'Employee profile not found.');
        }

        Expense::query()->create([
            'employee_id' => $employee->id,
            'category_id' => $request->validated('category_id'),
            'amount' => $request->validated('amount'),
            'description' => $request->validated('description'),
            'receipt_path' => $request->file('receipt')->store('expense-receipts', 'public'),
            'status' => Expense::STATUS_PENDING,
            'submitted_at' => now(),
        ]);

        return redirect()
            ->route('blade.expenses.index')
            ->with('success', 'Expense submitted successfully.');
    }

    public function pending(): View
    {
        $this->authorize('viewPending', Expense::class);

        return view('expenses.pending', [
            'expenses' => Expense::query()
                ->with(['employee', 'category'])
                ->pending()
                ->latest('submitted_at')
                ->paginate(10),
        ]);
    }

    public function approve(Expense $expense): RedirectResponse
    {
        $this->authorize('approve', $expense);

        $expense->update([
            'status' => Expense::STATUS_APPROVED,
        ]);

        return redirect()
            ->route('blade.expenses.pending')
            ->with('success', 'Expense approved successfully.');
    }

    public function reject(Expense $expense): RedirectResponse
    {
        $this->authorize('reject', $expense);

        $expense->update([
            'status' => Expense::STATUS_REJECTED,
        ]);

        return redirect()
            ->route('blade.expenses.pending')
            ->with('success', 'Expense rejected successfully.');
    }
}

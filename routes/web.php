<?php

use App\Http\Controllers\BladeAdminDepartmentController;
use App\Http\Controllers\BladeAdminExpenseCategoryController;
use App\Http\Controllers\BladeAdminUserController;
use App\Http\Controllers\BladeAuthController;
use App\Http\Controllers\BladeDashboardController;
use App\Http\Controllers\BladeDocumentController;
use App\Http\Controllers\BladeEmployeeController;
use App\Http\Controllers\BladeExpenseController;
use App\Http\Controllers\BladeSalaryController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('login'));

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [BladeAuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [BladeAuthController::class, 'login'])->name('login.submit');
});

Route::middleware('auth')->group(function (): void {
    Route::post('/logout', [BladeAuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [BladeDashboardController::class, 'index'])->name('dashboard');

    Route::middleware('role:admin,hr')->group(function (): void {
        Route::patch('/employees/{employee}/deactivate', [BladeEmployeeController::class, 'deactivate'])
            ->name('blade.employees.deactivate');

        Route::resource('employees', BladeEmployeeController::class)->only([
            'index',
            'create',
            'store',
            'edit',
            'update',
        ])->names([
            'index' => 'blade.employees.index',
            'create' => 'blade.employees.create',
            'store' => 'blade.employees.store',
            'edit' => 'blade.employees.edit',
            'update' => 'blade.employees.update',
        ]);

        Route::get('/salaries/create', [BladeSalaryController::class, 'create'])
            ->name('blade.salaries.create');

        Route::post('/salaries', [BladeSalaryController::class, 'store'])
            ->name('blade.salaries.store');

        Route::get('/expenses/pending', [BladeExpenseController::class, 'pending'])
            ->name('blade.expenses.pending');
        Route::patch('/expenses/{expense}/approve', [BladeExpenseController::class, 'approve'])
            ->name('blade.expenses.approve');
        Route::patch('/expenses/{expense}/reject', [BladeExpenseController::class, 'reject'])
            ->name('blade.expenses.reject');

        Route::get('/documents', [BladeDocumentController::class, 'index'])
            ->name('blade.documents.index');

        Route::get('/documents/create', [BladeDocumentController::class, 'create'])
            ->name('blade.documents.create');

        Route::post('/documents', [BladeDocumentController::class, 'store'])
            ->name('blade.documents.store');
    });

    Route::get('/salaries', [BladeSalaryController::class, 'index'])->name('blade.salaries.index');
    Route::get('/expenses', [BladeExpenseController::class, 'index'])->name('blade.expenses.index');
    Route::get('/expenses/create', [BladeExpenseController::class, 'create'])
        ->middleware('role:employee')
        ->name('blade.expenses.create');
    Route::post('/expenses', [BladeExpenseController::class, 'store'])
        ->middleware('role:employee')
        ->name('blade.expenses.store');
    Route::get('/my-documents', [BladeDocumentController::class, 'myDocuments'])
        ->middleware('role:employee')
        ->name('blade.documents.mine');

    Route::get('/documents/{document}/download', [BladeDocumentController::class, 'download'])
        ->name('blade.documents.download');

    Route::middleware('role:admin')->group(function (): void {
        Route::get('/admin', fn () => redirect()->route('blade.admin.users.index'))
            ->name('admin.index');

        Route::get('/admin/users', [BladeAdminUserController::class, 'index'])
            ->name('blade.admin.users.index');
        Route::get('/admin/users/{user}/edit', [BladeAdminUserController::class, 'edit'])
            ->name('blade.admin.users.edit');
        Route::patch('/admin/users/{user}', [BladeAdminUserController::class, 'update'])
            ->name('blade.admin.users.update');
        Route::patch('/admin/users/{user}/deactivate', [BladeAdminUserController::class, 'deactivate'])
            ->name('blade.admin.users.deactivate');

        Route::resource('/admin/departments', BladeAdminDepartmentController::class)
            ->only(['index', 'create', 'store', 'edit', 'update', 'destroy'])
            ->names([
                'index' => 'blade.admin.departments.index',
                'create' => 'blade.admin.departments.create',
                'store' => 'blade.admin.departments.store',
                'edit' => 'blade.admin.departments.edit',
                'update' => 'blade.admin.departments.update',
                'destroy' => 'blade.admin.departments.destroy',
            ]);
        Route::patch('/admin/departments/{department}/deactivate', [BladeAdminDepartmentController::class, 'deactivate'])
            ->name('blade.admin.departments.deactivate');

        Route::resource('/admin/expense-categories', BladeAdminExpenseCategoryController::class)
            ->only(['index', 'create', 'store', 'edit', 'update', 'destroy'])
            ->parameters([
                'expense-categories' => 'expenseCategory',
            ])
            ->names([
                'index' => 'blade.admin.expense-categories.index',
                'create' => 'blade.admin.expense-categories.create',
                'store' => 'blade.admin.expense-categories.store',
                'edit' => 'blade.admin.expense-categories.edit',
                'update' => 'blade.admin.expense-categories.update',
                'destroy' => 'blade.admin.expense-categories.destroy',
            ]);
        Route::patch('/admin/expense-categories/{expenseCategory}/deactivate', [BladeAdminExpenseCategoryController::class, 'deactivate'])
            ->name('blade.admin.expense-categories.deactivate');
    });
});

<?php

use App\Http\Controllers\BladeAuthController;
use App\Http\Controllers\BladeDashboardController;
use App\Http\Controllers\BladeEmployeeController;
use App\Http\Controllers\BladeExpenseController;
use App\Http\Controllers\BladeModuleController;
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
    });

    Route::get('/salaries', [BladeSalaryController::class, 'index'])->name('blade.salaries.index');
    Route::get('/expenses', [BladeExpenseController::class, 'index'])->name('blade.expenses.index');
    Route::get('/expenses/create', [BladeExpenseController::class, 'create'])
        ->middleware('role:employee')
        ->name('blade.expenses.create');
    Route::post('/expenses', [BladeExpenseController::class, 'store'])
        ->middleware('role:employee')
        ->name('blade.expenses.store');
    Route::get('/documents', [BladeModuleController::class, 'documents'])->name('documents.index');

    Route::middleware('role:admin')->group(function (): void {
        Route::get('/admin', [BladeModuleController::class, 'admin'])->name('admin.index');
    });
});

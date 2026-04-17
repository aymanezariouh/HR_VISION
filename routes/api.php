<?php

use App\Http\Controllers\AdminDepartmentController;
use App\Http\Controllers\AdminExpenseCategoryController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\EmployeeProfileController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\SalaryController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
    Route::get('/employees/{employee}', [EmployeeController::class, 'show']);
    Route::get('/employees/{employee}/documents', [DocumentController::class, 'index']);
    Route::get('/employees/{employee}/salaries', [SalaryController::class, 'index']);
    Route::get('/documents/{document}/download', [DocumentController::class, 'download']);

    Route::prefix('admin')->middleware('role:admin')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'admin']);

        Route::middleware('super_admin')->group(function () {
            Route::get('/users', [AdminUserController::class, 'index']);
            Route::get('/users/{user}', [AdminUserController::class, 'show']);
            Route::patch('/users/{user}', [AdminUserController::class, 'update']);
            Route::patch('/users/{user}/deactivate', [AdminUserController::class, 'deactivate']);
        });

        Route::get('/departments', [AdminDepartmentController::class, 'index']);
        Route::post('/departments', [AdminDepartmentController::class, 'store']);
        Route::patch('/departments/{department}', [AdminDepartmentController::class, 'update']);
        Route::patch('/departments/{department}/deactivate', [AdminDepartmentController::class, 'deactivate']);
        Route::delete('/departments/{department}', [AdminDepartmentController::class, 'destroy']);
        Route::get('/expense-categories', [AdminExpenseCategoryController::class, 'index']);
        Route::post('/expense-categories', [AdminExpenseCategoryController::class, 'store']);
        Route::patch('/expense-categories/{expenseCategory}', [AdminExpenseCategoryController::class, 'update']);
        Route::patch('/expense-categories/{expenseCategory}/deactivate', [AdminExpenseCategoryController::class, 'deactivate']);
        Route::delete('/expense-categories/{expenseCategory}', [AdminExpenseCategoryController::class, 'destroy']);
    });

    Route::prefix('hr')->middleware('role:admin,hr')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'hr']);
        Route::get('/employee-options', [EmployeeController::class, 'options']);
        Route::apiResource('employees', EmployeeController::class)
            ->only(['index', 'store', 'update']);
        Route::patch('/employees/{employee}/deactivate', [EmployeeController::class, 'deactivate'])
            ->name('employees.deactivate');
        Route::post('/documents', [DocumentController::class, 'store']);
        Route::post('/salaries', [SalaryController::class, 'store']);
        Route::get('/expenses/pending', [ExpenseController::class, 'pending']);
        Route::patch('/expenses/{expense}/approve', [ExpenseController::class, 'approve']);
        Route::patch('/expenses/{expense}/reject', [ExpenseController::class, 'reject']);
    });

    Route::prefix('employee')->middleware('role:employee')->group(function () {
        Route::get('/profile', [EmployeeProfileController::class, 'show']);
        Route::get('/documents', [DocumentController::class, 'myDocuments']);
        Route::get('/expenses', [ExpenseController::class, 'myExpenses']);
        Route::post('/expenses', [ExpenseController::class, 'store']);
    });
});

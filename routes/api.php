<?php

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
    });

    Route::prefix('hr')->middleware('role:admin,hr')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'hr']);
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

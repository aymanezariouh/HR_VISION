<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\EmployeeProfileController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
    Route::get('/employees/{employee}', [EmployeeController::class, 'show']);

    Route::prefix('admin')->middleware('role:admin')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'admin']);
    });

    Route::prefix('hr')->middleware('role:admin,hr')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'hr']);
        Route::apiResource('employees', EmployeeController::class)
            ->only(['index', 'store', 'update']);
        Route::patch('/employees/{employee}/deactivate', [EmployeeController::class, 'deactivate'])
            ->name('employees.deactivate');
    });

    Route::prefix('employee')->middleware('role:employee')->group(function () {
        Route::get('/profile', [EmployeeProfileController::class, 'show']);
    });
});

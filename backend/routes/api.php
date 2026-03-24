<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\EmployeeController;
use App\Http\Controllers\Api\PayrollController;
use App\Http\Controllers\Api\DepartmentController;
use App\Http\Controllers\TaxBracketController;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public routes (rate limited - 10 login attempts per minute)
Route::middleware('throttle:10,1')->group(function () {
    Route::post('/auth/login', [AuthController::class, 'login']);
});

// Protected routes (rate limited - 60 requests per minute)
Route::middleware(['auth:sanctum', 'throttle:60,1'])->group(function () {
    // Auth routes
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/user', [AuthController::class, 'user']);
    
    // Employee routes
    Route::apiResource('employees', EmployeeController::class);
    Route::get('/employees/{employee}/payroll', [EmployeeController::class, 'payroll']);
    
    // Department routes
    Route::apiResource('departments', DepartmentController::class);
    Route::get('/departments/{department}/employees', [DepartmentController::class, 'employees']);
    
    // Payroll routes - using apiResource for standard CRUD operations
    Route::apiResource('payrolls', PayrollController::class);
    
    // Additional payroll processing routes
    Route::post('/payrolls/{payroll}/process', [PayrollController::class, 'process']);
    Route::get('/payrolls/reports/summary', [PayrollController::class, 'summary']);
    Route::get('/payrolls/reports/monthly', [PayrollController::class, 'monthlyReport']);
    Route::get('/payrolls/approved', [PayrollController::class, 'getApprovedPayrolls']);
    Route::post('/payrolls/process-payments', [PayrollController::class, 'processPaymentsApi']);

    // Payroll generation service endpoints
    Route::post('/payroll/preview', [PayrollController::class, 'preview']);
    Route::post('/payroll/generate', [PayrollController::class, 'generate']);
    Route::post('/payroll/approve', [PayrollController::class, 'approveAll']);
    Route::post('/payroll/process-payments', [PayrollController::class, 'processPayments']);
    Route::post('/payroll/generate-payslips', [PayrollController::class, 'generatePayslips']);
    Route::get('/payroll/export', [PayrollController::class, 'export']);
    
    // Tax bracket routes
    Route::get('/tax-brackets', [TaxBracketController::class, 'index']);
    Route::post('/tax-brackets/calculate', [TaxBracketController::class, 'calculateTax']);
    Route::post('/tax-brackets/calculate-range', [TaxBracketController::class, 'calculateRange']);
    Route::get('/tax-brackets/active', [TaxBracketController::class, 'getActiveBrackets']);
    Route::post('/tax-brackets/philippine', [TaxBracketController::class, 'createPhilippineBrackets']);

    // HR Inbox endpoint
    Route::get('/hr/inbox', [App\Http\Controllers\Api\HrInboxController::class, 'index']);
});
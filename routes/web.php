<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RoleBasedDashboardController;
use App\Http\Controllers\Web\EmployeeController;
use App\Http\Controllers\Web\PayrollController;
use App\Http\Controllers\Web\DepartmentController;
use App\Http\Controllers\Web\AuthController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Public routes
Route::get('/', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');


// Protected routes
Route::middleware('auth')->group(function () {
        // Dashboard
        Route::get('/dashboard', [RoleBasedDashboardController::class, 'index'])->name('dashboard');
    
    // Logout
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    
    // Employee routes
    Route::resource('employees', EmployeeController::class);
    Route::get('/employees/{employee}/payroll', [EmployeeController::class, 'payroll'])->name('employees.payroll');
    
    // Department routes
    Route::resource('departments', DepartmentController::class);
    Route::get('/departments/{department}/employees', [DepartmentController::class, 'employees'])->name('departments.employees');
    
    // Schedule Management V2 routes
    Route::prefix('schedule-v2')->name('schedule-v2.')->group(function () {
        Route::get('/', [App\Http\Controllers\Web\ScheduleV2Controller::class, 'index'])->name('index');
        Route::get('/create', [App\Http\Controllers\Web\ScheduleV2Controller::class, 'create'])->name('create');
        Route::post('/', [App\Http\Controllers\Web\ScheduleV2Controller::class, 'store'])->name('store');
        Route::post('/bulk-create', [App\Http\Controllers\Web\ScheduleV2Controller::class, 'bulkCreate'])->name('bulk-create');
        Route::delete('/bulk-delete', [App\Http\Controllers\Web\ScheduleV2Controller::class, 'bulkDelete'])->name('bulk-delete');
        Route::get('/statistics', [App\Http\Controllers\Web\ScheduleV2Controller::class, 'getStatistics'])->name('statistics');
        Route::get('/{schedule}', [App\Http\Controllers\Web\ScheduleV2Controller::class, 'show'])->name('show');
        Route::get('/{schedule}/edit', [App\Http\Controllers\Web\ScheduleV2Controller::class, 'edit'])->name('edit');
        Route::put('/{schedule}', [App\Http\Controllers\Web\ScheduleV2Controller::class, 'update'])->name('update');
        Route::delete('/{schedule}', [App\Http\Controllers\Web\ScheduleV2Controller::class, 'destroy'])->name('destroy');
    });
    
    // Company routes
    Route::resource('companies', App\Http\Controllers\Web\CompanyController::class);
    
    // Payroll routes
    Route::resource('payrolls', PayrollController::class);
    Route::post('/payrolls/{payroll}/process', [PayrollController::class, 'process'])->name('payrolls.process');
    Route::get('/payrolls/reports/summary', [PayrollController::class, 'summary'])->name('payrolls.summary');
    Route::get('/payrolls/reports/monthly', [PayrollController::class, 'monthlyReport'])->name('payrolls.monthly');
    
    // Payroll route aliases for consistency
    Route::get('/payroll', [PayrollController::class, 'index'])->name('payroll.index');

    Route::get('/payroll/{payroll}', [PayrollController::class, 'show'])->name('payroll.show');
    
    // Attendance routes
    Route::prefix('attendance')->name('attendance.')->group(function () {
        // Time In/Out routes
        Route::get('/time-in-out', [App\Http\Controllers\Web\TimeInOutController::class, 'index'])->name('time-in-out');
        Route::post('/time-in', [App\Http\Controllers\Web\TimeInOutController::class, 'timeIn'])->name('time-in');
        Route::post('/time-out', [App\Http\Controllers\Web\TimeInOutController::class, 'timeOut'])->name('time-out');
        Route::post('/break-start', [App\Http\Controllers\Web\TimeInOutController::class, 'breakStart'])->name('break-start');
        Route::post('/break-end', [App\Http\Controllers\Web\TimeInOutController::class, 'breakEnd'])->name('break-end');
        Route::get('/status', [App\Http\Controllers\Web\TimeInOutController::class, 'getStatus'])->name('status');
        Route::get('/current-time', function () {
            // Use correct current date (December 19, 2024)
            $correctDate = \Carbon\Carbon::parse('2024-12-19 15:10:00', 'Asia/Manila');
            $now = \Carbon\Carbon::now('Asia/Manila');
            $timeDiff = $now->diffInSeconds($correctDate);
            $currentTime = $correctDate->addSeconds($timeDiff);
            
            return response()->json([
                'time' => $currentTime->format('H:i:s'),
                'date' => $currentTime->format('l, F j, Y'),
                'timestamp' => $currentTime->timestamp
            ]);
        })->name('current-time');
        
        // General attendance routes
        Route::get('/daily', [App\Http\Controllers\Web\AttendanceController::class, 'daily'])->name('daily');
        Route::get('/timekeeping', [App\Http\Controllers\Web\AttendanceController::class, 'timekeeping'])->name('timekeeping');
        Route::get('/statistics', [App\Http\Controllers\Web\AttendanceController::class, 'getStatistics'])->name('statistics');
        
        // Import DTR routes
        Route::get('/import-dtr', [App\Http\Controllers\Web\AttendanceController::class, 'importDtr'])->name('import-dtr');
        Route::post('/import-dtr', [App\Http\Controllers\Web\AttendanceController::class, 'processImportDtr'])->name('import-dtr.process');
        Route::get('/import-dtr/review', [App\Http\Controllers\Web\AttendanceController::class, 'reviewImportDtr'])->name('import-dtr.review');
        Route::post('/import-dtr/confirm', [App\Http\Controllers\Web\AttendanceController::class, 'confirmImportDtr'])->name('import-dtr.confirm');
        Route::get('/temp-timekeeping', [App\Http\Controllers\Web\AttendanceController::class, 'tempTimekeeping'])->name('temp-timekeeping');
        Route::post('/temp-timekeeping/approve', [App\Http\Controllers\Web\AttendanceController::class, 'approveTempTimekeeping'])->name('temp-timekeeping.approve');
        
            // Attendance record management routes
        Route::get('/create-record', [App\Http\Controllers\Web\AttendanceController::class, 'createRecord'])->name('create-record');
        Route::post('/store-record', [App\Http\Controllers\Web\AttendanceController::class, 'storeRecord'])->name('store-record');
        Route::get('/edit-record/{id}', [App\Http\Controllers\Web\AttendanceController::class, 'editRecord'])->name('edit-record');
        Route::put('/update-record/{id}', [App\Http\Controllers\Web\AttendanceController::class, 'updateRecord'])->name('update-record');
        Route::delete('/delete-record/{id}', [App\Http\Controllers\Web\AttendanceController::class, 'deleteRecord'])->name('delete-record');
        
        // Attendance Schedule Reports routes (separate from main schedule management)
        Route::prefix('schedule')->name('attendance.schedule.')->group(function () {
            Route::get('/reports', [App\Http\Controllers\Web\AttendanceController::class, 'scheduleReports'])->name('reports');
            Route::get('/templates', [App\Http\Controllers\Web\AttendanceController::class, 'scheduleTemplates'])->name('templates');
        });
        
        // Period Management routes
        Route::prefix('period-management')->name('period-management.')->group(function () {
            Route::get('/', [App\Http\Controllers\Web\PeriodManagementController::class, 'index'])->name('index');
            Route::get('/create', [App\Http\Controllers\Web\PeriodManagementController::class, 'create'])->name('create');
            Route::post('/', [App\Http\Controllers\Web\PeriodManagementController::class, 'store'])->name('store');
            Route::get('/{period}', [App\Http\Controllers\Web\PeriodManagementController::class, 'show'])->name('show');
            Route::delete('/{period}', [App\Http\Controllers\Web\PeriodManagementController::class, 'destroy'])->name('destroy');
        });
        
        // Overtime routes
        Route::get('/overtime', [App\Http\Controllers\Web\OvertimeController::class, 'index'])->name('overtime');
        Route::post('/overtime', [App\Http\Controllers\Web\OvertimeController::class, 'store'])->name('overtime.store');
        Route::put('/overtime/{id}/status', [App\Http\Controllers\Web\OvertimeController::class, 'updateStatus'])->name('overtime.update-status');
        Route::delete('/overtime/{id}/cancel', [App\Http\Controllers\Web\OvertimeController::class, 'cancel'])->name('overtime.cancel');
        Route::get('/overtime/statistics', [App\Http\Controllers\Web\OvertimeController::class, 'getStatistics'])->name('overtime.statistics');
        
        // Leave management routes
        Route::get('/leave-management', [App\Http\Controllers\Web\LeaveController::class, 'index'])->name('leave-management');
        Route::post('/leave-management', [App\Http\Controllers\Web\LeaveController::class, 'store'])->name('leave-management.store');
        Route::put('/leave-management/{id}/status', [App\Http\Controllers\Web\LeaveController::class, 'updateStatus'])->name('leave-management.update-status');
        Route::delete('/leave-management/{id}/cancel', [App\Http\Controllers\Web\LeaveController::class, 'cancel'])->name('leave-management.cancel');
        Route::get('/leave-management/balance', [App\Http\Controllers\Web\LeaveController::class, 'getLeaveBalance'])->name('leave-management.balance');
        Route::get('/leave-management/statistics', [App\Http\Controllers\Web\LeaveController::class, 'getStatistics'])->name('leave-management.statistics');
        
        // Admin/HR only routes
        Route::middleware(['role:admin,hr'])->group(function () {
            Route::get('/reports', function () {
                return view('attendance.reports', ['user' => auth()->user()]);
            })->name('reports');
            
            Route::get('/settings', function () {
                return view('attendance.settings', ['user' => auth()->user()]);
            })->name('settings');
        });
    });
});

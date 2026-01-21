<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RoleBasedDashboardController;
use App\Http\Controllers\Web\EmployeeController;
use App\Http\Controllers\Web\PayrollController;
use App\Http\Controllers\Web\DepartmentController;
use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\DocumentController;


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

// Public routes (only accessible to guests)
Route::middleware('guest')->group(function () {
    Route::get('/', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
    
    // Password Reset Routes
    Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])->name('password.email');
    Route::get('/reset-password', [AuthController::class, 'showResetPassword'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');
});

// Protected routes
Route::middleware(['auth', 'require.timein'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [RoleBasedDashboardController::class, 'index'])->name('dashboard');
   Route::get('/payroll/manage', [PayrollController::class, 'index'])->name('payroll.manage');
    Route::get('/payroll', [PayrollController::class, 'index'])->name('payroll.index');

    // Logout
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    
    // Employee routes
    Route::resource('employees', EmployeeController::class);
    Route::get('/employees/{employee}/payroll', [EmployeeController::class, 'payroll'])->name('employees.payroll');
    
    // Department routes
    Route::resource('departments', DepartmentController::class);
    Route::get('/departments/{department}/employees', [DepartmentController::class, 'employees'])->name('departments.employees');
    
    // Position routes
    Route::resource('positions', App\Http\Controllers\PositionController::class);
    
    // Payroll routes
    Route::resource('payrolls', PayrollController::class);
    Route::post('/payrolls/{payroll}/process', [PayrollController::class, 'process'])->name('payrolls.process');
    Route::get('/payrolls/reports/summary', [PayrollController::class, 'summary'])->name('payrolls.summary');
    Route::get('/payrolls/reports/monthly', [PayrollController::class, 'monthlyReport'])->name('payrolls.monthly');
    
    // Additional payroll processing routes
    
    Route::post('/payrolls/process-payments', [PayrollController::class, 'processPayments'])->name('payrolls.process-payments');
    Route::post('/payrolls/bulk-approve', [PayrollController::class, 'bulkApprove'])->name('payrolls.bulk-approve');
    Route::post('/payrolls/export-payroll', [PayrollController::class, 'exportPayroll'])->name('payrolls.export-payroll');
    Route::post('/payrolls/generate-payroll', [PayrollController::class, 'generatePayroll'])->name('payrolls.generate-payroll');
    Route::post('/payroll/generate', [\App\Http\Controllers\Web\PayrollController::class, 'generate'])->name('payrolls.generate');
    Route::get('/ajax/payrolls/approved', [\App\Http\Controllers\Web\PayrollController::class, 'getApprovedPayrolls']);
    Route::post('/ajax/payrolls/process-payments', [\App\Http\Controllers\Web\PayrollController::class, 'processPaymentsApi']);
    Route::get('/ajax/payrolls/status-count', [PayrollController::class, 'getPayrollStatusCount']);
    Route::post('/ajax/payrolls/bulk-approve', [PayrollController::class, 'ajaxBulkApprove']);
    Route::get('/ajax/payrolls/payment-status', [PayrollController::class, 'checkPaidStatus']);
    Route::get('/ajax/payrolls/pending', [\App\Http\Controllers\Web\PayrollController::class, 'getPendingPayrolls']);
    Route::post('/ajax/payrolls/approve-all', [\App\Http\Controllers\Web\PayrollController::class, 'approveAllViaAjax']);
    Route::post('/payroll/complete-workflow', [\App\Http\Controllers\Web\PayrollController::class, 'completePayrollWorkflow'])->name('payrolls.complete-workflow');
    Route::post('/payroll/generate', [PayrollController::class, 'generate'])->name('payrolls.generate');
    Route::post('/payrolls/generate-payslips', [PayrollController::class, 'generatePayslips'])->name('payrolls.generate-payslips');
    Route::get('/payrolls/{payroll}/download-payslip', [PayrollController::class, 'downloadPayslip'])->name('payrolls.download-payslip');
    Route::get('/payrolls/download-all-payslips', [PayrollController::class, 'downloadAllPayslips'])->name('payrolls.download-all-payslips');
    Route::post('/payrolls/{payroll}/generate-payslip', [PayrollController::class, 'generateSinglePayslip'])->name('payrolls.generate-payslip');
    Route::get('/payrolls/download-all-payslips', [PayrollController::class, 'downloadAllPayslips'])->name('payrolls.download-all-payslips');
    Route::post('/payrolls/mark-as-paid', [PayrollController::class, 'markAsPaid'])->name('payrolls.mark-as-paid');
    Route::post('/payrolls/approve-selected', [PayrollController::class, 'approveSelected'])->name('payrolls.approve-selected');
    Route::post('/payrolls/process-selected-payments', [PayrollController::class, 'processSelectedPayments'])->name('payrolls.process-selected-payments');
    Route::post('/payrolls/export-detailed', [PayrollController::class, 'exportDetailed'])->name('payrolls.export-detailed');
    Route::post('/payrolls/mark-as-paid', [PayrollController::class, 'markAsPaid'])
    ->name('payrolls.mark-as-paid')
    ->middleware('auth');
    Route::post('/payrolls/export-with-calculations', [PayrollController::class, 'exportWithCalculations'])
    ->name('payrolls.export-with-calculations');
    Route::post('/payrolls/export-with-calculations', [PayrollController::class, 'exportWithCalculations'])->name('payrolls.export-with-calculations');
    Route::post('/payrolls/export-with-calculations', [PayrollController::class, 'exportWithCalculations'])
    ->name('payrolls.export-with-calculations')
    ->middleware('auth');
    Route::post('/payrolls/simple-export', [PayrollController::class, 'simpleExport'])->name('payrolls.simple-export');

Route::post('/payrolls/generate-payslips', [PayrollController::class, 'generatePayslips'])
    ->name('payrolls.generate-payslips')
    ->middleware('auth');

Route::get('/payrolls/download-all-payslips', [PayrollController::class, 'downloadAllPayslips'])
    ->name('payrolls.download-all-payslips')
    ->middleware('auth');

Route::post('/payrolls/mark-as-paid', [PayrollController::class, 'markAsPaid'])
    ->name('payrolls.mark-as-paid')
    ->middleware('auth');
Route::get('/debug-payroll-match', [PayrollController::class, 'debugPayrollMatching']);

Route::post('/payroll/{payroll}/approve', [PayrollController::class, 'approvePayroll'])->name('payroll.approve');
Route::post('/payroll/{payroll}/reject', [PayrollController::class, 'rejectPayroll'])->name('payroll.reject');
Route::get('/payroll/{payroll}/download-payslip', [PayrollController::class, 'downloadViewPayslip'])->name('payroll.download-payslip');


    // Payroll route aliases for consistency
    Route::get('/payroll', [PayrollController::class, 'index'])->name('payroll.index');
    Route::get('/payroll/{payroll}', [PayrollController::class, 'show'])->name('payroll.show');
    
    // Debug route for payroll functions
    Route::get('/test-payroll-functions', function() {
        $service = app(\App\Services\PayrollGenerationService::class);
        
        // Test components
        $paymentModelExists = class_exists(\App\Models\Payment::class);
        $dompdfExists = class_exists(\Barryvdh\DomPDF\Facade\Pdf::class);
        $payrolls = \App\Models\Payroll::where('status', 'approved')->take(2)->get();
        $storageWritable = is_writable(storage_path());
        $payslipColumnExists = \Illuminate\Support\Facades\Schema::hasColumn('payrolls', 'payslip_file');
        
        return [
            'payment_model_exists' => $paymentModelExists,
            'dompdf_exists' => $dompdfExists,
            'storage_writable' => $storageWritable,
            'payslip_column_exists' => $payslipColumnExists,
            'approved_payrolls_count' => $payrolls->count(),
            'payrolls' => $payrolls->toArray()
        ];
    });

    // Add this to routes/web.php
Route::get('/debug-payroll-dates', function() {
    $payrolls = \App\Models\Payroll::select('id', 'employee_id', 'pay_period_start', 'pay_period_end', 'status', 'created_at')
        ->orderBy('pay_period_start', 'desc')
        ->limit(20)
        ->get();
    
    return response()->json([
        'total_payrolls' => \App\Models\Payroll::count(),
        'pending_count' => \App\Models\Payroll::where('status', 'pending')->count(),
        'approved_count' => \App\Models\Payroll::where('status', 'approved')->count(),
        'recent_payrolls' => $payrolls,
        'unique_periods' => \App\Models\Payroll::select('pay_period_start', 'pay_period_end')
            ->distinct()
            ->orderBy('pay_period_start', 'desc')
            ->get()
    ]);
});

// In routes/web.php
Route::get('/debug-current-payrolls', function() {
    $allPayrolls = \App\Models\Payroll::select(
        'id', 
        'employee_id', 
        'pay_period_start', 
        'pay_period_end', 
        'status',
        'created_at'
    )
    ->with(['employee:id,employee_id,first_name,last_name'])
    ->orderBy('pay_period_start', 'desc')
    ->limit(50)
    ->get();
    
    $uniquePeriods = \App\Models\Payroll::select('pay_period_start', 'pay_period_end')
        ->selectRaw('COUNT(*) as count')
        ->groupBy('pay_period_start', 'pay_period_end')
        ->orderBy('pay_period_start', 'desc')
        ->get();
    
    return response()->json([
        'total_payrolls' => \App\Models\Payroll::count(),
        'status_counts' => [
            'pending' => \App\Models\Payroll::where('status', 'pending')->count(),
            'approved' => \App\Models\Payroll::where('status', 'approved')->count(),
            'paid' => \App\Models\Payroll::where('status', 'paid')->count(),
        ],
        'recent_payrolls' => $allPayrolls,
        'unique_periods' => $uniquePeriods,
        'database_date_format' => 'Check if dates are YYYY-MM-DD or include time'
    ]);
});
    
    
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
    Route::post('/companies/switch', [App\Http\Controllers\Web\CompanyController::class, 'switchCompany'])->name('companies.switch');
    
    // HR Profile and Settings routes
    Route::prefix('hr')->name('hr.')->group(function () {
        Route::get('/profile', [App\Http\Controllers\Web\HrController::class, 'profile'])->name('profile');
        Route::put('/profile', [App\Http\Controllers\Web\HrController::class, 'updateProfile'])->name('profile.update');
        Route::get('/settings', [App\Http\Controllers\Web\HrController::class, 'settings'])->name('settings');
        Route::put('/settings', [App\Http\Controllers\Web\HrController::class, 'updateSettings'])->name('settings.update');
        Route::put('/settings/password', [App\Http\Controllers\Web\HrController::class, 'updatePassword'])->name('settings.password');
        Route::post('/export-data', [App\Http\Controllers\Web\HrController::class, 'exportData'])->name('export-data');
        Route::post('/backup-data', [App\Http\Controllers\Web\HrController::class, 'backupData'])->name('backup-data');
        Route::get('/sessions', [App\Http\Controllers\Web\HrController::class, 'getUserSessions'])->name('sessions');
        Route::delete('/sessions/{session}', [App\Http\Controllers\Web\HrController::class, 'terminateSession'])->name('sessions.terminate');
        Route::delete('/sessions', [App\Http\Controllers\Web\HrController::class, 'terminateAllOtherSessions'])->name('sessions.terminate-all');
        Route::post('/track-session', [App\Http\Controllers\Web\HrController::class, 'trackLoginSession'])->name('track-session');
    });
    
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
        Route::get('/daily/export/{format}', [App\Http\Controllers\Web\AttendanceController::class, 'exportDaily'])->name('daily.export');
        Route::get('/timekeeping', [App\Http\Controllers\Web\AttendanceController::class, 'timekeeping'])->name('timekeeping');
        Route::get('/timekeeping/export/{format}', [App\Http\Controllers\Web\AttendanceController::class, 'exportTimekeeping'])->name('timekeeping.export');
        Route::get('/reports', [App\Http\Controllers\Web\AttendanceController::class, 'reports'])->name('reports');
        Route::get('/reports/export/{format}', [App\Http\Controllers\Web\AttendanceController::class, 'exportReports'])->name('reports.export');
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
            
            // Payroll integration routes
            Route::get('/{period}/preview-payroll', [App\Http\Controllers\Web\PeriodManagementController::class, 'previewPayroll'])->name('preview-payroll');
            Route::post('/{period}/generate-payroll', [App\Http\Controllers\Web\PeriodManagementController::class, 'generatePayroll'])->name('generate-payroll');
            Route::get('/{period}/payroll-summary', [App\Http\Controllers\Web\PeriodManagementController::class, 'showPayrollSummary'])->name('payroll-summary');
            Route::get('/{period}/export-payroll', [App\Http\Controllers\Web\PeriodManagementController::class, 'exportPayroll'])->name('export-payroll');
        });
        
        // Overtime routes
        Route::get('/overtime', [App\Http\Controllers\Web\OvertimeController::class, 'index'])->name('overtime');
        Route::get('/overtime/export/{format}', [App\Http\Controllers\Web\OvertimeController::class, 'exportOvertime'])->name('overtime.export');
        Route::post('/overtime', [App\Http\Controllers\Web\OvertimeController::class, 'store'])->name('overtime.store');
        Route::put('/overtime/{id}/status', [App\Http\Controllers\Web\OvertimeController::class, 'updateStatus'])->name('overtime.update-status');
        Route::delete('/overtime/{id}/cancel', [App\Http\Controllers\Web\OvertimeController::class, 'cancel'])->name('overtime.cancel');
        Route::get('/overtime/statistics', [App\Http\Controllers\Web\OvertimeController::class, 'getStatistics'])->name('overtime.statistics');
        
        // Leave management routes
        Route::get('/leave-management', [App\Http\Controllers\Web\LeaveController::class, 'index'])->name('leave-management');
        Route::get('/leave-management/export/{format}', [App\Http\Controllers\Web\LeaveController::class, 'exportLeave'])->name('leave-management.export');
        Route::get('/leave-management/create', [App\Http\Controllers\Web\LeaveController::class, 'create'])->name('leave-management.create');
        Route::post('/leave-management', [App\Http\Controllers\Web\LeaveController::class, 'store'])->name('leave-management.store');
        Route::put('/leave-management/{id}/status', [App\Http\Controllers\Web\LeaveController::class, 'updateStatus'])->name('leave-management.update-status');
        Route::delete('/leave-management/{id}/cancel', [App\Http\Controllers\Web\LeaveController::class, 'cancel'])->name('leave-management.cancel');
        Route::get('/leave-management/balance', [App\Http\Controllers\Web\LeaveController::class, 'getLeaveBalance'])->name('leave-management.balance');
        Route::post('/leave-management/balance', [App\Http\Controllers\Web\LeaveController::class, 'storeBalance'])->name('leave-management.balance.store');
        Route::put('/leave-management/balance/{id}', [App\Http\Controllers\Web\LeaveController::class, 'updateBalance'])->name('leave-management.balance.update');
        Route::get('/leave-management/statistics', [App\Http\Controllers\Web\LeaveController::class, 'getStatistics'])->name('leave-management.statistics');
        
        // Admin/HR only routes
        Route::middleware(['role:admin,hr'])->group(function () {
            Route::get('/reports', [App\Http\Controllers\Web\AttendanceController::class, 'reports'])->name('reports');
            
            Route::get('/settings', function () {
                return view('attendance.settings', ['user' => auth()->user()]);
            })->name('settings');
        });
    });
    
    // Tax Bracket Management routes (outside attendance prefix)
    Route::resource('tax-brackets', App\Http\Controllers\Web\TaxBracketController::class);
    Route::post('/tax-brackets/calculate', [App\Http\Controllers\Web\TaxBracketController::class, 'calculateTax'])->name('tax-brackets.calculate');
    Route::post('/tax-brackets/philippine', [App\Http\Controllers\Web\TaxBracketController::class, 'createPhilippineBrackets'])->name('tax-brackets.philippine');

    // Add these routes to your web.php file

    // Document Management routes
    Route::middleware(['auth'])->group(function () {
    // Documents routes
    Route::get('/documents', [DocumentController::class, 'index'])->name('documents.index');
    Route::post('/documents/switch-company', [DocumentController::class, 'switchCompany'])->name('documents.switch-company');
    Route::get('/documents/export', [DocumentController::class, 'export'])->name('documents.export');
    Route::get('/documents/employee/{id}/export', [DocumentController::class, 'exportEmployee'])->name('documents.employee.export');
    
    // Employee details for modal
    Route::get('/employees/{id}/details', [DocumentController::class, 'getEmployeeDetails']);
    
    // Employee documents page
    Route::get('/employees/{id}/documents', [EmployeeController::class, 'documents'])->name('employees.documents');
});
    
});
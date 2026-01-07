<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use App\Models\LeaveBalance;
use App\Models\Employee;
use App\Helpers\TimezoneHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class LeaveController extends Controller
{
    /**
     * Display leave management page
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Check if user is an employee
        $userRole = strtolower(trim($user->role ?? ''));
        $isEmployee = ($userRole === 'employee');
        
        // Get employee ID for filtering if user is an employee
        $employeeIdForFilter = null;
        if ($isEmployee) {
            // Try multiple ways to get the employee ID
            $employee = null;
            
            // Method 1: Try relationship
            try {
                if ($user->employee) {
                    $employee = $user->employee;
                }
            } catch (\Exception $e) {
                \Log::warning('Employee relationship failed in leave index', ['error' => $e->getMessage()]);
            }
            
            // Method 2: Try direct find if employee_id exists
            if (!$employee && $user->employee_id) {
                $employee = Employee::find($user->employee_id);
            }
            
            // Method 3: Try to find by account relationship
            if (!$employee) {
                $employee = Employee::whereHas('account', function($q) use ($user) {
                    $q->where('id', $user->id);
                })->first();
            }
            
            if ($employee && $employee->id) {
                $employeeIdForFilter = $employee->id;
            }
        }
        
        $query = LeaveRequest::with(['employee.department', 'approvedBy']);

        // CRITICAL: For employees, restrict to ONLY their own leave requests
        if ($employeeIdForFilter !== null) {
            $query->where('employee_id', $employeeIdForFilter);
        } else {
            // For admin/hr/manager, apply filters
        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        if ($request->filled('department_id')) {
            $query->whereHas('employee', function($q) use ($request) {
                $q->where('department_id', $request->department_id);
            });
            }
        }

        if ($request->filled('leave_type')) {
            $query->where('leave_type', $request->leave_type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->where('start_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('end_date', '<=', $request->date_to);
        }

        $leaveRequests = $query->orderBy('start_date', 'desc')
         ->orderBy('start_date', 'desc')
         ->orderBy('created_at', 'desc')
         ->paginate(20);

        // Get employees (filtered by company if applicable)
        // For employees, only show their own record
        $employeesQuery = Employee::with('department');
        $currentCompany = \App\Helpers\CompanyHelper::getCurrentCompany();
        
        if ($employeeIdForFilter !== null) {
            // Employee can only see their own record
            $employeesQuery->where('id', $employeeIdForFilter);
        } else {
            // For admin/hr/manager, show all employees (filtered by company)
            if ($currentCompany) {
            $employeesQuery->where('company_id', $currentCompany->id);
        }
        }
        
        $employees = $employeesQuery->get();
        
        $departments = \App\Models\Department::all();

        // Calculate summary statistics
        $summary = $this->calculateLeaveSummary($leaveRequests);

        // Get leave balances for current year (filtered by company employees)
        $employeeIds = $employees->pluck('id')->toArray();
        $leaveBalances = LeaveBalance::with('employee')
            ->where('year', now()->year)
            ->whereIn('employee_id', $employeeIds)
            ->get()
            ->groupBy('employee_id');

        // Sync balances for approved leaves that might not have been deducted (for existing records)
        // This ensures any approved leaves that were created before balance deduction was implemented get synced
        $syncedCount = 0;
        try {
            $syncedCount = $this->syncApprovedLeaveBalances($employeeIds);
            if ($syncedCount > 0) {
                \Log::info('Synced ' . $syncedCount . ' leave balances in index method');
            }
        } catch (\Exception $e) {
            \Log::error('Error syncing leave balances in index: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
        }

        // Refresh balances after sync to ensure we have the latest data
        // Check current year and next year for year-end leaves
        $currentYear = now()->year;
        $leaveBalances = LeaveBalance::with('employee')
            ->where(function($q) use ($currentYear) {
                $q->where('year', $currentYear)
                  ->orWhere('year', $currentYear + 1)
                  ->orWhere('year', $currentYear - 1);
            })
            ->whereIn('employee_id', $employeeIds)
            ->get()
            ->groupBy('employee_id');

        // Check if there are employees without leave balances
        $employeesWithBalances = $leaveBalances->keys()->toArray();
        $employeesWithoutBalances = $employees->whereNotIn('id', $employeesWithBalances)->count();
        $hasEmployeesWithoutBalances = $employeesWithoutBalances > 0;

        return view('attendance.leave-management', compact('leaveRequests', 'employees', 'departments', 'summary', 'leaveBalances', 'user', 'hasEmployeesWithoutBalances'));
    }

    /**
     * Show the form for creating a new leave request
     */
    public function create(Request $request)
    {
        $user = Auth::user();
        $employee = $user->employee;
        
        if (!$employee && !in_array($user->role, ['admin', 'hr'])) {
            return redirect()->route('attendance.leave-management')->with('error', 'Employee record not found.');
        }

        // Get employees list for HR/Admin
        $employees = [];
        if (in_array($user->role, ['admin', 'hr'])) {
            $employees = Employee::with('department')->orderBy('first_name')->get();
        }

        // Get leave balance for current employee or selected employee
        $selectedEmployeeId = $request->get('employee_id', $employee?->id);
        $leaveBalance = null;
        $availableDays = [];

        if ($selectedEmployeeId) {
            $selectedEmployee = Employee::find($selectedEmployeeId);
            if ($selectedEmployee) {
                $leaveBalance = $selectedEmployee->getLeaveBalanceForYear(now()->year);
                if ($leaveBalance) {
                    $leaveTypes = ['vacation', 'sick', 'personal', 'emergency', 'maternity', 'paternity', 'bereavement', 'study'];
                    foreach ($leaveTypes as $type) {
                        $availableDays[$type] = $this->getAvailableLeaveDays($leaveBalance, $type);
                    }
                }
            }
        }

        return view('attendance.leave-request-create', compact('user', 'employee', 'employees', 'leaveBalance', 'availableDays'));
    }

    /**
     * Store a new leave request
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        
        // Role-based employee selection
        if (in_array($user->role, ['admin', 'hr'])) {
            // HR/Admin can submit for any employee
            $request->validate([
                'employee_id' => 'required|exists:employees,id',
            ]);
            $employee = Employee::findOrFail($request->employee_id);
        } else {
            // Employees can only submit for themselves
            $employee = $user->employee;
            if (!$employee) {
                return redirect()->route('attendance.leave-management.create')
                    ->with('error', 'Employee record not found.')
                    ->withInput();
            }
        }

        $request->validate([
            'leave_type' => 'required|in:vacation,sick,personal,emergency,maternity,paternity,bereavement,study',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string|max:500',
        ]);

        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);
        $daysRequested = $startDate->diffInDays($endDate) + 1;

        // Check leave balance
        $leaveBalance = $employee->getLeaveBalanceForYear(now()->year);
        if (!$leaveBalance) {
            $error = 'Leave balance not found for current year.';
            if ($request->expectsJson()) {
                return response()->json(['error' => $error], 400);
            }
            return redirect()->route('attendance.leave-management.create')
                ->with('error', $error)
                ->withInput();
        }

        $availableDays = $this->getAvailableLeaveDays($leaveBalance, $request->leave_type);
        if ($availableDays < $daysRequested) {
            $error = 'Insufficient leave balance. Available: ' . $availableDays . ' days.';
            if ($request->expectsJson()) {
                return response()->json(['error' => $error], 400);
            }
            return redirect()->route('attendance.leave-management.create')
                ->with('error', $error)
                ->withInput();
        }

        // Check for overlapping leave requests
        $overlappingRequest = LeaveRequest::where('employee_id', $employee->id)
            ->where('status', '!=', 'rejected')
            ->where(function($query) use ($startDate, $endDate) {
                $query->whereBetween('start_date', [$startDate, $endDate])
                      ->orWhereBetween('end_date', [$startDate, $endDate])
                      ->orWhere(function($q) use ($startDate, $endDate) {
                          $q->where('start_date', '<=', $startDate)
                            ->where('end_date', '>=', $endDate);
                      });
            })
            ->first();

        if ($overlappingRequest) {
            $error = 'You already have a leave request for this period.';
            if ($request->expectsJson()) {
                return response()->json(['error' => $error], 400);
            }
            return redirect()->route('attendance.leave-management.create')
                ->with('error', $error)
                ->withInput();
        }

        $leaveRequest = LeaveRequest::create([
            'employee_id' => $employee->id,
            'leave_type' => $request->leave_type,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'days_requested' => $daysRequested,
            'reason' => $request->reason,
            'status' => 'pending',
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Leave request submitted successfully',
                'leave_request' => $leaveRequest->load('employee.department'),
            ]);
        }

        return redirect()->route('attendance.leave-management')
            ->with('success', 'Leave request submitted successfully.');
    }

    /**
     * Update leave request status (approve/reject)
     */
    public function updateStatus(Request $request, $id)
    {
        try {
            $user = Auth::user();
            
            // Check if user has permission to approve/reject (case-insensitive)
            $userRole = strtolower($user->role ?? '');
            if (!in_array($userRole, ['admin', 'hr'])) {
                return response()->json(['success' => false, 'error' => 'Unauthorized access.'], 403);
            }

            $validated = $request->validate([
                'status' => 'required|in:approved,rejected',
                'rejection_reason' => 'nullable|required_if:status,rejected|string|max:500',
            ]);

            $leaveRequest = LeaveRequest::findOrFail($id);

            if ($leaveRequest->status !== 'pending') {
                return response()->json(['success' => false, 'error' => 'This leave request has already been processed.'], 400);
            }

            // If approved, update leave balance BEFORE updating status
            // This ensures balance is deducted atomically with approval
            if ($validated['status'] === 'approved') {
                try {
                    // Update balance first (before status change)
                    // The updateLeaveBalance method will check status, so we need to temporarily allow it
                    // or update the method to accept a flag
                    $this->updateLeaveBalance($leaveRequest, true); // Pass flag to indicate pre-approval update
                } catch (\Exception $e) {
                    // If balance update fails, we should not approve the leave
                    \Log::error('Failed to update leave balance for leave request ' . $id . ': ' . $e->getMessage(), [
                        'leave_request_id' => $id,
                        'employee_id' => $leaveRequest->employee_id,
                        'leave_type' => $leaveRequest->leave_type,
                        'days_requested' => $leaveRequest->days_requested,
                        'exception' => $e
                    ]);
                    return response()->json([
                        'success' => false,
                        'error' => 'Failed to update leave balance: ' . $e->getMessage() . '. Leave request was not approved.',
                    ], 500);
                }
            }

            // Update leave request status after balance is successfully updated
            $leaveRequest->update([
                'status' => $validated['status'],
                'approved_by' => $validated['status'] === 'approved' ? $user->id : null,
                'approved_at' => $validated['status'] === 'approved' ? TimezoneHelper::now() : null,
                'rejection_reason' => $validated['status'] === 'rejected' ? ($validated['rejection_reason'] ?? null) : null,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Leave request ' . $validated['status'] . ' successfully',
                'leave_request' => $leaveRequest->fresh(['employee.department', 'approvedBy']),
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $errors = [];
            foreach ($e->errors() as $field => $messages) {
                $errors[] = $field . ': ' . implode(', ', $messages);
            }
            return response()->json([
                'success' => false,
                'error' => 'Validation failed: ' . implode(' | ', $errors),
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Error updating leave request status: ' . $e->getMessage(), [
                'exception' => $e,
                'leave_request_id' => $id,
                'user_id' => Auth::id(),
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'An error occurred: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Cancel a leave request
     */
    public function cancel($id)
    {
        $user = Auth::user();
        $employee = $user->employee;
        
        if (!$employee) {
            return response()->json(['error' => 'Employee record not found.'], 404);
        }

        $leaveRequest = LeaveRequest::where('id', $id)
            ->where('employee_id', $employee->id)
            ->firstOrFail();

        if (!in_array($leaveRequest->status, ['pending', 'approved'])) {
            return response()->json(['error' => 'Only pending or approved leave requests can be cancelled.'], 400);
        }

        // If it was approved, restore leave balance
        if ($leaveRequest->status === 'approved') {
            $this->restoreLeaveBalance($leaveRequest);
        }

        $leaveRequest->update(['status' => 'cancelled']);

        return response()->json([
            'success' => true,
            'message' => 'Leave request cancelled successfully',
        ]);
    }

    /**
     * Get leave balance for employee
     */
    public function getLeaveBalance(Request $request)
    {
        $user = Auth::user();
        
        // For HR/Admin, allow getting balance for any employee
        if (in_array($user->role, ['admin', 'hr']) && $request->has('employee_id')) {
            $employee = Employee::find($request->employee_id);
        } else {
            $employee = $user->employee;
        }
        
        if (!$employee) {
            return response()->json(['error' => 'Employee record not found.'], 404);
        }

        $year = $request->get('year', now()->year);
        $leaveBalance = $employee->getLeaveBalanceForYear($year);

        if (!$leaveBalance) {
            return response()->json(['error' => 'Leave balance not found for the specified year.'], 404);
        }

        // Calculate available days
        $leaveTypes = ['vacation', 'sick', 'personal', 'emergency', 'maternity', 'paternity', 'bereavement', 'study'];
        $availableDays = [];
        foreach ($leaveTypes as $type) {
            $availableDays[$type] = $this->getAvailableLeaveDays($leaveBalance, $type);
        }

        return response()->json([
            'leave_balance' => $leaveBalance,
            'available_days' => $availableDays,
        ]);
    }

    /**
     * Store leave balance
     */
    public function storeBalance(Request $request)
    {
        $user = Auth::user();
        
        // Check if user has permission
        if (!in_array($user->role, ['admin', 'hr', 'manager'])) {
            return response()->json(['error' => 'Unauthorized access.'], 403);
        }

        $request->validate([
            'employee_id' => 'required',
            'year' => 'required|integer|min:2020|max:2100',
            'vacation_days_total' => 'required|integer|min:0',
            'sick_days_total' => 'required|integer|min:0',
            'personal_days_total' => 'required|integer|min:0',
            'emergency_days_total' => 'required|integer|min:0',
            'maternity_days_total' => 'required|integer|min:0',
            'paternity_days_total' => 'required|integer|min:0',
            'bereavement_days_total' => 'required|integer|min:0',
            'study_days_total' => 'required|integer|min:0',
        ]);

        // Check if "all employees" is selected
        if ($request->employee_id === 'all') {
            // Get all employees
            $employees = Employee::all();
            $created = 0;
            $updated = 0;
            $skipped = 0;

            foreach ($employees as $employee) {
                $existingBalance = LeaveBalance::where('employee_id', $employee->id)
                    ->where('year', $request->year)
                    ->first();

                if ($existingBalance) {
                    // Update existing balance, ensuring used days don't exceed new totals
                    $existingBalance->update([
                        'vacation_days_total' => max($request->vacation_days_total, $existingBalance->vacation_days_used),
                        'sick_days_total' => max($request->sick_days_total, $existingBalance->sick_days_used),
                        'personal_days_total' => max($request->personal_days_total, $existingBalance->personal_days_used),
                        'emergency_days_total' => max($request->emergency_days_total, $existingBalance->emergency_days_used),
                        'maternity_days_total' => max($request->maternity_days_total, $existingBalance->maternity_days_used),
                        'paternity_days_total' => max($request->paternity_days_total, $existingBalance->paternity_days_used),
                        'bereavement_days_total' => max($request->bereavement_days_total, $existingBalance->bereavement_days_used),
                        'study_days_total' => max($request->study_days_total, $existingBalance->study_days_used),
                    ]);
                    $updated++;
                } else {
                    // Create new balance
                    LeaveBalance::create([
                        'employee_id' => $employee->id,
                        'year' => $request->year,
                        'vacation_days_total' => $request->vacation_days_total,
                        'vacation_days_used' => 0,
                        'sick_days_total' => $request->sick_days_total,
                        'sick_days_used' => 0,
                        'personal_days_total' => $request->personal_days_total,
                        'personal_days_used' => 0,
                        'emergency_days_total' => $request->emergency_days_total,
                        'emergency_days_used' => 0,
                        'maternity_days_total' => $request->maternity_days_total,
                        'maternity_days_used' => 0,
                        'paternity_days_total' => $request->paternity_days_total,
                        'paternity_days_used' => 0,
                        'bereavement_days_total' => $request->bereavement_days_total,
                        'bereavement_days_used' => 0,
                        'study_days_total' => $request->study_days_total,
                        'study_days_used' => 0,
                    ]);
                    $created++;
                }
            }

            return response()->json([
                'success' => true,
                'message' => "Leave balance set for all employees. Created: {$created}, Updated: {$updated}",
            ]);
        }

        // Single employee validation
        $request->validate([
            'employee_id' => 'exists:employees,id',
        ]);

        // Check if balance already exists
        $existingBalance = LeaveBalance::where('employee_id', $request->employee_id)
            ->where('year', $request->year)
            ->first();

        if ($existingBalance) {
            return response()->json(['error' => 'Leave balance already exists for this employee and year. Use update instead.'], 400);
        }

        $leaveBalance = LeaveBalance::create([
            'employee_id' => $request->employee_id,
            'year' => $request->year,
            'vacation_days_total' => $request->vacation_days_total,
            'vacation_days_used' => 0,
            'sick_days_total' => $request->sick_days_total,
            'sick_days_used' => 0,
            'personal_days_total' => $request->personal_days_total,
            'personal_days_used' => 0,
            'emergency_days_total' => $request->emergency_days_total,
            'emergency_days_used' => 0,
            'maternity_days_total' => $request->maternity_days_total,
            'maternity_days_used' => 0,
            'paternity_days_total' => $request->paternity_days_total,
            'paternity_days_used' => 0,
            'bereavement_days_total' => $request->bereavement_days_total,
            'bereavement_days_used' => 0,
            'study_days_total' => $request->study_days_total,
            'study_days_used' => 0,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Leave balance created successfully',
            'leave_balance' => $leaveBalance->load('employee'),
        ]);
    }

    /**
     * Update leave balance
     */
    public function updateBalance(Request $request, $id)
    {
        $user = Auth::user();
        
        // Check if user has permission
        if (!in_array($user->role, ['admin', 'hr', 'manager'])) {
            return response()->json(['error' => 'Unauthorized access.'], 403);
        }

        $request->validate([
            'vacation_days_total' => 'required|integer|min:0',
            'sick_days_total' => 'required|integer|min:0',
            'personal_days_total' => 'required|integer|min:0',
            'emergency_days_total' => 'required|integer|min:0',
            'maternity_days_total' => 'required|integer|min:0',
            'paternity_days_total' => 'required|integer|min:0',
            'bereavement_days_total' => 'required|integer|min:0',
            'study_days_total' => 'required|integer|min:0',
        ]);

        $leaveBalance = LeaveBalance::findOrFail($id);

        // Ensure used days don't exceed new totals
        $leaveBalance->update([
            'vacation_days_total' => max($request->vacation_days_total, $leaveBalance->vacation_days_used),
            'sick_days_total' => max($request->sick_days_total, $leaveBalance->sick_days_used),
            'personal_days_total' => max($request->personal_days_total, $leaveBalance->personal_days_used),
            'emergency_days_total' => max($request->emergency_days_total, $leaveBalance->emergency_days_used),
            'maternity_days_total' => max($request->maternity_days_total, $leaveBalance->maternity_days_used),
            'paternity_days_total' => max($request->paternity_days_total, $leaveBalance->paternity_days_used),
            'bereavement_days_total' => max($request->bereavement_days_total, $leaveBalance->bereavement_days_used),
            'study_days_total' => max($request->study_days_total, $leaveBalance->study_days_used),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Leave balance updated successfully',
            'leave_balance' => $leaveBalance->fresh()->load('employee'),
        ]);
    }

    /**
     * Get leave statistics
     */
    public function getStatistics(Request $request)
    {
        $date = $request->get('date', today());
        $date = Carbon::parse($date);

        $stats = [
            'today' => $this->getTodayLeaveStats($date),
            'this_week' => $this->getWeekLeaveStats($date),
            'this_month' => $this->getMonthLeaveStats($date),
        ];

        return response()->json($stats);
    }

    /**
     * Calculate leave summary
     */
    private function calculateLeaveSummary($leaveRequests)
    {
        $total = $leaveRequests->count();
        $pending = $leaveRequests->where('status', 'pending')->count();
        $approved = $leaveRequests->where('status', 'approved')->count();
        $rejected = $leaveRequests->where('status', 'rejected')->count();
        $totalDays = $leaveRequests->where('status', 'approved')->sum('days_requested');

        return [
            'total' => $total,
            'pending' => $pending,
            'approved' => $approved,
            'rejected' => $rejected,
            'total_days' => $totalDays,
        ];
    }

    /**
     * Get available leave days for a specific type
     */
    private function getAvailableLeaveDays($leaveBalance, $leaveType)
    {
        $totalField = $leaveType . '_days_total';
        $usedField = $leaveType . '_days_used';
        
        return $leaveBalance->$totalField - $leaveBalance->$usedField;
    }

    /**
     * Get all available leave days
     */
    private function getAllAvailableLeaveDays($leaveBalance)
    {
        $leaveTypes = ['vacation', 'sick', 'personal', 'emergency', 'maternity', 'paternity', 'bereavement', 'study'];
        $available = [];

        foreach ($leaveTypes as $type) {
            $available[$type] = $this->getAvailableLeaveDays($leaveBalance, $type);
        }

        return $available;
    }

    /**
     * Update leave balance when request is approved
     * 
     * @param LeaveRequest $leaveRequest The leave request to process
     * @param bool $preApproval If true, skip status check (used when updating before status change)
     */
    private function updateLeaveBalance($leaveRequest, $preApproval = false)
    {
        try {
            // Reload the leave request to ensure we have the latest status
            $leaveRequest->refresh();
            
            // In pre-approval mode, we're updating balance BEFORE status change, so status should be pending
            if ($preApproval) {
                if ($leaveRequest->status !== 'pending') {
                    \Log::warning('Leave request status is not pending in pre-approval mode, skipping balance update', [
                        'leave_request_id' => $leaveRequest->id,
                        'status' => $leaveRequest->status
                    ]);
                    return;
                }
            } else {
                // Not in pre-approval mode - status should be approved
                if ($leaveRequest->status !== 'approved') {
                    \Log::warning('Leave request is not approved, skipping balance update', [
                        'leave_request_id' => $leaveRequest->id,
                        'status' => $leaveRequest->status
                    ]);
                    return;
                }
                
                // Check if balance was already deducted by checking if this leave was processed before
                // We can't easily check this, but we'll rely on the status check above
                // If status is approved but balance wasn't deducted (old records), we should still deduct
            }

            if (!$leaveRequest->employee) {
                \Log::warning('Leave request has no employee: ' . $leaveRequest->id);
                return;
            }

            if (!$leaveRequest->start_date) {
                \Log::warning('Leave request has no start_date: ' . $leaveRequest->id);
                return;
            }

            $year = $leaveRequest->start_date instanceof \Carbon\Carbon 
                ? $leaveRequest->start_date->year 
                : \Carbon\Carbon::parse($leaveRequest->start_date)->year;

            $leaveBalance = $leaveRequest->employee->getLeaveBalanceForYear($year);
            if (!$leaveBalance) {
                \Log::warning('Leave balance not found for employee ' . $leaveRequest->employee_id . ' year ' . $year);
                return;
            }

            $usedField = $leaveRequest->leave_type . '_days_used';
            $totalField = $leaveRequest->leave_type . '_days_total';
            
            if (!isset($leaveBalance->$usedField) || !isset($leaveBalance->$totalField)) {
                \Log::warning('Invalid leave type field for leave request ' . $leaveRequest->id, [
                    'leave_type' => $leaveRequest->leave_type,
                    'used_field' => $usedField,
                    'total_field' => $totalField
                ]);
                return;
            }

            $daysRequested = $leaveRequest->days_requested ?? 0;
            if ($daysRequested <= 0) {
                \Log::warning('Invalid days_requested: ' . $daysRequested . ' for leave request ' . $leaveRequest->id);
                return;
            }

            // Get current values before update
            $currentUsed = $leaveBalance->$usedField ?? 0;
            $currentTotal = $leaveBalance->$totalField ?? 0;
            $currentAvailable = $currentTotal - $currentUsed;

            // Check if there's enough balance (should have been checked during request creation, but double-check)
            if ($currentAvailable < $daysRequested) {
                \Log::warning('Insufficient leave balance for approval', [
                    'leave_request_id' => $leaveRequest->id,
                    'employee_id' => $leaveRequest->employee_id,
                    'leave_type' => $leaveRequest->leave_type,
                    'days_requested' => $daysRequested,
                    'available' => $currentAvailable,
                    'total' => $currentTotal,
                    'used' => $currentUsed
                ]);
                // Don't throw - let the approval proceed but log the warning
            }

            // Increment the used days
            $leaveBalance->increment($usedField, $daysRequested);
            
            // Refresh to get updated values
            $leaveBalance->refresh();
            
            \Log::info('Leave balance updated successfully', [
                'leave_request_id' => $leaveRequest->id,
                'employee_id' => $leaveRequest->employee_id,
                'employee_name' => $leaveRequest->employee->full_name ?? 'N/A',
                'leave_type' => $leaveRequest->leave_type,
                'days_requested' => $daysRequested,
                'year' => $year,
                'before_used' => $currentUsed,
                'after_used' => $leaveBalance->$usedField,
                'total' => $leaveBalance->$totalField,
                'remaining' => $leaveBalance->$totalField - $leaveBalance->$usedField
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error updating leave balance: ' . $e->getMessage(), [
                'leave_request_id' => $leaveRequest->id ?? null,
                'employee_id' => $leaveRequest->employee_id ?? null,
                'leave_type' => $leaveRequest->leave_type ?? null,
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Sync leave balances for approved leaves that might not have been deducted
     * This handles cases where leaves were approved before the balance deduction feature was implemented
     * It calculates the expected used days based on all approved leaves and updates balances accordingly
     */
    private function syncApprovedLeaveBalances($employeeIds = [])
    {
        try {
            \Log::info('Starting leave balance sync', [
                'employee_ids_count' => count($employeeIds),
                'year' => now()->year
            ]);
            
            // Group by employee and leave type to calculate totals efficiently
            // Check for approved leaves in current year and next year (to handle year-end leaves)
            $currentYear = now()->year;
            $nextYear = $currentYear + 1;
            
            $query = LeaveRequest::where('status', 'approved')
                ->where(function($q) use ($currentYear, $nextYear) {
                    $q->whereYear('start_date', $currentYear)
                      ->orWhereYear('start_date', $nextYear)
                      ->orWhereYear('start_date', $currentYear - 1); // Also check previous year for completeness
                });
            
            if (!empty($employeeIds)) {
                $query->whereIn('employee_id', $employeeIds);
            }
            
            // Get all approved leaves
            $approvedLeaves = $query->with('employee')->get();
            
            \Log::info('Found approved leaves for sync', [
                'count' => $approvedLeaves->count()
            ]);
            
            // Group by employee_id, year, and leave_type to calculate totals
            $expectedUsed = [];
            foreach ($approvedLeaves as $leave) {
                if (!$leave->employee_id || !$leave->leave_type || !$leave->days_requested) {
                    continue;
                }
                
                $year = $leave->start_date instanceof \Carbon\Carbon 
                    ? $leave->start_date->year 
                    : \Carbon\Carbon::parse($leave->start_date)->year;
                
                $key = $leave->employee_id . '_' . $year . '_' . $leave->leave_type;
                if (!isset($expectedUsed[$key])) {
                    $expectedUsed[$key] = [
                        'employee_id' => $leave->employee_id,
                        'year' => $year,
                        'leave_type' => $leave->leave_type,
                        'total_days' => 0,
                        'leave_ids' => []
                    ];
                }
                $expectedUsed[$key]['total_days'] += ($leave->days_requested ?? 0);
                $expectedUsed[$key]['leave_ids'][] = $leave->id;
            }
            
            \Log::info('Grouped approved leaves', [
                'groups_count' => count($expectedUsed)
            ]);
            
            $syncedCount = 0;
            $skippedCount = 0;
            
            // Update balances based on expected totals
            foreach ($expectedUsed as $key => $data) {
                try {
                    $employee = Employee::find($data['employee_id']);
                    if (!$employee) {
                        \Log::warning('Employee not found for sync', ['employee_id' => $data['employee_id']]);
                        continue;
                    }
                    
                    $leaveBalance = $employee->getLeaveBalanceForYear($data['year']);
                    if (!$leaveBalance) {
                        \Log::warning('Leave balance not found for sync', [
                            'employee_id' => $data['employee_id'],
                            'year' => $data['year']
                        ]);
                        continue;
                    }
                    
                    $usedField = $data['leave_type'] . '_days_used';
                    if (!isset($leaveBalance->$usedField)) {
                        \Log::warning('Invalid leave type field', [
                            'leave_type' => $data['leave_type'],
                            'used_field' => $usedField
                        ]);
                        continue;
                    }
                    
                    $currentUsed = $leaveBalance->$usedField ?? 0;
                    $expectedTotal = $data['total_days'];
                    
                    \Log::info('Checking balance sync', [
                        'employee_id' => $data['employee_id'],
                        'leave_type' => $data['leave_type'],
                        'current_used' => $currentUsed,
                        'expected_total' => $expectedTotal,
                        'leave_ids' => $data['leave_ids']
                    ]);
                    
                    // If current used is less than expected, update it
                    if ($currentUsed < $expectedTotal) {
                        $difference = $expectedTotal - $currentUsed;
                        
                        // Use update instead of increment to ensure atomicity
                        $leaveBalance->update([
                            $usedField => $expectedTotal
                        ]);
                        
                        // Refresh to get updated values
                        $leaveBalance->refresh();
                        $syncedCount++;
                        
                        \Log::info('Synced leave balance successfully', [
                            'employee_id' => $data['employee_id'],
                            'employee_name' => $employee->full_name ?? 'N/A',
                            'leave_type' => $data['leave_type'],
                            'year' => $data['year'],
                            'days_added' => $difference,
                            'expected_total' => $expectedTotal,
                            'previous_used' => $currentUsed,
                            'new_used' => $leaveBalance->$usedField,
                            'leave_request_ids' => $data['leave_ids']
                        ]);
                    } else {
                        $skippedCount++;
                        \Log::info('Balance already synced, skipping', [
                            'employee_id' => $data['employee_id'],
                            'leave_type' => $data['leave_type'],
                            'current_used' => $currentUsed,
                            'expected_total' => $expectedTotal
                        ]);
                    }
                } catch (\Exception $e) {
                    \Log::error('Error syncing balance for ' . $key, [
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                }
            }
            
            \Log::info('Leave balance sync completed', [
                'synced_balances' => $syncedCount,
                'skipped' => $skippedCount,
                'total_checked' => count($expectedUsed)
            ]);
            
            return $syncedCount;
        } catch (\Exception $e) {
            \Log::error('Error in syncApprovedLeaveBalances: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return 0;
        }
    }

    /**
     * Restore leave balance when request is cancelled
     */
    private function restoreLeaveBalance($leaveRequest)
    {
        try {
            // Reload the leave request to ensure we have the latest status
            $leaveRequest->refresh();
            
            // Only restore if it was previously approved
            if ($leaveRequest->status !== 'approved' && $leaveRequest->status !== 'cancelled') {
                \Log::info('Leave request not approved, skipping balance restoration', [
                    'leave_request_id' => $leaveRequest->id,
                    'status' => $leaveRequest->status
                ]);
                return;
            }

            if (!$leaveRequest->employee) {
                \Log::warning('Leave request has no employee for balance restoration: ' . $leaveRequest->id);
                return;
            }

            if (!$leaveRequest->start_date) {
                \Log::warning('Leave request has no start_date for balance restoration: ' . $leaveRequest->id);
                return;
            }

            $year = $leaveRequest->start_date instanceof \Carbon\Carbon 
                ? $leaveRequest->start_date->year 
                : \Carbon\Carbon::parse($leaveRequest->start_date)->year;

            $leaveBalance = $leaveRequest->employee->getLeaveBalanceForYear($year);
        if (!$leaveBalance) {
                \Log::warning('Leave balance not found for employee ' . $leaveRequest->employee_id . ' year ' . $year . ' during restoration');
            return;
        }

        $usedField = $leaveRequest->leave_type . '_days_used';
            if (!isset($leaveBalance->$usedField)) {
                \Log::warning('Invalid leave type field: ' . $usedField . ' for leave request ' . $leaveRequest->id . ' during restoration');
                return;
            }

            $daysRequested = $leaveRequest->days_requested ?? 0;
            if ($daysRequested <= 0) {
                \Log::warning('Invalid days_requested: ' . $daysRequested . ' for leave request ' . $leaveRequest->id . ' during restoration');
                return;
            }

            // Get current values before update
            $currentUsed = $leaveBalance->$usedField ?? 0;
            
            // Ensure we don't decrement below 0
            $newUsed = max(0, $currentUsed - $daysRequested);
            
            // Decrement the used days
            $leaveBalance->decrement($usedField, $daysRequested);
            
            // Refresh to get updated values
            $leaveBalance->refresh();
            
            \Log::info('Leave balance restored successfully', [
                'leave_request_id' => $leaveRequest->id,
                'employee_id' => $leaveRequest->employee_id,
                'employee_name' => $leaveRequest->employee->full_name ?? 'N/A',
                'leave_type' => $leaveRequest->leave_type,
                'days_restored' => $daysRequested,
                'year' => $year,
                'before_used' => $currentUsed,
                'after_used' => $leaveBalance->$usedField,
                'total' => $leaveBalance->{$leaveRequest->leave_type . '_days_total'} ?? 0,
                'remaining' => ($leaveBalance->{$leaveRequest->leave_type . '_days_total'} ?? 0) - $leaveBalance->$usedField
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error restoring leave balance: ' . $e->getMessage(), [
                'leave_request_id' => $leaveRequest->id ?? null,
                'employee_id' => $leaveRequest->employee_id ?? null,
                'leave_type' => $leaveRequest->leave_type ?? null,
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);
            // Don't throw - allow cancellation to proceed even if balance restoration fails
        }
    }

    private function getTodayLeaveStats($date)
    {
        $requests = LeaveRequest::where('start_date', '<=', $date)
            ->where('end_date', '>=', $date)
            ->where('status', 'approved')
            ->get();
        
        return [
            'employees_on_leave' => $requests->count(),
            'leave_types' => $requests->groupBy('leave_type')->map->count(),
        ];
    }

    private function getWeekLeaveStats($date)
    {
        $weekStart = $date->copy()->startOfWeek();
        $weekEnd = $date->copy()->endOfWeek();

        $requests = LeaveRequest::where('start_date', '<=', $weekEnd)
            ->where('end_date', '>=', $weekStart)
            ->where('status', 'approved')
            ->get();
        
        return [
            'total_requests' => $requests->count(),
            'total_days' => $requests->sum('days_requested'),
        ];
    }

    private function getMonthLeaveStats($date)
    {
        $monthStart = $date->copy()->startOfMonth();
        $monthEnd = $date->copy()->endOfMonth();

        $requests = LeaveRequest::where('start_date', '<=', $monthEnd)
            ->where('end_date', '>=', $monthStart)
            ->where('status', 'approved')
            ->get();
        
        return [
            'total_requests' => $requests->count(),
            'total_days' => $requests->sum('days_requested'),
            'average_days_per_request' => $requests->avg('days_requested') ?? 0,
        ];
    }

    /**
     * Export leave requests
     */
    public function exportLeave(Request $request, $format)
    {
        $user = Auth::user();
        $currentCompany = \App\Helpers\CompanyHelper::getCurrentCompany();
        
        // Build query same as index method
        $query = LeaveRequest::with(['employee.department', 'approvedBy']);

        // Apply filters
        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        if ($request->filled('department_id')) {
            $query->whereHas('employee', function($q) use ($request) {
                $q->where('department_id', $request->department_id);
            });
        }

        if ($request->filled('leave_type')) {
            $query->where('leave_type', $request->leave_type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->where('start_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('end_date', '<=', $request->date_to);
        }

        // Get all records (no pagination for export)
        $records = $query->orderBy('start_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        // Generate filename
        $dateFrom = $request->filled('date_from') ? Carbon::parse($request->date_from)->format('Y-m-d') : 'all';
        $dateTo = $request->filled('date_to') ? Carbon::parse($request->date_to)->format('Y-m-d') : 'all';
        $filename = 'leave_requests_' . $dateFrom . '_to_' . $dateTo . '_' . now()->format('Y-m-d');

        switch ($format) {
            case 'pdf':
                return $this->exportLeavePDF($records, $filename);
            case 'csv':
                return $this->exportLeaveCSV($records, $filename);
            case 'xls':
                return $this->exportLeaveXLS($records, $filename);
            default:
                return redirect()->route('attendance.leave-management')->with('error', 'Invalid export format.');
        }
    }

    /**
     * Export leave to PDF
     */
    private function exportLeavePDF($records, $filename)
    {
        $data = [
            'records' => $records,
            'date' => now()->format('F d, Y'),
        ];

        $pdf = Pdf::loadView('attendance.exports.leave-pdf', $data);
        return $pdf->download($filename . '.pdf');
    }

    /**
     * Export leave to CSV
     */
    private function exportLeaveCSV($records, $filename)
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '.csv"',
        ];

        $callback = function() use ($records) {
            $file = fopen('php://output', 'w');
            
            // BOM for UTF-8
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Headers
            fputcsv($file, [
                'Employee Code',
                'Employee Name',
                'Department',
                'Leave Type',
                'Start Date',
                'End Date',
                'Days Requested',
                'Reason',
                'Status',
                'Approved By',
                'Approved At',
                'Rejection Reason'
            ]);

            // Data
            foreach ($records as $record) {
                fputcsv($file, [
                    $record->employee->employee_code ?? 'N/A',
                    $record->employee->full_name ?? 'N/A',
                    $record->employee->department->name ?? 'N/A',
                    ucfirst(str_replace('_', ' ', $record->leave_type ?? 'N/A')),
                    Carbon::parse($record->start_date)->format('Y-m-d'),
                    Carbon::parse($record->end_date)->format('Y-m-d'),
                    $record->days_requested ?? 0,
                    $record->reason ?? 'N/A',
                    ucfirst($record->status ?? 'pending'),
                    $record->approvedBy ? $record->approvedBy->full_name : 'N/A',
                    $record->approved_at ? Carbon::parse($record->approved_at)->format('Y-m-d H:i:s') : 'N/A',
                    $record->rejection_reason ?? 'N/A'
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export leave to Excel
     */
    private function exportLeaveXLS($records, $filename)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set headers
        $headers = ['Employee Code', 'Employee Name', 'Department', 'Leave Type', 'Start Date', 'End Date', 'Days Requested', 'Reason', 'Status', 'Approved By', 'Approved At', 'Rejection Reason'];
        $sheet->fromArray($headers, null, 'A1');

        // Style header row
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4472C4']
            ],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ];
        $sheet->getStyle('A1:L1')->applyFromArray($headerStyle);

        // Add data
        $row = 2;
        foreach ($records as $record) {
            $sheet->setCellValue('A' . $row, $record->employee->employee_code ?? 'N/A');
            $sheet->setCellValue('B' . $row, $record->employee->full_name ?? 'N/A');
            $sheet->setCellValue('C' . $row, $record->employee->department->name ?? 'N/A');
            $sheet->setCellValue('D' . $row, ucfirst(str_replace('_', ' ', $record->leave_type ?? 'N/A')));
            $sheet->setCellValue('E' . $row, Carbon::parse($record->start_date)->format('Y-m-d'));
            $sheet->setCellValue('F' . $row, Carbon::parse($record->end_date)->format('Y-m-d'));
            $sheet->setCellValue('G' . $row, $record->days_requested ?? 0);
            $sheet->setCellValue('H' . $row, $record->reason ?? 'N/A');
            $sheet->setCellValue('I' . $row, ucfirst($record->status ?? 'pending'));
            $sheet->setCellValue('J' . $row, $record->approvedBy ? $record->approvedBy->full_name : 'N/A');
            $sheet->setCellValue('K' . $row, $record->approved_at ? Carbon::parse($record->approved_at)->format('Y-m-d H:i:s') : 'N/A');
            $sheet->setCellValue('L' . $row, $record->rejection_reason ?? 'N/A');
            $row++;
        }

        // Auto-size columns
        foreach (range('A', 'L') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Add borders
        $borderStyle = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'CCCCCC']
                ]
            ]
        ];
        $sheet->getStyle('A1:L' . ($row - 1))->applyFromArray($borderStyle);

        $writer = new Xlsx($spreadsheet);
        $tempFile = tempnam(sys_get_temp_dir(), $filename);
        $writer->save($tempFile);

        return response()->download($tempFile, $filename . '.xlsx')->deleteFileAfterSend(true);
    }
}

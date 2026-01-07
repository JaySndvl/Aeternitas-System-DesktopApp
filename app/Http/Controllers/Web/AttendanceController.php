<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\AttendanceRecord;
use App\Models\Employee;
use App\Models\AttendanceSetting;
use App\Models\AttendanceException;
use App\Helpers\TimezoneHelper;
use App\Helpers\CompanyHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class AttendanceController extends Controller
{
    /**
     * Display daily attendance page
     */
    public function daily(Request $request)
    {
        $date = $request->get('date', today()->format('Y-m-d'));
        $date = Carbon::parse($date);
        $currentCompany = CompanyHelper::getCurrentCompany();
        $user = Auth::user();
        
        // Check role case-insensitively
        $userRole = strtolower(trim($user->role ?? ''));
        $isEmployee = ($userRole === 'employee') || 
                      (strtolower(trim($user->role ?? '')) === 'employee') ||
                      ($user->role === 'employee') ||
                      ($user->role === 'Employee');
        
        // Initialize employee ID to null - will be set if user is an employee
        $employeeIdForFilter = null;
        
        // If user is an employee, restrict to ONLY their own records
        if ($isEmployee) {
            \Log::info('Daily attendance access - Employee detected', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'user_role' => $user->role
            ]);
            
            // Try multiple ways to get the employee ID
            $employee = null;
            
            // Method 1: Try relationship
            try {
                if ($user->employee) {
                    $employee = $user->employee;
                }
            } catch (\Exception $e) {
                \Log::warning('Employee relationship failed in daily()', ['error' => $e->getMessage()]);
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
                \Log::info('Employee ID determined for daily filter', [
                    'employee_id' => $employeeIdForFilter,
                    'employee_name' => $employee->full_name ?? 'N/A'
                ]);
            } else {
                // If no employee found, return empty results
                \Log::warning('Employee account has no linked employee record in daily()', [
                    'user_id' => $user->id,
                    'account_employee_id' => $user->employee_id
                ]);
                $employees = collect()->paginate(10);
                $attendanceRecords = collect()->keyBy('employee_id');
                $summary = [
                    'present' => 0,
                    'absent' => 0,
                    'late' => 0,
                    'attendance_rate' => 0
                ];
                return view('attendance.daily', compact('employees', 'attendanceRecords', 'summary', 'date', 'user'))
                    ->with('error', 'Your account is not linked to an employee record. Please contact administrator.');
            }
        }
        
        // Get employees - for employees, only show their own record
        if ($employeeIdForFilter !== null) {
            // Employee can only see their own record
            $employee = Employee::with(['department', 'account'])->find($employeeIdForFilter);
            if ($employee) {
                // Create a paginated collection for a single employee
                $employeesCollection = collect([$employee]);
                $currentPage = Paginator::resolveCurrentPage();
                $perPage = 10;
                $employees = new LengthAwarePaginator(
                    $employeesCollection,
                    $employeesCollection->count(),
                    $perPage,
                    $currentPage,
                    ['path' => $request->url(), 'query' => $request->query()]
                );
                $allEmployees = collect([$employee]);
            } else {
                // Create empty paginated collection
                $currentPage = Paginator::resolveCurrentPage();
                $perPage = 10;
                $employees = new LengthAwarePaginator(
                    collect(),
                    0,
                    $perPage,
                    $currentPage,
                    ['path' => $request->url(), 'query' => $request->query()]
                );
                $allEmployees = collect();
            }
        } else {
            // For admin/hr/manager, show all employees
            $allEmployeesQuery = Employee::with(['department', 'account'])
                ->whereHas('account', function($query) {
                    $query->where('is_active', true);
                });
                
            // Filter by current company if set
            if ($currentCompany) {
                $allEmployeesQuery->forCompany($currentCompany->id);
            }
            
            $allEmployees = $allEmployeesQuery->get();
            
            // Paginate employees
            $query = Employee::with(['department', 'account'])
                ->whereHas('account', function($query) {
                    $query->where('is_active', true);
                });
                
            // Filter by current company if set
            if ($currentCompany) {
                $query->forCompany($currentCompany->id);
            }
            
            $employees = $query->orderBy('first_name')
                ->paginate(10); // 10 employees per page
        }

        // Get attendance records - for employees, only their own records
        if ($employeeIdForFilter !== null) {
            // Only get records for this employee
            // Normalize the date to ensure consistent comparison
            // Use the date as a string in Y-m-d format to match database storage
            $dateString = $date->format('Y-m-d');
            
            // Try multiple query approaches to ensure we find the record
            // First try: Direct date comparison (most reliable for date columns)
            $attendanceRecords = AttendanceRecord::with(['employee.department', 'breaks'])
                ->where('date', $dateString)
                ->where('employee_id', $employeeIdForFilter)
                ->get();
            
            // If no records found, try whereDate as fallback (handles Carbon instances)
            if ($attendanceRecords->isEmpty()) {
                $attendanceRecords = AttendanceRecord::with(['employee.department', 'breaks'])
                    ->whereDate('date', $date)
                    ->where('employee_id', $employeeIdForFilter)
                    ->get();
            }
            
            // Key by employee_id for easy lookup
            $attendanceRecords = $attendanceRecords->keyBy('employee_id');
            
            \Log::info('Daily attendance records retrieved for employee', [
                'employee_id' => $employeeIdForFilter,
                'date_query_string' => $dateString,
                'date_carbon' => $date->toDateString(),
                'date_formatted' => $date->format('Y-m-d'),
                'records_count' => $attendanceRecords->count(),
                'all_records_for_employee' => AttendanceRecord::where('employee_id', $employeeIdForFilter)
                    ->orderBy('date', 'desc')
                    ->limit(5)
                    ->get()
                    ->map(function($r) {
                        return [
                            'id' => $r->id,
                            'date' => $r->date ? $r->date->format('Y-m-d') : null,
                            'time_in' => $r->time_in ? $r->time_in->format('Y-m-d H:i:s') : null,
                            'status' => $r->status
                        ];
                    }),
                'first_record' => $attendanceRecords->first() ? [
                    'id' => $attendanceRecords->first()->id,
                    'date' => $attendanceRecords->first()->date ? $attendanceRecords->first()->date->format('Y-m-d') : null,
                    'time_in' => $attendanceRecords->first()->time_in ? $attendanceRecords->first()->time_in->format('Y-m-d H:i:s') : null,
                    'time_out' => $attendanceRecords->first()->time_out ? $attendanceRecords->first()->time_out->format('Y-m-d H:i:s') : null,
                    'status' => $attendanceRecords->first()->status
                ] : null
            ]);
            
            $allAttendanceRecords = $attendanceRecords;
        } else {
            // For admin/hr/manager, get all records
            $allAttendanceRecords = AttendanceRecord::with(['employee.department', 'breaks'])
                ->where('date', $date)
                ->get()
                ->keyBy('employee_id');

            // Get attendance records for current page employees only
            $employeeIds = $employees->pluck('id');
            $attendanceRecords = AttendanceRecord::with(['employee.department', 'breaks'])
                ->where('date', $date)
                ->whereIn('employee_id', $employeeIds)
                ->get()
                ->keyBy('employee_id');
        }

        // Calculate summary statistics
        $summary = $this->calculateDailySummary($allEmployees, $allAttendanceRecords, $date);
        
        return view('attendance.daily', compact('employees', 'attendanceRecords', 'summary', 'date', 'user'));
    }

    /**
     * Display timekeeping page
     */
    public function timekeeping(Request $request)
    {
        $user = Auth::user();
        
        // CRITICAL: Immediately check if user is employee and enforce filter
        // This check happens FIRST before anything else
        $userRoleRaw = $user->role ?? '';
        $isEmployeeUser = in_array(strtolower(trim($userRoleRaw)), ['employee']);
        
        $currentCompany = CompanyHelper::getCurrentCompany();
        
        // Check role case-insensitively to ensure it works regardless of how it's stored
        $userRole = strtolower(trim($user->role ?? ''));
        
        // Log for debugging - CRITICAL for troubleshooting
        \Log::info('Timekeeping access - START', [
            'user_id' => $user->id,
            'user_email' => $user->email,
            'user_role_raw' => $user->role,
            'user_role_normalized' => $userRole,
            'is_employee_check' => ($userRole === 'employee') ? 'YES' : 'NO',
            'employee_id_from_account' => $user->employee_id,
            'has_employee_relationship' => $user->employee ? 'yes' : 'no'
        ]);
        
        // Initialize employee ID to null - will be set if user is an employee
        $employeeIdForFilter = null;
        
        // If user is an employee, restrict to ONLY their own records
        // Check multiple ways to ensure we catch the role correctly
        // Use strict comparison and also check the raw role value
        $isEmployee = ($userRole === 'employee') || 
                      (strtolower(trim($user->role ?? '')) === 'employee') ||
                      ($user->role === 'employee') ||
                      ($user->role === 'Employee');
        
        if ($isEmployee) {
            \Log::info('Employee role detected - applying filter', [
                'user_id' => $user->id,
                'role_checks' => [
                    'normalized' => $userRole === 'employee',
                    'raw_lowercase' => strtolower(trim($user->role ?? '')) === 'employee',
                    'raw_exact' => $user->role === 'employee',
                    'raw_capitalized' => $user->role === 'Employee'
                ]
            ]);
            // Try multiple ways to get the employee ID
            $employee = null;
            
            // Method 1: Try relationship
            try {
                if ($user->employee) {
                    $employee = $user->employee;
                }
            } catch (\Exception $e) {
                \Log::warning('Employee relationship failed', ['error' => $e->getMessage()]);
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
                // CRITICAL: Set the employee ID for filtering
                // This ensures employees can NEVER see other employees' attendance records
                $employeeIdForFilter = $employee->id;
                \Log::info('Employee ID determined for filtering', [
                    'employee_id' => $employeeIdForFilter,
                    'employee_name' => $employee->full_name ?? 'N/A'
                ]);
            } else {
                // If no employee found, return empty results with a message
                \Log::warning('Employee account has no linked employee record', [
                    'user_id' => $user->id,
                    'account_employee_id' => $user->employee_id
                ]);
                $attendanceRecords = collect()->paginate(20);
                $summary = $this->calculateTimekeepingSummary(collect());
                return view('attendance.timekeeping', compact('attendanceRecords', 'summary', 'user'))->with('error', 'Your account is not linked to an employee record. Please contact administrator.');
            }
        }
        
        // CRITICAL SAFETY CHECK: If user is an employee but filter wasn't set, BLOCK ACCESS
        // This prevents employees from seeing all records if something goes wrong
        if ($isEmployee && $employeeIdForFilter === null) {
            \Log::error('SECURITY ISSUE: Employee detected but filter not applied!', [
                'user_id' => $user->id,
                'user_role' => $user->role,
                'employee_id_from_account' => $user->employee_id
            ]);
            $attendanceRecords = collect()->paginate(20);
            $summary = $this->calculateTimekeepingSummary(collect());
            return view('attendance.timekeeping', compact('attendanceRecords', 'summary', 'user'))
                ->with('error', 'Unable to load your attendance records. Please contact administrator.');
        }
        
        // Build the query - start fresh to ensure clean filtering
        // CRITICAL: For employees, we MUST start with a filtered query
        if ($employeeIdForFilter !== null) {
            // Start the query directly with the employee filter - this ensures it cannot be bypassed
            $query = AttendanceRecord::with(['employee.department', 'employee.account', 'breaks'])
                ->where('employee_id', $employeeIdForFilter);
            \Log::info('Employee filter applied FIRST to query', [
                'employee_id' => $employeeIdForFilter,
                'user_id' => $user->id,
                'user_role' => $user->role
            ]);
        } else {
            // For non-employees, start with a normal query
            $query = AttendanceRecord::with(['employee.department', 'employee.account', 'breaks']);
        }

        // Filter by company (but don't apply if employee's company doesn't match - let them see their records)
        // IMPORTANT: This whereHas might interfere with employee filter, so we skip it for employees
        if ($currentCompany && $userRole !== 'employee' && $employeeIdForFilter === null) {
            $query->whereHas('employee', function($q) use ($currentCompany) {
                $q->where('company_id', $currentCompany->id);
            });
        }

        // Apply filters (only for HR/Admin, employees can't filter by other employees)
        // IMPORTANT: Employees are already filtered above, so these filters only apply to admin/hr/manager
        if ($request->filled('employee_id')) {
            // Only allow filtering by other employees if user is HR/Admin/Manager
            // Employees are already restricted to their own records above
            if (in_array($userRole, ['admin', 'hr', 'manager'])) {
                $query->where('employee_id', $request->employee_id);
            }
        }

        if ($request->filled('department_id')) {
            // Only allow department filtering if user is HR/Admin/Manager
            // Employees are already restricted to their own records above
            if (in_array($userRole, ['admin', 'hr', 'manager'])) {
                $query->whereHas('employee', function($q) use ($request) {
                    $q->where('department_id', $request->department_id);
                });
            }
        }

        if ($request->filled('date_from')) {
            $query->where('date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('date', '<=', $request->date_to);
        }

        // CRITICAL: Re-apply employee filter if it was set, to ensure it's never removed
        // This is a safety measure in case any previous filter operations removed it
        if ($employeeIdForFilter !== null) {
            // Force the filter to be applied - use whereRaw to ensure it cannot be bypassed
            $query->whereRaw('employee_id = ?', [$employeeIdForFilter]);
        }
        
        // Only show records that have a time_in value (employee actually clocked in)
        // Exclude incomplete records from past dates (only show complete records or today's incomplete record)
        $today = today()->format('Y-m-d');
        
        // Log the final query for debugging
        if ($employeeIdForFilter !== null) {
            \Log::info('Final query before execution', [
                'employee_id_filter' => $employeeIdForFilter,
                'sql_preview' => $query->toSql(),
                'bindings' => $query->getBindings()
            ]);
        }
        
        // Build the final query with all filters
        $finalQuery = $query->whereNotNull('time_in')
            ->where(function($q) use ($today) {
                // Show complete records (have time_out)
                $q->whereNotNull('time_out')
                  // OR show today's incomplete record (currently working)
                  ->orWhere(function($subQ) use ($today) {
                      $subQ->whereNull('time_out')
                           ->where('date', $today);
                  });
            });
        
        // CRITICAL: One more time, ensure employee filter is applied right before pagination
        if ($employeeIdForFilter !== null) {
            $finalQuery->where('employee_id', $employeeIdForFilter);
        }
        
        $attendanceRecords = $finalQuery
            ->orderBy('date', 'desc')
            ->orderBy('time_in', 'desc')
            ->paginate(20);
        
        // Log the results for debugging and SECURITY CHECK
        if ($employeeIdForFilter !== null) {
            $allEmployeeIds = $attendanceRecords->pluck('employee_id')->unique()->values()->all();
            $filterWorking = empty($allEmployeeIds) || (count($allEmployeeIds) === 1 && $allEmployeeIds[0] === $employeeIdForFilter);
            
            \Log::info('Query executed', [
                'employee_id_filter' => $employeeIdForFilter,
                'records_count' => $attendanceRecords->count(),
                'total_records' => $attendanceRecords->total(),
                'all_employee_ids_in_results' => $allEmployeeIds,
                'first_record_employee_id' => $attendanceRecords->first() ? $attendanceRecords->first()->employee_id : 'none',
                'filter_working' => $filterWorking ? 'YES' : 'NO'
            ]);
            
            // CRITICAL SECURITY CHECK: If filter is not working, BLOCK the results
            if (!$filterWorking && $attendanceRecords->count() > 0) {
                \Log::error('SECURITY BREACH DETECTED: Employee filter failed! Blocking results.', [
                    'employee_id_filter' => $employeeIdForFilter,
                    'found_employee_ids' => $allEmployeeIds,
                    'total_records' => $attendanceRecords->total(),
                    'user_id' => $user->id,
                    'user_email' => $user->email
                ]);
                // Return empty results to prevent data leak
                $attendanceRecords = collect()->paginate(20);
                $summary = $this->calculateTimekeepingSummary(collect());
                return view('attendance.timekeeping', compact('attendanceRecords', 'summary', 'user'))
                    ->with('error', 'Security error detected. Unable to load attendance records. Please contact administrator.');
            }
        }
        
        // CRITICAL SAFETY CHECK: If user is an employee but filter wasn't set, BLOCK ACCESS
        // This prevents employees from seeing all records if something goes wrong
        if ($isEmployee && $employeeIdForFilter === null && !$attendanceRecords->isEmpty()) {
            \Log::error('SECURITY ISSUE: Employee detected but filter not applied! Blocking access.', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'user_role' => $user->role,
                'employee_id_from_account' => $user->employee_id,
                'records_count' => $attendanceRecords->count()
            ]);
            $attendanceRecords = collect()->paginate(20);
            $summary = $this->calculateTimekeepingSummary(collect());
            return view('attendance.timekeeping', compact('attendanceRecords', 'summary', 'user'))
                ->with('error', 'Unable to load your attendance records. Please contact administrator.');
        }

        // Calculate summary statistics (recreate query to avoid pagination issues)
        $summaryQuery = AttendanceRecord::with(['employee.department', 'employee.account']);
        
        // Apply same employee restriction for summary - CRITICAL for employees
        // Use the same employeeIdForFilter to ensure consistency
        if ($employeeIdForFilter !== null) {
            $summaryQuery->where('employee_id', $employeeIdForFilter);
        }
        
        // Apply same filters for summary
        if ($request->filled('employee_id')) {
            // Only allow filtering by other employees if user is HR/Admin/Manager
            if (in_array($userRole, ['admin', 'hr', 'manager'])) {
                $summaryQuery->where('employee_id', $request->employee_id);
            }
        }
        if ($request->filled('department_id')) {
            // Only allow department filtering if user is HR/Admin/Manager
            if (in_array($userRole, ['admin', 'hr', 'manager'])) {
                $summaryQuery->whereHas('employee', function($q) use ($request) {
                    $q->where('department_id', $request->department_id);
                });
            }
        }
        if ($request->filled('date_from')) {
            $summaryQuery->where('date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $summaryQuery->where('date', '<=', $request->date_to);
        }
        
        // Filter summary by company (but don't apply if employee's company doesn't match)
        if ($currentCompany && $userRole !== 'employee') {
            $summaryQuery->whereHas('employee', function($q) use ($currentCompany) {
                $q->where('company_id', $currentCompany->id);
            });
        }
        
        // Only include records that have a time_in value (employee actually clocked in)
        // Exclude incomplete records from past dates (only show complete records or today's incomplete record)
        $today = today()->format('Y-m-d');
        $allRecords = $summaryQuery->whereNotNull('time_in')
            ->where(function($q) use ($today) {
                // Show complete records (have time_out)
                $q->whereNotNull('time_out')
                  // OR show today's incomplete record (currently working)
                  ->orWhere(function($subQ) use ($today) {
                      $subQ->whereNull('time_out')
                           ->where('date', $today);
                  });
            })
            ->get();
        $summary = $this->calculateTimekeepingSummary($allRecords);

        // Only show employee/department filters for HR/Admin
        $employees = collect();
        $departments = collect();
        
        // Use the same $userRole variable we defined earlier (no need to redefine)
        if (in_array($userRole, ['admin', 'hr', 'manager'])) {
            try {
            $employeesQuery = Employee::with('department')
                ->whereHas('account', function($query) {
                    $query->where('is_active', true);
                });
            
            // Filter by current company if set
            if ($currentCompany) {
                $employeesQuery->forCompany($currentCompany->id);
            }
            
            $employees = $employeesQuery->orderBy('first_name')->orderBy('last_name')->get();
            
            $departmentsQuery = \App\Models\Department::query();
            if ($currentCompany) {
                $departmentsQuery->forCompany($currentCompany->id);
            }
            $departments = $departmentsQuery->orderBy('name')->get();
            } catch (\Exception $e) {
                \Log::warning('Error loading employees/departments for filters', [
                    'error' => $e->getMessage(),
                    'user_id' => $user->id
                ]);
                // Continue with empty collections
            }
        }

        // Ensure all required variables are set
        if (!isset($attendanceRecords)) {
            $attendanceRecords = collect()->paginate(20);
        }
        if (!isset($summary)) {
            $summary = $this->calculateTimekeepingSummary(collect());
        }
        if (!isset($employees)) {
            $employees = collect();
        }
        if (!isset($departments)) {
            $departments = collect();
        }

        try {
            $view = view('attendance.timekeeping', compact('attendanceRecords', 'employees', 'departments', 'summary', 'user'));
            
            // Check if view is valid
            if ($view === null) {
                \Log::error('View returned null', [
                    'view' => 'attendance.timekeeping',
                    'user_id' => $user->id
                ]);
                throw new \Exception('View rendering returned null');
            }
        
        // CRITICAL: Prevent caching for employees to ensure fresh data
            // Use response() helper to properly set headers
        if ($employeeIdForFilter !== null) {
                return response($view)
                    ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
                    ->header('Pragma', 'no-cache')
                    ->header('Expires', '0');
            }
            
            return $view;
        } catch (\Exception $e) {
            \Log::error('Error rendering timekeeping view', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => $user->id,
                'employee_id_filter' => $employeeIdForFilter
            ]);
            
            // Return error view if view rendering fails
            $attendanceRecords = collect()->paginate(20);
            $summary = $this->calculateTimekeepingSummary(collect());
            $employees = $employees ?? collect();
            $departments = $departments ?? collect();
            return view('attendance.timekeeping', compact('attendanceRecords', 'employees', 'departments', 'summary', 'user'))
                ->with('error', 'An error occurred while loading the page. Please try again.');
        }
    }

    /**
     * Show the form for creating a new attendance record
     */
    public function createRecord()
    {
        $user = Auth::user();
        $currentCompany = CompanyHelper::getCurrentCompany();
        
        // Check if user is an employee
        $userRole = strtolower(trim($user->role ?? ''));
        $isEmployee = ($userRole === 'employee');
        
        $employeesQuery = Employee::with('department');
        
        // For employees, only show their own record
        if ($isEmployee && $user->employee) {
            $employeesQuery->where('id', $user->employee->id);
        } else {
            // For admin/hr/manager, show all employees (filtered by company)
        if ($currentCompany) {
            $employeesQuery->forCompany($currentCompany->id);
        }
        }
        
        $employees = $employeesQuery->get();
        
        return view('attendance.create-record', compact('employees', 'user'));
    }

    /**
     * Store a newly created attendance record
     */
    public function storeRecord(Request $request)
    {
        $user = Auth::user();
        
        // Check if user is an employee
        $userRole = strtolower(trim($user->role ?? ''));
        $isEmployee = ($userRole === 'employee');
        
        // For employees, ensure they can only create records for themselves
        if ($isEmployee && $user->employee) {
            // Override employee_id to ensure employees can only create their own records
            $request->merge(['employee_id' => $user->employee->id]);
        }
        
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'date' => 'required|date',
            'time_in' => 'required|date_format:H:i',
            'time_out' => 'nullable|date_format:H:i|after:time_in',
            'break_start' => 'nullable|date_format:H:i',
            'break_end' => 'nullable|date_format:H:i|after:break_start',
            'status' => 'required|in:present,absent,absent_excused,absent_unexcused,absent_sick,absent_personal,late,half_day,on_leave',
            'notes' => 'nullable|string|max:500'
        ]);

        // Additional security check: For employees, verify they're creating for themselves
        if ($isEmployee && $user->employee && $request->employee_id !== $user->employee->id) {
            return redirect()->route('attendance.create-record')
                ->with('error', 'You can only create attendance records for yourself.')
                ->withInput();
        }

        // Check if record already exists for this employee and date
        $existingRecord = AttendanceRecord::where('employee_id', $request->employee_id)
            ->where('date', $request->date)
            ->first();

        if ($existingRecord) {
            return redirect()->back()->with('error', 'An attendance record already exists for this employee on this date.');
        }

        // Calculate total hours if time_out is provided
        $totalHours = 0;
        $breakDuration = 0;
        $regularHours = 0;
        $overtimeHours = 0;
        
        if ($request->time_out) {
            $timeIn = \Carbon\Carbon::createFromFormat('Y-m-d H:i', $request->date . ' ' . $request->time_in);
            $timeOut = \Carbon\Carbon::createFromFormat('Y-m-d H:i', $request->date . ' ' . $request->time_out);
            
            
            // Calculate break duration if break_start and break_end are provided
            if ($request->break_start && $request->break_end) {
                $breakStart = \Carbon\Carbon::createFromFormat('Y-m-d H:i', $request->date . ' ' . $request->break_start);
                $breakEnd = \Carbon\Carbon::createFromFormat('Y-m-d H:i', $request->date . ' ' . $request->break_end);
                $breakMinutes = $breakEnd->diffInMinutes($breakStart);
                $breakDuration = $breakMinutes / 60;
            } else {
                $breakDuration = 0; // No break entered
            }
            
            // Business Rules:
            // Standard work day: 8 AM to 5 PM (9 hours total)
            // Break time: 1 hour (lunch break)
            // Regular hours: 8 hours (9 hours - 1 hour break)
            // Overtime starts: After 5:30 PM (8 AM + 8 regular hours + 1 hour break = 5 PM, so overtime starts at 5:30 PM)
            
            // Calculate total working time (excluding break)
            $totalMinutes = $timeIn->diffInMinutes($timeOut);
            $totalHours = $totalMinutes / 60;
            $totalHours = $totalHours - $breakDuration; // Subtract break time
            
            
            // Define standard work schedule
            $standardStart = \Carbon\Carbon::createFromFormat('Y-m-d H:i', $request->date . ' 08:00');
            $standardEnd = \Carbon\Carbon::createFromFormat('Y-m-d H:i', $request->date . ' 17:00');
            $overtimeStart = \Carbon\Carbon::createFromFormat('Y-m-d H:i', $request->date . ' 17:30');
            
            // Calculate regular and overtime hours based on business rules
            if ($timeOut <= $standardEnd) {
                // Worked within standard hours (8 AM - 5 PM)
                $regularHours = $totalHours;
                $overtimeHours = 0;
            } elseif ($timeOut <= $overtimeStart) {
                // Worked until 5:30 PM (no overtime yet)
                $regularHours = $totalHours;
                $overtimeHours = 0;
            } else {
                // Worked beyond 5:30 PM (overtime applies)
                // Calculate regular hours: from time in to 5:30 PM, minus break time
                $regularMinutes = $timeIn->diffInMinutes($overtimeStart);
                $regularHours = ($regularMinutes / 60) - $breakDuration;
                
                // Calculate overtime hours: from 5:30 PM to time out
                $overtimeMinutes = $overtimeStart->diffInMinutes($timeOut);
                $overtimeHours = $overtimeMinutes / 60;
                
                // Ensure regular hours don't exceed 8 hours
                $regularHours = min($regularHours, 8);
            }
            
            // Ensure non-negative values
            $totalHours = max(0, $totalHours);
            $regularHours = max(0, $regularHours);
            $overtimeHours = max(0, $overtimeHours);
            
            
        }

        $attendanceRecord = AttendanceRecord::create([
            'employee_id' => $request->employee_id,
            'date' => $request->date,
            'time_in' => $request->date . ' ' . $request->time_in,
            'time_out' => $request->time_out ? $request->date . ' ' . $request->time_out : null,
            'break_start' => $request->break_start ? $request->date . ' ' . $request->break_start : null,
            'break_end' => $request->break_end ? $request->date . ' ' . $request->break_end : null,
            'total_hours' => $totalHours,
            'regular_hours' => $regularHours,
            'overtime_hours' => $overtimeHours,
            'status' => $request->status,
            'notes' => $request->notes,
        ]);

        // Log the attendance action
        $this->logAttendanceAction($attendanceRecord, 'manual_entry', 'Attendance record manually created');

        return redirect()->route('attendance.timekeeping')->with('success', 'Attendance record created successfully.');
    }

    /**
     * Display schedule page
     */
public function schedule(Request $request)
    {
        $week = $request->get('week', now()->format('Y-\WW'));
        $weekStart = Carbon::parse($week . '1'); // Start of week (Monday)
        $weekEnd = $weekStart->copy()->addDays(6);

        $employees = Employee::with(['workSchedules' => function($query) use ($weekStart, $weekEnd) {
            $query->where('is_active', true)
                  ->where('effective_date', '<=', $weekEnd)
                  ->where(function($q) use ($weekStart) {
                      $q->whereNull('end_date')
                        ->orWhere('end_date', '>=', $weekStart);
                  });
        }])->get();

        $user = Auth::user();

        return view('attendance.schedule-v2.index', compact('employees', 'weekStart', 'weekEnd', 'user'));
    }

    /**
     * Display schedule reports page
     */
    public function scheduleReports(Request $request)
    {
        $user = Auth::user();
        
        // Get report data based on filters
        $reportType = $request->get('report_type', 'weekly');
        $employeeId = $request->get('employee_id');
        $departmentId = $request->get('department_id');
        $dateRange = $request->get('date_range');
        
        // Sample data for now - replace with actual data fetching
        $employees = Employee::with('department')->get();
        $departments = \App\Models\Department::all();
        
        return view('attendance.schedule.reports', compact('user', 'employees', 'departments', 'reportType', 'employeeId', 'departmentId', 'dateRange'));
    }

    /**
     * Display schedule templates page
     */
    public function scheduleTemplates(Request $request)
    {
        $user = Auth::user();
        
        // Get template data based on filters
        $templateType = $request->get('template_type');
        $departmentId = $request->get('department_id');
        $status = $request->get('status');
        
        // Sample data for now - replace with actual data fetching
        $employees = Employee::with('department')->get();
        $departments = \App\Models\Department::all();
        
        return view('attendance.schedule.templates', compact('user', 'employees', 'departments', 'templateType', 'departmentId', 'status'));
    }

    /**
     * Calculate daily attendance summary
     */
    private function calculateDailySummary($allEmployees, $attendanceRecords, $date)
    {
        $totalEmployees = $allEmployees->count();
        
        // Count statuses from attendance records
        $present = 0;
        $absent = 0;
        $late = 0;
        $halfDay = 0;
        
        foreach ($allEmployees as $employee) {
            $record = $attendanceRecords->get($employee->id);
            
            if ($record) {
                switch ($record->status) {
                    case 'present':
                        $present++;
                        break;
                    case 'absent':
                    case 'absent_excused':
                    case 'absent_unexcused':
                    case 'absent_sick':
                    case 'absent_personal':
                        $absent++;
                        break;
                    case 'late':
                        $late++;
                        break;
                    case 'half_day':
                        $halfDay++;
                        break;
                }
            } else {
                // No attendance record means absent
                $absent++;
            }
        }

        // Attendance rate = (present + late + half_day) / total employees * 100
        $attendanceRate = $totalEmployees > 0 ? round(($present + $late + $halfDay) / $totalEmployees * 100, 1) : 0;

        return [
            'total' => $totalEmployees,
            'present' => $present,
            'absent' => $absent,
            'late' => $late,
            'half_day' => $halfDay,
            'attendance_rate' => $attendanceRate,
        ];
    }

    /**
     * Calculate timekeeping summary
     */
    private function calculateTimekeepingSummary($attendanceRecords)
    {
        $totalHours = $attendanceRecords->sum('total_hours');
        $regularHours = $attendanceRecords->where('total_hours', '<=', 8)->sum('total_hours');
        $overtimeHours = $attendanceRecords->where('total_hours', '>', 8)->sum(function($record) {
            return max(0, $record->total_hours - 8);
        });
        
        $totalRecords = $attendanceRecords->count();
        $averageHours = $totalRecords > 0 ? round($totalHours / $totalRecords, 1) : 0;

        return [
            'total_hours' => $totalHours,
            'regular_hours' => $regularHours,
            'overtime_hours' => $overtimeHours,
            'average_hours' => $averageHours,
            'total_records' => $totalRecords,
        ];
    }

    /**
     * Get attendance statistics for dashboard
     */
    public function getStatistics(Request $request)
    {
        $date = $request->get('date', today());
        $date = Carbon::parse($date);

        $stats = [
            'today' => $this->getTodayStats($date),
            'this_week' => $this->getWeekStats($date),
            'this_month' => $this->getMonthStats($date),
        ];

        return response()->json($stats);
    }

    private function getTodayStats($date)
    {
        $records = AttendanceRecord::where('date', $date)->get();
        
        return [
            'total_employees' => Employee::whereHas('account', function($q) {
                $q->where('is_active', true);
            })->count(),
            'present' => $records->where('status', 'present')->count(),
            'absent' => $records->where('status', 'absent')->count(),
            'late' => $records->where('status', 'late')->count(),
            'total_hours' => $records->sum('total_hours'),
            'overtime_hours' => $records->sum('overtime_hours'),
        ];
    }

    private function getWeekStats($date)
    {
        $weekStart = $date->copy()->startOfWeek();
        $weekEnd = $date->copy()->endOfWeek();

        $records = AttendanceRecord::whereBetween('date', [$weekStart, $weekEnd])->get();
        
        return [
            'total_hours' => $records->sum('total_hours'),
            'overtime_hours' => $records->sum('overtime_hours'),
            'average_daily_hours' => $records->avg('total_hours') ?? 0,
        ];
    }

    private function getMonthStats($date)
    {
        $monthStart = $date->copy()->startOfMonth();
        $monthEnd = $date->copy()->endOfMonth();

        $records = AttendanceRecord::whereBetween('date', [$monthStart, $monthEnd])->get();
        
        return [
            'total_hours' => $records->sum('total_hours'),
            'overtime_hours' => $records->sum('overtime_hours'),
            'working_days' => $records->where('status', '!=', 'absent')->count(),
        ];
    }

    /**
     * Show the import DTR page
     */
    public function importDtr()
    {
        $user = Auth::user();
        
        // Get recent temp timekeeping imports (last 10 batches)
        $recentImports = \App\Models\TempTimekeeping::select('import_batch_id')
            ->selectRaw('MIN(created_at) as created_at')
            ->groupBy('import_batch_id')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($item) {
                $batchRecords = \App\Models\TempTimekeeping::where('import_batch_id', $item->import_batch_id)->get();
                return [
                    'batch_id' => $item->import_batch_id,
                    'created_at' => $item->created_at,
                    'total_records' => $batchRecords->count(),
                    'processed_records' => $batchRecords->where('is_processed', true)->count(),
                    'pending_records' => $batchRecords->where('is_processed', false)->count(),
                    'employees' => $batchRecords->pluck('employee_id')->unique()->count(),
                    'date_range' => [
                        'start' => $batchRecords->min('date'),
                        'end' => $batchRecords->max('date')
                    ]
                ];
            });
        
        return view('attendance.import-dtr', compact('user', 'recentImports'));
    }

    /**
     * Process the imported DTR file
     */
    public function processImportDtr(Request $request)
    {
        $user = Auth::user();

        // Only allow HR/Admin/Manager roles to process DTR files
        if (!in_array($user->role, ['admin', 'hr', 'manager'])) {
            return redirect()->route('attendance.timekeeping')
                ->with('error', 'You do not have permission to process DTR files.');
        }

        $request->validate([
            'dtr_file' => 'required|file|mimes:xlsx,xls,csv|max:10240', // 10MB max
        ]);

        try {
            $file = $request->file('dtr_file');
            
            // Create a unique filename
            $fileName = 'dtr_' . time() . '_' . $file->getClientOriginalName();
            $tempPath = storage_path('app' . DIRECTORY_SEPARATOR . 'temp' . DIRECTORY_SEPARATOR . $fileName);
            
            // Move the uploaded file to temp directory
            $file->move(storage_path('app' . DIRECTORY_SEPARATOR . 'temp'), $fileName);
            
            // Debug: Log the file path
            Log::info('DTR File Path: ' . $tempPath);
            Log::info('File exists: ' . (file_exists($tempPath) ? 'Yes' : 'No'));
            
            // Check if file exists
            if (!file_exists($tempPath)) {
                throw new \Exception('Uploaded file not found at: ' . $tempPath);
            }
            
            $fullPath = $tempPath;
            
            // Parse the DTR data
            $dtrService = new \App\Services\DtrImportService();
            $parsedData = $dtrService->parseDtrData($fullPath);
            
            // Debug: Log parsed data
            Log::info('Parsed Data Count: ' . $parsedData->count());
            Log::info('Parsed Data: ' . json_encode($parsedData->toArray()));
            
            // Validate the parsed data
            $validation = $dtrService->validateParsedData($parsedData);
            
            // Store data in session for review
            session([
                'dtr_import_data' => $parsedData->toArray(),
                'dtr_import_validation' => $validation,
                'dtr_import_file' => $file->getClientOriginalName()
            ]);
            
            return redirect()->route('attendance.import-dtr.review');
            
        } catch (\Exception $e) {
            Log::error('DTR Processing Error: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Failed to process DTR file: ' . $e->getMessage())
                ->withInput();
        }

        // Only allow HR/Admin/Manager roles to review DTR imports
        if (!in_array($user->role, ['admin', 'hr', 'manager'])) {
            return redirect()->route('attendance.timekeeping')
                ->with('error', 'You do not have permission to review DTR imports.');
        }

    }

    /**
     * Show the DTR import review page
     */
    public function reviewImportDtr()
    {
        $user = Auth::user();
        $parsedDataArray = session('dtr_import_data', []);
        $parsedData = collect($parsedDataArray);
        $validation = session('dtr_import_validation', ['errors' => collect(), 'warnings' => collect(), 'is_valid' => false]);
        $fileName = session('dtr_import_file', 'Unknown file');
        
        if (empty($parsedDataArray) || $parsedData->isEmpty()) {
            return redirect()->route('attendance.import-dtr')
                ->with('error', 'No import data found. Please upload a file first.');
        }
        
        return view('attendance.import-dtr-review', compact('user', 'parsedData', 'validation', 'fileName'));
    }

    /**
     * Confirm and import the DTR data
     */
    public function confirmImportDtr(Request $request)
    {
        $parsedDataArray = session('dtr_import_data', []);
        $parsedData = collect($parsedDataArray);
        $validation = session('dtr_import_validation', ['errors' => collect(), 'warnings' => collect(), 'is_valid' => false]);
        
        if (empty($parsedDataArray) || $parsedData->isEmpty()) {
            return redirect()->route('attendance.import-dtr')
                ->with('error', 'No import data found. Please upload a file first.');
        }
        
        try {
            // Generate a unique batch ID for this import
            $batchId = \App\Models\TempTimekeeping::generateBatchId();
            $importedCount = 0;
            $errors = collect();
            
            // Debug: Log the start of the process
            Log::info('Starting temp timekeeping import process', [
                'batch_id' => $batchId,
                'record_count' => $parsedData->count()
            ]);
            
            foreach ($parsedData as $record) {
                // Prepare validation errors for this record
                $recordErrors = [];
                $employee = \App\Models\Employee::where('employee_id', $record['employee_id'])->first();
                
                if (!$employee) {
                    $recordErrors[] = "Employee ID '{$record['employee_id']}' not found in system";
                }
                
                // Check for duplicate records in temp table
                $existingTempRecord = \App\Models\TempTimekeeping::where('employee_id', $record['employee_id'])
                    ->where('date', $record['date'])
                    ->where('import_batch_id', $batchId)
                    ->first();
                
                if ($existingTempRecord) {
                    $recordErrors[] = "Duplicate record for employee {$record['employee_id']} on {$record['date']}";
                }
                
                    // Create temp timekeeping record
                    try {
                        // Auto-process day off records
                        $isProcessed = ($record['status'] === 'day_off') ? true : false;
                        
                        $tempRecord = \App\Models\TempTimekeeping::create([
                            'employee_id' => $record['employee_id'],
                            'employee_name' => $record['employee_name'] ?? null,
                    'date' => $record['date'],
                    'time_in' => $record['time_in'],
                    'time_out' => $record['time_out'],
                            'break_start' => $record['break_start'] ?? null,
                            'break_end' => $record['break_end'] ?? null,
                            'total_hours' => $record['total_hours'] ?? 0,
                            'regular_hours' => $record['regular_hours'] ?? 0,
                            'overtime_hours' => $record['overtime_hours'] ?? 0,
                    'status' => $record['status'],
                            'schedule_status' => $record['schedule_status'] ?? null,
                            'notes' => $record['notes'] ?? null,
                            'validation_errors' => !empty($recordErrors) ? json_encode($recordErrors) : null,
                            'import_batch_id' => $batchId,
                            'is_processed' => $isProcessed
                        ]);
                    
                        Log::info('Successfully created temp record', [
                            'record_id' => $tempRecord->id,
                            'employee_id' => $record['employee_id'],
                            'date' => $record['date']
                        ]);

                        // Auto-create attendance record for day off
                        if ($record['status'] === 'day_off' && $employee) {
                            try {
                                // Check for existing attendance record
                                $existingAttendanceRecord = \App\Models\AttendanceRecord::where('employee_id', $employee->id)
                                    ->where('date', $record['date'])
                                    ->first();

                                if (!$existingAttendanceRecord) {
                                    \App\Models\AttendanceRecord::create([
                                        'employee_id' => $employee->id,
                                        'date' => $record['date'],
                                        'time_in' => null,
                                        'time_out' => null,
                                        'break_start' => null,
                                        'break_end' => null,
                                        'total_hours' => 0,
                                        'regular_hours' => 0,
                                        'overtime_hours' => 0,
                                        'status' => 'day_off',
                                        'notes' => 'Auto-processed day off',
                                        'is_night_shift' => false
                                    ]);

                                    Log::info('Auto-created attendance record for day off', [
                                        'employee_id' => $record['employee_id'],
                                        'date' => $record['date']
                                    ]);
                                }
                            } catch (\Exception $e) {
                                Log::error('Failed to auto-create attendance record for day off', [
                                    'employee_id' => $record['employee_id'],
                                    'date' => $record['date'],
                                    'error' => $e->getMessage()
                                ]);
                            }
                        }
                
                $importedCount++;
                } catch (\Exception $e) {
                    Log::error('Failed to create temp record', [
                        'employee_id' => $record['employee_id'],
                        'date' => $record['date'],
                        'error' => $e->getMessage()
                    ]);
                    $errors->push("Failed to save record for {$record['employee_id']} on {$record['date']}: " . $e->getMessage());
                }
            }
            
            // Clear session data
            session()->forget(['dtr_import_data', 'dtr_import_validation', 'dtr_import_file']);
            
            Log::info('Temp timekeeping import completed', [
                'batch_id' => $batchId,
                'imported_count' => $importedCount,
                'error_count' => $errors->count()
            ]);
            
            return redirect()->route('attendance.timekeeping')
                ->with('success', "Successfully saved {$importedCount} records to temporary storage. Records are ready for final processing.");
                
        } catch (\Exception $e) {
            return redirect()->route('attendance.import-dtr.review')
                ->with('error', 'Failed to save data to temporary storage: ' . $e->getMessage());
        }
    }

    /**
     * Show temporary timekeeping records
     */
    public function tempTimekeeping(Request $request)
    {
        $user = Auth::user();
        
        $query = \App\Models\TempTimekeeping::with('employee');
        
        // Filter by batch ID if provided
        if ($request->has('batch') && !empty($request->batch)) {
            $query->where('import_batch_id', $request->batch);
        }
        
        // Get all records first
        $allRecords = $query->orderBy('created_at', 'desc')->get();
        
        // Group by employee and get unique employees
        $allGroupedRecords = $allRecords->groupBy('employee_id');
        $uniqueEmployees = $allGroupedRecords->keys();
        
        // Implement employee-based pagination (10 employees per page)
        $employeesPerPage = 10;
        $currentPage = $request->get('page', 1);
        $offset = ($currentPage - 1) * $employeesPerPage;
        $paginatedEmployees = $uniqueEmployees->slice($offset, $employeesPerPage);
        
        // Get records for paginated employees
        $groupedRecords = collect();
        foreach ($paginatedEmployees as $employeeId) {
            $employeeRecords = $allGroupedRecords[$employeeId];
            $firstRecord = $employeeRecords->first();
            $groupedRecords[$employeeId] = [
                'employee_id' => $firstRecord->employee_id,
                'employee_name' => $firstRecord->employee_name,
                'records' => $employeeRecords->sortBy('date'),
                'total_records' => $employeeRecords->count(),
                'processed_records' => $employeeRecords->where('is_processed', true)->count(),
                'pending_records' => $employeeRecords->where('is_processed', false)->count(),
                'date_range' => [
                    'start' => $employeeRecords->min('date'),
                    'end' => $employeeRecords->max('date')
                ],
                'status_summary' => $employeeRecords->groupBy('status')->map->count()
            ];
        }
        
        // Create pagination info
        $totalEmployees = $uniqueEmployees->count();
        $totalPages = ceil($totalEmployees / $employeesPerPage);
        $hasNextPage = $currentPage < $totalPages;
        $hasPrevPage = $currentPage > 1;
        
        $paginationInfo = [
            'current_page' => $currentPage,
            'total_pages' => $totalPages,
            'total_employees' => $totalEmployees,
            'employees_per_page' => $employeesPerPage,
            'has_next_page' => $hasNextPage,
            'has_prev_page' => $hasPrevPage,
            'next_page' => $hasNextPage ? $currentPage + 1 : null,
            'prev_page' => $hasPrevPage ? $currentPage - 1 : null
        ];
        
        // Get batch information if filtering by batch
        $batchInfo = null;
        if ($request->has('batch') && !empty($request->batch)) {
            $batchRecords = \App\Models\TempTimekeeping::where('import_batch_id', $request->batch)->get();
            $batchInfo = [
                'batch_id' => $request->batch,
                'total_records' => $batchRecords->count(),
                'processed_records' => $batchRecords->where('is_processed', true)->count(),
                'pending_records' => $batchRecords->where('is_processed', false)->count(),
                'employees' => $batchRecords->pluck('employee_id')->unique()->count(),
                'date_range' => [
                    'start' => $batchRecords->min('date'),
                    'end' => $batchRecords->max('date')
                ],
                'created_at' => $batchRecords->first()?->created_at
            ];
        }
        
        return view('attendance.temp-timekeeping', compact('user', 'allRecords', 'groupedRecords', 'batchInfo', 'paginationInfo'));
    }

    /**
     * Approve selected temp timekeeping records and save to attendance_records
     */
    public function approveTempTimekeeping(Request $request)
    {
        $request->validate([
            'selected_records' => 'required|array|min:1',
            'selected_records.*' => 'required|string|exists:temp_timekeeping,id'
        ]);

        try {
            $selectedRecordIds = $request->selected_records;
            $approvedCount = 0;
            $errors = collect();

            Log::info('Starting temp timekeeping approval process', [
                'selected_count' => count($selectedRecordIds),
                'record_ids' => $selectedRecordIds
            ]);

            foreach ($selectedRecordIds as $recordId) {
                $tempRecord = \App\Models\TempTimekeeping::find($recordId);
                
                if (!$tempRecord) {
                    $errors->push("Record with ID {$recordId} not found");
                    continue;
                }

                if ($tempRecord->is_processed) {
                    $errors->push("Record for employee {$tempRecord->employee_id} on {$tempRecord->date} is already processed");
                    continue;
                }

                // Get or create employee
                $employee = \App\Models\Employee::where('employee_id', $tempRecord->employee_id)->first();
                if (!$employee) {
                    $errors->push("Employee {$tempRecord->employee_id} not found in system");
                    continue;
                }

                // Check for existing attendance record
                $existingRecord = \App\Models\AttendanceRecord::where('employee_id', $employee->id)
                    ->where('date', $tempRecord->date)
                    ->first();

                if ($existingRecord) {
                    $errors->push("Attendance record already exists for employee {$tempRecord->employee_id} on {$tempRecord->date}");
                    continue;
                }

                // Create attendance record
                try {
                    // Set default break times (12pm-1pm) if employee has both time in and time out
                    $breakStart = $tempRecord->break_start;
                    $breakEnd = $tempRecord->break_end;
                    
                    if ($tempRecord->time_in && $tempRecord->time_out && !$breakStart && !$breakEnd) {
                        // Set default break time to 12:00 PM - 1:00 PM
                        $breakStart = \Carbon\Carbon::parse($tempRecord->date)->setTime(12, 0, 0);
                        $breakEnd = \Carbon\Carbon::parse($tempRecord->date)->setTime(13, 0, 0);
                    }
                    
                    $attendanceRecord = \App\Models\AttendanceRecord::create([
                        'employee_id' => $employee->id,
                        'date' => $tempRecord->date,
                        'time_in' => $tempRecord->time_in,
                        'time_out' => $tempRecord->time_out,
                        'break_start' => $breakStart,
                        'break_end' => $breakEnd,
                        'total_hours' => $tempRecord->total_hours,
                        'regular_hours' => $tempRecord->regular_hours,
                        'overtime_hours' => $tempRecord->overtime_hours,
                        'status' => $tempRecord->status,
                        'notes' => $tempRecord->notes,
                        'is_night_shift' => false // Default value
                    ]);

                    // Mark temp record as processed
                    $tempRecord->update(['is_processed' => true]);

                    Log::info('Successfully approved temp record', [
                        'temp_record_id' => $tempRecord->id,
                        'attendance_record_id' => $attendanceRecord->id,
                        'employee_id' => $tempRecord->employee_id,
                        'date' => $tempRecord->date
                    ]);

                    $approvedCount++;

                } catch (\Exception $e) {
                    Log::error('Failed to create attendance record', [
                        'temp_record_id' => $tempRecord->id,
                        'employee_id' => $tempRecord->employee_id,
                        'date' => $tempRecord->date,
                        'error' => $e->getMessage()
                    ]);
                    $errors->push("Failed to create attendance record for {$tempRecord->employee_id} on {$tempRecord->date}: " . $e->getMessage());
                }
            }

            Log::info('Temp timekeeping approval completed', [
                'approved_count' => $approvedCount,
                'error_count' => $errors->count()
            ]);

            if ($approvedCount > 0) {
                $message = "Successfully approved {$approvedCount} records and saved to attendance records.";
                if ($errors->count() > 0) {
                    $message .= " {$errors->count()} records had errors and were not processed.";
                }
                return redirect()->route('attendance.temp-timekeeping')->with('success', $message);
            } else {
                return redirect()->route('attendance.temp-timekeeping')->with('error', 'No records were approved. ' . $errors->implode(', '));
            }

        } catch (\Exception $e) {
            Log::error('Temp timekeeping approval failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->route('attendance.temp-timekeeping')->with('error', 'Failed to approve records: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for editing an attendance record
     */
    public function editRecord($id)
    {
        $user = Auth::user();
        $attendanceRecord = AttendanceRecord::with(['employee.department'])->findOrFail($id);
        
        // Check if user is an employee
        $userRole = strtolower(trim($user->role ?? ''));
        $isEmployee = ($userRole === 'employee');
        
        // For employees, ensure they can only edit their own records
        if ($isEmployee && $user->employee) {
            if ($attendanceRecord->employee_id !== $user->employee->id) {
                return redirect()->route('attendance.timekeeping')
                    ->with('error', 'You can only edit your own attendance records.');
            }
        }
        
        // Get employees list - for employees, only show their own record
        $employeesQuery = Employee::with('department');
        if ($isEmployee && $user->employee) {
            $employeesQuery->where('id', $user->employee->id);
        } else {
            // For admin/hr/manager, show all employees
            $currentCompany = CompanyHelper::getCurrentCompany();
            if ($currentCompany) {
                $employeesQuery->forCompany($currentCompany->id);
            }
        }
        $employees = $employeesQuery->get();
        
        return view('attendance.edit-record', compact('attendanceRecord', 'employees', 'user'));
    }

    /**
     * Update the specified attendance record
     */
    public function updateRecord(Request $request, $id)
    {
        $user = Auth::user();
        $attendanceRecord = AttendanceRecord::findOrFail($id);
        
        // Check if user is an employee
        $userRole = strtolower(trim($user->role ?? ''));
        $isEmployee = ($userRole === 'employee');
        
        // For employees, ensure they can only update their own records
        if ($isEmployee && $user->employee) {
            if ($attendanceRecord->employee_id !== $user->employee->id) {
                return redirect()->route('attendance.timekeeping')
                    ->with('error', 'You can only update your own attendance records.');
            }
            // Override employee_id to ensure employees can only update their own records
            $request->merge(['employee_id' => $user->employee->id]);
        }
        
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'date' => 'required|date',
            'time_in' => 'required|date_format:H:i',
            'time_out' => 'nullable|date_format:H:i|after:time_in',
            'break_start' => 'nullable|date_format:H:i',
            'break_end' => 'nullable|date_format:H:i|after:break_start',
            'status' => 'required|in:present,absent,absent_excused,absent_unexcused,absent_sick,absent_personal,late,half_day,on_leave',
            'notes' => 'nullable|string|max:500'
        ]);

        // Additional security check: For employees, verify they're updating their own record
        if ($isEmployee && $user->employee && $request->employee_id !== $user->employee->id) {
            return redirect()->route('attendance.timekeeping')
                ->with('error', 'You can only update your own attendance records.')
                ->withInput();
        }

        // Check if record already exists for this employee and date (excluding current record)
        $existingRecord = AttendanceRecord::where('employee_id', $request->employee_id)
            ->where('date', $request->date)
            ->where('id', '!=', $id)
            ->first();

        if ($existingRecord) {
            return redirect()->back()->with('error', 'An attendance record already exists for this employee on this date.');
        }

        // Calculate total hours if time_out is provided
        $totalHours = 0;
        $breakDuration = 0;
        $regularHours = 0;
        $overtimeHours = 0;
        
        if ($request->time_out) {
            $timeIn = \Carbon\Carbon::createFromFormat('Y-m-d H:i', $request->date . ' ' . $request->time_in);
            $timeOut = \Carbon\Carbon::createFromFormat('Y-m-d H:i', $request->date . ' ' . $request->time_out);
            
            // Calculate break duration if break_start and break_end are provided
            if ($request->break_start && $request->break_end) {
                $breakStart = \Carbon\Carbon::createFromFormat('Y-m-d H:i', $request->date . ' ' . $request->break_start);
                $breakEnd = \Carbon\Carbon::createFromFormat('Y-m-d H:i', $request->date . ' ' . $request->break_end);
                $breakMinutes = $breakEnd->diffInMinutes($breakStart);
                $breakDuration = $breakMinutes / 60;
            } else {
                $breakDuration = 0; // No break entered
            }
            
            // Business Rules:
            // Standard work day: 8 AM to 5 PM (9 hours total)
            // Break time: 1 hour (lunch break)
            // Regular hours: 8 hours (9 hours - 1 hour break)
            // Overtime starts: After 5:30 PM (8 AM + 8 regular hours + 1 hour break = 5 PM, so overtime starts at 5:30 PM)
            
            // Calculate total working time (excluding break)
            $totalMinutes = $timeIn->diffInMinutes($timeOut);
            $totalHours = $totalMinutes / 60;
            $totalHours = $totalHours - $breakDuration; // Subtract break time
            
            // Define standard work schedule
            $standardStart = \Carbon\Carbon::createFromFormat('Y-m-d H:i', $request->date . ' 08:00');
            $standardEnd = \Carbon\Carbon::createFromFormat('Y-m-d H:i', $request->date . ' 17:00');
            $overtimeStart = \Carbon\Carbon::createFromFormat('Y-m-d H:i', $request->date . ' 17:30');
            
            // Calculate regular and overtime hours based on business rules
            if ($timeOut <= $standardEnd) {
                // Worked within standard hours (8 AM - 5 PM)
                $regularHours = $totalHours;
                $overtimeHours = 0;
            } elseif ($timeOut <= $overtimeStart) {
                // Worked until 5:30 PM (no overtime yet)
                $regularHours = $totalHours;
                $overtimeHours = 0;
            } else {
                // Worked beyond 5:30 PM (overtime applies)
                // Calculate regular hours: from time in to 5:30 PM, minus break time
                $regularMinutes = $timeIn->diffInMinutes($overtimeStart);
                $regularHours = ($regularMinutes / 60) - $breakDuration;
                
                // Calculate overtime hours: from 5:30 PM to time out
                $overtimeMinutes = $overtimeStart->diffInMinutes($timeOut);
                $overtimeHours = $overtimeMinutes / 60;
                
                // Ensure regular hours don't exceed 8 hours
                $regularHours = min($regularHours, 8);
            }
            
            // Ensure non-negative values
            $totalHours = max(0, $totalHours);
            $regularHours = max(0, $regularHours);
            $overtimeHours = max(0, $overtimeHours);
        }

        // Update the attendance record
        $attendanceRecord->update([
            'employee_id' => $request->employee_id,
            'date' => $request->date,
            'time_in' => $request->date . ' ' . $request->time_in,
            'time_out' => $request->time_out ? $request->date . ' ' . $request->time_out : null,
            'break_start' => $request->break_start ? $request->date . ' ' . $request->break_start : null,
            'break_end' => $request->break_end ? $request->date . ' ' . $request->break_end : null,
            'total_hours' => $totalHours,
            'regular_hours' => $regularHours,
            'overtime_hours' => $overtimeHours,
            'status' => $request->status,
            'notes' => $request->notes,
        ]);

        // Log the attendance action
        $this->logAttendanceAction($attendanceRecord, 'manual_update', 'Attendance record manually updated');

        return redirect()->route('attendance.timekeeping')->with('success', 'Attendance record updated successfully.');
    }

    /**
     * Delete the specified attendance record
     */
    public function deleteRecord($id)
    {
        $attendanceRecord = AttendanceRecord::findOrFail($id);
        
        // Log the attendance action before deletion
        $this->logAttendanceAction($attendanceRecord, 'manual_delete', 'Attendance record manually deleted');
        
        $attendanceRecord->delete();

        return redirect()->route('attendance.timekeeping')->with('success', 'Attendance record deleted successfully.');
    }

    /**
     * Log attendance action for audit trail
     */
    private function logAttendanceAction($attendanceRecord, $action, $description = null)
    {
        try {
            \App\Models\AttendanceLog::create([
                'attendance_record_id' => $attendanceRecord->id,
                'action' => $action,
                'performed_by' => Auth::id(),
                'performed_at' => now(),
                'reason' => $description,
            ]);
        } catch (\Exception $e) {
            // Log the error but don't fail the main operation
            Log::error('Failed to log attendance action: ' . $e->getMessage());
        }
    }

    /**
     * Export attendance records
     */
    public function exportTimekeeping(Request $request, $format)
    {
        $user = Auth::user();
        $currentCompany = CompanyHelper::getCurrentCompany();
        
        // Check role case-insensitively
        $userRole = strtolower(trim($user->role ?? ''));
        $isEmployee = ($userRole === 'employee') || 
                      (strtolower(trim($user->role ?? '')) === 'employee') ||
                      ($user->role === 'employee') ||
                      ($user->role === 'Employee');
        
        // Initialize employee ID to null - will be set if user is an employee
        $employeeIdForFilter = null;
        
        // Build query same as timekeeping method
        $query = AttendanceRecord::with(['employee.department', 'employee.account']);

        // If user is an employee, restrict to ONLY their own records
        if ($isEmployee) {
            \Log::info('Timekeeping export access - Employee detected', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'user_role' => $user->role
            ]);
            
            // Try multiple ways to get the employee ID
            $employee = null;
            
            // Method 1: Try relationship
            try {
                if ($user->employee) {
                    $employee = $user->employee;
                }
            } catch (\Exception $e) {
                \Log::warning('Employee relationship failed in exportTimekeeping()', ['error' => $e->getMessage()]);
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
                $query->where('employee_id', $employeeIdForFilter);
                \Log::info('Employee ID determined for timekeeping export filter', [
                    'employee_id' => $employeeIdForFilter,
                    'employee_name' => $employee->full_name ?? 'N/A'
                ]);
            } else {
                \Log::warning('Employee account has no linked employee record in exportTimekeeping()', [
                    'user_id' => $user->id,
                    'account_employee_id' => $user->employee_id
                ]);
                return redirect()->route('attendance.timekeeping')
                    ->with('error', 'Your account is not linked to an employee record. Please contact administrator.');
            }
        }

        // Filter by company (but don't apply if employee's company doesn't match)
        if ($currentCompany && !$isEmployee) {
            $query->whereHas('employee', function($q) use ($currentCompany) {
                $q->where('company_id', $currentCompany->id);
            });
        }

        // Apply filters (only for HR/Admin, employees can't filter by other employees)
        // IMPORTANT: Employees are already filtered above, so these filters only apply to admin/hr/manager
        if ($request->filled('employee_id')) {
            if (in_array($userRole, ['admin', 'hr', 'manager'])) {
                $query->where('employee_id', $request->employee_id);
            }
        }

        if ($request->filled('department_id')) {
            if (in_array($userRole, ['admin', 'hr', 'manager'])) {
                $query->whereHas('employee', function($q) use ($request) {
                    $q->where('department_id', $request->department_id);
                });
            }
        }

        if ($request->filled('date_from')) {
            $query->where('date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('date', '<=', $request->date_to);
        }

        // Get all records (no pagination for export)
        $today = today()->format('Y-m-d');
        $records = $query->whereNotNull('time_in')
            ->where(function($q) use ($today) {
                $q->whereNotNull('time_out')
                  ->orWhere(function($subQ) use ($today) {
                      $subQ->whereNull('time_out')
                           ->where('date', $today);
                  });
            })
            ->orderBy('date', 'desc')
            ->orderBy('time_in', 'desc')
            ->get();

        // Generate filename - include employee name for employee exports
        $dateFrom = $request->filled('date_from') ? Carbon::parse($request->date_from)->format('Y-m-d') : 'all';
        $dateTo = $request->filled('date_to') ? Carbon::parse($request->date_to)->format('Y-m-d') : 'all';
        
        if ($employeeIdForFilter !== null) {
            $employee = Employee::find($employeeIdForFilter);
            if ($employee) {
                $employeeName = str_replace(' ', '_', $employee->full_name);
                $filename = 'attendance_records_' . $employeeName . '_' . $dateFrom . '_to_' . $dateTo . '_' . now()->format('Y-m-d');
            } else {
                $filename = 'attendance_records_' . $dateFrom . '_to_' . $dateTo . '_' . now()->format('Y-m-d');
            }
        } else {
            $filename = 'attendance_records_' . $dateFrom . '_to_' . $dateTo . '_' . now()->format('Y-m-d');
        }

        switch ($format) {
            case 'pdf':
                return $this->exportPDF($records, $filename);
            case 'csv':
                return $this->exportCSV($records, $filename);
            case 'xls':
                return $this->exportXLS($records, $filename);
            default:
                return redirect()->route('attendance.timekeeping')->with('error', 'Invalid export format.');
        }
    }

    /**
     * Export to PDF
     */
    private function exportPDF($records, $filename)
    {
        $data = [
            'records' => $records,
            'date' => now()->format('F d, Y'),
        ];

        $pdf = Pdf::loadView('attendance.exports.pdf', $data);
        return $pdf->download($filename . '.pdf');
    }

    /**
     * Export to CSV
     */
    private function exportCSV($records, $filename)
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
                'Date',
                'Employee Code',
                'Employee Name',
                'Department',
                'Time In',
                'Time Out',
                'Hours Worked',
                'Status'
            ]);

            // Data
            foreach ($records as $record) {
                $hoursWorked = 'N/A';
                if ($record->time_in && $record->time_out) {
                    // time_in and time_out are already Carbon datetime instances
                    $timeIn = $record->time_in instanceof Carbon ? $record->time_in : Carbon::parse($record->time_in);
                    $timeOut = $record->time_out instanceof Carbon ? $record->time_out : Carbon::parse($record->time_out);
                    $hoursWorked = round($timeIn->diffInMinutes($timeOut) / 60, 2);
                }

                // Format time_in and time_out properly (they're already Carbon instances)
                $timeInStr = 'N/A';
                $timeOutStr = 'N/A';
                if ($record->time_in) {
                    $timeInStr = $record->time_in instanceof Carbon ? $record->time_in->format('H:i:s') : (is_string($record->time_in) ? substr($record->time_in, 11, 8) : $record->time_in);
                }
                if ($record->time_out) {
                    $timeOutStr = $record->time_out instanceof Carbon ? $record->time_out->format('H:i:s') : (is_string($record->time_out) ? substr($record->time_out, 11, 8) : $record->time_out);
                }
                
                fputcsv($file, [
                    Carbon::parse($record->date)->format('Y-m-d'),
                    $record->employee->employee_code ?? 'N/A',
                    $record->employee->full_name ?? 'N/A',
                    $record->employee->department->name ?? 'N/A',
                    $timeInStr,
                    $timeOutStr,
                    $hoursWorked,
                    $record->time_out ? 'Complete' : 'Incomplete'
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export to Excel
     */
    private function exportXLS($records, $filename)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set headers
        $headers = ['Date', 'Employee Code', 'Employee Name', 'Department', 'Time In', 'Time Out', 'Hours Worked', 'Status'];
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
        $sheet->getStyle('A1:H1')->applyFromArray($headerStyle);

        // Add data
        $row = 2;
        foreach ($records as $record) {
            $hoursWorked = 'N/A';
            if ($record->time_in && $record->time_out) {
                // time_in and time_out are already Carbon datetime instances
                $timeIn = $record->time_in instanceof Carbon ? $record->time_in : Carbon::parse($record->time_in);
                $timeOut = $record->time_out instanceof Carbon ? $record->time_out : Carbon::parse($record->time_out);
                $hoursWorked = round($timeIn->diffInMinutes($timeOut) / 60, 2);
            }

            // Format time_in and time_out properly (they're already Carbon instances)
            $timeInStr = 'N/A';
            $timeOutStr = 'N/A';
            if ($record->time_in) {
                $timeInStr = $record->time_in instanceof Carbon ? $record->time_in->format('H:i:s') : (is_string($record->time_in) ? substr($record->time_in, 11, 8) : $record->time_in);
            }
            if ($record->time_out) {
                $timeOutStr = $record->time_out instanceof Carbon ? $record->time_out->format('H:i:s') : (is_string($record->time_out) ? substr($record->time_out, 11, 8) : $record->time_out);
            }
            
            $sheet->setCellValue('A' . $row, Carbon::parse($record->date)->format('Y-m-d'));
            $sheet->setCellValue('B' . $row, $record->employee->employee_code ?? 'N/A');
            $sheet->setCellValue('C' . $row, $record->employee->full_name ?? 'N/A');
            $sheet->setCellValue('D' . $row, $record->employee->department->name ?? 'N/A');
            $sheet->setCellValue('E' . $row, $timeInStr);
            $sheet->setCellValue('F' . $row, $timeOutStr);
            $sheet->setCellValue('G' . $row, $hoursWorked);
            $sheet->setCellValue('H' . $row, $record->time_out ? 'Complete' : 'Incomplete');
            $row++;
        }

        // Auto-size columns
        foreach (range('A', 'H') as $col) {
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
        $sheet->getStyle('A1:H' . ($row - 1))->applyFromArray($borderStyle);

        $writer = new Xlsx($spreadsheet);
        $tempFile = tempnam(sys_get_temp_dir(), $filename);
        $writer->save($tempFile);

        return response()->download($tempFile, $filename . '.xlsx')->deleteFileAfterSend(true);
    }

    /**
     * Export daily attendance records
     */
    public function exportDaily(Request $request, $format)
    {
        $user = Auth::user();
        $currentCompany = CompanyHelper::getCurrentCompany();
        
        $date = $request->get('date', today()->format('Y-m-d'));
        $date = Carbon::parse($date);
        
        // Check role case-insensitively
        $userRole = strtolower(trim($user->role ?? ''));
        $isEmployee = ($userRole === 'employee') || 
                      (strtolower(trim($user->role ?? '')) === 'employee') ||
                      ($user->role === 'employee') ||
                      ($user->role === 'Employee');
        
        // Initialize employee ID to null - will be set if user is an employee
        $employeeIdForFilter = null;
        
        // If user is an employee, restrict to ONLY their own records
        if ($isEmployee) {
            \Log::info('Daily export access - Employee detected', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'user_role' => $user->role
            ]);
            
            // Try multiple ways to get the employee ID
            $employee = null;
            
            // Method 1: Try relationship
            try {
                if ($user->employee) {
                    $employee = $user->employee;
                }
            } catch (\Exception $e) {
                \Log::warning('Employee relationship failed in exportDaily()', ['error' => $e->getMessage()]);
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
                \Log::info('Employee ID determined for daily export filter', [
                    'employee_id' => $employeeIdForFilter,
                    'employee_name' => $employee->full_name ?? 'N/A'
                ]);
            } else {
                // If no employee found, redirect with error
                \Log::warning('Employee account has no linked employee record in exportDaily()', [
                    'user_id' => $user->id,
                    'account_employee_id' => $user->employee_id
                ]);
                return redirect()->route('attendance.daily')
                    ->with('error', 'Your account is not linked to an employee record. Please contact administrator.');
            }
        }
        
        // Get employees - for employees, only show their own record
        if ($employeeIdForFilter !== null) {
            // Employee can only export their own record
            $employee = Employee::with(['department', 'account'])->find($employeeIdForFilter);
            if ($employee) {
                $allEmployees = collect([$employee]);
            } else {
                $allEmployees = collect();
            }
        } else {
            // For admin/hr/manager, show all employees
            $allEmployeesQuery = Employee::with(['department', 'account'])
                ->whereHas('account', function($query) {
                    $query->where('is_active', true);
                });
                
            // Filter by current company if set
            if ($currentCompany) {
                $allEmployeesQuery->forCompany($currentCompany->id);
            }
            
            $allEmployees = $allEmployeesQuery->orderBy('first_name')->get();
        }

        // Get attendance records - for employees, only their own records
        if ($employeeIdForFilter !== null) {
            // Only get records for this employee
            // Use whereDate() for reliable date comparison (handles timezone and format differences)
            $allAttendanceRecords = AttendanceRecord::with(['employee.department'])
                ->whereDate('date', $date)
                ->where('employee_id', $employeeIdForFilter)
                ->get()
                ->keyBy('employee_id');
        } else {
            // For admin/hr/manager, get all records
            $allAttendanceRecords = AttendanceRecord::with(['employee.department'])
                ->where('date', $date)
                ->get()
                ->keyBy('employee_id');
        }

        // Calculate summary statistics
        $summary = $this->calculateDailySummary($allEmployees, $allAttendanceRecords, $date);

        // Generate filename - include employee name for employee exports
        if ($employeeIdForFilter !== null && $allEmployees->isNotEmpty()) {
            $employeeName = str_replace(' ', '_', $allEmployees->first()->full_name);
            $filename = 'attendance_records_' . $employeeName . '_' . $date->format('Y-m-d') . '_' . now()->format('Y-m-d');
        } else {
            $filename = 'attendance_records_' . $date->format('Y-m-d') . '_' . now()->format('Y-m-d');
        }

        switch ($format) {
            case 'pdf':
                return $this->exportDailyPDF($allEmployees, $allAttendanceRecords, $date, $summary, $filename);
            case 'csv':
                return $this->exportDailyCSV($allEmployees, $allAttendanceRecords, $date, $summary, $filename);
            case 'xls':
                return $this->exportDailyXLS($allEmployees, $allAttendanceRecords, $date, $summary, $filename);
            default:
                return redirect()->route('attendance.daily')->with('error', 'Invalid export format.');
        }
    }

    /**
     * Export daily attendance to PDF
     */
    private function exportDailyPDF($employees, $attendanceRecords, $date, $summary, $filename)
    {
        // Ensure all summary keys exist with defaults
        $summary = array_merge([
            'present' => 0,
            'absent' => 0,
            'late' => 0,
            'half_day' => 0,
            'total' => 0,
            'attendance_rate' => 0,
        ], $summary);
        
        $data = [
            'employees' => $employees,
            'attendanceRecords' => $attendanceRecords,
            'date' => $date,
            'summary' => $summary,
        ];

        $pdf = Pdf::loadView('attendance.exports.daily-pdf', $data);
        return $pdf->download($filename . '.pdf');
    }

    /**
     * Export daily attendance to CSV
     */
    private function exportDailyCSV($employees, $attendanceRecords, $date, $summary, $filename)
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '.csv"',
        ];

        $callback = function() use ($employees, $attendanceRecords, $date) {
            $file = fopen('php://output', 'w');
            
            // BOM for UTF-8
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Headers
            fputcsv($file, [
                'Employee ID',
                'Employee Name',
                'Department',
                'Time In',
                'Time Out',
                'Total Hours',
                'Status'
            ]);

            // Data
            foreach ($employees as $employee) {
                $attendance = $attendanceRecords->get($employee->id);
                
                $timeIn = 'N/A';
                $timeOut = 'N/A';
                $totalHours = 'N/A';
                $status = 'No Record';
                
                if ($attendance) {
                    // Format Time In
                    if ($attendance->time_in) {
                        $timeIn = Carbon::parse($attendance->time_in)->format('g:i A');
                    }
                    
                    // Format Time Out
                    if ($attendance->time_out) {
                        $timeOut = Carbon::parse($attendance->time_out)->format('g:i A');
                    } elseif ($attendance->time_in) {
                        $recordDate = Carbon::parse($attendance->date);
                        $timeOut = $recordDate->isToday() ? 'Working' : 'Not Clocked Out';
                    }
                    
                    // Calculate Total Hours
                    if ($attendance->total_hours) {
                        $totalHours = TimezoneHelper::formatHours($attendance->total_hours);
                    } elseif ($attendance->time_in && $attendance->time_out) {
                        $totalHours = TimezoneHelper::formatHours($attendance->calculateTotalHours());
                    }
                    
                    // Get Status
                    $status = ucfirst(str_replace('_', ' ', $attendance->status));
                }

                fputcsv($file, [
                    $employee->employee_id ?? 'N/A',
                    $employee->full_name ?? 'N/A',
                    $employee->department->name ?? 'N/A',
                    $timeIn,
                    $timeOut,
                    $totalHours,
                    $status
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export daily attendance to Excel
     */
    private function exportDailyXLS($employees, $attendanceRecords, $date, $summary, $filename)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set title
        $sheet->setCellValue('A1', 'Attendance Records - ' . $date->format('F d, Y'));
        $sheet->mergeCells('A1:G1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Set headers
        $headers = ['Employee ID', 'Employee Name', 'Department', 'Time In', 'Time Out', 'Total Hours', 'Status'];
        $sheet->fromArray($headers, null, 'A3');

        // Style header row
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4472C4']
            ],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ];
        $sheet->getStyle('A3:G3')->applyFromArray($headerStyle);

        // Add data
        $row = 4;
        foreach ($employees as $employee) {
            $attendance = $attendanceRecords->get($employee->id);
            
            $timeIn = 'N/A';
            $timeOut = 'N/A';
            $totalHours = 'N/A';
            $status = 'No Record';
            
            if ($attendance) {
                // Format Time In
                if ($attendance->time_in) {
                    $timeIn = Carbon::parse($attendance->time_in)->format('g:i A');
                }
                
                // Format Time Out
                if ($attendance->time_out) {
                    $timeOut = Carbon::parse($attendance->time_out)->format('g:i A');
                } elseif ($attendance->time_in) {
                    $recordDate = Carbon::parse($attendance->date);
                    $timeOut = $recordDate->isToday() ? 'Working' : 'Not Clocked Out';
                }
                
                // Calculate Total Hours
                if ($attendance->total_hours) {
                    $totalHours = TimezoneHelper::formatHours($attendance->total_hours);
                } elseif ($attendance->time_in && $attendance->time_out) {
                    $totalHours = TimezoneHelper::formatHours($attendance->calculateTotalHours());
                }
                
                // Get Status
                $status = ucfirst(str_replace('_', ' ', $attendance->status));
            }

            $sheet->setCellValue('A' . $row, $employee->employee_id ?? 'N/A');
            $sheet->setCellValue('B' . $row, $employee->full_name ?? 'N/A');
            $sheet->setCellValue('C' . $row, $employee->department->name ?? 'N/A');
            $sheet->setCellValue('D' . $row, $timeIn);
            $sheet->setCellValue('E' . $row, $timeOut);
            $sheet->setCellValue('F' . $row, $totalHours);
            $sheet->setCellValue('G' . $row, $status);
            $row++;
        }

        // Add summary
        $summaryRow = $row + 2;
        $sheet->setCellValue('A' . $summaryRow, 'Summary');
        $sheet->getStyle('A' . $summaryRow)->getFont()->setBold(true);
        $sheet->setCellValue('A' . ($summaryRow + 1), 'Present: ' . ($summary['present'] ?? 0));
        $sheet->setCellValue('B' . ($summaryRow + 1), 'Absent: ' . ($summary['absent'] ?? 0));
        $sheet->setCellValue('C' . ($summaryRow + 1), 'Late: ' . ($summary['late'] ?? 0));
        $sheet->setCellValue('D' . ($summaryRow + 1), 'Half Day: ' . ($summary['half_day'] ?? 0));

        // Auto-size columns
        foreach (range('A', 'G') as $col) {
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
        $sheet->getStyle('A3:G' . ($row - 1))->applyFromArray($borderStyle);

        $writer = new Xlsx($spreadsheet);
        $tempFile = tempnam(sys_get_temp_dir(), $filename);
        $writer->save($tempFile);

        return response()->download($tempFile, $filename . '.xlsx')->deleteFileAfterSend(true);
    }

    /**
     * Display attendance reports page
     */
    public function reports(Request $request)
    {
        $user = Auth::user();
        $currentCompany = CompanyHelper::getCurrentCompany();
        
        // Get departments (filtered by company)
        $departmentsQuery = \App\Models\Department::query();
        if ($currentCompany) {
            $departmentsQuery->forCompany($currentCompany->id);
        }
        $departments = $departmentsQuery->get();
        
        // Get report type and filters
        $reportType = $request->get('report_type', 'daily');
        $departmentId = $request->get('department_id');
        
        // Handle different date inputs based on report type
        if ($reportType === 'daily') {
            $dateFrom = $request->get('date_from', today()->format('Y-m-d'));
            $dateTo = $request->get('date_to', $dateFrom); // Same as date_from for daily
        } elseif ($reportType === 'weekly') {
            $dateFrom = $request->get('date_from', now()->startOfWeek()->format('Y-m-d'));
            $dateTo = $request->get('date_to', now()->endOfWeek()->format('Y-m-d'));
        } elseif ($reportType === 'monthly') {
            // Handle month input (YYYY-MM format)
            if ($request->has('month')) {
                $month = Carbon::createFromFormat('Y-m', $request->get('month'));
                $dateFrom = $month->copy()->startOfMonth()->format('Y-m-d');
                $dateTo = $month->copy()->endOfMonth()->format('Y-m-d');
            } else {
                $dateFrom = $request->get('date_from', now()->startOfMonth()->format('Y-m-d'));
                $dateTo = $request->get('date_to', now()->endOfMonth()->format('Y-m-d'));
            }
        } elseif ($reportType === 'yearly') {
            // Handle year input
            if ($request->has('year')) {
                $year = $request->get('year');
                $dateFrom = Carbon::createFromDate($year, 1, 1)->format('Y-m-d');
                $dateTo = Carbon::createFromDate($year, 12, 31)->format('Y-m-d');
            } else {
                $dateFrom = $request->get('date_from', now()->startOfYear()->format('Y-m-d'));
                $dateTo = $request->get('date_to', now()->endOfYear()->format('Y-m-d'));
            }
        } else {
            // Overtime and Leave reports use date range
            $dateFrom = $request->get('date_from', now()->startOfMonth()->format('Y-m-d'));
            $dateTo = $request->get('date_to', now()->format('Y-m-d'));
        }
        
        // Initialize summary data
        $summary = [
            'present_days' => 0,
            'absent_days' => 0,
            'late_arrivals' => 0,
            'attendance_rate' => 0,
        ];
        
        $departmentStats = [];
        $bestAttendance = [];
        $needsAttention = [];
        $attendanceTrend = [];
        $overtimeData = [];
        $leaveData = [];
        
        // Generate report data based on type
        if ($request->has('generate')) {
            if ($reportType === 'overtime') {
                // Handle overtime report
                $overtimeData = $this->calculateOvertimeReport($dateFrom, $dateTo, $departmentId, $currentCompany);
            } elseif ($reportType === 'leave') {
                // Handle leave report
                $leaveData = $this->calculateLeaveReport($dateFrom, $dateTo, $departmentId, $currentCompany);
            } else {
                // Handle attendance reports
                $summary = $this->calculateReportSummary($dateFrom, $dateTo, $departmentId, $currentCompany);
                $departmentStats = $this->calculateDepartmentStats($dateFrom, $dateTo, $departmentId, $currentCompany);
                $bestAttendance = $this->getBestAttendance($dateFrom, $dateTo, $departmentId, $currentCompany);
                $needsAttention = $this->getNeedsAttention($dateFrom, $dateTo, $departmentId, $currentCompany);
                $attendanceTrend = $this->calculateAttendanceTrend($dateFrom, $dateTo, $reportType, $departmentId, $currentCompany);
            }
        }
        
        return view('attendance.reports', compact(
            'user', 
            'departments', 
            'reportType', 
            'departmentId', 
            'dateFrom', 
            'dateTo',
            'summary',
            'departmentStats',
            'bestAttendance',
            'needsAttention',
            'overtimeData',
            'leaveData',
            'attendanceTrend'
        ));
    }

    /**
     * Export attendance reports
     */
    public function exportReports(Request $request, $format)
    {
        $user = Auth::user();
        $currentCompany = CompanyHelper::getCurrentCompany();
        
        // Get report type and filters (same logic as reports method)
        $reportType = $request->get('report_type', 'daily');
        $departmentId = $request->get('department_id');
        
        // Handle different date inputs based on report type
        if ($reportType === 'daily') {
            $dateFrom = $request->get('date_from', today()->format('Y-m-d'));
            $dateTo = $request->get('date_to', $dateFrom);
        } elseif ($reportType === 'weekly') {
            $dateFrom = $request->get('date_from', now()->startOfWeek()->format('Y-m-d'));
            $dateTo = $request->get('date_to', now()->endOfWeek()->format('Y-m-d'));
        } elseif ($reportType === 'monthly') {
            if ($request->has('month')) {
                $month = Carbon::createFromFormat('Y-m', $request->get('month'));
                $dateFrom = $month->copy()->startOfMonth()->format('Y-m-d');
                $dateTo = $month->copy()->endOfMonth()->format('Y-m-d');
            } else {
                $dateFrom = $request->get('date_from', now()->startOfMonth()->format('Y-m-d'));
                $dateTo = $request->get('date_to', now()->endOfMonth()->format('Y-m-d'));
            }
        } elseif ($reportType === 'yearly') {
            if ($request->has('year')) {
                $year = $request->get('year');
                $dateFrom = Carbon::createFromDate($year, 1, 1)->format('Y-m-d');
                $dateTo = Carbon::createFromDate($year, 12, 31)->format('Y-m-d');
            } else {
                $dateFrom = $request->get('date_from', now()->startOfYear()->format('Y-m-d'));
                $dateTo = $request->get('date_to', now()->endOfYear()->format('Y-m-d'));
            }
        } else {
            $dateFrom = $request->get('date_from', now()->startOfMonth()->format('Y-m-d'));
            $dateTo = $request->get('date_to', now()->format('Y-m-d'));
        }
        
        // Generate report data
        $summary = [
            'present_days' => 0,
            'absent_days' => 0,
            'late_arrivals' => 0,
            'attendance_rate' => 0,
        ];
        
        $departmentStats = [];
        $bestAttendance = [];
        $needsAttention = [];
        $overtimeData = [];
        $leaveData = [];
        
        if ($reportType === 'overtime') {
            $overtimeData = $this->calculateOvertimeReport($dateFrom, $dateTo, $departmentId, $currentCompany);
        } elseif ($reportType === 'leave') {
            $leaveData = $this->calculateLeaveReport($dateFrom, $dateTo, $departmentId, $currentCompany);
        } else {
            $summary = $this->calculateReportSummary($dateFrom, $dateTo, $departmentId, $currentCompany);
            $departmentStats = $this->calculateDepartmentStats($dateFrom, $dateTo, $departmentId, $currentCompany);
            $bestAttendance = $this->getBestAttendance($dateFrom, $dateTo, $departmentId, $currentCompany);
            $needsAttention = $this->getNeedsAttention($dateFrom, $dateTo, $departmentId, $currentCompany);
        }
        
        // Generate filename
        $reportTypeName = ucfirst($reportType);
        $dateFromFormatted = Carbon::parse($dateFrom)->format('Y-m-d');
        $dateToFormatted = Carbon::parse($dateTo)->format('Y-m-d');
        $filename = 'attendance_report_' . strtolower($reportTypeName) . '_' . $dateFromFormatted . '_to_' . $dateToFormatted . '_' . now()->format('Y-m-d');
        
        switch ($format) {
            case 'pdf':
                return $this->exportReportsPDF($summary, $departmentStats, $bestAttendance, $needsAttention, $overtimeData, $leaveData, $reportType, $dateFrom, $dateTo, $filename);
            case 'csv':
                return $this->exportReportsCSV($summary, $departmentStats, $bestAttendance, $needsAttention, $overtimeData, $leaveData, $reportType, $dateFrom, $dateTo, $filename);
            case 'xls':
                return $this->exportReportsXLS($summary, $departmentStats, $bestAttendance, $needsAttention, $overtimeData, $leaveData, $reportType, $dateFrom, $dateTo, $filename);
            default:
                return redirect()->route('attendance.reports')->with('error', 'Invalid export format.');
        }
    }

    /**
     * Export reports to PDF
     */
    private function exportReportsPDF($summary, $departmentStats, $bestAttendance, $needsAttention, $overtimeData, $leaveData, $reportType, $dateFrom, $dateTo, $filename)
    {
        $data = [
            'summary' => $summary,
            'departmentStats' => $departmentStats,
            'bestAttendance' => $bestAttendance,
            'needsAttention' => $needsAttention,
            'overtimeData' => $overtimeData,
            'leaveData' => $leaveData,
            'reportType' => $reportType,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'date' => now()->format('F d, Y'),
        ];

        $pdf = Pdf::loadView('attendance.exports.reports-pdf', $data);
        return $pdf->download($filename . '.pdf');
    }

    /**
     * Export reports to CSV
     */
    private function exportReportsCSV($summary, $departmentStats, $bestAttendance, $needsAttention, $overtimeData, $leaveData, $reportType, $dateFrom, $dateTo, $filename)
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '.csv"',
        ];

        $callback = function() use ($summary, $departmentStats, $bestAttendance, $needsAttention, $overtimeData, $leaveData, $reportType, $dateFrom, $dateTo) {
            $file = fopen('php://output', 'w');
            
            // BOM for UTF-8
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Report Header
            fputcsv($file, ['Attendance Report - ' . ucfirst($reportType)]);
            fputcsv($file, ['Date Range: ' . Carbon::parse($dateFrom)->format('M d, Y') . ' to ' . Carbon::parse($dateTo)->format('M d, Y')]);
            fputcsv($file, ['Generated: ' . now()->format('F d, Y H:i:s')]);
            fputcsv($file, []); // Empty row
            
            if ($reportType === 'overtime') {
                // Overtime Report
                fputcsv($file, ['Overtime Report Summary']);
                fputcsv($file, ['Total Requests', $overtimeData['summary']['total_requests'] ?? 0]);
                fputcsv($file, ['Total Hours', $overtimeData['summary']['total_hours'] ?? 0]);
                fputcsv($file, ['Total Employees', $overtimeData['summary']['total_employees'] ?? 0]);
                fputcsv($file, ['Average Hours', $overtimeData['summary']['average_hours'] ?? 0]);
                fputcsv($file, []); // Empty row
                
                fputcsv($file, ['Employee', 'Department', 'Total Requests', 'Total Hours', 'Average Hours']);
                foreach ($overtimeData['by_employee'] ?? [] as $emp) {
                    fputcsv($file, [
                        $emp['employee_name'],
                        $emp['department'] ?? 'N/A',
                        $emp['total_requests'],
                        $emp['total_hours'],
                        $emp['average_hours']
                    ]);
                }
            } elseif ($reportType === 'leave') {
                // Leave Report
                fputcsv($file, ['Leave Report Summary']);
                fputcsv($file, ['Total Requests', $leaveData['summary']['total_requests'] ?? 0]);
                fputcsv($file, ['Total Days', $leaveData['summary']['total_days'] ?? 0]);
                fputcsv($file, ['Total Employees', $leaveData['summary']['total_employees'] ?? 0]);
                fputcsv($file, ['Average Days', $leaveData['summary']['average_days'] ?? 0]);
                fputcsv($file, []); // Empty row
                
                fputcsv($file, ['Employee', 'Department', 'Total Requests', 'Total Days', 'Average Days']);
                foreach ($leaveData['by_employee'] ?? [] as $emp) {
                    fputcsv($file, [
                        $emp['employee_name'],
                        $emp['department'] ?? 'N/A',
                        $emp['total_requests'],
                        $emp['total_days'],
                        $emp['average_days']
                    ]);
                }
            } else {
                // Attendance Report
                fputcsv($file, ['Attendance Report Summary']);
                fputcsv($file, ['Present Days', $summary['present_days'] ?? 0]);
                fputcsv($file, ['Absent Days', $summary['absent_days'] ?? 0]);
                fputcsv($file, ['Late Arrivals', $summary['late_arrivals'] ?? 0]);
                fputcsv($file, ['Attendance Rate', ($summary['attendance_rate'] ?? 0) . '%']);
                fputcsv($file, []); // Empty row
                
                // Department-wise Stats
                fputcsv($file, ['Department-wise Attendance']);
                fputcsv($file, ['Department', 'Total Employees', 'Present', 'Absent', 'Late', 'Attendance Rate']);
                foreach ($departmentStats as $stat) {
                    fputcsv($file, [
                        $stat['department'],
                        $stat['total_employees'],
                        $stat['present'],
                        $stat['absent'],
                        $stat['late'],
                        $stat['attendance_rate'] . '%'
                    ]);
                }
                fputcsv($file, []); // Empty row
                
                // Top Performers
                fputcsv($file, ['Top Performers']);
                fputcsv($file, ['Employee', 'Department', 'Attendance Rate', 'Present Days', 'Absent Days']);
                foreach ($bestAttendance as $emp) {
                    fputcsv($file, [
                        $emp['employee_name'],
                        $emp['department'] ?? 'N/A',
                        $emp['rate'] . '%',
                        $emp['present_days'],
                        $emp['absent_days']
                    ]);
                }
                fputcsv($file, []); // Empty row
                
                // Needs Attention
                fputcsv($file, ['Needs Attention']);
                fputcsv($file, ['Employee', 'Department', 'Attendance Rate', 'Absent Days', 'Late Arrivals', 'Consecutive Absences']);
                foreach ($needsAttention as $item) {
                    $emp = $item['employee'];
                    fputcsv($file, [
                        $emp->full_name,
                        $emp->department->name ?? 'N/A',
                        $item['rate'] . '%',
                        $item['absent_days'] ?? 0,
                        $item['late_arrivals'] ?? 0,
                        $item['consecutive_absences'] ?? 0
                    ]);
                }
            }
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export reports to Excel
     */
    private function exportReportsXLS($summary, $departmentStats, $bestAttendance, $needsAttention, $overtimeData, $leaveData, $reportType, $dateFrom, $dateTo, $filename)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        $row = 1;
        
        // Header
        $sheet->setCellValue('A' . $row, 'Attendance Report - ' . ucfirst($reportType));
        $sheet->mergeCells('A' . $row . ':E' . $row);
        $sheet->getStyle('A' . $row)->getFont()->setBold(true)->setSize(14);
        $row++;
        
        $sheet->setCellValue('A' . $row, 'Date Range: ' . Carbon::parse($dateFrom)->format('M d, Y') . ' to ' . Carbon::parse($dateTo)->format('M d, Y'));
        $sheet->mergeCells('A' . $row . ':E' . $row);
        $row++;
        
        $sheet->setCellValue('A' . $row, 'Generated: ' . now()->format('F d, Y H:i:s'));
        $sheet->mergeCells('A' . $row . ':E' . $row);
        $row += 2;
        
        if ($reportType === 'overtime') {
            // Overtime Report
            $sheet->setCellValue('A' . $row, 'Overtime Report Summary');
            $sheet->getStyle('A' . $row)->getFont()->setBold(true)->setSize(12);
            $row++;
            
            $sheet->setCellValue('A' . $row, 'Total Requests');
            $sheet->setCellValue('B' . $row, $overtimeData['summary']['total_requests'] ?? 0);
            $row++;
            $sheet->setCellValue('A' . $row, 'Total Hours');
            $sheet->setCellValue('B' . $row, $overtimeData['summary']['total_hours'] ?? 0);
            $row++;
            $sheet->setCellValue('A' . $row, 'Total Employees');
            $sheet->setCellValue('B' . $row, $overtimeData['summary']['total_employees'] ?? 0);
            $row++;
            $sheet->setCellValue('A' . $row, 'Average Hours');
            $sheet->setCellValue('B' . $row, $overtimeData['summary']['average_hours'] ?? 0);
            $row += 2;
            
            // Employee Data
            $sheet->setCellValue('A' . $row, 'Employee');
            $sheet->setCellValue('B' . $row, 'Department');
            $sheet->setCellValue('C' . $row, 'Total Requests');
            $sheet->setCellValue('D' . $row, 'Total Hours');
            $sheet->setCellValue('E' . $row, 'Average Hours');
            $headerRow = $row;
            $sheet->getStyle('A' . $row . ':E' . $row)->getFont()->setBold(true);
            $sheet->getStyle('A' . $row . ':E' . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('4472C4');
            $sheet->getStyle('A' . $row . ':E' . $row)->getFont()->getColor()->setRGB('FFFFFF');
            $row++;
            
            foreach ($overtimeData['by_employee'] ?? [] as $emp) {
                $sheet->setCellValue('A' . $row, $emp['employee_name']);
                $sheet->setCellValue('B' . $row, $emp['department'] ?? 'N/A');
                $sheet->setCellValue('C' . $row, $emp['total_requests']);
                $sheet->setCellValue('D' . $row, $emp['total_hours']);
                $sheet->setCellValue('E' . $row, $emp['average_hours']);
                $row++;
            }
            
            $sheet->getStyle('A' . $headerRow . ':E' . ($row - 1))->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => 'CCCCCC']
                    ]
                ]
            ]);
            
        } elseif ($reportType === 'leave') {
            // Leave Report
            $sheet->setCellValue('A' . $row, 'Leave Report Summary');
            $sheet->getStyle('A' . $row)->getFont()->setBold(true)->setSize(12);
            $row++;
            
            $sheet->setCellValue('A' . $row, 'Total Requests');
            $sheet->setCellValue('B' . $row, $leaveData['summary']['total_requests'] ?? 0);
            $row++;
            $sheet->setCellValue('A' . $row, 'Total Days');
            $sheet->setCellValue('B' . $row, $leaveData['summary']['total_days'] ?? 0);
            $row++;
            $sheet->setCellValue('A' . $row, 'Total Employees');
            $sheet->setCellValue('B' . $row, $leaveData['summary']['total_employees'] ?? 0);
            $row++;
            $sheet->setCellValue('A' . $row, 'Average Days');
            $sheet->setCellValue('B' . $row, $leaveData['summary']['average_days'] ?? 0);
            $row += 2;
            
            // Employee Data
            $sheet->setCellValue('A' . $row, 'Employee');
            $sheet->setCellValue('B' . $row, 'Department');
            $sheet->setCellValue('C' . $row, 'Total Requests');
            $sheet->setCellValue('D' . $row, 'Total Days');
            $sheet->setCellValue('E' . $row, 'Average Days');
            $headerRow = $row;
            $sheet->getStyle('A' . $row . ':E' . $row)->getFont()->setBold(true);
            $sheet->getStyle('A' . $row . ':E' . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('4472C4');
            $sheet->getStyle('A' . $row . ':E' . $row)->getFont()->getColor()->setRGB('FFFFFF');
            $row++;
            
            foreach ($leaveData['by_employee'] ?? [] as $emp) {
                $sheet->setCellValue('A' . $row, $emp['employee_name']);
                $sheet->setCellValue('B' . $row, $emp['department'] ?? 'N/A');
                $sheet->setCellValue('C' . $row, $emp['total_requests']);
                $sheet->setCellValue('D' . $row, $emp['total_days']);
                $sheet->setCellValue('E' . $row, $emp['average_days']);
                $row++;
            }
            
            $sheet->getStyle('A' . $headerRow . ':E' . ($row - 1))->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => 'CCCCCC']
                    ]
                ]
            ]);
            
        } else {
            // Attendance Report
            $sheet->setCellValue('A' . $row, 'Attendance Report Summary');
            $sheet->getStyle('A' . $row)->getFont()->setBold(true)->setSize(12);
            $row++;
            
            $sheet->setCellValue('A' . $row, 'Present Days');
            $sheet->setCellValue('B' . $row, $summary['present_days'] ?? 0);
            $row++;
            $sheet->setCellValue('A' . $row, 'Absent Days');
            $sheet->setCellValue('B' . $row, $summary['absent_days'] ?? 0);
            $row++;
            $sheet->setCellValue('A' . $row, 'Late Arrivals');
            $sheet->setCellValue('B' . $row, $summary['late_arrivals'] ?? 0);
            $row++;
            $sheet->setCellValue('A' . $row, 'Attendance Rate');
            $sheet->setCellValue('B' . $row, ($summary['attendance_rate'] ?? 0) . '%');
            $row += 2;
            
            // Department-wise Stats
            $sheet->setCellValue('A' . $row, 'Department-wise Attendance');
            $sheet->getStyle('A' . $row)->getFont()->setBold(true)->setSize(12);
            $row++;
            
            $sheet->setCellValue('A' . $row, 'Department');
            $sheet->setCellValue('B' . $row, 'Total Employees');
            $sheet->setCellValue('C' . $row, 'Present');
            $sheet->setCellValue('D' . $row, 'Absent');
            $sheet->setCellValue('E' . $row, 'Late');
            $sheet->setCellValue('F' . $row, 'Attendance Rate');
            $deptHeaderRow = $row;
            $sheet->getStyle('A' . $row . ':F' . $row)->getFont()->setBold(true);
            $sheet->getStyle('A' . $row . ':F' . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('4472C4');
            $sheet->getStyle('A' . $row . ':F' . $row)->getFont()->getColor()->setRGB('FFFFFF');
            $row++;
            
            foreach ($departmentStats as $stat) {
                $sheet->setCellValue('A' . $row, $stat['department']);
                $sheet->setCellValue('B' . $row, $stat['total_employees']);
                $sheet->setCellValue('C' . $row, $stat['present']);
                $sheet->setCellValue('D' . $row, $stat['absent']);
                $sheet->setCellValue('E' . $row, $stat['late']);
                $sheet->setCellValue('F' . $row, $stat['attendance_rate'] . '%');
                $row++;
            }
            
            $sheet->getStyle('A' . $deptHeaderRow . ':F' . ($row - 1))->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => 'CCCCCC']
                    ]
                ]
            ]);
            
            $row += 2;
            
            // Top Performers
            $sheet->setCellValue('A' . $row, 'Top Performers');
            $sheet->getStyle('A' . $row)->getFont()->setBold(true)->setSize(12);
            $row++;
            
            $sheet->setCellValue('A' . $row, 'Employee');
            $sheet->setCellValue('B' . $row, 'Department');
            $sheet->setCellValue('C' . $row, 'Attendance Rate');
            $sheet->setCellValue('D' . $row, 'Present Days');
            $sheet->setCellValue('E' . $row, 'Absent Days');
            $topHeaderRow = $row;
            $sheet->getStyle('A' . $row . ':E' . $row)->getFont()->setBold(true);
            $sheet->getStyle('A' . $row . ':E' . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('4472C4');
            $sheet->getStyle('A' . $row . ':E' . $row)->getFont()->getColor()->setRGB('FFFFFF');
            $row++;
            
            foreach ($bestAttendance as $emp) {
                $sheet->setCellValue('A' . $row, $emp['employee_name']);
                $sheet->setCellValue('B' . $row, $emp['department'] ?? 'N/A');
                $sheet->setCellValue('C' . $row, $emp['rate'] . '%');
                $sheet->setCellValue('D' . $row, $emp['present_days']);
                $sheet->setCellValue('E' . $row, $emp['absent_days']);
                $row++;
            }
            
            $sheet->getStyle('A' . $topHeaderRow . ':E' . ($row - 1))->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => 'CCCCCC']
                    ]
                ]
            ]);
            
            $row += 2;
            
            // Needs Attention
            $sheet->setCellValue('A' . $row, 'Needs Attention');
            $sheet->getStyle('A' . $row)->getFont()->setBold(true)->setSize(12);
            $row++;
            
            $sheet->setCellValue('A' . $row, 'Employee');
            $sheet->setCellValue('B' . $row, 'Department');
            $sheet->setCellValue('C' . $row, 'Attendance Rate');
            $sheet->setCellValue('D' . $row, 'Absent Days');
            $sheet->setCellValue('E' . $row, 'Late Arrivals');
            $sheet->setCellValue('F' . $row, 'Consecutive Absences');
            $needsHeaderRow = $row;
            $sheet->getStyle('A' . $row . ':F' . $row)->getFont()->setBold(true);
            $sheet->getStyle('A' . $row . ':F' . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('4472C4');
            $sheet->getStyle('A' . $row . ':F' . $row)->getFont()->getColor()->setRGB('FFFFFF');
            $row++;
            
            foreach ($needsAttention as $item) {
                $emp = $item['employee'];
                $sheet->setCellValue('A' . $row, $emp->full_name);
                $sheet->setCellValue('B' . $row, $emp->department->name ?? 'N/A');
                $sheet->setCellValue('C' . $row, $item['rate'] . '%');
                $sheet->setCellValue('D' . $row, $item['absent_days'] ?? 0);
                $sheet->setCellValue('E' . $row, $item['late_arrivals'] ?? 0);
                $sheet->setCellValue('F' . $row, $item['consecutive_absences'] ?? 0);
                $row++;
            }
            
            $sheet->getStyle('A' . $needsHeaderRow . ':F' . ($row - 1))->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => 'CCCCCC']
                    ]
                ]
            ]);
        }
        
        // Auto-size columns
        foreach (range('A', 'F') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        
        $writer = new Xlsx($spreadsheet);
        $tempFile = tempnam(sys_get_temp_dir(), $filename);
        $writer->save($tempFile);
        
        return response()->download($tempFile, $filename . '.xlsx')->deleteFileAfterSend(true);
    }

    /**
     * Calculate report summary statistics
     */
    private function calculateReportSummary($dateFrom, $dateTo, $departmentId, $currentCompany)
    {
        // Calculate number of days in the range
        $startDate = Carbon::parse($dateFrom);
        $endDate = Carbon::parse($dateTo);
        $totalDays = $startDate->diffInDays($endDate) + 1;
        
        // Get all active employees (filtered by company and department if specified)
        $employeesQuery = Employee::whereHas('account', function($q) {
            $q->where('is_active', true);
        });
        
        if ($currentCompany) {
            $employeesQuery->where('company_id', $currentCompany->id);
        }
        
        if ($departmentId) {
            $employeesQuery->where('department_id', $departmentId);
        }
        
        $employees = $employeesQuery->get();
        $totalEmployees = $employees->count();
        
        // Get all attendance records for these employees
        $query = AttendanceRecord::with(['employee.department', 'employee.account'])
            ->whereBetween('date', [$dateFrom, $dateTo])
            ->whereHas('employee.account', function($q) {
                $q->where('is_active', true);
            });
        
        if ($currentCompany) {
            $query->whereHas('employee', function($q) use ($currentCompany) {
                $q->where('company_id', $currentCompany->id);
            });
        }
        
        if ($departmentId) {
            $query->whereHas('employee', function($q) use ($departmentId) {
                $q->where('department_id', $departmentId);
            });
        }
        
        $records = $query->get();
        
        // Count records by status
        $present = $records->where('status', 'present')->count();
        $late = $records->where('status', 'late')->count();
        $halfDay = $records->where('status', 'half_day')->count();
        $absentRecords = $records->where('status', 'absent')->count();
        
        // Calculate expected total attendance days (employees × days)
        $expectedTotalDays = $totalEmployees * $totalDays;
        
        // Count actual recorded days (all records)
        $recordedDays = $records->count();
        
        // Missing records (no attendance record) should be counted as absent
        $missingDays = max(0, $expectedTotalDays - $recordedDays);
        $absentDays = $absentRecords + $missingDays;
        
        // Present days includes present, late, and half_day (all are considered attendance)
        $presentDays = $present + $late + $halfDay;
        
        // Calculate attendance rate based on total expected days
        $attendanceRate = $expectedTotalDays > 0 ? round(($presentDays / $expectedTotalDays) * 100, 1) : 0;
        
        return [
            'present_days' => $presentDays,
            'absent_days' => $absentDays,
            'late_arrivals' => $late,
            'attendance_rate' => $attendanceRate,
        ];
    }

    /**
     * Calculate department-wise statistics
     */
    private function calculateDepartmentStats($dateFrom, $dateTo, $departmentId, $currentCompany)
    {
        // Get departments filtered by company and department (if specified)
        $departmentsQuery = \App\Models\Department::query();
        if ($currentCompany) {
            $departmentsQuery->forCompany($currentCompany->id);
        }
        
        // If a specific department is selected, only show that department
        if ($departmentId) {
            $departmentsQuery->where('id', $departmentId);
        }
        
        $departments = $departmentsQuery->get();
        
        $stats = [];
        
        // Calculate number of days in the range
        $startDate = Carbon::parse($dateFrom);
        $endDate = Carbon::parse($dateTo);
        $totalDays = $startDate->diffInDays($endDate) + 1;
        
        foreach ($departments as $dept) {
            // Get all employees in this department
            $employeesQuery = Employee::where('department_id', $dept->id)
                ->whereHas('account', function($q) {
                    $q->where('is_active', true);
                });
            
            if ($currentCompany) {
                $employeesQuery->where('company_id', $currentCompany->id);
            }
            
            $employees = $employeesQuery->get();
            $totalEmployees = $employees->count();
            
            // Show all departments, even if they have no employees (will show 0 for all stats)
            
            // Get all attendance records for employees in this department
            $query = AttendanceRecord::with(['employee.department', 'employee.account'])
                ->whereBetween('date', [$dateFrom, $dateTo])
                ->whereHas('employee', function($q) use ($dept) {
                    $q->where('department_id', $dept->id);
                })
                ->whereHas('employee.account', function($q) {
                    $q->where('is_active', true);
                });
            
            if ($currentCompany) {
                $query->whereHas('employee', function($q) use ($currentCompany) {
                    $q->where('company_id', $currentCompany->id);
                });
            }
            
            $records = $query->get();
            
            // Count records by status
            $present = $records->where('status', 'present')->count();
            $late = $records->where('status', 'late')->count();
            $halfDay = $records->where('status', 'half_day')->count();
            $absentRecords = $records->where('status', 'absent')->count();
            
            // Calculate expected total attendance days (employees × days)
            $expectedTotalDays = $totalEmployees * $totalDays;
            
            // Count actual recorded days (all records)
            $recordedDays = $records->count();
            
            // Missing records (no attendance record) should be counted as absent
            $missingDays = max(0, $expectedTotalDays - $recordedDays);
            $absent = $absentRecords + $missingDays;
            
            // Total present includes present, late, and half_day (all are considered attendance)
            $totalPresent = $present + $late + $halfDay;
            
            // Calculate attendance rate based on total expected days
            $attendanceRate = $expectedTotalDays > 0 ? round(($totalPresent / $expectedTotalDays) * 100, 1) : 0;
            
            $stats[] = [
                'department' => $dept->name,
                'total_employees' => $totalEmployees,
                'present' => $present,
                'absent' => $absent,
                'late' => $late,
                'attendance_rate' => $attendanceRate,
            ];
        }
        
        return $stats;
    }

    /**
     * Get best attendance employees
     */
    private function getBestAttendance($dateFrom, $dateTo, $departmentId, $currentCompany)
    {
        $query = Employee::with(['department', 'account'])
            ->whereHas('account', function($q) {
                $q->where('is_active', true);
            });
        
        if ($currentCompany) {
            $query->where('company_id', $currentCompany->id);
        }
        
        if ($departmentId) {
            $query->where('department_id', $departmentId);
        }
        
        $employees = $query->get();
        $attendanceRates = [];
        
        foreach ($employees as $employee) {
            $records = AttendanceRecord::where('employee_id', $employee->id)
                ->whereBetween('date', [$dateFrom, $dateTo])
                ->get();
            
            if ($records->count() > 0) {
                $present = $records->whereIn('status', ['present', 'late', 'half_day'])->count();
                $absent = $records->where('status', 'absent')->count();
                $total = $records->count();
                // Avoid division by zero
                $rate = $total > 0 ? round(($present / $total) * 100, 1) : 0;
                
                $attendanceRates[] = [
                    'employee' => $employee,
                    'employee_name' => $employee->full_name,
                    'department' => $employee->department->name ?? 'N/A',
                    'rate' => $rate,
                    'present_days' => $present,
                    'absent_days' => $absent,
                ];
            }
        }
        
        usort($attendanceRates, function($a, $b) {
            return $b['rate'] <=> $a['rate'];
        });
        
        return array_slice($attendanceRates, 0, 5);
    }

    /**
     * Calculate attendance trend data for chart
     */
    private function calculateAttendanceTrend($dateFrom, $dateTo, $reportType, $departmentId, $currentCompany)
    {
        $startDate = Carbon::parse($dateFrom);
        $endDate = Carbon::parse($dateTo);
        $daysDiff = $startDate->diffInDays($endDate);
        
        $trendData = [];
        $labels = [];
        
        // Determine grouping based on report type and date range
        if ($reportType === 'yearly' || $daysDiff > 90) {
            // Group by month
            $current = $startDate->copy()->startOfMonth();
            while ($current <= $endDate) {
                $monthStart = $current->copy()->startOfMonth();
                $monthEnd = min($current->copy()->endOfMonth(), $endDate);
                
                $employeesQuery = Employee::whereHas('account', function($q) {
                    $q->where('is_active', true);
                });
                if ($currentCompany) $employeesQuery->where('company_id', $currentCompany->id);
                if ($departmentId) $employeesQuery->where('department_id', $departmentId);
                
                $employees = $employeesQuery->get();
                $totalEmployees = $employees->count();
                
                if ($totalEmployees > 0) {
                    $records = AttendanceRecord::whereBetween('date', [$monthStart, $monthEnd])
                        ->whereHas('employee.account', function($q) { $q->where('is_active', true); });
                    if ($currentCompany) {
                        $records->whereHas('employee', function($q) use ($currentCompany) {
                            $q->where('company_id', $currentCompany->id);
                        });
                    }
                    if ($departmentId) {
                        $records->whereHas('employee', function($q) use ($departmentId) {
                            $q->where('department_id', $departmentId);
                        });
                    }
                    
                    $monthRecords = $records->get();
                    $present = $monthRecords->whereIn('status', ['present', 'late', 'half_day'])->count();
                    $totalDays = $monthStart->diffInDays($monthEnd) + 1;
                    $expectedTotal = $totalEmployees * $totalDays;
                    $attendanceRate = $expectedTotal > 0 ? round(($present / $expectedTotal) * 100, 1) : 0;
                    
                    $labels[] = $current->format('M Y');
                    $trendData[] = $attendanceRate;
                } else {
                    $labels[] = $current->format('M Y');
                    $trendData[] = 0;
                }
                $current->addMonth();
            }
        } elseif ($reportType === 'monthly' || $daysDiff > 30) {
            // Group by week
            $current = $startDate->copy()->startOfWeek();
            while ($current <= $endDate) {
                $weekStart = $current->copy();
                $weekEnd = min($current->copy()->endOfWeek(), $endDate);
                
                $employeesQuery = Employee::whereHas('account', function($q) {
                    $q->where('is_active', true);
                });
                if ($currentCompany) $employeesQuery->where('company_id', $currentCompany->id);
                if ($departmentId) $employeesQuery->where('department_id', $departmentId);
                
                $employees = $employeesQuery->get();
                $totalEmployees = $employees->count();
                
                if ($totalEmployees > 0) {
                    $records = AttendanceRecord::whereBetween('date', [$weekStart, $weekEnd])
                        ->whereHas('employee.account', function($q) { $q->where('is_active', true); });
                    if ($currentCompany) {
                        $records->whereHas('employee', function($q) use ($currentCompany) {
                            $q->where('company_id', $currentCompany->id);
                        });
                    }
                    if ($departmentId) {
                        $records->whereHas('employee', function($q) use ($departmentId) {
                            $q->where('department_id', $departmentId);
                        });
                    }
                    
                    $weekRecords = $records->get();
                    $present = $weekRecords->whereIn('status', ['present', 'late', 'half_day'])->count();
                    $totalDays = $weekStart->diffInDays($weekEnd) + 1;
                    $expectedTotal = $totalEmployees * $totalDays;
                    $attendanceRate = $expectedTotal > 0 ? round(($present / $expectedTotal) * 100, 1) : 0;
                    
                    $labels[] = $weekStart->format('M d') . ' - ' . $weekEnd->format('M d');
                    $trendData[] = $attendanceRate;
                } else {
                    $labels[] = $weekStart->format('M d') . ' - ' . $weekEnd->format('M d');
                    $trendData[] = 0;
                }
                $current->addWeek();
            }
        } else {
            // Group by day
            $current = $startDate->copy();
            while ($current <= $endDate) {
                $employeesQuery = Employee::whereHas('account', function($q) {
                    $q->where('is_active', true);
                });
                if ($currentCompany) $employeesQuery->where('company_id', $currentCompany->id);
                if ($departmentId) $employeesQuery->where('department_id', $departmentId);
                
                $employees = $employeesQuery->get();
                $totalEmployees = $employees->count();
                
                if ($totalEmployees > 0) {
                    $records = AttendanceRecord::where('date', $current->format('Y-m-d'))
                        ->whereHas('employee.account', function($q) { $q->where('is_active', true); });
                    if ($currentCompany) {
                        $records->whereHas('employee', function($q) use ($currentCompany) {
                            $q->where('company_id', $currentCompany->id);
                        });
                    }
                    if ($departmentId) {
                        $records->whereHas('employee', function($q) use ($departmentId) {
                            $q->where('department_id', $departmentId);
                        });
                    }
                    
                    $dayRecords = $records->get();
                    $present = $dayRecords->whereIn('status', ['present', 'late', 'half_day'])->count();
                    $attendanceRate = $totalEmployees > 0 ? round(($present / $totalEmployees) * 100, 1) : 0;
                    
                    $labels[] = $current->format('M d');
                    $trendData[] = $attendanceRate;
                } else {
                    $labels[] = $current->format('M d');
                    $trendData[] = 0;
                }
                $current->addDay();
            }
        }
        
        return ['labels' => $labels, 'data' => $trendData];
    }

    /**
     * Get employees needing attention
     */
    private function getNeedsAttention($dateFrom, $dateTo, $departmentId, $currentCompany)
    {
        // Calculate number of days in the range
        $startDate = Carbon::parse($dateFrom);
        $endDate = Carbon::parse($dateTo);
        $totalDays = $startDate->diffInDays($endDate) + 1;
        
        $query = Employee::with(['department', 'account'])
            ->whereHas('account', function($q) {
                $q->where('is_active', true);
            });
        
        if ($currentCompany) {
            $query->where('company_id', $currentCompany->id);
        }
        
        if ($departmentId) {
            $query->where('department_id', $departmentId);
        }
        
        $employees = $query->get();
        $attendanceRates = [];
        
        foreach ($employees as $employee) {
            $records = AttendanceRecord::where('employee_id', $employee->id)
                ->whereBetween('date', [$dateFrom, $dateTo])
                ->orderBy('date', 'desc')
                ->get();
            
            // Calculate expected days for this employee
            $expectedDays = $totalDays;
            
            // Count actual records
            $present = $records->whereIn('status', ['present', 'late', 'half_day'])->count();
            $absentRecords = $records->where('status', 'absent')->count();
            $late = $records->where('status', 'late')->count();
            $recordedDays = $records->count();
            
            // Missing records (no attendance record) should be counted as absent
            $missingDays = max(0, $expectedDays - $recordedDays);
            $absent = $absentRecords + $missingDays;
            
            // Calculate attendance rate based on expected days (not just recorded days)
            $rate = $expectedDays > 0 ? round(($present / $expectedDays) * 100, 1) : 0;
            
            // Calculate consecutive absences (checking from most recent date backwards)
            $consecutiveAbsences = 0;
            $currentDate = Carbon::parse($dateTo);
            
            // Check consecutive absences from the end date backwards
            for ($i = 0; $i < min(30, $totalDays); $i++) {
                $checkDate = $currentDate->copy()->subDays($i)->format('Y-m-d');
                $record = $records->firstWhere('date', $checkDate);
                
                if ($record && $record->status === 'absent') {
                    $consecutiveAbsences++;
                } elseif ($record && in_array($record->status, ['present', 'late', 'half_day'])) {
                    // Found a present day, stop counting
                    break;
                } elseif (!$record) {
                    // No record means absent (missing day)
                    $consecutiveAbsences++;
                } else {
                    // Some other status, stop counting
                    break;
                }
            }
            
            // Get last attendance date
            $lastAttendance = $records->whereIn('status', ['present', 'late', 'half_day'])
                ->sortByDesc('date')
                ->first();
            $lastAttendanceDate = $lastAttendance ? $lastAttendance->date : null;
            
            // Include employees with rate < 90% OR employees with no records at all (100% absent)
            if ($rate < 90 || $recordedDays === 0) {
                $attendanceRates[] = [
                    'employee' => $employee,
                    'rate' => $rate,
                    'absent_days' => $absent,
                    'late_arrivals' => $late,
                    'consecutive_absences' => $consecutiveAbsences,
                    'last_attendance_date' => $lastAttendanceDate,
                    'total_records' => $recordedDays,
                    'expected_days' => $expectedDays,
                ];
            }
        }
        
        usort($attendanceRates, function($a, $b) {
            return $a['rate'] <=> $b['rate'];
        });
        
        // Return all employees needing attention (not limited to 5)
        return $attendanceRates;
    }

    /**
     * Calculate overtime report data
     */
    private function calculateOvertimeReport($dateFrom, $dateTo, $departmentId, $currentCompany)
    {
        $query = \App\Models\OvertimeRequest::with(['employee.department', 'approver'])
            ->where('status', 'approved')
            ->whereBetween('date', [$dateFrom, $dateTo]);
        
        if ($currentCompany) {
            $query->whereHas('employee', function($q) use ($currentCompany) {
                $q->where('company_id', $currentCompany->id);
            });
        }
        
        if ($departmentId) {
            $query->whereHas('employee', function($q) use ($departmentId) {
                $q->where('department_id', $departmentId);
            });
        }
        
        $overtimeRequests = $query->orderBy('date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Calculate summary
        $totalRequests = $overtimeRequests->count();
        $totalHours = $overtimeRequests->sum('hours');
        $totalEmployees = $overtimeRequests->pluck('employee_id')->unique()->count();
        $averageHours = $totalRequests > 0 ? round($totalHours / $totalRequests, 2) : 0;
        
        // Group by employee
        $employeeOvertime = [];
        foreach ($overtimeRequests as $request) {
            $empId = $request->employee_id;
            if (!isset($employeeOvertime[$empId])) {
                $employeeOvertime[$empId] = [
                    'employee' => $request->employee,
                    'total_hours' => 0,
                    'total_requests' => 0,
                    'requests' => [],
                ];
            }
            $employeeOvertime[$empId]['total_hours'] += $request->hours;
            $employeeOvertime[$empId]['total_requests']++;
            $employeeOvertime[$empId]['requests'][] = $request;
        }
        
        // Sort by total hours (descending)
        usort($employeeOvertime, function($a, $b) {
            return $b['total_hours'] <=> $a['total_hours'];
        });
        
        // Department-wise breakdown
        $departmentBreakdown = [];
        foreach ($overtimeRequests->groupBy(function($request) {
            return $request->employee->department_id ?? 'no-department';
        }) as $deptId => $requests) {
            $dept = $deptId !== 'no-department' ? \App\Models\Department::find($deptId) : null;
            $departmentBreakdown[] = [
                'department' => $dept ? $dept->name : 'No Department',
                'total_requests' => $requests->count(),
                'total_hours' => $requests->sum('hours'),
                'total_employees' => $requests->pluck('employee_id')->unique()->count(),
            ];
        }
        
        return [
            'summary' => [
                'total_requests' => $totalRequests,
                'total_hours' => round($totalHours, 2),
                'total_employees' => $totalEmployees,
                'average_hours' => $averageHours,
            ],
            'employee_overtime' => array_values($employeeOvertime),
            'department_breakdown' => $departmentBreakdown,
            'all_requests' => $overtimeRequests,
        ];
    }

    /**
     * Calculate leave report data
     */
    private function calculateLeaveReport($dateFrom, $dateTo, $departmentId, $currentCompany)
    {
        $query = \App\Models\LeaveRequest::with(['employee.department', 'approvedBy'])
            ->where('status', 'approved')
            ->where(function($q) use ($dateFrom, $dateTo) {
                // Leave requests that overlap with the date range
                $q->where(function($subQ) use ($dateFrom, $dateTo) {
                    // Start date is within range
                    $subQ->whereBetween('start_date', [$dateFrom, $dateTo])
                        // Or end date is within range
                        ->orWhereBetween('end_date', [$dateFrom, $dateTo])
                        // Or leave spans the entire range
                        ->orWhere(function($spanQ) use ($dateFrom, $dateTo) {
                            $spanQ->where('start_date', '<=', $dateFrom)
                                  ->where('end_date', '>=', $dateTo);
                        });
                });
            });
        
        if ($currentCompany) {
            $query->whereHas('employee', function($q) use ($currentCompany) {
                $q->where('company_id', $currentCompany->id);
            });
        }
        
        if ($departmentId) {
            $query->whereHas('employee', function($q) use ($departmentId) {
                $q->where('department_id', $departmentId);
            });
        }
        
        $leaveRequests = $query->orderBy('start_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Calculate summary
        $totalRequests = $leaveRequests->count();
        $totalDays = $leaveRequests->sum('days_requested');
        $totalEmployees = $leaveRequests->pluck('employee_id')->unique()->count();
        $averageDays = $totalRequests > 0 ? round($totalDays / $totalRequests, 2) : 0;
        
        // Group by employee
        $employeeLeave = [];
        foreach ($leaveRequests as $request) {
            $empId = $request->employee_id;
            if (!isset($employeeLeave[$empId])) {
                $employeeLeave[$empId] = [
                    'employee' => $request->employee,
                    'total_days' => 0,
                    'total_requests' => 0,
                    'leave_types' => [],
                    'requests' => [],
                ];
            }
            $employeeLeave[$empId]['total_days'] += $request->days_requested;
            $employeeLeave[$empId]['total_requests']++;
            
            // Track leave types
            $leaveType = ucfirst(str_replace('_', ' ', $request->leave_type));
            if (!isset($employeeLeave[$empId]['leave_types'][$request->leave_type])) {
                $employeeLeave[$empId]['leave_types'][$request->leave_type] = [
                    'name' => $leaveType,
                    'days' => 0,
                    'count' => 0,
                ];
            }
            $employeeLeave[$empId]['leave_types'][$request->leave_type]['days'] += $request->days_requested;
            $employeeLeave[$empId]['leave_types'][$request->leave_type]['count']++;
            
            $employeeLeave[$empId]['requests'][] = $request;
        }
        
        // Sort by total days (descending)
        usort($employeeLeave, function($a, $b) {
            return $b['total_days'] <=> $a['total_days'];
        });
        
        // Department-wise breakdown
        $departmentBreakdown = [];
        foreach ($leaveRequests->groupBy(function($request) {
            return $request->employee->department_id ?? 'no-department';
        }) as $deptId => $requests) {
            $dept = $deptId !== 'no-department' ? \App\Models\Department::find($deptId) : null;
            $departmentBreakdown[] = [
                'department' => $dept ? $dept->name : 'No Department',
                'total_requests' => $requests->count(),
                'total_days' => $requests->sum('days_requested'),
                'total_employees' => $requests->pluck('employee_id')->unique()->count(),
            ];
        }
        
        // Leave type breakdown
        $leaveTypeBreakdown = [];
        foreach ($leaveRequests->groupBy('leave_type') as $leaveType => $requests) {
            $leaveTypeBreakdown[] = [
                'leave_type' => ucfirst(str_replace('_', ' ', $leaveType)),
                'total_requests' => $requests->count(),
                'total_days' => $requests->sum('days_requested'),
                'total_employees' => $requests->pluck('employee_id')->unique()->count(),
            ];
        }
        
        return [
            'summary' => [
                'total_requests' => $totalRequests,
                'total_days' => $totalDays,
                'total_employees' => $totalEmployees,
                'average_days' => $averageDays,
            ],
            'employee_leave' => array_values($employeeLeave),
            'department_breakdown' => $departmentBreakdown,
            'leave_type_breakdown' => $leaveTypeBreakdown,
            'all_requests' => $leaveRequests,
        ];
    }
}

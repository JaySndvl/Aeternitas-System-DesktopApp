<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Payroll;
use App\Models\Employee;
use App\Models\Department;
use App\Models\Period;
use App\Models\AttendanceRecord;
use App\Models\EmployeeSchedule;
use App\Services\PayrollGenerationService;
use App\Helpers\CompanyHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;

class PayrollController extends Controller
{
    protected $payrollService;

    public function __construct(PayrollGenerationService $payrollService)
    {
        $this->payrollService = $payrollService;
    }

public function index(Request $request)
{
    $currentCompany = CompanyHelper::getCurrentCompany();
    $user = Auth::user();
    
    // SIMPLIFIED QUERY: Get latest payrolls per employee
    $query = Payroll::query();
    
    // Get the latest payroll per employee
    $latestPayrollSubquery = DB::table('payrolls as p2')
        ->select(DB::raw('MAX(p2.id) as latest_id'))
        ->groupBy('p2.employee_id');
    
    $query->whereIn('id', $latestPayrollSubquery);
    
    if (!$request->filled('start_date') || !$request->filled('end_date')) {
        $request->merge([
            'start_date' => now()->startOfMonth()->format('Y-m-d'),
            'end_date' => now()->endOfMonth()->format('Y-m-d')
        ]);
    }

    // Filter by company
    if ($currentCompany) {
        $query->whereHas('employee', function($q) use ($currentCompany) {
            $q->where('company_id', $currentCompany->id);
        });
    }
    
    // Restrict to employee's own payrolls if user is an employee
    if ($user && $user->role === 'employee' && $user->employee) {
        $query->where('employee_id', $user->employee->id);
    }
    
    // FLEXIBLE Date filtering
if ($request->filled('start_date') && $request->filled('end_date')) {
    // Convert to Carbon for proper comparison
    $startDate = Carbon::parse($request->start_date)->startOfDay();
    $endDate = Carbon::parse($request->end_date)->endOfDay();
    
    // Log for debugging
    Log::info('Payroll filter - Searching for period:', [
        'selected_start' => $startDate->format('Y-m-d'),
        'selected_end' => $endDate->format('Y-m-d'),
        'search_criteria' => 'Looking for payrolls that overlap with selected period'
    ]);
    
    // BROADER SEARCH: Find any payroll that overlaps with selected period
    $query->where(function($q) use ($startDate, $endDate) {
        // Payroll period starts within selected range
        $q->whereBetween('latest_payrolls.pay_period_start', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
        // OR payroll period ends within selected range
        ->orWhereBetween('latest_payrolls.pay_period_end', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
        // OR selected range falls completely within a payroll period
        ->orWhere(function($subQ) use ($startDate, $endDate) {
            $subQ->where('latest_payrolls.pay_period_start', '<=', $startDate->format('Y-m-d'))
                 ->where('latest_payrolls.pay_period_end', '>=', $endDate->format('Y-m-d'));
        });
    });
}
    
    // Status filtering
    if ($request->has('status') && $request->status != 'all') {
        $query->where('status', $request->status);
    }
    
    // Load relationships
    $query->with(['employee', 'employee.department']);
    
    // Use window function to get the latest payroll per employee per period
    $latestPayrollsSubquery = DB::table('payrolls as p1')
        ->select(
            'p1.id',
            'p1.employee_id',
            'p1.pay_period_start',
            'p1.pay_period_end',
            'p1.status',
            'p1.basic_salary',
            'p1.overtime_pay',
            'p1.allowances',
            'p1.deductions',
            'p1.net_pay',
            'p1.gross_pay',
            'p1.created_at',
            DB::raw('ROW_NUMBER() OVER (PARTITION BY p1.employee_id, p1.pay_period_start, p1.pay_period_end ORDER BY p1.created_at DESC) as rn')
        );
    
    // Start with the subquery directly
    $query = DB::table(DB::raw("({$latestPayrollsSubquery->toSql()}) as latest_payrolls"))
        ->mergeBindings($latestPayrollsSubquery)
        ->where('latest_payrolls.rn', 1)
        ->select('latest_payrolls.*');
    
    // Add employee join
    $query->join('employees', 'latest_payrolls.employee_id', '=', 'employees.id');
    
    // Filter by company
    if ($currentCompany) {
        $query->where('employees.company_id', $currentCompany->id);
    }
    
    // Restrict to employee's own payrolls if user is an employee
    if ($user && $user->role === 'employee' && $user->employee) {
        $query->where('latest_payrolls.employee_id', $user->employee->id);
    }

    // Date filtering - FIXED: Use table alias
    if ($request->filled('start_date') && $request->filled('end_date')) {
        $query->where(function($q) use ($request) {
            // Match exact period
            $q->where('latest_payrolls.pay_period_start', $request->start_date)
            ->where('latest_payrolls.pay_period_end', $request->end_date);
            
            // OR match if period falls within selected range
            $q->orWhere(function($subQ) use ($request) {
                $subQ->where('latest_payrolls.pay_period_start', '>=', $request->start_date)
                    ->where('latest_payrolls.pay_period_end', '<=', $request->end_date);
            });
            
            // OR match if selected range falls within period
            $q->orWhere(function($subQ) use ($request) {
                $subQ->where('latest_payrolls.pay_period_start', '<=', $request->start_date)
                    ->where('latest_payrolls.pay_period_end', '>=', $request->end_date);
            });
        });
    }

    // Status filtering - FIXED: Use table alias
    if ($request->has('status') && $request->status != 'all') {
        $query->where('latest_payrolls.status', $request->status);
    }

    // Department filtering (only for admin/hr/manager)
    if ($user && !in_array($user->role, ['employee']) && $request->has('department_id') && $request->department_id != 'all') {
        // Add department join first
        $query->leftJoin('departments', 'employees.department_id', '=', 'departments.id');
        $query->where('departments.id', $request->department_id);
    } else {
        // Always join departments for consistent data
        $query->leftJoin('departments', 'employees.department_id', '=', 'departments.id');
    }

    // Employee filtering - FIXED: Use table alias (only for admin/hr/manager)
    if ($user && !in_array($user->role, ['employee']) && $request->has('employee_id') && $request->employee_id != 'all') {
        $query->where('latest_payrolls.employee_id', $request->employee_id);
    }

    // Month filtering - FIXED: Use table alias
    if ($request->has('month')) {
        $query->whereMonth('latest_payrolls.pay_period_start', $request->month);
    }

    // Year filtering - FIXED: Use table alias
    if ($request->has('year')) {
        $query->whereYear('latest_payrolls.pay_period_start', $request->year);
    }

    // Add select for employee and department data
    $query->addSelect([
        'employees.first_name',
        'employees.last_name',
        'employees.employee_id as employee_code',
        'departments.name as department_name'
    ]);
    
    // Sorting - FIXED
    $sortBy = $request->get('sort', 'latest_payrolls.created_at');
    $sortOrder = $request->get('order', 'desc');
    
    $sortMapping = [
        'name_asc' => ['employees.first_name', 'asc'],
        'name_desc' => ['employees.first_name', 'desc'],
        'net_pay_high_low' => ['latest_payrolls.net_pay', 'desc'],
        'net_pay_low_high' => ['latest_payrolls.net_pay', 'asc'],
        'date' => ['latest_payrolls.pay_period_start', 'desc'],
    ];
    
    if (isset($sortMapping[$sortBy])) {
        list($sortColumn, $sortOrder) = $sortMapping[$sortBy];
        $query->orderBy($sortColumn, $sortOrder);
    } else {
        $query->orderBy('latest_payrolls.pay_period_start', 'desc')
              ->orderBy('employees.first_name', 'asc');
    }
    
    // Paginate the results
    $payrolls = $query->paginate(15);
    
    // Transform results to match Payroll model format
    $payrolls->getCollection()->transform(function ($item) {
        $payroll = new \App\Models\Payroll([
            'id' => $item->id,
            'employee_id' => $item->employee_id,
            'pay_period_start' => $item->pay_period_start,
            'pay_period_end' => $item->pay_period_end,
            'status' => $item->status,
            'basic_salary' => $item->basic_salary,
            'overtime_pay' => $item->overtime_pay,
            'allowances' => $item->allowances,
            'deductions' => $item->deductions,
            'net_pay' => $item->net_pay,
            'gross_pay' => $item->gross_pay,
            'created_at' => $item->created_at,
        ]);
        
        // Manually set employee relationship
        $payroll->setRelation('employee', (object) [
            'id' => $item->employee_id,
            'first_name' => $item->first_name,
            'last_name' => $item->last_name,
            'full_name' => $item->first_name . ' ' . $item->last_name,
            'employee_id' => $item->employee_code,
            'department' => (object) [
                'name' => $item->department_name
            ]
        ]);
        
        return $payroll;
    });
    
    // Get employees for filters (restricted for employees)
    $employeesQuery = Employee::query();
    if ($currentCompany) {
        $employeesQuery->forCompany($currentCompany->id);
    }
    // Restrict to employee's own record if user is an employee
    if ($user && $user->role === 'employee' && $user->employee) {
        $employeesQuery->where('id', $user->employee->id);
    }
    $employees = $employeesQuery->get();
    
    // Get departments for the filter
    $departments = Department::all();

    // Calculate summary statistics - get ALL payrolls for the period (using same logic)
    $summaryQuery = Payroll::query();
    if ($currentCompany) {
        $summaryQuery->whereHas('employee', function($q) use ($currentCompany) {
            $q->where('company_id', $currentCompany->id);
        });
    }
    
    if ($request->filled('start_date') && $request->filled('end_date')) {
        $summaryQuery->where('pay_period_start', $request->start_date)
                     ->where('pay_period_end', $request->end_date);
    }
    
    $allPayrolls = $summaryQuery->get();
    $summary = [
        'total_employees' => $employeesQuery->count(),
        'gross_pay' => $allPayrolls->sum('gross_pay'),
        'total_deductions' => $allPayrolls->sum('deductions'),
        'net_pay' => $allPayrolls->sum('net_pay'),
        'pending_count' => $allPayrolls->where('status', 'pending')->count(),
        'approved_count' => $allPayrolls->where('status', 'approved')->count(),
        'paid_count' => $allPayrolls->where('status', 'paid')->count(),
    ];

    return view('payroll.index', compact('payrolls', 'employees', 'summary', 'departments'));
}

public function checkDuplicatePayroll(Request $request)
{
    $request->validate([
        'employee_id' => 'required|exists:employees,id',
        'start_date' => 'required|date',
        'end_date' => 'required|date'
    ]);
    
    $exists = Payroll::where('employee_id', $request->employee_id)
        ->where('pay_period_start', $request->start_date)
        ->where('pay_period_end', $request->end_date)
        ->exists();
    
    return response()->json([
        'exists' => $exists,
        'message' => $exists ? 'Payroll already exists for this period' : 'No duplicate found'
    ]);
}

    public function create()
    {
        $currentCompany = CompanyHelper::getCurrentCompany();
        
        $employeesQuery = Employee::query();
        if ($currentCompany) {
            $employeesQuery->forCompany($currentCompany->id);
        }
        $employees = $employeesQuery->get();
        
        return view('payroll.create', compact('employees'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'pay_period_start' => 'required|date',
            'pay_period_end' => 'required|date|after:pay_period_start',
            'basic_salary' => 'required|numeric|min:0',
            'overtime_hours' => 'nullable|numeric|min:0',
            'overtime_rate' => 'nullable|numeric|min:0',
            'bonuses' => 'nullable|numeric|min:0',
            'deductions' => 'nullable|numeric|min:0',
            'tax_amount' => 'nullable|numeric|min:0',
        ]);

        Payroll::create($request->validated());

        return redirect()->route('payrolls.index')
            ->with('success', 'Payroll created successfully.');
    }

    public function show(Payroll $payroll)
    {
        $payroll->load('employee.department');
        return view('payroll.show', compact('payroll'));
    }

    public function edit(Payroll $payroll)
    {
        $currentCompany = CompanyHelper::getCurrentCompany();
        
        $employeesQuery = Employee::query();
        if ($currentCompany) {
            $employeesQuery->forCompany($currentCompany->id);
        }
        $employees = $employeesQuery->get();
        
        return view('payroll.edit', compact('payroll', 'employees'));
    }

    public function update(Request $request, Payroll $payroll)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'pay_period_start' => 'required|date',
            'pay_period_end' => 'required|date|after:pay_period_start',
            'basic_salary' => 'required|numeric|min:0',
            'overtime_hours' => 'nullable|numeric|min:0',
            'overtime_rate' => 'nullable|numeric|min:0',
            'bonuses' => 'nullable|numeric|min:0',
            'deductions' => 'nullable|numeric|min:0',
            'tax_amount' => 'nullable|numeric|min:0',
        ]);

        $payroll->update($request->validated());

        return redirect()->route('payrolls.index')
            ->with('success', 'Payroll updated successfully.');
    }

    public function destroy(Payroll $payroll)
    {
        $payroll->delete();

        return redirect()->route('payrolls.index')
            ->with('success', 'Payroll deleted successfully.');
    }

    public function process(Payroll $payroll)
    {
        try {
            // Calculate net pay
            $grossPay = $payroll->basic_salary + 
                       ($payroll->overtime_hours * $payroll->overtime_rate) + 
                       $payroll->bonuses;

            $netPay = $grossPay - $payroll->deductions - $payroll->tax_amount;

            // Approve payroll and update calculations
            $payroll->update([
                'gross_pay' => $grossPay,
                'net_pay' => $netPay,
                'status' => 'approved',
                'approved_at' => now(),
                'approved_by' => auth()->id(),
                'processed_at' => now(),
            ]);

            // Generate payslip
            $filename = $this->payrollService->generatePayslip($payroll);
            
            return redirect()->route('payrolls.show', $payroll)
                ->with('success', 'Payroll processed successfully! Payslip generated.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error processing payroll: ' . $e->getMessage());
        }
    }

    public function summary(Request $request)
    {
        $user = Auth::user();
        
        // Use the more comprehensive summary method that supports date filtering
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->endOfMonth()->format('Y-m-d'));
        
        $summary = $this->payrollService->getPayrollSummary(
            Carbon::parse($startDate),
            Carbon::parse($endDate)
        );

        // Also get monthly data for charts
        $monthly_data = DB::table('payrolls')
            ->select([
                DB::raw('YEAR(pay_period_start) as year'),
                DB::raw('MONTH(pay_period_start) as month'),
                DB::raw('SUM(gross_pay) as gross_pay'),
                DB::raw('SUM(gross_pay - net_pay) as deductions'),
                DB::raw('SUM(net_pay) as net_pay'),
                DB::raw('COUNT(*) as count'),
            ])
            ->where('status', 'approved')
            ->groupBy('year', 'month')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->limit(12)
            ->get();

        return view('payroll.summary', compact('user', 'summary', 'monthly_data', 'startDate', 'endDate'));
    }

    public function monthlyReport(Request $request)
    {
        $user = Auth::user();
        
        $request->validate([
            'year' => 'required|integer|min:2020|max:2030',
            'month' => 'required|integer|min:1|max:12',
        ]);

        $year = $request->get('year', now()->year);
        $month = $request->get('month', now()->month);

        $payrolls = Payroll::whereYear('pay_period_start', $year)
            ->whereMonth('pay_period_start', $month)
            ->with('employee.department')
            ->get();

        // Calculate report summary
        $report = [
            'total_employees' => $payrolls->count(),
            'total_gross' => $payrolls->sum('gross_pay'),
            'total_net' => $payrolls->sum('net_pay'),
        ];

        return view('payroll.monthly', compact('user', 'payrolls', 'year', 'month', 'report'));
    }


public function processPayments(Request $request)
{
    try {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date'
        ]);

        // Get approved payrolls for the period
        $payrolls = Payroll::where('pay_period_start', $request->start_date)
            ->where('pay_period_end', $request->end_date)
            ->where('status', 'approved')
            ->with('employee')
            ->get();

        // Debug log
        \Illuminate\Support\Facades\Log::info('Processing payments - found payrolls:', [
            'count' => $payrolls->count(),
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'payroll_ids' => $payrolls->pluck('id')->toArray()
        ]);

        if ($payrolls->isEmpty()) {
            // Check if there are pending payrolls that can be auto-approved
            $pendingPayrolls = Payroll::where('pay_period_start', $request->start_date)
                ->where('pay_period_end', $request->end_date)
                ->where('status', 'pending')
                ->count();
                
            if ($pendingPayrolls > 0) {
                return redirect()->back()
                    ->with('info', "No approved payrolls found. Found {$pendingPayrolls} pending payroll(s). Please approve them first.")
                    ->with('start_date', $request->start_date)
                    ->with('end_date', $request->end_date);
            }
            
            return redirect()->back()
                ->with('error', 'No approved or pending payrolls found for this period. Please generate payroll first.')
                ->with('start_date', $request->start_date)
                ->with('end_date', $request->end_date);
        }

        $result = $this->payrollService->processPayments(
            ['start_date' => $request->start_date, 'end_date' => $request->end_date],
            $payrolls->pluck('employee_id')->toArray(),
            auth()->id()
        );

        return redirect()->back()
            ->with('success', 
                'Processed ' . $result['processed'] . ' payments. ' . 
                ($result['failed'] > 0 ? $result['failed'] . ' failed.' : '')
            )
            ->with('start_date', $request->start_date)
            ->with('end_date', $request->end_date);

    } catch (\Exception $e) {
        \Illuminate\Support\Facades\Log::error('Error processing payments: ' . $e->getMessage());
        return redirect()->back()
            ->with('error', 'Error processing payments: ' . $e->getMessage())
            ->with('start_date', $request->start_date)
            ->with('end_date', $request->end_date);
    }
}

    /**
     * Bulk approve pending payrolls
     */
    public function bulkApprove(Request $request)
    {
        try {
            $request->validate([
                'start_date' => 'required|date',
                'end_date' => 'required|date|after:start_date',
            ]);

            $period = $request->only(['start_date', 'end_date']);
            $employeeIds = $request->get('employee_ids', []);

            Log::info('Bulk approving payrolls', [
                'period' => $period,
                'employee_ids' => $employeeIds,
                'approved_by' => auth()->id()
            ]);

            $count = $this->payrollService->approveAllPending($period, $employeeIds, auth()->id());

            Log::info('Bulk approval completed', ['count' => $count]);

            return redirect()->back()
                ->with('success', 'Approved ' . $count . ' payroll record(s)!')
                ->with('start_date', $request->input('start_date'))
                ->with('end_date', $request->input('end_date'));

        } catch (\Exception $e) {
            Log::error('Error approving payrolls: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);
            
            return redirect()->back()
                ->with('error', 'Error approving payrolls: ' . $e->getMessage())
                ->with('start_date', $request->input('start_date'))
                ->with('end_date', $request->input('end_date'));
        }
    }

    /**
     * Get pending payrolls for API
     */
    public function getPendingPayrolls(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date'
        ]);
        
        // Get current company
        $currentCompany = CompanyHelper::getCurrentCompany();
        
        $query = Payroll::with('employee')
            ->where('status', 'pending')
            ->where('pay_period_start', $request->start_date)
            ->where('pay_period_end', $request->end_date);
        
        // Filter by company if applicable
        if ($currentCompany) {
            $query->whereHas('employee', function($q) use ($currentCompany) {
                $q->where('company_id', $currentCompany->id);
            });
        }
        
        $payrolls = $query->get()
            ->map(function ($payroll) {
                return [
                    'id' => $payroll->id,
                    'employee_id' => $payroll->employee_id,
                    'employee_name' => $payroll->employee->full_name ?? 'Unknown',
                    'employee_code' => $payroll->employee->employee_id ?? '',
                    'net_pay' => $payroll->net_pay,
                    'basic_salary' => $payroll->basic_salary,
                ];
            });
        
        return response()->json($payrolls);
    }

    /**
     * Approve all pending payrolls via AJAX
     */
    public function approveAllViaAjax(Request $request)
    {
        try {
            $request->validate([
                'start_date' => 'required|date',
                'end_date' => 'required|date'
            ]);

            // Prepare period data
            $periodData = [
                'start_date' => $request->input('start_date'),
                'end_date' => $request->input('end_date'),
            ];

            // Get current user
            $approvedBy = auth()->user()->id;

            // Call the service method
            $count = $this->payrollService->approveAllPending($periodData, null, $approvedBy);

            return response()->json([
                'success' => true,
                'approved_count' => $count,
                'message' => "Successfully approved {$count} payroll(s)"
            ]);

        } catch (\Exception $e) {
            Log::error('AJAX approval failed: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Approval failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate payroll from period
     */
    public function generatePayroll(Request $request)
    {
        try {
            $period = $request->validate([
                'start_date' => 'required|date',
                'end_date' => 'required|date'
            ]);

            // You'll need to get comprehensive data from somewhere
            // For now, this is a placeholder - you'll need to implement this based on your data source
            $comprehensiveData = []; // Get this from your period management or attendance data
            
            $created = $this->payrollService->generatePayroll($period, $comprehensiveData);

            return redirect()->back()
                ->with('success', 'Generated payroll for ' . count($created) . ' employees!');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error generating payroll: ' . $e->getMessage());
        }
    }

    /**
     * Show form to generate payroll from period management
     */
    public function generateFromPeriod()
    {
        // Get recent periods from database
        $periods = Period::with('department')
            ->orderBy('created_at', 'desc')
            ->get();
        $employees = Employee::with('department')->get();
        $departments = Department::all();

        return view('payroll.generate-from-period', compact('periods', 'employees', 'departments'));
    }

    /**
     * Generate payroll from period management data
     */
    public function generateFromPeriodData(Request $request)
    {
        $request->validate([
            'period_id' => 'required|string',
            'employee_ids' => 'nullable|array',
            'employee_ids.*' => 'exists:employees,id',
        ]);

        try {
            // Get period data from database
            $period = Period::find($request->period_id);

            if (!$period) {
                return redirect()->back()->with('error', 'Period not found.');
            }

            // Convert period to array format for the service
            $periodData = [
                'id' => $period->id,
                'name' => $period->name,
                'start_date' => $period->start_date->format('Y-m-d'),
                'end_date' => $period->end_date->format('Y-m-d'),
                'department_id' => $period->department_id,
                'employee_ids' => $period->employee_ids,
            ];

            // Get comprehensive attendance data for the period
            $startDate = Carbon::parse($period->start_date);
            $endDate = Carbon::parse($period->end_date);
            
            // Get employees for the period
            $employees = Employee::with('department');
            if (!empty($period->department_id)) {
                $employees = $employees->where('department_id', $period->department_id);
            }
            if (!empty($period->employee_ids) && is_array($period->employee_ids)) {
                $employees = $employees->whereIn('id', $period->employee_ids);
            }
            $employees = $employees->get();
            
            // Get comprehensive attendance data
            $comprehensiveData = $this->getComprehensiveAttendanceData($startDate, $endDate, $employees);
            
            // Generate payroll using comprehensive data
            $generatedPayrolls = $this->payrollService->generatePayrollFromComprehensiveData(
                $periodData, 
                $comprehensiveData,
                $request->employee_ids
            );

            if (empty($generatedPayrolls)) {
                return redirect()->back()->with('error', 'No payroll records were generated.');
            }

            return redirect()->route('payroll.index')
                ->with('success', 'Payroll generated successfully for ' . count($generatedPayrolls) . ' employees.');

        } catch (\Exception $e) {
            Log::error('Payroll generation failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to generate payroll: ' . $e->getMessage());
        }
    }

    /**
     * Complete payroll workflow: Generate → Approve → Process Payments
     */
    public function completePayrollWorkflow(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'employee_ids' => 'nullable|array',
            'employee_ids.*' => 'exists:employees,id'
        ]);

        try {
            DB::beginTransaction();
            
            $startDate = Carbon::parse($request->start_date);
            $endDate = Carbon::parse($request->end_date);
            
            // Step 1: Check if payroll already exists
            $existingPayrolls = Payroll::where('pay_period_start', $startDate->format('Y-m-d'))
                ->where('pay_period_end', $endDate->format('Y-m-d'))
                ->when($request->employee_ids, function($q) use ($request) {
                    $q->whereIn('employee_id', $request->employee_ids);
                })
                ->count();
                
            if ($existingPayrolls > 0) {
                return redirect()->back()
                    ->with('warning', "Payroll already exists for {$existingPayrolls} employee(s). Proceeding with approval and payment processing.")
                    ->with('start_date', $request->start_date)
                    ->with('end_date', $request->end_date);
            }
            
            // Step 2: Generate payroll (if needed)
            $currentCompany = CompanyHelper::getCurrentCompany();
            $employeesQuery = Employee::query();
            
            if ($currentCompany) {
                $employeesQuery->forCompany($currentCompany->id);
            }
            
            if ($request->employee_ids) {
                $employeesQuery->whereIn('id', $request->employee_ids);
            }
            
            $employees = $employeesQuery->get();
            
            if ($employees->isEmpty()) {
                throw new \Exception('No employees found for payroll generation.');
            }
            
            // Get comprehensive attendance data
            $comprehensiveData = $this->getComprehensiveAttendanceData($startDate, $endDate, $employees);
            
            if (empty($comprehensiveData)) {
                throw new \Exception('No attendance data found for the selected period.');
            }
            
            $periodData = [
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'company_id' => $currentCompany?->id
            ];
            
            // Generate payroll
            $generatedPayrolls = $this->payrollService->generatePayrollFromComprehensiveData(
                $periodData, 
                $comprehensiveData,
                $request->employee_ids
            );
            
            $generatedCount = count($generatedPayrolls);
            
            // Step 3: Approve all generated payrolls
            $approvedCount = $this->payrollService->approveAllPending(
                $periodData,
                $request->employee_ids,
                auth()->id()
            );
            
            // Step 4: Process payments
            $paymentResult = $this->payrollService->processPayments(
                $periodData,
                $request->employee_ids,
                auth()->id()
            );
            
            DB::commit();
            
            $message = "Payroll workflow completed: ";
            $message .= "Generated: {$generatedCount} payrolls, ";
            $message .= "Approved: {$approvedCount} payrolls, ";
            $message .= "Payments processed: {$paymentResult['processed']}, ";
            $message .= "Failed: {$paymentResult['failed']}";
            
            return redirect()->route('payroll.index')
                ->with('success', $message)
                ->with('start_date', $request->start_date)
                ->with('end_date', $request->end_date);
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Complete payroll workflow failed: ' . $e->getMessage());
            
            return redirect()->back()
                ->with('error', 'Payroll workflow failed: ' . $e->getMessage())
                ->with('start_date', $request->start_date)
                ->with('end_date', $request->end_date);
        }
    }

    /**
     * Get payroll status counts for API
     */
    public function getPayrollStatusCount(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date'
        ]);
        
        // Get current company
        $currentCompany = CompanyHelper::getCurrentCompany();
        
        $query = Payroll::where('pay_period_start', $request->start_date)
            ->where('pay_period_end', $request->end_date);
        
        // Filter by company if applicable
        if ($currentCompany) {
            $query->whereHas('employee', function($q) use ($currentCompany) {
                $q->where('company_id', $currentCompany->id);
            });
        }
        
        $counts = $query->selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();
        
        return response()->json([
            'approved' => $counts['approved'] ?? 0,
            'pending' => $counts['pending'] ?? 0,
            'paid' => $counts['paid'] ?? 0
        ]);
    }

    /**
     * AJAX bulk approve method
     */
    public function ajaxBulkApprove(Request $request)
    {
        try {
            $request->validate([
                'start_date' => 'required|date',
                'end_date' => 'required|date',
                'employee_ids' => 'nullable|array',
                'employee_ids.*' => 'exists:employees,id'
            ]);

            // Prepare period data
            $periodData = [
                'start_date' => $request->input('start_date'),
                'end_date' => $request->input('end_date'),
            ];

            // Get current user
            $approvedBy = auth()->user()->id;

            // Call the service method
            $count = $this->payrollService->approveAllPending(
                $periodData, 
                $request->employee_ids, 
                $approvedBy
            );

            return response()->json([
                'success' => true,
                'approved_count' => $count,
                'message' => "Successfully approved {$count} payroll(s)"
            ]);

        } catch (\Exception $e) {
            Log::error('AJAX bulk approve failed: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Bulk approval failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check if payrolls are already paid
     */
    public function checkPaidStatus(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date'
        ]);
        
        // Get current company
        $currentCompany = CompanyHelper::getCurrentCompany();
        
        $query = Payroll::where('pay_period_start', $request->start_date)
            ->where('pay_period_end', $request->end_date)
            ->whereIn('status', ['paid', 'processed']);
        
        // Filter by company if applicable
        if ($currentCompany) {
            $query->whereHas('employee', function($q) use ($currentCompany) {
                $q->where('company_id', $currentCompany->id);
            });
        }
        
        $count = $query->count();
        
        return response()->json([
            'already_paid' => $count > 0,
            'paid_count' => $count
        ]);
    }
    
    /**
     * Show payroll details for a specific period
     */
    public function showPeriodPayroll(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
        ]);

        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);

        $payrolls = Payroll::where('pay_period_start', $startDate->format('Y-m-d'))
            ->where('pay_period_end', $endDate->format('Y-m-d'))
            ->with('employee.department')
            ->get();

        $summary = $this->payrollService->getPayrollSummary($startDate, $endDate);

        return view('payroll.period-details', compact('payrolls', 'summary', 'startDate', 'endDate'));
    }

    /**
 * Approve a single payroll
 */
public function approvePayroll(Request $request, $payrollId)
{
    try {
        $payroll = Payroll::findOrFail($payrollId);
        $user = Auth::user();
        
        // Authorization check
        if ($user && $user->role === 'employee') {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to approve payroll.'
            ], 403);
        }
        
        // Only pending payrolls can be approved
        if ($payroll->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Only pending payrolls can be approved.'
            ]);
        }
        
        // Update payroll status
        $payroll->update([
            'status' => 'approved',
            'approved_at' => now(),
            'approved_by' => $user->id,
            'processed_at' => now(),
        ]);
        
        // Log the approval
        Log::info('Payroll approved', [
            'payroll_id' => $payrollId,
            'approved_by' => $user->id,
            'approved_at' => now()
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Payroll approved successfully!',
            'payroll_id' => $payrollId,
            'new_status' => 'approved',
            'status_color' => 'bg-green-100 text-green-800'
        ]);
        
    } catch (\Exception $e) {
        Log::error('Error approving payroll: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Error: ' . $e->getMessage()
        ], 500);
    }
}

/**
 * Reject a single payroll - COMPATIBLE VERSION
 */
public function rejectPayroll(Request $request, $payrollId)
{
    try {
        $payroll = Payroll::findOrFail($payrollId);
        $user = Auth::user();
        
        // Authorization check
        if ($user && $user->role === 'employee') {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to reject payroll.'
            ], 403);
        }
        
        // Only pending payrolls can be rejected
        if ($payroll->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Only pending payrolls can be rejected.'
            ]);
        }
        
        // Use 'canceled' instead of 'rejected' to avoid length issues
        // But first, let's check what status values are acceptable
        $status = 'canceled'; // Shorter than 'rejected'
        
        // Simple update using DB facade to avoid Eloquent issues
        DB::table('payrolls')
            ->where('id', $payrollId)
            ->update([
                'status' => $status,
                'rejected_at' => now(),
                'rejected_by' => $user->id ?? null,
                'rejection_reason' => 'Rejected by user',
                'updated_at' => now()
            ]);
        
        Log::info('Payroll rejected successfully', [
            'payroll_id' => $payrollId,
            'status' => $status,
            'user_id' => $user->id ?? null
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Payroll rejected successfully!',
            'payroll_id' => $payrollId,
            'new_status' => $status,
            'status_color' => 'bg-red-100 text-red-800'
        ]);
        
    } catch (\Exception $e) {
        Log::error('Error rejecting payroll: ' . $e->getMessage());
        
        // Last resort: Just update status without other columns
        try {
            DB::table('payrolls')
                ->where('id', $payrollId)
                ->update([
                    'status' => 'cancelled', // Even shorter
                    'updated_at' => now()
                ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Payroll marked as cancelled!',
                'payroll_id' => $payrollId,
                'new_status' => 'cancelled',
                'status_color' => 'bg-red-100 text-red-800'
            ]);
            
        } catch (\Exception $e2) {
            return response()->json([
                'success' => false,
                'message' => 'Final error: ' . $e2->getMessage()
            ], 500);
        }
    }
}


/**
 * Download single payslip PDF 
 */
public function downloadViewPayslip($payrollId)
{
    try {
        $payroll = Payroll::with(['employee', 'employee.department'])->findOrFail($payrollId);
        $user = Auth::user();
        
        // Authorization check
        if ($user && $user->role === 'employee' && $user->employee) {
            if ($payroll->employee_id !== $user->employee->id) {
                return redirect()->back()->with('error', 'You are not authorized to download this payslip.');
            }
        }
        
        // Check if DomPDF is available
        if (!class_exists('\Barryvdh\DomPDF\Facade\Pdf')) {
            // Fallback: Redirect to view
            return redirect()->route('payroll.show', $payrollId)
                ->with('error', 'PDF generation is not available. Please install DomPDF.');
        }
        
        // Get company info
        $company = CompanyHelper::getCurrentCompany() ?? (object)[
            'name' => 'Aeternitas Company',
            'address' => 'Not specified',
            'contact' => 'Not specified'
        ];
        
        // Create HTML content with proper encoding
        $html = $this->generatePayslipHtml($payroll, $company);
        
        // Generate PDF with UTF-8 support
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html);
        $pdf->setPaper('A4', 'portrait');
        
        // CRITICAL: Add these options for UTF-8 support
        $pdf->setOption('defaultFont', 'dejavusans');
        $pdf->setOption('isHtml5ParserEnabled', true);
        $pdf->setOption('isRemoteEnabled', true);
        $pdf->setOption('isPhpEnabled', true);
        $pdf->setOption('chroot', base_path());
        $pdf->setOption('defaultEncoding', 'UTF-8');
        $pdf->setOption('fontHeightRatio', 1.1);
        
        // Create filename
        $employeeName = $payroll->employee ? 
            preg_replace('/[^A-Za-z0-9_\-\.]/', '_', $payroll->employee->full_name) : 
            'Employee_' . $payroll->employee_id;
            
        $filename = 'Payslip_' . $employeeName . '_' . 
                   $payroll->pay_period_start . '_to_' . 
                   $payroll->pay_period_end . '.pdf';
        
        // Stream the PDF directly
        return $pdf->download($filename);
        
    } catch (\Exception $e) {
        Log::error('PDF Generation Error: ' . $e->getMessage());
        return redirect()->back()->with('error', 'Failed to generate PDF: ' . $e->getMessage());
    }
}

/**
 * Generate HTML for payslip - FIXED VERSION with correct status and currency
 */
private function generatePayslipHtml($payroll, $company)
{
    // CRITICAL FIX: Reload the payroll to get fresh data from database
    $payroll = Payroll::with(['employee', 'employee.department'])->find($payroll->id);
    
    if (!$payroll) {
        throw new \Exception('Payroll not found');
    }
    
    $employee = $payroll->employee;
    $today = now()->format('F j, Y');
    
    // Calculate total deductions
    $totalDeductions = $payroll->deductions + $payroll->tax_amount + 
                      ($payroll->sss ?? 0) + ($payroll->phic ?? 0) + ($payroll->hdmf ?? 0);
    
    // Get the ACTUAL status from the payroll record
    $status = $payroll->status;
    
    // Status color mapping
    $statusColors = [
        'pending' => '#e53e3e',     // Red
        'approved' => '#38a169',    // Green
        'paid' => '#3182ce',        // Blue
        'canceled' => '#718096',    // Gray
        'cancelled' => '#718096',   // Gray
        'rejected' => '#e53e3e'     // Red
    ];
    
    $statusColor = $statusColors[$status] ?? '#718096';
    
    // IMPORTANT: Add this for currency symbol support
    $currencySymbol = '₱'; // Unicode for Peso sign
    
    // Start building HTML
    $html = '<!DOCTYPE html>
    <html>
    <head>
        <meta charset="utf-8">
        <title>Payslip - ' . htmlspecialchars($employee->full_name) . '</title>
        <style>
            @font-face {
                font-family: "DejaVu Sans";
                src: url("' . public_path('fonts/dejavu-sans/DejaVuSans.ttf') . '") format("truetype");
            }
            body { font-family: "DejaVu Sans", "Arial Unicode MS", Arial, sans-serif; margin: 0; padding: 20px; color: #333; }
            .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #4a5568; padding-bottom: 20px; }
            .company-name { font-size: 24px; font-weight: bold; color: #2d3748; margin-bottom: 5px; }
            .payslip-title { font-size: 20px; color: #4a5568; margin-bottom: 10px; }
            .employee-info { background-color: #f7fafc; padding: 15px; border-radius: 5px; margin-bottom: 20px; }
            .info-row { margin-bottom: 8px; }
            .info-label { font-weight: bold; display: inline-block; width: 150px; }
            .table { width: 100%; border-collapse: collapse; margin: 20px 0; }
            .table th, .table td { padding: 12px; border: 1px solid #e2e8f0; text-align: left; }
            .table th { background-color: #4a5568; color: white; font-weight: bold; }
            .amount { text-align: right; font-family: "DejaVu Sans", "Courier New", monospace; }
            .total-row { font-weight: bold; background-color: #edf2f7; }
            .net-pay { text-align: center; padding: 25px; border: 3px solid #2d3748; margin: 30px 0; background-color: #f0fff4; }
            .net-pay-amount { font-size: 28px; font-weight: bold; color: #2f855a; margin-top: 10px; }
            .footer { text-align: center; margin-top: 40px; padding-top: 20px; border-top: 1px solid #e2e8f0; font-size: 12px; color: #718096; }
            .currency { font-family: "DejaVu Sans", "Courier New", monospace; }
            .status-badge { 
                display: inline-block; 
                padding: 4px 12px; 
                border-radius: 4px; 
                font-weight: bold; 
                font-size: 12px;
                color: white;
            }
        </style>
    </head>
    <body>
        <div class="header">
            <div class="company-name">' . htmlspecialchars($company->name) . '</div>
            <div class="payslip-title">EMPLOYEE PAYSLIP</div>
            <div style="color: #718096;">
                Period: ' . $payroll->pay_period_start . ' to ' . $payroll->pay_period_end . '
            </div>
            <div style="color: #718096; font-size: 14px;">Generated: ' . $today . '</div>
        </div>
        
        <div class="employee-info">
            <div class="info-row">
                <span class="info-label">Employee Name:</span>
                <span>' . htmlspecialchars($employee->full_name) . '</span>
            </div>
            <div class="info-row">
                <span class="info-label">Employee ID:</span>
                <span>' . htmlspecialchars($employee->employee_id) . '</span>
            </div>
            <div class="info-row">
                <span class="info-label">Department:</span>
                <span>' . htmlspecialchars($employee->department->name ?? 'N/A') . '</span>
            </div>
            <div class="info-row">
                <span class="info-label">Payroll Status:</span>
                <span class="status-badge" style="background-color: ' . $statusColor . ';">' . strtoupper($status) . '</span>
            </div>
        </div>
        
        <table class="table">
            <thead>
                <tr>
                    <th>EARNINGS</th>
                    <th class="amount">AMOUNT</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Basic Salary</td>
                    <td class="amount currency">' . $currencySymbol . number_format($payroll->basic_salary, 2) . '</td>
                </tr>';
    
    // Add optional earnings
    if ($payroll->overtime_pay > 0) {
        $html .= '<tr><td>Overtime Pay</td><td class="amount currency">' . $currencySymbol . number_format($payroll->overtime_pay, 2) . '</td></tr>';
    }
    if ($payroll->allowances > 0) {
        $html .= '<tr><td>Allowances</td><td class="amount currency">' . $currencySymbol . number_format($payroll->allowances, 2) . '</td></tr>';
    }
    if ($payroll->bonuses > 0) {
        $html .= '<tr><td>Bonuses</td><td class="amount currency">' . $currencySymbol . number_format($payroll->bonuses, 2) . '</td></tr>';
    }
    
    $html .= '<tr class="total-row">
                    <td><strong>TOTAL EARNINGS</strong></td>
                    <td class="amount currency"><strong>' . $currencySymbol . number_format($payroll->gross_pay, 2) . '</strong></td>
                </tr>
            </tbody>
        </table>
        
        <table class="table">
            <thead>
                <tr>
                    <th>DEDUCTIONS</th>
                    <th class="amount">AMOUNT</th>
                </tr>
            </thead>
            <tbody>';
    
    // Add deductions
    if ($payroll->deductions > 0) {
        $html .= '<tr><td>Deductions</td><td class="amount currency">' . $currencySymbol . number_format($payroll->deductions, 2) . '</td></tr>';
    }
    if ($payroll->tax_amount > 0) {
        $html .= '<tr><td>Tax Withholding</td><td class="amount currency">' . $currencySymbol . number_format($payroll->tax_amount, 2) . '</td></tr>';
    }
    if ($payroll->sss > 0) {
        $html .= '<tr><td>SSS Contribution</td><td class="amount currency">' . $currencySymbol . number_format($payroll->sss, 2) . '</td></tr>';
    }
    if ($payroll->phic > 0) {
        $html .= '<tr><td>PhilHealth</td><td class="amount currency">' . $currencySymbol . number_format($payroll->phic, 2) . '</td></tr>';
    }
    if ($payroll->hdmf > 0) {
        $html .= '<tr><td>Pag-IBIG</td><td class="amount currency">' . $currencySymbol . number_format($payroll->hdmf, 2) . '</td></tr>';
    }
    
    $html .= '<tr class="total-row">
                    <td><strong>TOTAL DEDUCTIONS</strong></td>
                    <td class="amount currency"><strong>' . $currencySymbol . number_format($totalDeductions, 2) . '</strong></td>
                </tr>
            </tbody>
        </table>
        
        <div class="net-pay">
            <div style="font-size: 18px; font-weight: bold; color: #2d3748;">NET PAY</div>
            <div class="net-pay-amount currency">' . $currencySymbol . number_format($payroll->net_pay, 2) . '</div>
            <div style="color: #718096; margin-top: 10px;">
                ' . number_format($payroll->net_pay, 2) . ' Philippine Pesos
            </div>
        </div>
        
        <div class="footer">
            <p>Generated by Aeternitas Payroll System</p>
            <p>This is an official document. Unauthorized distribution is prohibited.</p>
            <p>Document ID: PAYSLIP-' . strtoupper(substr(md5($payroll->id . $payroll->pay_period_start), 0, 12)) . '</p>
        </div>
    </body>
    </html>';
    
    return $html;
}

/**
 * Download existing file
 */
private function downloadExistingFile($filePath, $payroll)
{
    // Create download filename
    $employeeName = $payroll->employee ? 
        str_replace(' ', '_', $payroll->employee->full_name) : 
        'Employee_' . $payroll->employee_id;
        
    $filename = 'Payslip_' . $employeeName . '_' . 
               $payroll->pay_period_start . '_to_' . 
               $payroll->pay_period_end . '.pdf';
    
    // Clean filename
    $filename = preg_replace('/[^A-Za-z0-9_\-\.]/', '_', $filename);
    
    return response()->download($filePath, $filename, [
        'Content-Type' => 'application/pdf',
        'Content-Disposition' => 'attachment; filename="' . $filename . '"'
    ]);
}

/**
 * Generate and download payslip immediately
 */
private function generateAndDownloadPayslip($payroll)
{
    // Get employee data
    $employee = $payroll->employee;
    if (!$employee) {
        throw new \Exception('Employee not found for this payroll.');
    }
    
    // Get company
    $company = CompanyHelper::getCurrentCompany() ?? (object)['name' => 'Aeternitas Company'];
    
    // Generate HTML content
    $html = view('payroll.instant-payslip', [
        'payroll' => $payroll,
        'employee' => $employee,
        'company' => $company,
        'today' => now()->format('F j, Y')
    ])->render();
    
    // Generate PDF
    $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html);
    $pdf->setPaper('A4', 'portrait');
    
    // Save to temporary file
    $tempDir = storage_path('app/temp/payslips');
    if (!file_exists($tempDir)) {
        mkdir($tempDir, 0755, true);
    }
    
    $tempFilename = 'temp_payslip_' . $payroll->id . '_' . time() . '.pdf';
    $tempPath = $tempDir . '/' . $tempFilename;
    
    $pdf->save($tempPath);
    
    // Create download filename
    $employeeName = str_replace(' ', '_', $employee->full_name);
    $filename = 'Payslip_' . $employeeName . '_' . 
               $payroll->pay_period_start . '_to_' . 
               $payroll->pay_period_end . '.pdf';
    
    $filename = preg_replace('/[^A-Za-z0-9_\-\.]/', '_', $filename);
    
    // Store file reference for future use
    $relativePath = 'payslips/' . $tempFilename;
    $payroll->payslip_file = $relativePath;
    $payroll->save();
    
    // Move to permanent location
    $permDir = storage_path('app/public/payslips');
    if (!file_exists($permDir)) {
        mkdir($permDir, 0755, true);
    }
    
    $permPath = $permDir . '/' . $tempFilename;
    copy($tempPath, $permPath);
    
    // Update with permanent path
    $payroll->payslip_file = 'payslips/' . $tempFilename;
    $payroll->save();
    
    return response()->download($tempPath, $filename, [
        'Content-Type' => 'application/pdf',
        'Content-Disposition' => 'attachment; filename="' . $filename . '"'
    ])->deleteFileAfterSend(true);
}

/**
 * Generate PDF directly as fallback
 */
private function generateDirectPdf(Payroll $payroll)
{
    try {
        // Check if DomPDF is available
        if (!class_exists('\Barryvdh\DomPDF\Facade\Pdf')) {
            throw new \Exception('PDF generation library not available.');
        }
        
        $employee = $payroll->employee;
        $company = CompanyHelper::getCurrentCompany();
        
        // Simple HTML for PDF
        $html = view('payroll.simple-payslip', [
            'payroll' => $payroll,
            'employee' => $employee,
            'company' => $company,
            'today' => now()->format('F j, Y')
        ])->render();
        
        // Generate PDF
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html);
        $pdf->setPaper('A4', 'portrait');
        
        // Create directory if not exists
        $directory = storage_path('app/payslips');
        if (!file_exists($directory)) {
            mkdir($directory, 0755, true);
        }
        
        $filename = 'payslip_' . $payroll->id . '.pdf';
        $filePath = $directory . '/' . $filename;
        
        // Save the PDF
        $pdf->save($filePath);
        
        // Update payroll record
        $payroll->payslip_file = 'payslips/' . $filename;
        $payroll->save();
        
        // Create download filename
        $employeeName = $employee ? 
            str_replace(' ', '_', $employee->full_name) : 
            'Employee_' . $payroll->employee_id;
            
        $downloadName = 'Payslip_' . $employeeName . '_' . 
                       $payroll->pay_period_start . '_to_' . 
                       $payroll->pay_period_end . '.pdf';
        
        $downloadName = preg_replace('/[^A-Za-z0-9_\-\.]/', '_', $downloadName);
        
        return response()->download($filePath, $downloadName, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $downloadName . '"'
        ]);
        
    } catch (\Exception $e) {
        throw new \Exception('Direct PDF generation failed: ' . $e->getMessage());
    }
}

    /**
     * Update payroll status
     */
    public function updateStatus(Request $request, Payroll $payroll)
    {
        try {
            $request->validate([
                'status' => 'required|in:pending,processed,approved,paid,cancelled'
            ]);

            $updateData = ['status' => $request->status];

            // Set timestamps based on status
            if ($request->status === 'processed' || $request->status === 'approved') {
                $updateData['processed_at'] = now();
            }
            if ($request->status === 'approved') {
                $updateData['approved_at'] = now();
                $updateData['approved_by'] = auth()->id();
            }
            if ($request->status === 'paid') {
                $updateData['paid_at'] = now();
            }

            $payroll->update($updateData);

            return redirect()->back()
                ->with('success', 'Payroll status updated successfully!');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error updating payroll status: ' . $e->getMessage());
        }
    }

/**
 * Export payroll data with download
 */
public function exportPayroll(Request $request)
{
    try {
        $user = Auth::user();
        
        // Authorization check: Only admin/hr/manager can export payroll
        if ($user && $user->role === 'employee') {
            return redirect()->back()->with('error', 'You are not authorized to export payroll.');
        }
        
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'format' => 'required|in:csv,xlsx', // CSV and Excel only
        ]);

        $period = $request->only(['start_date', 'end_date']);
        $employeeIds = $request->get('employee_ids', []);
        $format = $request->get('format', 'csv');

        Log::info('Exporting payroll data', [
            'period' => $period, 
            'employee_ids' => $employeeIds,
            'format' => $format
        ]);

        // Use the service method for CSV/Excel
        $filename = $this->payrollService->exportPayrollToExcel($period, $employeeIds, $format);

        // Get the full path
        $fullPath = storage_path('app/' . $filename);
        
        if (!file_exists($fullPath)) {
            throw new \Exception('Export file not found: ' . $filename);
        }

        // Return download response
        $headers = [
            'Content-Type' => $format === 'xlsx' 
                ? 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' 
                : 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . basename($filename) . '"',
        ];

        return response()->download($fullPath, basename($filename), $headers);

    } catch (\Exception $e) {
        Log::error('Error exporting payroll: ' . $e->getMessage());
        
        return redirect()->back()
            ->with('error', 'Error exporting payroll: ' . $e->getMessage())
            ->with('start_date', $request->input('start_date'))
            ->with('end_date', $request->input('end_date'));
    }
}

/**
 * Simple export function as alternative
 */
public function simpleExport(Request $request)
{
    try {
        $user = Auth::user();
        
        if ($user && $user->role === 'employee') {
            return redirect()->back()->with('error', 'You are not authorized to export payroll.');
        }
        
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'format' => 'required|in:csv,xlsx',
        ]);

        $payrolls = Payroll::with(['employee', 'employee.department'])
            ->where('pay_period_start', $request->start_date)
            ->where('pay_period_end', $request->end_date)
            ->get();
        
        if ($payrolls->isEmpty()) {
            return redirect()->back()
                ->with('error', 'No payroll records found for the selected period.')
                ->with('start_date', $request->start_date)
                ->with('end_date', $request->end_date);
        }

        $filename = 'payroll_export_' . $request->start_date . '_to_' . $request->end_date . '.' . $request->format;
        
        if ($request->format === 'csv') {
            return $this->generateSimpleCSV($payrolls, $filename);
        } else {
            return $this->generateSimpleExcel($payrolls, $filename);
        }

    } catch (\Exception $e) {
        Log::error('Simple export failed: ' . $e->getMessage());
        
        return redirect()->back()
            ->with('error', 'Export failed: ' . $e->getMessage())
            ->with('start_date', $request->input('start_date'))
            ->with('end_date', $request->input('end_date'));
    }
}

/**
 * Generate simple CSV file
 */
private function generateSimpleCSV($payrolls, $filename)
{
    $filepath = storage_path('app/exports/' . $filename);
    
    // Ensure directory exists
    $directory = storage_path('app/exports');
    if (!is_dir($directory)) {
        mkdir($directory, 0755, true);
    }

    $handle = fopen($filepath, 'w');
    
    // Add BOM for Excel UTF-8 support
    fwrite($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));
    
    // Simple headers
    $headers = [
        'Employee ID',
        'Employee Name',
        'Department',
        'Period',
        'Basic Salary',
        'Overtime Pay',
        'Allowances',
        'Deductions',
        'Net Pay',
        'Status'
    ];
    fputcsv($handle, $headers);
    
    foreach ($payrolls as $payroll) {
        $employee = $payroll->employee;
        
        if (!$employee) {
            continue;
        }
        
        $row = [
            $employee->employee_id ?? '',
            $employee->full_name ?? '',
            $employee->department->name ?? 'N/A',
            $payroll->pay_period_start . ' to ' . $payroll->pay_period_end,
            number_format($payroll->basic_salary, 2),
            number_format($payroll->overtime_pay ?? 0, 2),
            number_format($payroll->allowances ?? 0, 2),
            number_format($payroll->deductions ?? 0, 2),
            number_format($payroll->net_pay, 2),
            ucfirst($payroll->status)
        ];
        
        fputcsv($handle, $row);
    }
    
    fclose($handle);
    
    return response()->download($filepath, $filename);
}

/**
 * Generate simple Excel file
 */
private function generateSimpleExcel($payrolls, $filename)
{
    if (!class_exists('\PhpOffice\PhpSpreadsheet\Spreadsheet')) {
        throw new \Exception('PhpSpreadsheet not installed.');
    }

    $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    
    // Headers
    $headers = [
        'Employee ID',
        'Employee Name',
        'Department',
        'Period Start',
        'Period End',
        'Basic Salary',
        'Overtime Pay',
        'Allowances',
        'Deductions',
        'Net Pay',
        'Status'
    ];
    
    // Set headers
    foreach ($headers as $colIndex => $header) {
        $column = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex + 1);
        $sheet->setCellValue($column . '1', $header);
        $sheet->getStyle($column . '1')->getFont()->setBold(true);
    }
    
    // Data rows
    $row = 2;
    foreach ($payrolls as $payroll) {
        $employee = $payroll->employee;
        
        if (!$employee) {
            continue;
        }
        
        $rowData = [
            $employee->employee_id ?? '',
            $employee->full_name ?? '',
            $employee->department->name ?? 'N/A',
            $payroll->pay_period_start,
            $payroll->pay_period_end,
            $payroll->basic_salary,
            $payroll->overtime_pay ?? 0,
            $payroll->allowances ?? 0,
            $payroll->deductions ?? 0,
            $payroll->net_pay,
            ucfirst($payroll->status)
        ];
        
        // Set data for each column
        foreach ($rowData as $colIndex => $value) {
            $column = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex + 1);
            $sheet->setCellValue($column . $row, $value);
        }
        
        $row++;
    }
    
    // Auto-size columns
    for ($col = 1; $col <= count($headers); $col++) {
        $column = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
        $sheet->getColumnDimension($column)->setAutoSize(true);
    }
    
    // Format currency columns
    for ($col = 6; $col <= 10; $col++) { // Columns F to J are currency
        $column = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
        $sheet->getStyle($column . '2:' . $column . ($row - 1))
              ->getNumberFormat()
              ->setFormatCode('#,##0.00');
    }
    
    // Save file
    $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
    $filepath = storage_path('app/exports/' . $filename);
    $writer->save($filepath);
    
    return response()->download($filepath, $filename);
}

/**
 * Export payroll with detailed calculations to Excel
 */
public function exportWithCalculations(Request $request)
{
    try {
        $user = Auth::user();
        
        // Authorization check
        if ($user && $user->role === 'employee') {
            return redirect()->back()->with('error', 'You are not authorized to export detailed payroll.');
        }
        
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date',
        ]);

        // Get payrolls with relationships using BROADER date matching
        $payrolls = Payroll::with(['employee', 'employee.department'])
            ->where(function($q) use ($request) {
                // Match payrolls that overlap with selected period
                $q->whereBetween('pay_period_start', [$request->start_date, $request->end_date])
                  ->orWhereBetween('pay_period_end', [$request->start_date, $request->end_date])
                  ->orWhere(function($subQ) use ($request) {
                      $subQ->where('pay_period_start', '<=', $request->start_date)
                           ->where('pay_period_end', '>=', $request->end_date);
                  });
            })
            ->orderBy('employee_id')
            ->get();
        
        if ($payrolls->isEmpty()) {
            return redirect()->back()
                ->with('error', 'No payroll records found for the selected period.')
                ->with('start_date', $request->start_date)
                ->with('end_date', $request->end_date);
        }

        // Recalculate each payroll with proper formulas
        foreach ($payrolls as $payroll) {
            if (!$payroll->relationLoaded('employee')) {
                $payroll->load('employee');
            }
            
            if ($payroll->employee) {
                $this->recalculatePayrollWithCompanyFormulas($payroll);
            }
        }

        // Determine format (default to xlsx)
        $format = 'xlsx';
        
        // Generate filename
        $filename = 'payroll_calculations_' . $request->start_date . '_to_' . $request->end_date . '.' . $format;
        $filepath = storage_path('app/exports/' . $filename);
        
        // Ensure directory exists
        $directory = storage_path('app/exports');
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }
        
        // Generate Excel file with proper calculations
        $this->generateExcelWithCompanyCalculations($payrolls, $filepath);
        
        // Check if file was created
        if (!file_exists($filepath)) {
            throw new \Exception('Export file was not created.');
        }
        
        // Return download response
        return response()->download($filepath, $filename)->deleteFileAfterSend(true);

    } catch (\Exception $e) {
        \Log::error('Export with calculations failed: ' . $e->getMessage());
        
        return redirect()->back()
            ->with('error', 'Export failed: ' . $e->getMessage())
            ->with('start_date', $request->input('start_date'))
            ->with('end_date', $request->input('end_date'));
    }
}

/**
 * Recalculate payroll using actual company Excel formulas
 */
private function recalculatePayrollWithCompanyFormulas(Payroll $payroll)
{
    $employee = $payroll->employee;
    if (!$employee) {
        return;
    }
    
    try {
        // Get basic rates from payroll or employee
        $monthlyRate = $payroll->monthly_rate ?? $employee->salary ?? 0;
        
        // Company Excel Formula: Daily Rate = (Monthly Rate × 12) / 313
        $dailyRate = $payroll->daily_rate ?? $employee->daily_rate ?? (($monthlyRate * 12) / 313);
        
        // Company Excel Formula: Hourly Rate = Daily Rate / 8
        $hourlyRate = $payroll->hourly_rate ?? $employee->hourly_rate ?? ($dailyRate / 8);
        
        // Company Excel Formula: Overtime Rate = Hourly Rate × 125%
        $overtimeRate = $payroll->overtime_rate ?? ($hourlyRate * 1.25);
        
        // Company Excel Formula: Night Differential Rate = Hourly Rate × 10%
        $nightDiffRate = $payroll->night_differential_rate ?? ($hourlyRate * 0.10);
        
        // Calculate Basic Salary if not set
        $basicSalary = $payroll->basic_salary;
        if ($basicSalary == 0 && $monthlyRate > 0) {
            // For monthly payroll, basic salary is the monthly rate
            $basicSalary = $monthlyRate;
        }
        
        // Calculate Overtime Pay: hours × overtime rate
        $overtimePay = $payroll->overtime_pay;
        if ($overtimePay == 0 && ($payroll->overtime_hours ?? 0) > 0) {
            $overtimePay = ($payroll->overtime_hours ?? 0) * $overtimeRate;
        }
        
        // Calculate Night Differential Pay: hours × night diff rate
        $nightDiffPay = $payroll->night_differential_pay;
        if ($nightDiffPay == 0 && ($payroll->night_differential_hours ?? 0) > 0) {
            $nightDiffPay = ($payroll->night_differential_hours ?? 0) * $nightDiffRate;
        }
        
        // Calculate statutory deductions (from your sample data)
        $sss = 450.00; // Fixed as per your drivers
        $phic = 225.88; // Fixed as per your sample
        $hdmf = 100.00; // Fixed as per your sample
        
        // Calculate Gross Pay
        $grossPay = $payroll->gross_pay;
        if ($grossPay == 0) {
            $grossPay = $basicSalary + $overtimePay + $nightDiffPay + 
                       ($payroll->rest_day_premium_pay ?? 0) + 
                       ($payroll->allowances ?? 0) + 
                       ($payroll->bonuses ?? 0);
        }
        
        // Calculate Net Pay
        $netPay = $payroll->net_pay;
        if ($netPay == 0) {
            $totalDeductions = ($payroll->deductions ?? 0) + $sss + $phic + $hdmf + ($payroll->tax_amount ?? 0);
            $netPay = $grossPay - $totalDeductions;
        }
        
        // Update payroll with calculated values
        $payroll->update([
            'monthly_rate' => $monthlyRate,
            'daily_rate' => round($dailyRate, 2),
            'hourly_rate' => round($hourlyRate, 2),
            'overtime_rate' => round($overtimeRate, 2),
            'night_differential_rate' => round($nightDiffRate, 2),
            'basic_salary' => round($basicSalary, 2),
            'overtime_pay' => round($overtimePay, 2),
            'night_differential_pay' => round($nightDiffPay, 2),
            'sss' => $sss,
            'phic' => $phic,
            'hdmf' => $hdmf,
            'gross_pay' => round($grossPay, 2),
            'net_pay' => round($netPay, 2),
            'updated_at' => now()
        ]);
        
    } catch (\Exception $e) {
        \Log::error("Error recalculating payroll {$payroll->id}: " . $e->getMessage());
    }
}

/**
 * Generate Excel with company calculations
 */
private function generateExcelWithCompanyCalculations($payrolls, $filepath)
{
    if (!class_exists('\PhpOffice\PhpSpreadsheet\Spreadsheet')) {
        throw new \Exception('PhpSpreadsheet not installed. Please install via composer: composer require phpoffice/phpspreadsheet');
    }

    $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    
    // Set document properties
    $spreadsheet->getProperties()
        ->setCreator('Aeternitas Payroll System')
        ->setLastModifiedBy('Aeternitas Payroll System')
        ->setTitle('Payroll Calculations Report')
        ->setSubject('Detailed Payroll Calculations')
        ->setDescription('Payroll export with company calculation formulas');
    
    // Headers matching your sample data format
    $headers = [
        'Payroll ID',
        'Employee ID',
        'Employee Name',
        'Department',
        'Period Start',
        'Period End',
        'Basic Salary',
        'Daily Rate',
        'Hourly Rate',
        'Overtime Hours',
        'Overtime Rate',
        'Overtime Pay',
        'Night Differential Hours',
        'Night Differential Rate',
        'Night Differential Pay',
        'Rest Day Premium Pay',
        'Allowances',
        'Bonuses',
        'Deductions',
        'Tax Amount',
        'Gross Pay',
        'Net Pay',
        'Status',
        'Approved Date',
        'Paid Date'
    ];
    
    // Set headers with formatting
    foreach ($headers as $colIndex => $header) {
        $column = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex + 1);
        $sheet->setCellValue($column . '1', $header);
        $sheet->getStyle($column . '1')->getFont()->setBold(true);
        $sheet->getStyle($column . '1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
        $sheet->getStyle($column . '1')->getFill()->getStartColor()->setARGB('FFE0E0E0');
    }
    
    // Data rows
    $row = 2;
    foreach ($payrolls as $payroll) {
        $employee = $payroll->employee;
        
        if (!$employee) {
            continue;
        }
        
        // Ensure payroll has latest calculated values
        $this->recalculatePayrollWithCompanyFormulas($payroll);
        $payroll->refresh();
        
        $rowData = [
            $payroll->id,
            $employee->employee_id ?? '',
            $employee->full_name ?? '',
            $employee->department->name ?? 'N/A',
            $payroll->pay_period_start,
            $payroll->pay_period_end,
            $payroll->basic_salary,
            $payroll->daily_rate ?? 0,
            $payroll->hourly_rate ?? 0,
            $payroll->overtime_hours ?? 0,
            $payroll->overtime_rate ?? 0,
            $payroll->overtime_pay ?? 0,
            $payroll->night_differential_hours ?? 0,
            $payroll->night_differential_rate ?? 0,
            $payroll->night_differential_pay ?? 0,
            $payroll->rest_day_premium_pay ?? 0,
            $payroll->allowances ?? 0,
            $payroll->bonuses ?? 0,
            $payroll->deductions ?? 0,
            $payroll->tax_amount ?? 0,
            $payroll->gross_pay,
            $payroll->net_pay,
            ucfirst($payroll->status),
            $payroll->approved_at ? $payroll->approved_at->format('Y-m-d H:i:s') : '',
            $payroll->paid_at ? $payroll->paid_at->format('Y-m-d H:i:s') : ''
        ];
        
        // Set data for each column
        foreach ($rowData as $colIndex => $value) {
            $column = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex + 1);
            $sheet->setCellValue($column . $row, $value);
        }
        
        $row++;
    }
    
    // Add totals row
    $totalRow = $row;
    $sheet->setCellValue('F' . $totalRow, 'TOTALS:');
    
    // Total formulas for currency columns
    $totalColumns = ['G', 'H', 'I', 'L', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V'];
    foreach ($totalColumns as $col) {
        $sheet->setCellValue($col . $totalRow, '=SUM(' . $col . '2:' . $col . ($row-1) . ')');
    }
    
    // Format totals row
    $sheet->getStyle('F' . $totalRow . ':V' . $totalRow)->getFont()->setBold(true);
    $sheet->getStyle('F' . $totalRow . ':V' . $totalRow)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
    $sheet->getStyle('F' . $totalRow . ':V' . $totalRow)->getFill()->getStartColor()->setARGB('FFF0F0F0');
    
    // Auto-size columns
    for ($col = 1; $col <= count($headers); $col++) {
        $column = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
        $sheet->getColumnDimension($column)->setAutoSize(true);
    }
    
    // Format currency columns
    $currencyColumns = ['G', 'H', 'I', 'L', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V'];
    foreach ($currencyColumns as $col) {
        $lastRow = $row - 1;
        if ($lastRow >= 2) {
            $range = $col . '2:' . $col . $lastRow;
            $sheet->getStyle($range)
                  ->getNumberFormat()
                  ->setFormatCode('#,##0.00');
        }
    }
    
    // Format totals row currency
    $sheet->getStyle('G' . $totalRow . ':V' . $totalRow)
          ->getNumberFormat()
          ->setFormatCode('#,##0.00');
    
    // Add borders
    $styleArray = [
        'borders' => [
            'allBorders' => [
                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                'color' => ['argb' => 'FF000000'],
            ],
        ],
    ];
    
    $lastColumn = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($headers));
    $sheet->getStyle('A1:' . $lastColumn . $totalRow)->applyFromArray($styleArray);
    
    // Save file
    $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
    $writer->save($filepath);
}

/**
 * Recalculate payroll using actual company Excel formulas
 */
private function recalculatePayrollWithExcelFormulas(Payroll $payroll, Employee $employee)
{
    try {
        // Get monthly rate from payroll or employee
        $monthlyRate = $payroll->monthly_rate ?? $employee->salary ?? 0;
        
        // Company Excel Formula: Semi-monthly = Monthly / 2
        $semiMonthlyRate = $monthlyRate / 2;
        
        // Company Excel Formula: Daily Rate = (Monthly Rate × 12) / 313
        $dailyRate = ($monthlyRate * 12) / 313;
        
        // Company Excel Formula: Hourly Rate = Daily Rate / 8
        $hourlyRate = $dailyRate / 8;
        
        // Company Excel Formula: Overtime Rate = Hourly Rate × 1.25 (125%)
        $overtimeRate = $hourlyRate * 1.25;
        
        // Company Excel Formula: Night Differential Rate = Hourly Rate × 0.10 (10%)
        $nightDiffRate = $hourlyRate * 0.10;
        
        // Calculate Basic Salary (Semi-monthly rate)
        $basicSalary = $semiMonthlyRate;
        
        // Calculate Overtime Pay: =H14*L14*1.25 (hours × hourly rate × 1.25)
        $overtimePay = ($payroll->overtime_hours ?? 0) * $overtimeRate;
        
        // Calculate Night Differential: =H14*0.1*X14 (hours × 10% × hourly rate)
        $nightDiffPay = ($payroll->night_differential_hours ?? 0) * $nightDiffRate;
        
        // Calculate statutory deductions from company Excel
        $sss = 450.00; // Fixed as per Excel for drivers
        $phic = ($monthlyRate >= 10000) ? 225.88 : 0; // Excel: =451.75/2
        $hdmf = 100.00; // Fixed amount
        
        // Calculate Gross Pay using company formula pattern
        // Excel: =G14*K14+I14+J14+M14+O14+Q14+S14+U14+W14+Y14-AC14
        $grossPay = $basicSalary 
            + ($payroll->incentive_leave_pay ?? 0) // I14 (5 days incentive leave)
            + $overtimePay
            + $nightDiffPay
            + ($payroll->rest_day_premium_pay ?? 0)
            + ($payroll->allowances ?? 0)
            - ($payroll->late_deductions ?? 0);
        
        // Calculate deductions
        $deductions = $payroll->deductions ?? 0;
        $taxAmount = $payroll->tax_amount ?? 0;
        
        // Calculate Net Pay
        $netPay = $grossPay - $deductions - $taxAmount - $sss - $phic - $hdmf;
        
        // Update payroll with calculated values
        $payroll->update([
            'monthly_rate' => $monthlyRate,
            'semi_monthly_rate' => $semiMonthlyRate,
            'daily_rate' => round($dailyRate, 2),
            'hourly_rate' => round($hourlyRate, 2),
            'overtime_rate' => round($overtimeRate, 2),
            'night_differential_rate' => round($nightDiffRate, 2),
            'basic_salary' => round($basicSalary, 2),
            'overtime_pay' => round($overtimePay, 2),
            'night_differential_pay' => round($nightDiffPay, 2),
            'sss' => $sss,
            'phic' => round($phic, 2),
            'hdmf' => $hdmf,
            'gross_pay' => round($grossPay, 2),
            'net_pay' => round($netPay, 2),
            'updated_at' => now()
        ]);
        
        Log::info("Recalculated payroll {$payroll->id} with Excel formulas", [
            'monthly_rate' => $monthlyRate,
            'daily_rate' => $dailyRate,
            'hourly_rate' => $hourlyRate,
            'basic_salary' => $basicSalary,
            'net_pay' => $netPay
        ]);
        
    } catch (\Exception $e) {
        Log::error("Error recalculating payroll with Excel formulas: " . $e->getMessage());
        throw $e;
    }
}

// Add to PayrollController
private function createPayslipZip($payrolls, $filename)
{
    $zipPath = storage_path('app/temp/' . $filename);
    
    // Ensure temp directory exists
    $tempDir = storage_path('app/temp');
    if (!file_exists($tempDir)) {
        mkdir($tempDir, 0755, true);
    }
    
    // Create ZIP archive
    $zip = new \ZipArchive();
    if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === TRUE) {
        foreach ($payrolls as $payroll) {
            if ($payroll->payslip_file && Storage::exists($payroll->payslip_file)) {
                $employeeName = preg_replace('/[^A-Za-z0-9_-]/', '_', $payroll->employee->full_name ?? 'Unknown');
                $filenameInZip = "payslip_{$employeeName}_{$payroll->pay_period_start}_{$payroll->pay_period_end}.pdf";
                
                try {
                    $fileContent = Storage::get($payroll->payslip_file);
                    $zip->addFromString($filenameInZip, $fileContent);
                } catch (\Exception $e) {
                    Log::error('Error adding file to ZIP: ' . $e->getMessage());
                }
            }
        }
        $zip->close();
    } else {
        throw new \Exception('Failed to create ZIP file.');
    }
    
    return $zipPath;
}

/**
 * Export payroll to PDF
 */
private function exportPayrollToPDF(Request $request)
{
    try {
        $currentCompany = CompanyHelper::getCurrentCompany();
        
        // Use window function to get latest payroll per employee per period (same as index page)
        $latestPayrollsSubquery = DB::table('payrolls as p1')
            ->select(
                'p1.id',
                'p1.employee_id',
                'p1.pay_period_start',
                'p1.pay_period_end',
                DB::raw('ROW_NUMBER() OVER (PARTITION BY p1.employee_id, p1.pay_period_start, p1.pay_period_end ORDER BY p1.created_at DESC) as rn')
            );
        
        // Build date filter for subquery
        if ($request->start_date && $request->end_date) {
            $latestPayrollsSubquery->where(function($q) use ($request) {
                $q->where(function($subQ) use ($request) {
                    $subQ->whereBetween('p1.pay_period_start', [$request->start_date, $request->end_date]);
                })->orWhere(function($subQ) use ($request) {
                    $subQ->whereBetween('p1.pay_period_end', [$request->start_date, $request->end_date]);
                })->orWhere(function($subQ) use ($request) {
                    $subQ->where('p1.pay_period_start', '<=', $request->start_date)
                         ->where('p1.pay_period_end', '>=', $request->end_date);
                })->orWhere(function($subQ) use ($request) {
                    $subQ->where('p1.pay_period_start', $request->start_date)
                         ->where('p1.pay_period_end', $request->end_date);
                });
            });
        } elseif ($request->start_date) {
            $latestPayrollsSubquery->where('p1.pay_period_start', '>=', $request->start_date);
        } elseif ($request->end_date) {
            $latestPayrollsSubquery->where('p1.pay_period_end', '<=', $request->end_date);
        }
        
        // Filter by employee if specified
        if ($request->filled('employee_ids') && is_array($request->employee_ids)) {
            $latestPayrollsSubquery->whereIn('p1.employee_id', $request->employee_ids);
        }
        
        // Get only the latest payroll per employee per period
        $latestPayrollsQuery = DB::table(DB::raw("({$latestPayrollsSubquery->toSql()}) as latest_payrolls"))
            ->mergeBindings($latestPayrollsSubquery)
            ->where('latest_payrolls.rn', 1)
            ->select('latest_payrolls.id');
        
        $latestPayrollIds = $latestPayrollsQuery->pluck('id')->toArray();
        
        $query = Payroll::with(['employee', 'employee.department'])
            ->whereIn('id', $latestPayrollIds);
            
        // IMPORTANT: Add status filter to get correct statuses
        if ($request->has('status') && $request->status != 'all') {
            $query->where('status', $request->status);
        } else {
            // Get all statuses but ensure status is properly loaded
            $query->whereIn('status', ['pending', 'approved', 'paid', 'processed']);
        }
        
        $payrolls = $query->orderBy('employee_id')->get();
        
        if ($payrolls->isEmpty()) {
            throw new \Exception('No payroll records found for the selected period.');
        }
        
        // ===== INTEGRATED STATUS VERIFICATION AND DATA FRESHNESS CHECK =====
        $payrollIds = $payrolls->pluck('id')->toArray();
        
        // Use a single query to refresh all payrolls and check payments
        $payrolls = Payroll::with(['employee', 'employee.department'])
            ->whereIn('id', $payrollIds)
            ->orderBy('employee_id')
            ->get();
        
        // Check payment records in bulk if Payment model exists
        if (class_exists('\App\Models\Payment')) {
            $paymentStatuses = \App\Models\Payment::whereIn('payroll_id', $payrollIds)
                ->where('status', 'completed')
                ->pluck('payroll_id')
                ->toArray();
            
            // Update payroll statuses based on payment records
            foreach ($payrolls as $payroll) {
                if (in_array($payroll->id, $paymentStatuses) && $payroll->status !== 'paid') {
                    $payroll->status = 'paid';
                }
            }
        }
        // ===== END STATUS VERIFICATION =====
        
        // Recalculate payrolls with zero values before export
        $hasRecalculations = false;
        foreach ($payrolls as $payroll) {
            if (($payroll->basic_salary == 0 && $payroll->gross_pay == 0) && $payroll->employee) {
                $this->recalculatePayrollValues($payroll);
                $hasRecalculations = true;
            }
        }
        
        // Only reload if recalculations were performed
        if ($hasRecalculations) {
            $payrolls = Payroll::with(['employee', 'employee.department'])
                ->whereIn('id', $payrollIds)
                ->orderBy('employee_id')
                ->get();
        }
        
        // Check if DomPDF is available
        if (!class_exists('\Barryvdh\DomPDF\Facade\Pdf')) {
            throw new \Exception('PDF generation library not available.');
        }
        
        // Generate PDF HTML with proper encoding
        $html = view('payroll.export-pdf', [
            'payrolls' => $payrolls,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'company' => $currentCompany,
        ])->render();
        
        // Add proper HTML header for UTF-8
        $html = '<!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Payroll Export</title>
            <style>
                body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
                table { width: 100%; border-collapse: collapse; }
                th, td { padding: 8px; border: 1px solid #ddd; text-align: left; }
                th { background-color: #f5f5f5; font-weight: bold; }
                .total-row { font-weight: bold; background-color: #f0f0f0; }
            </style>
        </head>
        <body>' . $html . '</body></html>';
        
        // Generate PDF with UTF-8 encoding
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html);
        $pdf->setPaper('a4', 'landscape');
        
        // Ensure UTF-8 encoding for PDF with proper font
        $pdf->setOption('defaultFont', 'dejavusans');
        $pdf->setOption('isHtml5ParserEnabled', true);
        $pdf->setOption('isRemoteEnabled', true);
        $pdf->setOption('isPhpEnabled', true);
        $pdf->setOption('chroot', base_path());
        
        $filename = 'payroll_export_' . $request->start_date . '_to_' . $request->end_date . '.pdf';
        
        return $pdf->download($filename);
        
    } catch (\Exception $e) {
        Log::error('Error exporting payroll to PDF: ' . $e->getMessage());
        throw $e;
    }
}

    /**
     * Recalculate payroll values from employee data
     */
    private function recalculatePayrollValues(Payroll $payroll)
    {
        $employee = $payroll->employee;
        if (!$employee) {
            return;
        }
        
        // Get rates from payroll or employee
        $monthlyRate = $payroll->monthly_rate ?? $employee->salary ?? 0;
        $semiMonthlyRate = $payroll->semi_monthly_rate ?? ($monthlyRate / 2);
        $dailyRate = $payroll->daily_rate ?? $employee->daily_rate ?? ($monthlyRate * 12 / 313);
        $hourlyRate = $payroll->hourly_rate ?? $employee->hourly_rate ?? ($dailyRate / 8);
        
        // Calculate basic salary - use semi-monthly rate if available
        $basicSalary = $payroll->basic_salary;
        if ($basicSalary == 0) {
            if ($semiMonthlyRate > 0) {
                $basicSalary = $semiMonthlyRate;
            } elseif ($monthlyRate > 0) {
                $basicSalary = $monthlyRate / 2;
            } elseif ($dailyRate > 0) {
                // Calculate based on pay period days
                $startDate = \Carbon\Carbon::parse($payroll->pay_period_start);
                $endDate = \Carbon\Carbon::parse($payroll->pay_period_end);
                $daysInPeriod = $startDate->diffInDays($endDate) + 1;
                $basicSalary = $dailyRate * min($daysInPeriod, 15); // Semi-monthly typically 15 days
            }
        }
        
        // Calculate overtime pay
        $overtimeRate = $payroll->overtime_rate ?? ($hourlyRate * 1.25);
        $overtimePay = $payroll->overtime_pay;
        if ($overtimePay == 0 && ($payroll->overtime_hours ?? 0) > 0) {
            $overtimePay = ($payroll->overtime_hours ?? 0) * $overtimeRate;
        }
        
        // Get other components
        $nightDiffPay = $payroll->night_differential_pay ?? 0;
        $restDayPremium = $payroll->rest_day_premium_pay ?? 0;
        $allowances = $payroll->allowances ?? 0;
        $bonuses = $payroll->bonuses ?? 0;
        $deductions = $payroll->deductions ?? 0;
        $taxAmount = $payroll->tax_amount ?? 0;
        
        // Calculate gross pay
        $grossPay = $basicSalary + $overtimePay + $nightDiffPay + 
                   $restDayPremium + $allowances + $bonuses;
        
        // Calculate net pay
        $netPay = $grossPay - $deductions - $taxAmount;
        
        // Update payroll if values changed
        if ($basicSalary > 0 || $grossPay > 0) {
            $payroll->update([
                'basic_salary' => $basicSalary,
                'gross_pay' => $grossPay,
                'net_pay' => $netPay,
                'overtime_pay' => $overtimePay,
                'monthly_rate' => $monthlyRate,
                'semi_monthly_rate' => $semiMonthlyRate,
                'daily_rate' => $dailyRate,
                'hourly_rate' => $hourlyRate,
                'overtime_rate' => $overtimeRate,
            ]);
    }
}

    /**
     * Generate payroll from selected dates
     */
    public function generate(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date'
        ]);

        try {
            // Get the current company
            $currentCompany = CompanyHelper::getCurrentCompany();
            
            // Get employees for the current company
            $employeesQuery = Employee::query();
            if ($currentCompany) {
                $employeesQuery->forCompany($currentCompany->id);
            }
            $employees = $employeesQuery->get();
            
            if ($employees->isEmpty()) {
                return redirect()->route('payroll.index')
                    ->with('error', 'No employees found for your company.');
            }

            // Get comprehensive attendance data for the period
            $startDate = Carbon::parse($request->start_date);
            $endDate = Carbon::parse($request->end_date);
            
            Log::info('Generating payroll for period', [
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
                'employee_count' => $employees->count(),
                'company_id' => $currentCompany?->id
            ]);
            
            // Get comprehensive attendance data
            $comprehensiveData = $this->getComprehensiveAttendanceData($startDate, $endDate, $employees);
            
            Log::info('Comprehensive attendance data', [
                'data_count' => count($comprehensiveData),
                'has_data' => !empty($comprehensiveData)
            ]);
            
            if (empty($comprehensiveData)) {
                return redirect()->route('payroll.index')
                    ->with('error', 'No attendance data found for the selected period. Please ensure attendance records exist for this period.')
                    ->with('start_date', $request->start_date)
                    ->with('end_date', $request->end_date);
            }
            
            // Prepare period data for payroll service
            $periodData = [
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'company_id' => $currentCompany?->id
            ];
            
            // Generate payroll using the service
            $generatedPayrolls = $this->payrollService->generatePayrollFromComprehensiveData(
                $periodData, 
                $comprehensiveData
            );
            
            $count = count($generatedPayrolls);
            
            Log::info('Payroll generation completed', [
                'generated_count' => $count,
                'period' => $request->start_date . ' to ' . $request->end_date
            ]);
            
            if ($count === 0) {
                return redirect()->route('payroll.index')
                    ->with('warning', 'No payroll records were generated. This could be because payroll already exists for this period or there were issues with attendance data.')
                    ->with('start_date', $request->start_date)
                    ->with('end_date', $request->end_date);
            }
            
            return redirect()->route('payroll.index')
                ->with('success', "Generated payroll for {$count} employees!")
                ->with('start_date', $request->start_date)
                ->with('end_date', $request->end_date);
                
        } catch (\Exception $e) {
            Log::error('Payroll generation failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'start_date' => $request->start_date ?? null,
                'end_date' => $request->end_date ?? null
            ]);
            
            return redirect()->route('payroll.index')
                ->with('error', 'Payroll generation failed: ' . $e->getMessage())
                ->with('start_date', $request->start_date)
                ->with('end_date', $request->end_date);
        }
    }

    /**
     * Get approved payrolls for API (ADD THIS METHOD)
     */
    public function getApprovedPayrolls(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date'
        ]);
        
        // Get current company
        $currentCompany = CompanyHelper::getCurrentCompany();
        
        $query = Payroll::with('employee')
            ->where('status', 'approved')
            ->where('pay_period_start', $request->start_date)
            ->where('pay_period_end', $request->end_date);
        
        // Filter by company if applicable
        if ($currentCompany) {
            $query->whereHas('employee', function($q) use ($currentCompany) {
                $q->where('company_id', $currentCompany->id);
            });
        }
        
        $payrolls = $query->get()
            ->map(function ($payroll) {
                return [
                    'id' => $payroll->id,
                    'employee_id' => $payroll->employee_id,
                    'employee_name' => $payroll->employee->full_name ?? 'Unknown',
                    'employee_code' => $payroll->employee->employee_id ?? '',
                    'net_pay' => $payroll->net_pay,
                    'gross_pay' => $payroll->gross_pay,
                    'basic_salary' => $payroll->basic_salary,
                    'overtime_pay' => $payroll->overtime_pay ?? 0,
                    'deductions' => $payroll->deductions ?? 0
                ];
            });
        
        Log::info('API: getApprovedPayrolls', [
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'count' => $payrolls->count(),
            'company_id' => $currentCompany?->id
        ]);
        
        return response()->json($payrolls);
    }
    
    /**
     * Process payments via API (ADD THIS METHOD)
     */
    public function processPaymentsApi(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'employee_ids' => 'required|array',
            'employee_ids.*' => 'exists:employees,id',
            'payment_method' => 'required|string',
            'notes' => 'nullable|string'
        ]);
        
        try {
            // Get current company
            $currentCompany = CompanyHelper::getCurrentCompany();
            
            // Verify employees belong to current company
            if ($currentCompany) {
                $validEmployeeIds = Employee::where('company_id', $currentCompany->id)
                    ->whereIn('id', $request->employee_ids)
                    ->pluck('id')
                    ->toArray();
                
                if (count($validEmployeeIds) !== count($request->employee_ids)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Some employees do not belong to your company.'
                    ], 403);
                }
            }
            
            $result = $this->payrollService->processPayments(
                ['start_date' => $request->start_date, 'end_date' => $request->end_date],
                $request->employee_ids,
                auth()->user()->id
            );
            
            // Store payment method in Payment model if exists
            if (class_exists(\App\Models\Payment::class) && !empty($result['processed'])) {
                // Update each payment individually to ensure payment method is set
                foreach ($request->employee_ids as $employeeId) {
                    \App\Models\Payment::where('employee_id', $employeeId)
                        ->whereDate('created_at', today())
                        ->update([
                            'payment_method' => $request->payment_method,
                            'notes' => $request->notes
                        ]);
                }
                
                Log::info('Updated payments with payment method', [
                    'employee_count' => count($request->employee_ids),
                    'payment_method' => $request->payment_method,
                    'processed_count' => $result['processed']
                ]);
            }
            
            return response()->json([
                'success' => true,
                'processed' => $result['processed'],
                'failed' => $result['failed'],
                'message' => 'Payments processed successfully'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Payment processing failed: ' . $e->getMessage(), [
                'employee_ids' => $request->employee_ids,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Payment processing failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
 * Download all payslips as ZIP file
 */
public function downloadAllPayslips(Request $request)
{
    try {
        $user = Auth::user();
        
        // Authorization check: Only admin/hr/manager can download all payslips
        if ($user && $user->role === 'employee') {
            return redirect()->back()->with('error', 'You are not authorized to download all payslips.');
        }
        
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date'
        ]);
        
        \Illuminate\Support\Facades\Log::info('Downloading payslips as ZIP', [
            'start_date' => $request->start_date,
            'end_date' => $request->end_date
        ]);
        
        // Get all payrolls with payslips for the period
        $payrolls = Payroll::where('pay_period_start', '>=', $request->start_date)
            ->where('pay_period_end', '<=', $request->end_date)
            ->whereNotNull('payslip_file')
            ->with('employee')
            ->get();
        
        if ($payrolls->isEmpty()) {
            return redirect()->back()
                ->with('error', 'No payslips found for the selected period. Generate payslips first.')
                ->with('start_date', $request->start_date)
                ->with('end_date', $request->end_date);
        }
        
        \Illuminate\Support\Facades\Log::info('Found ' . $payrolls->count() . ' payslips to download');
        
        // Create ZIP file
        $zipFileName = 'payslips_' . $request->start_date . '_to_' . $request->end_date . '.zip';
        $zipPath = storage_path('app/temp/' . $zipFileName);
        
        // Ensure temp directory exists
        $tempDir = storage_path('app/temp');
        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0755, true);
        }
        
        // Create ZIP archive
        $zip = new \ZipArchive();
        if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === TRUE) {
            foreach ($payrolls as $payroll) {
                if ($payroll->payslip_file && Storage::exists($payroll->payslip_file)) {
                    // Clean up employee name for filename
                    $employeeName = preg_replace('/[^A-Za-z0-9_-]/', '_', $payroll->employee->full_name ?? 'Unknown');
                    $filename = "payslip_{$employeeName}_{$payroll->pay_period_start}_{$payroll->pay_period_end}.pdf";
                    
                    // Get file content
                    $fileContent = Storage::get($payroll->payslip_file);
                    
                    // Add to ZIP
                    $zip->addFromString($filename, $fileContent);
                    
                    \Illuminate\Support\Facades\Log::info('Added to ZIP: ' . $filename);
                }
            }
            $zip->close();
        } else {
            throw new \Exception('Failed to create ZIP file.');
        }
        
        // Check if ZIP was created
        if (!file_exists($zipPath)) {
            throw new \Exception('ZIP file was not created: ' . $zipPath);
        }
        
        \Illuminate\Support\Facades\Log::info('ZIP file created: ' . $zipPath . ', size: ' . filesize($zipPath));
        
        // Download the ZIP file
        return response()->download($zipPath, $zipFileName)->deleteFileAfterSend(true);
        
    } catch (\Exception $e) {
        \Illuminate\Support\Facades\Log::error('Error downloading all payslips: ' . $e->getMessage());
        return redirect()->back()
            ->with('error', 'Error downloading payslips: ' . $e->getMessage())
            ->with('start_date', $request->input('start_date', ''))
            ->with('end_date', $request->input('end_date', ''));
    }
}

/**
 * Export payroll with calculations (SIMPLIFIED VERSION)
 */
public function exportDetailed(Request $request)
{
    try {
        $user = Auth::user();
        
        // Authorization check
        if ($user && $user->role === 'employee') {
            return redirect()->back()->with('error', 'You are not authorized to export detailed payroll.');
        }
        
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'format' => 'nullable|in:csv,xlsx',
        ]);

        // Get payrolls with relationships
        $payrolls = Payroll::with(['employee', 'employee.department'])
            ->where('pay_period_start', $request->start_date)
            ->where('pay_period_end', $request->end_date)
            ->get();
        
        if ($payrolls->isEmpty()) {
            return redirect()->back()
                ->with('error', 'No payroll records found for the selected period.')
                ->with('start_date', $request->start_date)
                ->with('end_date', $request->end_date);
        }

        // Determine format
        $format = $request->get('format', 'xlsx');
        
        // Generate filename
        $filename = 'payroll_export_' . $request->start_date . '_to_' . $request->end_date . '.' . $format;
        $filepath = storage_path('app/exports/' . $filename);
        
        // Ensure directory exists
        $directory = storage_path('app/exports');
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }
        
        // Generate CSV or Excel file
        if ($format === 'csv') {
            $this->generateCSVExport($payrolls, $filepath);
        } else {
            $this->generateExcelExport($payrolls, $filepath);
        }
        
        // Check if file was created
        if (!file_exists($filepath)) {
            throw new \Exception('Export file was not created.');
        }
        
        // Return download response
        return response()->download($filepath, $filename);

    } catch (\Exception $e) {
        \Log::error('Detailed export failed: ' . $e->getMessage());
        
        return redirect()->back()
            ->with('error', 'Export failed: ' . $e->getMessage())
            ->with('start_date', $request->input('start_date'))
            ->with('end_date', $request->input('end_date'));
    }
}

/**
 * Generate CSV export (simple version)
 */
private function generateCSVExport($payrolls, $filepath)
{
    $handle = fopen($filepath, 'w');
    
    // Add BOM for Excel UTF-8 support
    fwrite($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));
    
    // Headers
    $headers = [
        'Employee ID',
        'Employee Name',
        'Department',
        'Period Start',
        'Period End',
        'Basic Salary',
        'Overtime Pay',
        'Allowances',
        'Deductions',
        'Tax Amount',
        'Gross Pay',
        'Net Pay',
        'Status'
    ];
    fputcsv($handle, $headers);
    
    foreach ($payrolls as $payroll) {
        $employee = $payroll->employee;
        
        if (!$employee) {
            continue;
        }
        
        $row = [
            $employee->employee_id ?? '',
            $employee->full_name ?? '',
            $employee->department->name ?? 'N/A',
            $payroll->pay_period_start,
            $payroll->pay_period_end,
            number_format($payroll->basic_salary, 2),
            number_format($payroll->overtime_pay ?? 0, 2),
            number_format($payroll->allowances ?? 0, 2),
            number_format($payroll->deductions ?? 0, 2),
            number_format($payroll->tax_amount ?? 0, 2),
            number_format($payroll->gross_pay, 2),
            number_format($payroll->net_pay, 2),
            ucfirst($payroll->status)
        ];
        
        fputcsv($handle, $row);
    }
    
    fclose($handle);
}

/**
 * Generate Excel export (simple version)
 */
private function generateExcelExport($payrolls, $filepath)
{
    if (!class_exists('\PhpOffice\PhpSpreadsheet\Spreadsheet')) {
        throw new \Exception('PhpSpreadsheet not installed.');
    }

    $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    
    // Headers
    $headers = [
        'Employee ID',
        'Employee Name',
        'Department',
        'Period Start',
        'Period End',
        'Basic Salary',
        'Overtime Pay',
        'Allowances',
        'Deductions',
        'Tax Amount',
        'Gross Pay',
        'Net Pay',
        'Status'
    ];
    
    // Set headers
    foreach ($headers as $colIndex => $header) {
        $column = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex + 1);
        $sheet->setCellValue($column . '1', $header);
        $sheet->getStyle($column . '1')->getFont()->setBold(true);
    }
    
    // Data rows
    $row = 2;
    foreach ($payrolls as $payroll) {
        $employee = $payroll->employee;
        
        if (!$employee) {
            continue;
        }
        
        $rowData = [
            $employee->employee_id ?? '',
            $employee->full_name ?? '',
            $employee->department->name ?? 'N/A',
            $payroll->pay_period_start,
            $payroll->pay_period_end,
            $payroll->basic_salary,
            $payroll->overtime_pay ?? 0,
            $payroll->allowances ?? 0,
            $payroll->deductions ?? 0,
            $payroll->tax_amount ?? 0,
            $payroll->gross_pay,
            $payroll->net_pay,
            ucfirst($payroll->status)
        ];
        
        // Set data for each column
        foreach ($rowData as $colIndex => $value) {
            $column = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex + 1);
            $sheet->setCellValue($column . $row, $value);
        }
        
        $row++;
    }
    
    // Auto-size columns
    for ($col = 1; $col <= count($headers); $col++) {
        $column = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
        $sheet->getColumnDimension($column)->setAutoSize(true);
    }
    
    // Format currency columns
    for ($col = 6; $col <= 12; $col++) { // Columns F to L are currency
        $column = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
        $sheet->getStyle($column . '2:' . $column . ($row - 1))
              ->getNumberFormat()
              ->setFormatCode('#,##0.00');
    }
    
    // Save file
    $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
    $writer->save($filepath);
}

/**
 * Enhanced export function in PayrollGenerationService
 */
public function exportPayrollWithCalculations($payrolls, $format = 'xlsx')
{
    // Ensure all payrolls have recalculated values
    foreach ($payrolls as $payroll) {
        if (!$payroll->relationLoaded('employee')) {
            $payroll->load('employee');
        }
        
        if ($payroll->employee) {
            $this->recalculatePayrollValues($payroll);
        }
    }
    
    // Get fresh data with updated values
    $payrollIds = $payrolls->pluck('id')->toArray();
    $payrolls = Payroll::with(['employee', 'employee.department'])
        ->whereIn('id', $payrollIds)
        ->get();
    
    $filename = 'payroll_export_detailed_' . date('Ymd_His') . '.' . $format;
    
    // Ensure directory exists
    $directory = storage_path('app/exports');
    if (!is_dir($directory)) {
        mkdir($directory, 0755, true);
    }
    
    if ($format === 'csv') {
        return $this->exportDetailedToCSV($payrolls, $filename);
    } elseif ($format === 'xlsx') {
        return $this->exportDetailedToXLSX($payrolls, $filename);
    } else {
        throw new \Exception('Unsupported format: ' . $format);
    }
}

/**
 * Process payments for selected payrolls only
 */
public function processSelectedPayments(Request $request)
{
    try {
        $request->validate([
            'payroll_ids' => 'required|array',
            'payroll_ids.*' => 'exists:payrolls,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date'
        ]);

        $result = $this->payrollService->processPayments(
            ['start_date' => $request->start_date, 'end_date' => $request->end_date],
            $request->payroll_ids,
            auth()->id()
        );

        return response()->json([
            'success' => true,
            'processed' => $result['processed'],
            'message' => 'Payments processed successfully!'
        ]);

    } catch (\Exception $e) {
        Log::error('Process selected payments failed: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Error: ' . $e->getMessage()
        ], 500);
    }
}

/**
 * Approve selected payrolls
 */
public function approveSelected(Request $request)
{
    try {
        $request->validate([
            'payroll_ids' => 'required|array',
            'payroll_ids.*' => 'exists:payrolls,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date'
        ]);

        $period = ['start_date' => $request->start_date, 'end_date' => $request->end_date];
        $approvedBy = auth()->id();

        $count = 0;
        foreach ($request->payroll_ids as $payrollId) {
            $payroll = Payroll::find($payrollId);
            
            if ($payroll && $payroll->status === 'pending') {
                // Calculate gross pay if not set
                if (empty($payroll->gross_pay)) {
                    $grossPay = $payroll->basic_salary 
                        + ($payroll->overtime_hours * $payroll->overtime_rate)
                        + $payroll->bonuses;
                    
                    $netPay = $grossPay - $payroll->deductions - $payroll->tax_amount;
                    
                    $payroll->gross_pay = $grossPay;
                    $payroll->net_pay = $netPay;
                }

                $payroll->update([
                    'status' => 'approved',
                    'approved_at' => now(),
                    'approved_by' => $approvedBy,
                    'processed_at' => now(),
                ]);
                
                $count++;
            }
        }

        return response()->json([
            'success' => true,
            'approved_count' => $count,
            'message' => "Successfully approved {$count} payroll(s)!"
        ]);

    } catch (\Exception $e) {
        Log::error('Approve selected failed: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Error: ' . $e->getMessage()
        ], 500);
    }
}

/**
 * Mark selected payrolls as paid
 */
public function markAsPaid(Request $request)
{
    try {
        $request->validate([
            'payroll_ids' => 'required|array',
            'payroll_ids.*' => 'exists:payrolls,id'
        ]);

        $count = 0;
        foreach ($request->payroll_ids as $payrollId) {
            $payroll = Payroll::find($payrollId);
            
            if ($payroll && $payroll->status === 'approved') {
                $payroll->update([
                    'status' => 'paid',
                    'paid_at' => now(),
                    'paid_by' => auth()->id(),
                ]);
                
                $count++;
            }
        }

        return response()->json([
            'success' => true,
            'marked_count' => $count,
            'message' => "Successfully marked {$count} payroll(s) as paid!"
        ]);

    } catch (\Exception $e) {
        Log::error('Mark as paid failed: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Error: ' . $e->getMessage()
        ], 500);
    }
}
    
 /**
 * Generate payslips for selected period (BULK) - FIXED ZIP
 */
/**
 * Generate payslips for selected period (BULK) - FIXED VERSION
 */
public function generatePayslips(Request $request)
{
    try {
        $user = Auth::user();
        
        // Authorization check
        if ($user && $user->role === 'employee') {
            return redirect()->back()->with('error', 'You are not authorized to generate payslips.');
        }
        
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
        ]);

        Log::info('Generating payslips for period', [
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
        ]);

        // Get payrolls for the period using BROADER matching
        $payrolls = Payroll::where(function($q) use ($request) {
            // Match payrolls that overlap with selected period
            $q->whereBetween('pay_period_start', [$request->start_date, $request->end_date])
              ->orWhereBetween('pay_period_end', [$request->start_date, $request->end_date])
              ->orWhere(function($subQ) use ($request) {
                  $subQ->where('pay_period_start', '<=', $request->start_date)
                       ->where('pay_period_end', '>=', $request->end_date);
              });
        })
        ->with('employee')
        ->get();
        
        if ($payrolls->isEmpty()) {
            return redirect()->back()
                ->with('error', 'No payrolls found for the selected period.')
                ->with('start_date', $request->start_date)
                ->with('end_date', $request->end_date);
        }
        
        $generated = 0;
        $failed = 0;
        $payrollsWithFiles = []; // Store payroll objects instead of just file paths
        
        // Generate individual payslips first
        foreach ($payrolls as $payroll) {
            try {
                // Use the service to generate payslip
                $fileUrl = $this->payrollService->generatePayslip($payroll);
                
                if ($fileUrl && $payroll->payslip_file) {
                    $generated++;
                    $payrollsWithFiles[] = $payroll; // Store the payroll object
                    Log::info('Payslip generated successfully', [
                        'payroll_id' => $payroll->id,
                        'file_path' => $payroll->payslip_file,
                        'employee_name' => $payroll->employee->full_name ?? 'Unknown'
                    ]);
                } else {
                    $failed++;
                    Log::error('Failed to generate payslip', ['payroll_id' => $payroll->id]);
                }
                
            } catch (\Exception $e) {
                $failed++;
                Log::error('Error generating payslip for payroll ' . $payroll->id . ': ' . $e->getMessage());
            }
        }
        
        if ($generated === 0) {
            return redirect()->back()
                ->with('error', 'No payslips could be generated. Please try again.')
                ->with('start_date', $request->start_date)
                ->with('end_date', $request->end_date);
        }
        
        // Create ZIP file with all generated payslips
        return $this->createPayslipZipFromPayrolls($payrollsWithFiles, $request);
            
    } catch (\Exception $e) {
        Log::error('generatePayslips error: ' . $e->getMessage());
        
        return redirect()->back()
            ->with('error', 'Error generating payslips: ' . $e->getMessage())
            ->with('start_date', $request->input('start_date', ''))
            ->with('end_date', $request->input('end_date', ''));
    }
}

/**
 * Create ZIP file from payroll objects (NEW METHOD - MORE RELIABLE)
 */
private function createPayslipZipFromPayrolls(array $payrolls, Request $request)
{
    try {
        $zipFileName = 'payslips_' . $request->start_date . '_to_' . $request->end_date . '.zip';
        $zipPath = storage_path('app/temp/' . $zipFileName);
        
        // Ensure temp directory exists
        $tempDir = storage_path('app/temp');
        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0755, true);
        }
        
        // Remove existing zip file if it exists
        if (file_exists($zipPath)) {
            @unlink($zipPath);
        }
        
        // Create ZIP archive
        $zip = new \ZipArchive();
        if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === TRUE) {
            $addedCount = 0;
            
            foreach ($payrolls as $payroll) {
                if ($payroll->payslip_file && \Illuminate\Support\Facades\Storage::exists($payroll->payslip_file)) {
                    // Get employee name safely
                    $employeeName = 'Unknown';
                    if ($payroll->employee) {
                        $employeeName = preg_replace('/[^A-Za-z0-9_-]/', '_', $payroll->employee->full_name);
                    }
                    
                    // Create a clean filename
                    $filenameInZip = sprintf(
                        "payslip_%s_%s_%s.pdf",
                        $employeeName,
                        $payroll->pay_period_start,
                        $payroll->pay_period_end
                    );
                    
                    // Ensure unique filenames in ZIP
                    $counter = 1;
                    $originalName = $filenameInZip;
                    while ($zip->locateName($filenameInZip) !== false) {
                        $filenameInZip = pathinfo($originalName, PATHINFO_FILENAME) . "_" . $counter . ".pdf";
                        $counter++;
                    }
                    
                    try {
                        // Get file content
                        $fileContent = \Illuminate\Support\Facades\Storage::get($payroll->payslip_file);
                        
                        // Add to ZIP
                        if ($zip->addFromString($filenameInZip, $fileContent)) {
                            $addedCount++;
                            Log::info('Added to ZIP: ' . $filenameInZip . ' (payroll ID: ' . $payroll->id . ')');
                        } else {
                            Log::warning('Failed to add file to ZIP: ' . $filenameInZip);
                        }
                    } catch (\Exception $e) {
                        Log::error('Error reading file for ZIP: ' . $e->getMessage());
                    }
                } else {
                    Log::warning('Payslip file not found for payroll ID: ' . $payroll->id);
                }
            }
            
            $zip->close();
            
            if ($addedCount === 0) {
                throw new \Exception('No files were added to ZIP archive.');
            }
            
        } else {
            throw new \Exception('Failed to create ZIP file. Check directory permissions.');
        }
        
        // Verify ZIP was created
        if (!file_exists($zipPath)) {
            throw new \Exception('ZIP file was not created: ' . $zipPath);
        }
        
        $fileSize = filesize($zipPath);
        if ($fileSize === 0) {
            throw new \Exception('ZIP file is empty (0 bytes).');
        }
        
        Log::info('ZIP file created successfully: ' . $zipPath . ', size: ' . $fileSize . ' bytes, files: ' . $addedCount);
        
        // Download the ZIP file
        return response()->download($zipPath, $zipFileName, [
            'Content-Type' => 'application/zip',
            'Content-Length' => $fileSize
        ])->deleteFileAfterSend(true);
        
    } catch (\Exception $e) {
        Log::error('Error creating payslip ZIP: ' . $e->getMessage());
        throw $e;
    }
}

/**
 * Export detailed data to CSV
 */
private function exportDetailedToCSV($payrolls, $filename)
{
    $filepath = storage_path('app/exports/' . $filename);
    
    // Ensure directory exists
    $directory = storage_path('app/exports');
    if (!is_dir($directory)) {
        mkdir($directory, 0755, true);
    }

    $handle = fopen($filepath, 'w');
    
    if (!$handle) {
        throw new \Exception('Unable to create CSV file: ' . $filepath);
    }
    
    // Add BOM for Excel UTF-8 support
    fwrite($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));
    
    // Headers
    $headers = [
        'Employee ID',
        'Employee Name',
        'Department',
        'Period Start',
        'Period End',
        'Basic Salary',
        'Overtime Pay',
        'Allowances',
        'Bonuses',
        'Deductions',
        'Tax Amount',
        'Gross Pay',
        'Net Pay',
        'Status'
    ];
    fputcsv($handle, $headers);
    
    foreach ($payrolls as $payroll) {
        $employee = $payroll->employee;
        
        if (!$employee) {
            continue;
        }
        
        $row = [
            $employee->employee_id ?? '',
            $employee->full_name ?? '',
            $employee->department->name ?? 'N/A',
            $payroll->pay_period_start,
            $payroll->pay_period_end,
            number_format($payroll->basic_salary, 2),
            number_format($payroll->overtime_pay ?? 0, 2),
            number_format($payroll->allowances ?? 0, 2),
            number_format($payroll->bonuses ?? 0, 2),
            number_format($payroll->deductions ?? 0, 2),
            number_format($payroll->tax_amount ?? 0, 2),
            number_format($payroll->gross_pay, 2),
            number_format($payroll->net_pay, 2),
            ucfirst($payroll->status)
        ];
        
        fputcsv($handle, $row);
    }
    
    fclose($handle);
    
    return 'exports/' . $filename;
}

/**
 * Export detailed data to XLSX
 */
private function exportDetailedToXLSX($payrolls, $filename)
{
    if (!class_exists('\PhpOffice\PhpSpreadsheet\Spreadsheet')) {
        throw new \Exception('PhpSpreadsheet not installed. Please install via composer: composer require phpoffice/phpspreadsheet');
    }

    $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    
    // Headers
    $headers = [
        'Employee ID',
        'Employee Name',
        'Department',
        'Period Start',
        'Period End',
        'Basic Salary',
        'Overtime Pay',
        'Allowances',
        'Bonuses',
        'Deductions',
        'Tax Amount',
        'Gross Pay',
        'Net Pay',
        'Status'
    ];
    
    // Set headers
    foreach ($headers as $colIndex => $header) {
        $column = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex + 1);
        $sheet->setCellValue($column . '1', $header);
        $sheet->getStyle($column . '1')->getFont()->setBold(true);
    }
    
    // Data rows
    $row = 2;
    foreach ($payrolls as $payroll) {
        $employee = $payroll->employee;
        
        if (!$employee) {
            continue;
        }
        
        $rowData = [
            $employee->employee_id ?? '',
            $employee->full_name ?? '',
            $employee->department->name ?? 'N/A',
            $payroll->pay_period_start,
            $payroll->pay_period_end,
            $payroll->basic_salary,
            $payroll->overtime_pay ?? 0,
            $payroll->allowances ?? 0,
            $payroll->bonuses ?? 0,
            $payroll->deductions ?? 0,
            $payroll->tax_amount ?? 0,
            $payroll->gross_pay,
            $payroll->net_pay,
            ucfirst($payroll->status)
        ];
        
        // Set data for each column
        foreach ($rowData as $colIndex => $value) {
            $column = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex + 1);
            $sheet->setCellValue($column . $row, $value);
        }
        
        $row++;
    }
    
    // Auto-size columns
    for ($col = 1; $col <= count($headers); $col++) {
        $column = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
        $sheet->getColumnDimension($column)->setAutoSize(true);
    }
    
    // Format currency columns (columns F to M)
    for ($col = 6; $col <= 13; $col++) {
        $column = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
        $sheet->getStyle($column . '2:' . $column . ($row - 1))
              ->getNumberFormat()
              ->setFormatCode('#,##0.00');
    }
    
    // Save file
    $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
    $filepath = storage_path('app/exports/' . $filename);
    $writer->save($filepath);
    
    return 'exports/' . $filename;
}

/**
 * Generate single payslip
 */
public function generateSinglePayslip(Request $request, $payrollId)
{
    try {
        $payroll = Payroll::findOrFail($payrollId);
        
        $result = $this->payrollService->generatePayslip($payroll);
        
        if ($result) {
            return redirect()->back()
                ->with('success', 'Payslip generated successfully!')
                ->with('payroll_id', $payroll->id);
        } else {
            return redirect()->back()
                ->with('error', 'Failed to generate payslip.');
        }
        
    } catch (\Exception $e) {
        Log::error('Error generating single payslip: ' . $e->getMessage());
        return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
    }
}

    /**
 * Download payslip
 */
// In PayrollController, update the downloadPayslip method:
public function downloadPayslip($payrollId)
{
    try {
        $payroll = Payroll::findOrFail($payrollId);
        $user = Auth::user();
        
        // Authorization check: Employees can only download their own payslips
        if ($user && $user->role === 'employee' && $user->employee) {
            if ($payroll->employee_id !== $user->employee->id) {
                return redirect()->back()->with('error', 'You are not authorized to download this payslip.');
            }
        }
        
        // Generate or get payslip
        if (!$payroll->payslip_file) {
            $service = app(PayrollGenerationService::class);
            $fileUrl = $service->generatePayslip($payroll);
            
            if (!$fileUrl) {
                throw new \Exception('Failed to generate payslip');
            }
            
            $payroll->refresh();
        }
        
        // Get file path
        $relativePath = str_replace('/storage/', '', $payroll->payslip_file);
        $filePath = storage_path('app/' . $relativePath);
        
        if (!file_exists($filePath)) {
            throw new \Exception('Payslip file not found: ' . $filePath);
        }
        
        // Create download filename
        $filename = 'payslip_' . $payroll->employee->employee_id . '_' . 
                   $payroll->pay_period_start . '_' . $payroll->pay_period_end . '.pdf';
        
        return response()->download($filePath, $filename, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"'
        ]);
        
    } catch (\Exception $e) {
        Log::error('Error downloading payslip: ' . $e->getMessage());
        return redirect()->back()->with('error', 'Error downloading payslip: ' . $e->getMessage());
    }
}

    /**
     * Get comprehensive attendance data for all employees in the period
     */
    private function getComprehensiveAttendanceData($startDate, $endDate, $employees)
    {
        $comprehensiveData = [];
        
        foreach ($employees as $employee) {
            $currentDate = $startDate->copy();
            
            while ($currentDate->lte($endDate)) {
                $dateStr = $currentDate->format('Y-m-d');
                
                // Get attendance record for this date
                $attendanceRecord = AttendanceRecord::where('employee_id', $employee->id)
                    ->where('date', $dateStr)
                    ->first();
                
                // Get schedule for this date
                $schedule = EmployeeSchedule::where('employee_id', $employee->id)
                    ->where('date', $dateStr)
                    ->first();
                
                // Determine schedule status
                $scheduleStatus = $this->getScheduleStatus($schedule);
                
                // Determine attendance status
                $attendanceStatus = $this->getAttendanceStatus($attendanceRecord, $schedule);
                
                // Initialize default values for non-working days
                $workedHours = '—';
                $scheduledHours = '—';
                $morningOvertime = 0;
                $eveningOvertime = 0;
                $overtime = 0;
                $nightDifferentialHours = 0;
                $lateMinutes = 0;
                $isNightShift = false;
                
                // Only calculate attendance metrics if schedule status is 'Working' or 'Regular Holiday' or 'Special Holiday'
                if (in_array($scheduleStatus, ['Working', 'Regular Holiday', 'Special Holiday'])) {
                    
                    // Calculate worked hours if attendance record exists
                    if ($attendanceRecord && $attendanceRecord->time_in && $attendanceRecord->time_out) {
                        $workedHours = $attendanceRecord->total_hours ?? 0;
                        $scheduledHours = $this->formatHours($workedHours);
                        
                        // Calculate overtime
                        if ($workedHours > 8) {
                            $overtime = $workedHours - 8;
                        }
                        
                        // Calculate night differential hours
                        $nightDifferentialHours = $attendanceRecord->calculateNightShiftHours();
                        $isNightShift = $nightDifferentialHours > 0;
                        
                        // Calculate late minutes
                        if ($attendanceRecord->isLate()) {
                            $lateMinutes = $attendanceRecord->late_minutes ?? 0;
                        }
                    } else {
                        // No attendance record - mark as absent
                        $attendanceStatus = 'Absent';
                        $scheduledHours = '—';
                    }
                } else {
                    // Non-working day
                    $scheduledHours = $scheduleStatus;
                }
                
                // Add to comprehensive data
                $comprehensiveData[] = [
                    'employee_id' => $employee->id,
                    'employee_name' => $employee->full_name,
                    'employee_id_number' => $employee->employee_id,
                    'department' => $employee->department->name ?? 'N/A',
                    'date' => $dateStr,
                    'date_formatted' => $currentDate->format('M j, Y'),
                    'day_of_week' => $currentDate->format('l'),
                    'schedule_status' => $scheduleStatus,
                    'attendance_status' => $attendanceStatus,
                    'scheduled_hours' => $scheduledHours,
                    'worked_hours' => $workedHours,
                    'overtime' => $overtime,
                    'morning_overtime' => $morningOvertime,
                    'evening_overtime' => $eveningOvertime,
                    'night_differential_hours' => $nightDifferentialHours,
                    'late_minutes' => $lateMinutes,
                    'is_night_shift' => $isNightShift,
                ];
                
                $currentDate->addDay();
            }
        }
        
        return $comprehensiveData;
    }
    
    /**
     * Determine schedule status for a given schedule
     */
    private function getScheduleStatus($schedule)
    {
        if (!$schedule) {
            return 'Day Off';
        }
        
        // Check if it's a holiday
        if ($schedule->is_holiday) {
            return $schedule->holiday_type === 'regular' ? 'Regular Holiday' : 'Special Holiday';
        }
        
        // Check if it's a leave day
        if ($schedule->is_leave) {
            return 'Leave';
        }
        
        // Check if it's a working day
        if ($schedule->is_working_day) {
            return 'Working';
        }
        
        return 'Day Off';
    }
    
    /**
     * Determine attendance status based on attendance record and schedule
     */
    private function getAttendanceStatus($attendanceRecord, $schedule)
    {
        if (!$attendanceRecord) {
            return 'Absent';
        }
        
        if ($attendanceRecord->time_in && $attendanceRecord->time_out) {
            $totalHours = $attendanceRecord->total_hours ?? 0;
            
            if ($totalHours < 4) {
                return 'Half Day';
            }
            
            if ($attendanceRecord->isLate()) {
                return 'Late';
            }
            
            return 'Present';
        }
        
        if ($attendanceRecord->time_in && !$attendanceRecord->time_out) {
            return 'Present (No Time Out)';
        }
        
        return 'Absent';
    }
    
    /**
     * Format hours for display
     */
    private function formatHours($hours)
    {
        if ($hours == 0) {
            return '—';
        }
        
        $wholeHours = floor($hours);
        $minutes = round(($hours - $wholeHours) * 60);
        
        if ($minutes == 0) {
            return $wholeHours . ' hrs';
        }
        
        return $wholeHours . ' hrs ' . $minutes . ' mins';
    }
}
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
        $query->where('latest_payrolls.pay_period_start', $request->start_date)
              ->where('latest_payrolls.pay_period_end', $request->end_date);
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
        
        return view('payrolls.create', compact('employees'));
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

        Payroll::create($request->all());

        return redirect()->route('payrolls.index')
            ->with('success', 'Payroll created successfully.');
    }

    public function show(Payroll $payroll)
    {
        $payroll->load('employee.department');
        return view('payrolls.show', compact('payroll'));
    }

    public function edit(Payroll $payroll)
    {
        $currentCompany = CompanyHelper::getCurrentCompany();
        
        $employeesQuery = Employee::query();
        if ($currentCompany) {
            $employeesQuery->forCompany($currentCompany->id);
        }
        $employees = $employeesQuery->get();
        
        return view('payrolls.edit', compact('payroll', 'employees'));
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

        $payroll->update($request->all());

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
                DB::raw('SUM(gross_pay) as total_gross_pay'),
                DB::raw('SUM(net_pay) as total_net_pay'),
                DB::raw('COUNT(*) as payroll_count'),
            ])
            ->where('status', 'approved')
            ->groupBy('year', 'month')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->limit(12)
            ->get();

        return view('payrolls.summary', compact('summary', 'monthly_data', 'startDate', 'endDate'));
    }

    public function monthlyReport(Request $request)
    {
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

        // Also get department-wise report
        $report = DB::table('payrolls')
            ->join('employees', 'payrolls.employee_id', '=', 'employees.id')
            ->join('departments', 'employees.department_id', '=', 'departments.id')
            ->select([
                'departments.name as department_name',
                DB::raw('COUNT(payrolls.id) as employee_count'),
                DB::raw('SUM(payrolls.gross_pay) as total_gross_pay'),
                DB::raw('SUM(payrolls.net_pay) as total_net_pay'),
            ])
            ->whereYear('payrolls.pay_period_start', $request->year)
            ->whereMonth('payrolls.pay_period_start', $request->month)
            ->where('payrolls.status', 'approved')
            ->groupBy('departments.id', 'departments.name')
            ->get();

        return view('payrolls.monthly', compact('payrolls', 'year', 'month', 'report'));
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
            'format' => 'required|in:csv,xlsx,pdf',
        ]);

        $period = $request->only(['start_date', 'end_date']);
        $employeeIds = $request->get('employee_ids', []);
        $format = $request->get('format', 'csv');

        Log::info('Exporting payroll data', [
            'period' => $period, 
            'employee_ids' => $employeeIds,
            'format' => $format
        ]);

        // Handle PDF export separately
        if ($format === 'pdf') {
            return $this->exportPayrollToPDF($request);
        }

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
        
        // Now get the full payroll records with relationships
        $query = Payroll::with(['employee', 'employee.department'])
            ->whereIn('id', $latestPayrollIds);
        
        // Filter by company
        if ($currentCompany) {
            $query->whereHas('employee', function($q) use ($currentCompany) {
                $q->where('company_id', $currentCompany->id);
            });
        }
        
        $payrolls = $query->orderBy('employee_id')->get();
        
        if ($payrolls->isEmpty()) {
            throw new \Exception('No payroll records found for the selected period.');
        }
        
        // Recalculate payrolls with zero values before export
        $payrollIds = $payrolls->pluck('id')->toArray();
        foreach ($payrolls as $payroll) {
            if (($payroll->basic_salary == 0 && $payroll->gross_pay == 0) && $payroll->employee) {
                $this->recalculatePayrollValues($payroll);
            }
        }
        
        // Refresh payrolls to get updated values
        $payrolls = Payroll::with(['employee', 'employee.department'])
            ->whereIn('id', $payrollIds)
            ->orderBy('employee_id')
            ->get();
        
        // Check if DomPDF is available
        if (!class_exists('\Barryvdh\DomPDF\Facade\Pdf')) {
            throw new \Exception('PDF generation library not available.');
        }
        
        // Generate PDF HTML
        $html = view('payroll.export-pdf', [
            'payrolls' => $payrolls,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'company' => $currentCompany
        ])->render();
        
        // Generate PDF
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html);
        $pdf->setPaper('a4', 'landscape');
        
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

// Add this method to PayrollController
public function exportDetailed(Request $request)
{
    try {
        $user = Auth::user();
        
        // Authorization check: Only admin/hr/manager can export detailed payroll
        if ($user && $user->role === 'employee') {
            return redirect()->back()->with('error', 'You are not authorized to export detailed payroll.');
        }
        
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'format' => 'nullable|in:csv,xlsx',
            'payroll_ids' => 'nullable|array',
            'payroll_ids.*' => 'exists:payrolls,id'
        ]);

        // Get payrolls with all necessary relationships
        $query = Payroll::with(['employee', 'employee.department'])
            ->where('pay_period_start', $request->start_date)
            ->where('pay_period_end', $request->end_date);

        if ($request->filled('payroll_ids')) {
            $query->whereIn('id', $request->payroll_ids);
        }

        $payrolls = $query->get();
        
        if ($payrolls->isEmpty()) {
            throw new \Exception('No payroll records found for the selected period.');
        }

        \Illuminate\Support\Facades\Log::info('Exporting detailed payroll data', [
            'count' => $payrolls->count(),
            'format' => $request->get('format', 'xlsx'),
            'period' => $request->start_date . ' to ' . $request->end_date,
            'employee_count' => $payrolls->pluck('employee_id')->unique()->count()
        ]);

        // Generate export file
        $filename = $this->payrollService->exportPayrollWithCalculations(
            $payrolls, 
            $request->get('format', 'xlsx')
        );

        $fullPath = storage_path('app/' . $filename);
        
        if (!file_exists($fullPath)) {
            throw new \Exception('Export file not found: ' . $fullPath);
        }

        // Determine content type
        $contentType = $request->get('format', 'xlsx') === 'csv' 
            ? 'text/csv' 
            : 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
        
        $downloadName = 'payroll_export_with_calculations_' . $request->start_date . '_to_' . $request->end_date . '.' . $request->get('format', 'xlsx');

        return response()->download($fullPath, $downloadName, [
            'Content-Type' => $contentType,
            'Content-Disposition' => 'attachment; filename="' . $downloadName . '"'
        ]);

    } catch (\Exception $e) {
        \Illuminate\Support\Facades\Log::error('Detailed export failed: ' . $e->getMessage(), [
            'request' => $request->all(),
            'trace' => $e->getTraceAsString()
        ]);
        
        return redirect()->back()
            ->with('error', 'Export failed: ' . $e->getMessage())
            ->with('start_date', $request->input('start_date'))
            ->with('end_date', $request->input('end_date'));
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
 * Generate payslips for selected period (BULK)
 */
public function generatePayslips(Request $request)
{
    try {
        $user = Auth::user();
        
        // Authorization check: Only admin/hr/manager can generate payslips
        if ($user && $user->role === 'employee') {
            return redirect()->back()->with('error', 'You are not authorized to generate payslips.');
        }
        
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'employee_ids' => 'nullable|array',
            'employee_ids.*' => 'exists:employees,id'
        ]);

        \Illuminate\Support\Facades\Log::info('Generating payslips for period', [
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'employee_ids' => $request->employee_ids
        ]);

        // Get payrolls for the period
        $query = Payroll::where('pay_period_start', '>=', $request->start_date)
            ->where('pay_period_end', '<=', $request->end_date);
            
        if ($request->filled('employee_ids')) {
            $query->whereIn('employee_id', $request->employee_ids);
        }
        
        $payrolls = $query->get();
        
        if ($payrolls->isEmpty()) {
            \Illuminate\Support\Facades\Log::warning('No payrolls found for period', [
                'start_date' => $request->start_date,
                'end_date' => $request->end_date
            ]);
            
            return redirect()->back()
                ->with('error', 'No payrolls found for the selected period.')
                ->with('start_date', $request->start_date)
                ->with('end_date', $request->end_date);
        }
        
        \Illuminate\Support\Facades\Log::info('Found ' . $payrolls->count() . ' payrolls to process');
        
        $generated = 0;
        $failed = 0;
        
        foreach ($payrolls as $payroll) {
            \Illuminate\Support\Facades\Log::info('Generating payslip for payroll: ' . $payroll->id);
            
            $result = $this->payrollService->generatePayslip($payroll);
            
            if ($result) {
                $generated++;
                \Illuminate\Support\Facades\Log::info('✅ Payslip generated for payroll: ' . $payroll->id);
            } else {
                $failed++;
                \Illuminate\Support\Facades\Log::error('❌ Failed to generate payslip for payroll: ' . $payroll->id);
            }
        }
        
        $message = "Generated {$generated} payslips successfully";
        if ($failed > 0) {
            $message .= ", {$failed} failed";
        }
        
        \Illuminate\Support\Facades\Log::info('Payslip generation completed', [
            'total' => $payrolls->count(),
            'generated' => $generated,
            'failed' => $failed
        ]);
        
        // Check if any payslips were generated
        if ($generated > 0) {
            // Instead of redirecting, return a download response
            return $this->downloadAllPayslips($request);
        } else {
            return redirect()->back()
                ->with('error', 'No payslips could be generated. Please check if payrolls exist for the selected period.')
                ->with('start_date', $request->start_date)
                ->with('end_date', $request->end_date);
        }
            
    } catch (\Exception $e) {
        \Illuminate\Support\Facades\Log::error('generatePayslips error: ' . $e->getMessage(), [
            'trace' => $e->getTraceAsString()
        ]);
        
        return redirect()->back()
            ->with('error', 'Error generating payslips: ' . $e->getMessage())
            ->with('start_date', $request->input('start_date', ''))
            ->with('end_date', $request->input('end_date', ''));
    }
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
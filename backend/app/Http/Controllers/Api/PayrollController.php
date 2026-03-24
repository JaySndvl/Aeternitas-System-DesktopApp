<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payroll;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Services\PayrollGenerationService;

class PayrollController extends Controller
{
    protected PayrollGenerationService $payrollService;

    public function __construct(PayrollGenerationService $payrollService)
    {
        // If you use API guards/middleware, apply them in routes/api.php
        $this->payrollService = $payrollService;
    }

    /**
     * GET /api/payrolls
     */
    public function index(Request $request)
    {
        $query = Payroll::with('employee.department');

        if ($request->has('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        if ($request->has('month')) {
            $query->whereMonth('pay_period_start', $request->month);
        }

        if ($request->has('year')) {
            $query->whereYear('pay_period_start', $request->year);
        }

        $payrolls = $query->orderBy('pay_period_start', 'desc')->paginate(15);
        return response()->json($payrolls);
    }

    /**
     * POST /api/payrolls
     */
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

        $payroll = Payroll::create($request->validated());

        return response()->json($payroll, 201);
    }

    /**
     * GET /api/payrolls/{payroll}
     */
    public function show(Payroll $payroll)
    {
        $payroll->load('employee.department');
        return response()->json($payroll);
    }

    /**
     * PUT/PATCH /api/payrolls/{payroll}
     */
    public function update(Request $request, Payroll $payroll)
    {
        $request->validate([
            'employee_id' => 'sometimes|required|exists:employees,id',
            'pay_period_start' => 'sometimes|required|date',
            'pay_period_end' => 'sometimes|required|date|after:pay_period_start',
            'basic_salary' => 'sometimes|required|numeric|min:0',
            'overtime_hours' => 'nullable|numeric|min:0',
            'overtime_rate' => 'nullable|numeric|min:0',
            'bonuses' => 'nullable|numeric|min:0',
            'deductions' => 'nullable|numeric|min:0',
            'tax_amount' => 'nullable|numeric|min:0',
        ]);

        $payroll->update($request->validated());

        return response()->json($payroll);
    }

    /**
     * DELETE /api/payrolls/{payroll}
     */
    public function destroy(Payroll $payroll)
    {
        $payroll->delete();
        return response()->json(null, 204);
    }

    /**
     * POST /api/payrolls/{payroll}/process
     *
     * This endpoint processes a single payroll (recalculates gross/net and marks processed).
     */
    public function process(Payroll $payroll)
    {
        // Recalculate gross and net using available payroll fields (defensive)
        $grossPay = ($payroll->basic_salary ?? 0) +
                    (($payroll->overtime_hours ?? 0) * ($payroll->overtime_rate ?? 0)) +
                    ($payroll->overtime_pay ?? 0) +
                    ($payroll->bonuses ?? 0) +
                    ($payroll->holiday_basic_pay ?? 0) +
                    ($payroll->holiday_premium ?? 0) +
                    ($payroll->night_differential_pay ?? 0) +
                    ($payroll->rest_day_premium_pay ?? 0);

        $netPay = $grossPay - ($payroll->deductions ?? 0) - ($payroll->tax_amount ?? 0);

        $payroll->update([
            'gross_pay' => $grossPay,
            'net_pay' => $netPay,
            'status' => 'processed',
            'processed_at' => now(),
        ]);

        return response()->json($payroll);
    }

    /**
     * GET /api/payrolls/reports/summary
     */
    public function summary()
    {
        $summary = DB::table('payrolls')
            ->select([
                DB::raw('COUNT(*) as total_payrolls'),
                DB::raw('SUM(gross_pay) as total_gross_pay'),
                DB::raw('SUM(net_pay) as total_net_pay'),
                DB::raw('AVG(gross_pay) as average_gross_pay'),
                DB::raw('AVG(net_pay) as average_net_pay'),
            ])
            ->where('status', 'processed')
            ->first();

        return response()->json($summary);
    }

    /**
     * GET /api/payrolls/reports/monthly
     */
    public function monthlyReport(Request $request)
    {
        $request->validate([
            'year' => 'required|integer|min:2020|max:2030',
            'month' => 'required|integer|min:1|max:12',
        ]);

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
            ->where('payrolls.status', 'processed')
            ->groupBy('departments.id', 'departments.name')
            ->get();

        return response()->json($report);
    }

    //
    // Integrations with PayrollGenerationService
    //

    /**
     * POST /api/payroll/preview
     */
    public function preview(Request $request)
    {
        $payload = $request->validate([
            'period.start_date' => 'required|date',
            'period.end_date' => 'required|date',
            'data' => 'required|array',
            'employee_ids' => 'array|nullable'
        ]);

        $period = $payload['period'];
        $data = $payload['data'];
        $employeeIds = $payload['employee_ids'] ?? null;

        $preview = $this->payrollService->generatePayrollPreview($period, $data, $employeeIds);

        return response()->json(['success' => true, 'preview' => $preview]);
    }

    /**
     * POST /api/payroll/generate
     */
    public function generate(Request $request)
    {
        // If you want to restrict to admins/hr, ensure middleware or policy is applied
        $payload = $request->validate([
            'period.start_date' => 'required|date',
            'period.end_date' => 'required|date',
            'data' => 'required|array',
            'employee_ids' => 'array|nullable'
        ]);

        $period = $payload['period'];
        $data = $payload['data'];
        $employeeIds = $payload['employee_ids'] ?? null;

        $created = $this->payrollService->generatePayroll($period, $data, $employeeIds);

        return response()->json(['success' => true, 'created_count' => count($created), 'created' => $created]);
    }

    /**
     * POST /api/payroll/approve
     */
    public function approveAll(Request $request)
    {
        // permission check example: $this->authorize('managePayroll');
        $payload = $request->validate([
            'period' => 'array|nullable',
            'employee_ids' => 'array|nullable'
        ]);

        $period = $payload['period'] ?? null;
        $employeeIds = $payload['employee_ids'] ?? null;
        $approvedBy = Auth::id();

        $count = $this->payrollService->approveAllPending($period, $employeeIds, $approvedBy);

        return response()->json(['success' => true, 'approved_count' => $count]);
    }

    /**
     * POST /api/payroll/process-payments
     */
    public function processPayments(Request $request)
    {
        $payload = $request->validate([
            'period' => 'array|nullable',
            'employee_ids' => 'array|nullable',
            'options' => 'array|nullable'
        ]);

        $period = $payload['period'] ?? null;
        $employeeIds = $payload['employee_ids'] ?? null;
        $options = $payload['options'] ?? [];

        $result = $this->payrollService->processPayments($period, $employeeIds, $options);

        return response()->json(['success' => true, 'result' => $result]);
    }

    /**
     * POST /api/payroll/generate-payslips
     */
    public function generatePayslips(Request $request)
    {
        $payload = $request->validate([
            'period' => 'array|nullable',
            'employee_ids' => 'array|nullable'
        ]);

        $period = $payload['period'] ?? null;
        $employeeIds = $payload['employee_ids'] ?? null;

        $files = $this->payrollService->generatePayslips($period, $employeeIds);

        $urls = array_map(function ($path) {
            return Storage::exists($path) ? Storage::url($path) : $path;
        }, $files);

        return response()->json(['success' => true, 'files' => $files, 'urls' => $urls]);
    }

    /**
     * GET /api/payroll/export
     */
    public function export(Request $request)
    {
        $periodStart = $request->query('period.start_date');
        $periodEnd = $request->query('period.end_date');
        $employeeIds = $request->query('employee_ids', null);
        $format = $request->query('format', 'csv');

        $period = null;
        if ($periodStart || $periodEnd) {
            $period = [
                'start_date' => $periodStart,
                'end_date' => $periodEnd
            ];
        }

        $path = $this->payrollService->exportPayroll($period, $employeeIds, $format);

        $url = Storage::exists($path) ? Storage::url($path) : null;

        return response()->json(['success' => true, 'path' => $path, 'url' => $url]);
    }
}
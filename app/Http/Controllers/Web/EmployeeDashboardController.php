<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Payroll;
use App\Models\Employee;
use App\Models\AttendanceRecord;
use App\Models\EmployeeSchedule;
use App\Helpers\CompanyHelper;
use App\Helpers\TimezoneHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class EmployeeDashboardController extends Controller
{
    /**
     * Display employee dashboard
     */
    public function index()
    {
        $user = Auth::user();
        
        // Get employee data
        $employee = $user->employee;
        
        if (!$employee) {
            abort(404, 'Employee record not found');
        }
        
        // Get today's attendance
        $today = TimezoneHelper::now();
        $todayAttendance = AttendanceRecord::where('employee_id', $employee->id)
            ->whereDate('date', $today->format('Y-m-d'))
            ->first();
        
        // Get recent payrolls
        $recent_payrolls = Payroll::where('employee_id', $employee->id)
            ->whereIn('status', ['approved', 'processed', 'paid'])
            ->orderBy('pay_period_start', 'desc')
            ->take(10)
            ->get();
        
        // Get yearly summary
        $yearly_summary = Payroll::where('employee_id', $employee->id)
            ->whereIn('status', ['approved', 'processed', 'paid'])
            ->selectRaw('YEAR(pay_period_start) as year, COUNT(*) as payroll_count, SUM(net_pay) as total_net_pay')
            ->groupBy('year')
            ->orderBy('year', 'desc')
            ->get();
        
        // Get recent activity
        $recentActivity = AttendanceRecord::where('employee_id', $employee->id)
            ->orderBy('date', 'desc')
            ->take(5)
            ->get();
        
        // Prepare stats
        $stats = [
            'employee_name' => $employee->full_name,
            'employee_id' => $employee->employee_id,
            'position' => $employee->position ?? 'N/A',
            'department' => $employee->department->name ?? 'N/A',
            'salary' => $employee->salary ?? 0,
            'hire_date' => $employee->hire_date ?? now(),
        ];
        
        return view('dashboard.employee', compact(
            'stats',
            'todayAttendance',
            'recent_payrolls',
            'yearly_summary',
            'recentActivity'
        ));
    }
    
    /**
     * Download employee payslip - SIMPLIFIED VERSION
     */
    public function downloadPayslip($payrollId)
    {
        try {
            $user = Auth::user();
            $employee = $user->employee;
            
            if (!$employee) {
                return response()->json(['error' => 'Employee not found'], 404);
            }
            
            // Get payroll with authorization check
            $payroll = Payroll::with(['employee', 'employee.department'])
                ->where('id', $payrollId)
                ->where('employee_id', $employee->id) // Ensure employee can only access their own
                ->firstOrFail();
            
            // Check if payroll is downloadable
            if (!in_array($payroll->status, ['approved', 'processed', 'paid'])) {
                return response()->json([
                    'error' => 'Payslip not available',
                    'message' => 'Payslip is not available for download yet. Status: ' . $payroll->status
                ], 400);
            }
            
            // Get company info
            $company = CompanyHelper::getCurrentCompany() ?? (object)[
                'name' => 'Aeternitas Company',
                'address' => 'Not specified',
                'contact' => 'Not specified'
            ];
            
            // Generate HTML
            $html = $this->generatePayslipHtml($payroll, $company);
            
            // Check if DomPDF is available
            if (!class_exists('\Barryvdh\DomPDF\Facade\Pdf')) {
                // Return HTML as fallback
                return response($html)
                    ->header('Content-Type', 'text/html')
                    ->header('Content-Disposition', 'attachment; filename="payslip_' . $payroll->employee->employee_id . '.html"');
            }
            
            // Generate PDF
            $pdf = Pdf::loadHTML($html);
            $pdf->setPaper('A4', 'portrait');
            
            // Set UTF-8 encoding
            $pdf->setOption('defaultFont', 'dejavusans');
            $pdf->setOption('isHtml5ParserEnabled', true);
            $pdf->setOption('isRemoteEnabled', true);
            $pdf->setOption('defaultEncoding', 'UTF-8');
            
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
            Log::error('Employee Payslip Download Error: ' . $e->getMessage());
            
            return response()->json([
                'error' => 'Failed to download payslip',
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Generate HTML for payslip - USING YOUR EXACT FUNCTION
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
     * AJAX function to get employee dashboard data
     */
    public function getDashboardData()
    {
        try {
            $user = Auth::user();
            $employee = $user->employee;
            
            if (!$employee) {
                return response()->json(['error' => 'Employee not found'], 404);
            }
            
            $today = TimezoneHelper::now();
            $todayAttendance = AttendanceRecord::where('employee_id', $employee->id)
                ->whereDate('date', $today->format('Y-m-d'))
                ->first();
            
            // Get latest available payroll for download
            $latestPayroll = Payroll::where('employee_id', $employee->id)
                ->whereIn('status', ['approved', 'processed', 'paid'])
                ->orderBy('pay_period_start', 'desc')
                ->first();
            
            return response()->json([
                'success' => true,
                'attendance' => $todayAttendance,
                'latest_payroll' => $latestPayroll,
                'has_payslip' => $latestPayroll ? true : false,
                'employee_name' => $employee->full_name
            ]);
            
        } catch (\Exception $e) {
            Log::error('Employee dashboard data error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to load data'], 500);
        }
    }
    
    /**
     * Test function to check if download works
     */
    public function testDownload($payrollId)
    {
        try {
            $user = Auth::user();
            $employee = $user->employee;
            
            if (!$employee) {
                return response()->json(['error' => 'Employee not found'], 404);
            }
            
            $payroll = Payroll::where('id', $payrollId)
                ->where('employee_id', $employee->id)
                ->first();
            
            if (!$payroll) {
                return response()->json([
                    'error' => 'Payroll not found or unauthorized',
                    'authorized' => false
                ], 404);
            }
            
            return response()->json([
                'success' => true,
                'authorized' => true,
                'payroll_id' => $payrollId,
                'employee_id' => $employee->id,
                'employee_name' => $employee->full_name,
                'payroll_status' => $payroll->status,
                'downloadable' => in_array($payroll->status, ['approved', 'processed', 'paid']),
                'message' => 'Download test successful'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Test failed',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
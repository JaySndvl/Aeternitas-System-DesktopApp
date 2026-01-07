<?php

namespace App\Services;

use App\Models\Payroll;
use App\Models\Employee;
use App\Models\AttendanceRecord;
use App\Models\EmployeeSchedule;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;
use App\Helpers\CompanyHelper;

class PayrollGenerationService
{
    /**
     * Generate payroll preview from comprehensive attendance data (without saving to database)
     *
     * @param array $periodData Period data from session
     * @param array $comprehensiveData Comprehensive attendance data from period management
     * @param array|null $employeeIds Optional array of employee IDs to process
     * @return array Preview payroll data
     */
    public function generatePayrollPreview(array $periodData, array $comprehensiveData, ?array $employeeIds = null): array
    {
        $generatedPayrolls = [];

        // Group comprehensive data by employee
        $groupedData = collect($comprehensiveData)->groupBy('employee_id');

        foreach ($groupedData as $employeeId => $employeeRecords) {
            // Filter by employee IDs if specified
            if ($employeeIds !== null && !in_array($employeeId, $employeeIds)) {
                continue;
            }

            $employee = Employee::find($employeeId);
            if (! $employee) {
                continue;
            }

            try {
                $payrollData = $this->calculatePayrollPreviewFromRecords($employee, $employeeRecords, $periodData);
                if ($payrollData) {
                    $generatedPayrolls[] = $payrollData;
                }
            } catch (\Throwable $e) {
                Log::error('generatePayrollPreview error', ['employee_id' => $employeeId, 'error' => $e->getMessage()]);
            }
        }

        return $generatedPayrolls;
    }

    /**
     * Generate payroll for a specific period using comprehensive attendance data and persist to DB
     *
     * @param array $periodData
     * @param array $comprehensiveData
     * @param array|null $employeeIds
     * @return array Created Payroll models
     * @throws \Exception
     */
    public function generatePayrollFromComprehensiveData(array $periodData, array $comprehensiveData, ?array $employeeIds = null): array
    {
        try {
            DB::beginTransaction();

            $groupedData = collect($comprehensiveData)->groupBy('employee_id');

            if (!empty($employeeIds)) {
                $groupedData = $groupedData->filter(function ($records, $employeeId) use ($employeeIds) {
                    return in_array($employeeId, $employeeIds);
                });
            }

            $generatedPayrolls = [];

            foreach ($groupedData as $employeeId => $employeeRecords) {
                $employee = Employee::find($employeeId);
                if (! $employee) {
                    continue;
                }

                try {
                    $payroll = $this->calculatePayrollFromRecords($employee, $employeeRecords, $periodData);
                    if ($payroll) {
                        $generatedPayrolls[] = $payroll;
                    }
                } catch (\Throwable $e) {
                    Log::error('calculatePayrollFromRecords error', ['employee_id' => $employeeId, 'error' => $e->getMessage()]);
                }
            }

            DB::commit();

            return $generatedPayrolls;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Payroll generation failed: ' . $e->getMessage());
            throw $e;
        }
    }

// Add this method to debug the PDF generation
public function testPdfGeneration(Payroll $payroll)
{
    try {
        echo "=== Starting PDF Test ===\n";
        
        // 1. Check employee
        $employee = Employee::find($payroll->employee_id);
        echo "1. Employee found: " . ($employee ? '✅ ' . $employee->full_name : '❌ No') . "\n";
        
        if (!$employee) {
            return null;
        }
        
        // 2. Check view
        $viewPath = 'payslips.pdf';
        $viewExists = \Illuminate\Support\Facades\View::exists($viewPath);
        echo "2. View exists: " . ($viewExists ? '✅ Yes' : '❌ No') . "\n";
        
        if (!$viewExists) {
            // Create a simple view
            $html = '<h1>Test PDF</h1><p>Employee: ' . $employee->full_name . '</p>';
        } else {
            $html = \Illuminate\Support\Facades\View::make($viewPath, [
                'payroll' => $payroll,
                'employee' => $employee,
                'company' => (object)['name' => 'Test Company'],
                'today' => now()->format('F j, Y')
            ])->render();
        }
        
        echo "3. HTML generated: " . strlen($html) . " bytes\n";
        
        // 3. Check DomPDF
        $dompdfExists = class_exists('\Barryvdh\DomPDF\Facade\Pdf');
        echo "4. DomPDF exists: " . ($dompdfExists ? '✅ Yes' : '❌ No') . "\n";
        
        if (!$dompdfExists) {
            return null;
        }
        
        // 4. Generate PDF
        echo "5. Generating PDF...\n";
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html);
        $pdf->setPaper('A4', 'portrait');
        
        try {
            $content = $pdf->output();
            echo "6. PDF generated: " . strlen($content) . " bytes\n";
        } catch (\Exception $e) {
            echo "6. PDF generation failed: " . $e->getMessage() . "\n";
            return null;
        }
        
        // 5. Save to storage
        $dir = "payslips/test";
        echo "7. Creating directory: " . $dir . "\n";
        
        \Illuminate\Support\Facades\Storage::makeDirectory($dir);
        
        $filename = $dir . "/test_" . $payroll->id . ".pdf";
        echo "8. Saving file: " . $filename . "\n";
        
        $saved = \Illuminate\Support\Facades\Storage::put($filename, $content);
        echo "9. File saved: " . ($saved ? '✅ Yes' : '❌ No') . "\n";
        
        if ($saved) {
            $url = \Illuminate\Support\Facades\Storage::url($filename);
            echo "10. File URL: " . $url . "\n";
            return $url;
        }
        
        return null;
        
    } catch (\Exception $e) {
        echo "ERROR: " . $e->getMessage() . "\n";
        echo "TRACE: " . $e->getTraceAsString() . "\n";
        return null;
    }
}

    /**
     * Generate payslips for multiple payrolls (BULK method - accepts arrays)
     *
     * @param array|null $periodData
     * @param array|null $employeeIds
     * @return array list of saved file paths
     */
// Replace your current generatePayslip method with this:


private function generateSimplePayslipHTML(Payroll $payroll, Employee $employee): string
{
    return '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="utf-8">
        <title>Payslip - ' . htmlspecialchars($employee->full_name) . '</title>
        <style>
            body { font-family: Arial, sans-serif; margin: 20px; }
            .header { text-align: center; margin-bottom: 20px; }
            .table { width: 100%; border-collapse: collapse; margin: 10px 0; }
            .table th, .table td { padding: 8px; border: 1px solid #ddd; text-align: left; }
            .total { font-weight: bold; background-color: #f5f5f5; }
        </style>
    </head>
    <body>
        <div class="header">
            <h1>Aeternitas Company</h1>
            <h2>PAYSLIP</h2>
        </div>
        
        <div>
            <p><strong>Employee:</strong> ' . htmlspecialchars($employee->full_name) . '</p>
            <p><strong>Employee ID:</strong> ' . htmlspecialchars($employee->employee_id) . '</p>
            <p><strong>Period:</strong> ' . $payroll->pay_period_start . ' to ' . $payroll->pay_period_end . '</p>
        </div>
        
        <table class="table">
            <tr><th>Earnings</th><th>Amount</th></tr>
            <tr><td>Basic Salary</td><td>₱' . number_format($payroll->basic_salary, 2) . '</td></tr>
            ' . ($payroll->overtime_pay > 0 ? '<tr><td>Overtime Pay</td><td>₱' . number_format($payroll->overtime_pay, 2) . '</td></tr>' : '') . '
            ' . ($payroll->bonuses > 0 ? '<tr><td>Bonuses</td><td>₱' . number_format($payroll->bonuses, 2) . '</td></tr>' : '') . '
            <tr class="total"><td>Total Earnings</td><td>₱' . number_format($payroll->gross_pay, 2) . '</td></tr>
        </table>
        
        <table class="table">
            <tr><th>Deductions</th><th>Amount</th></tr>
            ' . ($payroll->deductions > 0 ? '<tr><td>Deductions</td><td>₱' . number_format($payroll->deductions, 2) . '</td></tr>' : '') . '
            ' . ($payroll->tax_amount > 0 ? '<tr><td>Tax</td><td>₱' . number_format($payroll->tax_amount, 2) . '</td></tr>' : '') . '
            <tr class="total"><td>Total Deductions</td><td>₱' . number_format($payroll->deductions + $payroll->tax_amount, 2) . '</td></tr>
        </table>
        
        <div style="text-align: center; padding: 20px; border: 2px solid #000; margin: 20px 0;">
            <h2>NET PAY: ₱' . number_format($payroll->net_pay, 2) . '</h2>
        </div>
        
        <div style="text-align: center; font-size: 12px; margin-top: 40px;">
            <p>Generated on ' . now()->format('F j, Y') . '</p>
        </div>
    </body>
    </html>';
}

public function debugPayslipGeneration(Payroll $payroll)
{
    Log::channel('single')->debug('=== START DEBUG ===');
    
    try {
        // Step 1: Find employee
        $employee = Employee::find($payroll->employee_id);
        Log::debug('Step 1 - Employee found: ' . ($employee ? 'Yes' : 'No'));
        
        if (!$employee) {
            return null;
        }
        
        // Step 2: Check view
        $viewPath = 'payslips.pdf';
        $viewExists = View::exists($viewPath);
        Log::debug('Step 2 - View exists: ' . ($viewExists ? 'Yes' : 'No'));
        
        // Step 3: Generate HTML
        $html = View::make($viewPath, [
            'payroll' => $payroll,
            'employee' => $employee,
            'company' => CompanyHelper::getCurrentCompany(),
            'today' => now()->format('F j, Y')
        ])->render();
        
        Log::debug('Step 3 - HTML generated: ' . strlen($html) . ' bytes');
        
        // Step 4: Check DomPDF
        $dompdfExists = class_exists('\Barryvdh\DomPDF\Facade\Pdf');
        Log::debug('Step 4 - DomPDF exists: ' . ($dompdfExists ? 'Yes' : 'No'));
        
        if (!$dompdfExists) {
            return null;
        }
        
        // Step 5: Generate PDF
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html);
        $content = $pdf->output();
        Log::debug('Step 5 - PDF generated: ' . strlen($content) . ' bytes');
        
        // Step 6: Save to storage
        $dir = "payslips/test";
        Storage::makeDirectory($dir);
        Log::debug('Step 6 - Directory created');
        
        $filename = "{$dir}/test_{$payroll->id}.pdf";
        $saved = Storage::put($filename, $content);
        Log::debug('Step 7 - File saved: ' . ($saved ? 'Yes' : 'No'));
        
        if ($saved) {
            $url = Storage::url($filename);
            Log::debug('Step 8 - URL: ' . $url);
            return $url;
        }
        
    } catch (\Exception $e) {
        Log::debug('ERROR: ' . $e->getMessage());
        Log::debug('TRACE: ' . $e->getTraceAsString());
    }
    
    Log::debug('=== END DEBUG ===');
    return null;
}

    /**
     * Recalculate payroll values from employee data for export
     */
    private function recalculatePayrollForExport(Payroll $payroll)
    {
        $employee = $payroll->employee;
        if (!$employee) {
            Log::warning("Payroll {$payroll->id} has no employee relationship");
            return;
        }
        
        // Get rates from payroll or employee - prioritize employee salary
        $monthlyRate = $payroll->monthly_rate ?? $employee->salary ?? 0;
        
        // If still zero, try to get from employee directly
        if ($monthlyRate == 0 && $employee->salary > 0) {
            $monthlyRate = $employee->salary;
        }
        
        // Log for debugging
        Log::info("Recalculating payroll {$payroll->id}", [
            'employee_id' => $employee->id,
            'employee_salary' => $employee->salary,
            'payroll_monthly_rate' => $payroll->monthly_rate,
            'calculated_monthly_rate' => $monthlyRate,
        ]);
        
        // If employee has no salary, we can't calculate
        if ($monthlyRate == 0) {
            Log::warning("Cannot recalculate payroll {$payroll->id}: Employee {$employee->id} has no salary");
            return;
        }
        
        $semiMonthlyRate = $payroll->semi_monthly_rate ?? ($monthlyRate / 2);
        $dailyRate = $payroll->daily_rate ?? $employee->daily_rate ?? ($monthlyRate / 26);
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
                $startDate = Carbon::parse($payroll->pay_period_start);
                $endDate = Carbon::parse($payroll->pay_period_end);
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
        
        // Always update if we calculated values (even if they're still zero, update the rates)
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
        
        Log::info("Updated payroll {$payroll->id}", [
            'basic_salary' => $basicSalary,
            'gross_pay' => $grossPay,
            'net_pay' => $netPay,
        ]);
    }

    /**
     * Export payroll data to CSV/Excel
     *
     * @param array|null $periodData
     * @param array|null $employeeIds
     * @param string $format csv or xlsx
     * @return string Path to exported file
     */
  public function exportPayrollToExcel(?array $periodData = null, ?array $employeeIds = null, string $format = 'csv'): string
{
    // Use window function to get latest payroll per employee per period (same as index page)
    $latestPayrollsSubquery = DB::table('payrolls as p1')
        ->select(
            'p1.id',
            'p1.employee_id',
            'p1.pay_period_start',
            'p1.pay_period_end',
            'p1.status',
            'p1.basic_salary',
            'p1.overtime_pay',
            'p1.overtime_hours',
            'p1.overtime_rate',
            'p1.allowances',
            'p1.bonuses',
            'p1.deductions',
            'p1.tax_amount',
            'p1.net_pay',
            'p1.gross_pay',
            'p1.night_differential_hours',
            'p1.night_differential_rate',
            'p1.night_differential_pay',
            'p1.rest_day_premium_pay',
            'p1.sss_contribution',
            'p1.phic_contribution',
            'p1.hdmf_contribution',
            'p1.approved_at',
            'p1.paid_at',
            'p1.created_at',
            DB::raw('ROW_NUMBER() OVER (PARTITION BY p1.employee_id, p1.pay_period_start, p1.pay_period_end ORDER BY p1.created_at DESC) as rn')
        );
    
    // Build date filter for subquery
    $subqueryBindings = [];
    if (!empty($periodData['start_date']) && !empty($periodData['end_date'])) {
        $latestPayrollsSubquery->where(function($q) use ($periodData) {
            $q->where(function($subQ) use ($periodData) {
                $subQ->whereBetween('p1.pay_period_start', [$periodData['start_date'], $periodData['end_date']]);
            })->orWhere(function($subQ) use ($periodData) {
                $subQ->whereBetween('p1.pay_period_end', [$periodData['start_date'], $periodData['end_date']]);
            })->orWhere(function($subQ) use ($periodData) {
                $subQ->where('p1.pay_period_start', '<=', $periodData['start_date'])
                     ->where('p1.pay_period_end', '>=', $periodData['end_date']);
            })->orWhere(function($subQ) use ($periodData) {
                $subQ->where('p1.pay_period_start', $periodData['start_date'])
                     ->where('p1.pay_period_end', $periodData['end_date']);
            });
        });
    } elseif (!empty($periodData['start_date'])) {
        $latestPayrollsSubquery->where('p1.pay_period_start', '>=', $periodData['start_date']);
    } elseif (!empty($periodData['end_date'])) {
        $latestPayrollsSubquery->where('p1.pay_period_end', '<=', $periodData['end_date']);
    }
    
    if (!empty($employeeIds)) {
        $latestPayrollsSubquery->whereIn('p1.employee_id', $employeeIds);
    }

    // Get only the latest payroll per employee per period
    $latestPayrollsQuery = DB::table(DB::raw("({$latestPayrollsSubquery->toSql()}) as latest_payrolls"))
        ->mergeBindings($latestPayrollsSubquery)
        ->where('latest_payrolls.rn', 1)
        ->select('latest_payrolls.*');
    
    $latestPayrollIds = $latestPayrollsQuery->pluck('id')->toArray();
    
    // Now get the full payroll records with relationships
    $query = Payroll::with(['employee', 'employee.department'])
        ->whereIn('id', $latestPayrollIds);
    
    $payrolls = $query->orderBy('pay_period_start', 'desc')
                      ->orderBy('employee_id')
                      ->get();

    if ($payrolls->isEmpty()) {
        $dateRange = !empty($periodData['start_date']) && !empty($periodData['end_date']) 
            ? "from {$periodData['start_date']} to {$periodData['end_date']}" 
            : "for the selected period";
        throw new \Exception("No payroll records found {$dateRange}. Please generate payrolls first or select a different date range.");
    }

    // Recalculate payrolls with zero values before export
    $payrollIds = $payrolls->pluck('id')->toArray();
    $recalculatedCount = 0;
    foreach ($payrolls as $payroll) {
        // Always try to recalculate if values are zero, even if employee relationship exists
        if ($payroll->basic_salary == 0 && $payroll->gross_pay == 0) {
            // Ensure employee relationship is loaded
            if (!$payroll->relationLoaded('employee')) {
                $payroll->load('employee');
            }
            
            if ($payroll->employee) {
                $this->recalculatePayrollForExport($payroll);
                $recalculatedCount++;
            } else {
                Log::warning("Payroll {$payroll->id} has no employee - cannot recalculate");
            }
        }
    }
    
    Log::info("Recalculated {$recalculatedCount} payrolls before export");
    
    // Refresh payrolls to get updated values
    $payrolls = Payroll::with(['employee', 'employee.department'])
        ->whereIn('id', $payrollIds)
        ->orderBy('pay_period_start', 'desc')
        ->orderBy('employee_id')
        ->get();

    // Log for debugging
    Log::info('Exporting payroll data', [
        'total_payrolls' => $payrolls->count(),
        'period' => $periodData,
        'employee_ids' => $employeeIds
    ]);

    if ($format === 'csv') {
        return $this->exportToCSV($payrolls);
    } elseif ($format === 'xlsx') {
        return $this->exportToXLSX($payrolls);
    } else {
        throw new \InvalidArgumentException('Unsupported format: ' . $format);
    }
}

    /**
     * Export to CSV format
     */
private function exportToCSV($payrolls): string
{
    $filename = 'payroll_export_' . date('Ymd_His') . '.csv';
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
    
    // Comprehensive headers matching your requirements
    $headers = [
        'Employee ID',
        'Employee Name',
        'Department',
        'Pay Period Start',
        'Pay Period End',
        'Monthly Rate',
        'Semi-Monthly Rate',
        'Daily Rate',
        'Hourly Rate',
        'Days Worked',
        'Basic Salary',
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
        'SSS Contribution',
        'PHIC Contribution',
        'HDMF Contribution',
        'Status',
        'Approved Date',
        'Paid Date'
    ];
    fputcsv($handle, $headers);

    // Data rows
    foreach ($payrolls as $payroll) {
        $employee = $payroll->employee;
        
        if (!$employee) {
            continue; // Skip if no employee
        }
        
        // Get rates - prioritize employee salary, then payroll rates
        $monthlyRate = $payroll->monthly_rate ?? $employee->salary ?? 0;
        $semiMonthlyRate = $payroll->semi_monthly_rate ?? ($monthlyRate / 2);
        $dailyRate = $payroll->daily_rate ?? $employee->daily_rate ?? ($monthlyRate / 26);
        $hourlyRate = $payroll->hourly_rate ?? $employee->hourly_rate ?? ($dailyRate / 8);
        $overtimeRate = $payroll->overtime_rate ?? ($hourlyRate * 1.25);
        $nightDiffRate = $payroll->night_differential_rate ?? ($hourlyRate * 0.10);
        
        // Calculate basic salary if zero
        $basicSalary = $payroll->basic_salary;
        if ($basicSalary == 0) {
            if ($semiMonthlyRate > 0) {
                $basicSalary = $semiMonthlyRate;
            } elseif ($monthlyRate > 0) {
                $basicSalary = $monthlyRate / 2;
            } elseif ($dailyRate > 0) {
                // Calculate based on pay period days
                $startDate = Carbon::parse($payroll->pay_period_start);
                $endDate = Carbon::parse($payroll->pay_period_end);
                $daysInPeriod = $startDate->diffInDays($endDate) + 1;
                $basicSalary = $dailyRate * min($daysInPeriod, 15); // Semi-monthly typically 15 days
            }
        }
        
        // Calculate overtime pay
        $overtimePay = $payroll->overtime_pay;
        if ($overtimePay == 0 && ($payroll->overtime_hours ?? 0) > 0) {
        $overtimePay = ($payroll->overtime_hours ?? 0) * $overtimeRate;
        }
        
        // Calculate night differential pay
        $nightDiffPay = $payroll->night_differential_pay;
        if ($nightDiffPay == 0 && ($payroll->night_differential_hours ?? 0) > 0) {
        $nightDiffPay = ($payroll->night_differential_hours ?? 0) * $nightDiffRate;
        }
        
        // Get other components
        $restDayPremium = $payroll->rest_day_premium_pay ?? 0;
        $allowances = $payroll->allowances ?? 0;
        $bonuses = $payroll->bonuses ?? 0;
        $deductions = $payroll->deductions ?? 0;
        $taxAmount = $payroll->tax_amount ?? 0;
        
        // Calculate gross pay
        $grossPay = $payroll->gross_pay;
        if ($grossPay == 0) {
            $grossPay = $basicSalary + $overtimePay + $nightDiffPay + 
                       $restDayPremium + $allowances + $bonuses;
        }
        
        // Calculate net pay
        $netPay = $payroll->net_pay;
        if ($netPay == 0) {
            $netPay = $grossPay - $deductions - $taxAmount;
        }
        
        $row = [
            $employee->employee_id ?? '',
            $employee->full_name ?? '',
            $employee->department->name ?? 'N/A',
            $payroll->pay_period_start,
            $payroll->pay_period_end,
            number_format($monthlyRate, 2),
            number_format($semiMonthlyRate, 2),
            number_format($dailyRate, 2),
            number_format($hourlyRate, 2),
            $this->calculateDaysWorked($payroll),
            number_format($basicSalary, 2), // Use calculated basic salary
            number_format($payroll->overtime_hours ?? 0, 2),
            number_format($overtimeRate, 2),
            number_format($overtimePay, 2), // Use calculated overtime pay
            number_format($payroll->night_differential_hours ?? 0, 2),
            number_format($nightDiffRate, 2),
            number_format($nightDiffPay, 2), // Use calculated night diff pay
            number_format($restDayPremium, 2),
            number_format($allowances, 2),
            number_format($bonuses, 2),
            number_format($deductions, 2),
            number_format($taxAmount, 2),
            number_format($grossPay, 2), // Use calculated gross pay
            number_format($netPay, 2), // Use calculated net pay
            number_format($payroll->sss_contribution ?? 450, 2),
            number_format($payroll->phic_contribution ?? 225.88, 2),
            number_format($payroll->hdmf_contribution ?? 100, 2),
            ucfirst($payroll->status),
            $payroll->approved_at ? $payroll->approved_at->format('Y-m-d H:i:s') : '',
            $payroll->paid_at ? $payroll->paid_at->format('Y-m-d H:i:s') : ''
        ];
        fputcsv($handle, $row);
    }
    
    // Calculate totals from actual calculated values (recalculate for totals)
    $totalBasicSalary = 0;
    $totalOvertimePay = 0;
    $totalAllowances = 0;
    $totalBonuses = 0;
    $totalDeductions = 0;
    $totalTaxAmount = 0;
    $totalGrossPay = 0;
    $totalNetPay = 0;
    
    foreach ($payrolls as $payroll) {
        $employee = $payroll->employee;
        if (!$employee) continue;
        
        $monthlyRate = $payroll->monthly_rate ?? $employee->salary ?? 0;
        $semiMonthlyRate = $payroll->semi_monthly_rate ?? ($monthlyRate / 2);
        $dailyRate = $payroll->daily_rate ?? $employee->daily_rate ?? ($monthlyRate / 26);
        $hourlyRate = $payroll->hourly_rate ?? $employee->hourly_rate ?? ($dailyRate / 8);
        $overtimeRate = $payroll->overtime_rate ?? ($hourlyRate * 1.25);
        
        $basicSalary = $payroll->basic_salary;
        if ($basicSalary == 0) {
            $basicSalary = $semiMonthlyRate > 0 ? $semiMonthlyRate : ($monthlyRate > 0 ? $monthlyRate / 2 : 0);
        }
        
        $overtimePay = $payroll->overtime_pay;
        if ($overtimePay == 0 && ($payroll->overtime_hours ?? 0) > 0) {
            $overtimePay = ($payroll->overtime_hours ?? 0) * $overtimeRate;
        }
        
        $grossPay = $payroll->gross_pay;
        if ($grossPay == 0) {
            $grossPay = $basicSalary + $overtimePay + ($payroll->night_differential_pay ?? 0) + 
                       ($payroll->rest_day_premium_pay ?? 0) + ($payroll->allowances ?? 0) + ($payroll->bonuses ?? 0);
        }
        
        $netPay = $payroll->net_pay;
        if ($netPay == 0) {
            $netPay = $grossPay - ($payroll->deductions ?? 0) - ($payroll->tax_amount ?? 0);
        }
        
        $totalBasicSalary += $basicSalary;
        $totalOvertimePay += $overtimePay;
        $totalAllowances += $payroll->allowances ?? 0;
        $totalBonuses += $payroll->bonuses ?? 0;
        $totalDeductions += $payroll->deductions ?? 0;
        $totalTaxAmount += $payroll->tax_amount ?? 0;
        $totalGrossPay += $grossPay;
        $totalNetPay += $netPay;
    }
    
    // Add summary/total row
    $totals = [
        '',
        '',
        'TOTAL:',
        '',
        '',
        '',
        '',
        '',
        '',
        '',
        number_format($totalBasicSalary, 2),
        '',
        '',
        number_format($totalOvertimePay, 2),
        '',
        '',
        '',
        '',
        number_format($totalAllowances, 2),
        number_format($totalBonuses, 2),
        number_format($totalDeductions, 2),
        number_format($totalTaxAmount, 2),
        number_format($totalGrossPay, 2),
        number_format($totalNetPay, 2),
        '',
        '',
        '',
        '',
        '',
        ''
    ];
    fputcsv($handle, $totals);

    fclose($handle);

    return 'exports/' . $filename;
}

    /**
     * Export to XLSX format (using PhpSpreadsheet if available)
     */
    private function exportToXLSX($payrolls): string
    {
        if (!class_exists('\PhpOffice\PhpSpreadsheet\Spreadsheet')) {
            throw new \Exception('PhpSpreadsheet not installed. Please install via composer: composer require phpoffice/phpspreadsheet');
        }

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Headers
        $headers = [
            'A' => 'Payroll ID',
            'B' => 'Employee ID',
            'C' => 'Employee Name',
            'D' => 'Department',
            'E' => 'Period Start',
            'F' => 'Period End',
            'G' => 'Basic Salary',
            'H' => 'Overtime Hours',
            'I' => 'Overtime Pay',
            'J' => 'Bonuses',
            'K' => 'Deductions',
            'L' => 'Tax Amount',
            'M' => 'Gross Pay',
            'N' => 'Net Pay',
            'O' => 'Status',
            'P' => 'Approved Date',
            'Q' => 'Paid Date'
        ];

        foreach ($headers as $col => $header) {
            $sheet->setCellValue($col . '1', $header);
            $sheet->getStyle($col . '1')->getFont()->setBold(true);
        }

        // Data rows
        $row = 2;
        foreach ($payrolls as $payroll) {
            $sheet->setCellValue('A' . $row, $payroll->id);
            $sheet->setCellValue('B' . $row, $payroll->employee->employee_id ?? '');
            $sheet->setCellValue('C' . $row, $payroll->employee->full_name ?? ($payroll->employee->first_name . ' ' . $payroll->employee->last_name));
            $sheet->setCellValue('D' . $row, $payroll->employee->department->name ?? 'N/A');
            $sheet->setCellValue('E' . $row, $payroll->pay_period_start);
            $sheet->setCellValue('F' . $row, $payroll->pay_period_end);
            $sheet->setCellValue('G' . $row, $payroll->basic_salary);
            $sheet->setCellValue('H' . $row, $payroll->overtime_hours ?? 0);
            $sheet->setCellValue('I' . $row, ($payroll->overtime_hours ?? 0) * ($payroll->overtime_rate ?? 0));
            $sheet->setCellValue('J' . $row, $payroll->bonuses ?? 0);
            $sheet->setCellValue('K' . $row, $payroll->deductions ?? 0);
            $sheet->setCellValue('L' . $row, $payroll->tax_amount ?? 0);
            $sheet->setCellValue('M' . $row, $payroll->gross_pay ?? 0);
            $sheet->setCellValue('N' . $row, $payroll->net_pay ?? 0);
            $sheet->setCellValue('O' . $row, ucfirst($payroll->status));
            $sheet->setCellValue('P' . $row, $payroll->approved_at ? $payroll->approved_at->format('Y-m-d H:i:s') : '');
            $sheet->setCellValue('Q' . $row, $payroll->paid_at ? $payroll->paid_at->format('Y-m-d H:i:s') : '');
            
            // Format currency columns
            $currencyColumns = ['G', 'I', 'J', 'K', 'L', 'M', 'N'];
            foreach ($currencyColumns as $col) {
                $sheet->getStyle($col . $row)
                      ->getNumberFormat()
                      ->setFormatCode('#,##0.00');
            }
            
            $row++;
        }

        // Auto size columns
        foreach (range('A', 'Q') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        $filename = 'payroll_export_' . date('Ymd_His') . '.xlsx';
        $filepath = storage_path('app/exports/' . $filename);
        
        // Ensure directory exists
        $directory = storage_path('app/exports');
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save($filepath);

        // Verify file was created
        if (!file_exists($filepath)) {
            throw new \Exception('XLSX file was not created: ' . $filepath);
        }

        return 'exports/' . $filename;
    }

    /**
     * Alias for generatePayrollFromComprehensiveData
     */
    public function generatePayroll(array $periodData, array $comprehensiveData, ?array $employeeIds = null): array
    {
        return $this->generatePayrollFromComprehensiveData($periodData, $comprehensiveData, $employeeIds);
    }

    /**
     * Approve all pending payrolls optionally filtered by period or employee IDs.
     */
    public function approveAllPending(?array $periodData = null, ?array $employeeIds = null, ?string $approvedBy = null): int
    {
        return DB::transaction(function () use ($periodData, $employeeIds, $approvedBy) {
            $query = Payroll::where('status', 'pending');

            // Add date filtering
            if (!empty($periodData['start_date'])) {
                $query->where('pay_period_start', '>=', $periodData['start_date']);
            }
            if (!empty($periodData['end_date'])) {
                $query->where('pay_period_end', '<=', $periodData['end_date']);
            }
            
            // Add employee filtering
            if (!empty($employeeIds)) {
                $query->whereIn('employee_id', $employeeIds);
            }

            $payrolls = $query->get();
            
            if ($payrolls->isEmpty()) {
                Log::info('No pending payrolls found to approve');
                return 0;
            }

            $count = 0;
            foreach ($payrolls as $payroll) {
                try {
                    // Calculate gross pay if not already calculated
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
                    
                    Log::info('Approved payroll', [
                        'payroll_id' => $payroll->id,
                        'employee_id' => $payroll->employee_id,
                        'approved_by' => $approvedBy
                    ]);

                } catch (\Exception $e) {
                    Log::error('Failed to approve payroll ' . $payroll->id . ': ' . $e->getMessage());
                    // Continue with next payroll even if one fails
                    continue;
                }
            }

            Log::info('Bulk approval completed', ['count' => $count]);
            return $count;
        });
    }

    /**
     * Process payments for approved payrolls.
     *
     * If App\Models\Payment exists it will be used; otherwise a payment_reference will be written on the payroll row (if column exists).
     */
    public function processPayments(?array $periodData = null, ?array $employeeIds = null, ?string $processedBy = null): array
    {
        $result = ['processed' => 0, 'failed' => 0, 'errors' => []];

        // Get approved payrolls that are not already paid
        $query = Payroll::whereIn('status', ['approved', 'processed']);

        if (!empty($periodData['start_date'])) {
            $query->where('pay_period_start', $periodData['start_date']);
        }
        if (!empty($periodData['end_date'])) {
            $query->where('pay_period_end', $periodData['end_date']);
        }
        if (!empty($employeeIds)) {
            $query->whereIn('employee_id', $employeeIds);
        }

        $payrolls = $query->get();

        // Log for debugging
        Log::info('Processing payments for ' . $payrolls->count() . ' payrolls', [
            'period_data' => $periodData,
            'employee_ids' => $employeeIds,
            'statuses' => $payrolls->pluck('status')->toArray()
        ]);

        if ($payrolls->isEmpty()) {
            Log::warning('No payrolls found for payment processing');
            return $result;
        }

        $paymentModelExists = class_exists(\App\Models\Payment::class);
        Log::info('Payment model exists: ' . ($paymentModelExists ? 'Yes' : 'No'));

        foreach ($payrolls as $payroll) {
            try {
                DB::beginTransaction();

                $txRef = 'PAY-' . Str::upper(Str::random(10));
                $status = 'success'; // Simulate successful payment for now

                if ($paymentModelExists) {
                    try {
                        $payment = \App\Models\Payment::create([
                            'payroll_id' => $payroll->id,
                            'employee_id' => $payroll->employee_id,
                            'amount' => $payroll->net_pay ?? $payroll->gross_pay ?? 0,
                            'status' => ($status === 'success') ? 'completed' : 'failed',
                            'transaction_id' => $txRef,
                            'paid_at' => ($status === 'success') ? now() : null,
                            'meta' => null,
                        ]);
                        
                        Log::info('Payment record created', [
                            'payment_id' => $payment->id,
                            'payroll_id' => $payroll->id,
                            'amount' => $payment->amount
                        ]);
                    } catch (\Throwable $e) {
                        Log::warning('Payment creation failed: ' . $e->getMessage(), [
                            'payroll_id' => $payroll->id,
                            'error' => $e->getMessage()
                        ]);
                        throw $e;
                    }

                    // Update payroll with payment_id if column exists
                    if (Schema::hasColumn('payrolls', 'payment_id')) {
                        $payroll->payment_id = $payment->id;
                    }
                } else {
                    // If Payment model doesn't exist, store reference in payroll
                    if (Schema::hasColumn('payrolls', 'payment_reference')) {
                        $payroll->payment_reference = $txRef;
                    }
                }

                if ($status === 'success') {
                    $payroll->status = 'paid';
                    $payroll->paid_at = now();
                    $payroll->paid_by = $processedBy;
                    $result['processed']++;
                    
                    Log::info('Payroll marked as paid', [
                        'payroll_id' => $payroll->id,
                        'employee_id' => $payroll->employee_id,
                        'net_pay' => $payroll->net_pay
                    ]);
                } else {
                    $payroll->status = 'payment_failed';
                    $result['failed']++;
                }

                $payroll->save();
                DB::commit();

            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('processPayments error', [
                    'payroll_id' => $payroll->id ?? null,
                    'employee_id' => $payroll->employee_id ?? null,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                $result['failed']++;
                $result['errors'][] = [
                    'payroll_id' => $payroll->id ?? null,
                    'message' => $e->getMessage()
                ];
            }
        }

        Log::info('Payment processing completed', $result);
        return $result;
    }

    /**
     * Generate payslip file (PDF using DomPDF if available, or HTML fallback) - SINGLE payroll
     *
     * @param Payroll $payroll
     * @return string|null File path
     */
public function generatePayslip(Payroll $payroll): ?string
{
    try {
        \Illuminate\Support\Facades\Log::info('Starting payslip generation for payroll: ' . $payroll->id);
        
        // 1. Get employee
        $employee = Employee::find($payroll->employee_id);
        if (!$employee) {
            \Illuminate\Support\Facades\Log::warning('Employee not found for payroll: ' . $payroll->id);
            return null;
        }
        
        \Illuminate\Support\Facades\Log::info('Employee found: ' . $employee->full_name);
        
        // 2. Get HTML content from view or fallback
        $viewPath = 'payslips.pdf';
        if (!\Illuminate\Support\Facades\View::exists($viewPath)) {
            \Illuminate\Support\Facades\Log::warning('View does not exist: ' . $viewPath);
            $html = $this->generateFallbackPayslipHTML($payroll, $employee);
        } else {
            try {
                $viewData = [
                    'payroll' => $payroll, 
                    'employee' => $employee,
                    'company' => CompanyHelper::getCurrentCompany() ?? (object)['name' => 'Aeternitas Company'],
                    'today' => now()->format('F j, Y')
                ];
                
                $html = \Illuminate\Support\Facades\View::make($viewPath, $viewData)->render();
                \Illuminate\Support\Facades\Log::info('View rendered successfully: ' . strlen($html) . ' bytes');
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Error rendering view: ' . $e->getMessage());
                $html = $this->generateFallbackPayslipHTML($payroll, $employee);
            }
        }
        
        // 3. Generate PDF
        if (!class_exists('\Barryvdh\DomPDF\Facade\Pdf')) {
            \Illuminate\Support\Facades\Log::error('DomPDF not installed');
            return null;
        }
        
        try {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html);
            $pdf->setPaper('A4', 'portrait');
            $content = $pdf->output();
            \Illuminate\Support\Facades\Log::info('PDF generated: ' . strlen($content) . ' bytes');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('PDF generation failed: ' . $e->getMessage());
            return null;
        }
        
        // 4. Create directory and filename
        $dir = 'payslips';
        \Illuminate\Support\Facades\Storage::makeDirectory($dir);
        
        // Simple filename without colons or spaces
        $filename = $dir . '/payslip_' . $payroll->id . '.pdf';
        
        \Illuminate\Support\Facades\Log::info('Saving file: ' . $filename);
        
        // 5. Save file
        if (\Illuminate\Support\Facades\Storage::put($filename, $content)) {
            \Illuminate\Support\Facades\Log::info('File saved successfully');
            
            // 6. Update payroll record
            if (\Illuminate\Support\Facades\Schema::hasColumn('payrolls', 'payslip_file')) {
                $payroll->payslip_file = $filename;
                $payroll->saveQuietly();
                \Illuminate\Support\Facades\Log::info('Payroll record updated with payslip file');
            }
            
            // 7. Return URL
            $url = \Illuminate\Support\Facades\Storage::url($filename);
            \Illuminate\Support\Facades\Log::info('Payslip generated: ' . $url);
            
            return $url;
            
        } else {
            \Illuminate\Support\Facades\Log::error('Failed to save file: ' . $filename);
            return null;
        }
        
    } catch (\Exception $e) {
        \Illuminate\Support\Facades\Log::error('generatePayslip error: ' . $e->getMessage());
        \Illuminate\Support\Facades\Log::error('Trace: ' . $e->getTraceAsString());
        return null;
    }
}


/**
 * Calculate all payroll components with exact Excel formulas
 */
/**
 * Calculate all payroll components with exact Excel formulas
 */
private function calculateAllPayrollComponents(Employee $employee, $employeeRecords, array $periodData): array
{
    // Get rates from employee or calculate them
    $monthlyRate = $employee->salary ?? 0;
    $semiMonthlyRate = $monthlyRate / 2;
    $dailyRate = $employee->daily_rate ?? ($monthlyRate * 12 / 313); // Excel formula: =E16*12/313
    $hourlyRate = $dailyRate / 8; // Excel formula: =+G16/8
    
    // Calculate basic working days and hours
    $daysWorked = $this->calculateDaysWorkedFromRecords($employeeRecords);
    $basicSalary = $daysWorked * $dailyRate;
    
    // Calculate overtime with Excel multipliers
    $overtimeData = $this->calculateOvertimeWithExcelRates($employeeRecords, $hourlyRate);
    
    // Calculate night differential with Excel formula (10% of hourly rate)
    $nightDiffData = $this->calculateNightDifferentialWithExcelRates($employeeRecords, $hourlyRate);
    
    // Calculate holiday premiums with Excel multipliers
    $holidayData = $this->calculateHolidayPayWithExcelRates($employee, $employeeRecords, $dailyRate, $hourlyRate);
    
    // Calculate rest day premiums
    $restDayData = $this->calculateRestDayPremiumWithExcelRates($employee, $employeeRecords, $dailyRate);
    
    // Calculate allowances (incentive leave from Excel - 5 days)
    $allowances = $this->calculateAllowances($employee, $dailyRate);
    
    // Calculate statutory deductions (SSS, PHIC, HDMF)
    $statutoryDeductions = $this->calculateStatutoryDeductions($employee, $monthlyRate);
    
    // Calculate late/undertime deductions
    $lateDeductions = $this->calculateLateUndertimeDeductions($employeeRecords, $hourlyRate);
    
    // Calculate absence deductions
    $absentDeductions = $this->calculateAbsenceDeductions($employeeRecords, $dailyRate);
    
    // Total deductions
    $totalDeductions = $lateDeductions + $absentDeductions + 
                      $statutoryDeductions['sss'] + 
                      $statutoryDeductions['phic'] + 
                      $statutoryDeductions['hdmf'];
    
    // Calculate gross pay using Excel formula pattern
    $grossPay = $this->calculateGrossPayWithExcelFormula(
        $basicSalary,
        $overtimeData['total_pay'],
        $nightDiffData['total_pay'],
        $holidayData['total_pay'],
        $restDayData['total_pay'],
        $allowances,
        0, // bonuses
        $lateDeductions,
        $absentDeductions
    );
    
    // Calculate tax
    $taxAmount = $this->calculateTax($grossPay);
    
    // Calculate net pay
    $netPay = $grossPay - $totalDeductions - $taxAmount;
    
    return [
        'monthly_rate' => $monthlyRate,
        'semi_monthly_rate' => $semiMonthlyRate,
        'daily_rate' => $dailyRate,
        'hourly_rate' => $hourlyRate,
        'basic_salary' => $basicSalary,
        'days_worked' => $daysWorked,
        'overtime_hours' => $overtimeData['total_hours'],
        'overtime_rate' => $hourlyRate * 1.25, // Excel: 125% of hourly rate
        'overtime_pay' => $overtimeData['total_pay'],
        'night_differential_hours' => $nightDiffData['total_hours'],
        'night_differential_rate' => $hourlyRate * 0.10, // Excel: 10% of hourly rate
        'night_differential_pay' => $nightDiffData['total_pay'],
        'rest_day_premium_pay' => $restDayData['total_pay'],
        'allowances' => $allowances,
        'bonuses' => 0,
        'total_deductions' => $totalDeductions,
        'late_deductions' => $lateDeductions,
        'absent_deductions' => $absentDeductions,
        'sss' => $statutoryDeductions['sss'],
        'phic' => $statutoryDeductions['phic'],
        'hdmf' => $statutoryDeductions['hdmf'],
        'tax_amount' => $taxAmount,
        'gross_pay' => $grossPay,
        'net_pay' => $netPay,
        // Holiday data for reference
        'holiday_basic_pay' => $holidayData['basic_pay'] ?? 0,
        'holiday_premium' => $holidayData['premium_pay'] ?? 0,
        'special_holiday_premium' => $holidayData['special_premium'] ?? 0,
        'regular_holiday_days' => $holidayData['regular_days'] ?? 0,
        'special_holiday_days' => $holidayData['special_days'] ?? 0,
        'scheduled_hours' => $daysWorked * 8, // Assuming 8 hours per day
    ];
}

/**
 * Calculate days worked from attendance records
 */
private function calculateDaysWorkedFromRecords($employeeRecords): float
{
    $totalHours = 0;
    
    foreach ($employeeRecords as $record) {
        // Only count actual hours worked on working days
        if ($record['schedule_status'] === 'Working' && 
            $record['attendance_status'] === 'Present') {
            
            // Parse scheduled hours from the record
            $hours = $this->parseFormattedHours($record['scheduled_hours'] ?? '0 hrs 0 mins');
            $totalHours += $hours;
        }
    }
    
    // Convert hours to days (assuming 8 hours per day)
    return $totalHours / 8;
}

/**
 * Calculate overtime with exact Excel multipliers
 */
private function calculateOvertimeWithExcelRates($employeeRecords, $hourlyRate): array
{
    $totalHours = 0;
    $totalPay = 0;
    
    foreach ($employeeRecords as $record) {
        // Regular OT: hours × 1.25 × hourly rate
        if ($record['overtime'] > 0) {
            $totalHours += $record['overtime'];
            $totalPay += $record['overtime'] * $hourlyRate * 1.25;
        }
        
        // LH OT: hours × 2.0 × 1.3 × hourly rate (Excel: =H17*200%*1.3*N17)
        if (isset($record['lh_overtime']) && $record['lh_overtime'] > 0) {
            $totalHours += $record['lh_overtime'];
            $totalPay += $record['lh_overtime'] * $hourlyRate * 2.0 * 1.3;
        }
        
        // SH OT: hours × 1.3 × hourly rate (Excel: =H14*P14*1.3)
        if (isset($record['sh_overtime']) && $record['sh_overtime'] > 0) {
            $totalHours += $record['sh_overtime'];
            $totalPay += $record['sh_overtime'] * $hourlyRate * 1.3;
        }
    }
    
    return [
        'total_hours' => $totalHours,
        'total_pay' => round($totalPay, 2)
    ];
}

/**
 * Calculate night differential with Excel formula (10% of hourly rate)
 */
private function calculateNightDifferentialWithExcelRates($employeeRecords, $hourlyRate): array
{
    $totalHours = 0;
    
    foreach ($employeeRecords as $record) {
        if (isset($record['night_differential_hours']) && $record['night_differential_hours'] > 0) {
            $totalHours += $record['night_differential_hours'];
        }
    }
    
    // Excel formula: =H14*0.1*X14 (hours × 10% × hourly rate)
    $totalPay = $totalHours * $hourlyRate * 0.10;
    
    return [
        'total_hours' => $totalHours,
        'total_pay' => round($totalPay, 2)
    ];
}

/**
 * Calculate holiday pay with Excel multipliers
 */
private function calculateHolidayPayWithExcelRates(Employee $employee, $employeeRecords, $dailyRate, $hourlyRate): array
{
    $regularHolidayDays = 0;
    $specialHolidayDays = 0;
    $totalPay = 0;
    
    foreach ($employeeRecords as $record) {
        if ($record['schedule_status'] === 'Regular Holiday') {
            $regularHolidayDays++;
            // Excel: =G14*R14*0.3 (daily rate × 1 × 30%)
            $totalPay += $dailyRate * 1 * 0.30;
        } elseif ($record['schedule_status'] === 'Special Holiday') {
            $specialHolidayDays++;
            // Excel: =G14*R14*0.3 (daily rate × 1 × 30%)
            $totalPay += $dailyRate * 1 * 0.30;
        }
    }
    
    return [
        'regular_holiday_days' => $regularHolidayDays,
        'special_holiday_days' => $specialHolidayDays,
        'total_pay' => round($totalPay, 2)
    ];
}

/**
 * Calculate rest day premium with Excel rates
 */
private function calculateRestDayPremiumWithExcelRates(Employee $employee, $employeeRecords, $dailyRate): array
{
    $totalPay = 0;
    
    // Rest day duty: daily rate × 1.3 (Excel: =H14*V14*1.3)
    foreach ($employeeRecords as $record) {
        if ($record['schedule_status'] === 'Leave' && $record['attendance_status'] === 'Present') {
            // Excel formula for rest day duty
            $totalPay += $dailyRate * 1.3;
        }
    }
    
    return [
        'total_pay' => round($totalPay, 2)
    ];
}

/**
 * Calculate late/undertime deductions based on Excel formula
 */
private function calculateLateUndertimeDeductions($employeeRecords, $hourlyRate): float
{
    $totalMinutes = 0;
    
    foreach ($employeeRecords as $record) {
        if (isset($record['late_minutes']) && $record['late_minutes'] > 0) {
            $totalMinutes += $record['late_minutes'];
        }
    }
    
    // Excel formula: =H14/60*AB14 (hourly rate ÷ 60 × total minutes)
    $deduction = ($hourlyRate / 60) * $totalMinutes;
    
    return round($deduction, 2);
}

/**
 * Calculate absence deductions
 */
private function calculateAbsenceDeductions($employeeRecords, $dailyRate): float
{
    $absentDays = 0;
    
    foreach ($employeeRecords as $record) {
        if ($record['attendance_status'] === 'Absent' && 
            $record['schedule_status'] === 'Working') {
            $absentDays++;
        }
    }
    
    // Excel: =G14*Z14 (daily rate × absent days)
    return round($absentDays * $dailyRate, 2);
}

/**
 * Calculate allowances (incentive leave)
 */
private function calculateAllowances(Employee $employee, $employeeRecords): array
{
    $incentiveLeaveDays = 5; // Default from Excel
    $totalAllowance = $employee->daily_rate * $incentiveLeaveDays;
    
    return [
        'incentive_leave_days' => $incentiveLeaveDays,
        'total' => round($totalAllowance, 2)
    ];
}

/**
 * Calculate statutory deductions based on Excel values
 */
private function calculateStatutoryDeductions(Employee $employee, $monthlyRate): array
{
    // Default values from Excel
    $sss = 450.00; // For drivers with daily rate 695
    $phic = $monthlyRate >= 10000 ? 225.88 : 0; // Excel: =451.75/2
    $hdmf = 100.00; // Fixed amount
    
    return [
        'sss' => $sss,
        'phic' => $phic,
        'hdmf' => $hdmf
    ];
}

/**
 * Calculate gross pay using Excel formula pattern
 */
private function calculateGrossPayWithExcelFormula(
    $basicSalary,
    $overtimeData,
    $nightDiffData,
    $holidayData,
    $restDayData,
    $allowances,
    $lateDeductions,
    $absentDeductions
): float {
    // Excel formula pattern from your file:
    // =G14*K14+I14+J14+M14+O14+Q14+S14+U14+W14+Y14-AC14
    
    // Where:
    // G14*K14 = Basic salary
    // I14 = Incentive leave
    // J14, M14, O14, Q14, S14, U14, W14, Y14 = Various premiums and allowances
    // AC14 = Late deductions
    
    $grossPay = $basicSalary
        + $allowances['total']  // Incentive leave
        + $overtimeData['total_pay']
        + $nightDiffData['total_pay']
        + $holidayData['total_pay']
        + $restDayData['total_pay']
        - $lateDeductions
        - $absentDeductions;
    
    return round($grossPay, 2);
}

/**
 * Export to Excel with comprehensive calculations
 */
public function exportPayrollWithCalculations($payrolls, $format = 'xlsx')
{
    // Ensure payrolls have employee relationships loaded
    $payrolls->load(['employee', 'employee.department']);
    
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
 * Export detailed data to CSV (fallback method)
 */
/**
 * Export detailed data to CSV with all calculations
 */
private function exportDetailedToCSV($payrolls, $filename)
{
    $filepath = storage_path('app/exports/' . $filename);
    $handle = fopen($filepath, 'w');
    
    if (!$handle) {
        throw new \Exception('Unable to create CSV file: ' . $filepath);
    }
    
    // Add BOM for Excel UTF-8 support
    fwrite($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));
    
    // Comprehensive headers based on Excel template with all calculations
    $headers = [
        'Employee ID',
        'Employee Name',
        'Department',
        'Pay Period Start',
        'Pay Period End',
        'Monthly Rate',
        'Semi-Monthly Rate',
        'Daily Rate',
        'Hourly Rate',
        'Days Worked',
        'Basic Salary',
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
        'SSS',
        'PHIC',
        'HDMF',
        'Tax Amount',
        'Gross Pay',
        'Net Pay',
        'Status'
    ];
    fputcsv($handle, $headers);
    
    foreach ($payrolls as $payroll) {
        // Use existing payroll data or calculate if missing
        $row = [
            $payroll->employee->employee_id ?? '',
            $payroll->employee->full_name ?? '',
            $payroll->employee->department->name ?? 'N/A',
            $payroll->pay_period_start,
            $payroll->pay_period_end,
            number_format($payroll->monthly_rate ?? 0, 2),
            number_format($payroll->semi_monthly_rate ?? 0, 2),
            number_format($payroll->daily_rate ?? 0, 2),
            number_format($payroll->hourly_rate ?? 0, 2),
            $this->calculateDaysWorked($payroll),
            number_format($payroll->basic_salary, 2),
            number_format($payroll->overtime_hours ?? 0, 2),
            number_format($payroll->overtime_rate ?? 0, 2),
            number_format($payroll->overtime_pay ?? 0, 2),
            number_format($payroll->night_differential_hours ?? 0, 2),
            number_format($payroll->night_differential_rate ?? 0, 2),
            number_format($payroll->night_differential_pay ?? 0, 2),
            number_format($payroll->rest_day_premium_pay ?? 0, 2),
            number_format($payroll->allowances ?? 0, 2),
            number_format($payroll->bonuses ?? 0, 2),
            number_format($payroll->deductions ?? 0, 2),
            number_format($payroll->sss ?? 0, 2),
            number_format($payroll->phic ?? 0, 2),
            number_format($payroll->hdmf ?? 0, 2),
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
 * Helper method to calculate days worked
 */
private function calculateDaysWorked($payroll)
{
    // You need to implement this based on your attendance data
    // For now, return a default value
    return 13; // Default 13 working days in a half-month
}

/**
 * Export detailed data to XLSX
 */
private function exportDetailedToXLSX($payrolls, $filename)
{
    // Check if PhpSpreadsheet is available
    if (!class_exists('\PhpOffice\PhpSpreadsheet\Spreadsheet')) {
        throw new \Exception('PhpSpreadsheet not installed. Please install via composer: composer require phpoffice/phpspreadsheet');
    }

    $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    
    // Set document properties
    $spreadsheet->getProperties()
        ->setCreator('Aeternitas Payroll System')
        ->setLastModifiedBy('Aeternitas Payroll System')
        ->setTitle('Payroll Report')
        ->setSubject('Payroll Data')
        ->setDescription('Detailed payroll report with calculations');
    
    // Headers
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
    
    // Set headers
    foreach ($headers as $colIndex => $header) {
        $column = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex + 1);
        $sheet->setCellValue($column . '1', $header);
        $sheet->getStyle($column . '1')->getFont()->setBold(true);
    }
    
    // Data rows
    $row = 2;
    foreach ($payrolls as $payroll) {
        // Calculate detailed payroll information
        $details = $this->calculatePayrollDetailsForExport($payroll);
        
        $data = [
            $payroll->id,
            $payroll->employee->employee_id ?? '',
            $payroll->employee->full_name ?? '',
            $payroll->employee->department->name ?? 'N/A',
            $payroll->pay_period_start,
            $payroll->pay_period_end,
            $payroll->basic_salary,
            $details['daily_rate'],
            $details['hourly_rate'],
            $payroll->overtime_hours ?? 0,
            $details['overtime_rate'],
            $details['overtime_pay'],
            $payroll->night_differential_hours ?? 0,
            $details['night_diff_rate'],
            $details['night_diff_pay'],
            $details['rest_day_premium_pay'],
            $payroll->allowances ?? 0,
            $payroll->bonuses ?? 0,
            $payroll->deductions ?? 0,
            $payroll->tax_amount ?? 0,
            $details['gross_pay'],
            $details['net_pay'],
            ucfirst($payroll->status),
            $payroll->approved_at ? $payroll->approved_at->format('Y-m-d H:i:s') : '',
            $payroll->paid_at ? $payroll->paid_at->format('Y-m-d H:i:s') : ''
        ];
        
        // Set data for each column
        foreach ($data as $colIndex => $value) {
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
    
    // Format currency columns (columns 7-22 are currency amounts)
    $currencyColumns = ['G', 'H', 'I', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V'];
    foreach ($currencyColumns as $col) {
        $lastRow = $row - 1;
        if ($lastRow >= 2) {
            $range = $col . '2:' . $col . $lastRow;
            $sheet->getStyle($range)
                  ->getNumberFormat()
                  ->setFormatCode('#,##0.00');
        }
    }
    
    // Add border to all cells
    $styleArray = [
        'borders' => [
            'allBorders' => [
                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                'color' => ['argb' => 'FF000000'],
            ],
        ],
    ];
    
    $lastColumn = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($headers));
    $sheet->getStyle('A1:' . $lastColumn . ($row - 1))->applyFromArray($styleArray);
    
    // Save file
    $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
    $filepath = storage_path('app/exports/' . $filename);
    $writer->save($filepath);
    
    return 'exports/' . $filename;
}

/**
 * Calculate payroll details for export
 */
private function calculatePayrollDetailsForExport($payroll)
{
    $employee = $payroll->employee;
    
    // If employee doesn't exist, use default calculations
    if (!$employee) {
        // Default daily rate calculation: (Basic Salary * 12) / 313 (working days in a year)
        $dailyRate = ($payroll->basic_salary * 12) / 313;
        $hourlyRate = $dailyRate / 8;
        $overtimeRate = $hourlyRate * 1.25;
        $nightDiffRate = $hourlyRate * 1.1;
    } else {
        // Use employee's rates if available, otherwise calculate
        $dailyRate = $employee->daily_rate ?? (($employee->salary ?? $payroll->basic_salary) * 12 / 313);
        $hourlyRate = $employee->hourly_rate ?? ($dailyRate / 8);
        $overtimeRate = $employee->overtime_rate ?? ($hourlyRate * 1.25);
        $nightDiffRate = $employee->night_differential_rate ?? ($hourlyRate * 1.1);
    }
    
    // Calculate actual values
    $overtimePay = $payroll->overtime_pay ?? ($payroll->overtime_hours * $overtimeRate);
    $nightDiffPay = $payroll->night_differential_pay ?? ($payroll->night_differential_hours * $nightDiffRate);
    $restDayPremiumPay = $payroll->rest_day_premium_pay ?? 0;
    
    // Calculate gross pay
    $grossPay = $payroll->gross_pay ?? (
        $payroll->basic_salary 
        + $overtimePay
        + $nightDiffPay
        + $restDayPremiumPay
        + ($payroll->allowances ?? 0)
        + ($payroll->bonuses ?? 0)
    );
    
    // Calculate net pay
    $netPay = $payroll->net_pay ?? (
        $grossPay 
        - ($payroll->deductions ?? 0)
        - ($payroll->tax_amount ?? 0)
    );
    
    return [
        'daily_rate' => $dailyRate,
        'hourly_rate' => $hourlyRate,
        'overtime_rate' => $overtimeRate,
        'night_diff_rate' => $nightDiffRate,
        'overtime_pay' => $overtimePay,
        'night_diff_pay' => $nightDiffPay,
        'rest_day_premium_pay' => $restDayPremiumPay,
        'gross_pay' => $grossPay,
        'net_pay' => $netPay
    ];
}
    
private function generateFallbackPayslipHTML(Payroll $payroll, Employee $employee): string
{
    return '<!DOCTYPE html>
    <html>
    <head>
        <meta charset="utf-8">
        <title>Payslip - ' . htmlspecialchars($employee->full_name) . '</title>
        <style>
            body { font-family: Arial, sans-serif; margin: 20px; }
            h1 { color: #333; }
            table { width: 100%; border-collapse: collapse; }
            th, td { padding: 8px; border: 1px solid #ddd; }
        </style>
    </head>
    <body>
        <h1>Payslip</h1>
        <p><strong>Employee:</strong> ' . htmlspecialchars($employee->full_name) . '</p>
        <p><strong>Period:</strong> ' . $payroll->pay_period_start . ' to ' . $payroll->pay_period_end . '</p>
        <p><strong>Net Pay:</strong> ₱' . number_format($payroll->net_pay, 2) . '</p>
    </body>
    </html>';
}
    /**
     * Export payroll for a period (CSV) - Legacy method
     *
     * @param array|null $periodData
     * @param array|null $employeeIds
     * @param string $format
     * @return string
     */
    public function exportPayroll(?array $periodData = null, ?array $employeeIds = null, string $format = 'csv'): string
    {
        if ($format !== 'csv') {
            throw new \InvalidArgumentException('Only csv export is implemented.');
        }

        $query = Payroll::query();

        if (!empty($periodData['start_date'])) {
            $query->where('pay_period_start', $periodData['start_date']);
        }
        if (!empty($periodData['end_date'])) {
            $query->where('pay_period_end', $periodData['end_date']);
        }
        if (!empty($employeeIds)) {
            $query->whereIn('employee_id', $employeeIds);
        }

        $payrolls = $query->with('employee')->get();

        $rows = [];
        $headers = [
            'payroll_id', 'employee_id', 'employee_name', 'period_start', 'period_end',
            'basic_salary', 'holiday_basic_pay', 'holiday_premium', 'special_holiday_premium',
            'overtime_hours', 'overtime_rate', 'overtime_pay',
            'night_differential_pay', 'rest_day_premium_pay',
            'bonuses', 'deductions', 'tax_amount', 'gross_pay', 'net_pay',
            'status', 'approved_at', 'paid_at'
        ];
        $rows[] = $headers;

        foreach ($payrolls as $p) {
            $employeeName = $p->employee->name ?? (($p->employee->first_name ?? '') . ' ' . ($p->employee->last_name ?? ''));
            $rows[] = [
                $p->id,
                $p->employee_id,
                trim($employeeName),
                $p->pay_period_start,
                $p->pay_period_end,
                $p->basic_salary,
                $p->holiday_basic_pay,
                $p->holiday_premium,
                $p->special_holiday_premium,
                $p->overtime_hours,
                $p->overtime_rate ?? '',
                $p->overtime_pay ?? '',
                $p->night_differential_pay ?? '',
                $p->rest_day_premium_pay ?? '',
                $p->bonuses ?? '',
                $p->deductions ?? '',
                $p->tax_amount ?? '',
                $p->gross_pay ?? '',
                $p->net_pay ?? '',
                $p->status,
                $p->approved_at ?? '',
                $p->paid_at ?? '',
            ];
        }

        $exportDir = storage_path('app/exports/payroll');
        if (!is_dir($exportDir)) {
            mkdir($exportDir, 0755, true);
        }

        $filename = "exports/payroll/payroll_export_" . now()->format('Ymd_His') . ".csv";
        $fullPath = storage_path("app/{$filename}");

        $handle = fopen($fullPath, 'w');
        fwrite($handle, chr(0xEF) . chr(0xBB) . chr(0xBF)); // BOM for Excel
        foreach ($rows as $row) {
            fputcsv($handle, $row);
        }
        fclose($handle);

        Log::info("Exported payroll to {$filename}");

        return $filename;
    }

    /**
     * Calculate payroll preview from comprehensive records (without saving to database)
     *
     * @param Employee $employee
     * @param \Illuminate\Support\Collection $employeeRecords
     * @param array $periodData
     * @return array|null
     */
  private function calculatePayrollPreviewFromRecords(Employee $employee, $employeeRecords, array $periodData): ?array
{
    $startDate = Carbon::parse($periodData['start_date']);
    $endDate = Carbon::parse($periodData['end_date']);

    // Calculate all payroll components with Excel formulas
    $components = $this->calculateAllPayrollComponents($employee, $employeeRecords, $periodData);

    // Return preview data array (not saved to database)
    return [
        'employee_id' => $employee->id,
        'employee_name' => $employee->full_name,
        'pay_period_start' => $startDate->format('Y-m-d'),
        'pay_period_end' => $endDate->format('Y-m-d'),
        'monthly_rate' => $components['monthly_rate'],
        'semi_monthly_rate' => $components['semi_monthly_rate'],
        'daily_rate' => $components['daily_rate'],
        'hourly_rate' => $components['hourly_rate'],
        'basic_salary' => $components['basic_salary'],
        'overtime_hours' => $components['overtime_hours'],
        'overtime_rate' => $components['overtime_rate'],
        'overtime_pay' => $components['overtime_pay'],
        'night_differential_hours' => $components['night_differential_hours'],
        'night_differential_rate' => $components['night_differential_rate'],
        'night_differential_pay' => $components['night_differential_pay'],
        'rest_day_premium_pay' => $components['rest_day_premium_pay'],
        'allowances' => $components['allowances'],
        'bonuses' => $components['bonuses'],
        'deductions' => $components['late_deductions'] + $components['absent_deductions'],
        'tax_amount' => $components['tax_amount'],
        'gross_pay' => $components['gross_pay'],
        'net_pay' => $components['net_pay'],
        'sss' => $components['sss'],
        'phic' => $components['phic'],
        'hdmf' => $components['hdmf'],
        'status' => 'preview',
    ];
}

    /**
     * Calculate payroll from comprehensive attendance records and persist
     *
     * @param Employee $employee
     * @param \Illuminate\Support\Collection $employeeRecords
     * @param array $periodData
     * @return Payroll|null
     */
private function calculatePayrollFromRecords(Employee $employee, $employeeRecords, array $periodData): ?Payroll
{
    $startDate = Carbon::parse($periodData['start_date']);
    $endDate = Carbon::parse($periodData['end_date']);

    // Check if payroll already exists
    $existingPayroll = Payroll::where('employee_id', $employee->id)
        ->where('pay_period_start', $startDate->format('Y-m-d'))
        ->where('pay_period_end', $endDate->format('Y-m-d'))
        ->first();

    if ($existingPayroll) {
        Log::info("Payroll already exists for employee {$employee->id}");
        return null;
    }

    // Calculate all payroll components with Excel formulas
    $components = $this->calculateAllPayrollComponents($employee, $employeeRecords, $periodData);
    
    Log::info('calculateAllPayrollComponents called', [
        'employee_id' => $employee->id,
        'records_count' => $employeeRecords->count(),
        'sample_record' => $employeeRecords->first(),
    ]);

    // Debug log
    Log::info("Payroll calculation for {$employee->full_name}", [
        'basic_salary' => $components['basic_salary'],
        'overtime_pay' => $components['overtime_pay'],
        'night_diff_pay' => $components['night_differential_pay'],
        'rest_day_premium' => $components['rest_day_premium_pay'],
        'gross_pay' => $components['gross_pay'],
        'net_pay' => $components['net_pay']
    ]);

    // Create payroll record with ALL required fields
    try {
        $payroll = Payroll::create([
            'employee_id' => $employee->id,
            'pay_period_start' => $startDate->format('Y-m-d'),
            'pay_period_end' => $endDate->format('Y-m-d'),
            'basic_salary' => $components['basic_salary'],
            'monthly_rate' => $components['monthly_rate'],
            'semi_monthly_rate' => $components['semi_monthly_rate'],
            'daily_rate' => $components['daily_rate'],
            'hourly_rate' => $components['hourly_rate'],
            'holiday_basic_pay' => $components['holiday_basic_pay'] ?? 0,
            'holiday_premium' => $components['holiday_premium'] ?? 0,
            'special_holiday_premium' => $components['special_holiday_premium'] ?? 0,
            'regular_holiday_days' => $components['regular_holiday_days'] ?? 0,
            'special_holiday_days' => $components['special_holiday_days'] ?? 0,
            'overtime_hours' => $components['overtime_hours'],
            'overtime_rate' => $components['overtime_rate'],
            'overtime_pay' => $components['overtime_pay'],
            'scheduled_hours' => $components['scheduled_hours'] ?? 0,
            'night_differential_hours' => $components['night_differential_hours'],
            'night_differential_rate' => $components['night_differential_rate'],
            'night_differential_pay' => $components['night_differential_pay'],
            'rest_day_premium_pay' => $components['rest_day_premium_pay'],
            'allowances' => $components['allowances'],
            'bonuses' => $components['bonuses'],
            'deductions' => $components['total_deductions'],
            'sss' => $components['sss'],
            'phic' => $components['phic'],
            'hdmf' => $components['hdmf'],
            'tax_amount' => $components['tax_amount'],
            'gross_pay' => $components['gross_pay'],
            'net_pay' => $components['net_pay'],
            'status' => 'pending',
        ]);

        Log::info("Generated payroll for employee {$employee->id}: Net Pay: {$components['net_pay']}");

        return $payroll;
    } catch (\Exception $e) {
        Log::error("Failed to create payroll for employee {$employee->id}: " . $e->getMessage());
        return null;
    }
}

    /**
     * Calculate basic salary from comprehensive records based on actual scheduled hours worked
     */
    private function calculateBasicSalaryFromRecords(Employee $employee, $employeeRecords): array
    {
        $totalScheduledHours = 0;
        $scheduledHoursDetails = [];
        
        // Calculate total scheduled hours for records where employee actually worked
        // Include all work (regular days + holidays) for basic salary calculation
        // Note: Leave days with attendance are treated as rest day work (1.2x premium) and excluded from basic salary
        // Leave days without attendance are not paid
        $workingRecords = $employeeRecords->filter(function($record) {
            // Exclude Leave days - rest day work (Leave with attendance) gets premium pay separately (1.2x)
            // Leave without attendance is not paid
            if ($record['schedule_status'] === 'Leave') {
                return false;
            }
            
            // Include if it's a working day with present attendance
            if ($record['schedule_status'] === 'Working' && $record['attendance_status'] === 'Present') {
                return true;
            }
            
            // Include holidays with actual scheduled hours worked
            if (($record['schedule_status'] === 'Regular Holiday' || $record['schedule_status'] === 'Special Holiday') && 
                $this->parseFormattedHours($record['scheduled_hours']) > 0) {
                return true;
            }
            
            // Also include if there are actual scheduled hours worked (for other statuses)
            $scheduledHours = $this->parseFormattedHours($record['scheduled_hours']);
            return $scheduledHours > 0;
        });
        
        foreach ($workingRecords as $record) {
            // Parse scheduled hours from the formatted string (e.g., "7 hrs 1 min" -> 7.017 hours)
            $scheduledHours = $this->parseFormattedHours($record['scheduled_hours']);
            $totalScheduledHours += $scheduledHours;
            
            // Store details for display
            $scheduledHoursDetails[] = [
                'date' => $record['date_formatted'],
                'hours' => $record['scheduled_hours'],
                'decimal_hours' => $scheduledHours
            ];
        }
        
        // Calculate basic salary based on daily rate and scheduled hours
        // Formula: Basic Salary = Daily Rate × (Scheduled Hours / 8)
        $dailyRate = $employee->daily_rate;
        $basicSalary = $dailyRate * ($totalScheduledHours / 8);
        
        // Calculate hourly rate for reference
        $hourlyRate = $dailyRate / 8;
        
        return [
            'amount' => round($basicSalary, 2),
            'total_scheduled_hours' => $totalScheduledHours,
            'scheduled_hours_details' => $scheduledHoursDetails,
            'daily_rate' => $dailyRate,
            'hourly_rate' => $hourlyRate
        ];
    }

    /**
     * Calculate overtime from comprehensive records
     */
    private function calculateOvertimeFromRecords(Employee $employee, $employeeRecords): array
    {
        $totalOvertimeHours = $employeeRecords->sum('overtime');
        $overtimePay = $totalOvertimeHours * $employee->overtime_rate;

        return [
            'overtime_hours' => $totalOvertimeHours,
            'overtime_rate' => $employee->overtime_rate,
            'overtime_pay' => round($overtimePay, 2)
        ];
    }

    /**
     * Calculate night differential from comprehensive records
     */
    private function calculateNightDifferentialFromRecords(Employee $employee, $employeeRecords): array
    {
        $nightShiftHours = $employeeRecords->sum('night_differential_hours');
        $nightDifferentialPay = $nightShiftHours * $employee->night_differential_rate;

        return [
            'night_differential_hours' => $nightShiftHours,
            'night_differential_rate' => $employee->night_differential_rate,
            'night_differential_pay' => round($nightDifferentialPay, 2)
        ];
    }

    /**
     * Calculate holiday pay from comprehensive records
     */
    private function calculateHolidayPayFromRecords(Employee $employee, $employeeRecords): array
    {
        $regularHolidayDays = $employeeRecords->where('schedule_status', 'Regular Holiday')->count();
        $specialHolidayDays = $employeeRecords->where('schedule_status', 'Special Holiday')->count();
        
        // Calculate holiday premium based on full days (8 hours per day)
        // Regular holiday: 100% premium on full days (8 hours per day)
        $regularHolidayPremium = $regularHolidayDays * $employee->daily_rate; // 100% holiday premium per day
        
        // Special holiday: 30% premium on full days (8 hours per day)
        $specialHolidayPremium = $specialHolidayDays * $employee->daily_rate * 0.3; // 30% holiday premium per day
        
        // Basic pay for holidays (will be added to basic salary column)
        $holidayBasicPay = ($regularHolidayDays + $specialHolidayDays) * $employee->daily_rate;
        
        // Total holiday pay = basic pay + premium (for display purposes)
        $totalHolidayPay = $holidayBasicPay + $regularHolidayPremium + $specialHolidayPremium;
        

        return [
            'regular_holiday_days' => $regularHolidayDays,
            'special_holiday_days' => $specialHolidayDays,
            'holiday_basic_pay' => round($holidayBasicPay, 2),
            'holiday_premium' => round($regularHolidayPremium, 2),
            'special_holiday_premium' => round($specialHolidayPremium, 2),
            'holiday_pay' => round($totalHolidayPay, 2)
        ];
    }

    /**
     * Calculate rest day premium from comprehensive records
     * 
     * When employee works on Leave day (rest day), they are paid 1.2x daily rate
     * This applies to complete daily days worked on Leave/Rest days
     * 
     * @param Employee $employee
     * @param \Illuminate\Support\Collection $employeeRecords
     * @return array Rest day premium data
     */
    private function calculateRestDayPremiumFromRecords(Employee $employee, $employeeRecords): array
    {
        // Find Leave days where employee has attendance (worked on rest day)
        // Only count complete days worked (has both time_in and time_out)
        $restDayWorkRecords = $employeeRecords->filter(function($record) {
            // Must be Leave status with Present attendance (worked on rest day)
            return $record['schedule_status'] === 'Leave' && $record['attendance_status'] === 'Present';
        });
        
        $restDayDays = $restDayWorkRecords->count();
        
        // Calculate rest day premium: daily_rate × 1.2 for each complete day
        // For complete daily days worked on rest day, pay 1.2x daily rate
        $restDayPremiumPay = $restDayDays * $employee->daily_rate * 1.2;
        
        return [
            'rest_day_days' => $restDayDays,
            'rest_day_premium_pay' => round($restDayPremiumPay, 2),
            'rest_day_rate' => round($employee->daily_rate * 1.2, 2),
        ];
    }

    /**
     * Calculate bonuses from comprehensive records
     */
    private function calculateBonusesFromRecords(Employee $employee, $employeeRecords): float
    {
        $bonuses = 0;
        
        // Perfect attendance bonus
        $totalWorkingDays = $employeeRecords->where('schedule_status', 'Working')->count();
        $presentDays = $employeeRecords->where('attendance_status', 'Present')->count();
        
        if ($totalWorkingDays > 0 && $presentDays == $totalWorkingDays) {
            $bonuses += 500; // Perfect attendance bonus
        }
        
        // Performance bonus (example: based on overtime hours)
        $totalOvertimeHours = $employeeRecords->sum('overtime');
        if ($totalOvertimeHours > 20) {
            $bonuses += 300; // Overtime performance bonus
        }
        
        return $bonuses;
    }

    /**
     * Calculate deductions from comprehensive records
     */
    private function calculateDeductionsFromRecords(Employee $employee, $employeeRecords): float
    {
        $deductions = 0;
        
        // Late deductions - based on basic salary per minute
        $totalLateMinutes = $employeeRecords->sum('late_minutes');
        if ($totalLateMinutes > 0) {
            // Calculate rate per minute: Daily rate / (8 hours * 60 minutes)
            $minutesPerDay = 8 * 60; // 480 minutes per day
            $ratePerMinute = $employee->daily_rate / $minutesPerDay;
            $deductions += $totalLateMinutes * $ratePerMinute;
        }
        
        // Error deductions (incomplete time records)
        $errorDays = $employeeRecords->where('attendance_status', 'Error')->count();
        $deductions += $errorDays * $employee->daily_rate; // Full day deduction for error
        
        return $deductions;
    }

    /**
     * Calculate late minutes details for preview display
     */
    private function calculateLateMinutesDetails(Employee $employee, $employeeRecords): array
    {
        $totalLateMinutes = $employeeRecords->sum('late_minutes');
        $lateDays = $employeeRecords->where('late_minutes', '>', 0);
        
        $lateDetails = [];
        $totalLateDeduction = 0;
        
        if ($totalLateMinutes > 0) {
            // Calculate rate per minute: Daily rate / (8 hours * 60 minutes)
            $minutesPerDay = 8 * 60; // 480 minutes per day
            $ratePerMinute = $employee->daily_rate / $minutesPerDay;
            
            foreach ($lateDays as $record) {
                if ($record['late_minutes'] > 0) {
                    $lateDeduction = $record['late_minutes'] * $ratePerMinute;
                    $totalLateDeduction += $lateDeduction;
                    
                    $lateDetails[] = [
                        'date' => $record['date_formatted'],
                        'late_minutes' => $record['late_minutes'],
                        'deduction_amount' => round($lateDeduction, 2),
                        'rate_per_minute' => round($ratePerMinute, 4)
                    ];
                }
            }
        }
        
        return [
            'total_late_minutes' => $totalLateMinutes,
            'total_late_deduction' => round($totalLateDeduction, 2),
            'late_days_count' => count($lateDetails),
            'late_details' => $lateDetails,
            'rate_per_minute' => $totalLateMinutes > 0 ? round($employee->daily_rate / (8 * 60), 4) : 0
        ];
    }

    /**
     * Convert formatted hours string back to decimal hours for calculations
     * 
     * @param string $formattedHours Formatted hours like "8 hrs 30 mins", "8 hrs", "30 mins", "—", "Holiday"
     * @return float Decimal hours
     */
    private function parseFormattedHours($formattedHours)
    {
        // Handle special cases
        if ($formattedHours === '—' || $formattedHours === 'Regular Holiday' || $formattedHours === 'Special Holiday' || $formattedHours === 'Leave' || $formattedHours === 'Day Off') {
            return 0;
        }
        
        // Parse "X hrs Y mins" format
        if (preg_match('/(\d+)\s*hrs?\s*(\d+)\s*mins?/', $formattedHours, $matches)) {
            $hours = (int)$matches[1];
            $minutes = (int)$matches[2];
            return $hours + ($minutes / 60);
        }
        
        // Parse "X hrs" format (no minutes)
        if (preg_match('/(\d+)\s*hrs?/', $formattedHours, $matches)) {
            return (int)$matches[1];
        }
        
        // Parse "X mins" format (no hours)
        if (preg_match('/(\d+)\s*mins?/', $formattedHours, $matches)) {
            return (int)$matches[1] / 60;
        }
        
        return 0;
    }

    /**
     * Calculate tax amount using TaxCalculationService
     *
     * @param float $grossPay
     * @return float
     */
    private function calculateTax(float $grossPay): float
    {
        $taxService = app(\App\Services\TaxCalculationService::class);
        return $taxService->calculateTax($grossPay);
    }

    /**
     * Get payroll summary for a period
     *
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return array
     */
    public function getPayrollSummary(Carbon $startDate, Carbon $endDate): array
    {
        $payrolls = Payroll::where('pay_period_start', $startDate->format('Y-m-d'))
            ->where('pay_period_end', $endDate->format('Y-m-d'))
            ->get();

        return [
            'total_employees' => $payrolls->count(),
            'total_gross_pay' => $payrolls->sum('gross_pay'),
            'total_overtime_hours' => $payrolls->sum('overtime_hours'),
            'total_deductions' => $payrolls->sum('deductions'),
            'total_net_pay' => $payrolls->sum('net_pay'),
        ];
    }
}
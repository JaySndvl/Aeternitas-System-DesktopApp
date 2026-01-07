<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Payroll Export - {{ $start_date }} to {{ $end_date }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            margin: 0;
            padding: 10px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .header h1 {
            margin: 0;
            font-size: 18px;
            color: #333;
        }
        .header p {
            margin: 5px 0;
            font-size: 12px;
            color: #666;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th {
            background-color: #4a5568;
            color: white;
            padding: 8px 4px;
            text-align: left;
            font-size: 9px;
            border: 1px solid #2d3748;
        }
        td {
            padding: 6px 4px;
            border: 1px solid #ddd;
            font-size: 9px;
        }
        tr:nth-child(even) {
            background-color: #f7fafc;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .summary {
            margin-top: 20px;
            padding: 10px;
            background-color: #edf2f7;
            border: 1px solid #cbd5e0;
        }
        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 5px 0;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $company->name ?? 'Company' }} - Payroll Report</h1>
        <p>Period: {{ \Carbon\Carbon::parse($start_date)->format('M d, Y') }} to {{ \Carbon\Carbon::parse($end_date)->format('M d, Y') }}</p>
        <p>Generated: {{ now()->format('M d, Y h:i A') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Employee ID</th>
                <th>Employee Name</th>
                <th>Department</th>
                <th>Basic Salary</th>
                <th>Overtime</th>
                <th>Allowances</th>
                <th>Deductions</th>
                <th>Gross Pay</th>
                <th>Net Pay</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totalBasicSalary = 0;
                $totalOvertime = 0;
                $totalAllowances = 0;
                $totalDeductions = 0;
                $totalGrossPay = 0;
                $totalNetPay = 0;
            @endphp
            @foreach($payrolls as $payroll)
                @php
                    $employee = $payroll->employee;
                    
                    // Calculate values if payroll has zero values (uncalculated)
                    $basicSalary = $payroll->basic_salary ?? 0;
                    $overtimePay = $payroll->overtime_pay ?? 0;
                    $allowances = $payroll->allowances ?? 0;
                    $deductions = $payroll->deductions ?? 0;
                    $grossPay = $payroll->gross_pay ?? 0;
                    $netPay = $payroll->net_pay ?? 0;
                    
                    // If values are zero, try to calculate from employee rates and payroll data
                    if (($basicSalary == 0 && $grossPay == 0) && $employee) {
                        // Get rates from payroll or employee
                        $dailyRate = $payroll->daily_rate ?? $employee->daily_rate ?? (($employee->salary ?? 0) / 26);
                        $hourlyRate = $payroll->hourly_rate ?? $employee->hourly_rate ?? ($dailyRate / 8);
                        
                        // Calculate basic salary - use semi-monthly rate if available, otherwise calculate from daily rate
                        if ($payroll->semi_monthly_rate > 0) {
                            $basicSalary = $payroll->semi_monthly_rate;
                        } elseif ($payroll->monthly_rate > 0) {
                            $basicSalary = $payroll->monthly_rate / 2;
                        } elseif ($dailyRate > 0) {
                            // Calculate based on pay period days
                            $startDate = \Carbon\Carbon::parse($payroll->pay_period_start);
                            $endDate = \Carbon\Carbon::parse($payroll->pay_period_end);
                            $daysInPeriod = $startDate->diffInDays($endDate) + 1;
                            $basicSalary = $dailyRate * min($daysInPeriod, 15); // Semi-monthly typically 15 days
                        }
                        
                        // Calculate overtime pay if hours exist
                        if (($payroll->overtime_hours ?? 0) > 0) {
                            $overtimeRate = $payroll->overtime_rate ?? ($hourlyRate * 1.25);
                            $overtimePay = ($payroll->overtime_hours ?? 0) * $overtimeRate;
                        }
                        
                        // Get other components from payroll if available
                        $nightDiffPay = $payroll->night_differential_pay ?? 0;
                        $restDayPremium = $payroll->rest_day_premium_pay ?? 0;
                        $bonuses = $payroll->bonuses ?? 0;
                        
                        // Calculate gross pay
                        $grossPay = $basicSalary + $overtimePay + $nightDiffPay + 
                                   $restDayPremium + $allowances + $bonuses;
                        
                        // Get deductions from payroll
                        $deductions = $payroll->deductions ?? 0;
                        $taxAmount = $payroll->tax_amount ?? 0;
                        
                        // Calculate net pay
                        $netPay = $grossPay - $deductions - $taxAmount;
                    }
                    
                    $totalBasicSalary += $basicSalary;
                    $totalOvertime += $overtimePay;
                    $totalAllowances += $allowances;
                    $totalDeductions += $deductions;
                    $totalGrossPay += $grossPay;
                    $totalNetPay += $netPay;
                @endphp
                <tr>
                    <td>{{ $employee->employee_id ?? 'N/A' }}</td>
                    <td>{{ $employee->full_name ?? 'N/A' }}</td>
                    <td>{{ $employee->department->name ?? 'N/A' }}</td>
                    <td class="text-right">₱{{ number_format($basicSalary, 2) }}</td>
                    <td class="text-right">₱{{ number_format($overtimePay, 2) }}</td>
                    <td class="text-right">₱{{ number_format($allowances, 2) }}</td>
                    <td class="text-right">₱{{ number_format($deductions, 2) }}</td>
                    <td class="text-right">₱{{ number_format($grossPay, 2) }}</td>
                    <td class="text-right">₱{{ number_format($netPay, 2) }}</td>
                    <td class="text-center">{{ ucfirst($payroll->status) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr style="background-color: #e2e8f0; font-weight: bold;">
                <td colspan="3" class="text-right">TOTAL:</td>
                <td class="text-right">₱{{ number_format($totalBasicSalary, 2) }}</td>
                <td class="text-right">₱{{ number_format($totalOvertime, 2) }}</td>
                <td class="text-right">₱{{ number_format($totalAllowances, 2) }}</td>
                <td class="text-right">₱{{ number_format($totalDeductions, 2) }}</td>
                <td class="text-right">₱{{ number_format($totalGrossPay, 2) }}</td>
                <td class="text-right">₱{{ number_format($totalNetPay, 2) }}</td>
                <td></td>
            </tr>
        </tfoot>
    </table>

    <div class="summary">
        <div class="summary-row">
            <span>Total Employees:</span>
            <span>{{ $payrolls->count() }}</span>
        </div>
        <div class="summary-row">
            <span>Total Gross Pay:</span>
            <span>₱{{ number_format($totalGrossPay, 2) }}</span>
        </div>
        <div class="summary-row">
            <span>Total Net Pay:</span>
            <span>₱{{ number_format($totalNetPay, 2) }}</span>
        </div>
    </div>
</body>
</html>


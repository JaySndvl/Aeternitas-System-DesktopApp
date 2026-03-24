<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payroll Export - {{ $start_date }} to {{ $end_date }}</title>
    <style>
        /* Base styles with improved PDF compatibility */
        body {
            font-family: 'DejaVu Sans', 'Arial Unicode MS', Arial, sans-serif;
            font-size: 10px;
            line-height: 1.4;
            margin: 0;
            padding: 15px;
            color: #333;
        }
        
        /* Header Section */
        .header {
            text-align: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #4a5568;
        }
        
        .company-name {
            font-size: 20px;
            font-weight: bold;
            margin: 0 0 5px 0;
            color: #2d3748;
        }
        
        .report-title {
            font-size: 16px;
            margin: 0 0 15px 0;
            color: #4a5568;
        }
        
        .period-info {
            font-size: 12px;
            font-weight: bold;
            margin: 5px 0;
            color: #666;
        }
        
        .generated-date {
            font-size: 10px;
            color: #718096;
            margin: 5px 0;
        }
        
        /* Table Styles */
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0 25px 0;
        }
        
        th {
            background-color: #4a5568;
            color: white;
            padding: 8px 6px;
            text-align: left;
            font-size: 9px;
            font-weight: bold;
            border: 1px solid #2d3748;
        }
        
        td {
            padding: 6px 6px;
            border: 1px solid #e2e8f0;
            font-size: 9px;
            vertical-align: top;
        }
        
        tr:nth-child(even) {
            background-color: #f7fafc;
        }
        
        /* Text Alignment */
        .text-right {
            text-align: right;
        }
        
        .text-center {
            text-align: center;
        }
        
        .text-left {
            text-align: left;
        }
        
        /* Currency formatting */
        .currency {
            font-family: 'DejaVu Sans', monospace;
        }
        
        /* Status Styles */
        .status-pending { color: #d69e2e; font-weight: bold; }
        .status-approved { color: #38a169; font-weight: bold; }
        .status-paid { color: #3182ce; font-weight: bold; }
        .status-cancelled { color: #e53e3e; font-weight: bold; }
        
        /* Totals Row */
        tfoot tr {
            background-color: #e2e8f0;
            font-weight: bold;
        }
        
        tfoot td {
            border-top: 2px solid #4a5568;
            border-bottom: 2px solid #4a5568;
        }
        
        /* Summary Section */
        .summary-section {
            margin-top: 30px;
            padding: 15px;
            background-color: #edf2f7;
            border: 1px solid #cbd5e0;
            border-radius: 4px;
        }
        
        .summary-title {
            font-size: 14px;
            font-weight: bold;
            margin: 0 0 15px 0;
            color: #2d3748;
            border-bottom: 1px solid #cbd5e0;
            padding-bottom: 5px;
        }
        
        .summary-table {
            width: 100%;
            max-width: 400px;
            margin: 0;
        }
        
        .summary-table td {
            border: none;
            padding: 8px 0;
            background-color: transparent;
        }
        
        .summary-table tr:last-child td {
            border-top: 1px solid #cbd5e0;
            padding-top: 12px;
            font-weight: bold;
        }
        
        /* Footer */
        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 9px;
            color: #718096;
            padding-top: 10px;
            border-top: 1px solid #e2e8f0;
        }
        
        /* Page break for printing */
        @media print {
            .page-break {
                page-break-before: always;
            }
            
            body {
                padding: 0;
            }
            
            table {
                page-break-inside: auto;
            }
            
            tr {
                page-break-inside: avoid;
                page-break-after: auto;
            }
            
            thead {
                display: table-header-group;
            }
            
            tfoot {
                display: table-footer-group;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="company-info">
            <h1 class="company-name">{{ $company->name ?? 'Aeternitas Company' }}</h1>
            <h2 class="report-title">Payroll Report</h2>
        </div>
        
        <div class="period-info">
            Period: {{ \Carbon\Carbon::parse($start_date)->format('F j, Y') }} to {{ \Carbon\Carbon::parse($end_date)->format('F j, Y') }}
        </div>
        
        <div class="generated-date">
            Generated: {{ now()->format('F j, Y h:i A') }}
        </div>
    </div>

    <!-- Payroll Table -->
    <table>
        <thead>
            <tr>
                <th>Employee ID</th>
                <th>Employee Name</th>
                <th>Department</th>
                <th class="text-right">Basic Salary</th>
                <th class="text-right">Overtime</th>
                <th class="text-right">Allowances</th>
                <th class="text-right">Deductions</th>
                <th class="text-right">Gross Pay</th>
                <th class="text-right">Net Pay</th>
                <th class="text-center">Status</th>
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
                $totalEmployees = $payrolls->count();
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
                    <td>{{ $employee->full_name ?? 'Unknown Employee' }}</td>
                    <td>{{ $employee->department->name ?? 'N/A' }}</td>
                    <td class="text-right currency">₱{{ number_format($basicSalary, 2) }}</td>
                    <td class="text-right currency">₱{{ number_format($overtimePay, 2) }}</td>
                    <td class="text-right currency">₱{{ number_format($allowances, 2) }}</td>
                    <td class="text-right currency">₱{{ number_format($deductions, 2) }}</td>
                    <td class="text-right currency">₱{{ number_format($grossPay, 2) }}</td>
                    <td class="text-right currency">₱{{ number_format($netPay, 2) }}</td>
                    <td class="text-center status-{{ $payroll->status }}">{{ ucfirst($payroll->status) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3" class="text-right"><strong>TOTALS:</strong></td>
                <td class="text-right currency"><strong>₱{{ number_format($totalBasicSalary, 2) }}</strong></td>
                <td class="text-right currency"><strong>₱{{ number_format($totalOvertime, 2) }}</strong></td>
                <td class="text-right currency"><strong>₱{{ number_format($totalAllowances, 2) }}</strong></td>
                <td class="text-right currency"><strong>₱{{ number_format($totalDeductions, 2) }}</strong></td>
                <td class="text-right currency"><strong>₱{{ number_format($totalGrossPay, 2) }}</strong></td>
                <td class="text-right currency"><strong>₱{{ number_format($totalNetPay, 2) }}</strong></td>
                <td></td>
            </tr>
        </tfoot>
    </table>

    <!-- Summary Section -->
    <div class="summary-section">
        <h3 class="summary-title">Payroll Summary</h3>
        <table class="summary-table">
            <tr>
                <td>Total Employees:</td>
                <td class="text-right">{{ $totalEmployees }}</td>
            </tr>
            <tr>
                <td>Total Basic Salary:</td>
                <td class="text-right currency">₱{{ number_format($totalBasicSalary, 2) }}</td>
            </tr>
            <tr>
                <td>Total Overtime Pay:</td>
                <td class="text-right currency">₱{{ number_format($totalOvertime, 2) }}</td>
            </tr>
            <tr>
                <td>Total Allowances:</td>
                <td class="text-right currency">₱{{ number_format($totalAllowances, 2) }}</td>
            </tr>
            <tr>
                <td>Total Deductions:</td>
                <td class="text-right currency">₱{{ number_format($totalDeductions, 2) }}</td>
            </tr>
            <tr>
                <td><strong>Total Gross Pay:</strong></td>
                <td class="text-right currency"><strong>₱{{ number_format($totalGrossPay, 2) }}</strong></td>
            </tr>
            <tr>
                <td><strong>Total Net Pay:</strong></td>
                <td class="text-right currency"><strong>₱{{ number_format($totalNetPay, 2) }}</strong></td>
            </tr>
        </table>
    </div>

    <!-- Footer -->
    <div class="footer">
        <p>Generated by Aeternitas Payroll System</p>
        <p>This is an official payroll document. Unauthorized distribution is prohibited.</p>
        <p>Page 1 of 1</p>
    </div>
</body>
</html>
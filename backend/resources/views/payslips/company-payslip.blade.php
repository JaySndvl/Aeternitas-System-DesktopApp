<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>PAYSLIP - {{ $employee->full_name }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 11px; margin: 0; padding: 20px; }
        .container { width: 100%; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #000; padding-bottom: 10px; }
        .company-name { font-size: 16px; font-weight: bold; }
        .title { font-size: 14px; font-weight: bold; margin: 5px 0; }
        .payout-period { font-size: 12px; }
        
        .employee-info { margin-bottom: 15px; }
        .info-row { display: flex; margin-bottom: 5px; }
        .info-label { width: 150px; font-weight: bold; }
        .info-value { flex: 1; }
        
        .salary-table { width: 100%; border-collapse: collapse; margin: 15px 0; }
        .salary-table th, .salary-table td { 
            border: 1px solid #000; 
            padding: 5px; 
            text-align: left;
            font-size: 10px;
        }
        .salary-table th { background-color: #f2f2f2; font-weight: bold; }
        .total-row { font-weight: bold; background-color: #e6e6e6; }
        
        .calculation-section { 
            border: 1px solid #000; 
            padding: 10px; 
            margin: 10px 0;
            page-break-inside: avoid;
        }
        
        .signature-section { margin-top: 30px; page-break-inside: avoid; }
        .signature-line { 
            display: inline-block; 
            width: 200px; 
            border-top: 1px solid #000; 
            margin: 0 20px; 
            padding-top: 5px;
        }
        
        @media print {
            body { margin: 0; padding: 10px; }
            .no-print { display: none; }
            .page-break { page-break-after: always; }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="company-name">E-BRIGHT RETAIL CORP</div>
            <div class="title">PAYSLIP</div>
            <div class="payout-period">Payout: {{ date('M d, Y', strtotime($payroll->paid_at ?? now())) }}</div>
            <div>Payroll Period Covered: {{ $payroll->pay_period_start }} - {{ $payroll->pay_period_end }}</div>
        </div>
        
        <!-- Employee Information -->
        <div class="employee-info">
            <div class="info-row">
                <div class="info-label">Name:</div>
                <div class="info-value">{{ $employee->full_name }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Employee ID:</div>
                <div class="info-value">{{ $employee->employee_id }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Position:</div>
                <div class="info-value">{{ $employee->position ?? 'N/A' }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Department:</div>
                <div class="info-value">{{ $employee->department->name ?? 'N/A' }}</div>
            </div>
        </div>
        
        <!-- Salary Calculation Section -->
        <div class="calculation-section">
            <h3 style="margin-top: 0;">BASIC PAY & ALLOWANCES</h3>
            
            <table class="salary-table">
                <thead>
                    <tr>
                        <th>Description</th>
                        <th>Days/Hours</th>
                        <th>Rate</th>
                        <th>Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Basic Salary -->
                    <tr>
                        <td>Basic Salary ({{ $payroll->days_worked ?? 13 }} days)</td>
                        <td>{{ $payroll->days_worked ?? 13 }} days</td>
                        <td>₱{{ number_format($payroll->daily_rate ?? 0, 2) }}/day</td>
                        <td>₱{{ number_format($payroll->basic_salary, 2) }}</td>
                    </tr>
                    
                    <!-- Overtime -->
                    @if(($payroll->overtime_hours ?? 0) > 0)
                    <tr>
                        <td>Overtime (Regular)</td>
                        <td>{{ $payroll->overtime_hours }} hrs</td>
                        <td>₱{{ number_format($payroll->hourly_rate * 1.25, 2) }}/hr</td>
                        <td>₱{{ number_format($payroll->overtime_pay ?? 0, 2) }}</td>
                    </tr>
                    @endif
                    
                    <!-- Night Differential -->
                    @if(($payroll->night_differential_hours ?? 0) > 0)
                    <tr>
                        <td>Night Differential</td>
                        <td>{{ $payroll->night_differential_hours }} hrs</td>
                        <td>₱{{ number_format($payroll->hourly_rate * 0.10, 2) }}/hr</td>
                        <td>₱{{ number_format($payroll->night_differential_pay ?? 0, 2) }}</td>
                    </tr>
                    @endif
                    
                    <!-- Allowances -->
                    @if(($payroll->allowances ?? 0) > 0)
                    <tr>
                        <td>Allowances</td>
                        <td>-</td>
                        <td>-</td>
                        <td>₱{{ number_format($payroll->allowances, 2) }}</td>
                    </tr>
                    @endif
                    
                    <!-- Bonuses -->
                    @if(($payroll->bonuses ?? 0) > 0)
                    <tr>
                        <td>Bonuses</td>
                        <td>-</td>
                        <td>-</td>
                        <td>₱{{ number_format($payroll->bonuses, 2) }}</td>
                    </tr>
                    @endif
                    
                    <!-- Total Earnings -->
                    <tr class="total-row">
                        <td colspan="3" style="text-align: right;"><strong>TOTAL EARNINGS:</strong></td>
                        <td><strong>₱{{ number_format($payroll->gross_pay, 2) }}</strong></td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <!-- Deductions Section -->
        <div class="calculation-section">
            <h3>DEDUCTIONS</h3>
            
            <table class="salary-table">
                <thead>
                    <tr>
                        <th>Description</th>
                        <th>Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Statutory Deductions -->
                    <tr>
                        <td>SSS Contribution</td>
                        <td>₱{{ number_format($payroll->sss ?? 450, 2) }}</td>
                    </tr>
                    <tr>
                        <td>PhilHealth</td>
                        <td>₱{{ number_format($payroll->phic ?? ($payroll->monthly_rate >= 10000 ? 225.88 : 0), 2) }}</td>
                    </tr>
                    <tr>
                        <td>HDMF (Pag-IBIG)</td>
                        <td>₱{{ number_format($payroll->hdmf ?? 100, 2) }}</td>
                    </tr>
                    
                    <!-- Withholding Tax -->
                    @if(($payroll->tax_amount ?? 0) > 0)
                    <tr>
                        <td>Withholding Tax</td>
                        <td>₱{{ number_format($payroll->tax_amount, 2) }}</td>
                    </tr>
                    @endif
                    
                    <!-- Other Deductions -->
                    @if(($payroll->deductions ?? 0) > 0)
                    <tr>
                        <td>Other Deductions</td>
                        <td>₱{{ number_format($payroll->deductions, 2) }}</td>
                    </tr>
                    @endif
                    
                    <!-- Total Deductions -->
                    <tr class="total-row">
                        <td><strong>TOTAL DEDUCTIONS:</strong></td>
                        <td><strong>₱{{ number_format(($payroll->deductions ?? 0) + ($payroll->tax_amount ?? 0) + ($payroll->sss ?? 450) + ($payroll->phic ?? ($payroll->monthly_rate >= 10000 ? 225.88 : 0)) + ($payroll->hdmf ?? 100), 2) }}</strong></td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <!-- Net Pay Section -->
        <div class="calculation-section" style="background-color: #f9f9f9; border: 2px solid #000;">
            <h2 style="text-align: center; margin: 10px 0;">
                NET TAKE HOME PAY: ₱{{ number_format($payroll->net_pay, 2) }}
            </h2>
        </div>
        
        <!-- Rate Information -->
        <div style="margin-top: 20px; font-size: 10px; color: #666;">
            <p><strong>Rate Information:</strong></p>
            <p>Monthly Rate: ₱{{ number_format($payroll->monthly_rate ?? ($employee->salary ?? 0), 2) }}</p>
            <p>Daily Rate: ₱{{ number_format($payroll->daily_rate ?? 0, 2) }} (Formula: Monthly Rate × 12 ÷ 313)</p>
            <p>Hourly Rate: ₱{{ number_format($payroll->hourly_rate ?? 0, 2) }} (Formula: Daily Rate ÷ 8)</p>
        </div>
        
        <!-- Signatures -->
        <div class="signature-section">
            <div style="text-align: center;">
                <div style="display: inline-block; margin: 0 40px;">
                    <div class="signature-line"></div>
                    <div style="text-align: center;">Prepared by</div>
                </div>
                
                <div style="display: inline-block; margin: 0 40px;">
                    <div class="signature-line"></div>
                    <div style="text-align: center;">Checked by</div>
                </div>
                
                <div style="display: inline-block; margin: 0 40px;">
                    <div class="signature-line"></div>
                    <div style="text-align: center;">Received by</div>
                </div>
            </div>
        </div>
        
        <!-- Footer -->
        <div style="margin-top: 30px; text-align: center; font-size: 9px; color: #999; border-top: 1px solid #ccc; padding-top: 10px;">
            <p>Generated on {{ date('F j, Y, g:i a') }}</p>
            <p>This is a computer-generated document and does not require a signature.</p>
        </div>
    </div>
</body>
</html>
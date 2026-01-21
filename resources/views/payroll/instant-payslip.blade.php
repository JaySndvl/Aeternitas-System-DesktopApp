<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Payslip - {{ $employee->full_name }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #4a5568;
            padding-bottom: 20px;
        }
        .company-name {
            font-size: 24px;
            font-weight: bold;
            color: #2d3748;
            margin-bottom: 5px;
        }
        .payslip-title {
            font-size: 20px;
            color: #4a5568;
            margin-bottom: 10px;
        }
        .employee-info {
            background-color: #f7fafc;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .info-row {
            margin-bottom: 5px;
            display: flex;
        }
        .info-label {
            font-weight: bold;
            width: 150px;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        .table th, .table td {
            padding: 12px;
            border: 1px solid #e2e8f0;
            text-align: left;
        }
        .table th {
            background-color: #4a5568;
            color: white;
            font-weight: bold;
        }
        .amount {
            text-align: right;
            font-family: monospace;
        }
        .total-row {
            font-weight: bold;
            background-color: #edf2f7;
        }
        .net-pay {
            text-align: center;
            padding: 25px;
            border: 3px solid #2d3748;
            margin: 30px 0;
            background-color: #f0fff4;
        }
        .net-pay-amount {
            font-size: 28px;
            font-weight: bold;
            color: #2f855a;
            margin-top: 10px;
        }
        .footer {
            text-align: center;
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #e2e8f0;
            font-size: 12px;
            color: #718096;
        }
        .currency {
            font-family: monospace;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-name">{{ $company->name }}</div>
        <div class="payslip-title">EMPLOYEE PAYSLIP</div>
        <div style="color: #718096;">
            Period: {{ \Carbon\Carbon::parse($payroll->pay_period_start)->format('F j, Y') }} 
            to {{ \Carbon\Carbon::parse($payroll->pay_period_end)->format('F j, Y') }}
        </div>
        <div style="color: #718096; font-size: 14px;">Generated: {{ $today }}</div>
    </div>
    
    <div class="employee-info">
        <div class="info-row">
            <span class="info-label">Employee Name:</span>
            <span>{{ $employee->full_name }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Employee ID:</span>
            <span>{{ $employee->employee_id }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Department:</span>
            <span>{{ $employee->department->name ?? 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Payroll Status:</span>
            <span style="font-weight: bold; 
                @if($payroll->status === 'paid')
                    color: #38a169;
                @elseif($payroll->status === 'approved')
                    color: #3182ce;
                @elseif($payroll->status === 'pending')
                    color: #d69e2e;
                @else
                    color: #e53e3e;
                @endif">
                {{ strtoupper($payroll->status) }}
            </span>
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
                <td class="amount currency">₱{{ number_format($payroll->basic_salary, 2) }}</td>
            </tr>
            @if($payroll->overtime_pay > 0)
            <tr>
                <td>Overtime Pay</td>
                <td class="amount currency">₱{{ number_format($payroll->overtime_pay, 2) }}</td>
            </tr>
            @endif
            @if($payroll->allowances > 0)
            <tr>
                <td>Allowances</td>
                <td class="amount currency">₱{{ number_format($payroll->allowances, 2) }}</td>
            </tr>
            @endif
            @if($payroll->bonuses > 0)
            <tr>
                <td>Bonuses</td>
                <td class="amount currency">₱{{ number_format($payroll->bonuses, 2) }}</td>
            </tr>
            @endif
            @if($payroll->night_differential_pay > 0)
            <tr>
                <td>Night Differential</td>
                <td class="amount currency">₱{{ number_format($payroll->night_differential_pay, 2) }}</td>
            </tr>
            @endif
            <tr class="total-row">
                <td><strong>TOTAL EARNINGS</strong></td>
                <td class="amount currency"><strong>₱{{ number_format($payroll->gross_pay, 2) }}</strong></td>
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
        <tbody>
            @if($payroll->deductions > 0)
            <tr>
                <td>Deductions</td>
                <td class="amount currency">₱{{ number_format($payroll->deductions, 2) }}</td>
            </tr>
            @endif
            @if($payroll->tax_amount > 0)
            <tr>
                <td>Tax Withholding</td>
                <td class="amount currency">₱{{ number_format($payroll->tax_amount, 2) }}</td>
            </tr>
            @endif
            @if($payroll->sss > 0)
            <tr>
                <td>SSS Contribution</td>
                <td class="amount currency">₱{{ number_format($payroll->sss, 2) }}</td>
            </tr>
            @endif
            @if($payroll->phic > 0)
            <tr>
                <td>PhilHealth</td>
                <td class="amount currency">₱{{ number_format($payroll->phic, 2) }}</td>
            </tr>
            @endif
            @if($payroll->hdmf > 0)
            <tr>
                <td>Pag-IBIG</td>
                <td class="amount currency">₱{{ number_format($payroll->hdmf, 2) }}</td>
            </tr>
            @endif
            <tr class="total-row">
                <td><strong>TOTAL DEDUCTIONS</strong></td>
                <td class="amount currency">
                    <strong>₱{{ number_format($payroll->deductions + $payroll->tax_amount + ($payroll->sss ?? 0) + ($payroll->phic ?? 0) + ($payroll->hdmf ?? 0), 2) }}</strong>
                </td>
            </tr>
        </tbody>
    </table>
    
    <div class="net-pay">
        <div style="font-size: 18px; font-weight: bold; color: #2d3748;">NET PAY</div>
        <div class="net-pay-amount currency">₱{{ number_format($payroll->net_pay, 2) }}</div>
        <div style="color: #718096; margin-top: 10px;">
            {{ number_format($payroll->net_pay, 2) }} Philippine Pesos
        </div>
    </div>
    
    <div class="footer">
        <p>Generated by Aeternitas Payroll System</p>
        <p>This is an official document. Unauthorized distribution is prohibited.</p>
        <p>Document ID: PAYSLIP-{{ strtoupper(substr(md5($payroll->id . $payroll->pay_period_start), 0, 12)) }}</p>
    </div>
</body>
</html>
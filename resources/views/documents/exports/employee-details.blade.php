<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Employee Details: {{ $employee->full_name }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
        }
        .header h1 {
            color: #2d3748;
            margin-bottom: 5px;
        }
        .employee-info {
            display: flex;
            align-items: center;
            margin-bottom: 30px;
            padding: 20px;
            background-color: #f7fafc;
            border-radius: 5px;
        }
        .avatar {
            width: 80px;
            height: 80px;
            background-color: #4299e1;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 24px;
            font-weight: bold;
            margin-right: 20px;
        }
        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }
        .info-card {
            padding: 15px;
            border: 1px solid #cbd5e0;
            border-radius: 5px;
        }
        .info-card h3 {
            margin-top: 0;
            color: #2d3748;
            border-bottom: 1px solid #cbd5e0;
            padding-bottom: 5px;
            margin-bottom: 10px;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
        }
        .label {
            font-weight: bold;
            color: #4a5568;
        }
        .value {
            color: #2d3748;
        }
        .salary-boxes {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin-bottom: 30px;
        }
        .salary-box {
            padding: 15px;
            border-radius: 5px;
            text-align: center;
        }
        .salary-box.monthly {
            background-color: #bee3f8;
        }
        .salary-box.annual {
            background-color: #c6f6d5;
        }
        .salary-box.tax {
            background-color: #e9d8fd;
        }
        .salary-label {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .salary-amount {
            font-size: 18px;
            font-weight: bold;
        }
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #cbd5e0;
            text-align: center;
            color: #718096;
            font-size: 10px;
        }
    </style>
</head>
<body>
    @php
        // Determine status color
        $statusColor = '#718096'; // default gray
        if ($employee->status == 'active') {
            $statusColor = '#38a169'; // green
        } elseif ($employee->status == 'on-leave') {
            $statusColor = '#d69e2e'; // yellow
        } else {
            $statusColor = '#e53e3e'; // red
        }
        
        // Calculate salary and tax
        $monthlySalary = $employee->salary ?? 0;
        $annualSalary = $monthlySalary * 12;
        
        // Calculate tax estimate (Philippines tax computation)
        $tax = 0;
        if ($annualSalary > 8000000) {
            $tax = 2410000 + ($annualSalary - 8000000) * 0.35;
        } elseif ($annualSalary > 2000000) {
            $tax = 490000 + ($annualSalary - 2000000) * 0.32;
        } elseif ($annualSalary > 800000) {
            $tax = 130000 + ($annualSalary - 800000) * 0.30;
        } elseif ($annualSalary > 400000) {
            $tax = 30000 + ($annualSalary - 400000) * 0.25;
        } elseif ($annualSalary > 250000) {
            $tax = ($annualSalary - 250000) * 0.20;
        }
    @endphp
    
    <div class="header">
        <h1>Employee Details Report</h1>
        <p>Generated on: {{ $export_date }}</p>
    </div>
    
    <div class="employee-info">
        <div class="avatar">
            {{ substr($employee->first_name, 0, 1) }}{{ substr($employee->last_name, 0, 1) }}
        </div>
        <div>
            <h2 style="margin: 0 0 5px 0;">{{ $employee->full_name }}</h2>
            <p style="margin: 0 0 5px 0; color: #4a5568;">{{ $employee->position }}</p>
            <p style="margin: 0; color: #718096;">{{ $employee->department->name ?? 'No Department' }}</p>
        </div>
    </div>
    
    <div class="info-grid">
        <div class="info-card">
            <h3>Personal Information</h3>
            <div class="info-row">
                <span class="label">Employee ID:</span>
                <span class="value">{{ $employee->employee_id }}</span>
            </div>
            <div class="info-row">
                <span class="label">Email:</span>
                <span class="value">{{ $employee->email ?? 'Not provided' }}</span>
            </div>
            <div class="info-row">
                <span class="label">Phone:</span>
                <span class="value">{{ $employee->phone ?? 'Not provided' }}</span>
            </div>
            <div class="info-row">
                <span class="label">Address:</span>
                <span class="value">{{ $employee->address ?? 'Not provided' }}</span>
            </div>
        </div>
        
        <div class="info-card">
            <h3>Employment Information</h3>
            <div class="info-row">
                <span class="label">Status:</span>
                <span class="value" style="color: {{ $statusColor }}">
                    {{ ucfirst($employee->status ?? 'active') }}
                </span>
            </div>
            <div class="info-row">
                <span class="label">Hire Date:</span>
                <span class="value">
                    @if($employee->hire_date)
                        {{ \Carbon\Carbon::parse($employee->hire_date)->format('M j, Y') }}
                    @else
                        Not provided
                    @endif
                </span>
            </div>
            <div class="info-row">
                <span class="label">Employment Type:</span>
                <span class="value">{{ ucfirst($employee->employment_type ?? 'Regular') }}</span>
            </div>
            <div class="info-row">
                <span class="label">Department:</span>
                <span class="value">{{ $employee->department->name ?? 'Not assigned' }}</span>
            </div>
        </div>
    </div>
    
    <div class="salary-boxes">
        <div class="salary-box monthly">
            <div class="salary-label">Monthly Salary</div>
            <div class="salary-amount">₱{{ number_format($monthlySalary, 2) }}</div>
        </div>
        
        <div class="salary-box annual">
            <div class="salary-label">Annual Salary</div>
            <div class="salary-amount">₱{{ number_format($annualSalary, 2) }}</div>
        </div>
        
        <div class="salary-box tax">
            <div class="salary-label">Annual Tax Estimate</div>
            <div class="salary-amount">₱{{ number_format($tax, 2) }}</div>
        </div>
    </div>
    
    <div class="info-card">
        <h3>Government IDs & Numbers</h3>
        <div class="info-grid" style="grid-template-columns: repeat(2, 1fr); gap: 10px; margin-top: 15px;">
            <div class="info-row">
                <span class="label">TIN:</span>
                <span class="value">{{ $employee->tin ?? 'Not provided' }}</span>
            </div>
            <div class="info-row">
                <span class="label">SSS Number:</span>
                <span class="value">{{ $employee->sss_number ?? 'Not provided' }}</span>
            </div>
            <div class="info-row">
                <span class="label">Pag-IBIG Number:</span>
                <span class="value">{{ $employee->pagibig_number ?? 'Not provided' }}</span>
            </div>
            <div class="info-row">
                <span class="label">PhilHealth Number:</span>
                <span class="value">{{ $employee->philhealth_number ?? 'Not provided' }}</span>
            </div>
        </div>
    </div>
    
    <div class="footer">
        <p>Generated by Aeternitas System • Confidential Employee Document</p>
        <p>This document contains sensitive employee information. Handle with care.</p>
    </div>
</body>
</html>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Payslip #{{ $payroll->id ?? 'N/A' }}</title>
  <style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #222; }
    .header { text-align: center; margin-bottom: 12px; }
    .section { margin-bottom: 8px; }
    table { width: 100%; border-collapse: collapse; }
    th, td { padding: 6px; border: 1px solid #ddd; text-align: left; }
    .right { text-align: right; }
    h2 { margin: 0 0 6px 0; }
    .meta { font-size: 11px; color: #666; margin-bottom: 8px; }
  </style>
</head>
<body>
  <div class="header">
    <h2>Payslip</h2>
    <div class="meta">
      Employee: {{ $employee->name ?? ($employee->first_name . ' ' . $employee->last_name ?? 'Unknown') }} |
      Period: {{ $payroll->pay_period_start }} - {{ $payroll->pay_period_end }} |
      Payslip ID: {{ $payroll->id ?? 'N/A' }}
    </div>
  </div>

  <div class="section">
    <table>
      <tr>
        <th>Description</th>
        <th class="right">Amount (PHP)</th>
      </tr>

      <tr><td>Basic Salary</td><td class="right">{{ number_format($payroll->basic_salary ?? 0, 2) }}</td></tr>
      <tr><td>Holiday Basic Pay</td><td class="right">{{ number_format($payroll->holiday_basic_pay ?? 0, 2) }}</td></tr>
      <tr><td>Holiday Premium</td><td class="right">{{ number_format($payroll->holiday_premium ?? 0, 2) }}</td></tr>
      <tr><td>Special Holiday Premium</td><td class="right">{{ number_format($payroll->special_holiday_premium ?? 0, 2) }}</td></tr>
      <tr><td>Overtime Pay</td><td class="right">{{ number_format($payroll->overtime_pay ?? 0, 2) }}</td></tr>
      <tr><td>Night Differential</td><td class="right">{{ number_format($payroll->night_differential_pay ?? 0, 2) }}</td></tr>
      <tr><td>Rest Day Premium</td><td class="right">{{ number_format($payroll->rest_day_premium_pay ?? 0, 2) }}</td></tr>
      <tr><td>Bonuses</td><td class="right">{{ number_format($payroll->bonuses ?? 0, 2) }}</td></tr>

      <tr><th>Total Gross</th><th class="right">{{ number_format($payroll->gross_pay ?? 0, 2) }}</th></tr>

      <tr><td>Deductions</td><td class="right">{{ number_format($payroll->deductions ?? 0, 2) }}</td></tr>
      <tr><td>Tax</td><td class="right">{{ number_format($payroll->tax_amount ?? 0, 2) }}</td></tr>

      <tr><th>Net Pay</th><th class="right">{{ number_format($payroll->net_pay ?? 0, 2) }}</th></tr>
    </table>
  </div>

  <div class="section">
    <small>Generated at {{ now()->toDateTimeString() }}</small>
  </div>
</body>
</html>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Attendance Report - {{ ucfirst($reportType) }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            margin: 0;
            padding: 20px;
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
        .summary {
            margin: 20px 0;
            padding: 15px;
            background-color: #f5f5f5;
            border-radius: 5px;
        }
        .summary h2 {
            margin: 0 0 10px 0;
            font-size: 14px;
            color: #333;
        }
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
        }
        .summary-item {
            padding: 8px;
            background-color: white;
            border-radius: 3px;
        }
        .summary-item strong {
            display: block;
            font-size: 16px;
            color: #333;
        }
        .summary-item span {
            font-size: 11px;
            color: #666;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            margin-bottom: 20px;
        }
        th {
            background-color: #4472C4;
            color: white;
            padding: 8px;
            text-align: left;
            font-weight: bold;
            border: 1px solid #333;
        }
        td {
            padding: 6px;
            border: 1px solid #ddd;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .section-title {
            font-size: 14px;
            font-weight: bold;
            margin-top: 20px;
            margin-bottom: 10px;
            color: #333;
        }
        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 9px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Attendance Report - {{ ucfirst($reportType) }}</h1>
        <p>Date Range: {{ \Carbon\Carbon::parse($dateFrom)->format('M d, Y') }} to {{ \Carbon\Carbon::parse($dateTo)->format('M d, Y') }}</p>
        <p>Generated on: {{ $date }}</p>
    </div>

    @if($reportType === 'overtime')
        <!-- Overtime Report -->
        <div class="summary">
            <h2>Overtime Report Summary</h2>
            <div class="summary-grid">
                <div class="summary-item">
                    <strong>{{ $overtimeData['summary']['total_requests'] ?? 0 }}</strong>
                    <span>Total Requests</span>
                </div>
                <div class="summary-item">
                    <strong>{{ number_format($overtimeData['summary']['total_hours'] ?? 0, 2) }}h</strong>
                    <span>Total Hours</span>
                </div>
                <div class="summary-item">
                    <strong>{{ $overtimeData['summary']['total_employees'] ?? 0 }}</strong>
                    <span>Total Employees</span>
                </div>
                <div class="summary-item">
                    <strong>{{ number_format($overtimeData['summary']['average_hours'] ?? 0, 2) }}h</strong>
                    <span>Average Hours</span>
                </div>
            </div>
        </div>

        <div class="section-title">Approved Overtime Requests by Employee</div>
        <table>
            <thead>
                <tr>
                    <th>Employee</th>
                    <th>Department</th>
                    <th>Total Requests</th>
                    <th>Total Hours</th>
                    <th>Average Hours</th>
                </tr>
            </thead>
            <tbody>
                @forelse($overtimeData['by_employee'] ?? [] as $emp)
                    <tr>
                        <td>{{ $emp['employee_name'] }}</td>
                        <td>{{ $emp['department'] ?? 'N/A' }}</td>
                        <td>{{ $emp['total_requests'] }}</td>
                        <td>{{ number_format($emp['total_hours'], 2) }}h</td>
                        <td>{{ number_format($emp['average_hours'], 2) }}h</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" style="text-align: center;">No overtime requests found</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

    @elseif($reportType === 'leave')
        <!-- Leave Report -->
        <div class="summary">
            <h2>Leave Report Summary</h2>
            <div class="summary-grid">
                <div class="summary-item">
                    <strong>{{ $leaveData['summary']['total_requests'] ?? 0 }}</strong>
                    <span>Total Requests</span>
                </div>
                <div class="summary-item">
                    <strong>{{ $leaveData['summary']['total_days'] ?? 0 }}</strong>
                    <span>Total Days</span>
                </div>
                <div class="summary-item">
                    <strong>{{ $leaveData['summary']['total_employees'] ?? 0 }}</strong>
                    <span>Total Employees</span>
                </div>
                <div class="summary-item">
                    <strong>{{ number_format($leaveData['summary']['average_days'] ?? 0, 2) }}</strong>
                    <span>Average Days</span>
                </div>
            </div>
        </div>

        <div class="section-title">Approved Leave Requests by Employee</div>
        <table>
            <thead>
                <tr>
                    <th>Employee</th>
                    <th>Department</th>
                    <th>Total Requests</th>
                    <th>Total Days</th>
                    <th>Average Days</th>
                </tr>
            </thead>
            <tbody>
                @forelse($leaveData['by_employee'] ?? [] as $emp)
                    <tr>
                        <td>{{ $emp['employee_name'] }}</td>
                        <td>{{ $emp['department'] ?? 'N/A' }}</td>
                        <td>{{ $emp['total_requests'] }}</td>
                        <td>{{ $emp['total_days'] }}</td>
                        <td>{{ number_format($emp['average_days'], 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" style="text-align: center;">No leave requests found</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

    @else
        <!-- Attendance Report -->
        <div class="summary">
            <h2>Attendance Report Summary</h2>
            <div class="summary-grid">
                <div class="summary-item">
                    <strong>{{ $summary['present_days'] ?? 0 }}</strong>
                    <span>Present Days</span>
                </div>
                <div class="summary-item">
                    <strong>{{ $summary['absent_days'] ?? 0 }}</strong>
                    <span>Absent Days</span>
                </div>
                <div class="summary-item">
                    <strong>{{ $summary['late_arrivals'] ?? 0 }}</strong>
                    <span>Late Arrivals</span>
                </div>
                <div class="summary-item">
                    <strong>{{ number_format($summary['attendance_rate'] ?? 0, 1) }}%</strong>
                    <span>Attendance Rate</span>
                </div>
            </div>
        </div>

        <div class="section-title">Department-wise Attendance</div>
        <table>
            <thead>
                <tr>
                    <th>Department</th>
                    <th>Total Employees</th>
                    <th>Present</th>
                    <th>Absent</th>
                    <th>Late</th>
                    <th>Attendance Rate</th>
                </tr>
            </thead>
            <tbody>
                @forelse($departmentStats as $stat)
                    <tr>
                        <td>{{ $stat['department'] }}</td>
                        <td>{{ $stat['total_employees'] }}</td>
                        <td>{{ $stat['present'] }}</td>
                        <td>{{ $stat['absent'] }}</td>
                        <td>{{ $stat['late'] }}</td>
                        <td>{{ number_format($stat['attendance_rate'], 1) }}%</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="text-align: center;">No department data available</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="section-title">Top Performers</div>
        <table>
            <thead>
                <tr>
                    <th>Employee</th>
                    <th>Department</th>
                    <th>Attendance Rate</th>
                    <th>Present Days</th>
                    <th>Absent Days</th>
                </tr>
            </thead>
            <tbody>
                @forelse(($bestAttendance ?? []) as $emp)
                    @php
                        if (!is_array($emp)) {
                            continue;
                        }
                        $employee = array_key_exists('employee', $emp) ? $emp['employee'] : null;
                        $employeeName = array_key_exists('employee_name', $emp) ? $emp['employee_name'] : ($employee && is_object($employee) ? ($employee->full_name ?? 'N/A') : 'N/A');
                        $department = array_key_exists('department', $emp) ? $emp['department'] : ($employee && is_object($employee) && isset($employee->department) ? ($employee->department->name ?? 'N/A') : 'N/A');
                        $rate = array_key_exists('rate', $emp) ? (float)$emp['rate'] : 0;
                        $presentDays = array_key_exists('present_days', $emp) ? (int)$emp['present_days'] : 0;
                        $absentDays = array_key_exists('absent_days', $emp) ? (int)$emp['absent_days'] : 0;
                    @endphp
                    <tr>
                        <td>{{ $employeeName }}</td>
                        <td>{{ $department }}</td>
                        <td>{{ number_format($rate, 1) }}%</td>
                        <td>{{ $presentDays }}</td>
                        <td>{{ $absentDays }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" style="text-align: center;">No data available</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="section-title">Needs Attention</div>
        <table>
            <thead>
                <tr>
                    <th>Employee</th>
                    <th>Department</th>
                    <th>Attendance Rate</th>
                    <th>Absent Days</th>
                    <th>Late Arrivals</th>
                    <th>Consecutive Absences</th>
                </tr>
            </thead>
            <tbody>
                @forelse($needsAttention as $item)
                    @php
                        $employee = $item['employee'];
                    @endphp
                    <tr>
                        <td>{{ $employee->full_name }}</td>
                        <td>{{ $employee->department->name ?? 'N/A' }}</td>
                        <td>{{ number_format($item['rate'], 1) }}%</td>
                        <td>{{ $item['absent_days'] ?? 0 }}</td>
                        <td>{{ $item['late_arrivals'] ?? 0 }}</td>
                        <td>{{ $item['consecutive_absences'] ?? 0 }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="text-align: center;">No employees need attention</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    @endif

    <div class="footer">
        <p>This report was generated automatically by the Attendance Management System</p>
    </div>
</body>
</html>


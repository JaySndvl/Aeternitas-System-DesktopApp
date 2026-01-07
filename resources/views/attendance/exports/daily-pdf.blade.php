<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Daily Attendance Report</title>
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
            margin-bottom: 15px;
            padding: 10px;
            background-color: #f9f9f9;
            border-radius: 5px;
        }
        .summary h3 {
            margin: 0 0 10px 0;
            font-size: 12px;
            color: #333;
        }
        .summary-row {
            display: flex;
            justify-content: space-around;
            margin-top: 5px;
        }
        .summary-item {
            text-align: center;
        }
        .summary-item strong {
            display: block;
            font-size: 14px;
            color: #333;
        }
        .summary-item span {
            font-size: 10px;
            color: #666;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
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
        .status-present {
            color: #10b981;
            font-weight: bold;
        }
        .status-absent {
            color: #ef4444;
            font-weight: bold;
        }
        .status-late {
            color: #f59e0b;
            font-weight: bold;
        }
        .status-leave {
            color: #3b82f6;
            font-weight: bold;
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
        <h1>Daily Attendance Report</h1>
        <p>Date: {{ $date->format('F d, Y') }}</p>
        <p>Generated on: {{ now()->format('F d, Y h:i A') }}</p>
    </div>

    <div class="summary">
        <h3>Summary</h3>
        <div class="summary-row">
            <div class="summary-item">
                <strong>{{ $summary['present'] ?? 0 }}</strong>
                <span>Present</span>
            </div>
            <div class="summary-item">
                <strong>{{ $summary['absent'] ?? 0 }}</strong>
                <span>Absent</span>
            </div>
            <div class="summary-item">
                <strong>{{ $summary['late'] ?? 0 }}</strong>
                <span>Late</span>
            </div>
            <div class="summary-item">
                <strong>{{ $summary['half_day'] ?? 0 }}</strong>
                <span>Half Day</span>
            </div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Employee ID</th>
                <th>Employee Name</th>
                <th>Department</th>
                <th>Time In</th>
                <th>Time Out</th>
                <th>Total Hours</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($employees as $employee)
                @php
                    $attendance = $attendanceRecords->get($employee->id);
                    
                    $timeIn = 'N/A';
                    $timeOut = 'N/A';
                    $totalHours = 'N/A';
                    $status = 'No Record';
                    $statusClass = 'status-absent';
                    
                    if ($attendance) {
                        // Format Time In
                        if ($attendance->time_in) {
                            $timeIn = \Carbon\Carbon::parse($attendance->time_in)->format('g:i A');
                        }
                        
                        // Format Time Out
                        if ($attendance->time_out) {
                            $timeOut = \Carbon\Carbon::parse($attendance->time_out)->format('g:i A');
                        } elseif ($attendance->time_in) {
                            $recordDate = \Carbon\Carbon::parse($attendance->date);
                            $timeOut = $recordDate->isToday() ? 'Working' : 'Not Clocked Out';
                        }
                        
                        // Calculate Total Hours
                        if ($attendance->total_hours) {
                            $totalHours = \App\Helpers\TimezoneHelper::formatHours($attendance->total_hours);
                        } elseif ($attendance->time_in && $attendance->time_out) {
                            $totalHours = \App\Helpers\TimezoneHelper::formatHours($attendance->calculateTotalHours());
                        }
                        
                        // Get Status
                        $status = ucfirst(str_replace('_', ' ', $attendance->status));
                        
                        // Set status class based on status
                        if (in_array($attendance->status, ['present'])) {
                            $statusClass = 'status-present';
                        } elseif (in_array($attendance->status, ['absent', 'absent_unexcused'])) {
                            $statusClass = 'status-absent';
                        } elseif (in_array($attendance->status, ['late'])) {
                            $statusClass = 'status-late';
                        } elseif (in_array($attendance->status, ['on_leave', 'half_day'])) {
                            $statusClass = 'status-leave';
                        }
                    }
                @endphp
            <tr>
                <td>{{ $employee->employee_id ?? 'N/A' }}</td>
                <td>{{ $employee->full_name ?? 'N/A' }}</td>
                <td>{{ $employee->department->name ?? 'N/A' }}</td>
                <td>{{ $timeIn }}</td>
                <td>{{ $timeOut }}</td>
                <td>{{ $totalHours }}</td>
                <td class="{{ $statusClass }}">{{ $status }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="7" style="text-align: center; padding: 20px;">No employees found</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>Total Employees: {{ $employees->count() }}</p>
    </div>
</body>
</html>


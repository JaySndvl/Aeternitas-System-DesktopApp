<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Attendance Records Report</title>
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
        <h1>Attendance Records Report</h1>
        <p>Generated on: {{ $date }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Employee Code</th>
                <th>Employee Name</th>
                <th>Department</th>
                <th>Time In</th>
                <th>Time Out</th>
                <th>Hours Worked</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($records as $record)
            <tr>
                <td>{{ \Carbon\Carbon::parse($record->date)->format('Y-m-d') }}</td>
                <td>{{ $record->employee->employee_code ?? 'N/A' }}</td>
                <td>{{ $record->employee->full_name ?? 'N/A' }}</td>
                <td>{{ $record->employee->department->name ?? 'N/A' }}</td>
                <td>
                    @if($record->time_in)
                        {{ $record->time_in instanceof \Carbon\Carbon ? $record->time_in->format('H:i:s') : (is_string($record->time_in) ? substr($record->time_in, 11, 8) : 'N/A') }}
                    @else
                        N/A
                    @endif
                </td>
                <td>
                    @if($record->time_out)
                        {{ $record->time_out instanceof \Carbon\Carbon ? $record->time_out->format('H:i:s') : (is_string($record->time_out) ? substr($record->time_out, 11, 8) : 'N/A') }}
                    @else
                        N/A
                    @endif
                </td>
                <td>
                    @if($record->time_in && $record->time_out)
                        @php
                            // time_in and time_out are already Carbon datetime instances
                            $timeIn = $record->time_in instanceof \Carbon\Carbon ? $record->time_in : \Carbon\Carbon::parse($record->time_in);
                            $timeOut = $record->time_out instanceof \Carbon\Carbon ? $record->time_out : \Carbon\Carbon::parse($record->time_out);
                            $hoursWorked = round($timeIn->diffInMinutes($timeOut) / 60, 2);
                        @endphp
                        {{ $hoursWorked }} hrs
                    @else
                        N/A
                    @endif
                </td>
                <td>{{ $record->time_out ? 'Complete' : 'Incomplete' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="8" style="text-align: center; padding: 20px;">No records found</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>Total Records: {{ $records->count() }}</p>
    </div>
</body>
</html>


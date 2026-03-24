@extends('layouts.pdf-base')

@section('title', 'Attendance Records Report')

@section('content')
<div class="pdf-header">
    <div class="document-title">Attendance Records Report</div>
    <div class="document-info">Generated on: {{ $date }}</div>
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


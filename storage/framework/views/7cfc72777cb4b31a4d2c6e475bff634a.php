<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Overtime Requests Report</title>
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
        <h1>Overtime Requests Report</h1>
        <p>Generated on: <?php echo e($date); ?></p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Employee Code</th>
                <th>Employee Name</th>
                <th>Department</th>
                <th>Start Time</th>
                <th>End Time</th>
                <th>Hours</th>
                <th>Rate</th>
                <th>Amount</th>
                <th>Reason</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $records; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $record): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <?php
                    $startTime = $record->start_time ? ($record->start_time instanceof \Carbon\Carbon ? $record->start_time->format('H:i:s') : (is_string($record->start_time) ? substr($record->start_time, 11, 8) : $record->start_time)) : 'N/A';
                    $endTime = $record->end_time ? ($record->end_time instanceof \Carbon\Carbon ? $record->end_time->format('H:i:s') : (is_string($record->end_time) ? substr($record->end_time, 11, 8) : $record->end_time)) : 'N/A';
                    
                    // Calculate amount
                    $hourlyRate = $record->employee->hourly_rate ?? 0;
                    $amount = $record->hours * $record->rate_multiplier * $hourlyRate;
                ?>
            <tr>
                <td><?php echo e(\Carbon\Carbon::parse($record->date)->format('Y-m-d')); ?></td>
                <td><?php echo e($record->employee->employee_code ?? 'N/A'); ?></td>
                <td><?php echo e($record->employee->full_name ?? 'N/A'); ?></td>
                <td><?php echo e($record->employee->department->name ?? 'N/A'); ?></td>
                <td><?php echo e($startTime); ?></td>
                <td><?php echo e($endTime); ?></td>
                <td><?php echo e($record->hours ?? 0); ?></td>
                <td><?php echo e($record->rate_multiplier ?? 1); ?>x</td>
                <td><?php echo e(number_format($amount, 2)); ?></td>
                <td><?php echo e($record->reason ?? 'N/A'); ?></td>
                <td><?php echo e(ucfirst($record->status ?? 'pending')); ?></td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <tr>
                <td colspan="11" style="text-align: center; padding: 20px;">No records found</td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="footer">
        <p>Total Records: <?php echo e($records->count()); ?></p>
    </div>
</body>
</html>

<?php /**PATH C:\Users\caior\Documents\Mica files\Aeternitas-System-V2\resources\views/attendance/exports/overtime-pdf.blade.php ENDPATH**/ ?>
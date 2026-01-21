<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Leave Requests Report</title>
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
        <h1>Leave Requests Report</h1>
        <p>Generated on: <?php echo e($date); ?></p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Employee Code</th>
                <th>Employee Name</th>
                <th>Department</th>
                <th>Leave Type</th>
                <th>Start Date</th>
                <th>End Date</th>
                <th>Days</th>
                <th>Reason</th>
                <th>Status</th>
                <th>Approved By</th>
            </tr>
        </thead>
        <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $records; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $record): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <tr>
                <td><?php echo e($record->employee->employee_code ?? 'N/A'); ?></td>
                <td><?php echo e($record->employee->full_name ?? 'N/A'); ?></td>
                <td><?php echo e($record->employee->department->name ?? 'N/A'); ?></td>
                <td><?php echo e(ucfirst(str_replace('_', ' ', $record->leave_type ?? 'N/A'))); ?></td>
                <td><?php echo e(\Carbon\Carbon::parse($record->start_date)->format('Y-m-d')); ?></td>
                <td><?php echo e(\Carbon\Carbon::parse($record->end_date)->format('Y-m-d')); ?></td>
                <td><?php echo e($record->days_requested ?? 0); ?></td>
                <td><?php echo e(Str::limit($record->reason ?? 'N/A', 30)); ?></td>
                <td><?php echo e(ucfirst($record->status ?? 'pending')); ?></td>
                <td><?php echo e($record->approvedBy ? $record->approvedBy->full_name : 'N/A'); ?></td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <tr>
                <td colspan="10" style="text-align: center; padding: 20px;">No records found</td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="footer">
        <p>Total Records: <?php echo e($records->count()); ?></p>
    </div>
</body>
</html>

<?php /**PATH C:\xampp\htdocs\Aeternitas-System-V2\resources\views/attendance/exports/leave-pdf.blade.php ENDPATH**/ ?>
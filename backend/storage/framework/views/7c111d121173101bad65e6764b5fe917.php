<?php $__env->startSection('title', 'Review DTR Import'); ?>

<?php $__env->startSection('content'); ?>
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Review DTR Import</h1>
            <p class="mt-1 text-sm text-gray-600">Review and confirm the imported data before proceeding</p>
            <p class="mt-1 text-xs text-gray-500">File: <?php echo e($fileName); ?></p>
        </div>
        <div class="mt-4 sm:mt-0">
            <a href="<?php echo e(route('attendance.import-dtr')); ?>" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                <i class="fas fa-arrow-left mr-2"></i>
                Back to Upload
            </a>
        </div>
    </div>

    <!-- Validation Summary -->
    <?php if($validation['errors']->isNotEmpty() || $validation['warnings']->isNotEmpty()): ?>
    <div class="space-y-4">
        <?php if($validation['errors']->isNotEmpty()): ?>
        <div class="bg-red-50 border border-red-200 rounded-lg p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-exclamation-circle text-red-400"></i>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-red-800">Validation Errors (<?php echo e($validation['errors']->count()); ?>)</h3>
                    <div class="mt-2 text-sm text-red-700">
                        <ul class="list-disc list-inside space-y-1">
                            <?php $__currentLoopData = $validation['errors']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <li><?php echo e($error); ?></li>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php if($validation['warnings']->isNotEmpty()): ?>
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-exclamation-triangle text-yellow-400"></i>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-yellow-800">Warnings (<?php echo e($validation['warnings']->count()); ?>)</h3>
                    <div class="mt-2 text-sm text-yellow-700">
                        <ul class="list-disc list-inside space-y-1">
                            <?php $__currentLoopData = $validation['warnings']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $warning): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <li><?php echo e($warning); ?></li>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- Import Summary -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 sm:p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Import Summary</h3>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="bg-blue-50 rounded-lg p-4">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-users text-blue-600"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-blue-800">Total Records</p>
                        <p class="text-2xl font-bold text-blue-900"><?php echo e($parsedData->count()); ?></p>
                    </div>
                </div>
            </div>
            
            <div class="bg-green-50 rounded-lg p-4">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-check-circle text-green-600"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-green-800">Valid Records</p>
                        <p class="text-2xl font-bold text-green-900"><?php echo e($parsedData->where('status', '!=', 'absent')->count()); ?></p>
                    </div>
                </div>
            </div>
            
            <div class="bg-orange-50 rounded-lg p-4">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-clock text-orange-600"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-orange-800">Total Hours</p>
                        <p class="text-2xl font-bold text-orange-900"><?php echo e($parsedData->sum('total_hours')); ?></p>
                    </div>
                </div>
            </div>
            
            <div class="bg-red-50 rounded-lg p-4">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-clock text-red-600"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-red-800">Overtime Hours</p>
                        <p class="text-2xl font-bold text-red-900"><?php echo e($parsedData->sum('overtime_hours')); ?></p>
                    </div>
                </div>
            </div>
            
            <div class="bg-red-100 rounded-lg p-4">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-exclamation-triangle text-red-600"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-red-800">Errors</p>
                        <p class="text-2xl font-bold text-red-900"><?php echo e($parsedData->where('status', 'error')->count()); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Temporary Storage Information -->
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
        <div class="flex">
            <div class="flex-shrink-0">
                <i class="fas fa-info-circle text-blue-500"></i>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-blue-800">Temporary Storage</h3>
                <div class="mt-2 text-sm text-blue-700">
                    <p>When you click "Save to Temporary Storage", the data will be saved to a temporary table for review and final processing. This allows you to:</p>
                    <ul class="list-disc list-inside mt-2 space-y-1">
                        <li>Review the data before final import to attendance records</li>
                        <li>Make corrections if needed</li>
                        <li>Process the data in batches</li>
                        <li>Track import history with batch IDs</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Data Preview - Collapsible by Employee -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="px-4 py-5 sm:p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">Data Preview - Grouped by Employee</h3>
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-3">
                    <div class="flex items-start">
                        <i class="fas fa-info-circle text-blue-500 mt-0.5 mr-2"></i>
                        <div class="text-sm text-blue-700">
                            <p class="font-medium">All attendance data is shown</p>
                            <p class="text-xs mt-1">Even if no schedule exists for that day, all CSV/Excel data will be displayed with calculated hours and overtime.</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Filter Buttons -->
            <div class="mb-4">
                <div class="flex flex-wrap gap-2 mb-3">
                    <button onclick="filterByStatus('all')" class="filter-btn px-3 py-1 text-sm rounded-full border border-gray-300 bg-white text-gray-700 hover:bg-gray-50" data-status="all">
                        All Records
                    </button>
                    <button onclick="filterByStatus('error')" class="filter-btn px-3 py-1 text-sm rounded-full border border-red-300 bg-red-50 text-red-700 hover:bg-red-100" data-status="error">
                        <i class="fas fa-exclamation-triangle mr-1"></i>
                        Errors Only
                    </button>
                    <button onclick="filterByStatus('present')" class="filter-btn px-3 py-1 text-sm rounded-full border border-green-300 bg-green-50 text-green-700 hover:bg-green-100" data-status="present">
                        Present
                    </button>
                    <button onclick="filterByStatus('absent')" class="filter-btn px-3 py-1 text-sm rounded-full border border-red-300 bg-red-50 text-red-700 hover:bg-red-100" data-status="absent">
                        Absent
                    </button>
                    <button onclick="filterByStatus('late')" class="filter-btn px-3 py-1 text-sm rounded-full border border-yellow-300 bg-yellow-50 text-yellow-700 hover:bg-yellow-100" data-status="late">
                        Late
                    </button>
                    <button onclick="filterByStatus('day_off')" class="filter-btn px-3 py-1 text-sm rounded-full border border-gray-300 bg-gray-50 text-gray-700 hover:bg-gray-100" data-status="day_off">
                        Day Off
                    </button>
                </div>
                <div class="text-sm text-gray-600">
                    <span id="filterStatus">Showing all records</span>
                </div>
            </div>
            
            <?php
                // Group data by employee
                $groupedData = $parsedData->groupBy('employee_id');
            ?>
            
            <div class="space-y-4">
                <?php $__currentLoopData = $groupedData; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $employeeId => $employeeRecords): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="border border-gray-200 rounded-lg" x-data="{ open: false }">
                    <!-- Employee Header (Always Visible) -->
                    <div class="bg-gray-50 px-4 py-3 cursor-pointer" @click="open = !open">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-user text-gray-400"></i>
                                </div>
                                <div>
                                    <h4 class="text-sm font-medium text-gray-900"><?php echo e($employeeId); ?></h4>
                                    <p class="text-sm text-gray-500"><?php echo e($employeeRecords->first()['employee_name'] ?? 'Unknown Employee'); ?></p>
                                </div>
                            </div>
                            <div class="flex items-center space-x-4">
                                <div class="text-sm text-gray-500">
                                    <?php echo e($employeeRecords->count()); ?> record(s)
                                </div>
                                <div class="flex-shrink-0">
                                    <i class="fas fa-chevron-down text-gray-400 transition-transform duration-200" :class="{ 'rotate-180': open }"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Employee Records (Collapsible) -->
                    <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 transform scale-100" x-transition:leave-end="opacity-0 transform scale-95">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Time In</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Time Out</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Hours</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Overtime</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Schedule</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <?php $__currentLoopData = $employeeRecords; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $record): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr class="<?php echo e($index % 2 === 0 ? 'bg-white' : 'bg-gray-50'); ?>">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?php echo e(\Carbon\Carbon::parse($record['date'])->format('M d, Y')); ?>

                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?php echo e($record['time_in'] ? \Carbon\Carbon::parse($record['time_in'])->format('H:i') : '-'); ?>

                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?php echo e($record['time_out'] ? \Carbon\Carbon::parse($record['time_out'])->format('H:i') : '-'); ?>

                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?php if($record['total_hours'] == 0 && in_array($record['status'], ['absent', 'day_off', 'error'])): ?>
                                                -
                                            <?php else: ?>
                                                <?php echo e($record['total_hours']); ?>h
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?php if($record['overtime_hours'] == 0 && in_array($record['status'], ['absent', 'day_off', 'error'])): ?>
                                                -
                                            <?php else: ?>
                                                <?php echo e($record['overtime_hours'] ?? 0); ?>h
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <?php if(isset($record['schedule_status']) && $record['schedule_status']): ?>
                                                <?php
                                                    $scheduleColors = [
                                                        'Working' => 'bg-blue-100 text-blue-800',
                                                        'Day Off' => 'bg-gray-100 text-gray-800',
                                                        'Leave' => 'bg-yellow-100 text-yellow-800',
                                                        'Holiday' => 'bg-purple-100 text-purple-800',
                                                        'Overtime' => 'bg-orange-100 text-orange-800'
                                                    ];
                                                ?>
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo e($scheduleColors[$record['schedule_status']] ?? 'bg-gray-100 text-gray-800'); ?>">
                                                    <?php echo e($record['schedule_status']); ?>

                                                </span>
                                            <?php else: ?>
                                                <span class="text-sm text-gray-500">No Schedule</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <?php
                                                $statusColors = [
                                                    'present' => 'bg-green-100 text-green-800',
                                                    'late' => 'bg-yellow-100 text-yellow-800',
                                                    'absent' => 'bg-red-100 text-red-800',
                                                    'half_day' => 'bg-orange-100 text-orange-800',
                                                    'day_off' => 'bg-gray-100 text-gray-800',
                                                    'leave' => 'bg-yellow-100 text-yellow-800',
                                                    'holiday' => 'bg-purple-100 text-purple-800',
                                                    'overtime' => 'bg-orange-100 text-orange-800',
                                                    'error' => 'bg-red-200 text-red-900 border border-red-300'
                                                ];
                                            ?>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo e($statusColors[$record['status']] ?? 'bg-gray-100 text-gray-800'); ?>">
                                                <?php if($record['status'] === 'error'): ?>
                                                    <i class="fas fa-exclamation-triangle mr-1"></i>
                                                <?php endif; ?>
                                                <?php echo e(ucfirst(str_replace('_', ' ', $record['status']))); ?>

                                            </span>
                                        </td>
                                    </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="flex justify-between items-center pt-6 border-t border-gray-200">
        <div class="flex space-x-3">
            <a href="<?php echo e(route('attendance.import-dtr')); ?>" class="px-4 py-2 border border-gray-300 rounded-lg font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                Cancel
            </a>
            <button onclick="downloadPreview()" class="px-4 py-2 border border-gray-300 rounded-lg font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                <i class="fas fa-download mr-2"></i>
                Download Preview
            </button>
        </div>
        
        <div class="flex space-x-3">
            <?php if($validation['is_valid']): ?>
                <form method="POST" action="<?php echo e(route('attendance.import-dtr.confirm')); ?>" class="inline">
                    <?php echo csrf_field(); ?>
                    <button type="submit" class="px-4 py-2 bg-green-600 border border-transparent rounded-lg font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors">
                        <i class="fas fa-save mr-2"></i>
                        Save to Temporary Storage
                    </button>
                </form>
            <?php else: ?>
                <button disabled class="px-4 py-2 bg-gray-400 border border-transparent rounded-lg font-medium text-white cursor-not-allowed">
                    <i class="fas fa-times mr-2"></i>
                    Cannot Save (Has Errors)
                </button>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function downloadPreview() {
    // Create a simple CSV download of the preview data
    const data = <?php echo json_encode($parsedData, 15, 512) ?>;
    const csvContent = [
        ['Employee ID', 'Name', 'Date', 'Time In', 'Time Out', 'Total Hours', 'Status'],
        ...data.map(record => [
            record.employee_id,
            record.employee_name || 'Unknown Employee',
            record.date,
            record.time_in || '',
            record.time_out || '',
            record.total_hours,
            record.status
        ])
    ].map(row => row.join(',')).join('\n');
    
    const blob = new Blob([csvContent], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'dtr_preview_<?php echo e(date("Y-m-d_H-i-s")); ?>.csv';
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    window.URL.revokeObjectURL(url);
}

// Filter functionality
function filterByStatus(status) {
    const employeeGroups = document.querySelectorAll('.border.border-gray-200.rounded-lg');
    let visibleCount = 0;
    let totalCount = 0;
    
    employeeGroups.forEach(group => {
        const rows = group.querySelectorAll('tbody tr');
        let hasVisibleRows = false;
        
        rows.forEach(row => {
            const statusCell = row.querySelector('td:last-child span');
            const statusText = statusCell ? statusCell.textContent.toLowerCase().trim() : '';
            
            totalCount++;
            
            // Convert formatted status back to original format for comparison
            let normalizedStatus = statusText.replace(/\s+/g, '_');
            
            if (status === 'all' || normalizedStatus === status.toLowerCase() || statusText.includes(status.toLowerCase())) {
                row.style.display = '';
                hasVisibleRows = true;
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });
        
        // Show/hide entire employee group based on visible rows
        if (hasVisibleRows || status === 'all') {
            group.style.display = '';
        } else {
            group.style.display = 'none';
        }
    });
    
    // Update filter status text
    const filterStatus = document.getElementById('filterStatus');
    if (status === 'all') {
        filterStatus.textContent = `Showing all ${totalCount} records`;
    } else {
        filterStatus.textContent = `Showing ${visibleCount} ${status} records`;
    }
    
    // Update button states
    document.querySelectorAll('.filter-btn').forEach(btn => {
        btn.classList.remove('ring-2', 'ring-blue-500', 'bg-blue-100');
        if (btn.dataset.status === status) {
            btn.classList.add('ring-2', 'ring-blue-500', 'bg-blue-100');
        }
    });
}
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.dashboard-base', ['user' => $user, 'activeRoute' => 'attendance.import-dtr'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\internship\Aeternitas-Desktop app\backend\resources\views/attendance/import-dtr-review.blade.php ENDPATH**/ ?>
<?php $__env->startSection('title', 'Daily Attendance'); ?>

<?php $__env->startSection('content'); ?>
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Attendance Records</h1>
            <p class="mt-1 text-sm text-gray-600">View daily attendance records</p>
        </div>
        <div class="mt-4 sm:mt-0">
            <!-- Export Dropdown -->
            <div class="relative" x-data="{ open: false }">
                <button @click="open = !open" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-lg font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                    <i class="fas fa-download mr-2"></i>
                    Export Report
                    <i class="fas fa-chevron-down ml-2 text-xs"></i>
                </button>
                <div x-show="open" @click.away="open = false" x-transition class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 z-10">
                    <div class="py-1">
                        <a href="<?php echo e(route('attendance.daily.export', ['format' => 'pdf']) . '?date=' . $date->format('Y-m-d')); ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                            <i class="fas fa-file-pdf mr-2 text-red-500"></i>Export as PDF
                        </a>
                        <a href="<?php echo e(route('attendance.daily.export', ['format' => 'csv']) . '?date=' . $date->format('Y-m-d')); ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                            <i class="fas fa-file-csv mr-2 text-green-500"></i>Export as CSV
                        </a>
                        <a href="<?php echo e(route('attendance.daily.export', ['format' => 'xls']) . '?date=' . $date->format('Y-m-d')); ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                            <i class="fas fa-file-excel mr-2 text-green-600"></i>Export as Excel
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Date Filter -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 sm:p-6">
        <form method="GET" action="<?php echo e(route('attendance.daily')); ?>" class="flex flex-col sm:flex-row space-y-3 sm:space-y-0 sm:space-x-4">
            <div class="flex-1">
                <label for="date" class="block text-sm font-medium text-gray-700 mb-2">Select Date</label>
                <input type="date" name="date" id="date" value="<?php echo e($date->format('Y-m-d')); ?>" 
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors bg-white text-gray-900" style="background-color: white !important; color: #111827 !important;">
            </div>
            <div class="flex items-end">
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    <i class="fas fa-search mr-2"></i>Load Data
                </button>
            </div>
        </form>
    </div>

    <!-- Attendance Summary -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 sm:p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-check-circle text-green-600"></i>
                    </div>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-gray-500">Present</p>
                    <p class="text-lg font-semibold text-gray-900"><?php echo e($summary['present']); ?></p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 sm:p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-red-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-times-circle text-red-600"></i>
                    </div>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-gray-500">Absent</p>
                    <p class="text-lg font-semibold text-gray-900"><?php echo e($summary['absent']); ?></p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 sm:p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-yellow-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-clock text-yellow-600"></i>
                    </div>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-gray-500">Late</p>
                    <p class="text-lg font-semibold text-gray-900"><?php echo e($summary['late']); ?></p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 sm:p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-percentage text-blue-600"></i>
                    </div>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-gray-500">Attendance Rate</p>
                    <p class="text-lg font-semibold text-gray-900"><?php echo e($summary['attendance_rate']); ?>%</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Attendance Records -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-4 sm:px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Attendance Records</h3>
            <p class="mt-1 text-sm text-gray-600">Daily attendance for <?php echo e($date->format('F d, Y')); ?></p>
        </div>

        <!-- Desktop Table -->
        <div class="hidden lg:block overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Employee
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Department
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Time In
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Time Out
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Total Hours
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Status
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php $__empty_1 = true; $__currentLoopData = $employees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $employee): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php
                            $attendance = $attendanceRecords->get($employee->id);
                            $initials = strtoupper(substr($employee->first_name, 0, 1) . substr($employee->last_name, 0, 1));
                        ?>
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        <div class="h-10 w-10 rounded-full bg-gradient-to-r from-blue-500 to-blue-600 flex items-center justify-center">
                                            <span class="text-sm font-medium text-white"><?php echo e($initials); ?></span>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900"><?php echo e($employee->full_name); ?></div>
                                        <div class="text-sm text-gray-500"><?php echo e($employee->employee_id); ?></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900"><?php echo e($employee->department->name ?? 'N/A'); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">
                                    <?php if($attendance && $attendance->time_in): ?>
                                        <?php echo e(\Carbon\Carbon::parse($attendance->time_in)->format('g:i A')); ?>

                                    <?php elseif($attendance && !$attendance->time_in): ?>
                                        <span class="text-gray-400">-</span>
                                    <?php else: ?>
                                        <span class="text-gray-400">-</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">
                                    <?php if($attendance && $attendance->time_out): ?>
                                        <?php echo e(\Carbon\Carbon::parse($attendance->time_out)->format('g:i A')); ?>

                                    <?php elseif($attendance && $attendance->time_in && !$attendance->time_out): ?>
                                        <?php
                                            $recordDate = \Carbon\Carbon::parse($attendance->date);
                                            $isToday = $recordDate->isToday();
                                        ?>
                                        <?php if($isToday): ?>
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                <div class="w-1.5 h-1.5 rounded-full mr-1.5 bg-blue-400 animate-pulse"></div>
                                                Working
                                            </span>
                                        <?php else: ?>
                                            <span class="text-gray-400">Not Clocked Out</span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="text-gray-400">-</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">
                                    <?php if($attendance && $attendance->time_in && $attendance->time_out): ?>
                                        <?php
                                            // Load breaks relationship if not already loaded
                                            if (!$attendance->relationLoaded('breaks')) {
                                                $attendance->load('breaks');
                                            }
                                            // Always calculate total hours to ensure accuracy
                                            $calculatedHours = $attendance->calculateTotalHours();
                                            // Always use calculated hours for display (more accurate)
                                            $displayHours = $calculatedHours > 0 ? $calculatedHours : 0;
                                        ?>
                                        <?php if($displayHours > 0): ?>
                                            <?php echo e(\App\Helpers\TimezoneHelper::formatHours($displayHours)); ?>

                                        <?php else: ?>
                                            <span class="text-gray-400">0h</span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="text-gray-400">-</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php if($attendance): ?>
                                    <?php
                                        $statusColors = [
                                            'present' => 'bg-green-100 text-green-800',
                                            'absent' => 'bg-red-100 text-red-800',
                                            'absent_excused' => 'bg-yellow-100 text-yellow-800',
                                            'absent_unexcused' => 'bg-red-100 text-red-800',
                                            'absent_sick' => 'bg-orange-100 text-orange-800',
                                            'absent_personal' => 'bg-purple-100 text-purple-800',
                                            'late' => 'bg-yellow-100 text-yellow-800',
                                            'half_day' => 'bg-blue-100 text-blue-800',
                                            'on_leave' => 'bg-indigo-100 text-indigo-800',
                                        ];
                                        $statusColor = $statusColors[$attendance->status] ?? 'bg-gray-100 text-gray-800';
                                        $statusText = ucfirst(str_replace('_', ' ', $attendance->status));
                                    ?>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo e($statusColor); ?>">
                                        <div class="w-1.5 h-1.5 rounded-full mr-1.5 <?php echo e(str_replace('text-', 'bg-', $statusColor)); ?>"></div>
                                        <?php echo e($statusText); ?>

                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                        <div class="w-1.5 h-1.5 rounded-full mr-1.5 bg-gray-400"></div>
                                        No Record
                                    </span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                                No employees found
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="px-4 sm:px-6 py-4 border-t border-gray-200">
            <div class="flex items-center justify-between">
                <div class="flex-1 flex justify-between sm:hidden">
                    <?php echo e($employees->appends(request()->query())->links('pagination::simple-tailwind')); ?>

                </div>
                <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm text-gray-700">
                            Showing
                            <span class="font-medium"><?php echo e($employees->firstItem()); ?></span>
                            to
                            <span class="font-medium"><?php echo e($employees->lastItem()); ?></span>
                            of
                            <span class="font-medium"><?php echo e($employees->total()); ?></span>
                            results
                        </p>
                    </div>
                    <div>
                        <?php echo e($employees->appends(request()->query())->links('pagination::tailwind')); ?>

                    </div>
                </div>
            </div>
        </div>

        <!-- Mobile Cards -->
        <div class="lg:hidden">
            <div class="p-4 space-y-4">
                <?php $__empty_1 = true; $__currentLoopData = $employees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $employee): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <?php
                        $attendance = $attendanceRecords->get($employee->id);
                        $initials = strtoupper(substr($employee->first_name, 0, 1) . substr($employee->last_name, 0, 1));
                    ?>
                    <div class="border border-gray-200 rounded-lg p-4">
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center space-x-3">
                                <div class="h-10 w-10 rounded-full bg-gradient-to-r from-blue-500 to-blue-600 flex items-center justify-center">
                                    <span class="text-sm font-medium text-white"><?php echo e($initials); ?></span>
                                </div>
                                <div>
                                    <div class="font-medium text-gray-900"><?php echo e($employee->full_name); ?></div>
                                    <div class="text-sm text-gray-500"><?php echo e($employee->department->name ?? 'N/A'); ?></div>
                                </div>
                            </div>
                            <?php if($attendance): ?>
                                <?php
                                    $statusColors = [
                                        'present' => 'bg-green-100 text-green-800',
                                        'absent' => 'bg-red-100 text-red-800',
                                        'late' => 'bg-yellow-100 text-yellow-800',
                                        'half_day' => 'bg-blue-100 text-blue-800'
                                    ];
                                    $statusColor = $statusColors[$attendance->status] ?? 'bg-gray-100 text-gray-800';
                                ?>
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium <?php echo e($statusColor); ?>">
                                    <div class="w-1.5 h-1.5 rounded-full mr-1 <?php echo e(str_replace('text-', 'bg-', $statusColor)); ?>"></div>
                                    <?php echo e(ucfirst(str_replace('_', ' ', $attendance->status))); ?>

                                </span>
                            <?php else: ?>
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                    <div class="w-1.5 h-1.5 rounded-full mr-1 bg-gray-400"></div>
                                    No Record
                                </span>
                            <?php endif; ?>
                        </div>
                        <div class="grid grid-cols-3 gap-4 text-sm">
                            <div>
                                <div class="text-gray-500">Time In</div>
                                <div class="font-medium">
                                    <?php if($attendance && $attendance->time_in): ?>
                                        <?php echo e(\Carbon\Carbon::parse($attendance->time_in)->format('g:i A')); ?>

                                    <?php else: ?>
                                        <span class="text-gray-400">-</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div>
                                <div class="text-gray-500">Time Out</div>
                                <div class="font-medium">
                                    <?php if($attendance && $attendance->time_out): ?>
                                        <?php echo e(\Carbon\Carbon::parse($attendance->time_out)->format('g:i A')); ?>

                                    <?php else: ?>
                                        <span class="text-gray-400">-</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div>
                                <div class="text-gray-500">Total Hours</div>
                                <div class="font-medium">
                                    <?php if($attendance && $attendance->time_in && $attendance->time_out): ?>
                                        <?php
                                            // Load breaks relationship if not already loaded
                                            if (!$attendance->relationLoaded('breaks')) {
                                                $attendance->load('breaks');
                                            }
                                            // Always calculate total hours to ensure accuracy
                                            $calculatedHours = $attendance->calculateTotalHours();
                                            // Always use calculated hours for display (more accurate)
                                            $displayHours = $calculatedHours > 0 ? $calculatedHours : 0;
                                        ?>
                                        <?php if($displayHours > 0): ?>
                                            <?php echo e(\App\Helpers\TimezoneHelper::formatHours($displayHours)); ?>

                                        <?php else: ?>
                                            <span class="text-gray-400">0h</span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="text-gray-400">-</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="text-center text-gray-500 py-8">
                        No employees found
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Mobile Pagination -->
            <div class="px-4 py-4 border-t border-gray-200">
                <div class="flex items-center justify-between">
                    <div class="flex-1 flex justify-between">
                        <?php echo e($employees->appends(request()->query())->links('pagination::simple-tailwind')); ?>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Flatpickr CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

<!-- Flatpickr JS -->
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<style>
    /* Flatpickr Calendar Styling */
    .flatpickr-calendar {
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 0.5rem;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        font-family: inherit;
    }
    
    .flatpickr-months {
        background: #ffffff;
        border-radius: 0.5rem 0.5rem 0 0;
        padding: 0.5rem;
    }
    
    .flatpickr-month {
        color: #111827;
    }
    
    .flatpickr-current-month {
        color: #111827;
        font-weight: 600;
    }
    
    .flatpickr-weekdays {
        background: #f9fafb;
        border-bottom: 1px solid #e5e7eb;
    }
    
    .flatpickr-weekday {
        color: #6b7280;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.75rem;
    }
    
    .flatpickr-day {
        color: #111827;
        border-radius: 0.375rem;
    }
    
    .flatpickr-day:hover {
        background: #f3f4f6;
        border-color: #d1d5db;
    }
    
    .flatpickr-day.selected,
    .flatpickr-day.startRange,
    .flatpickr-day.endRange {
        background: #2563eb;
        border-color: #2563eb;
        color: #ffffff;
    }
    
    .flatpickr-day.selected:hover,
    .flatpickr-day.startRange:hover,
    .flatpickr-day.endRange:hover {
        background: #1d4ed8;
        border-color: #1d4ed8;
    }
    
    .flatpickr-day.flatpickr-disabled,
    .flatpickr-day.prevMonthDay,
    .flatpickr-day.nextMonthDay {
        color: #d1d5db;
    }
    
    .flatpickr-day.today {
        border-color: #2563eb;
        font-weight: 600;
    }
    
    .flatpickr-prev-month,
    .flatpickr-next-month {
        color: #6b7280;
    }
    
    .flatpickr-prev-month:hover,
    .flatpickr-next-month:hover {
        color: #2563eb;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Flatpickr for date input
    const datePicker = flatpickr("#date", {
        dateFormat: "Y-m-d",
        defaultDate: "<?php echo e($date->format('Y-m-d')); ?>",
        onReady: function(selectedDates, dateStr, instance) {
            // Ensure text is visible
            instance.calendarContainer.style.color = '#111827';
        }
    });
    
    function loadAttendanceData() {
        const date = document.getElementById('date').value;
        // This will be implemented when backend is ready
        console.log('Loading attendance data for:', date);
    }
});
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.dashboard-base', ['user' => $user, 'activeRoute' => 'attendance.daily'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\internship\Aeternitas-Desktop app\backend\resources\views/attendance/daily.blade.php ENDPATH**/ ?>
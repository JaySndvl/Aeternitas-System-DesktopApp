<?php $__env->startSection('title', 'Period Details - ' . $period->name); ?>

<?php
    function formatHoursToReadable($decimalHours) {
        if ($decimalHours <= 0) {
            return '0 hrs';
        }
        
        $hours = floor($decimalHours);
        $minutes = round(($decimalHours - $hours) * 60);
        
        // Handle minute rounding that might exceed 59
        if ($minutes >= 60) {
            $hours += 1;
            $minutes = 0;
        }
        
        $result = '';
        
        if ($hours > 0) {
            $result .= $hours . ' hr' . ($hours > 1 ? 's' : '');
        }
        
        if ($minutes > 0) {
            if ($hours > 0) {
                $result .= ' ';
            }
            $result .= $minutes . ' min' . ($minutes > 1 ? 's' : '');
        }
        
        return $result ?: '0 hrs';
    }
?>

<?php $__env->startSection('content'); ?>
<div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white shadow-sm border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="py-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900"><?php echo e($period->name); ?></h1>
                        <p class="mt-1 text-sm text-gray-600">
                            <?php echo e($period->start_date->format('M j, Y')); ?> - 
                            <?php echo e($period->end_date->format('M j, Y')); ?>

                        </p>
                        <?php if($period->description): ?>
                            <p class="mt-1 text-sm text-gray-500"><?php echo e($period->description); ?></p>
                        <?php endif; ?>
                        <?php if(!empty($period->department_id) || !empty($period->employee_ids)): ?>
                            <div class="mt-2">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                    <i class="fas fa-filter mr-1"></i>
                                    <?php if(!empty($period->employee_ids) && count($period->employee_ids) > 0): ?>
                                        <?php echo e(count($period->employee_ids)); ?> Employee(s) Analysis
                                    <?php elseif(!empty($period->department_id)): ?>
                                        Department Filtered Analysis
                                    <?php endif; ?>
                                </span>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="flex space-x-3">
                        <?php if($user->role !== 'employee'): ?>
                        <a href="<?php echo e(route('attendance.period-management.preview-payroll', $period->id)); ?>?refresh=<?php echo e(time()); ?>" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-lg font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                            <i class="fas fa-eye mr-2"></i>
                            Preview Payroll
                        </a>
                        <a href="<?php echo e(route('attendance.period-management.payroll-summary', $period->id)); ?>" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-lg font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                            <i class="fas fa-chart-bar mr-2"></i>
                            Payroll Summary
                        </a>
                        <a href="<?php echo e(route('attendance.period-management.export-payroll', $period->id)); ?>" class="inline-flex items-center px-4 py-2 bg-purple-600 border border-transparent rounded-lg font-medium text-white hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 transition-colors">
                            <i class="fas fa-download mr-2"></i>
                            Export Payroll
                        </a>
                        <?php endif; ?>
                        <a href="<?php echo e(route('attendance.period-management.index')); ?>" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                            <i class="fas fa-arrow-left mr-2"></i>
                            Back to Periods
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
            <!-- Total Employees -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                <div class="flex items-center">
                    <div class="h-8 w-8 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                        <i class="fas fa-users text-blue-600 text-sm"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-semibold text-gray-900"><?php echo e($summaryData['total_employees']); ?></h3>
                        <p class="text-xs text-gray-600">Employees</p>
                    </div>
                </div>
            </div>

            <!-- Present Days -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                <div class="flex items-center">
                    <div class="h-8 w-8 bg-green-100 rounded-lg flex items-center justify-center mr-3">
                        <i class="fas fa-check-circle text-green-600 text-sm"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-semibold text-gray-900"><?php echo e($summaryData['present_days']); ?></h3>
                        <p class="text-xs text-gray-600">Present</p>
                    </div>
                </div>
            </div>

            <!-- Absent Days -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                <div class="flex items-center">
                    <div class="h-8 w-8 bg-red-100 rounded-lg flex items-center justify-center mr-3">
                        <i class="fas fa-user-times text-red-600 text-sm"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-semibold text-gray-900"><?php echo e($summaryData['absent_days']); ?></h3>
                        <p class="text-xs text-gray-600">Absent</p>
                    </div>
                </div>
            </div>

            <!-- Scheduled Hours -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                <div class="flex items-center">
                    <div class="h-8 w-8 bg-purple-100 rounded-lg flex items-center justify-center mr-3">
                        <i class="fas fa-clock text-purple-600 text-sm"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900"><?php echo e(formatHoursToReadable($summaryData['total_scheduled_hours'])); ?></h3>
                        <p class="text-xs text-gray-600">Scheduled</p>
                    </div>
                </div>
            </div>

            <!-- Total Overtime -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                <div class="flex items-center">
                    <div class="h-8 w-8 bg-orange-100 rounded-lg flex items-center justify-center mr-3">
                        <i class="fas fa-plus text-orange-600 text-sm"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900"><?php echo e(formatHoursToReadable($summaryData['total_morning_overtime_hours'] + $summaryData['total_evening_overtime_hours'])); ?></h3>
                        <p class="text-xs text-gray-600">Overtime</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Comprehensive Attendance Table -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-gray-900">Attendance Records</h2>
                    <div class="flex space-x-3">
                        <button onclick="expandAll()" class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                            <i class="fas fa-expand-alt mr-2"></i>
                            Expand All
                        </button>
                        <button onclick="collapseAll()" class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                            <i class="fas fa-compress-alt mr-2"></i>
                            Collapse All
                        </button>
                        <button onclick="exportToCSV()" class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                            <i class="fas fa-download mr-2"></i>
                            Export CSV
                        </button>
                        <button onclick="exportToExcel()" class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                            <i class="fas fa-file-excel mr-2"></i>
                            Export Excel
                        </button>
                    </div>
                </div>
            </div>

            <?php
                // Group data by employee
                $groupedData = collect($comprehensiveData)->groupBy('employee_id');
            ?>

            <div class="space-y-4 p-6">
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
                                    <h4 class="text-sm font-medium text-gray-900">
                                        <?php echo e($employeeRecords->first()['employee_code']); ?> - <?php echo e($employeeRecords->first()['employee_name']); ?>

                                    </h4>
                                    <p class="text-sm text-gray-500"><?php echo e($employeeRecords->count()); ?> record(s)</p>
                                </div>
                            </div>
                            <div class="flex items-center space-x-4">
                                <div class="text-sm text-gray-500">
                                    <?php
                                        $presentCount = $employeeRecords->where('attendance_status', 'Present')->count();
                                        $absentCount = $employeeRecords->where('attendance_status', 'Absent')->count();
                                    ?>
                                    <span class="text-green-600 font-medium"><?php echo e($presentCount); ?>P</span>
                                    <span class="text-red-600 font-medium"><?php echo e($absentCount); ?>A</span>
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
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Schedule (In–Out)</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Working Hours</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actual (In–Out)</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Worked Hours</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Scheduled Hours</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pre-Shift OT</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Post-Shift OT</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Late Arrival</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Night Shift</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <?php $__currentLoopData = $employeeRecords; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $record): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr class="<?php echo e($index % 2 === 0 ? 'bg-white' : 'bg-gray-50'); ?> hover:bg-blue-50 cursor-pointer" onclick="showEmployeeDetails('<?php echo e($record['employee_id']); ?>', '<?php echo e($record['date']); ?>')">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <?php echo e($record['date_formatted']); ?>

                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <?php if($record['schedule_status'] === 'Regular Holiday'): ?>
                                                <span class="text-yellow-600 font-semibold"><?php echo e($record['schedule_in_out']); ?></span>
                                            <?php elseif($record['schedule_status'] === 'Special Holiday'): ?>
                                                <span class="text-pink-600 font-semibold"><?php echo e($record['schedule_in_out']); ?></span>
                                            <?php elseif($record['schedule_status'] === 'Day Off'): ?>
                                                <span class="text-slate-600 font-medium"><?php echo e($record['schedule_in_out']); ?></span>
                                            <?php elseif($record['schedule_status'] === 'Leave'): ?>
                                                <span class="text-purple-600 font-medium"><?php echo e($record['schedule_in_out']); ?></span>
                                            <?php elseif($record['schedule_status'] === 'Holiday'): ?>
                                                <span class="text-red-600 font-medium"><?php echo e($record['schedule_in_out']); ?></span>
                                            <?php else: ?>
                                                <?php echo e($record['schedule_in_out']); ?>

                                            <?php endif; ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <?php if($record['schedule_status'] === 'Regular Holiday'): ?>
                                                <span class="text-yellow-600 font-semibold"><?php echo e($record['working_hours']); ?></span>
                                            <?php elseif($record['schedule_status'] === 'Special Holiday'): ?>
                                                <span class="text-pink-600 font-semibold"><?php echo e($record['working_hours']); ?></span>
                                            <?php elseif($record['schedule_status'] === 'Day Off'): ?>
                                                <span class="text-slate-600 font-medium"><?php echo e($record['working_hours']); ?></span>
                                            <?php elseif($record['schedule_status'] === 'Leave'): ?>
                                                <span class="text-purple-600 font-medium"><?php echo e($record['working_hours']); ?></span>
                                            <?php elseif($record['schedule_status'] === 'Holiday'): ?>
                                                <span class="text-red-600 font-medium"><?php echo e($record['working_hours']); ?></span>
                                            <?php else: ?>
                                                <?php echo e($record['working_hours']); ?>

                                            <?php endif; ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <?php echo e($record['actual_in_out']); ?>

                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <?php if($record['worked_hours'] === '—'): ?>
                                                <span class="text-gray-400"><?php echo e($record['worked_hours']); ?></span>
                                            <?php else: ?>
                                                <?php echo e($record['worked_hours']); ?>

                                            <?php endif; ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <?php if($record['scheduled_hours'] === '—'): ?>
                                                <span class="text-gray-400"><?php echo e($record['scheduled_hours']); ?></span>
                                            <?php else: ?>
                                                <?php echo e($record['scheduled_hours']); ?>

                                            <?php endif; ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <?php if($record['morning_overtime'] > 0): ?>
                                                <?php echo e(formatHoursToReadable($record['morning_overtime'])); ?>

                                            <?php else: ?>
                                                <span class="text-gray-400">0 hrs</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <?php if($record['evening_overtime'] > 0): ?>
                                                <?php echo e(formatHoursToReadable($record['evening_overtime'])); ?>

                                            <?php else: ?>
                                                <span class="text-gray-400">0 hrs</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <?php if($record['late_minutes'] > 0): ?>
                                                <span class="text-red-600 font-medium"><?php echo e($record['late_minutes']); ?> min</span>
                                            <?php else: ?>
                                                <span class="text-gray-400">0</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <?php if($record['is_night_shift'] && $record['night_differential_hours'] > 0): ?>
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                                                     <?php echo e(formatHoursToReadable($record['night_differential_hours'])); ?>

                                                </span>
                                            <?php else: ?>
                                                <span class="text-gray-400">—</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                <?php if($record['attendance_status'] === 'Present'): ?> bg-green-100 text-green-800
                                                <?php elseif($record['attendance_status'] === 'Absent'): ?> bg-red-100 text-red-800
                                                <?php elseif($record['attendance_status'] === 'Error'): ?> bg-yellow-100 text-yellow-800
                                                <?php elseif($record['attendance_status'] === 'Day Off'): ?> bg-gray-100 text-gray-800
                                                <?php elseif($record['attendance_status'] === 'No Schedule'): ?> bg-gray-100 text-gray-500
                                                <?php else: ?> bg-gray-100 text-gray-800
                                                <?php endif; ?>">
                                                <?php if($record['attendance_status'] === 'Present'): ?>
                                                    <?php if($record['schedule_status'] === 'Regular Holiday'): ?>
                                                        🟢 <span class="text-yellow-600 font-semibold"><?php echo e($record['combined_status']); ?></span>
                                                    <?php elseif($record['schedule_status'] === 'Special Holiday'): ?>
                                                        🟢 <span class="text-pink-600 font-semibold"><?php echo e($record['combined_status']); ?></span>
                                                    <?php else: ?>
                                                        🟢 <?php echo e($record['combined_status']); ?>

                                                    <?php endif; ?>
                                                <?php elseif($record['attendance_status'] === 'Absent'): ?>
                                                    <?php if($record['schedule_status'] === 'Regular Holiday'): ?>
                                                        🔴 <span class="text-yellow-600 font-semibold"><?php echo e($record['combined_status']); ?></span>
                                                    <?php elseif($record['schedule_status'] === 'Special Holiday'): ?>
                                                        🔴 <span class="text-pink-600 font-semibold"><?php echo e($record['combined_status']); ?></span>
                                                    <?php else: ?>
                                                        🔴 <?php echo e($record['combined_status']); ?>

                                                    <?php endif; ?>
                                                <?php elseif($record['attendance_status'] === 'Error'): ?>
                                                    <?php if($record['schedule_status'] === 'Regular Holiday'): ?>
                                                        🟡 <span class="text-yellow-600 font-semibold"><?php echo e($record['combined_status']); ?></span>
                                                    <?php elseif($record['schedule_status'] === 'Special Holiday'): ?>
                                                        🟡 <span class="text-pink-600 font-semibold"><?php echo e($record['combined_status']); ?></span>
                                                    <?php else: ?>
                                                        🟡<?php echo e($record['combined_status']); ?>

                                                    <?php endif; ?>
                                                <?php elseif($record['attendance_status'] === 'Day Off'): ?>
                                                    ⚪ <?php echo e($record['combined_status']); ?>

                                                <?php elseif($record['attendance_status'] === 'No Schedule'): ?>
                                                    <span class="text-gray-500"><?php echo e($record['combined_status']); ?></span>
                                                <?php else: ?>
                                                    ⚪ <?php echo e($record['combined_status']); ?>

                                                <?php endif; ?>
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

            <?php if(empty($comprehensiveData)): ?>
                <div class="text-center py-12">
                    <div class="mx-auto h-16 w-16 text-gray-400">
                        <i class="fas fa-calendar-times text-4xl"></i>
                                </div>
                    <h3 class="mt-4 text-lg font-medium text-gray-900">No attendance records found</h3>
                    <p class="mt-2 text-sm text-gray-600">No attendance data available for the selected period.</p>
                                </div>
            <?php endif; ?>
        </div>

        <!-- Payroll Preview Section -->
        <?php
            $startDate = \Carbon\Carbon::parse($period['start_date']);
            $endDate = \Carbon\Carbon::parse($period['end_date']);
            $existingPayrolls = \App\Models\Payroll::where('pay_period_start', $startDate->format('Y-m-d'))
                ->where('pay_period_end', $endDate->format('Y-m-d'))
                ->with('employee.department')
                ->get();
        ?>

        <?php if($existingPayrolls->count() > 0): ?>
        <div class="mt-6 bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900">Payroll Preview</h3>
                    <div class="flex space-x-3">
                        <?php if($user->role !== 'employee'): ?>
                        <a href="<?php echo e(route('attendance.period-management.payroll-summary', $period['id'])); ?>" class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                            <i class="fas fa-eye mr-2"></i>
                            View Details
                        </a>
                        <a href="<?php echo e(route('attendance.period-management.export-payroll', $period['id'])); ?>" class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                            <i class="fas fa-download mr-2"></i>
                            Export CSV
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                    <div class="bg-green-50 rounded-lg p-4">
                        <div class="flex items-center">
                            <div class="h-8 w-8 bg-green-100 rounded-lg flex items-center justify-center mr-3">
                                <i class="fas fa-users text-green-600 text-sm"></i>
                            </div>
                            <div>
                                <h4 class="text-lg font-semibold text-gray-900"><?php echo e($existingPayrolls->count()); ?></h4>
                                <p class="text-xs text-gray-600">Employees</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-blue-50 rounded-lg p-4">
                        <div class="flex items-center">
                            <div class="h-8 w-8 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                                <i class="fas fa-money-bill-wave text-blue-600 text-sm"></i>
                            </div>
                            <div>
                                <h4 class="text-lg font-semibold text-gray-900">₱<?php echo e(number_format($existingPayrolls->sum('gross_pay'), 2)); ?></h4>
                                <p class="text-xs text-gray-600">Gross Pay</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-orange-50 rounded-lg p-4">
                        <div class="flex items-center">
                            <div class="h-8 w-8 bg-orange-100 rounded-lg flex items-center justify-center mr-3">
                                <i class="fas fa-minus-circle text-orange-600 text-sm"></i>
                            </div>
                            <div>
                                <h4 class="text-lg font-semibold text-gray-900">₱<?php echo e(number_format($existingPayrolls->sum('deductions'), 2)); ?></h4>
                                <p class="text-xs text-gray-600">Deductions</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-purple-50 rounded-lg p-4">
                        <div class="flex items-center">
                            <div class="h-8 w-8 bg-purple-100 rounded-lg flex items-center justify-center mr-3">
                                <i class="fas fa-wallet text-purple-600 text-sm"></i>
                            </div>
                            <div>
                                <h4 class="text-lg font-semibold text-gray-900">₱<?php echo e(number_format($existingPayrolls->sum('net_pay'), 2)); ?></h4>
                                <p class="text-xs text-gray-600">Net Pay</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Payroll Table -->
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Employee</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Department</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Basic Salary</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Overtime</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Net Pay</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php $__currentLoopData = $existingPayrolls->take(5); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $payroll): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900"><?php echo e($payroll->employee->employee_id); ?></div>
                                    <div class="text-sm text-gray-500"><?php echo e($payroll->employee->full_name); ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?php echo e($payroll->employee->department->name ?? 'N/A'); ?>

                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    ₱<?php echo e(number_format($payroll->basic_salary, 2)); ?>

                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?php echo e(number_format($payroll->overtime_hours, 1)); ?> hrs
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    ₱<?php echo e(number_format($payroll->net_pay, 2)); ?>

                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        <?php if($payroll->status === 'pending'): ?> bg-yellow-100 text-yellow-800
                                        <?php elseif($payroll->status === 'processed'): ?> bg-blue-100 text-blue-800
                                        <?php elseif($payroll->status === 'paid'): ?> bg-green-100 text-green-800
                                        <?php else: ?> bg-gray-100 text-gray-800
                                        <?php endif; ?>">
                                        <?php echo e(ucfirst($payroll->status)); ?>

                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>

                <?php if($existingPayrolls->count() > 5): ?>
                <div class="mt-4 text-center">
                    <p class="text-sm text-gray-500">Showing 5 of <?php echo e($existingPayrolls->count()); ?> payroll records</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
// Export functions
function exportToCSV() {
    const data = <?php echo json_encode($comprehensiveData, 15, 512) ?>;
    const headers = ['Employee', 'Date', 'Schedule (In–Out)', 'Working Hours', 'Actual (In–Out)', 'Worked Hours', 'Scheduled Hours', 'Pre-Shift OT', 'Post-Shift OT', 'Late Arrival', 'Status'];
    
    let csvContent = headers.join(',') + '\n';
    
    data.forEach(record => {
        const row = [
            `"${record.employee_code} - ${record.employee_name}"`,
            record.date_formatted,
            record.schedule_in_out,
            record.working_hours,
            record.actual_in_out,
            record.worked_hours,
            record.scheduled_hours,
            record.morning_overtime > 0 ? `${record.morning_overtime} hrs` : '0',
            record.evening_overtime > 0 ? `${record.evening_overtime} hrs` : '0',
            record.late_minutes > 0 ? `${record.late_minutes} min` : '0',
            `"${record.combined_status}"`
        ];
        csvContent += row.join(',') + '\n';
    });
    
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    const url = URL.createObjectURL(blob);
    link.setAttribute('href', url);
    link.setAttribute('download', 'attendance_records_<?php echo e($period["name"]); ?>.csv');
    link.style.visibility = 'hidden';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

function exportToExcel() {
    // For now, we'll export as CSV with .xlsx extension
    // In a real implementation, you'd use a library like SheetJS
    exportToCSV();
}

function showEmployeeDetails(employeeId, date) {
    // This could open a modal or navigate to a detailed view
    alert(`Employee ID: ${employeeId}\nDate: ${date}\n\nDetailed view coming soon!`);
}

// Expand/Collapse all functionality
function expandAll() {
    document.querySelectorAll('[x-data]').forEach(element => {
        element._x_dataStack[0].open = true;
    });
}

function collapseAll() {
    document.querySelectorAll('[x-data]').forEach(element => {
        element._x_dataStack[0].open = false;
    });
}
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.dashboard-base', ['user' => $user, 'activeRoute' => 'attendance.period-management.index'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\caior\Documents\Mica files\Aeternitas-System-V2\resources\views/attendance/period-management/show.blade.php ENDPATH**/ ?>
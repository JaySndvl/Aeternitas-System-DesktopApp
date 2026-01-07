<?php $__env->startSection('title', 'Timekeeping'); ?>

<?php $__env->startSection('content'); ?>
<div class="space-y-6">
    <!-- Success/Error Messages -->
    <?php if(session('success')): ?>
        <div class="bg-green-50 border border-green-200 rounded-lg p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-check-circle text-green-400"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-green-800"><?php echo e(session('success')); ?></p>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php if(session('error')): ?>
        <div class="bg-red-50 border border-red-200 rounded-lg p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-exclamation-circle text-red-400"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-red-800"><?php echo e(session('error')); ?></p>
                </div>
            </div>
        </div>
    <?php endif; ?>
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Timekeeping</h1>
            <p class="mt-1 text-sm text-gray-600">
                <?php if($user->role === 'employee'): ?>
                    View your time records
                <?php else: ?>
                    Track and manage employee time records
                <?php endif; ?>
            </p>
        </div>
        <?php if(in_array($user->role, ['admin', 'hr', 'manager'])): ?>
        <div class="mt-4 sm:mt-0 flex space-x-3">
            <!-- Export Dropdown -->
            <div class="relative" x-data="{ open: false }">
                <button @click="open = !open" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                    <i class="fas fa-download mr-2"></i>
                    Export Report
                    <i class="fas fa-chevron-down ml-2 text-xs"></i>
                </button>
                <div x-show="open" @click.away="open = false" x-transition class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 z-10">
                    <div class="py-1">
                        <a href="<?php echo e(route('attendance.timekeeping.export', ['format' => 'pdf']) . '?' . http_build_query(request()->query())); ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                            <i class="fas fa-file-pdf mr-2 text-red-500"></i>Export as PDF
                        </a>
                        <a href="<?php echo e(route('attendance.timekeeping.export', ['format' => 'csv']) . '?' . http_build_query(request()->query())); ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                            <i class="fas fa-file-csv mr-2 text-green-500"></i>Export as CSV
                        </a>
                        <a href="<?php echo e(route('attendance.timekeeping.export', ['format' => 'xls']) . '?' . http_build_query(request()->query())); ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                            <i class="fas fa-file-excel mr-2 text-green-600"></i>Export as Excel
                        </a>
                    </div>
                </div>
            </div>
            <a href="<?php echo e(route('attendance.import-dtr')); ?>" class="inline-flex items-center px-4 py-2 border border-orange-300 rounded-lg font-medium text-orange-700 bg-orange-50 hover:bg-orange-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 transition-colors">
                <i class="fas fa-file-import mr-2"></i>
                Import DTR
            </a>
            <a href="<?php echo e(route('attendance.create-record')); ?>" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-lg font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                <i class="fas fa-plus mr-2"></i>
                Add Record
            </a>
        </div>
        <?php endif; ?>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 sm:p-6">
        <form method="GET" action="<?php echo e(route('attendance.timekeeping')); ?>" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <?php if(in_array($user->role, ['admin', 'hr', 'manager'])): ?>
            <div>
                <label for="employee_id" class="block text-sm font-medium text-gray-700 mb-2">Employee</label>
                <select name="employee_id" id="employee_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors bg-white text-gray-900" style="background-color: white !important; color: #111827 !important;">
                    <option value="" style="color: #111827 !important;">All Employees</option>
                    <?php $__currentLoopData = $employees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $employee): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($employee->id); ?>" <?php echo e(request('employee_id') == $employee->id ? 'selected' : ''); ?> style="color: #111827 !important;">
                            <?php echo e($employee->full_name); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div>
                <label for="department_id" class="block text-sm font-medium text-gray-700 mb-2">Department</label>
                <select name="department_id" id="department_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors bg-white text-gray-900" style="background-color: white !important; color: #111827 !important;">
                    <option value="" style="color: #111827 !important;">All Departments</option>
                    <?php $__currentLoopData = $departments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $department): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($department->id); ?>" <?php echo e(request('department_id') == $department->id ? 'selected' : ''); ?> style="color: #111827 !important;">
                            <?php echo e($department->name); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <?php endif; ?>
            <div>
                <label for="date_from" class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-calendar-alt mr-1 text-gray-500"></i>From Date
                </label>
                <div class="relative">
                    <input type="text" name="date_from" id="date_from" value="<?php echo e(request('date_from')); ?>" placeholder="Select date" class="w-full px-3 py-2 pl-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors bg-white text-gray-900 cursor-pointer" style="background-color: white !important; color: #111827 !important;">
                    <i class="fas fa-calendar absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 cursor-pointer hover:text-gray-600 transition-colors" style="pointer-events: auto;"></i>
                </div>
            </div>
            <div>
                <label for="date_to" class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-calendar-alt mr-1 text-gray-500"></i>To Date
                </label>
                <div class="relative">
                    <input type="text" name="date_to" id="date_to" value="<?php echo e(request('date_to')); ?>" placeholder="Select date" class="w-full px-3 py-2 pl-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors bg-white text-gray-900 cursor-pointer" style="background-color: white !important; color: #111827 !important;">
                    <i class="fas fa-calendar absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 cursor-pointer hover:text-gray-600 transition-colors" style="pointer-events: auto;"></i>
                </div>
            </div>
        </div>
        <div class="mt-4 flex justify-end space-x-3">
            <a href="<?php echo e(route('attendance.timekeeping')); ?>" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                <i class="fas fa-times mr-2"></i>Clear
            </a>
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                <i class="fas fa-search mr-2"></i>Apply Filters
            </button>
        </div>
        </form>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 sm:p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-clock text-blue-600"></i>
                    </div>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-gray-500">Total Hours</p>
                    <p class="text-lg font-semibold text-gray-900"><?php echo e(\App\Helpers\TimezoneHelper::formatHours($summary['total_hours'])); ?></p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 sm:p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-check-circle text-green-600"></i>
                    </div>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-gray-500">Regular Hours</p>
                    <p class="text-lg font-semibold text-gray-900"><?php echo e(\App\Helpers\TimezoneHelper::formatHours($summary['regular_hours'])); ?></p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 sm:p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-yellow-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-plus-circle text-yellow-600"></i>
                    </div>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-gray-500">Overtime Hours</p>
                    <p class="text-lg font-semibold text-gray-900"><?php echo e(\App\Helpers\TimezoneHelper::formatHours($summary['overtime_hours'])); ?></p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 sm:p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-percentage text-purple-600"></i>
                    </div>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-gray-500">Average Hours</p>
                    <p class="text-lg font-semibold text-gray-900"><?php echo e(\App\Helpers\TimezoneHelper::formatHours($summary['average_hours'])); ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Timekeeping Records -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-4 sm:px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Timekeeping Records</h3>
            <p class="mt-1 text-sm text-gray-600">
                <?php if($user->role === 'employee'): ?>
                    Your time records for the selected period
                <?php else: ?>
                    Employee time records for the selected period
                <?php endif; ?>
            </p>
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
                            Date
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Time In
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Time Out
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Break Time
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Total Hours
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Overtime
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Status
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php $__empty_1 = true; $__currentLoopData = $attendanceRecords; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $record): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php
                            $initials = strtoupper(substr($record->employee->first_name, 0, 1) . substr($record->employee->last_name, 0, 1));
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
                                        <div class="text-sm font-medium text-gray-900"><?php echo e($record->employee->full_name); ?></div>
                                        <div class="text-sm text-gray-500"><?php echo e($record->employee->department->name ?? 'N/A'); ?></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900"><?php echo e(\Carbon\Carbon::parse($record->date)->format('M d, Y')); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">
                                    <?php if($record->time_in): ?>
                                        <?php echo e(\Carbon\Carbon::parse($record->time_in)->format('g:i A')); ?>

                                    <?php else: ?>
                                        <span class="text-gray-400">-</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">
                                    <?php if($record->time_out): ?>
                                        <?php echo e(\Carbon\Carbon::parse($record->time_out)->format('g:i A')); ?>

                                    <?php elseif($record->time_in): ?>
                                        <?php
                                            $recordDate = \Carbon\Carbon::parse($record->date);
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
                                    <?php if($record->time_out): ?>
                                        <?php
                                            // Calculate total break duration from all breaks
                                            $totalBreakMinutes = 0;
                                            
                                            // Use breaks relationship if available (for multiple breaks)
                                            if ($record->relationLoaded('breaks') && $record->breaks->count() > 0) {
                                                foreach ($record->breaks as $break) {
                                                    if ($break->break_end) {
                                                        // Use stored duration if available, otherwise calculate
                                                        $totalBreakMinutes += $break->break_duration_minutes ?? $break->break_start->diffInMinutes($break->break_end);
                                                    }
                                                }
                                            } 
                                            // Fallback to old break_start/break_end fields for backward compatibility
                                            elseif ($record->break_start && $record->break_end) {
                                                $breakStart = \Carbon\Carbon::parse($record->break_start);
                                                $breakEnd = \Carbon\Carbon::parse($record->break_end);
                                                $totalBreakMinutes = $breakStart->diffInMinutes($breakEnd);
                                            }
                                            
                                            $breakDuration = $totalBreakMinutes / 60;
                                        ?>
                                        <?php if($breakDuration > 0): ?>
                                            <?php echo e(\App\Helpers\TimezoneHelper::formatHours($breakDuration)); ?>

                                        <?php else: ?>
                                            <span class="text-gray-400">-</span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="text-gray-400">-</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">
                                    <?php if($record->time_out && $record->time_in): ?>
                                        <?php
                                            // Load breaks relationship if not already loaded
                                            if (!$record->relationLoaded('breaks')) {
                                                $record->load('breaks');
                                            }
                                            // Always calculate total hours to ensure accuracy
                                            $calculatedHours = $record->calculateTotalHours();
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
                                <div class="text-sm text-gray-900">
                                    <?php if($record->time_out && $record->overtime_hours > 0): ?>
                                        <?php echo e(\App\Helpers\TimezoneHelper::formatHours($record->overtime_hours)); ?>

                                    <?php else: ?>
                                        <span class="text-gray-400">-</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php
                                    $recordDate = \Carbon\Carbon::parse($record->date);
                                    $isToday = $recordDate->isToday();
                                    
                                    // If employee is still working TODAY (time in but no time out), show "Working" status
                                    // For past dates with time in but no time out, show "Incomplete" status
                                    if ($record->time_in && !$record->time_out) {
                                        if ($isToday) {
                                            $statusColor = 'bg-blue-100 text-blue-800';
                                            $statusText = 'Working';
                                        } else {
                                            $statusColor = 'bg-yellow-100 text-yellow-800';
                                            $statusText = 'Incomplete';
                                        }
                                    } else {
                                        $statusColors = [
                                            'present' => 'bg-green-100 text-green-800',
                                            'absent' => 'bg-red-100 text-red-800',
                                            'late' => 'bg-yellow-100 text-yellow-800',
                                            'half_day' => 'bg-blue-100 text-blue-800'
                                        ];
                                        $statusColor = $statusColors[$record->status] ?? 'bg-gray-100 text-gray-800';
                                        $statusText = ucfirst(str_replace('_', ' ', $record->status));
                                    }
                                ?>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo e($statusColor); ?>">
                                    <div class="w-1.5 h-1.5 rounded-full mr-1.5 <?php echo e(str_replace('text-', 'bg-', $statusColor)); ?> <?php if($record->time_in && !$record->time_out && $isToday): ?> animate-pulse <?php endif; ?>"></div>
                                    <?php echo e($statusText); ?>

                                </span>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="8" class="px-6 py-4 text-center">
                                <div class="flex flex-col items-center justify-center py-8">
                                    <i class="fas fa-clock text-gray-400 text-4xl mb-4"></i>
                                    <p class="text-gray-500 text-lg font-medium mb-2">No attendance records found</p>
                                    <?php if($user->role === 'employee'): ?>
                                        <p class="text-gray-400 text-sm">You haven't clocked in yet. Use the Time In/Out page to record your attendance.</p>
                                        <a href="<?php echo e(route('attendance.time-in-out')); ?>" class="mt-4 inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                                            <i class="fas fa-clock mr-2"></i>
                                            Go to Time In/Out
                                        </a>
                                    <?php else: ?>
                                        <p class="text-gray-400 text-sm">Try adjusting your filters or date range.</p>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="px-4 sm:px-6 py-4 border-t border-gray-200">
            <!-- Mobile Pagination -->
            <div class="sm:hidden">
                <?php echo e($attendanceRecords->appends(request()->query())->links('pagination::default')); ?>

            </div>
            
            <!-- Desktop Pagination -->
            <div class="hidden sm:block">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-700">
                            Showing
                            <span class="font-medium"><?php echo e($attendanceRecords->firstItem()); ?></span>
                            to
                            <span class="font-medium"><?php echo e($attendanceRecords->lastItem()); ?></span>
                            of
                            <span class="font-medium"><?php echo e($attendanceRecords->total()); ?></span>
                            results
                        </p>
                    </div>
                    <div>
                        <?php if($attendanceRecords->hasPages()): ?>
                            <div class="flex items-center space-x-2">
                                <?php if($attendanceRecords->onFirstPage()): ?>
                                    <span class="px-3 py-2 text-sm text-gray-400 bg-gray-100 rounded">Previous</span>
                                <?php else: ?>
                                    <a href="<?php echo e($attendanceRecords->previousPageUrl()); ?>" class="px-3 py-2 text-sm text-blue-600 bg-white border border-gray-300 rounded hover:bg-gray-50">Previous</a>
                                <?php endif; ?>
                                
                                <?php for($i = 1; $i <= $attendanceRecords->lastPage(); $i++): ?>
                                    <?php if($i == $attendanceRecords->currentPage()): ?>
                                        <span class="px-3 py-2 text-sm text-white bg-blue-600 rounded"><?php echo e($i); ?></span>
                                    <?php else: ?>
                                        <a href="<?php echo e($attendanceRecords->url($i)); ?>" class="px-3 py-2 text-sm text-blue-600 bg-white border border-gray-300 rounded hover:bg-gray-50"><?php echo e($i); ?></a>
                                    <?php endif; ?>
                                <?php endfor; ?>
                                
                                <?php if($attendanceRecords->hasMorePages()): ?>
                                    <a href="<?php echo e($attendanceRecords->nextPageUrl()); ?>" class="px-3 py-2 text-sm text-blue-600 bg-white border border-gray-300 rounded hover:bg-gray-50">Next</a>
                                <?php else: ?>
                                    <span class="px-3 py-2 text-sm text-gray-400 bg-gray-100 rounded">Next</span>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mobile Cards -->
        <div class="lg:hidden">
            <div class="p-4 space-y-4">
                <?php $__empty_1 = true; $__currentLoopData = $attendanceRecords; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $record): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <?php
                        $initials = strtoupper(substr($record->employee->first_name, 0, 1) . substr($record->employee->last_name, 0, 1));
                    ?>
                    <div class="border border-gray-200 rounded-lg p-4">
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center space-x-3">
                                <div class="h-10 w-10 rounded-full bg-gradient-to-r from-blue-500 to-blue-600 flex items-center justify-center">
                                    <span class="text-sm font-medium text-white"><?php echo e($initials); ?></span>
                                </div>
                                <div>
                                    <div class="font-medium text-gray-900"><?php echo e($record->employee->full_name); ?></div>
                                    <div class="text-sm text-gray-500"><?php echo e($record->employee->department->name ?? 'N/A'); ?></div>
                                </div>
                            </div>
                            <?php
                                $recordDate = \Carbon\Carbon::parse($record->date);
                                $isToday = $recordDate->isToday();
                                
                                // If employee is still working TODAY (time in but no time out), show "Working" status
                                // For past dates with time in but no time out, show "Incomplete" status
                                if ($record->time_in && !$record->time_out) {
                                    if ($isToday) {
                                        $statusColor = 'bg-blue-100 text-blue-800';
                                        $statusText = 'Working';
                                    } else {
                                        $statusColor = 'bg-yellow-100 text-yellow-800';
                                        $statusText = 'Incomplete';
                                    }
                                } else {
                                    $statusColors = [
                                        'present' => 'bg-green-100 text-green-800',
                                        'absent' => 'bg-red-100 text-red-800',
                                        'late' => 'bg-yellow-100 text-yellow-800',
                                        'half_day' => 'bg-blue-100 text-blue-800'
                                    ];
                                    $statusColor = $statusColors[$record->status] ?? 'bg-gray-100 text-gray-800';
                                    $statusText = ucfirst(str_replace('_', ' ', $record->status));
                                }
                            ?>
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium <?php echo e($statusColor); ?>">
                                <div class="w-1.5 h-1.5 rounded-full mr-1 <?php echo e(str_replace('text-', 'bg-', $statusColor)); ?> <?php if($record->time_in && !$record->time_out && $isToday): ?> animate-pulse <?php endif; ?>"></div>
                                <?php echo e($statusText); ?>

                            </span>
                        </div>
                        <div class="grid grid-cols-2 gap-4 text-sm mb-3">
                            <div>
                                <div class="text-gray-500">Date</div>
                                <div class="font-medium"><?php echo e(\Carbon\Carbon::parse($record->date)->format('M d, Y')); ?></div>
                            </div>
                            <div>
                                <div class="text-gray-500">Time In</div>
                                <div class="font-medium">
                                    <?php if($record->time_in): ?>
                                        <?php echo e(\Carbon\Carbon::parse($record->time_in)->format('g:i A')); ?>

                                    <?php else: ?>
                                        <span class="text-gray-400">-</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div>
                                <div class="text-gray-500">Time Out</div>
                                <div class="font-medium">
                                    <?php if($record->time_out): ?>
                                        <?php echo e(\Carbon\Carbon::parse($record->time_out)->format('g:i A')); ?>

                                    <?php elseif($record->time_in): ?>
                                        <?php
                                            $recordDate = \Carbon\Carbon::parse($record->date);
                                            $isToday = $recordDate->isToday();
                                        ?>
                                        <?php if($isToday): ?>
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                <div class="w-1.5 h-1.5 rounded-full mr-1 bg-blue-400 animate-pulse"></div>
                                                Working
                                            </span>
                                        <?php else: ?>
                                            <span class="text-gray-400">Not Clocked Out</span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="text-gray-400">-</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php if($record->time_out): ?>
                            <div>
                                <div class="text-gray-500">Total Hours</div>
                                <div class="font-medium">
                                    <?php if($record->time_in && $record->time_out): ?>
                                        <?php
                                            // Load breaks relationship if not already loaded
                                            if (!$record->relationLoaded('breaks')) {
                                                $record->load('breaks');
                                            }
                                            // Always calculate total hours to ensure accuracy
                                            $calculatedHours = $record->calculateTotalHours();
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
                            <div>
                                <div class="text-gray-500">Break Time</div>
                                <div class="font-medium">
                                    <?php
                                        // Calculate total break duration from all breaks
                                        $totalBreakMinutes = 0;
                                        
                                        // Use breaks relationship if available (for multiple breaks)
                                        if ($record->relationLoaded('breaks') && $record->breaks->count() > 0) {
                                            foreach ($record->breaks as $break) {
                                                if ($break->break_end) {
                                                    // Use stored duration if available, otherwise calculate
                                                    $totalBreakMinutes += $break->break_duration_minutes ?? $break->break_start->diffInMinutes($break->break_end);
                                                }
                                            }
                                        } 
                                        // Fallback to old break_start/break_end fields for backward compatibility
                                        elseif ($record->break_start && $record->break_end) {
                                            $breakStart = \Carbon\Carbon::parse($record->break_start);
                                            $breakEnd = \Carbon\Carbon::parse($record->break_end);
                                            $totalBreakMinutes = $breakStart->diffInMinutes($breakEnd);
                                        }
                                        
                                        $breakDuration = $totalBreakMinutes / 60;
                                    ?>
                                    <?php if($breakDuration > 0): ?>
                                        <?php echo e(\App\Helpers\TimezoneHelper::formatHours($breakDuration)); ?>

                                    <?php else: ?>
                                        <span class="text-gray-400">-</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="text-center py-8">
                        <div class="flex flex-col items-center justify-center">
                            <i class="fas fa-clock text-gray-400 text-4xl mb-4"></i>
                            <p class="text-gray-500 text-lg font-medium mb-2">No attendance records found</p>
                            <?php if($user->role === 'employee'): ?>
                                <p class="text-gray-400 text-sm mb-4">You haven't clocked in yet. Use the Time In/Out page to record your attendance.</p>
                                <a href="<?php echo e(route('attendance.time-in-out')); ?>" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                                    <i class="fas fa-clock mr-2"></i>
                                    Go to Time In/Out
                                </a>
                            <?php else: ?>
                                <p class="text-gray-400 text-sm">Try adjusting your filters or date range.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Mobile Pagination -->
            <div class="px-4 py-4 border-t border-gray-200">
                <div class="flex items-center justify-between">
                    <div class="flex-1 flex justify-between">
                        <?php echo e($attendanceRecords->appends(request()->query())->links('pagination::default')); ?>

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
    /* Flatpickr Calendar Styling - Matching other pages */
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
    
    /* Date input styling */
    input[type="date"] {
        color: #111827 !important;
        background-color: #ffffff !important;
    }
    
    input[type="date"]::-webkit-calendar-picker-indicator {
        filter: invert(0);
    }
    
    input[type="date"]::-webkit-datetime-edit-text,
    input[type="date"]::-webkit-datetime-edit-month-field,
    input[type="date"]::-webkit-datetime-edit-day-field,
    input[type="date"]::-webkit-datetime-edit-year-field {
        color: #111827 !important;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Check if Flatpickr is loaded
    if (typeof flatpickr === 'undefined') {
        console.error('Flatpickr is not loaded!');
        return;
    }
    
    const today = new Date().toISOString().split('T')[0];
    
    // Initialize Flatpickr for "From Date" - matching other pages
    const dateFromInput = document.getElementById('date_from');
    if (!dateFromInput) {
        console.error('date_from input not found');
        return;
    }
    
    const dateFromPicker = flatpickr("#date_from", {
        dateFormat: "Y-m-d",
        defaultDate: "<?php echo e(request('date_from') ? request('date_from') : ''); ?>",
        maxDate: today,
        onChange: function(selectedDates, dateStr, instance) {
            // Update min date of "To Date" when "From Date" changes
            if (dateStr && dateToPicker) {
                dateToPicker.set('minDate', dateStr);
            }
        },
        onReady: function(selectedDates, dateStr, instance) {
            // Ensure text is visible
            instance.calendarContainer.style.color = '#111827';
        }
    });
    console.log('Date From picker initialized:', dateFromPicker);
    
    // Initialize Flatpickr for "To Date" - matching other pages
    const dateToInput = document.getElementById('date_to');
    if (!dateToInput) {
        console.error('date_to input not found');
        return;
    }
    
    const dateToPicker = flatpickr("#date_to", {
        dateFormat: "Y-m-d",
        defaultDate: "<?php echo e(request('date_to') ? request('date_to') : ''); ?>",
        maxDate: today,
        minDate: "<?php echo e(request('date_from') ? request('date_from') : ''); ?>",
        onReady: function(selectedDates, dateStr, instance) {
            // Ensure text is visible
            instance.calendarContainer.style.color = '#111827';
        }
    });
    console.log('Date To picker initialized:', dateToPicker);
    
    // Make calendar icons clickable to open date picker
    const calendarIcons = document.querySelectorAll('.fa-calendar');
    calendarIcons.forEach(function(icon) {
        icon.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            const input = this.parentElement.querySelector('input[type="text"]');
            if (input && input.id === 'date_from') {
                console.log('Opening date_from picker');
                dateFromPicker.open();
            } else if (input && input.id === 'date_to') {
                console.log('Opening date_to picker');
                dateToPicker.open();
            }
        });
        icon.style.cursor = 'pointer';
        icon.style.pointerEvents = 'auto';
    });
    
    // Also ensure input clicks work
    dateFromInput.addEventListener('click', function() {
        console.log('date_from input clicked');
        dateFromPicker.open();
    });
    
    dateToInput.addEventListener('click', function() {
        console.log('date_to input clicked');
        dateToPicker.open();
    });
});
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.dashboard-base', ['user' => $user, 'activeRoute' => 'attendance.timekeeping'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\caior\Documents\Mica files\Aeternitas-System-V2\resources\views/attendance/timekeeping.blade.php ENDPATH**/ ?>
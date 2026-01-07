<?php $__env->startSection('title', 'Attendance Reports'); ?>

<!-- Flatpickr CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

<!-- Flatpickr JS -->
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<?php $__env->startSection('content'); ?>
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Attendance Reports</h1>
            <p class="mt-1 text-sm text-gray-600">Generate and view attendance reports</p>
        </div>
        <div class="mt-4 sm:mt-0 flex space-x-3" x-data="reportForm()">
            <!-- Export Dropdown -->
            <div class="relative" x-data="{ open: false }">
                <button @click="open = !open" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                <i class="fas fa-download mr-2"></i>
                    Export Report
                    <i class="fas fa-chevron-down ml-2 text-xs"></i>
            </button>
                <div x-show="open" @click.away="open = false" x-transition class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 z-10">
                    <div class="py-1">
                        <a :href="getExportUrl('pdf')" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                            <i class="fas fa-file-pdf mr-2 text-red-500"></i>Export as PDF
                        </a>
                        <a :href="getExportUrl('csv')" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                            <i class="fas fa-file-csv mr-2 text-green-500"></i>Export as CSV
                        </a>
                        <a :href="getExportUrl('xls')" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                            <i class="fas fa-file-excel mr-2 text-green-600"></i>Export as Excel
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Report Filters -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 sm:p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Report Filters</h3>
        <form method="GET" action="<?php echo e(route('attendance.reports')); ?>" x-data="reportForm()">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div>
                <label for="reportType" class="block text-sm font-medium text-gray-700 mb-2">Report Type</label>
                <select id="reportType" name="report_type" x-model="reportType" @change="updateDateInputs()" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors bg-white text-gray-900" style="background-color: white !important; color: #111827 !important;">
                    <option value="daily" style="color: #111827 !important;">Daily Attendance</option>
                    <option value="weekly" style="color: #111827 !important;">Weekly Summary</option>
                    <option value="monthly" style="color: #111827 !important;">Monthly Report</option>
                    <option value="yearly" style="color: #111827 !important;">Yearly Summary</option>
                    <option value="overtime" style="color: #111827 !important;">Overtime Report</option>
                    <option value="leave" style="color: #111827 !important;">Leave Report</option>
                </select>
            </div>
            <div>
                <label for="department" class="block text-sm font-medium text-gray-700 mb-2">Department</label>
                <select id="department" name="department_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors bg-white text-gray-900" style="background-color: white !important; color: #111827 !important;">
                    <option value="" style="color: #111827 !important;">All Departments</option>
                    <?php $__currentLoopData = $departments ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dept): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($dept->id); ?>" <?php echo e(request('department_id') == $dept->id ? 'selected' : ''); ?> style="color: #111827 !important;"><?php echo e($dept->name); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            
            <!-- Single set of hidden inputs for form submission - these will be updated dynamically -->
            <input type="hidden" name="date_from" :value="getDateFrom()">
            <input type="hidden" name="date_to" :value="getDateTo()">
            
            <!-- Daily: Single Date -->
            <div x-show="reportType === 'daily'">
                <label for="dateSingle" class="block text-sm font-medium text-gray-700 mb-2">Date</label>
                <div class="relative">
                    <input type="text" id="dateSingle" :value="dailyDate" readonly class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors bg-white text-gray-900 date-picker-input" style="background-color: white !important; color: #111827 !important;" placeholder="Select date">
                    <input type="hidden" x-model="dailyDate">
                    <i class="fas fa-calendar absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 calendar-icon" style="pointer-events: auto; cursor: pointer;"></i>
            </div>
            </div>
            
            <!-- Weekly: Week Selector -->
            <div x-show="reportType === 'weekly'">
                <label for="weekStart" class="block text-sm font-medium text-gray-700 mb-2">Week Starting</label>
                <div class="relative">
                    <input type="text" id="weekStart" :value="weekStart" readonly class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors bg-white text-gray-900 date-picker-input" style="background-color: white !important; color: #111827 !important;" placeholder="Select week start">
                    <input type="hidden" x-model="weekStart" @change="updateWeekEnd()">
                    <i class="fas fa-calendar absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 calendar-icon" style="pointer-events: auto; cursor: pointer;"></i>
        </div>
                <p class="mt-1 text-xs text-gray-500" x-text="'Week ending: ' + formatDate(weekEnd)"></p>
            </div>
            
            <!-- Monthly: Month/Year Selector -->
            <div x-show="reportType === 'monthly'">
                <label for="monthSelect" class="block text-sm font-medium text-gray-700 mb-2">Month</label>
                <div class="relative">
                    <input type="text" id="monthSelect" :value="monthValue" readonly class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors bg-white text-gray-900 date-picker-input" style="background-color: white !important; color: #111827 !important;" placeholder="Select month">
                    <input type="hidden" name="month" x-model="monthValue" @change="updateMonthDates()">
                    <i class="fas fa-calendar absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 calendar-icon" style="pointer-events: auto; cursor: pointer;"></i>
                </div>
            </div>
            
            <!-- Yearly: Year Selector -->
            <div x-show="reportType === 'yearly'">
                <label for="yearSelect" class="block text-sm font-medium text-gray-700 mb-2">Year</label>
                <select id="yearSelect" name="year" x-model="yearValue" @change="updateYearDates()" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors bg-white text-gray-900" style="background-color: white !important; color: #111827 !important;">
                    <?php for($year = now()->year; $year >= now()->year - 5; $year--): ?>
                        <option value="<?php echo e($year); ?>" style="color: #111827 !important;"><?php echo e($year); ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            
            <!-- Overtime/Leave: Date Range - Side by Side -->
            <div x-show="reportType === 'overtime' || reportType === 'leave'" class="sm:col-span-2">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="dateFrom" class="block text-sm font-medium text-gray-700 mb-2">From Date</label>
                        <div class="relative">
                            <input type="text" id="dateFrom" x-model="dateFrom" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors bg-white text-gray-900 date-picker-input" style="background-color: white !important; color: #111827 !important;" placeholder="Select from date">
                            <i class="fas fa-calendar absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 calendar-icon" style="pointer-events: auto; cursor: pointer;"></i>
                        </div>
                    </div>
                    <div>
                        <label for="dateTo" class="block text-sm font-medium text-gray-700 mb-2">To Date</label>
                        <div class="relative">
                            <input type="text" id="dateTo" x-model="dateTo" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors bg-white text-gray-900 date-picker-input" style="background-color: white !important; color: #111827 !important;" placeholder="Select to date">
                            <i class="fas fa-calendar absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 calendar-icon" style="pointer-events: auto; cursor: pointer;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Generate Report Button - Outside Grid -->
        <div class="mt-4 flex justify-end">
            <input type="hidden" name="generate" value="1">
            <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                <i class="fas fa-chart-bar mr-2"></i>Generate Report
            </button>
        </div>
        </form>
    </div>

    <!-- Report Summary -->
    <?php if($reportType === 'overtime' && isset($overtimeData)): ?>
        <!-- Overtime Report Summary -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 sm:p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-file-alt text-blue-600"></i>
                        </div>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-gray-500">Total Requests</p>
                        <p class="text-lg font-semibold text-gray-900"><?php echo e(number_format($overtimeData['summary']['total_requests'] ?? 0)); ?></p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 sm:p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-clock text-green-600"></i>
                        </div>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-gray-500">Total Hours</p>
                        <p class="text-lg font-semibold text-gray-900"><?php echo e(number_format($overtimeData['summary']['total_hours'] ?? 0, 2)); ?>h</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 sm:p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-users text-purple-600"></i>
                        </div>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-gray-500">Employees</p>
                        <p class="text-lg font-semibold text-gray-900"><?php echo e(number_format($overtimeData['summary']['total_employees'] ?? 0)); ?></p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 sm:p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-orange-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-chart-line text-orange-600"></i>
                        </div>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-gray-500">Average Hours</p>
                        <p class="text-lg font-semibold text-gray-900"><?php echo e(number_format($overtimeData['summary']['average_hours'] ?? 0, 2)); ?>h</p>
                    </div>
                </div>
            </div>
        </div>
    <?php elseif($reportType === 'leave' && isset($leaveData)): ?>
        <!-- Leave Report Summary -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 sm:p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-file-alt text-blue-600"></i>
                        </div>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-gray-500">Total Requests</p>
                        <p class="text-lg font-semibold text-gray-900"><?php echo e(number_format($leaveData['summary']['total_requests'] ?? 0)); ?></p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 sm:p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-calendar-check text-green-600"></i>
                        </div>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-gray-500">Total Days</p>
                        <p class="text-lg font-semibold text-gray-900"><?php echo e(number_format($leaveData['summary']['total_days'] ?? 0)); ?></p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 sm:p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-users text-purple-600"></i>
                        </div>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-gray-500">Employees</p>
                        <p class="text-lg font-semibold text-gray-900"><?php echo e(number_format($leaveData['summary']['total_employees'] ?? 0)); ?></p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 sm:p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-orange-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-chart-line text-orange-600"></i>
                        </div>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-gray-500">Average Days</p>
                        <p class="text-lg font-semibold text-gray-900"><?php echo e(number_format($leaveData['summary']['average_days'] ?? 0, 2)); ?></p>
                    </div>
                </div>
            </div>
        </div>
    <?php else: ?>
        <!-- Attendance Report Summary -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 sm:p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-check-circle text-green-600"></i>
                    </div>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-gray-500">Present Days</p>
                        <p class="text-lg font-semibold text-gray-900"><?php echo e(number_format($summary['present_days'] ?? 0)); ?></p>
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
                    <p class="text-sm font-medium text-gray-500">Absent Days</p>
                        <p class="text-lg font-semibold text-gray-900"><?php echo e(number_format($summary['absent_days'] ?? 0)); ?></p>
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
                    <p class="text-sm font-medium text-gray-500">Late Arrivals</p>
                        <p class="text-lg font-semibold text-gray-900"><?php echo e(number_format($summary['late_arrivals'] ?? 0)); ?></p>
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
                        <p class="text-lg font-semibold text-gray-900"><?php echo e($summary['attendance_rate'] ?? 0); ?>%</p>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php if($reportType !== 'overtime' && $reportType !== 'leave' && isset($attendanceTrend) && !empty($attendanceTrend['data'])): ?>
    <!-- Attendance Chart -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="text-lg font-medium text-gray-900">Attendance Trend</h3>
                <p class="text-sm text-gray-600 mt-1">Attendance rate over the selected period</p>
            </div>
        </div>
        <div class="h-80">
            <canvas id="attendanceTrendChart"></canvas>
        </div>
    </div>
    <?php elseif($reportType !== 'overtime' && $reportType !== 'leave'): ?>
    <!-- Attendance Chart Placeholder -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Attendance Trend</h3>
        <div class="h-64 flex items-center justify-center bg-gray-50 rounded-lg">
            <div class="text-center">
                <i class="fas fa-chart-line text-4xl text-gray-400 mb-2"></i>
                <p class="text-gray-500">Generate a report to view attendance trend</p>
                <p class="text-sm text-gray-400">Attendance trend over time</p>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php if($reportType === 'overtime' && isset($overtimeData)): ?>
        <!-- Overtime Employees Table -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-4 sm:px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Approved Overtime Requests</h3>
                <p class="mt-1 text-sm text-gray-600">Employees with approved overtime requests</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Employee</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Department</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Requests</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Hours</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Average Hours</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                        <?php $__empty_1 = true; $__currentLoopData = $overtimeData['employee_overtime'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php
                            $employee = $item['employee'];
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
                                        <div class="text-sm text-gray-500"><?php echo e($employee->employee_code ?? 'N/A'); ?></div>
                                    </div>
                                </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900"><?php echo e($employee->department->name ?? 'N/A'); ?></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900"><?php echo e($item['total_requests']); ?></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900"><?php echo e(number_format($item['total_hours'], 2)); ?>h</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900"><?php echo e(number_format($item['total_hours'] / $item['total_requests'], 2)); ?>h</div>
                        </td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="5" class="px-6 py-4 text-center text-gray-500">No approved overtime requests found for the selected period.</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
                                </div>
                            </div>
    <?php elseif($reportType === 'leave' && isset($leaveData)): ?>
        <!-- Leave Employees Table -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-4 sm:px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Approved Leave Requests</h3>
                <p class="mt-1 text-sm text-gray-600">Employees with approved leave requests</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Employee</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Department</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Requests</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Days</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Average Days</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Leave Types</th>
                    </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php $__empty_1 = true; $__currentLoopData = $leaveData['employee_leave'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php
                            $employee = $item['employee'];
                            $initials = strtoupper(substr($employee->first_name, 0, 1) . substr($employee->last_name, 0, 1));
                        ?>
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        <div class="h-10 w-10 rounded-full bg-gradient-to-r from-green-500 to-green-600 flex items-center justify-center">
                                            <span class="text-sm font-medium text-white"><?php echo e($initials); ?></span>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900"><?php echo e($employee->full_name); ?></div>
                                        <div class="text-sm text-gray-500"><?php echo e($employee->employee_code ?? 'N/A'); ?></div>
                                    </div>
                                </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900"><?php echo e($employee->department->name ?? 'N/A'); ?></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900"><?php echo e($item['total_requests']); ?></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900"><?php echo e(number_format($item['total_days'])); ?> days</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900"><?php echo e(number_format($item['total_days'] / $item['total_requests'], 2)); ?> days</div>
                        </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-wrap gap-1">
                                    <?php $__currentLoopData = $item['leave_types']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        <?php echo e($type['name']); ?> (<?php echo e($type['days']); ?>d)
                                    </span>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        </td>
                    </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-gray-500">No approved leave requests found for the selected period.</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php else: ?>
    <!-- Department-wise Attendance -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-4 sm:px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Department-wise Attendance</h3>
            <p class="mt-1 text-sm text-gray-600">Attendance summary by department</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Department
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Total Employees
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Present
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Absent
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Late
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Attendance Rate
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php $__empty_1 = true; $__currentLoopData = $departmentStats ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $stat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900"><?php echo e($stat['department']); ?></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900"><?php echo e($stat['total_employees']); ?></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900"><?php echo e($stat['present']); ?></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900"><?php echo e($stat['absent']); ?></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900"><?php echo e($stat['late']); ?></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="text-sm text-gray-900 mr-2"><?php echo e($stat['attendance_rate']); ?>%</div>
                                <div class="w-16 bg-gray-200 rounded-full h-2">
                                    <?php
                                        $color = $stat['attendance_rate'] >= 90 ? 'bg-green-600' : ($stat['attendance_rate'] >= 75 ? 'bg-yellow-600' : 'bg-red-600');
                                    ?>
                                    <div class="<?php echo e($color); ?> h-2 rounded-full" style="width: <?php echo e(min(100, $stat['attendance_rate'])); ?>%"></div>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">No data available. Please generate a report.</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>

    <?php if($reportType !== 'overtime' && $reportType !== 'leave'): ?>
    <!-- Top Performers -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Best Attendance -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Best Attendance</h3>
            <div class="space-y-3">
                <?php $__empty_1 = true; $__currentLoopData = $bestAttendance ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <?php
                    $employee = $item['employee'];
                    $initials = strtoupper(substr($employee->first_name, 0, 1) . substr($employee->last_name, 0, 1));
                    $rate = $item['rate'];
                    $label = $rate >= 98 ? 'Perfect' : ($rate >= 95 ? 'Excellent' : 'Good');
                ?>
                <div class="flex items-center justify-between p-3 bg-green-50 rounded-lg">
                    <div class="flex items-center">
                        <div class="w-8 h-8 rounded-full bg-gradient-to-r from-green-500 to-green-600 flex items-center justify-center mr-3">
                            <span class="text-xs font-medium text-white"><?php echo e($initials); ?></span>
                        </div>
                        <div>
                            <div class="font-medium text-gray-900"><?php echo e($employee->full_name); ?></div>
                            <div class="text-sm text-gray-500"><?php echo e($employee->department->name ?? 'N/A'); ?></div>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="text-sm font-medium text-green-600"><?php echo e($rate); ?>%</div>
                        <div class="text-xs text-gray-500"><?php echo e($label); ?></div>
                    </div>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <p class="text-gray-500 text-sm text-center py-4">No data available. Please generate a report.</p>
                <?php endif; ?>
                        </div>
                        </div>

        <!-- Needs Attention -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6" 
             x-data="{ 
                 currentPage: 1, 
                 itemsPerPage: 1,
                 needsAttention: <?php echo \Illuminate\Support\Js::from(collect($needsAttention ?? [])->map(function($item) use ($dateFrom, $dateTo) {
                     $employee = $item['employee'];
                     return [
                         'employee_id' => $employee->id,
                         'employee_name' => $employee->full_name,
                         'employee_initials' => strtoupper(substr($employee->first_name, 0, 1) . substr($employee->last_name, 0, 1)),
                         'department' => $employee->department->name ?? 'N/A',
                         'rate' => $item['rate'],
                         'absent_days' => $item['absent_days'] ?? 0,
                         'late_arrivals' => $item['late_arrivals'] ?? 0,
                         'consecutive_absences' => $item['consecutive_absences'] ?? 0,
                         'last_attendance_date' => $item['last_attendance_date'] ? \Carbon\Carbon::parse($item['last_attendance_date'])->format('M d, Y') : 'N/A',
                         'bg_color' => $item['rate'] < 75 ? 'bg-red-50' : 'bg-yellow-50',
                         'text_color' => $item['rate'] < 75 ? 'text-red-600' : 'text-yellow-600',
                         'gradient' => $item['rate'] < 75 ? 'from-red-500 to-red-600' : 'from-yellow-500 to-yellow-600',
                         'border_color' => $item['rate'] < 75 ? 'border-red-200' : 'border-yellow-200',
                         'label' => $item['rate'] < 75 ? 'Below Average' : 'Needs Improvement',
                         'view_url' => route('attendance.timekeeping', ['employee_id' => $employee->id, 'date_from' => $dateFrom, 'date_to' => $dateTo]),
                     ];
                 })->toArray())->toHtml() ?>,
                 get totalPages() {
                     return Math.ceil(this.needsAttention.length / this.itemsPerPage);
                 },
                 get displayedItems() {
                     const start = (this.currentPage - 1) * this.itemsPerPage;
                     const end = start + this.itemsPerPage;
                     return this.needsAttention.slice(start, end);
                 },
                 get hasMore() {
                     return this.currentPage < this.totalPages;
                 },
                 nextPage() {
                     if (this.hasMore) {
                         this.currentPage++;
                     }
                 },
                 prevPage() {
                     if (this.currentPage > 1) {
                         this.currentPage--;
                     }
                 }
             }">
            <div class="flex items-center justify-between mb-4">
                        <div>
                    <h3 class="text-lg font-medium text-gray-900">Needs Attention</h3>
                    <p class="text-sm text-gray-600 mt-1">Employees with attendance rate below 90%</p>
                        </div>
                <div class="text-sm text-gray-500" x-show="needsAttention.length > 0">
                    <span x-text="`Showing ${(currentPage - 1) * itemsPerPage + 1}-${Math.min(currentPage * itemsPerPage, needsAttention.length)} of ${needsAttention.length}`"></span>
                    </div>
                    </div>
            
            <div class="space-y-3" x-show="needsAttention.length > 0">
                <template x-for="(item, index) in displayedItems" :key="index">
                    <div class="p-4" :class="item.bg_color" :class="'border ' + item.border_color" class="rounded-lg">
                        <div class="flex items-start justify-between mb-3">
                            <div class="flex items-center flex-1">
                                <div class="w-10 h-10 rounded-full flex items-center justify-center mr-3 flex-shrink-0" :class="'bg-gradient-to-r ' + item.gradient">
                                    <span class="text-sm font-medium text-white" x-text="item.employee_initials"></span>
                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="font-medium text-gray-900" x-text="item.employee_name"></div>
                                    <div class="text-sm text-gray-500" x-text="item.department"></div>
                                </div>
                            </div>
                            <div class="text-right ml-4">
                                <div class="text-lg font-bold" :class="item.text_color" x-text="item.rate + '%'"></div>
                                <div class="text-xs text-gray-500" x-text="item.label"></div>
            </div>
        </div>

                        <!-- Additional Details -->
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mt-3 pt-3" :class="'border-t ' + item.border_color">
                            <div>
                                <div class="text-xs text-gray-500 mb-1">Absent Days</div>
                                <div class="text-sm font-semibold text-gray-900" x-text="item.absent_days"></div>
                        </div>
                        <div>
                                <div class="text-xs text-gray-500 mb-1">Late Arrivals</div>
                                <div class="text-sm font-semibold text-gray-900" x-text="item.late_arrivals"></div>
                        </div>
                            <div>
                                <div class="text-xs text-gray-500 mb-1">Consecutive Absences</div>
                                <div class="text-sm font-semibold" :class="item.consecutive_absences > 0 ? 'text-red-600' : 'text-gray-900'" x-text="item.consecutive_absences"></div>
                    </div>
                            <div>
                                <div class="text-xs text-gray-500 mb-1">Last Attendance</div>
                                <div class="text-sm font-semibold text-gray-900" x-text="item.last_attendance_date"></div>
                    </div>
                </div>
                        
                        <!-- Action Button -->
                        <div class="mt-3 flex justify-end">
                            <a :href="item.view_url" 
                               class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-blue-600 bg-blue-50 rounded-md hover:bg-blue-100 transition-colors">
                                <i class="fas fa-eye mr-1.5"></i>View Details
                            </a>
                        </div>
                        </div>
                </template>
                    </div>
            
            <!-- Pagination Controls -->
            <div class="flex items-center justify-between mt-6 pt-4 border-t border-gray-200" x-show="needsAttention.length > 1">
                <button @click="prevPage()" 
                        :disabled="currentPage === 1"
                        :class="currentPage === 1 ? 'opacity-50 cursor-not-allowed' : 'hover:bg-gray-50'"
                        class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg transition-colors">
                    <i class="fas fa-chevron-left mr-2"></i>Previous
                </button>
                
                <div class="text-sm text-gray-600" x-text="`Page ${currentPage} of ${totalPages}`"></div>
                
                <button @click="nextPage()" 
                        :disabled="!hasMore"
                        :class="!hasMore ? 'opacity-50 cursor-not-allowed' : 'hover:bg-gray-50'"
                        class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg transition-colors">
                    Next<i class="fas fa-chevron-right ml-2"></i>
                </button>
                    </div>
            
            <!-- Empty State -->
            <div class="text-center py-8" x-show="needsAttention.length === 0">
                <i class="fas fa-check-circle text-green-500 text-4xl mb-3"></i>
                <p class="text-gray-500 text-sm">No employees need attention. All attendance rates are above 90%.</p>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

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
    
    .date-picker-input {
        padding-right: 2.5rem;
    }
    
    .fa-calendar {
        pointer-events: auto !important;
        cursor: pointer;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Wait for Alpine.js to initialize
    setTimeout(function() {
        // Check if Flatpickr is loaded
        if (typeof flatpickr === 'undefined') {
            console.error('Flatpickr is not loaded!');
            return;
        }
        
        const today = new Date().toISOString().split('T')[0];
        let dateFromPicker = null;
        let dateToPicker = null;
        window.dateSinglePicker = null;
        window.weekStartPicker = null;
        
        // Initialize Flatpickr for Monthly Date Picker
        const monthSelectInput = document.getElementById('monthSelect');
        if (monthSelectInput) {
            const initMonthPicker = function() {
                if (!window.Alpine) {
                    setTimeout(initMonthPicker, 50);
                    return;
                }
                
                const alpineElement = monthSelectInput.closest('[x-data]');
                const alpineData = alpineElement ? Alpine.$data(alpineElement) : null;
                const initialMonth = alpineData?.monthValue || "<?php echo e(request('month', now()->format('Y-m'))); ?>";
                
                // Destroy existing instance if any
                if (window.monthPicker) {
                    window.monthPicker.destroy();
                }
                
                window.monthPicker = flatpickr(monthSelectInput, {
                    dateFormat: "Y-m-d",
                    altInput: false,
                    defaultDate: initialMonth + '-01', // Add day for defaultDate
                    maxDate: today,
                    allowInput: false,
                    clickOpens: true,
                    mode: "single",
                    onChange: function(selectedDates, dateStr, instance) {
                        console.log('Month picker onChange triggered:', selectedDates, dateStr);
                        if (selectedDates.length > 0) {
                            const selectedDate = selectedDates[0];
                            // Format as Y-m (year-month)
                            const year = selectedDate.getFullYear();
                            const month = String(selectedDate.getMonth() + 1).padStart(2, '0');
                            const monthValue = year + '-' + month;
                            
                            console.log('Formatted month value:', monthValue);
                            
                            if (alpineData) {
                                alpineData.monthValue = monthValue;
                                if (alpineData.updateMonthDates) {
                                    alpineData.updateMonthDates();
                                }
                                monthSelectInput.value = monthValue;
                                // Update hidden input
                                const hiddenInput = monthSelectInput.parentElement.querySelector('input[type="hidden"]');
                                if (hiddenInput) {
                                    hiddenInput.value = monthValue;
                                }
                                // Close the calendar after selection
                                setTimeout(() => {
                                    instance.close();
                                }, 100);
                            }
                        }
                    },
                    onClose: function(selectedDates, dateStr, instance) {
                        console.log('Month picker closed:', selectedDates, dateStr);
                        // Ensure value is updated even if closed without explicit selection
                        if (selectedDates.length > 0 && alpineData) {
                            const selectedDate = selectedDates[0];
                            const year = selectedDate.getFullYear();
                            const month = String(selectedDate.getMonth() + 1).padStart(2, '0');
                            const monthValue = year + '-' + month;
                            if (alpineData.monthValue !== monthValue) {
                                alpineData.monthValue = monthValue;
                                if (alpineData.updateMonthDates) {
                                    alpineData.updateMonthDates();
                                }
                                monthSelectInput.value = monthValue;
                            }
                        }
                    },
                    onReady: function(selectedDates, dateStr, instance) {
                        instance.calendarContainer.style.color = '#111827';
                        // Format initial value if date is selected
                        if (selectedDates.length > 0 && alpineData) {
                            const selectedDate = selectedDates[0];
                            const year = selectedDate.getFullYear();
                            const month = String(selectedDate.getMonth() + 1).padStart(2, '0');
                            const monthValue = year + '-' + month;
                            alpineData.monthValue = monthValue;
                            monthSelectInput.value = monthValue;
                        }
                    }
                });
            };
            
            initMonthPicker();
        }
        
        // Function to initialize date pickers - will be called when inputs become visible
        window.initDatePickers = function() {
            // Initialize Daily Date Picker if visible and not already initialized
            const dateSingleInput = document.getElementById('dateSingle');
            if (dateSingleInput && !window.dateSinglePicker && dateSingleInput.offsetParent !== null) {
                const alpineElement = dateSingleInput.closest('[x-data]');
                const alpineData = alpineElement ? Alpine.$data(alpineElement) : null;
                const initialDate = alpineData?.dailyDate || "<?php echo e(request('date_from', today()->format('Y-m-d'))); ?>";
                
                // Destroy existing instance if any
                if (window.dateSinglePicker) {
                    window.dateSinglePicker.destroy();
                }
                
                window.dateSinglePicker = flatpickr(dateSingleInput, {
                    dateFormat: "Y-m-d",
                    altInput: false,
                    defaultDate: initialDate,
                    maxDate: today,
                    allowInput: false,
                    clickOpens: true,
                    onChange: function(selectedDates, dateStr) {
                        console.log('Daily date changed to:', dateStr);
                        if (dateStr && alpineData) {
                            alpineData.dailyDate = dateStr;
                            dateSingleInput.value = dateStr;
                            // Update hidden input
                            const hiddenInput = dateSingleInput.parentElement.querySelector('input[type="hidden"]');
                            if (hiddenInput) {
                                hiddenInput.value = dateStr;
                            }
                        }
                    },
                    onReady: function(selectedDates, dateStr, instance) {
                        instance.calendarContainer.style.color = '#111827';
                    }
                });
            }
            
            // Initialize Weekly Start Date Picker if visible and not already initialized
            const weekStartInput = document.getElementById('weekStart');
            if (weekStartInput && !window.weekStartPicker && weekStartInput.offsetParent !== null) {
                const alpineElement = weekStartInput.closest('[x-data]');
                const alpineData = alpineElement ? Alpine.$data(alpineElement) : null;
                const initialDate = alpineData?.weekStart || "<?php echo e(request('date_from', now()->startOfWeek()->format('Y-m-d'))); ?>";
                
                // Destroy existing instance if any
                if (window.weekStartPicker) {
                    window.weekStartPicker.destroy();
                }
                
                window.weekStartPicker = flatpickr(weekStartInput, {
                    dateFormat: "Y-m-d",
                    altInput: false,
                    defaultDate: initialDate,
                    maxDate: today,
                    allowInput: false,
                    clickOpens: true,
                    onChange: function(selectedDates, dateStr) {
                        console.log('Weekly date changed to:', dateStr);
                        if (dateStr && alpineData) {
                            alpineData.weekStart = dateStr;
                            if (alpineData.updateWeekEnd) {
                                alpineData.updateWeekEnd();
                            }
                            weekStartInput.value = dateStr;
                            // Update hidden input
                            const hiddenInput = weekStartInput.parentElement.querySelector('input[type="hidden"]');
                            if (hiddenInput) {
                                hiddenInput.value = dateStr;
                            }
                        }
                    },
                    onReady: function(selectedDates, dateStr, instance) {
                        instance.calendarContainer.style.color = '#111827';
                    }
                });
            }
            
            // Initialize Monthly Date Picker if visible and not already initialized
            const monthSelectInput = document.getElementById('monthSelect');
            if (monthSelectInput && !window.monthPicker && monthSelectInput.offsetParent !== null) {
                const alpineElement = monthSelectInput.closest('[x-data]');
                const alpineData = alpineElement ? Alpine.$data(alpineElement) : null;
                const initialMonth = alpineData?.monthValue || "<?php echo e(request('month', now()->format('Y-m'))); ?>";
                
                // Destroy existing instance if any
                if (window.monthPicker) {
                    window.monthPicker.destroy();
                }
                
                window.monthPicker = flatpickr(monthSelectInput, {
                    dateFormat: "Y-m-d",
                    altInput: false,
                    defaultDate: initialMonth + '-01', // Add day for defaultDate
                    maxDate: today,
                    allowInput: false,
                    clickOpens: true,
                    mode: "single",
                    onChange: function(selectedDates, dateStr, instance) {
                        console.log('Month picker onChange triggered:', selectedDates, dateStr);
                        if (selectedDates.length > 0) {
                            const selectedDate = selectedDates[0];
                            // Format as Y-m (year-month)
                            const year = selectedDate.getFullYear();
                            const month = String(selectedDate.getMonth() + 1).padStart(2, '0');
                            const monthValue = year + '-' + month;
                            
                            console.log('Formatted month value:', monthValue);
                            
                            if (alpineData) {
                                alpineData.monthValue = monthValue;
                                if (alpineData.updateMonthDates) {
                                    alpineData.updateMonthDates();
                                }
                                monthSelectInput.value = monthValue;
                                // Update hidden input
                                const hiddenInput = monthSelectInput.parentElement.querySelector('input[type="hidden"]');
                                if (hiddenInput) {
                                    hiddenInput.value = monthValue;
                                }
                                // Close the calendar after selection
                                instance.close();
                            }
                        }
                    },
                    onClose: function(selectedDates, dateStr, instance) {
                        console.log('Month picker closed:', selectedDates, dateStr);
                        // Ensure value is updated even if closed without explicit selection
                        if (selectedDates.length > 0 && alpineData) {
                            const selectedDate = selectedDates[0];
                            const year = selectedDate.getFullYear();
                            const month = String(selectedDate.getMonth() + 1).padStart(2, '0');
                            const monthValue = year + '-' + month;
                            if (alpineData.monthValue !== monthValue) {
                                alpineData.monthValue = monthValue;
                                if (alpineData.updateMonthDates) {
                                    alpineData.updateMonthDates();
                                }
                                monthSelectInput.value = monthValue;
                            }
                        }
                    },
                    onReady: function(selectedDates, dateStr, instance) {
                        instance.calendarContainer.style.color = '#111827';
                        // Format initial value if date is selected
                        if (selectedDates.length > 0 && alpineData) {
                            const selectedDate = selectedDates[0];
                            const year = selectedDate.getFullYear();
                            const month = String(selectedDate.getMonth() + 1).padStart(2, '0');
                            const monthValue = year + '-' + month;
                            alpineData.monthValue = monthValue;
                            monthSelectInput.value = monthValue;
                        }
                    }
                });
            }
        };
        
        // Try to initialize pickers after Alpine is ready
        if (window.Alpine) {
            setTimeout(() => window.initDatePickers(), 300);
        } else {
            // Wait for Alpine
            const checkAlpine = setInterval(() => {
                if (window.Alpine) {
                    clearInterval(checkAlpine);
                    setTimeout(() => window.initDatePickers(), 300);
                }
            }, 100);
        }
        
        // Initialize Flatpickr for Overtime/Leave From Date
        const dateFromInput = document.getElementById('dateFrom');
        if (dateFromInput) {
            dateFromPicker = flatpickr("#dateFrom", {
                dateFormat: "Y-m-d",
                altInput: true,
                altFormat: "d/m/Y",
                defaultDate: "<?php echo e(request('date_from', now()->startOfMonth()->format('Y-m-d'))); ?>",
                maxDate: today,
                allowInput: true,
                clickOpens: true,
                onChange: function(selectedDates, dateStr, instance) {
                    // Update min date of "To Date" when "From Date" changes
                    if (dateStr && dateToPicker) {
                        dateToPicker.set('minDate', dateStr);
                    }
                    // Update Alpine model
                    if (window.Alpine) {
                        const alpineElement = dateFromInput.closest('[x-data]');
                        if (alpineElement) {
                            const alpineData = Alpine.$data(alpineElement);
                            if (alpineData) {
                                alpineData.dateFrom = dateStr;
                            }
                        }
                    }
                },
                onReady: function(selectedDates, dateStr, instance) {
                    instance.calendarContainer.style.color = '#111827';
                }
            });
        }
        
        // Initialize Flatpickr for Overtime/Leave To Date
        const dateToInput = document.getElementById('dateTo');
        if (dateToInput) {
            dateToPicker = flatpickr("#dateTo", {
                dateFormat: "Y-m-d",
                altInput: true,
                altFormat: "d/m/Y",
                defaultDate: "<?php echo e(request('date_to', now()->format('Y-m-d'))); ?>",
                maxDate: today,
                minDate: "<?php echo e(request('date_from', now()->startOfMonth()->format('Y-m-d'))); ?>",
                allowInput: true,
                clickOpens: true,
                onChange: function(selectedDates, dateStr, instance) {
                    // Update Alpine model
                    if (window.Alpine) {
                        const alpineElement = dateToInput.closest('[x-data]');
                        if (alpineElement) {
                            const alpineData = Alpine.$data(alpineElement);
                            if (alpineData) {
                                alpineData.dateTo = dateStr;
                            }
                        }
                    }
                },
                onReady: function(selectedDates, dateStr, instance) {
                    instance.calendarContainer.style.color = '#111827';
                }
            });
        }
        
        // Make calendar icons clickable
        document.querySelectorAll('.calendar-icon, .fa-calendar').forEach(function(icon) {
            icon.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                const input = this.parentElement.querySelector('.date-picker-input');
                if (input) {
                    if (input.id === 'dateSingle' && window.dateSinglePicker) {
                        window.dateSinglePicker.open();
                    } else if (input.id === 'weekStart' && window.weekStartPicker) {
                        window.weekStartPicker.open();
                    } else if (input.id === 'monthSelect' && window.monthPicker) {
                        window.monthPicker.open();
                    } else if (input.id === 'dateFrom' && dateFromPicker) {
                        dateFromPicker.open();
                    } else if (input.id === 'dateTo' && dateToPicker) {
                        dateToPicker.open();
                    }
                }
            });
            icon.style.cursor = 'pointer';
            icon.style.pointerEvents = 'auto';
        });
        
        // Also make inputs clickable to open calendar
        document.querySelectorAll('.date-picker-input').forEach(function(input) {
            input.addEventListener('click', function() {
                if (this.id === 'dateSingle' && window.dateSinglePicker) {
                    window.dateSinglePicker.open();
                } else if (this.id === 'weekStart' && window.weekStartPicker) {
                    window.weekStartPicker.open();
                } else if (this.id === 'monthSelect' && window.monthPicker) {
                    window.monthPicker.open();
                } else if (this.id === 'dateFrom' && dateFromPicker) {
                    dateFromPicker.open();
                } else if (this.id === 'dateTo' && dateToPicker) {
                    dateToPicker.open();
                }
            });
        });
        
        // Re-initialize pickers when report type changes (Alpine.js)
        if (window.Alpine) {
            Alpine.effect(function() {
                const reportTypeSelect = document.getElementById('reportType');
                if (reportTypeSelect) {
                    reportTypeSelect.addEventListener('change', function() {
                        setTimeout(function() {
                            // Re-initialize pickers for newly visible inputs
                            const newDateFrom = document.getElementById('dateFrom');
                            const newDateTo = document.getElementById('dateTo');
                            const newDateSingle = document.getElementById('dateSingle');
                            const newWeekStart = document.getElementById('weekStart');
                            
                            if (newDateFrom && !dateFromPicker) {
                                dateFromPicker = flatpickr("#dateFrom", {
                                    dateFormat: "Y-m-d",
                                    altInput: true,
                                    altFormat: "d/m/Y",
                                    maxDate: today,
                                    allowInput: true,
                                    clickOpens: true,
                                    onChange: function(selectedDates, dateStr, instance) {
                                        if (dateStr && dateToPicker) {
                                            dateToPicker.set('minDate', dateStr);
                                        }
                                    },
                                    onReady: function(selectedDates, dateStr, instance) {
                                        instance.calendarContainer.style.color = '#111827';
                                    }
                                });
                            }
                            
                            if (newDateTo && !dateToPicker) {
                                dateToPicker = flatpickr("#dateTo", {
                                    dateFormat: "Y-m-d",
                                    altInput: true,
                                    altFormat: "d/m/Y",
                                    maxDate: today,
                                    allowInput: true,
                                    clickOpens: true,
                                    onReady: function(selectedDates, dateStr, instance) {
                                        instance.calendarContainer.style.color = '#111827';
                                    }
                                });
                            }
                            
                            if (newDateSingle && !window.dateSinglePicker) {
                                window.dateSinglePicker = flatpickr("#dateSingle", {
                                    dateFormat: "Y-m-d",
                                    altInput: true,
                                    altFormat: "d/m/Y",
                                    maxDate: today,
                                    allowInput: true,
                                    clickOpens: true,
                                    onReady: function(selectedDates, dateStr, instance) {
                                        instance.calendarContainer.style.color = '#111827';
                                    }
                                });
                            }
                            
                            if (newWeekStart && !window.weekStartPicker) {
                                window.weekStartPicker = flatpickr("#weekStart", {
                                    dateFormat: "Y-m-d",
                                    altInput: true,
                                    altFormat: "d/m/Y",
                                    maxDate: today,
                                    allowInput: true,
                                    clickOpens: true,
                                    onReady: function(selectedDates, dateStr, instance) {
                                        instance.calendarContainer.style.color = '#111827';
                                    }
                                });
                            }
                        }, 100);
                    });
                }
            });
        }
    }, 500);
});

function reportForm() {
    const today = new Date();
    const currentWeekStart = getWeekStart(today);
    const currentWeekEnd = new Date(currentWeekStart);
    currentWeekEnd.setDate(currentWeekEnd.getDate() + 6);
    
    const currentMonth = today.getFullYear() + '-' + String(today.getMonth() + 1).padStart(2, '0');
    const monthStartDate = new Date(today.getFullYear(), today.getMonth(), 1);
    const monthEndDate = new Date(today.getFullYear(), today.getMonth() + 1, 0);
    
    const currentYear = today.getFullYear();
    const yearStartDate = new Date(currentYear, 0, 1);
    const yearEndDate = new Date(currentYear, 11, 31);
    
    return {
        reportType: '<?php echo e(request('report_type', 'daily')); ?>',
        dailyDate: '<?php echo e(request('date_from', today()->format('Y-m-d'))); ?>',
        weekStart: '<?php echo e(request('date_from', now()->startOfWeek()->format('Y-m-d'))); ?>',
        weekEnd: '<?php echo e(request('date_to', now()->endOfWeek()->format('Y-m-d'))); ?>',
        monthValue: '<?php echo e(request('month', now()->format('Y-m'))); ?>',
        monthStart: '<?php echo e(request('date_from', now()->startOfMonth()->format('Y-m-d'))); ?>',
        monthEnd: '<?php echo e(request('date_to', now()->endOfMonth()->format('Y-m-d'))); ?>',
        yearValue: '<?php echo e(request('year', now()->year)); ?>',
        yearStart: '<?php echo e(request('date_from', now()->startOfYear()->format('Y-m-d'))); ?>',
        yearEnd: '<?php echo e(request('date_to', now()->endOfYear()->format('Y-m-d'))); ?>',
        dateFrom: '<?php echo e(request('date_from', now()->startOfMonth()->format('Y-m-d'))); ?>',
        dateTo: '<?php echo e(request('date_to', now()->format('Y-m-d'))); ?>',
        
        updateDateInputs() {
            const today = new Date();
            switch(this.reportType) {
                case 'daily':
                    this.dailyDate = today.toISOString().split('T')[0];
                    break;
                case 'weekly':
                    const weekStart = getWeekStart(today);
                    this.weekStart = weekStart.toISOString().split('T')[0];
                    this.updateWeekEnd();
                    break;
                case 'monthly':
                    this.monthValue = today.getFullYear() + '-' + String(today.getMonth() + 1).padStart(2, '0');
                    this.updateMonthDates();
                    break;
                case 'yearly':
                    this.yearValue = today.getFullYear();
                    this.updateYearDates();
                    break;
                case 'overtime':
                case 'leave':
                    const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
                    this.dateFrom = firstDay.toISOString().split('T')[0];
                    this.dateTo = today.toISOString().split('T')[0];
                    break;
            }
        },
        
        updateWeekEnd() {
            if (this.weekStart) {
                const start = new Date(this.weekStart);
                const end = new Date(start);
                end.setDate(end.getDate() + 6);
                this.weekEnd = end.toISOString().split('T')[0];
            }
        },
        
        init() {
            // Watch for reportType changes and initialize pickers when inputs become visible
            this.$watch('reportType', (newType) => {
                // Wait for Alpine to show/hide elements, then initialize pickers
                setTimeout(() => {
                    if (window.initDatePickers) {
                        window.initDatePickers();
                    }
                    // Also initialize/reinitialize month picker if monthly is selected
                    if (newType === 'monthly') {
                        const monthInput = document.getElementById('monthSelect');
                        if (monthInput && monthInput.offsetParent !== null) {
                            const alpineData = Alpine.$data(monthInput.closest('[x-data]'));
                            if (window.monthPicker) {
                                window.monthPicker.destroy();
                            }
                            const initialMonth = alpineData?.monthValue || "<?php echo e(now()->format('Y-m')); ?>";
                            window.monthPicker = flatpickr(monthInput, {
                                dateFormat: "Y-m-d",
                                altInput: false,
                                defaultDate: initialMonth + '-01', // Add day for defaultDate
                                maxDate: new Date().toISOString().split('T')[0],
                                allowInput: false,
                                clickOpens: true,
                                mode: "single",
                                onChange: function(selectedDates, dateStr) {
                                    if (selectedDates.length > 0) {
                                        const selectedDate = selectedDates[0];
                                        // Format as Y-m (year-month)
                                        const year = selectedDate.getFullYear();
                                        const month = String(selectedDate.getMonth() + 1).padStart(2, '0');
                                        const monthValue = year + '-' + month;
                                        
                                        if (alpineData) {
                                            alpineData.monthValue = monthValue;
                                            if (alpineData.updateMonthDates) {
                                                alpineData.updateMonthDates();
                                            }
                                            monthInput.value = monthValue;
                                            const hiddenInput = monthInput.parentElement.querySelector('input[type="hidden"]');
                                            if (hiddenInput) {
                                                hiddenInput.value = monthValue;
                                            }
                                        }
                                    }
                                },
                                onReady: function(selectedDates, dateStr, instance) {
                                    instance.calendarContainer.style.color = '#111827';
                                    // Format initial value if date is selected
                                    if (selectedDates.length > 0 && alpineData) {
                                        const selectedDate = selectedDates[0];
                                        const year = selectedDate.getFullYear();
                                        const month = String(selectedDate.getMonth() + 1).padStart(2, '0');
                                        const monthValue = year + '-' + month;
                                        alpineData.monthValue = monthValue;
                                        monthInput.value = monthValue;
                                    }
                                }
                            });
                        }
                    }
                }, 200);
            });
            
            // Also initialize on initial load if inputs are visible
            setTimeout(() => {
                if (window.initDatePickers) {
                    window.initDatePickers();
                }
            }, 500);
        },
        
        updateMonthDates() {
            if (this.monthValue) {
                const [year, month] = this.monthValue.split('-');
                const start = new Date(year, month - 1, 1);
                const end = new Date(year, month, 0);
                this.monthStart = start.toISOString().split('T')[0];
                this.monthEnd = end.toISOString().split('T')[0];
            }
        },
        
        updateYearDates() {
            if (this.yearValue) {
                const start = new Date(this.yearValue, 0, 1);
                const end = new Date(this.yearValue, 11, 31);
                this.yearStart = start.toISOString().split('T')[0];
                this.yearEnd = end.toISOString().split('T')[0];
            }
        },
        
        formatDate(dateString) {
            if (!dateString) return '';
            const date = new Date(dateString);
            return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
        },
        
        getDateFrom() {
            switch(this.reportType) {
                case 'daily':
                    return this.dailyDate || '';
                case 'weekly':
                    return this.weekStart || '';
                case 'monthly':
                    return this.monthStart || '';
                case 'yearly':
                    return this.yearStart || '';
                case 'overtime':
                case 'leave':
                    return this.dateFrom || '';
                default:
                    return '';
            }
        },
        
        getDateTo() {
            switch(this.reportType) {
                case 'daily':
                    return this.dailyDate || '';
                case 'weekly':
                    return this.weekEnd || '';
                case 'monthly':
                    return this.monthEnd || '';
                case 'yearly':
                    return this.yearEnd || '';
                case 'overtime':
                case 'leave':
                    return this.dateTo || '';
                default:
                    return '';
            }
        },
        
        getExportUrl(format) {
            const baseUrl = '<?php echo e(route("attendance.reports.export", ["format" => "FORMAT"])); ?>'.replace('FORMAT', format);
            const params = new URLSearchParams();
            
            params.append('report_type', this.reportType);
            params.append('date_from', this.getDateFrom());
            params.append('date_to', this.getDateTo());
            
            const departmentSelect = document.getElementById('department');
            if (departmentSelect && departmentSelect.value) {
                params.append('department_id', departmentSelect.value);
            }
            
            if (this.reportType === 'monthly' && this.monthValue) {
                params.append('month', this.monthValue);
            }
            
            if (this.reportType === 'yearly' && this.yearValue) {
                params.append('year', this.yearValue);
            }
            
            return baseUrl + '?' + params.toString();
        }
    };
}

function getWeekStart(date) {
    const d = new Date(date);
    const day = d.getDay();
    const diff = d.getDate() - day + (day === 0 ? -6 : 1); // Adjust when day is Sunday
    return new Date(d.setDate(diff));
}

// Initialize Attendance Trend Chart
<?php if(isset($attendanceTrend) && !empty($attendanceTrend['data'])): ?>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('attendanceTrendChart');
    if (ctx) {
        const trendData = <?php echo json_encode($attendanceTrend, 15, 512) ?>;
        
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: trendData.labels,
                datasets: [{
                    label: 'Attendance Rate (%)',
                    data: trendData.data,
                    borderColor: 'rgb(59, 130, 246)',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    pointBackgroundColor: 'rgb(59, 130, 246)',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                        callbacks: {
                            label: function(context) {
                                return 'Attendance Rate: ' + context.parsed.y + '%';
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        ticks: {
                            callback: function(value) {
                                return value + '%';
                            }
                        },
                        title: {
                            display: true,
                            text: 'Attendance Rate (%)'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Period'
                        },
                        ticks: {
                            maxRotation: 45,
                            minRotation: 45
                        }
                    }
                },
                interaction: {
                    mode: 'nearest',
                    axis: 'x',
                    intersect: false
                }
            }
        });
    }
});
<?php endif; ?>
</script>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.dashboard-base', ['user' => $user, 'activeRoute' => 'attendance.reports'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\caior\Documents\Mica files\Aeternitas-System-V2\resources\views/attendance/reports.blade.php ENDPATH**/ ?>
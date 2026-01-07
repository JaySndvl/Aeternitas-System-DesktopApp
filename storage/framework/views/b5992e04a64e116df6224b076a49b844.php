<?php $__env->startSection('title', 'Payroll Management'); ?>

<?php $__env->startSection('content'); ?>
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Payroll Management</h1>
            <p class="mt-1 text-sm text-gray-600">Manage employee salaries, deductions, and payments</p>
        </div>
        <div class="mt-4 sm:mt-0 flex space-x-3">
        <form action="<?php echo e(route('payrolls.generate')); ?>" method="POST" class="inline" id="generatePayrollForm">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="start_date" id="generateStartDate" value="">
            <input type="hidden" name="end_date" id="generateEndDate" value="">
            <button type="button" onclick="generatePayroll()" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-lg font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                <i class="fas fa-plus mr-2"></i>
                Generate Payroll
            </button>
        </form>
        </div>
    </div>

    <!-- Flash Messages -->
<?php if(session('success')): ?>
<div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">
    <div class="flex items-center">
        <i class="fas fa-check-circle mr-2"></i>
        <?php echo e(session('success')); ?>

    </div>
</div>
<?php endif; ?>

<?php if(session('error')): ?>
<div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg">
    <div class="flex items-center">
        <i class="fas fa-exclamation-circle mr-2"></i>
        <?php echo e(session('error')); ?>

    </div>
</div>
<?php endif; ?>

<?php if(session('info')): ?>
<div class="mb-4 p-4 bg-blue-100 border border-blue-400 text-blue-700 rounded-lg">
    <div class="flex items-center">
        <i class="fas fa-info-circle mr-2"></i>
        <?php echo e(session('info')); ?>

    </div>
</div>
<?php endif; ?>

    <!-- Payroll Period Selector -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 sm:p-6">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
            <div class="mb-4 lg:mb-0">
                <h3 class="text-lg font-medium text-gray-900">Payroll Period</h3>
                <p class="text-sm text-gray-600">Select the payroll period to view and manage</p>
            </div>
            <div class="flex flex-col sm:flex-row space-y-3 sm:space-y-0 sm:space-x-3">
                <!-- Single Calendar Date Range Picker -->
                <div class="flex-1">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Select Date Range</label>
                    <div class="relative">
                        <button onclick="toggleCalendar()" 
                                id="dateRangeButton" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-white text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors text-left flex items-center justify-between">
                            <span id="dateRangeText"><?php echo e(date('M d, Y')); ?> - <?php echo e(date('M d, Y')); ?></span>
                            <i class="fas fa-calendar-alt text-gray-400"></i>
                        </button>
                        
                        <!-- Calendar Popup -->
                        <div id="calendarPopup" class="absolute top-full left-0 mt-1 bg-white border border-gray-200 rounded-lg shadow-lg z-50 hidden">
                            <div class="p-4">
                                <!-- Calendar Header -->
                                <div class="flex items-center justify-between mb-4">
                                    <button onclick="previousMonth()" class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
                                        <i class="fas fa-chevron-left text-gray-600"></i>
                                    </button>
                                    <div class="text-center">
                                        <h3 id="calendarMonthYear" class="text-lg font-medium text-gray-900">December 2024</h3>
                                        <p id="selectionStatus" class="text-xs text-gray-500 mt-1">Select start date</p>
                                    </div>
                                    <button onclick="nextMonth()" class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
                                        <i class="fas fa-chevron-right text-gray-600"></i>
                                    </button>
                                </div>
                                
                                <!-- Calendar Grid -->
                                <div class="grid grid-cols-7 gap-1 mb-2">
                                    <div class="text-center text-xs font-medium text-gray-500 py-2">Sun</div>
                                    <div class="text-center text-xs font-medium text-gray-500 py-2">Mon</div>
                                    <div class="text-center text-xs font-medium text-gray-500 py-2">Tue</div>
                                    <div class="text-center text-xs font-medium text-gray-500 py-2">Wed</div>
                                    <div class="text-center text-xs font-medium text-gray-500 py-2">Thu</div>
                                    <div class="text-center text-xs font-medium text-gray-500 py-2">Fri</div>
                                    <div class="text-center text-xs font-medium text-gray-500 py-2">Sat</div>
                                </div>
                                
                                <div id="calendarGrid" class="grid grid-cols-7 gap-1">
                                    <!-- Calendar days will be generated here -->
                                </div>
                                
                                <!-- Quick Presets -->
                                <div class="mt-4 pt-4 border-t border-gray-200">
                                    <div class="flex flex-wrap gap-2">
                                        <button onclick="setDateRange('thisMonth')" class="px-3 py-1 text-xs font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">
                                            This Month
                                        </button>
                                        <button onclick="setDateRange('lastMonth')" class="px-3 py-1 text-xs font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">
                                            Last Month
                                        </button>
                                        <button onclick="setDateRange('thisQuarter')" class="px-3 py-1 text-xs font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">
                                            This Quarter
                                        </button>
                                    </div>
                                </div>
                                
                                <!-- Action Buttons -->
                                <div class="mt-4 flex justify-end space-x-2">
                                    <button onclick="clearDateRange()" class="px-3 py-1 text-sm text-gray-600 hover:text-gray-800 transition-colors">
                                        Clear
                                    </button>
                                    <button onclick="applyDateRange()" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                                        Apply
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="flex items-end">
                    <button onclick="loadPayrollData()" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                        <i class="fas fa-search mr-2"></i>Load
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Additional Filters Form -->
        <form method="GET" action="<?php echo e(route('payroll.index')); ?>" class="mt-6 pt-6 border-t border-gray-200">
            <div class="flex flex-col sm:flex-row space-y-4 sm:space-y-0 sm:space-x-4">
                <!-- Department Filter -->
                <div class="flex-1">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Department</label>
                    <select name="department_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm text-gray-900 bg-white focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">All Departments</option>
                        <?php $__currentLoopData = $departments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $department): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($department->id); ?>" <?php echo e(request('department_id') == $department->id ? 'selected' : ''); ?>>
                                <?php echo e($department->name); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                
                <!-- Status Filter -->
                <div class="flex-1">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm text-gray-900 bg-white focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">All Status</option>
                        <option value="pending" <?php echo e(request('status') == 'pending' ? 'selected' : ''); ?>>Pending</option>
                        <option value="approved" <?php echo e(request('status') == 'approved' ? 'selected' : ''); ?>>Approved</option>
                        <option value="paid" <?php echo e(request('status') == 'paid' ? 'selected' : ''); ?>>Paid</option>
                    </select>
                </div>
                
                <!-- Employee Filter -->
                <div class="flex-1">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Employee</label>
                    <select name="employee_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm text-gray-900 bg-white focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">All Employees</option>
                        <?php $__currentLoopData = $employees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $employee): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($employee->id); ?>" <?php echo e(request('employee_id') == $employee->id ? 'selected' : ''); ?>>
                                <?php echo e($employee->full_name); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                
                <!-- Apply Filters Button -->
                <div class="self-end flex space-x-2">
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                        <i class="fas fa-filter mr-2"></i>Apply Filters
                    </button>
                    <a href="<?php echo e(route('payroll.index')); ?>" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                        <i class="fas fa-times mr-2"></i>Clear
                    </a>
                </div>
            </div>
            
            <!-- Date Range Inputs (hidden - populated by calendar) -->
            <input type="hidden" name="start_date" id="filterStartDate" value="<?php echo e(request('start_date', now()->startOfMonth()->format('Y-m-d'))); ?>">
            <input type="hidden" name="end_date" id="filterEndDate" value="<?php echo e(request('end_date', now()->endOfMonth()->format('Y-m-d'))); ?>">
        </form>
        
        <!-- Selected Period Display -->
        <div id="selectedPeriod" class="mt-4 p-3 bg-blue-50 border border-blue-200 rounded-lg">
            <div class="flex items-center">
                <i class="fas fa-calendar-check text-blue-600 mr-2"></i>
                <span class="text-sm font-medium text-blue-800">
                    Selected Period: <span id="periodDisplay"><?php echo e(date('M d, Y')); ?> - <?php echo e(date('M d, Y')); ?></span>
                </span>
            </div>
            <?php if(request()->anyFilled(['department_id', 'status', 'employee_id'])): ?>
            <div class="mt-2 flex flex-wrap gap-2">
                <?php if(request('department_id')): ?>
                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                    <i class="fas fa-building mr-1"></i>
                    Department: <?php echo e($departments->where('id', request('department_id'))->first()->name ?? 'N/A'); ?>

                </span>
                <?php endif; ?>
                <?php if(request('status')): ?>
                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                    <i class="fas fa-check-circle mr-1"></i>
                    Status: <?php echo e(ucfirst(request('status'))); ?>

                </span>
                <?php endif; ?>
                <?php if(request('employee_id')): ?>
                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                    <i class="fas fa-user mr-1"></i>
                    Employee: <?php echo e($employees->where('id', request('employee_id'))->first()->full_name ?? 'N/A'); ?>

                </span>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 sm:p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-users text-green-600"></i>
                    </div>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-gray-500">Total Employees</p>
                    <p class="text-lg font-semibold text-gray-900"><?php echo e(number_format($summary['total_employees'])); ?></p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 sm:p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-dollar-sign text-blue-600"></i>
                    </div>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-gray-500">Gross Pay</p>
                    <p class="text-lg font-semibold text-gray-900">₱<?php echo e(number_format($summary['gross_pay'], 2)); ?></p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 sm:p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-red-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-minus-circle text-red-600"></i>
                    </div>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-gray-500">Total Deductions</p>
                    <p class="text-lg font-semibold text-gray-900">₱<?php echo e(number_format($summary['total_deductions'], 2)); ?></p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 sm:p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-wallet text-purple-600"></i>
                    </div>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-gray-500">Net Pay</p>
                    <p class="text-lg font-semibold text-gray-900">₱<?php echo e(number_format($summary['net_pay'], 2)); ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Employee Rate Information -->
    <?php if($employees->count() > 0): ?>
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 sm:p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-medium text-gray-900">
                <i class="fas fa-calculator mr-2 text-blue-600"></i>
                Employee Daily & Hourly Rates
            </h3>
            <div class="flex items-center space-x-2">
                <span id="employeePaginationInfo" class="text-sm text-gray-500">
                    Showing 1-6 of <?php echo e($employees->count()); ?> employees
                </span>
            </div>
        </div>
        
        <!-- Employee Cards Container -->
        <div id="employeeCardsContainer" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <?php $__currentLoopData = $employees->take(6); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $employee): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="border border-gray-200 rounded-lg p-4 employee-card">
                <div class="flex items-center justify-between mb-2">
                    <h4 class="font-medium text-gray-900"><?php echo e($employee->full_name); ?></h4>
                    <span class="text-xs text-gray-500"><?php echo e($employee->employee_id); ?></span>
                </div>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Monthly Salary:</span>
                        <span class="font-medium">₱<?php echo e(number_format($employee->salary, 2)); ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Daily Rate:</span>
                        <span class="font-medium text-blue-600">₱<?php echo e(number_format($employee->daily_rate, 2)); ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Hourly Rate:</span>
                        <span class="font-medium text-green-600">₱<?php echo e(number_format($employee->hourly_rate, 2)); ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Overtime Rate:</span>
                        <span class="font-medium text-orange-600">₱<?php echo e(number_format($employee->overtime_rate, 2)); ?></span>
                    </div>
                </div>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
        
        <!-- Hidden data for JavaScript -->
        <script>
            // Make employees data globally available
            window.allEmployeesData = <?php echo json_encode($employees->map(function($employee) {
                return [
                    'id' => $employee->id,
                    'full_name' => $employee->full_name,
                    'employee_id' => $employee->employee_id,
                    'salary' => $employee->salary,
                    'daily_rate' => $employee->daily_rate,
                    'hourly_rate' => $employee->hourly_rate,
                    'overtime_rate' => $employee->overtime_rate
                ];
            })); ?>;
        </script>
        
        <!-- Pagination Controls -->
        <?php if($employees->count() > 6): ?>
        <div class="mt-6 flex items-center justify-between">
            <div class="flex items-center space-x-2">
                <button id="prevEmployeePage" 
                        onclick="changeEmployeePage(-1)" 
                        type="button"
                        class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 hover:text-gray-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                        disabled>
                    <i class="fas fa-chevron-left mr-1"></i>
                    Previous
                </button>
                <button id="nextEmployeePage" 
                        onclick="changeEmployeePage(1)" 
                        type="button"
                        class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 hover:text-gray-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                    Next
                    <i class="fas fa-chevron-right ml-1"></i>
                </button>
            </div>
            
            <div class="flex items-center space-x-2">
                <span class="text-sm text-gray-500">Page</span>
                <span id="currentEmployeePage" class="text-sm font-medium text-gray-900">1</span>
                <span class="text-sm text-gray-500">of</span>
                <span id="totalEmployeePages" class="text-sm font-medium text-gray-900"><?php echo e(ceil($employees->count() / 6)); ?></span>
            </div>
        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- Payroll Status Overview -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 sm:p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-medium text-gray-900">Payroll Status</h3>
            <div class="flex space-x-2">
                <button class="px-3 py-1 text-xs font-medium bg-yellow-100 text-yellow-800 rounded-full">
                    Pending Review
                </button>
                <button class="px-3 py-1 text-xs font-medium bg-green-100 text-green-800 rounded-full">
                    Ready for Payment
                </button>
            </div>
        </div>
        
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="text-center p-4 bg-gray-50 rounded-lg">
                <div class="text-2xl font-bold text-gray-900"><?php echo e($summary['pending_count']); ?></div>
                <div class="text-sm text-gray-600">Pending Review</div>
            </div>
            <div class="text-center p-4 bg-gray-50 rounded-lg">
                <div class="text-2xl font-bold text-gray-900"><?php echo e($summary['approved_count']); ?></div>
                <div class="text-sm text-gray-600">Approved</div>
            </div>
            <div class="text-center p-4 bg-gray-50 rounded-lg">
                <div class="text-2xl font-bold text-gray-900"><?php echo e($summary['paid_count']); ?></div>
                <div class="text-sm text-gray-600">Paid</div>
            </div>
        </div>
    </div>

<!-- Payroll List -->
<div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
    <div class="px-4 sm:px-6 py-4 border-b border-gray-200">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h3 class="text-lg font-medium text-gray-900">Employee Payroll</h3>
                <p class="mt-1 text-sm text-gray-600">Individual payroll records for the selected period</p>
            </div>
            <div class="mt-4 sm:mt-0 flex space-x-3">
                <div class="relative">
                    <button onclick="toggleFilterDropdown()" class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition-colors">
                        <i class="fas fa-filter mr-2"></i>
                        Filter
                        <i class="fas fa-chevron-down ml-2 text-xs"></i>
                    </button>
                    <div id="filterDropdown" class="hidden absolute right-0 mt-2 w-48 bg-white border border-gray-200 rounded-lg shadow-lg z-10">
                        <div class="p-2">
                            <label class="flex items-center px-2 py-1 hover:bg-gray-100 rounded cursor-pointer">
                                <input type="checkbox" class="rounded text-blue-600">
                                <span class="ml-2 text-sm">Pending</span>
                            </label>
                            <label class="flex items-center px-2 py-1 hover:bg-gray-100 rounded cursor-pointer">
                                <input type="checkbox" class="rounded text-blue-600">
                                <span class="ml-2 text-sm">Approved</span>
                            </label>
                            <label class="flex items-center px-2 py-1 hover:bg-gray-100 rounded cursor-pointer">
                                <input type="checkbox" class="rounded text-blue-600">
                                <span class="ml-2 text-sm">Paid</span>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="relative">
                    <button onclick="toggleSortDropdown()" class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition-colors">
                        <i class="fas fa-sort mr-2"></i>
                        Sort
                        <i class="fas fa-chevron-down ml-2 text-xs"></i>
                    </button>
                    <div id="sortDropdown" class="hidden absolute right-0 mt-2 w-48 bg-white border border-gray-200 rounded-lg shadow-lg z-10">
                        <div class="p-2">
                            <button class="w-full text-left px-2 py-1 hover:bg-gray-100 rounded text-sm">Name (A-Z)</button>
                            <button class="w-full text-left px-2 py-1 hover:bg-gray-100 rounded text-sm">Name (Z-A)</button>
                            <button class="w-full text-left px-2 py-1 hover:bg-gray-100 rounded text-sm">Net Pay (High-Low)</button>
                            <button class="w-full text-left px-2 py-1 hover:bg-gray-100 rounded text-sm">Net Pay (Low-High)</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Desktop Table -->
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead>
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider bg-gray-50">
                        EMPLOYEE
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider bg-gray-50">
                        DEPARTMENT
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider bg-gray-50">
                        BASIC SALARY
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider bg-gray-50">
                        OVERTIME
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider bg-gray-50">
                        ALLOWANCES
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider bg-gray-50">
                        DEDUCTIONS
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider bg-gray-50">
                        NET PAY
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider bg-gray-50">
                        STATUS
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider bg-gray-50">
                        ACTIONS
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php $__empty_1 = true; $__currentLoopData = $payrolls; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $payroll): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <?php
                        $initials = strtoupper(substr($payroll->employee->first_name, 0, 1) . substr($payroll->employee->last_name, 0, 1));
                        $statusColors = [
                            'pending' => 'bg-yellow-100 text-yellow-800',
                            'approved' => 'bg-green-100 text-green-800',
                            'paid' => 'bg-blue-100 text-blue-800',
                            'rejected' => 'bg-red-100 text-red-800'
                        ];
                        $statusColor = $statusColors[$payroll->status] ?? 'bg-gray-100 text-gray-800';
                    ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                <div class="h-8 w-8 flex-shrink-0">
                                    <div class="h-8 w-8 rounded-full bg-gradient-to-r from-blue-500 to-blue-600 flex items-center justify-center">
                                        <span class="text-xs font-semibold text-white"><?php echo e($initials); ?></span>
                                    </div>
                                </div>
                                <div class="ml-3">
                                    <div class="text-sm font-medium text-gray-900"><?php echo e($payroll->employee->full_name); ?></div>
                                    <div class="text-xs text-gray-500"><?php echo e($payroll->employee->employee_id); ?></div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="text-sm text-gray-900"><?php echo e($payroll->employee->department->name ?? 'N/A'); ?></span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="text-sm font-medium text-gray-900">₱<?php echo e(number_format($payroll->basic_salary, 2)); ?></span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="text-sm font-medium text-gray-900">₱<?php echo e(number_format($payroll->overtime_pay, 2)); ?></span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="text-sm font-medium text-gray-900">₱<?php echo e(number_format($payroll->allowances, 2)); ?></span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="text-sm font-medium text-gray-900">₱<?php echo e(number_format($payroll->deductions, 2)); ?></span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="text-sm font-bold text-gray-900">₱<?php echo e(number_format($payroll->net_pay, 2)); ?></span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo e($statusColor); ?>">
                                <div class="w-1.5 h-1.5 rounded-full mr-1.5 <?php echo e(str_replace('text-', 'bg-', $statusColor)); ?>"></div>
                                <?php echo e(ucfirst($payroll->status)); ?>

                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex items-center space-x-2">
                                <button onclick="openPayrollModal('<?php echo e($payroll->id); ?>')" 
                                        class="text-blue-600 hover:text-blue-900 p-1 rounded hover:bg-blue-50" 
                                        title="View Details">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <?php if($payroll->status === 'pending'): ?>
                                    <button class="text-green-600 hover:text-green-900 p-1 rounded hover:bg-green-50" title="Approve">
                                        <i class="fas fa-check"></i>
                                    </button>
                                    <button class="text-red-600 hover:text-red-900 p-1 rounded hover:bg-red-50" title="Reject">
                                        <i class="fas fa-times"></i>
                                    </button>
                                <?php elseif($payroll->status === 'approved'): ?>
                                    <button class="text-purple-600 hover:text-purple-900 p-1 rounded hover:bg-purple-50" title="Pay">
                                        <i class="fas fa-credit-card"></i>
                                    </button>
                                <?php elseif($payroll->status === 'paid'): ?>
                                    <button class="text-gray-600 hover:text-gray-900 p-1 rounded hover:bg-gray-100" title="Print Payslip">
                                        <i class="fas fa-print"></i>
                                    </button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="9" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center justify-center">
                                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                                    <i class="fas fa-file-invoice text-2xl text-gray-400"></i>
                                </div>
                                <h3 class="text-lg font-medium text-gray-900 mb-2">No payroll records found</h3>
                                <p class="text-gray-500">Generate payroll for the selected period to view records.</p>
                            </div>
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
                <?php echo e($payrolls->appends(request()->query())->links('pagination::simple-tailwind')); ?>

            </div>
            <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm text-gray-700">
                        Showing
                        <span class="font-medium"><?php echo e($payrolls->firstItem()); ?></span>
                        to
                        <span class="font-medium"><?php echo e($payrolls->lastItem()); ?></span>
                        of
                        <span class="font-medium"><?php echo e($payrolls->total()); ?></span>
                        results
                    </p>
                </div>
                <div>
                    <?php echo e($payrolls->appends(request()->query())->links('pagination::tailwind')); ?>

                </div>
            </div>
        </div>
    </div>
</div>

<!-- Mobile Cards - Keep this section as is -->
<div class="lg:hidden">
    <div class="p-4 space-y-4">
        <?php $__empty_1 = true; $__currentLoopData = $payrolls; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $payroll): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <?php
                $initials = strtoupper(substr($payroll->employee->first_name, 0, 1) . substr($payroll->employee->last_name, 0, 1));
                $statusColors = [
                    'pending' => 'bg-yellow-100 text-yellow-800',
                    'approved' => 'bg-green-100 text-green-800',
                    'paid' => 'bg-blue-100 text-blue-800',
                    'rejected' => 'bg-red-100 text-red-800'
                ];
                $statusColor = $statusColors[$payroll->status] ?? 'bg-gray-100 text-gray-800';
            ?>
            <div class="border border-gray-200 rounded-lg p-4">
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center space-x-3">
                        <div class="h-10 w-10 rounded-full bg-gradient-to-r from-blue-500 to-blue-600 flex items-center justify-center">
                            <span class="text-sm font-medium text-white"><?php echo e($initials); ?></span>
                        </div>
                        <div>
                            <div class="font-medium text-gray-900"><?php echo e($payroll->employee->full_name); ?></div>
                            <div class="text-sm text-gray-500"><?php echo e($payroll->employee->department->name ?? 'N/A'); ?></div>
                        </div>
                    </div>
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium <?php echo e($statusColor); ?>">
                        <div class="w-1.5 h-1.5 rounded-full mr-1 <?php echo e(str_replace('text-', 'bg-', $statusColor)); ?>"></div>
                        <?php echo e(ucfirst($payroll->status)); ?>

                    </span>
                </div>
                <div class="grid grid-cols-2 gap-4 text-sm mb-3">
                    <div>
                        <div class="text-gray-500">Basic Salary</div>
                        <div class="font-medium">₱<?php echo e(number_format($payroll->basic_salary, 2)); ?></div>
                    </div>
                    <div>
                        <div class="text-gray-500">Net Pay</div>
                        <div class="font-medium">₱<?php echo e(number_format($payroll->net_pay, 2)); ?></div>
                    </div>
                    <div>
                        <div class="text-gray-500">Overtime</div>
                        <div class="font-medium">₱<?php echo e(number_format($payroll->overtime_pay, 2)); ?></div>
                    </div>
                    <div>
                        <div class="text-gray-500">Deductions</div>
                        <div class="font-medium">₱<?php echo e(number_format($payroll->deductions, 2)); ?></div>
                    </div>
                </div>
                <div class="flex justify-end space-x-2">
                    <button onclick="openPayrollModal('<?php echo e($payroll->id); ?>')" class="text-blue-600 hover:text-blue-900 transition-colors">
                        <i class="fas fa-eye mr-1"></i>View
                    </button>
                    <?php if($payroll->status === 'pending'): ?>
                        <button class="text-green-600 hover:text-green-900 transition-colors">
                            <i class="fas fa-check mr-1"></i>Approve
                        </button>
                    <?php elseif($payroll->status === 'approved'): ?>
                        <button class="text-purple-600 hover:text-purple-900 transition-colors">
                            <i class="fas fa-credit-card mr-1"></i>Pay
                        </button>
                    <?php elseif($payroll->status === 'paid'): ?>
                        <button class="text-gray-600 hover:text-gray-900 transition-colors">
                            <i class="fas fa-print mr-1"></i>Print
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <div class="text-center text-gray-500 py-8">
                <i class="fas fa-file-invoice-dollar text-3xl mb-3 opacity-50"></i>
                <p>No payroll records found</p>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Mobile Pagination -->
    <div class="px-4 py-4 border-t border-gray-200">
        <div class="flex items-center justify-between">
            <div class="flex-1 flex justify-between">
                <?php echo e($payrolls->appends(request()->query())->links('pagination::simple-tailwind')); ?>

            </div>
        </div>
    </div>
</div>

        <!-- Mobile Cards -->
        <div class="lg:hidden">
            <div class="p-4 space-y-4">
                <?php $__empty_1 = true; $__currentLoopData = $payrolls; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $payroll): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <?php
                        $initials = strtoupper(substr($payroll->employee->first_name, 0, 1) . substr($payroll->employee->last_name, 0, 1));
                        $statusColors = [
                            'pending' => 'bg-yellow-100 text-yellow-800',
                            'approved' => 'bg-green-100 text-green-800',
                            'paid' => 'bg-blue-100 text-blue-800',
                            'rejected' => 'bg-red-100 text-red-800'
                        ];
                        $statusColor = $statusColors[$payroll->status] ?? 'bg-gray-100 text-gray-800';
                    ?>
                    <div class="border border-gray-200 rounded-lg p-4">
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center space-x-3">
                                <div class="h-10 w-10 rounded-full bg-gradient-to-r from-blue-500 to-blue-600 flex items-center justify-center">
                                    <span class="text-sm font-medium text-white"><?php echo e($initials); ?></span>
                                </div>
                                <div>
                                    <div class="font-medium text-gray-900"><?php echo e($payroll->employee->full_name); ?></div>
                                    <div class="text-sm text-gray-500"><?php echo e($payroll->employee->department->name ?? 'N/A'); ?></div>
                                </div>
                            </div>
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium <?php echo e($statusColor); ?>">
                                <div class="w-1.5 h-1.5 rounded-full mr-1 <?php echo e(str_replace('text-', 'bg-', $statusColor)); ?>"></div>
                                <?php echo e(ucfirst($payroll->status)); ?>

                            </span>
                        </div>
                        <div class="grid grid-cols-2 gap-4 text-sm mb-3">
                            <div>
                                <div class="text-gray-500">Basic Salary</div>
                                <div class="font-medium">₱<?php echo e(number_format($payroll->basic_salary, 2)); ?></div>
                            </div>
                            <div>
                                <div class="text-gray-500">Net Pay</div>
                                <div class="font-medium">₱<?php echo e(number_format($payroll->net_pay, 2)); ?></div>
                            </div>
                            <div>
                                <div class="text-gray-500">Overtime</div>
                                <div class="font-medium">₱<?php echo e(number_format($payroll->overtime_pay, 2)); ?></div>
                            </div>
                            <div>
                                <div class="text-gray-500">Deductions</div>
                                <div class="font-medium">₱<?php echo e(number_format($payroll->deductions, 2)); ?></div>
                            </div>
                        </div>
                        <div class="flex justify-end space-x-2">
                            <button onclick="openPayrollModal('<?php echo e($payroll->id); ?>')" class="text-blue-600 hover:text-blue-900 transition-colors">
                                <i class="fas fa-eye mr-1"></i>View
                            </button>
                            <?php if($payroll->status === 'pending'): ?>
                                <button class="text-green-600 hover:text-green-900 transition-colors">
                                    <i class="fas fa-check mr-1"></i>Approve
                                </button>
                            <?php elseif($payroll->status === 'approved'): ?>
                                <button class="text-purple-600 hover:text-purple-900 transition-colors">
                                    <i class="fas fa-credit-card mr-1"></i>Pay
                                </button>
                            <?php elseif($payroll->status === 'paid'): ?>
                                <button class="text-gray-600 hover:text-gray-900 transition-colors">
                                    <i class="fas fa-print mr-1"></i>Print
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="text-center text-gray-500 py-8">
                        No payroll records found
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Mobile Pagination -->
            <div class="px-4 py-4 border-t border-gray-200">
                <div class="flex items-center justify-between">
                    <div class="flex-1 flex justify-between">
                        <?php echo e($payrolls->appends(request()->query())->links('pagination::simple-tailwind')); ?>

                    </div>
                </div>
            </div>
        </div>
    </div>

<!-- Payroll Actions -->
<div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 sm:p-6">
    <h3 class="text-lg font-medium text-gray-900 mb-4">Bulk Actions</h3>
    
    <div class="flex flex-col sm:flex-row flex-wrap gap-3">
        <!-- Complete Workflow Button -->
        <form action="<?php echo e(route('payrolls.complete-workflow')); ?>" method="POST" class="inline">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="start_date" id="workflowStartDate" value="<?php echo e(old('start_date', request('start_date', date('Y-m-d')))); ?>">
            <input type="hidden" name="end_date" id="workflowEndDate" value="<?php echo e(old('end_date', request('end_date', date('Y-m-d')))); ?>">
            <button type="submit" 
                    onclick="return confirm('This will generate, approve, and process payments for all payrolls in the selected period. Continue?')"
                    class="inline-flex items-center justify-center px-4 py-2 border border-transparent rounded-lg font-medium text-white bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 transition-colors">
                <i class="fas fa-bolt mr-2"></i>
                Complete Workflow (Generate → Approve → Pay)
            </button>
        </form>

        <!-- Approve All Pending -->
        <div class="inline">
            <!-- Add these hidden inputs for the Approve All Pending button -->
            <input type="hidden" name="bulk_start_date" id="bulkStartDate" value="<?php echo e(old('start_date', request('start_date', date('Y-m-d')))); ?>">
            <input type="hidden" name="bulk_end_date" id="bulkEndDate" value="<?php echo e(old('end_date', request('end_date', date('Y-m-d')))); ?>">
            
            <button type="button" 
                    onclick="approveAllPendingWithConfirmation()"
                    id="approveAllPendingBtn"
                    class="inline-flex items-center justify-center px-4 py-2 border border-transparent rounded-lg font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors">
                <i class="fas fa-check-double mr-2"></i>
                Approve All Pending
            </button>
        </div>

        <!-- Process Payments -->
        <form action="<?php echo e(route('payrolls.process-payments')); ?>" method="POST" class="inline" id="processPaymentsForm">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="start_date" id="paymentStartDate" value="<?php echo e(old('start_date', request('start_date', date('Y-m-d')))); ?>">
            <input type="hidden" name="end_date" id="paymentEndDate" value="<?php echo e(old('end_date', request('end_date', date('Y-m-d')))); ?>">
            <input type="hidden" name="payroll_ids" id="payrollIds" value="">
            
            <button type="button" 
                    onclick="processPayments()"
                    id="processPaymentsBtn"
                    class="inline-flex items-center justify-center px-4 py-2 border border-transparent rounded-lg font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                <i class="fas fa-credit-card mr-2"></i>
                Process Payments
            </button>
        </form>

        <!-- Generate Payslips - Only for admin/hr/manager -->
        <?php if(!in_array(auth()->user()->role ?? '', ['employee'])): ?>
        <div class="flex items-center space-x-2">
            <!-- Generate Payslips Button -->
            <form action="<?php echo e(route('payrolls.generate-payslips')); ?>" method="POST" class="inline" id="payslipForm">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="start_date" id="payslipStartDate" value="<?php echo e(old('start_date', request('start_date', date('Y-m-d')))); ?>">
                <input type="hidden" name="end_date" id="payslipEndDate" value="<?php echo e(old('end_date', request('end_date', date('Y-m-d')))); ?>">
                <button type="submit" 
                        class="inline-flex items-center justify-center px-4 py-2 border border-gray-300 rounded-lg font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                    <i class="fas fa-file-pdf mr-2"></i>
                    Generate Payslips
                </button>
            </form>

            <!-- Download All Payslips Button (if there are generated payslips) - Only for admin/hr/manager -->
            <?php if(isset($payslip_results) && count($payslip_results) > 0 && !in_array(auth()->user()->role ?? '', ['employee'])): ?>
            <a href="<?php echo e(route('payrolls.download-all-payslips', ['start_date' => request('start_date', date('Y-m-d')), 'end_date' => request('end_date', date('Y-m-d'))])); ?>"
            class="inline-flex items-center justify-center px-4 py-2 border border-transparent rounded-lg font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors">
                <i class="fas fa-download mr-2"></i>
                Download All Payslips
            </a>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- Export Payroll - Only for admin/hr/manager -->
        <?php if(!in_array(auth()->user()->role ?? '', ['employee'])): ?>
        <div class="flex items-center space-x-2">
        <!-- Export with Calculations Button -->
        <button type="button" onclick="exportPayrollWithCalculations()" 
                class="inline-flex items-center justify-center px-4 py-2 border border-gray-300 rounded-lg font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
            <i class="fas fa-file-excel mr-2"></i>
            Export with Calculations
        </button>

            <!-- Export Payroll Dropdown -->
            <div class="relative inline-block" id="exportDropdownContainer">
                <form action="<?php echo e(route('payrolls.export-payroll')); ?>" method="POST" class="inline" id="exportForm" onsubmit="return false;">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="start_date" id="exportStartDate" value="<?php echo e(old('start_date', request('start_date', date('Y-m-d')))); ?>">
            <input type="hidden" name="end_date" id="exportEndDate" value="<?php echo e(old('end_date', request('end_date', date('Y-m-d')))); ?>">
                    <input type="hidden" name="format" id="exportFormat" value="pdf">
                    <button type="button" 
                            id="exportPayrollButton"
                            onclick="event.preventDefault(); event.stopPropagation(); toggleExportDropdownDirect(event); return false;"
                        class="inline-flex items-center justify-center px-4 py-2 border border-gray-300 rounded-lg font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                    <i class="fas fa-download mr-2"></i>
                        Export Payroll
                        <i class="fas fa-chevron-down ml-2 text-xs"></i>
                </button>
                </form>
                <!-- Export Format Dropdown -->
                <div id="exportDropdown" style="display: none !important;" class="absolute right-0 top-full mt-2 bg-white border-2 border-gray-300 rounded-lg shadow-2xl z-[9999] min-w-[200px] overflow-hidden">
                    <div class="py-2">
                        <button type="button" onclick="exportPayroll('pdf'); return false;" class="w-full text-left px-4 py-3 text-sm font-medium text-gray-700 hover:bg-blue-50 hover:text-blue-700 flex items-center transition-colors border-b border-gray-100">
                            <i class="fas fa-file-pdf mr-3 text-red-600 text-lg"></i>
                            <span>Export to PDF</span>
                    </button>
                        <button type="button" onclick="exportPayroll('csv'); return false;" class="w-full text-left px-4 py-3 text-sm font-medium text-gray-700 hover:bg-green-50 hover:text-green-700 flex items-center transition-colors border-b border-gray-100">
                            <i class="fas fa-file-csv mr-3 text-green-600 text-lg"></i>
                            <span>Export to CSV</span>
                        </button>
                        <button type="button" onclick="exportPayroll('xlsx'); return false;" class="w-full text-left px-4 py-3 text-sm font-medium text-gray-700 hover:bg-blue-50 hover:text-blue-700 flex items-center transition-colors">
                            <i class="fas fa-file-excel mr-3 text-blue-600 text-lg"></i>
                            <span>Export to Excel</span>
                    </button>
                </div>
            </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Mark as Paid (Individual) -->
    <div class="mt-4">
        <button onclick="markSelectedAsPaid()" 
                class="inline-flex items-center justify-center px-4 py-2 border border-transparent rounded-lg font-medium text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 transition-colors">
            <i class="fas fa-money-check-alt mr-2"></i>
            Mark Selected as Paid
        </button>
    </div>

    <!-- Checkbox for selecting payrolls -->
    <div class="mt-4 p-3 bg-blue-50 border border-blue-200 rounded-lg">
        <label class="flex items-center">
            <input type="checkbox" id="selectAllPayrolls" class="rounded text-blue-600 mr-2">
            <span class="text-sm font-medium text-blue-800">Select all payroll records for bulk processing</span>
        </label>
        <p class="text-xs text-blue-600 mt-1 ml-6">Selected records will be processed when using bulk actions</p>
    </div>
</div>

<script>
// Function to export with calculations
async function exportPayrollWithCalculations() {
    const startDate = document.getElementById('exportStartDate').value;
    const endDate = document.getElementById('exportEndDate').value;
    
    if (!startDate || !endDate) {
        alert('Please select a date range first.');
        return;
    }
    
    // Get selected payroll IDs
    const selectedCheckboxes = document.querySelectorAll('.payroll-checkbox:checked');
    const selectedIds = Array.from(selectedCheckboxes).map(cb => cb.value);
    
    // Show loading
    const originalText = event.target.innerHTML;
    event.target.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Exporting...';
    event.target.disabled = true;
    
    try {
        const response = await fetch('/payrolls/export-detailed', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                start_date: startDate,
                end_date: endDate,
                payroll_ids: selectedIds.length > 0 ? selectedIds : null,
                format: 'xlsx'
            })
        });
        
        if (response.ok) {
            const blob = await response.blob();
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `payroll_export_with_calculations_${startDate}_to_${endDate}.xlsx`;
            document.body.appendChild(a);
            a.click();
            window.URL.revokeObjectURL(url);
            document.body.removeChild(a);
        } else {
            const errorText = await response.text();
            console.error('Export error response:', errorText);
            alert('Error exporting payroll data. Please check console for details.');
        }
    } catch (error) {
        console.error('Export error:', error);
        alert('Error exporting payroll data: ' + error.message);
    } finally {
        // Reset button
        event.target.innerHTML = originalText;
        event.target.disabled = false;
    }
}
</script>

<!-- Payroll Details Modal -->
<div id="payrollModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">Payroll Details</h3>
                <button onclick="closePayrollModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <div class="space-y-4">
                <!-- Employee Info -->
                <div class="bg-gray-50 p-4 rounded-lg">
                    <h4 class="font-medium text-gray-900 mb-2">Employee Information</h4>
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <span class="text-gray-500">Name:</span>
                            <span class="ml-2 font-medium">John Smith</span>
                        </div>
                        <div>
                            <span class="text-gray-500">Employee ID:</span>
                            <span class="ml-2 font-medium">EMP-001</span>
                        </div>
                        <div>
                            <span class="text-gray-500">Department:</span>
                            <span class="ml-2 font-medium">IT Department</span>
                        </div>
                        <div>
                            <span class="text-gray-500">Position:</span>
                            <span class="ml-2 font-medium">Software Developer</span>
                        </div>
                    </div>
                </div>

                <!-- Earnings -->
                <div class="bg-green-50 p-4 rounded-lg">
                    <h4 class="font-medium text-gray-900 mb-2">Earnings</h4>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span>Basic Salary</span>
                            <span class="font-medium">₱25,000.00</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Overtime Pay</span>
                            <span class="font-medium">₱3,500.00</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Allowances</span>
                            <span class="font-medium">₱2,000.00</span>
                        </div>
                        <div class="flex justify-between border-t pt-2 font-medium">
                            <span>Total Earnings</span>
                            <span>₱30,500.00</span>
                        </div>
                    </div>
                </div>

                <!-- Deductions -->
                <div class="bg-red-50 p-4 rounded-lg">
                    <h4 class="font-medium text-gray-900 mb-2">Deductions</h4>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span>SSS Contribution</span>
                            <span class="font-medium">₱1,200.00</span>
                        </div>
                        <div class="flex justify-between">
                            <span>PhilHealth</span>
                            <span class="font-medium">₱800.00</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Pag-IBIG</span>
                            <span class="font-medium">₱200.00</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Withholding Tax</span>
                            <span class="font-medium">₱2,000.00</span>
                        </div>
                        <div class="flex justify-between border-t pt-2 font-medium">
                            <span>Total Deductions</span>
                            <span>₱4,200.00</span>
                        </div>
                    </div>
                </div>

                <!-- Net Pay -->
                <div class="bg-blue-50 p-4 rounded-lg">
                    <div class="flex justify-between items-center">
                        <span class="text-lg font-medium text-gray-900">Net Pay</span>
                        <span class="text-2xl font-bold text-blue-600">₱26,300.00</span>
                    </div>
                </div>
            </div>

            <div class="flex justify-end space-x-3 mt-6">
                <button onclick="closePayrollModal()" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
                    Close
                </button>
                <a id="downloadPayslipLink" href="#" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors inline-flex items-center">
                    <i class="fas fa-download mr-2"></i>Download Payslip
                </a>

                <!-- Payment Modal -->
<div id="paymentModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 xl:w-1/3 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">Process Payments</h3>
                <button onclick="closePaymentModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <div class="space-y-4">
                <!-- Date Range Display -->
                <div class="bg-blue-50 p-3 rounded-lg">
                    <div class="flex items-center">
                        <i class="fas fa-calendar-alt text-blue-600 mr-2"></i>
                        <span class="text-sm font-medium text-blue-800">
                            Period: <span id="paymentPeriodDisplay"><?php echo e(date('M d, Y')); ?> - <?php echo e(date('M d, Y')); ?></span>
                        </span>
                    </div>
                </div>
                
                <!-- Payment Method -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Payment Method</label>
                    <select id="paymentMethod" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-gray-900 bg-white focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="bank_transfer">Bank Transfer</option>
                        <option value="cash">Cash</option>
                        <option value="check">Check</option>
                        <option value="online">Online Payment</option>
                    </select>
                </div>
                
                <!-- Employee Selection -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Select Employees</label>
                    <div class="border border-gray-300 rounded-lg max-h-60 overflow-y-auto">
                        <div class="p-2 border-b">
                            <div class="flex items-center">
                                <input type="checkbox" id="selectAllEmployees" onchange="toggleAllEmployees()" class="h-4 w-4 text-blue-600 border-gray-300 rounded">
                                <label for="selectAllEmployees" class="ml-2 text-sm font-medium text-gray-700">Select All Employees</label>
                            </div>
                        </div>
                        <div id="employeeList" class="p-2 space-y-2">
                            <div class="text-center py-4 text-gray-500">
                                <i class="fas fa-users text-gray-400 mb-2"></i>
                                <p>Select a date range first, then employees will appear here</p>
                            </div>
                        </div>
                    </div>
                    <p class="mt-1 text-xs text-gray-500" id="selectedCount">0 employees selected</p>
                </div>
                
                <!-- Total Amount -->
                <div class="bg-gray-50 p-3 rounded-lg">
                    <div class="flex justify-between items-center">
                        <span class="text-sm font-medium text-gray-900">Total Amount to Process:</span>
                        <span id="totalAmount" class="text-lg font-bold text-green-600">₱0.00</span>
                    </div>
                </div>
                
                <!-- Notes -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Notes (Optional)</label>
                    <textarea id="paymentNotes" rows="2" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Add any notes about this payment..."></textarea>
                </div>
            </div>

            <div class="flex justify-end space-x-3 mt-6">
                <button onclick="closePaymentModal()" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
                    Cancel
                </button>
                <button onclick="processSelectedPayments()" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    <i class="fas fa-credit-card mr-2"></i>Process Payments
                </button>
            </div>
        </div>
    </div>
</div>
            </div>
        </div>
    </div>
</div>
<script>
// Modal Functions
function openPayrollModal(payrollId) {
    document.getElementById('payrollModal').classList.remove('hidden');
    
    // Set the download payslip link
    const downloadLink = document.getElementById('downloadPayslipLink');
    if (downloadLink && payrollId) {
        downloadLink.href = '<?php echo e(route("payrolls.download-payslip", ":id")); ?>'.replace(':id', payrollId);
    }
}

function closePayrollModal() {
    document.getElementById('payrollModal').classList.add('hidden');
}

// Close modal when clicking outside
document.getElementById('payrollModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closePayrollModal();
    }
});

// Add these JavaScript functions

// Select/Deselect all checkboxes
document.getElementById('selectAllEmployees').addEventListener('change', function(e) {
    const checkboxes = document.querySelectorAll('.payroll-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = e.target.checked;
    });
});

// Process payments for selected employees
async function processSelectedPayments() {
    const selectedCheckboxes = document.querySelectorAll('.payroll-checkbox:checked');
    const selectedIds = Array.from(selectedCheckboxes).map(cb => cb.value);
    
    if (selectedIds.length === 0) {
        alert('Please select at least one payroll to process payment.');
        return;
    }
    
    const startDate = document.getElementById('paymentStartDate').value;
    const endDate = document.getElementById('paymentEndDate').value;
    
    if (!startDate || !endDate) {
        alert('Please select a date range first.');
        return;
    }
    
    if (confirm(`Process payments for ${selectedIds.length} selected payroll(s)?`)) {
        try {
            const response = await fetch('/payrolls/process-selected-payments', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    payroll_ids: selectedIds,
                    start_date: startDate,
                    end_date: endDate
                })
            });
            
            const result = await response.json();
            
            if (result.success) {
                alert(`Successfully processed ${result.processed} payments!`);
                location.reload(); // Refresh to show updated status
            } else {
                alert('Error: ' + result.message);
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Error processing payments. Please try again.');
        }
    }
}

// Approve selected payrolls
async function approveSelectedPayrolls() {
    const selectedCheckboxes = document.querySelectorAll('.payroll-checkbox:checked');
    const selectedIds = Array.from(selectedCheckboxes).map(cb => cb.value);
    
    if (selectedIds.length === 0) {
        alert('Please select at least one payroll to approve.');
        return;
    }
    
    const startDate = document.getElementById('bulkStartDate').value;
    const endDate = document.getElementById('bulkEndDate').value;
    
    if (!startDate || !endDate) {
        alert('Please select a date range first.');
        return;
    }
    
    if (confirm(`Approve ${selectedIds.length} selected payroll(s)?`)) {
        try {
            const response = await fetch('/payrolls/approve-selected', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    payroll_ids: selectedIds,
                    start_date: startDate,
                    end_date: endDate
                })
            });
            
            const result = await response.json();
            
            if (result.success) {
                alert(`Successfully approved ${result.approved_count} payroll(s)!`);
                location.reload(); // Refresh to show updated status
            } else {
                alert('Error: ' + result.message);
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Error approving payrolls. Please try again.');
        }
    }
}

// Mark selected as paid
async function markSelectedAsPaid() {
    const selectedCheckboxes = document.querySelectorAll('.payroll-checkbox:checked');
    const selectedIds = Array.from(selectedCheckboxes).map(cb => cb.value);
    
    if (selectedIds.length === 0) {
        alert('Please select at least one payroll to mark as paid.');
        return;
    }
    
    if (confirm(`Mark ${selectedIds.length} selected payroll(s) as paid?`)) {
        try {
            const response = await fetch('/payrolls/mark-as-paid', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    payroll_ids: selectedIds
                })
            });
            
            const result = await response.json();
            
            if (result.success) {
                alert(`Successfully marked ${result.marked_count} payroll(s) as paid!`);
                location.reload(); // Refresh to show updated status
            } else {
                alert('Error: ' + result.message);
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Error marking as paid. Please try again.');
        }
    }
}

// Single Calendar Date Range Picker
let currentDate = new Date();
let selectedFromDate = null;
let selectedToDate = null;
let isSelectingFrom = true;

function toggleCalendar() {
    const popup = document.getElementById('calendarPopup');
    popup.classList.toggle('hidden');
    
    if (!popup.classList.contains('hidden')) {
        generateCalendar();
    }
}

function generateCalendar() {
    const year = currentDate.getFullYear();
    const month = currentDate.getMonth();
    
    // Check if calendar is visible before updating elements
    const calendarPopup = document.getElementById('calendarPopup');
    if (calendarPopup && calendarPopup.classList.contains('hidden')) {
        return; // Don't update if calendar is not visible
    }
    
    // Update month/year display
    const monthYearElement = document.getElementById('calendarMonthYear');
    if (monthYearElement) {
        monthYearElement.textContent = 
            currentDate.toLocaleDateString('en-US', { month: 'long', year: 'numeric' });
    }
    
    // Get first day of month and number of days
    const firstDay = new Date(year, month, 1);
    const lastDay = new Date(year, month + 1, 0);
    const startDate = new Date(firstDay);
    startDate.setDate(startDate.getDate() - firstDay.getDay());
    
    const calendarGrid = document.getElementById('calendarGrid');
    if (!calendarGrid) {
        return; // Exit if calendar grid doesn't exist
    }
    calendarGrid.innerHTML = '';
    
    // Generate 42 days (6 weeks)
    for (let i = 0; i < 42; i++) {
        const date = new Date(startDate);
        date.setDate(startDate.getDate() + i);
        
        const dayElement = document.createElement('div');
        dayElement.className = 'text-center py-2 cursor-pointer hover:bg-gray-100 rounded-lg transition-colors';
        dayElement.textContent = date.getDate();
        
        // Add classes based on date state
        if (date.getMonth() !== month) {
            dayElement.classList.add('text-gray-400');
        } else {
            dayElement.classList.add('text-gray-900');
        }
        
        // Highlight selected dates
        if (selectedFromDate && isSameDate(date, selectedFromDate)) {
            dayElement.classList.add('bg-blue-500', 'text-white', 'font-medium');
        } else if (selectedToDate && isSameDate(date, selectedToDate)) {
            dayElement.classList.add('bg-blue-500', 'text-white', 'font-medium');
        } else if (selectedFromDate && selectedToDate && 
                   date >= selectedFromDate && date <= selectedToDate) {
            dayElement.classList.add('bg-blue-100', 'text-blue-800');
        } else if (selectedFromDate && !selectedToDate && date >= selectedFromDate) {
            // Highlight potential "to" dates when only "from" is selected
            dayElement.classList.add('bg-blue-50', 'text-blue-600', 'font-medium');
        }
        
        // Add click handler
        dayElement.addEventListener('click', () => selectDate(date));
        
        calendarGrid.appendChild(dayElement);
    }
}

function selectDate(date) {
    if (isSelectingFrom || !selectedFromDate) {
        // First date selection (from)
        selectedFromDate = new Date(date);
        selectedToDate = null;
        isSelectingFrom = false;
        
        // Update display to show we're now selecting "to" date
        updateDateRangeDisplay();
        updatePeriodDisplay();
        generateCalendar();
    } else {
        // Second date selection (to)
        if (date < selectedFromDate) {
            selectedToDate = selectedFromDate;
            selectedFromDate = new Date(date);
        } else {
            selectedToDate = new Date(date);
        }
        isSelectingFrom = true;
        
        // Update display and keep calendar open
        updateDateRangeDisplay();
        updatePeriodDisplay();
        generateCalendar();
        
        // Don't auto-close the calendar, let user manually apply or continue selecting
    }
}

function isSameDate(date1, date2) {
    return date1.getFullYear() === date2.getFullYear() &&
           date1.getMonth() === date2.getMonth() &&
           date1.getDate() === date2.getDate();
}

function previousMonth() {
    currentDate.setMonth(currentDate.getMonth() - 1);
    generateCalendar();
}

function nextMonth() {
    currentDate.setMonth(currentDate.getMonth() + 1);
    generateCalendar();
}

function setDateRange(preset) {
    const today = new Date();
    
    switch(preset) {
        case 'thisMonth':
            selectedFromDate = new Date(today.getFullYear(), today.getMonth(), 1);
            selectedToDate = new Date(today.getFullYear(), today.getMonth() + 1, 0);
            break;
            
        case 'lastMonth':
            selectedFromDate = new Date(today.getFullYear(), today.getMonth() - 1, 1);
            selectedToDate = new Date(today.getFullYear(), today.getMonth(), 0);
            break;
            
        case 'thisQuarter':
            const quarter = Math.floor(today.getMonth() / 3);
            selectedFromDate = new Date(today.getFullYear(), quarter * 3, 1);
            selectedToDate = new Date(today.getFullYear(), quarter * 3 + 3, 0);
            break;
    }
    
    updateDateRangeDisplay();
    updatePeriodDisplay();
    updateAllDateFormFields(); // Using the updated function from second block
    generateCalendar();
}

function clearDateRange() {
    selectedFromDate = null;
    selectedToDate = null;
    isSelectingFrom = true;
    
    updateDateRangeDisplay();
    updatePeriodDisplay();
    
    // Clear all form fields
    const fields = [
        'bulkStartDate', 'bulkEndDate',
        'paymentStartDate', 'paymentEndDate',
        'payslipStartDate', 'payslipEndDate',
        'exportStartDate', 'exportEndDate'
    ];
    
    fields.forEach(fieldId => {
        const field = document.getElementById(fieldId);
        if (field) field.value = '';
    });
    
    // Clear debug info
    document.getElementById('debugStartDate').textContent = 'Not set';
    document.getElementById('debugEndDate').textContent = 'Not set';
    document.getElementById('debugApprovedCount').textContent = 'Not set';
    
    // Only regenerate calendar if it's visible
    const calendarPopup = document.getElementById('calendarPopup');
    if (calendarPopup && !calendarPopup.classList.contains('hidden')) {
        generateCalendar();
    }
}

function applyDateRange() {
    if (selectedFromDate && selectedToDate) {
        updatePeriodDisplay();
        updateAllDateFormFields(); // Using the updated function from second block
        toggleCalendar();
        
        // Load payroll data automatically
        loadPayrollData();
    } else {
        alert('Please select both start and end dates');
    }
}

function updateDateRangeDisplay() {
    const dateRangeText = document.getElementById('dateRangeText');
    const selectionStatus = document.getElementById('selectionStatus');
    
    if (selectedFromDate && selectedToDate) {
        dateRangeText.textContent = `${formatDateForDisplay(selectedFromDate)} - ${formatDateForDisplay(selectedToDate)}`;
        if (selectionStatus) {
            selectionStatus.textContent = 'Range selected - Click Apply to confirm';
            selectionStatus.className = 'text-xs text-green-600 mt-1 font-medium';
        }
    } else if (selectedFromDate) {
        dateRangeText.textContent = `${formatDateForDisplay(selectedFromDate)} - Select end date`;
        if (selectionStatus) {
            selectionStatus.textContent = 'Now select end date';
            selectionStatus.className = 'text-xs text-blue-600 mt-1 font-medium';
        }
    } else {
        dateRangeText.textContent = 'Select start date';
        if (selectionStatus) {
            selectionStatus.textContent = 'Select start date';
            selectionStatus.className = 'text-xs text-gray-500 mt-1';
        }
    }
}

function formatDateForInput(date) {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
}

function formatDateForDisplay(date) {
    return date.toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric'
    });
}

function updatePeriodDisplay() {
    const periodDisplay = document.getElementById('periodDisplay');
    
    if (selectedFromDate && selectedToDate) {
        periodDisplay.textContent = `${formatDateForDisplay(selectedFromDate)} - ${formatDateForDisplay(selectedToDate)}`;
    } else if (selectedFromDate) {
        periodDisplay.textContent = `${formatDateForDisplay(selectedFromDate)} - Select end date`;
    } else {
        periodDisplay.textContent = 'Select date range';
    }
}

// Updated function from second block
function updateAllDateFormFields() {
    if (selectedFromDate && selectedToDate) {
        const fromDate = formatDateForInput(selectedFromDate);
        const toDate = formatDateForInput(selectedToDate);
        
        // Update ALL form fields
        const fields = [
            'bulkStartDate', 'bulkEndDate',
            'paymentStartDate', 'paymentEndDate',
            'payslipStartDate', 'payslipEndDate',
            'exportStartDate', 'exportEndDate'
        ];
        
        fields.forEach(fieldId => {
            const field = document.getElementById(fieldId);
            if (field) {
                if (fieldId.includes('StartDate')) {
                    field.value = fromDate;
                } else if (fieldId.includes('EndDate')) {
                    field.value = toDate;
                }
            }
        });
        
        // Update debug info (if elements exist)
        const debugStartDate = document.getElementById('debugStartDate');
        const debugEndDate = document.getElementById('debugEndDate');
        if (debugStartDate) {
            debugStartDate.textContent = fromDate;
        }
        if (debugEndDate) {
            debugEndDate.textContent = toDate;
        }
        
        // Check approved payrolls
        if (typeof checkApprovedPayrolls === 'function') {
        checkApprovedPayrolls(fromDate, toDate);
        }
    }
}

function loadPayrollData() {
    if (!selectedFromDate || !selectedToDate) {
        alert('Please select both from and to dates');
        return;
    }
    
    if (selectedFromDate > selectedToDate) {
        alert('From date cannot be later than to date');
        return;
    }
    
    // Show loading state
    const loadButton = event.target;
    const originalText = loadButton.innerHTML;
    loadButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Loading...';
    loadButton.disabled = true;
    
    // Simulate API call (replace with actual implementation)
    setTimeout(() => {
        // Reset button
        loadButton.innerHTML = originalText;
        loadButton.disabled = false;
        
        // Show success message
        showNotification('Payroll data loaded successfully!', 'success');
        
        // Here you would typically reload the page with new data or update the content via AJAX
        // For now, we'll just show a success message
    }, 1500);
}

function showNotification(message, type) {
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 p-4 rounded-lg shadow-lg z-50 ${
        type === 'success' ? 'bg-green-100 text-green-800 border border-green-200' : 'bg-red-100 text-red-800 border border-red-200'
    }`;
    notification.innerHTML = `
        <div class="flex items-center">
            <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'} mr-2"></i>
            ${message}
        </div>
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.remove();
    }, 3000);
}

// Employee Pagination
let currentEmployeePage = 1;
const employeesPerPage = 6;
let allEmployees = [];

// Initialize employee data from server
function initializeEmployeeData() {
    // Get all employee data from the global variable (most reliable method)
    if (window.allEmployeesData && Array.isArray(window.allEmployeesData) && window.allEmployeesData.length > 0) {
        allEmployees = window.allEmployeesData.map(employee => ({
            html: generateEmployeeCardHTML(employee),
            name: employee.full_name,
            id: employee.employee_id
        }));
        
        // Update display and controls
        updatePaginationControls();
        return true; // Successfully initialized
    }
    
    // Fallback: Try to get from JSON script tag
    const employeesDataScript = document.getElementById('employeesData');
    if (employeesDataScript) {
        let jsonText = '';
        if (employeesDataScript.textContent) {
            jsonText = employeesDataScript.textContent.trim();
        } else if (employeesDataScript.innerHTML) {
            jsonText = employeesDataScript.innerHTML.trim();
        }
        
        if (jsonText) {
            try {
                const employeesData = JSON.parse(jsonText);
                if (Array.isArray(employeesData) && employeesData.length > 0) {
        allEmployees = employeesData.map(employee => ({
            html: generateEmployeeCardHTML(employee),
            name: employee.full_name,
            id: employee.employee_id
        }));
        updatePaginationControls();
                    return true;
                }
            } catch (e) {
                console.error('Error parsing employees JSON:', e);
            }
        }
    }
    
    // Fallback: Try to get total count and build from existing cards + server data
    // Get total employee count from pagination info
    const paginationInfo = document.getElementById('employeePaginationInfo');
    let totalEmployees = 0;
    
    if (paginationInfo) {
        const match = paginationInfo.textContent.match(/of (\d+)/);
        if (match) {
            totalEmployees = parseInt(match[1]);
        }
    }
    
    // If we have the JSON script but parsing failed, try to extract data differently
    if (employeesDataScript && totalEmployees > 0) {
        // Try innerHTML as fallback
        try {
            const jsonText = employeesDataScript.innerHTML.trim();
            const employeesData = JSON.parse(jsonText);
            if (Array.isArray(employeesData) && employeesData.length > 0) {
                allEmployees = employeesData.map(employee => ({
                    html: generateEmployeeCardHTML(employee),
                    name: employee.full_name,
                    id: employee.employee_id
                }));
                updatePaginationControls();
                return true;
            }
        } catch (e) {
            // Continue to card-based fallback
        }
    }
    
    // Last resort: Use cards on page (but this only works if all employees are on page 1)
        const employeeCards = document.querySelectorAll('.employee-card');
    if (employeeCards.length > 0 && totalEmployees === employeeCards.length) {
        // Only use this if we have all employees on the page
        allEmployees = Array.from(employeeCards).map(card => ({
            html: card.outerHTML,
            name: card.querySelector('h4')?.textContent || '',
            id: card.querySelector('span')?.textContent || ''
        }));
        updatePaginationControls();
        return true;
    }
    
    return false; // Failed to initialize
}

function generateEmployeeCardHTML(employee) {
    return `
        <div class="border border-gray-200 rounded-lg p-4 employee-card">
            <div class="flex items-center justify-between mb-2">
                <h4 class="font-medium text-gray-900">${employee.full_name}</h4>
                <span class="text-xs text-gray-500">${employee.employee_id}</span>
            </div>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-600">Monthly Salary:</span>
                    <span class="font-medium">₱${parseFloat(employee.salary).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Daily Rate:</span>
                    <span class="font-medium text-blue-600">₱${parseFloat(employee.daily_rate).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Hourly Rate:</span>
                    <span class="font-medium text-green-600">₱${parseFloat(employee.hourly_rate).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Overtime Rate:</span>
                    <span class="font-medium text-orange-600">₱${parseFloat(employee.overtime_rate).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</span>
                </div>
            </div>
        </div>
    `;
}

function sortBy(field) {
    let url = new URL(window.location.href);
    
    // Remove page parameter
    url.searchParams.delete('page');
    
    // Set sort parameter
    url.searchParams.set('sort', field);
    
    // If clicking same field, toggle order
    const currentSort = url.searchParams.get('sort');
    const currentOrder = url.searchParams.get('order');
    
    if (currentSort === field && currentOrder === 'desc') {
        url.searchParams.set('order', 'asc');
    } else {
        url.searchParams.set('order', 'desc');
    }
    
    window.location.href = url.toString();
    document.getElementById('sortDropdown').classList.add('hidden');
}

function changeEmployeePage(direction) {
    // If employees not loaded, try to initialize first
    if (!allEmployees || allEmployees.length === 0) {
        console.warn('Employees not loaded, attempting to initialize...');
        initializeEmployeeData();
        
        // If still empty after initialization, use fallback
        if (!allEmployees || allEmployees.length === 0) {
            console.error('Failed to load employees data');
            return;
        }
    }
    
    const totalPages = Math.ceil(allEmployees.length / employeesPerPage);
    const newPage = currentEmployeePage + direction;
    
    if (newPage < 1 || newPage > totalPages) {
        return;
    }
    
    currentEmployeePage = newPage;
    updateEmployeeDisplay();
    updatePaginationControls();
}

function updateEmployeeDisplay() {
    const container = document.getElementById('employeeCardsContainer');
    if (!container) {
        console.error('Employee cards container not found');
        return;
    }
    
    if (!allEmployees || allEmployees.length === 0) {
        console.error('No employees data available');
        return;
    }
    
    const startIndex = (currentEmployeePage - 1) * employeesPerPage;
    const endIndex = startIndex + employeesPerPage;
    const currentEmployees = allEmployees.slice(startIndex, endIndex);
    
    // Clear container
    container.innerHTML = '';
    
    // Add current page employees
    currentEmployees.forEach(employee => {
        const div = document.createElement('div');
        div.innerHTML = employee.html;
        container.appendChild(div.firstElementChild);
    });
}

function updatePaginationControls() {
    if (!allEmployees || allEmployees.length === 0) {
        return;
    }
    
    const totalPages = Math.ceil(allEmployees.length / employeesPerPage);
    const startIndex = (currentEmployeePage - 1) * employeesPerPage + 1;
    const endIndex = Math.min(currentEmployeePage * employeesPerPage, allEmployees.length);
    
    // Update page info
    const paginationInfo = document.getElementById('employeePaginationInfo');
    if (paginationInfo) {
        paginationInfo.textContent = 
        `Showing ${startIndex}-${endIndex} of ${allEmployees.length} employees`;
    }
    
    // Update page numbers
    const currentPageEl = document.getElementById('currentEmployeePage');
    const totalPagesEl = document.getElementById('totalEmployeePages');
    if (currentPageEl) {
        currentPageEl.textContent = currentEmployeePage;
    }
    if (totalPagesEl) {
        totalPagesEl.textContent = totalPages;
    }
    
    // Update button states
    const prevButton = document.getElementById('prevEmployeePage');
    const nextButton = document.getElementById('nextEmployeePage');
    
    if (prevButton) {
        const shouldDisable = currentEmployeePage === 1;
        prevButton.disabled = shouldDisable;
        if (shouldDisable) {
        prevButton.classList.add('opacity-50', 'cursor-not-allowed');
    } else {
        prevButton.classList.remove('opacity-50', 'cursor-not-allowed');
        }
    }
    
    if (nextButton) {
        const shouldDisable = currentEmployeePage >= totalPages;
        nextButton.disabled = shouldDisable;
        if (shouldDisable) {
        nextButton.classList.add('opacity-50', 'cursor-not-allowed');
    } else {
        nextButton.classList.remove('opacity-50', 'cursor-not-allowed');
        }
    }
}

// Generate Payroll Function
function generatePayroll() {
    if (!selectedFromDate || !selectedToDate) {
        alert('Please select a date range first');
        return;
    }
    
    if (!confirm(`Generate payroll for period ${formatDateForDisplay(selectedFromDate)} to ${formatDateForDisplay(selectedToDate)}?`)) {
        return;
    }
    
    // Set dates in form
    document.getElementById('generateStartDate').value = formatDateForInput(selectedFromDate);
    document.getElementById('generateEndDate').value = formatDateForInput(selectedToDate);
    
    // Submit form
    document.getElementById('generatePayrollForm').submit();
}

// Filter and Sort Functions
function toggleFilterDropdown() {
    const dropdown = document.getElementById('filterDropdown');
    dropdown.classList.toggle('hidden');
    
    // Close sort dropdown if open
    const sortDropdown = document.getElementById('sortDropdown');
    sortDropdown.classList.add('hidden');
}

function toggleSortDropdown() {
    const dropdown = document.getElementById('sortDropdown');
    dropdown.classList.toggle('hidden');
    
    // Close filter dropdown if open
    const filterDropdown = document.getElementById('filterDropdown');
    filterDropdown.classList.add('hidden');
}

function applyFilters() {
    const status = document.getElementById('filterStatus').value;
    const department = document.getElementById('filterDepartment').value;
    
    let url = new URL(window.location.href);
    
    if (status) {
        url.searchParams.set('status', status);
    } else {
        url.searchParams.delete('status');
    }
    
    if (department) {
        url.searchParams.set('department', department);
    } else {
        url.searchParams.delete('department');
    }
    
    url.searchParams.delete('page');
    
    window.location.href = url.toString();
}

function clearFilters() {
    let url = new URL(window.location.href);
    url.searchParams.delete('status');
    url.searchParams.delete('department');
    url.searchParams.delete('page');
    window.location.href = url.toString();
}

function sortBy(field) {
    let url = new URL(window.location.href);
    const currentSort = url.searchParams.get('sort');
    const currentOrder = url.searchParams.get('order');
    
    if (currentSort === field) {
        url.searchParams.set('order', currentOrder === 'asc' ? 'desc' : 'asc');
    } else {
        url.searchParams.set('sort', field);
        url.searchParams.set('order', 'asc');
    }
    
    url.searchParams.delete('page');
    
    window.location.href = url.toString();
    document.getElementById('sortDropdown').classList.add('hidden');
}

// Add this function to update all date fields
function updateAllDateFields() {
    if (selectedFromDate && selectedToDate) {
        const fromDate = formatDateForInput(selectedFromDate);
        const toDate = formatDateForInput(selectedToDate);
        
        // Update all date fields
        const allDateFields = [
            'generateStartDate', 'generateEndDate',
            'bulkStartDate', 'bulkEndDate',
            'paymentStartDate', 'paymentEndDate',
            'payslipStartDate', 'payslipEndDate',
            'exportStartDate', 'exportEndDate',
            'workflowStartDate', 'workflowEndDate'
        ];
        
        allDateFields.forEach(fieldId => {
            const field = document.getElementById(fieldId);
            if (field) {
                if (fieldId.includes('StartDate')) {
                    field.value = fromDate;
                } else if (fieldId.includes('EndDate')) {
                    field.value = toDate;
                }
            }
        });
    }
}

// Call this function whenever you update the date range
function applyDateRange() {
    if (selectedFromDate && selectedToDate) {
        updatePeriodDisplay();
        updateAllDateFields();
        toggleCalendar();
        
        // Load payroll data automatically
        loadPayrollData();
    } else {
        alert('Please select both start and end dates');
    }
}

// Payment Modal Functions
let selectedEmployees = new Set();
let employeeData = {};

function openPaymentModal() {
    if (!selectedFromDate || !selectedToDate) {
        alert('Please select a date range first');
        return;
    }
    
    // Update modal period display
    document.getElementById('paymentPeriodDisplay').textContent = 
        `${formatDateForDisplay(selectedFromDate)} - ${formatDateForDisplay(selectedToDate)}`;
    
    // Load approved payrolls
    loadApprovedPayrolls();
    
    // Show modal
    document.getElementById('paymentModal').classList.remove('hidden');
}

function closePaymentModal() {
    document.getElementById('paymentModal').classList.add('hidden');
    selectedEmployees.clear();
    updateSelectionDisplay();
}

function loadApprovedPayrolls() {
    const employeeList = document.getElementById('employeeList');
    employeeList.innerHTML = '<div class="text-center py-4"><i class="fas fa-spinner fa-spin text-blue-600"></i><p class="mt-2 text-sm text-gray-500">Loading approved payrolls...</p></div>';
    
    const fromDate = formatDateForInput(selectedFromDate);
    const toDate = formatDateForInput(selectedToDate);
    
    const url = `/ajax/payrolls/approved?start_date=${fromDate}&end_date=${toDate}`;
    
    fetch(url, {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        employeeData = {};
        selectedEmployees.clear();
        
        if (data.length === 0) {
            employeeList.innerHTML = '<div class="text-center py-4 text-yellow-500">No approved payrolls found for this period.</div>';
            return;
        }
        
        let html = '';
        data.forEach(payroll => {
            employeeData[payroll.employee_id] = {
                id: payroll.employee_id,
                name: payroll.employee_name,
                net_pay: payroll.net_pay,
                payroll_id: payroll.id
            };
            
            html += `
                <div class="flex items-center justify-between p-2 hover:bg-gray-50 rounded">
                    <div class="flex items-center">
                        <input type="checkbox" 
                               id="emp_${payroll.employee_id}" 
                               value="${payroll.employee_id}"
                               onchange="toggleEmployee(${payroll.employee_id})"
                               class="h-4 w-4 text-blue-600 border-gray-300 rounded">
                        <label for="emp_${payroll.employee_id}" class="ml-2">
                            <div class="font-medium text-gray-900">${payroll.employee_name}</div>
                            <div class="text-xs text-gray-500">ID: ${payroll.employee_code || payroll.employee_id}</div>
                        </label>
                    </div>
                    <div class="text-right">
                        <div class="font-medium text-green-600">₱${parseFloat(payroll.net_pay).toLocaleString('en-US', {minimumFractionDigits: 2})}</div>
                        <div class="text-xs text-gray-500">Net Pay</div>
                    </div>
                </div>
            `;
        });
        
        employeeList.innerHTML = html;
        updateSelectionDisplay();
    })
    .catch(error => {
        console.error('Error loading payrolls:', error);
        employeeList.innerHTML = '<div class="text-center py-4 text-red-500">Error loading payroll data.</div>';
    });
}

// Initialize export dropdown on page load
document.addEventListener('DOMContentLoaded', function() {
    const exportButton = document.getElementById('exportPayrollButton');
    const exportDropdown = document.getElementById('exportDropdown');
    const exportContainer = document.getElementById('exportDropdownContainer');
    
    console.log('Export dropdown initialization check:', {
        button: !!exportButton,
        dropdown: !!exportDropdown,
        container: !!exportContainer
    });
    
    if (exportButton && exportDropdown) {
        console.log('Export dropdown initialized');
        
        // Toggle dropdown on button click
        exportButton.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();
            
            console.log('Export button clicked');
            
            // Use setTimeout to ensure this runs after any other handlers
            setTimeout(function() {
                const isHidden = exportDropdown.classList.contains('hidden') || exportDropdown.style.display === 'none';
                
                console.log('Dropdown is hidden:', isHidden);
                console.log('Dropdown current display:', exportDropdown.style.display);
                console.log('Dropdown classes:', exportDropdown.className);
                
                // Close all other dropdowns first
                document.querySelectorAll('[id$="Dropdown"]').forEach(dd => {
                    if (dd !== exportDropdown && dd.id) {
                        dd.classList.add('hidden');
                        dd.style.display = 'none';
                    }
                });
                
                // Toggle this dropdown
                if (isHidden) {
                    exportDropdown.classList.remove('hidden');
                    exportDropdown.style.removeProperty('display');
                    exportDropdown.style.visibility = 'visible';
                    exportDropdown.style.opacity = '1';
                    exportDropdown.style.position = 'absolute';
                    console.log('Dropdown shown');
                    console.log('Dropdown after show - display:', window.getComputedStyle(exportDropdown).display);
                    console.log('Dropdown after show - visibility:', window.getComputedStyle(exportDropdown).visibility);
                } else {
                    exportDropdown.classList.add('hidden');
                    exportDropdown.style.display = 'none';
                    console.log('Dropdown hidden');
                }
            }, 0);
        });
        
        // Close dropdown when clicking outside (with delay to allow button click)
        let clickOutsideHandler = null;
        setTimeout(function() {
            clickOutsideHandler = function(e) {
                // Don't close if clicking on the button or dropdown
                if (exportContainer && exportContainer.contains(e.target)) {
                    return;
                }
                
                // Don't close if clicking on dropdown items
                if (exportDropdown && exportDropdown.contains(e.target)) {
                    return;
                }
                
                // Close dropdown
                if (exportDropdown && !exportDropdown.classList.contains('hidden')) {
                    console.log('Closing dropdown - clicked outside');
                    exportDropdown.classList.add('hidden');
                    exportDropdown.style.display = 'none';
                }
            };
            
            // Use a slight delay to prevent immediate closing
            document.addEventListener('click', function(e) {
                setTimeout(function() {
                    if (clickOutsideHandler) {
                        clickOutsideHandler(e);
                    }
                }, 10);
            });
        }, 500);
    } else {
        console.error('Export elements not found:', {
            button: !!exportButton,
            dropdown: !!exportDropdown,
            container: !!exportContainer
        });
    }
});

// Direct toggle function for onclick handler
function toggleExportDropdownDirect(e) {
    if (e) {
        e.preventDefault();
        e.stopPropagation();
        e.stopImmediatePropagation();
    }
    
    console.log('toggleExportDropdownDirect called');
    
    const dropdown = document.getElementById('exportDropdown');
    const button = document.getElementById('exportPayrollButton');
    
    if (!dropdown) {
        console.error('Export dropdown not found');
        alert('Export dropdown element not found. Please refresh the page.');
        return false;
    }
    
    if (!button) {
        console.error('Export button not found');
        return false;
    }
    
    // Check if dropdown is currently visible
    const computedStyle = window.getComputedStyle(dropdown);
    const isHidden = dropdown.style.display === 'none' || 
                     dropdown.style.display === '' && computedStyle.display === 'none' ||
                     dropdown.classList.contains('hidden');
    
    console.log('Dropdown state check:', {
        styleDisplay: dropdown.style.display,
        computedDisplay: computedStyle.display,
        hasHiddenClass: dropdown.classList.contains('hidden'),
        isHidden: isHidden
    });
    
    // Close all other dropdowns first
    document.querySelectorAll('[id$="Dropdown"]').forEach(dd => {
        if (dd !== dropdown && dd.id) {
            dd.classList.add('hidden');
            dd.style.display = 'none';
    }
    });
    
    // Toggle this dropdown
    if (isHidden) {
        // Remove hidden class
        dropdown.classList.remove('hidden');
        
        // Force display with !important via style attribute
        dropdown.setAttribute('style', 'display: block !important; visibility: visible !important; opacity: 1 !important; position: absolute !important;');
        
        console.log('Dropdown shown');
        console.log('After show - style display:', dropdown.style.display);
        console.log('After show - computed display:', window.getComputedStyle(dropdown).display);
        console.log('After show - offsetHeight:', dropdown.offsetHeight);
        console.log('After show - offsetWidth:', dropdown.offsetWidth);
    } else {
        dropdown.classList.add('hidden');
        dropdown.setAttribute('style', 'display: none !important;');
        console.log('Dropdown hidden');
    }
    
    return false;
}

// Toggle export dropdown (for backward compatibility)
function toggleExportDropdown(e) {
    return toggleExportDropdownDirect(e);
}

// Export payroll in specified format
function exportPayroll(format) {
    const formatInput = document.getElementById('exportFormat');
    const exportForm = document.getElementById('exportForm');
    
    if (formatInput && exportForm) {
        formatInput.value = format;
        
        // Close dropdown
        const dropdown = document.getElementById('exportDropdown');
        if (dropdown) {
            dropdown.classList.add('hidden');
        }
        
        // Get format display name
        const formatNames = {
            'pdf': 'PDF',
            'csv': 'CSV',
            'xlsx': 'Excel'
        };
        
        const formatName = formatNames[format] || format.toUpperCase();
        
        // Confirm and submit
        if (confirm(`Export payroll data to ${formatName}?`)) {
            exportForm.submit();
        }
    }
}

// Set export format (legacy function for compatibility)
function setExportFormat(format) {
    exportPayroll(format);
}

// Generate Payslips function
function generatePayslips() {
    const startDate = document.getElementById('payslipStartDate').value;
    const endDate = document.getElementById('payslipEndDate').value;
    
    if (!startDate || !endDate) {
        alert('Please select a date range first.');
        return;
    }
    
    if (confirm(`Generate payslips for period ${formatDateForDisplay(new Date(startDate))} to ${formatDateForDisplay(new Date(endDate))}?`)) {
        // Submit the form
        document.getElementById('payslipForm').submit();
    }
}

function toggleAllEmployees() {
    const selectAll = document.getElementById('selectAllEmployees');
    const checkboxes = document.querySelectorAll('#employeeList input[type="checkbox"]');
    
    if (selectAll.checked) {
        Object.keys(employeeData).forEach(id => {
            selectedEmployees.add(parseInt(id));
        });
        checkboxes.forEach(cb => cb.checked = true);
    } else {
        selectedEmployees.clear();
        checkboxes.forEach(cb => cb.checked = false);
    }
    
    updateSelectionDisplay();
}

function toggleEmployee(employeeId) {
    const checkbox = document.getElementById(`emp_${employeeId}`);
    
    if (checkbox.checked) {
        selectedEmployees.add(employeeId);
    } else {
        selectedEmployees.delete(employeeId);
    }
    
    const selectAll = document.getElementById('selectAllEmployees');
    selectAll.checked = selectedEmployees.size === Object.keys(employeeData).length;
    
    updateSelectionDisplay();
}

function updateSelectionDisplay() {
    const selectedCount = selectedEmployees.size;
    document.getElementById('selectedCount').textContent = `${selectedCount} employees selected`;
    
    let total = 0;
    selectedEmployees.forEach(id => {
        if (employeeData[id]) {
            total += parseFloat(employeeData[id].net_pay);
        }
    });
    
    document.getElementById('totalAmount').textContent = `₱${total.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
}

function processSelectedPayments() {
    if (selectedEmployees.size === 0) {
        alert('Please select at least one employee to process payment.');
        return;
    }
    
    if (!confirm(`Process payments for ${selectedEmployees.size} employee(s) with total amount of ${document.getElementById('totalAmount').textContent}?`)) {
        return;
    }
    
    const fromDate = formatDateForInput(selectedFromDate);
    const toDate = formatDateForInput(selectedToDate);
    const paymentMethod = document.getElementById('paymentMethod').value;
    const notes = document.getElementById('paymentNotes').value;
    const employeeIds = Array.from(selectedEmployees);
    
    const processBtn = document.querySelector('#paymentModal button[onclick="processSelectedPayments()"]');
    const originalText = processBtn.innerHTML;
    processBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Processing...';
    processBtn.disabled = true;
    
    fetch('/ajax/payrolls/process-payments', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            start_date: fromDate,
            end_date: toDate,
            employee_ids: employeeIds,
            payment_method: paymentMethod,
            notes: notes
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification(`Successfully processed ${data.processed} payments!`, 'success');
            closePaymentModal();
            
            setTimeout(() => {
                window.location.reload();
            }, 2000);
        } else {
            showNotification(`Error: ${data.message}`, 'error');
            processBtn.innerHTML = originalText;
            processBtn.disabled = false;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error processing payments. Please try again.', 'error');
        processBtn.innerHTML = originalText;
        processBtn.disabled = false;
    });
}

// OLD APPROVE FUNCTION - RENAME THIS
function approveAllPendingOld() {
    const startDate = document.getElementById('bulkStartDate').value;
    const endDate = document.getElementById('bulkEndDate').value;
    
    if (!startDate || !endDate) {
        alert('Please select a date range first');
        return;
    }
    
    if (!confirm(`Approve all pending payrolls for period ${formatDateForDisplay(selectedFromDate)} to ${formatDateForDisplay(selectedToDate)}?`)) {
        return;
    }
    
    const button = event.target;
    const originalText = button.innerHTML;
    button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Approving...';
    button.disabled = true;
    
    // Submit the form
    document.getElementById('approveForm').submit();
}

// Process Payments Function - FIXED VERSION
async function processPayments() {
    const button = document.getElementById('processPaymentsBtn');
    const originalText = button.innerHTML;
    
    try {
        // Get date values
        const startDate = document.getElementById('paymentStartDate').value;
        const endDate = document.getElementById('paymentEndDate').value;
        
        if (!startDate || !endDate) {
            alert('Please select a date range first.');
            return;
        }
        
        // Show loading state
        button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Processing...';
        button.disabled = true;
        
        // Get payroll status counts
        const statusResponse = await fetch(`/ajax/payrolls/status-count?start_date=${encodeURIComponent(startDate)}&end_date=${encodeURIComponent(endDate)}`);
        const statusData = await statusResponse.json();
        
        const approvedCount = statusData.approved || 0;
        const pendingCount = statusData.pending || 0;
        
        if (approvedCount === 0 && pendingCount === 0) {
            button.innerHTML = originalText;
            button.disabled = false;
            alert('No approved or pending payrolls found for this period. Please generate payroll first.');
            return;
        }
        
        if (approvedCount === 0 && pendingCount > 0) {
            const confirmApprove = confirm(`No approved payrolls found. Found ${pendingCount} pending payroll(s). Do you want to approve them first and then process payments?`);
            
            if (confirmApprove) {
                // Approve pending payrolls first
                const approveResponse = await fetch('/ajax/payrolls/approve-all', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        start_date: startDate,
                        end_date: endDate
                    })
                });
                
                const approveResult = await approveResponse.json();
                
                if (approveResult.success && approveResult.approved_count > 0) {
                    // Wait a moment and check again
                    await new Promise(resolve => setTimeout(resolve, 1000));
                    
                    // Now process payments
                    if (confirm(`Successfully approved ${approveResult.approved_count} payroll(s). Now process payments?`)) {
                        // Submit the form to process payments
                        document.getElementById('processPaymentsForm').submit();
                    } else {
                        button.innerHTML = originalText;
                        button.disabled = false;
                    }
                } else {
                    button.innerHTML = originalText;
                    button.disabled = false;
                    alert('Failed to approve payrolls. Please try again.');
                }
            } else {
                button.innerHTML = originalText;
                button.disabled = false;
                alert('Cannot process payments without approved payrolls.');
            }
        } else if (approvedCount > 0) {
            // We have approved payrolls, process them directly
            if (confirm(`Process payments for ${approvedCount} approved payroll(s) for this period?`)) {
                // Submit the form to process payments
                document.getElementById('processPaymentsForm').submit();
            } else {
                button.innerHTML = originalText;
                button.disabled = false;
            }
        }
        
    } catch (error) {
        console.error('Error:', error);
        button.innerHTML = originalText;
        button.disabled = false;
        alert('Error: ' + error.message);
    }
}

// Submit payment processing
async function submitPaymentProcess(startDate, endDate) {
    // Submit the form directly - the backend will handle the rest
    document.getElementById('processPaymentsForm').submit();
}

// Check pending payrolls (helper function)
async function checkPendingPayrolls(startDate, endDate) {
    try {
        const response = await fetch(`/ajax/payrolls/pending?start_date=${encodeURIComponent(startDate)}&end_date=${encodeURIComponent(endDate)}`);
        if (response.ok) {
            const data = await response.json();
            return data.length;
        }
        return 0;
    } catch (error) {
        console.error('Error checking pending payrolls:', error);
        return 0;
    }
}

// Approve all pending payrolls via AJAX
async function approveAllPendingAJAX(startDate, endDate) {
    try {
        const response = await fetch(`/ajax/payrolls/approve-all`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                start_date: startDate,
                end_date: endDate
            })
        });
        
        const result = await response.json();
        
        if (result.success) {
            return result.approved_count;
        } else {
            throw new Error(result.message || 'Failed to approve payrolls');
        }
    } catch (error) {
        console.error('Error approving payrolls:', error);
        throw error;
    }
}

// Approve All Pending with confirmation and auto-refresh
async function approveAllPendingWithConfirmation() {
    const button = document.getElementById('approveAllPendingBtn');
    const originalText = button.innerHTML;
    
    try {
        // Get date values - FIXED: using the right IDs
        const startDate = document.getElementById('bulkStartDate').value;
        const endDate = document.getElementById('bulkEndDate').value;
        
        if (!startDate || !endDate) {
            alert('Please select a date range first.');
            return;
        }
        
        // Check pending payrolls count
        const pendingCount = await checkPendingPayrolls(startDate, endDate);
        
        if (pendingCount === 0) {
            alert('No pending payrolls found for this period.');
            return;
        }
        
        // Show loading state
        button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Approving...';
        button.disabled = true;
        
        // Get confirmation
        if (confirm(`Approve ${pendingCount} pending payroll(s) for period ${formatDateForDisplay(new Date(startDate))} to ${formatDateForDisplay(new Date(endDate))}?`)) {
            // Use the AJAX function (UPDATED)
            const approvedCount = await approveAllPendingAJAX(startDate, endDate);
            
            if (approvedCount > 0) {
                // Success - reload the page to show updated status
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                alert('No payrolls were approved. Please try again.');
                button.innerHTML = originalText;
                button.disabled = false;
            }
        } else {
            // User cancelled
            button.innerHTML = originalText;
            button.disabled = false;
        }
        
    } catch (error) {
        console.error('Error:', error);
        button.innerHTML = originalText;
        button.disabled = false;
        alert('Error: ' + error.message);
    }
}

// New function to check payroll status
async function checkPayrollStatus(startDate, endDate) {
    try {
        const response = await fetch(`/ajax/payrolls/status-count?start_date=${encodeURIComponent(startDate)}&end_date=${encodeURIComponent(endDate)}`);
        const data = await response.json();
        
        return {
            approvedCount: data.approved || 0,
            pendingCount: data.pending || 0,
            paidCount: data.paid || 0
        };
    } catch (error) {
        console.error('Error checking payroll status:', error);
        return { approvedCount: 0, pendingCount: 0, paidCount: 0 };
    }
}

// New function to approve pending payrolls
async function approvePendingPayrolls(startDate, endDate) {
    try {
        const response = await fetch('/ajax/payrolls/bulk-approve', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                start_date: startDate,
                end_date: endDate
            })
        });
        
        const data = await response.json();
        return data.approved || 0;
    } catch (error) {
        console.error('Error approving payrolls:', error);
        return 0;
    }
}

// Function to submit payment process
async function submitPaymentProcess(startDate, endDate) {
    const button = document.getElementById('processPaymentsBtn');
    button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Processing...';
    button.disabled = true;
    
    // Submit the form
    document.getElementById('processPaymentsForm').submit();
}

// Check approved payrolls (from second block)
async function checkApprovedPayrolls(startDate, endDate) {
    try {
        const response = await fetch(`/ajax/payrolls/approved?start_date=${encodeURIComponent(startDate)}&end_date=${encodeURIComponent(endDate)}`);
        const data = await response.json();
        
        // Update debug info (if elements exist)
        const debugApprovedCount = document.getElementById('debugApprovedCount');
        const debugStartDate = document.getElementById('debugStartDate');
        const debugEndDate = document.getElementById('debugEndDate');
        
        if (debugApprovedCount) {
            debugApprovedCount.textContent = data.length;
        }
        if (debugStartDate) {
            debugStartDate.textContent = startDate;
        }
        if (debugEndDate) {
            debugEndDate.textContent = endDate;
        }
        
        return data.length;
    } catch (error) {
        console.error('Error checking approved payrolls:', error);
        return 0;
    }
}

// Test function (from second block)
async function testPaymentProcessing() {
    console.log('Testing payment processing...');
    
    // Test 1: Check route
    console.log('Route URL:', "<?php echo e(route('payrolls.process-payments')); ?>");
    
    // Test 2: Check form values
    const startDate = document.getElementById('paymentStartDate').value;
    const endDate = document.getElementById('paymentEndDate').value;
    console.log('Start Date:', startDate);
    console.log('End Date:', endDate);
    
    // Test 3: Check approved payrolls
    const count = await checkApprovedPayrolls(startDate, endDate);
    alert(`Found ${count} approved payroll(s) for the selected period.`);
    
    // Test 4: Try to submit form directly
    console.log('Form HTML:', document.getElementById('processPaymentsForm').outerHTML);
}

// Close dropdowns when clicking outside
document.addEventListener('click', function(e) {
    const filterBtn = document.querySelector('[onclick="toggleFilterDropdown()"]');
    const sortBtn = document.querySelector('[onclick="toggleSortDropdown()"]');
    const filterDropdown = document.getElementById('filterDropdown');
    const sortDropdown = document.getElementById('sortDropdown');
    
    if (filterDropdown && !filterBtn.contains(e.target) && !filterDropdown.contains(e.target)) {
        filterDropdown.classList.add('hidden');
    }
    
    if (sortDropdown && !sortBtn.contains(e.target) && !sortDropdown.contains(e.target)) {
        sortDropdown.classList.add('hidden');
    }
});

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    // Initialize with current month
    setDateRange('thisMonth');
    
    // Initialize employee pagination - wait a bit for script tags to be parsed
    setTimeout(function() {
    initializeEmployeeData();
    }, 100);
    
    // Also attach event listeners as backup (in addition to onclick attributes)
    setTimeout(function() {
        const prevButton = document.getElementById('prevEmployeePage');
        const nextButton = document.getElementById('nextEmployeePage');
        
        if (prevButton) {
            prevButton.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                changeEmployeePage(-1);
            });
        }
        
        if (nextButton) {
            nextButton.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                changeEmployeePage(1);
            });
        }
    }, 200);
    
    // Close calendar when clicking outside
    document.addEventListener('click', function(e) {
        const calendarPopup = document.getElementById('calendarPopup');
        const dateRangeButton = document.getElementById('dateRangeButton');
        
        if (calendarPopup && !calendarPopup.contains(e.target) && 
            dateRangeButton && !dateRangeButton.contains(e.target)) {
            calendarPopup.classList.add('hidden');
        }
    });
    
    // Check approved payrolls on page load
    const startDate = document.getElementById('paymentStartDate').value;
    const endDate = document.getElementById('paymentEndDate').value;
    if (startDate && endDate) {
        checkApprovedPayrolls(startDate, endDate);
    }
});
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.dashboard-base', ['user' => auth()->user(), 'activeRoute' => 'payroll.index'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\caior\Documents\Mica files\Aeternitas-System-V2\resources\views/payroll/index.blade.php ENDPATH**/ ?>
<?php $__env->startSection('title', 'Leave Management'); ?>

<?php $__env->startSection('content'); ?>
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Leave Management</h1>
            <p class="mt-1 text-sm text-gray-600">Manage employee leave requests and balances</p>
        </div>
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
                        <a href="<?php echo e(route('attendance.leave-management.export', ['format' => 'pdf']) . '?' . http_build_query(request()->query())); ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                            <i class="fas fa-file-pdf mr-2 text-red-500"></i>Export as PDF
                        </a>
                        <a href="<?php echo e(route('attendance.leave-management.export', ['format' => 'csv']) . '?' . http_build_query(request()->query())); ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                            <i class="fas fa-file-csv mr-2 text-green-500"></i>Export as CSV
                        </a>
                        <a href="<?php echo e(route('attendance.leave-management.export', ['format' => 'xls']) . '?' . http_build_query(request()->query())); ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                            <i class="fas fa-file-excel mr-2 text-green-600"></i>Export as Excel
                        </a>
                    </div>
                </div>
            </div>
            <?php if($user->role === 'employee'): ?>
            <a href="<?php echo e(route('attendance.leave-management.create')); ?>" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-lg font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                <i class="fas fa-plus mr-2"></i>
                New Leave Request
            </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Leave Summary -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 sm:p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-calendar-alt text-blue-600"></i>
                    </div>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-gray-500">Total Requests</p>
                    <p class="text-lg font-semibold text-gray-900"><?php echo e($summary['total'] ?? 0); ?></p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 sm:p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-yellow-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-hourglass-half text-yellow-600"></i>
                    </div>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-gray-500">Pending</p>
                    <p class="text-lg font-semibold text-gray-900"><?php echo e($summary['pending'] ?? 0); ?></p>
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
                    <p class="text-sm font-medium text-gray-500">Approved</p>
                    <p class="text-lg font-semibold text-gray-900"><?php echo e($summary['approved'] ?? 0); ?></p>
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
                    <p class="text-sm font-medium text-gray-500">Rejected</p>
                    <p class="text-lg font-semibold text-gray-900"><?php echo e($summary['rejected'] ?? 0); ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 sm:p-6">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <?php if($user->role !== 'employee'): ?>
            <div>
                <label for="employee" class="block text-sm font-medium text-gray-700 mb-2">Employee</label>
                <select id="employee" name="employee_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-white text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors">
                    <option value="">All Employees</option>
                    <?php if(isset($employees) && $employees->count() > 0): ?>
                        <?php $__currentLoopData = $employees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $emp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($emp->id); ?>" <?php echo e(request('employee_id') == $emp->id ? 'selected' : ''); ?>>
                                <?php echo e($emp->full_name); ?> - <?php echo e($emp->department->name ?? 'No Department'); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <?php endif; ?>
                </select>
            </div>
            <?php endif; ?>
            <div>
                <label for="leaveType" class="block text-sm font-medium text-gray-700 mb-2">Leave Type</label>
                <select id="leaveType" name="leave_type" class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-white text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors">
                    <option value="">All Types</option>
                    <option value="vacation" <?php echo e(request('leave_type') == 'vacation' ? 'selected' : ''); ?>>Vacation Leave</option>
                    <option value="sick" <?php echo e(request('leave_type') == 'sick' ? 'selected' : ''); ?>>Sick Leave</option>
                    <option value="personal" <?php echo e(request('leave_type') == 'personal' ? 'selected' : ''); ?>>Personal Leave</option>
                    <option value="emergency" <?php echo e(request('leave_type') == 'emergency' ? 'selected' : ''); ?>>Emergency Leave</option>
                    <option value="maternity" <?php echo e(request('leave_type') == 'maternity' ? 'selected' : ''); ?>>Maternity Leave</option>
                    <option value="paternity" <?php echo e(request('leave_type') == 'paternity' ? 'selected' : ''); ?>>Paternity Leave</option>
                    <option value="bereavement" <?php echo e(request('leave_type') == 'bereavement' ? 'selected' : ''); ?>>Bereavement Leave</option>
                    <option value="study" <?php echo e(request('leave_type') == 'study' ? 'selected' : ''); ?>>Study Leave</option>
                </select>
            </div>
            <div>
                <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                <select id="status" name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-white text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors">
                    <option value="">All Status</option>
                    <option value="pending" <?php echo e(request('status') == 'pending' ? 'selected' : ''); ?>>Pending</option>
                    <option value="approved" <?php echo e(request('status') == 'approved' ? 'selected' : ''); ?>>Approved</option>
                    <option value="rejected" <?php echo e(request('status') == 'rejected' ? 'selected' : ''); ?>>Rejected</option>
                    <option value="cancelled" <?php echo e(request('status') == 'cancelled' ? 'selected' : ''); ?>>Cancelled</option>
                </select>
            </div>
            <div>
                <label for="dateFrom" class="block text-sm font-medium text-gray-700 mb-2">From Date</label>
                <input type="date" id="dateFrom" name="date_from" value="<?php echo e(request('date_from')); ?>" class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-white text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors">
            </div>
        </div>
        <div class="mt-4 flex flex-col sm:flex-row gap-3 sm:items-end">
            <button onclick="applyFilters()" class="w-full sm:w-auto px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium">
                <i class="fas fa-search mr-2"></i>Apply Filters
            </button>
            <?php if(in_array($user->role, ['admin', 'hr', 'manager']) && ($hasEmployeesWithoutBalances ?? true)): ?>
            <button onclick="openSetLeaveBalanceModal()" class="w-full sm:w-auto px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors font-medium" title="Set Leave Balance">
                <i class="fas fa-calendar-plus mr-2"></i><span class="hidden sm:inline">Set Leave Balance</span><span class="sm:hidden">Set Balance</span>
            </button>
            <?php endif; ?>
        </div>
    </div>

    <!-- Leave Requests -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-4 sm:px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Leave Requests</h3>
            <p class="mt-1 text-sm text-gray-600">Employee leave requests and approvals</p>
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
                            Leave Type
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Start Date
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            End Date
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Duration
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Reason
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Status
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php $__empty_1 = true; $__currentLoopData = $leaveRequests; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $leaveRequest): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php
                            $employee = $leaveRequest->employee;
                            $initials = strtoupper(substr($employee->first_name ?? '', 0, 1) . substr($employee->last_name ?? '', 0, 1));
                            $statusColors = [
                                'pending' => 'bg-yellow-100 text-yellow-800',
                                'approved' => 'bg-green-100 text-green-800',
                                'rejected' => 'bg-red-100 text-red-800',
                                'cancelled' => 'bg-gray-100 text-gray-800'
                            ];
                            $leaveTypeColors = [
                                'vacation' => 'bg-blue-100 text-blue-800',
                                'sick' => 'bg-red-100 text-red-800',
                                'personal' => 'bg-purple-100 text-purple-800',
                                'emergency' => 'bg-orange-100 text-orange-800',
                                'maternity' => 'bg-pink-100 text-pink-800',
                                'paternity' => 'bg-indigo-100 text-indigo-800',
                                'bereavement' => 'bg-gray-100 text-gray-800',
                                'study' => 'bg-teal-100 text-teal-800'
                            ];
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
                                        <div class="text-sm font-medium text-gray-900"><?php echo e($employee->full_name ?? 'N/A'); ?></div>
                                        <div class="text-sm text-gray-500"><?php echo e($employee->department->name ?? 'No Department'); ?></div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo e($leaveTypeColors[$leaveRequest->leave_type] ?? 'bg-gray-100 text-gray-800'); ?>">
                                    <?php echo e(ucfirst(str_replace('_', ' ', $leaveRequest->leave_type))); ?> Leave
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900"><?php echo e(\Carbon\Carbon::parse($leaveRequest->start_date)->format('M d, Y')); ?></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900"><?php echo e(\Carbon\Carbon::parse($leaveRequest->end_date)->format('M d, Y')); ?></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900"><?php echo e($leaveRequest->days_requested); ?> <?php echo e($leaveRequest->days_requested == 1 ? 'day' : 'days'); ?></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900"><?php echo e(Str::limit($leaveRequest->reason, 30)); ?></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo e($statusColors[$leaveRequest->status] ?? 'bg-gray-100 text-gray-800'); ?>">
                                    <?php if($leaveRequest->status == 'pending' || $leaveRequest->status == 'approved'): ?>
                                        <div class="w-1.5 h-1.5 rounded-full mr-1.5 <?php echo e($leaveRequest->status == 'pending' ? 'bg-yellow-400' : 'bg-green-400'); ?>"></div>
                                    <?php endif; ?>
                                    <?php echo e(ucfirst($leaveRequest->status)); ?>

                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex space-x-2">
                                    <?php if(in_array($user->role, ['admin', 'hr']) && $leaveRequest->status == 'pending'): ?>
                                        <button data-leave-id="<?php echo e($leaveRequest->id); ?>" data-action="approve" class="time-action-btn text-green-600 hover:text-green-900 transition-colors" title="Approve">
                                    <i class="fas fa-check"></i>
                                </button>
                                        <button data-leave-id="<?php echo e($leaveRequest->id); ?>" data-action="reject" class="time-action-btn text-red-600 hover:text-red-900 transition-colors" title="Reject">
                                    <i class="fas fa-times"></i>
                                </button>
                                    <?php endif; ?>
                                    <?php if($leaveRequest->status == 'pending' && ($user->role == 'employee' && $leaveRequest->employee_id == $user->employee?->id)): ?>
                                        <button data-leave-id="<?php echo e($leaveRequest->id); ?>" data-action="cancel" class="time-action-btn text-orange-600 hover:text-orange-900 transition-colors" title="Cancel">
                                            <i class="fas fa-ban"></i>
                                        </button>
                                    <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="8" class="px-6 py-4 text-center text-sm text-gray-500">No leave requests found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <?php if($leaveRequests->hasPages()): ?>
            <div class="px-6 py-4 border-t border-gray-200">
                <?php echo e($leaveRequests->appends(request()->query())->links()); ?>

            </div>
        <?php endif; ?>

        <!-- Mobile Cards -->
        <div class="lg:hidden">
            <div class="p-4 space-y-4">
                <?php $__empty_1 = true; $__currentLoopData = $leaveRequests; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $leaveRequest): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <?php
                        $employee = $leaveRequest->employee;
                        $initials = strtoupper(substr($employee->first_name ?? '', 0, 1) . substr($employee->last_name ?? '', 0, 1));
                        $statusColors = [
                            'pending' => 'bg-yellow-100 text-yellow-800',
                            'approved' => 'bg-green-100 text-green-800',
                            'rejected' => 'bg-red-100 text-red-800',
                            'cancelled' => 'bg-gray-100 text-gray-800'
                        ];
                        $leaveTypeColors = [
                            'vacation' => 'bg-blue-100 text-blue-800',
                            'sick' => 'bg-red-100 text-red-800',
                            'personal' => 'bg-purple-100 text-purple-800',
                            'emergency' => 'bg-orange-100 text-orange-800',
                            'maternity' => 'bg-pink-100 text-pink-800',
                            'paternity' => 'bg-indigo-100 text-indigo-800',
                            'bereavement' => 'bg-gray-100 text-gray-800',
                            'study' => 'bg-teal-100 text-teal-800'
                        ];
                    ?>
                <div class="border border-gray-200 rounded-lg p-4">
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center space-x-3">
                            <div class="h-10 w-10 rounded-full bg-gradient-to-r from-blue-500 to-blue-600 flex items-center justify-center">
                                    <span class="text-sm font-medium text-white"><?php echo e($initials); ?></span>
                            </div>
                            <div>
                                    <div class="font-medium text-gray-900"><?php echo e($employee->full_name ?? 'N/A'); ?></div>
                                    <div class="text-sm text-gray-500"><?php echo e($employee->department->name ?? 'No Department'); ?></div>
                                </div>
                            </div>
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium <?php echo e($statusColors[$leaveRequest->status] ?? 'bg-gray-100 text-gray-800'); ?>">
                                <?php if($leaveRequest->status == 'pending' || $leaveRequest->status == 'approved'): ?>
                                    <div class="w-1.5 h-1.5 rounded-full mr-1 <?php echo e($leaveRequest->status == 'pending' ? 'bg-yellow-400' : 'bg-green-400'); ?>"></div>
                                <?php endif; ?>
                                <?php echo e(ucfirst($leaveRequest->status)); ?>

                            </span>
                    </div>
                    <div class="mb-3">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo e($leaveTypeColors[$leaveRequest->leave_type] ?? 'bg-gray-100 text-gray-800'); ?>">
                                <?php echo e(ucfirst(str_replace('_', ' ', $leaveRequest->leave_type))); ?> Leave
                        </span>
                    </div>
                    <div class="grid grid-cols-2 gap-4 text-sm mb-3">
                        <div>
                            <div class="text-gray-500">Start Date</div>
                                <div class="font-medium"><?php echo e(\Carbon\Carbon::parse($leaveRequest->start_date)->format('M d, Y')); ?></div>
                        </div>
                        <div>
                            <div class="text-gray-500">End Date</div>
                                <div class="font-medium"><?php echo e(\Carbon\Carbon::parse($leaveRequest->end_date)->format('M d, Y')); ?></div>
                        </div>
                        <div>
                            <div class="text-gray-500">Duration</div>
                                <div class="font-medium"><?php echo e($leaveRequest->days_requested); ?> <?php echo e($leaveRequest->days_requested == 1 ? 'day' : 'days'); ?></div>
                        </div>
                        <div>
                            <div class="text-gray-500">Type</div>
                                <div class="font-medium"><?php echo e(ucfirst(str_replace('_', ' ', $leaveRequest->leave_type))); ?></div>
                            </div>
                    </div>
                    <div class="text-sm mb-3">
                        <div class="text-gray-500">Reason</div>
                            <div class="font-medium"><?php echo e(Str::limit($leaveRequest->reason, 50)); ?></div>
                    </div>
                    <div class="flex justify-end space-x-2">
                            <?php if(in_array($user->role, ['admin', 'hr']) && $leaveRequest->status == 'pending'): ?>
                                <button data-leave-id="<?php echo e($leaveRequest->id); ?>" data-action="approve" class="time-action-btn text-green-600 hover:text-green-900 transition-colors">
                            <i class="fas fa-check mr-1"></i>Approve
                        </button>
                                <button data-leave-id="<?php echo e($leaveRequest->id); ?>" data-action="reject" class="time-action-btn text-red-600 hover:text-red-900 transition-colors">
                            <i class="fas fa-times mr-1"></i>Reject
                        </button>
                            <?php endif; ?>
                            <?php if($leaveRequest->status == 'pending' && ($user->role == 'employee' && $leaveRequest->employee_id == $user->employee?->id)): ?>
                                <button data-leave-id="<?php echo e($leaveRequest->id); ?>" data-action="cancel" class="time-action-btn text-orange-600 hover:text-orange-900 transition-colors">
                                    <i class="fas fa-ban mr-1"></i>Cancel
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="text-center text-sm text-gray-500 py-8">No leave requests found.</div>
                <?php endif; ?>
                
                <?php if($leaveRequests->hasPages()): ?>
                    <div class="mt-4">
                        <?php echo e($leaveRequests->appends(request()->query())->links()); ?>

                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Leave Balances -->
    <?php
        $selectedEmployeeId = request('employee_id');
        $showLeaveBalances = false;
        $selectedEmployeeBalance = null;
        $selectedEmployee = null;
        
        // For employees, always show their own balance
        if ($user->role === 'employee' && $user->employee) {
            $showLeaveBalances = true;
            $selectedEmployee = $user->employee;
            // Always refresh balance from database to ensure we have latest data
            // Check which year has the most recent approved leaves and prioritize that year
            $currentYear = now()->year;
            $approvedLeaves = \App\Models\LeaveRequest::where('employee_id', $user->employee->id)
                ->where('status', 'approved')
                ->get();
            
            // Find the year with the most recent approved leave
            $mostRecentYear = $currentYear;
            if ($approvedLeaves->count() > 0) {
                $mostRecentLeave = $approvedLeaves->sortByDesc('start_date')->first();
                $mostRecentYear = $mostRecentLeave->start_date instanceof \Carbon\Carbon 
                    ? $mostRecentLeave->start_date->year 
                    : \Carbon\Carbon::parse($mostRecentLeave->start_date)->year;
            }
            
            // Prioritize the year with approved leaves, then current year, then others
            $selectedEmployeeBalance = \App\Models\LeaveBalance::where('employee_id', $user->employee->id)
                ->where(function($q) use ($currentYear, $mostRecentYear) {
                    $q->where('year', $mostRecentYear)
                      ->orWhere('year', $currentYear)
                      ->orWhere('year', $currentYear - 1)
                      ->orWhere('year', $currentYear + 1);
                })
                ->orderByRaw("CASE WHEN year = {$mostRecentYear} THEN 1 WHEN year = {$currentYear} THEN 2 ELSE 3 END")
                ->orderBy('year', 'desc')
                ->first();
            
            // Fallback to collection if database lookup fails
            if (!$selectedEmployeeBalance) {
            $balanceGroup = $leaveBalances->get($user->employee->id);
            if ($balanceGroup && $balanceGroup instanceof \Illuminate\Support\Collection) {
                    $selectedEmployeeBalance = $balanceGroup->firstWhere('year', $currentYear) 
                        ?? $balanceGroup->firstWhere('year', $currentYear - 1)
                        ?? $balanceGroup->firstWhere('year', $currentYear + 1)
                        ?? $balanceGroup->first();
            } else {
                $selectedEmployeeBalance = $balanceGroup;
            }
            }
        }
        // For HR/Admin, only show when a specific employee is selected (not "All Employees")
        elseif (in_array($user->role, ['admin', 'hr', 'manager']) && $selectedEmployeeId && $selectedEmployeeId !== '') {
            // Find employee from the filtered employees collection (respects company filtering)
            // Use firstWhere with string comparison to handle UUID properly
            $selectedEmployee = $employees->first(function($emp) use ($selectedEmployeeId) {
                return (string)$emp->id === (string)$selectedEmployeeId;
            });
            
            if ($selectedEmployee) {
                $showLeaveBalances = true;
                // Try to get balance from grouped collection - get first item from the group
                // Use string comparison for UUID keys
                $balanceGroup = $leaveBalances->first(function($group, $key) use ($selectedEmployeeId) {
                    return (string)$key === (string)$selectedEmployeeId;
                });
                
                // Always refresh balance from database to ensure we have latest data
                // Check which year has the most recent approved leaves and prioritize that year
                $currentYear = now()->year;
                $approvedLeaves = \App\Models\LeaveRequest::where('employee_id', $selectedEmployeeId)
                    ->where('status', 'approved')
                    ->get();
                
                // Find the year with the most recent approved leave
                $mostRecentYear = $currentYear;
                if ($approvedLeaves->count() > 0) {
                    $mostRecentLeave = $approvedLeaves->sortByDesc('start_date')->first();
                    $mostRecentYear = $mostRecentLeave->start_date instanceof \Carbon\Carbon 
                        ? $mostRecentLeave->start_date->year 
                        : \Carbon\Carbon::parse($mostRecentLeave->start_date)->year;
                }
                
                // Prioritize the year with approved leaves, then current year, then others
                $selectedEmployeeBalance = \App\Models\LeaveBalance::where('employee_id', $selectedEmployeeId)
                    ->where(function($q) use ($currentYear, $mostRecentYear) {
                        $q->where('year', $mostRecentYear)
                          ->orWhere('year', $currentYear)
                          ->orWhere('year', $currentYear - 1)
                          ->orWhere('year', $currentYear + 1);
                    })
                    ->orderByRaw("CASE WHEN year = {$mostRecentYear} THEN 1 WHEN year = {$currentYear} THEN 2 ELSE 3 END")
                    ->orderBy('year', 'desc')
                    ->first();
                
                // Fallback to collection if database lookup fails
                if (!$selectedEmployeeBalance) {
                if ($balanceGroup && $balanceGroup instanceof \Illuminate\Support\Collection) {
                        $selectedEmployeeBalance = $balanceGroup->firstWhere('year', $currentYear) 
                            ?? $balanceGroup->firstWhere('year', $currentYear - 1)
                            ?? $balanceGroup->firstWhere('year', $currentYear + 1)
                            ?? $balanceGroup->first();
                } else {
                    $selectedEmployeeBalance = $balanceGroup;
                }
                }
            }
        }
    ?>
    
    <?php if($showLeaveBalances && $selectedEmployee): ?>
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-medium text-gray-900">
                Leave Balances
                <?php if($user->role !== 'employee' && $selectedEmployee): ?>
                    - <?php echo e($selectedEmployee->full_name); ?>

                <?php endif; ?>
            </h3>
            <?php if(in_array($user->role, ['admin', 'hr', 'manager']) && $selectedEmployeeBalance): ?>
            <button onclick="openEditLeaveBalanceModal('<?php echo e($selectedEmployee->id); ?>')" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-sm">
                <i class="fas fa-edit mr-2"></i>Edit Balance
            </button>
            <?php endif; ?>
        </div>
        <?php if($selectedEmployeeBalance): ?>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <?php
                $leaveTypes = [
                    'vacation' => ['label' => 'Vacation Leave', 'color' => 'blue'],
                    'sick' => ['label' => 'Sick Leave', 'color' => 'red'],
                    'personal' => ['label' => 'Personal Leave', 'color' => 'green'],
                    'emergency' => ['label' => 'Emergency Leave', 'color' => 'yellow'],
                    'maternity' => ['label' => 'Maternity Leave', 'color' => 'pink'],
                    'paternity' => ['label' => 'Paternity Leave', 'color' => 'indigo'],
                    'bereavement' => ['label' => 'Bereavement Leave', 'color' => 'gray'],
                    'study' => ['label' => 'Study Leave', 'color' => 'purple'],
                ];
            ?>
            <?php $__currentLoopData = $leaveTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type => $config): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                    $totalField = $type . '_days_total';
                    $usedField = $type . '_days_used';
                    $total = $selectedEmployeeBalance->$totalField ?? 0;
                    $used = $selectedEmployeeBalance->$usedField ?? 0;
                    $remaining = $total - $used;
                    $percentage = $total > 0 ? ($used / $total) * 100 : 0;
                    $widthPercentage = min((float)$percentage, 100);
                    $colorClasses = [
                        'blue' => 'bg-blue-600',
                        'red' => 'bg-red-600',
                        'green' => 'bg-green-600',
                        'yellow' => 'bg-yellow-600',
                        'pink' => 'bg-pink-600',
                        'indigo' => 'bg-indigo-600',
                        'gray' => 'bg-gray-600',
                        'purple' => 'bg-purple-600',
                    ];
                    $barColor = $colorClasses[$config['color']] ?? 'bg-blue-600';
                ?>
                <?php if($total > 0): ?>
            <div class="border border-gray-200 rounded-lg p-4">
                <div class="flex items-center justify-between mb-2">
                        <h4 class="font-medium text-gray-900"><?php echo e($config['label']); ?></h4>
                        <span class="text-sm text-gray-500"><?php echo e($total); ?> days</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="<?php echo e($barColor); ?> h-2 rounded-full transition-all" style="--width: <?php echo e($widthPercentage); ?>%; width: var(--width)"></div>
                </div>
                <div class="text-xs text-gray-500 mt-1">
                    <span class="font-medium"><?php echo e($used); ?></span> days used, 
                    <span class="font-medium text-green-600"><?php echo e($remaining); ?></span> remaining
                </div>
                </div>
                <?php endif; ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
        <?php else: ?>
        <div class="text-center py-8 text-gray-500">
            <i class="fas fa-info-circle text-4xl mb-4 text-gray-400"></i>
            <p class="text-lg font-medium mb-2">No leave balance record found</p>
            <p class="text-sm">This employee doesn't have a leave balance record for <?php echo e(now()->year); ?>.</p>
        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>

<script>
function applyFilters() {
    const employee = document.getElementById('employee').value;
    const leaveType = document.getElementById('leaveType').value;
    const status = document.getElementById('status').value;
    const dateFrom = document.getElementById('dateFrom').value;
    
    // Build query string
    const params = new URLSearchParams();
    if (employee) params.append('employee_id', employee);
    if (leaveType) params.append('leave_type', leaveType);
    if (status) params.append('status', status);
    if (dateFrom) params.append('date_from', dateFrom);
    
    // Redirect with filters
    window.location.href = '<?php echo e(route("attendance.leave-management")); ?>?' + params.toString();
}

// Notification system
function showNotification(message, type = 'success') {
    // Remove any existing notifications
    const existingNotifications = document.querySelectorAll('.dynamic-notification');
    existingNotifications.forEach(notif => notif.remove());
    
    const notification = document.createElement('div');
    notification.className = 'dynamic-notification fixed top-4 right-4 z-50 max-w-md w-full';
    notification.style.opacity = '0';
    notification.style.transform = 'translateX(100%)';
    
    const bgColor = type === 'success' ? 'bg-green-50 border-green-200' : 'bg-red-50 border-red-200';
    const iconColor = type === 'success' ? 'text-green-400' : 'text-red-400';
    const textColor = type === 'success' ? 'text-green-800' : 'text-red-800';
    const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
    
    notification.innerHTML = `
        <div class="${bgColor} border rounded-lg p-4 shadow-lg">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <i class="fas ${icon} ${iconColor} text-lg"></i>
                </div>
                <div class="ml-3 flex-1">
                    <p class="text-sm font-medium ${textColor}">${message}</p>
                </div>
                <div class="ml-4 flex-shrink-0">
                    <button onclick="this.closest('.dynamic-notification').remove()" class="inline-flex text-gray-400 hover:text-gray-600 focus:outline-none transition-colors">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        </div>
    `;
    
    document.body.appendChild(notification);
    
    // Animate in
    setTimeout(() => {
        notification.style.transition = 'opacity 0.3s ease-out, transform 0.3s ease-out';
        notification.style.opacity = '1';
        notification.style.transform = 'translateX(0)';
    }, 10);
    
    // Auto-remove after 5 seconds
    setTimeout(() => {
        notification.style.transition = 'opacity 0.3s ease-out, transform 0.3s ease-out';
        notification.style.opacity = '0';
        notification.style.transform = 'translateX(100%)';
        setTimeout(() => notification.remove(), 300);
    }, 5000);
}

function updateLeaveStatus(leaveRequestId, status) {
    if (!confirm(`Are you sure you want to ${status} this leave request?`)) {
        return;
    }
    
    let rejectionReason = null;
    if (status === 'rejected') {
        rejectionReason = prompt('Please provide a reason for rejection:');
        if (!rejectionReason || rejectionReason.trim() === '') {
            showNotification('Rejection reason is required.', 'error');
            return;
        }
    }
    
    const url = '<?php echo e(route("attendance.leave-management.update-status", ["id" => ":id"])); ?>'.replace(':id', leaveRequestId);
    
    // Build request body - only include rejection_reason if rejecting
    const requestBody = {
        status: status
    };
    if (status === 'rejected' && rejectionReason) {
        requestBody.rejection_reason = rejectionReason;
    }
    
    fetch(url, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>',
            'Accept': 'application/json'
        },
        body: JSON.stringify(requestBody)
    })
    .then(async response => {
        // Check if response is JSON
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            const text = await response.text();
            throw new Error('Server returned non-JSON response. Status: ' + response.status);
        }
        
        const data = await response.json();
        
        // Check HTTP status code - handle validation errors specially
        if (!response.ok) {
            // For 422 validation errors, show detailed error messages
            if (response.status === 422 && data.errors) {
                const errorMessages = [];
                for (const field in data.errors) {
                    errorMessages.push(...data.errors[field]);
                }
                throw new Error(errorMessages.join(', ') || data.error || 'Validation failed');
            }
            throw new Error(data.error || data.message || 'Request failed with status ' + response.status);
        }
        
        return data;
    })
    .then(data => {
        if (data.success) {
            showNotification('Leave request ' + status + ' successfully', 'success');
            setTimeout(() => {
                location.reload();
            }, 1500);
        } else {
            showNotification(data.error || 'Failed to update leave request status.', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        const errorMessage = error.message || 'An error occurred while updating the leave request.';
        showNotification(errorMessage, 'error');
    });
}

function cancelLeaveRequest(leaveRequestId) {
    if (!confirm('Are you sure you want to cancel this leave request?')) {
        return;
    }
    
    const url = '<?php echo e(route("attendance.leave-management.cancel", ["id" => ":id"])); ?>'.replace(':id', leaveRequestId);
    
    fetch(url, {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Leave request cancelled successfully', 'success');
            setTimeout(() => {
                location.reload();
            }, 1500);
        } else {
            showNotification(data.error || 'Failed to cancel leave request.', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('An error occurred while cancelling the leave request.', 'error');
    });
}

// Event delegation for action buttons
document.addEventListener('DOMContentLoaded', function() {
    document.addEventListener('click', function(e) {
        const btn = e.target.closest('.time-action-btn');
        if (!btn) return;
        
        const leaveId = btn.getAttribute('data-leave-id');
        const action = btn.getAttribute('data-action');
        
        if (action === 'approve') {
            updateLeaveStatus(leaveId, 'approved');
        } else if (action === 'reject') {
            updateLeaveStatus(leaveId, 'rejected');
        } else if (action === 'cancel') {
            cancelLeaveRequest(leaveId);
        }
    });
    });
</script>

<!-- Set Leave Balance Modal -->
<div id="setLeaveBalanceModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-2/3 lg:w-1/2 shadow-lg rounded-md bg-white max-h-[90vh] overflow-y-auto">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">Set Leave Balance</h3>
                <button onclick="closeSetLeaveBalanceModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            <form id="setLeaveBalanceForm" class="space-y-4">
                <?php echo csrf_field(); ?>
                <input type="hidden" id="balanceYear" name="year" value="<?php echo e(now()->year); ?>">
                
                <div>
                    <label for="balanceEmployeeSelect" class="block text-sm font-medium text-gray-700 mb-2">Employee</label>
                    <select id="balanceEmployeeSelect" name="employee_id" required class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-white text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">Select Employee</option>
                        <option value="all">All Employees</option>
                        <?php $__currentLoopData = $employees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $emp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($emp->id); ?>"><?php echo e($emp->full_name); ?> - <?php echo e($emp->department->name ?? 'No Department'); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                    <p class="mt-1 text-xs text-gray-500">Select "All Employees" to set leave balance for all employees at once</p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Leave Types</label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <?php
                            $leaveTypes = [
                                'vacation' => ['label' => 'Vacation Leave', 'default' => 15],
                                'sick' => ['label' => 'Sick Leave', 'default' => 10],
                                'personal' => ['label' => 'Personal Leave', 'default' => 5],
                                'emergency' => ['label' => 'Emergency Leave', 'default' => 3],
                                'maternity' => ['label' => 'Maternity Leave', 'default' => 0],
                                'paternity' => ['label' => 'Paternity Leave', 'default' => 0],
                                'bereavement' => ['label' => 'Bereavement Leave', 'default' => 0],
                                'study' => ['label' => 'Study Leave', 'default' => 0],
                            ];
                        ?>
                        <?php $__currentLoopData = $leaveTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type => $config): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="border border-gray-200 rounded-lg p-3">
                            <label class="block text-sm font-medium text-gray-700 mb-1"><?php echo e($config['label']); ?></label>
                            <input type="number" 
                                   name="<?php echo e($type); ?>_days_total" 
                                   id="<?php echo e($type); ?>_days_total"
                                   min="0" 
                                   value="<?php echo e($config['default']); ?>"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-white text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                   placeholder="Days">
                        </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>
                
                <div class="flex justify-end space-x-3 pt-4 border-t">
                    <button type="button" onclick="closeSetLeaveBalanceModal()" 
                            class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
                        Cancel
                    </button>
                    <button type="submit" 
                            class="px-4 py-2 bg-blue-600 border border-transparent rounded-lg text-white hover:bg-blue-700 transition-colors">
                        <i class="fas fa-save mr-2"></i>Save Leave Balance
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Leave Balance Modal -->
<div id="editLeaveBalanceModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-2/3 lg:w-1/2 shadow-lg rounded-md bg-white max-h-[90vh] overflow-y-auto">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">Edit Leave Balance</h3>
                <button onclick="closeEditLeaveBalanceModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            <form id="editLeaveBalanceForm" class="space-y-4">
                <?php echo csrf_field(); ?>
                <input type="hidden" id="editBalanceId" name="balance_id">
                <input type="hidden" id="editBalanceEmployeeId" name="employee_id">
                <input type="hidden" id="editBalanceYear" name="year">
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Leave Types</label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <?php
                            $leaveTypes = [
                                'vacation' => ['label' => 'Vacation Leave'],
                                'sick' => ['label' => 'Sick Leave'],
                                'personal' => ['label' => 'Personal Leave'],
                                'emergency' => ['label' => 'Emergency Leave'],
                                'maternity' => ['label' => 'Maternity Leave'],
                                'paternity' => ['label' => 'Paternity Leave'],
                                'bereavement' => ['label' => 'Bereavement Leave'],
                                'study' => ['label' => 'Study Leave'],
                            ];
                        ?>
                        <?php $__currentLoopData = $leaveTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type => $config): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="border border-gray-200 rounded-lg p-3">
                            <label class="block text-sm font-medium text-gray-700 mb-1"><?php echo e($config['label']); ?></label>
                            <input type="number" 
                                   name="<?php echo e($type); ?>_days_total" 
                                   id="edit_<?php echo e($type); ?>_days_total"
                                   min="0" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-white text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                   placeholder="Days">
                        </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>
                
                <div class="flex justify-end space-x-3 pt-4 border-t">
                    <button type="button" onclick="closeEditLeaveBalanceModal()" 
                            class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
                        Cancel
                    </button>
                    <button type="submit" 
                            class="px-4 py-2 bg-blue-600 border border-transparent rounded-lg text-white hover:bg-blue-700 transition-colors">
                        <i class="fas fa-save mr-2"></i>Update Leave Balance
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openSetLeaveBalanceModal() {
    const selectedEmployeeId = document.getElementById('employee').value;
    if (selectedEmployeeId) {
        document.getElementById('balanceEmployeeSelect').value = selectedEmployeeId;
    }
    document.getElementById('setLeaveBalanceModal').classList.remove('hidden');
}

function closeSetLeaveBalanceModal() {
    document.getElementById('setLeaveBalanceModal').classList.add('hidden');
    document.getElementById('setLeaveBalanceForm').reset();
}

function openEditLeaveBalanceModal(employeeId) {
    fetch(`<?php echo e(route('attendance.leave-management.balance')); ?>?employee_id=${employeeId}&year=<?php echo e(now()->year); ?>`)
        .then(response => response.json())
        .then(data => {
            if (data.leave_balance) {
                const balance = data.leave_balance;
                document.getElementById('editBalanceId').value = balance.id;
                document.getElementById('editBalanceEmployeeId').value = balance.employee_id;
                document.getElementById('editBalanceYear').value = balance.year;
                
                document.getElementById('edit_vacation_days_total').value = balance.vacation_days_total || 0;
                document.getElementById('edit_sick_days_total').value = balance.sick_days_total || 0;
                document.getElementById('edit_personal_days_total').value = balance.personal_days_total || 0;
                document.getElementById('edit_emergency_days_total').value = balance.emergency_days_total || 0;
                document.getElementById('edit_maternity_days_total').value = balance.maternity_days_total || 0;
                document.getElementById('edit_paternity_days_total').value = balance.paternity_days_total || 0;
                document.getElementById('edit_bereavement_days_total').value = balance.bereavement_days_total || 0;
                document.getElementById('edit_study_days_total').value = balance.study_days_total || 0;
                
                document.getElementById('editLeaveBalanceModal').classList.remove('hidden');
            } else {
                showNotification('Leave balance not found', 'error');
            }
        })
        .catch(error => {
            console.error('Error fetching leave balance:', error);
            showNotification('Failed to load leave balance', 'error');
        });
}

function closeEditLeaveBalanceModal() {
    document.getElementById('editLeaveBalanceModal').classList.add('hidden');
}

document.getElementById('setLeaveBalanceForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const data = Object.fromEntries(formData);
    
    // Check if "All Employees" is selected
    if (data.employee_id === 'all') {
        if (!confirm('Are you sure you want to set leave balance for ALL employees? This will create/update leave balances for every employee.')) {
            return;
        }
    }
    
    const submitData = {
        employee_id: data.employee_id === 'all' ? 'all' : data.employee_id,
        year: parseInt(data.year),
        vacation_days_total: parseInt(data.vacation_days_total || 0),
        sick_days_total: parseInt(data.sick_days_total || 0),
        personal_days_total: parseInt(data.personal_days_total || 0),
        emergency_days_total: parseInt(data.emergency_days_total || 0),
        maternity_days_total: parseInt(data.maternity_days_total || 0),
        paternity_days_total: parseInt(data.paternity_days_total || 0),
        bereavement_days_total: parseInt(data.bereavement_days_total || 0),
        study_days_total: parseInt(data.study_days_total || 0),
    };
    
    try {
        const response = await fetch('<?php echo e(route("attendance.leave-management.balance.store")); ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>'
            },
            body: JSON.stringify(submitData)
        });
        
        const result = await response.json();
        
        if (response.ok) {
            showNotification(result.message || 'Leave balance set successfully', 'success');
            closeSetLeaveBalanceModal();
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            showNotification(result.error || 'Failed to set leave balance', 'error');
        }
    } catch (error) {
        console.error('Error setting leave balance:', error);
        showNotification('Failed to set leave balance', 'error');
    }
});

document.getElementById('editLeaveBalanceForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const data = Object.fromEntries(formData);
    
    const submitData = {
        vacation_days_total: parseInt(data.vacation_days_total || 0),
        sick_days_total: parseInt(data.sick_days_total || 0),
        personal_days_total: parseInt(data.personal_days_total || 0),
        emergency_days_total: parseInt(data.emergency_days_total || 0),
        maternity_days_total: parseInt(data.maternity_days_total || 0),
        paternity_days_total: parseInt(data.paternity_days_total || 0),
        bereavement_days_total: parseInt(data.bereavement_days_total || 0),
        study_days_total: parseInt(data.study_days_total || 0),
    };
    
    const balanceId = document.getElementById('editBalanceId').value;
    
    try {
        const response = await fetch(`<?php echo e(route("attendance.leave-management.balance.update", ":id")); ?>`.replace(':id', balanceId), {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>'
            },
            body: JSON.stringify(submitData)
        });
        
        const result = await response.json();
        
        if (response.ok) {
            showNotification(result.message || 'Leave balance updated successfully', 'success');
            closeEditLeaveBalanceModal();
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            showNotification(result.error || 'Failed to update leave balance', 'error');
        }
    } catch (error) {
        console.error('Error updating leave balance:', error);
        showNotification('Failed to update leave balance', 'error');
    }
});
</script>

<style>
    /* Ensure all form inputs and selects are visible with dark text */
    #employee,
    #leaveType,
    #status,
    #dateFrom {
        color: #111827 !important; /* text-gray-900 */
        background-color: #ffffff !important; /* bg-white */
    }
    
    /* Ensure select options are visible */
    select option {
        color: #111827 !important;
        background-color: #ffffff !important;
    }
    
    select option:checked {
        color: #111827 !important;
        background-color: #f3f4f6 !important;
    }
    
    select option:hover {
        background-color: #e5e7eb !important;
        color: #111827 !important;
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
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.dashboard-base', ['user' => $user, 'activeRoute' => 'attendance.leave-management'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\caior\Documents\Mica files\Aeternitas-System-V2\resources\views/attendance/leave-management.blade.php ENDPATH**/ ?>
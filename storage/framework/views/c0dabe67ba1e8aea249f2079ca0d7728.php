<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['user', 'activeRoute' => 'dashboard']));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter((['user', 'activeRoute' => 'dashboard']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $currentCompany = \App\Helpers\CompanyHelper::getCurrentCompany();
    
    // Check if employee is currently timed in (for employee role users)
    $isCurrentlyTimedIn = false;
    if ($user->role === 'employee' && $user->employee) {
        $todayAttendance = $user->employee->getTodayAttendance();
        $isCurrentlyTimedIn = $todayAttendance && $todayAttendance->time_in && !$todayAttendance->time_out;
    }
?>

<nav class="mt-8 px-4 pb-4">
    <div class="space-y-2">
        <!-- Dashboard -->
        <a href="<?php echo e(route('dashboard')); ?>" class="flex items-center px-4 py-3 text-sm font-medium <?php echo e($activeRoute === 'dashboard' ? 'text-blue-600 bg-blue-50 border-r-4 border-blue-600' : 'text-gray-700 hover:bg-gray-50 hover:text-blue-600'); ?> rounded-lg transition-all duration-200 group">
            <i class="fas fa-tachometer-alt mr-3 text-lg <?php echo e($activeRoute === 'dashboard' ? 'text-blue-600' : 'text-gray-400 group-hover:text-blue-600'); ?>"></i>
            <span>Dashboard</span>
        </a>
        
        <?php if($user->role === 'admin' || $user->role === 'hr'): ?>
        <!-- Employees -->
        <?php
            $employeeCount = $currentCompany 
                ? \App\Models\Employee::forCompany($currentCompany->id)->count() 
                : \App\Models\Employee::count();
        ?>
        <a href="<?php echo e(route('employees.index')); ?>" class="flex items-center px-4 py-3 text-sm font-medium <?php echo e($activeRoute === 'employees.index' ? 'text-blue-600 bg-blue-50 border-r-4 border-blue-600' : 'text-gray-700 hover:bg-gray-50 hover:text-blue-600'); ?> rounded-lg transition-all duration-200 group">
            <i class="fas fa-users mr-3 text-lg <?php echo e($activeRoute === 'employees.index' ? 'text-blue-600' : 'text-gray-400 group-hover:text-blue-600'); ?>"></i>
            <span>Employees</span>
            <span class="ml-auto bg-blue-100 text-blue-600 text-xs px-2 py-1 rounded-full"><?php echo e($employeeCount); ?></span>
        </a>
        
        <!-- Departments -->
        <?php
            $departmentCount = $currentCompany 
                ? \App\Models\Department::forCompany($currentCompany->id)->count() 
                : \App\Models\Department::count();
        ?>
        <a href="<?php echo e(route('departments.index')); ?>" class="flex items-center px-4 py-3 text-sm font-medium <?php echo e($activeRoute === 'departments.index' ? 'text-blue-600 bg-blue-50 border-r-4 border-blue-600' : 'text-gray-700 hover:bg-gray-50 hover:text-blue-600'); ?> rounded-lg transition-all duration-200 group">
            <i class="fas fa-building mr-3 text-lg <?php echo e($activeRoute === 'departments.index' ? 'text-blue-600' : 'text-gray-400 group-hover:text-blue-600'); ?>"></i>
            <span>Departments</span>
            <span class="ml-auto bg-blue-100 text-blue-600 text-xs px-2 py-1 rounded-full"><?php echo e($departmentCount); ?></span>
        </a>
        
        <!-- Positions -->
        <?php
            $positionCount = $currentCompany 
                ? \App\Models\Position::forCompany($currentCompany->id)->count() 
                : \App\Models\Position::count();
        ?>
        <a href="<?php echo e(route('positions.index')); ?>" class="flex items-center px-4 py-3 text-sm font-medium <?php echo e($activeRoute === 'positions.index' ? 'text-blue-600 bg-blue-50 border-r-4 border-blue-600' : 'text-gray-700 hover:bg-gray-50 hover:text-blue-600'); ?> rounded-lg transition-all duration-200 group">
            <i class="fas fa-briefcase mr-3 text-lg <?php echo e($activeRoute === 'positions.index' ? 'text-blue-600' : 'text-gray-400 group-hover:text-blue-600'); ?>"></i>
            <span>Positions</span>
            <span class="ml-auto bg-blue-100 text-blue-600 text-xs px-2 py-1 rounded-full"><?php echo e($positionCount); ?></span>
        </a>
        
        <!-- Companies -->
        <a href="<?php echo e(route('companies.index')); ?>" class="flex items-center px-4 py-3 text-sm font-medium <?php echo e($activeRoute === 'companies.index' ? 'text-blue-600 bg-blue-50 border-r-4 border-blue-600' : 'text-gray-700 hover:bg-gray-50 hover:text-blue-600'); ?> rounded-lg transition-all duration-200 group">
            <i class="fas fa-industry mr-3 text-lg <?php echo e($activeRoute === 'companies.index' ? 'text-blue-600' : 'text-gray-400 group-hover:text-blue-600'); ?>"></i>
            <span>Companies</span>
            <span class="ml-auto bg-blue-100 text-blue-600 text-xs px-2 py-1 rounded-full"><?php echo e(\App\Models\Company::count()); ?></span>
        </a>
        <?php endif; ?>
        
        <?php if($user->role === 'admin' || $user->role === 'hr' || $user->role === 'manager'): ?>
        <!-- Payroll -->
        <a href="<?php echo e(route('payroll.index')); ?>" class="flex items-center px-4 py-3 text-sm font-medium <?php echo e($activeRoute === 'payroll.index' ? 'text-blue-600 bg-blue-50 border-r-4 border-blue-600' : 'text-gray-700 hover:bg-gray-50 hover:text-blue-600'); ?> rounded-lg transition-all duration-200 group">
            <i class="fas fa-money-bill-wave mr-3 text-lg <?php echo e($activeRoute === 'payroll.index' ? 'text-blue-600' : 'text-gray-400 group-hover:text-blue-600'); ?>"></i>
            <span>Payroll</span>
            <span class="ml-auto bg-green-100 text-green-600 text-xs px-2 py-1 rounded-full">New</span>
        </a>
        <?php endif; ?>
        
        <!-- Attendance Dropdown -->
        <div class="relative" x-data="{ 
            open: <?php echo e(in_array($activeRoute, ['attendance.time-in-out', 'attendance.daily', 'attendance.timekeeping', 'attendance.import-dtr', 'schedule-v2.index', 'schedule-v2.create', 'schedule-v2.show', 'schedule-v2.edit', 'attendance.schedule.reports', 'attendance.schedule.templates', 'attendance.overtime', 'attendance.leave-management', 'attendance.reports', 'attendance.settings', 'attendance.period-management.index', 'attendance.period-management.create', 'attendance.period-management.show']) ? 'true' : 'false'); ?>

        }">
            <button @click="open = !open" class="w-full flex items-center justify-between px-4 py-3 text-sm font-medium <?php echo e(in_array($activeRoute, ['attendance.time-in-out', 'attendance.daily', 'attendance.timekeeping', 'attendance.import-dtr', 'schedule-v2.index', 'schedule-v2.create', 'schedule-v2.show', 'schedule-v2.edit', 'attendance.schedule.reports', 'attendance.schedule.templates', 'attendance.overtime', 'attendance.leave-management', 'attendance.reports', 'attendance.settings', 'attendance.period-management.index', 'attendance.period-management.create', 'attendance.period-management.show']) ? 'text-blue-600 bg-blue-50 border-r-4 border-blue-600' : 'text-gray-700 hover:bg-gray-50 hover:text-blue-600'); ?> rounded-lg transition-all duration-200 group">
                <div class="flex items-center">
                    <i class="fas fa-clock mr-3 text-lg <?php echo e(in_array($activeRoute, ['attendance.time-in-out', 'attendance.daily', 'attendance.timekeeping', 'attendance.import-dtr', 'schedule-v2.index', 'schedule-v2.create', 'schedule-v2.show', 'schedule-v2.edit', 'attendance.schedule.reports', 'attendance.schedule.templates', 'attendance.overtime', 'attendance.leave-management', 'attendance.reports', 'attendance.settings', 'attendance.period-management.index', 'attendance.period-management.create', 'attendance.period-management.show']) ? 'text-blue-600' : 'text-gray-400 group-hover:text-blue-600'); ?>"></i>
                    <span>Attendance</span>
                </div>
                <i class="fas fa-chevron-down text-xs text-gray-400 transition-transform duration-200" :class="{ 'rotate-180': open }"></i>
            </button>
            
            <!-- Dropdown Menu -->
            <div x-show="open" 
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 transform scale-95"
                 x-transition:enter-end="opacity-100 transform scale-100"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 transform scale-100"
                 x-transition:leave-end="opacity-0 transform scale-95"
                 class="ml-8 mt-2 space-y-1 bg-gray-50 rounded-lg p-2 border border-gray-200">
                
                <!-- Time In/Out - Only show for employees -->
                <?php if($user->role === 'employee'): ?>
                <?php
                    $timeInOutDisabled = !$isCurrentlyTimedIn;
                ?>
                <a href="<?php echo e($timeInOutDisabled ? '#' : route('attendance.time-in-out')); ?>" 
                   class="flex items-center px-3 py-2 text-sm <?php echo e($timeInOutDisabled ? 'text-gray-400 cursor-not-allowed' : 'text-gray-700 hover:bg-white hover:text-blue-600'); ?> rounded-md transition-all duration-200 group <?php echo e($activeRoute === 'attendance.time-in-out' ? 'bg-white text-blue-600' : ''); ?>"
                   <?php echo e($timeInOutDisabled ? 'onclick="return false;"' : ''); ?>>
                    <i class="fas fa-sign-in-alt mr-3 text-sm <?php echo e($timeInOutDisabled ? 'text-gray-300' : 'text-gray-400 group-hover:text-blue-600'); ?> <?php echo e($activeRoute === 'attendance.time-in-out' ? 'text-blue-600' : ''); ?>"></i>
                    <span>Time In/Out</span>
                    <span class="ml-auto bg-blue-100 text-blue-600 text-xs px-2 py-1 rounded-full">Live</span>
                </a>
                <?php endif; ?>
                
                <!-- Attendance Record -->
                <?php
                    $dailyDisabled = $user->role === 'employee' && !$isCurrentlyTimedIn;
                ?>
                <a href="<?php echo e($dailyDisabled ? '#' : route('attendance.daily')); ?>" 
                   class="flex items-center px-3 py-2 text-sm <?php echo e($dailyDisabled ? 'text-gray-400 cursor-not-allowed' : 'text-gray-700 hover:bg-white hover:text-blue-600'); ?> rounded-md transition-all duration-200 group <?php echo e($activeRoute === 'attendance.daily' ? 'bg-white text-blue-600' : ''); ?>"
                   <?php echo e($dailyDisabled ? 'onclick="return false;"' : ''); ?>>
                    <i class="fas fa-calendar-day mr-3 text-sm <?php echo e($dailyDisabled ? 'text-gray-300' : 'text-gray-400 group-hover:text-blue-600'); ?> <?php echo e($activeRoute === 'attendance.daily' ? 'text-blue-600' : ''); ?>"></i>
                    <span>Attendance Record</span>
                </a>
                
                <!-- Timekeeping -->
                <?php
                    $timekeepingDisabled = $user->role === 'employee' && !$isCurrentlyTimedIn;
                ?>
                <a href="<?php echo e($timekeepingDisabled ? '#' : route('attendance.timekeeping')); ?>" 
                   class="flex items-center px-3 py-2 text-sm <?php echo e($timekeepingDisabled ? 'text-gray-400 cursor-not-allowed' : 'text-gray-700 hover:bg-white hover:text-blue-600'); ?> rounded-md transition-all duration-200 group <?php echo e($activeRoute === 'attendance.timekeeping' ? 'bg-white text-blue-600' : ''); ?>"
                   <?php echo e($timekeepingDisabled ? 'onclick="return false;"' : ''); ?>>
                    <i class="fas fa-stopwatch mr-3 text-sm <?php echo e($timekeepingDisabled ? 'text-gray-300' : 'text-gray-400 group-hover:text-blue-600'); ?> <?php echo e($activeRoute === 'attendance.timekeeping' ? 'text-blue-600' : ''); ?>"></i>
                    <span>Timekeeping</span>
                </a>
                
                <!-- Import DTR -->
                <?php
                    $importDtrDisabled = $user->role === 'employee' && !$isCurrentlyTimedIn;
                ?>
                <a href="<?php echo e($importDtrDisabled ? '#' : route('attendance.import-dtr')); ?>" 
                   class="flex items-center px-3 py-2 text-sm <?php echo e($importDtrDisabled ? 'text-gray-400 cursor-not-allowed' : 'text-gray-700 hover:bg-white hover:text-blue-600'); ?> rounded-md transition-all duration-200 group <?php echo e($activeRoute === 'attendance.import-dtr' ? 'bg-white text-blue-600' : ''); ?>"
                   <?php echo e($importDtrDisabled ? 'onclick="return false;"' : ''); ?>>
                    <i class="fas fa-file-import mr-3 text-sm <?php echo e($importDtrDisabled ? 'text-gray-300' : 'text-gray-400 group-hover:text-blue-600'); ?> <?php echo e($activeRoute === 'attendance.import-dtr' ? 'text-blue-600' : ''); ?>"></i>
                    <span>Import DTR</span>
                    <span class="ml-auto bg-orange-100 text-orange-600 text-xs px-2 py-1 rounded-full">New</span>
                </a>
                
                <!-- Schedule Management -->
                <?php if($user->role !== 'employee'): ?>
                <?php
                    $scheduleDisabled = $user->role === 'employee' && !$isCurrentlyTimedIn;
                ?>
                <a href="<?php echo e($scheduleDisabled ? '#' : route('schedule-v2.index')); ?>" 
                   class="flex items-center px-3 py-2 text-sm <?php echo e($scheduleDisabled ? 'text-gray-400 cursor-not-allowed' : 'text-gray-700 hover:bg-white hover:text-blue-600'); ?> rounded-md transition-all duration-200 group <?php echo e($activeRoute === 'schedule-v2.index' ? 'bg-white text-blue-600' : ''); ?>"
                   <?php echo e($scheduleDisabled ? 'onclick="return false;"' : ''); ?>>
                    <i class="fas fa-calendar-plus mr-3 text-sm <?php echo e($scheduleDisabled ? 'text-gray-300' : 'text-gray-400 group-hover:text-blue-600'); ?> <?php echo e($activeRoute === 'schedule-v2.index' ? 'text-blue-600' : ''); ?>"></i>
                    <span>Schedule Management</span>
                </a>
                <?php endif; ?>
                
                <!-- Overtime -->
                <?php
                    $overtimeDisabled = $user->role === 'employee' && !$isCurrentlyTimedIn;
                ?>
                <a href="<?php echo e($overtimeDisabled ? '#' : route('attendance.overtime')); ?>" 
                   class="flex items-center px-3 py-2 text-sm <?php echo e($overtimeDisabled ? 'text-gray-400 cursor-not-allowed' : 'text-gray-700 hover:bg-white hover:text-blue-600'); ?> rounded-md transition-all duration-200 group <?php echo e($activeRoute === 'attendance.overtime' ? 'bg-white text-blue-600' : ''); ?>"
                   <?php echo e($overtimeDisabled ? 'onclick="return false;"' : ''); ?>>
                    <i class="fas fa-clock mr-3 text-sm <?php echo e($overtimeDisabled ? 'text-gray-300' : 'text-gray-400 group-hover:text-blue-600'); ?> <?php echo e($activeRoute === 'attendance.overtime' ? 'text-blue-600' : ''); ?>"></i>
                    <span>Overtime</span>
                </a>
                
                <!-- Leave Management -->
                <?php
                    $leaveDisabled = $user->role === 'employee' && !$isCurrentlyTimedIn;
                ?>
                <a href="<?php echo e($leaveDisabled ? '#' : route('attendance.leave-management')); ?>" 
                   class="flex items-center px-3 py-2 text-sm <?php echo e($leaveDisabled ? 'text-gray-400 cursor-not-allowed' : 'text-gray-700 hover:bg-white hover:text-blue-600'); ?> rounded-md transition-all duration-200 group <?php echo e($activeRoute === 'attendance.leave-management' ? 'bg-white text-blue-600' : ''); ?>"
                   <?php echo e($leaveDisabled ? 'onclick="return false;"' : ''); ?>>
                    <i class="fas fa-calendar-times mr-3 text-sm <?php echo e($leaveDisabled ? 'text-gray-300' : 'text-gray-400 group-hover:text-blue-600'); ?> <?php echo e($activeRoute === 'attendance.leave-management' ? 'text-blue-600' : ''); ?>"></i>
                    <span>Leave Management</span>
                </a>
                
                <!-- Period Management -->
                <a href="<?php echo e(route('attendance.period-management.index')); ?>" 
                   class="flex items-center px-3 py-2 text-sm text-gray-700 hover:bg-white hover:text-blue-600 rounded-md transition-all duration-200 group <?php echo e($activeRoute === 'attendance.period-management.index' ? 'bg-white text-blue-600' : ''); ?>">
                    <i class="fas fa-calendar-week mr-3 text-sm text-gray-400 group-hover:text-blue-600 <?php echo e($activeRoute === 'attendance.period-management.index' ? 'text-blue-600' : ''); ?>"></i>
                    <span>Period Management</span>
                    <?php if($user->role !== 'employee'): ?>
                    <span class="ml-auto bg-purple-100 text-purple-600 text-xs px-2 py-1 rounded-full">New</span>
                    <?php endif; ?>
                </a>
                
                <?php if($user->role === 'admin' || $user->role === 'hr'): ?>
                <!-- Attendance Reports -->
                <div class="border-t border-gray-200 my-2"></div>
                <a href="<?php echo e(route('attendance.reports')); ?>" class="flex items-center px-3 py-2 text-sm text-gray-700 hover:bg-white hover:text-blue-600 rounded-md transition-all duration-200 group <?php echo e($activeRoute === 'attendance.reports' ? 'bg-white text-blue-600' : ''); ?>">
                    <i class="fas fa-chart-line mr-3 text-sm text-gray-400 group-hover:text-blue-600 <?php echo e($activeRoute === 'attendance.reports' ? 'text-blue-600' : ''); ?>"></i>
                    <span>Attendance Reports</span>
                </a>
                
                <!-- Attendance Settings -->
                <a href="<?php echo e(route('attendance.settings')); ?>" class="flex items-center px-3 py-2 text-sm text-gray-700 hover:bg-white hover:text-blue-600 rounded-md transition-all duration-200 group <?php echo e($activeRoute === 'attendance.settings' ? 'bg-white text-blue-600' : ''); ?>">
                    <i class="fas fa-cog mr-3 text-sm text-gray-400 group-hover:text-blue-600 <?php echo e($activeRoute === 'attendance.settings' ? 'text-blue-600' : ''); ?>"></i>
                    <span>Attendance Settings</span>
                </a>
                <?php endif; ?>
            </div>
        </div>
        
        <?php if($user->role === 'admin' || $user->role === 'hr'): ?>
        <!-- Tax Brackets -->
        <a href="<?php echo e(route('tax-brackets.index')); ?>" class="flex items-center px-4 py-3 text-sm font-medium <?php echo e($activeRoute === 'tax-brackets.index' ? 'text-blue-600 bg-blue-50 border-r-4 border-blue-600' : 'text-gray-700 hover:bg-gray-50 hover:text-blue-600'); ?> rounded-lg transition-all duration-200 group">
            <i class="fas fa-percentage mr-3 text-lg <?php echo e($activeRoute === 'tax-brackets.index' ? 'text-blue-600' : 'text-gray-400 group-hover:text-blue-600'); ?>"></i>
            <span>Tax Brackets</span>
            <span class="ml-auto bg-green-100 text-green-600 text-xs px-2 py-1 rounded-full">New</span>
        </a>
        
        <!-- Reports -->
        <a href="#" class="flex items-center px-4 py-3 text-sm font-medium text-gray-700 hover:bg-gray-50 hover:text-blue-600 rounded-lg transition-all duration-200 group">
            <i class="fas fa-chart-bar mr-3 text-lg text-gray-400 group-hover:text-blue-600"></i>
            <span>Reports</span>
        </a>
        <?php endif; ?>
        
        <?php if($user->role === 'admin'): ?>
        <!-- Settings -->
        <a href="#" class="flex items-center px-4 py-3 text-sm font-medium text-gray-700 hover:bg-gray-50 hover:text-blue-600 rounded-lg transition-all duration-200 group">
            <i class="fas fa-cog mr-3 text-lg text-gray-400 group-hover:text-blue-600"></i>
            <span>Settings</span>
        </a>
        <?php endif; ?>
        
        <!-- Divider -->
        <div class="my-6 border-t border-gray-200"></div>
        
        <!-- Quick Actions -->
        <div class="px-4 mb-2">
            <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Quick Actions</h3>
        </div>
        
        <?php if($user->role === 'admin' || $user->role === 'hr'): ?>
        <a href="<?php echo e(route('employees.create')); ?>" class="flex items-center px-4 py-3 text-sm font-medium text-gray-700 hover:bg-gray-50 hover:text-blue-600 rounded-lg transition-all duration-200 group">
            <i class="fas fa-user-plus mr-3 text-lg text-gray-400 group-hover:text-blue-600"></i>
            <span>Add Employee</span>
        </a>
        
        <a href="#" class="flex items-center px-4 py-3 text-sm font-medium text-gray-700 hover:bg-gray-50 hover:text-blue-600 rounded-lg transition-all duration-200 group">
            <i class="fas fa-file-export mr-3 text-lg text-gray-400 group-hover:text-blue-600"></i>
            <span>Export Data</span>
        </a>
        <?php endif; ?>
        
        <?php if($user->role === 'employee'): ?>
        <!-- Time In Button -->
        <?php if(!isset($todayAttendance) || !$todayAttendance || !$todayAttendance->time_in || $todayAttendance->time_out): ?>
        <button onclick="sidebarTimeIn()" class="w-full flex items-center px-4 py-3 text-sm font-medium text-gray-700 hover:bg-gray-50 hover:text-green-600 rounded-lg transition-all duration-200 group">
            <i class="fas fa-sign-in-alt mr-3 text-lg text-gray-400 group-hover:text-green-600"></i>
            <span>Time In</span>
        </button>
        <?php else: ?>
        <div class="flex items-center px-4 py-3 text-sm font-medium text-gray-400 rounded-lg cursor-not-allowed">
            <i class="fas fa-check mr-3 text-lg text-gray-400"></i>
            <span>Already Clocked In</span>
        </div>
        <?php endif; ?>

        <!-- Time Out Button -->
        <?php if($todayAttendance && $todayAttendance->time_in && !$todayAttendance->time_out): ?>
        <button onclick="sidebarTimeOut()" class="w-full flex items-center px-4 py-3 text-sm font-medium text-gray-700 hover:bg-gray-50 hover:text-red-600 rounded-lg transition-all duration-200 group">
            <i class="fas fa-sign-out-alt mr-3 text-lg text-gray-400 group-hover:text-red-600"></i>
            <span>Time Out</span>
        </button>
        <?php elseif($todayAttendance && $todayAttendance->time_out): ?>
        <div class="flex items-center px-4 py-3 text-sm font-medium text-gray-400 rounded-lg cursor-not-allowed">
            <i class="fas fa-check mr-3 text-lg text-gray-400"></i>
            <span>Already Clocked Out</span>
        </div>
        <?php else: ?>
        <div class="flex items-center px-4 py-3 text-sm font-medium text-gray-400 rounded-lg cursor-not-allowed">
            <i class="fas fa-sign-out-alt mr-3 text-lg text-gray-400"></i>
            <span>Time Out (Clock In First)</span>
        </div>
        <?php endif; ?>
        
        <a href="#" class="flex items-center px-4 py-3 text-sm font-medium text-gray-700 hover:bg-gray-50 hover:text-blue-600 rounded-lg transition-all duration-200 group">
            <i class="fas fa-download mr-3 text-lg text-gray-400 group-hover:text-blue-600"></i>
            <span>Download Payslip</span>
        </a>
        
        <a href="<?php echo e(route('hr.profile')); ?>" class="flex items-center px-4 py-3 text-sm font-medium text-gray-700 hover:bg-gray-50 hover:text-blue-600 rounded-lg transition-all duration-200 group">
            <i class="fas fa-edit mr-3 text-lg text-gray-400 group-hover:text-blue-600"></i>
            <span>Update Profile</span>
        </a>
        <?php endif; ?>
        
        <!-- Additional test items to ensure scrolling -->
        <?php if($user->role === 'admin' || $user->role === 'hr'): ?>
        <!-- Documents -->
        <a href="<?php echo e(route('documents.index')); ?>" class="flex items-center px-4 py-3 text-sm font-medium <?php echo e(request()->routeIs('documents.*') ? 'text-blue-600 bg-blue-50 border-r-4 border-blue-600' : 'text-gray-700 hover:bg-gray-50 hover:text-blue-600'); ?> rounded-lg transition-all duration-200 group">
            <i class="fas fa-folder mr-3 text-lg <?php echo e(request()->routeIs('documents.*') ? 'text-blue-600' : 'text-gray-400 group-hover:text-blue-600'); ?>"></i>
            <span>Documents</span>
        </a>

        <a href="<?php echo e(route('attendance.leave-management.create')); ?>" class="flex items-center px-4 py-3 text-sm font-medium text-gray-700 hover:bg-gray-50 hover:text-blue-600 rounded-lg transition-all duration-200 group">
            <i class="fas fa-calendar-check mr-3 text-lg text-gray-400 group-hover:text-blue-600"></i>
            <span>Leave Requests</span>
        </a>

        <a href="#" class="flex items-center px-4 py-3 text-sm font-medium text-gray-700 hover:bg-gray-50 hover:text-blue-600 rounded-lg transition-all duration-200 group">
            <i class="fas fa-user-friends mr-3 text-lg text-gray-400 group-hover:text-blue-600"></i>
            <span>Team Directory</span>
        </a>

        <a href="#" class="flex items-center px-4 py-3 text-sm font-medium text-gray-700 hover:bg-gray-50 hover:text-blue-600 rounded-lg transition-all duration-200 group">
            <i class="fas fa-bell mr-3 text-lg text-gray-400 group-hover:text-blue-600"></i>
            <span>Notifications</span>
        </a>

        <a href="#" class="flex items-center px-4 py-3 text-sm font-medium text-gray-700 hover:bg-gray-50 hover:text-blue-600 rounded-lg transition-all duration-200 group">
            <i class="fas fa-question-circle mr-3 text-lg text-gray-400 group-hover:text-blue-600"></i>
            <span>Help & Support</span>
        </a>
        <?php endif; ?>
    </div>
</nav>
<?php /**PATH C:\xampp\htdocs\Aeternitas-System-V2\resources\views/components/dashboard/sidebar/navigation.blade.php ENDPATH**/ ?>
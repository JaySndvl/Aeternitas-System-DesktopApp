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
    $todayAttendance = null;
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
        <!-- Employees Dropdown -->
        <?php
            $employeeCount = $currentCompany 
                ? \App\Models\Employee::forCompany($currentCompany->id)->count() 
                : \App\Models\Employee::count();
        ?>
        <div class="relative group">
            <button id="employeeMenuBtn" class="w-full flex items-center px-4 py-3 text-sm font-medium text-gray-700 hover:bg-gray-50 hover:text-blue-600 rounded-lg transition-all duration-200 group">
                <i class="fas fa-users mr-3 text-lg text-gray-400 group-hover:text-blue-600"></i>
                <span>Employees</span>
                <i class="fas fa-chevron-down ml-auto text-xs text-gray-400 group-hover:text-blue-600 transition-transform duration-200" id="employeeChevron"></i>
                <span class="ml-2 bg-blue-100 text-blue-600 text-xs px-2 py-1 rounded-full"><?php echo e($employeeCount); ?></span>
            </button>

            <!-- Employees Dropdown Menu -->
            <div id="employeeSubMenu" class="hidden absolute left-0 mt-0 w-64 bg-white border border-gray-200 rounded-lg shadow-xl z-50 py-2">
                <a href="<?php echo e(route('employees.index')); ?>" class="flex items-center px-4 py-2.5 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors">
                    <i class="fas fa-list mr-3 text-gray-400"></i>
                    <span>Employee List</span>
                </a>
                <a href="#" class="flex items-center px-4 py-2.5 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors">
                    <i class="fas fa-circle text-red-500 mr-2 text-xs"></i>
                    <i class="fas fa-id-card mr-3 text-gray-400"></i>
                    <span>Employee Info</span>
                </a>
                <a href="<?php echo e(route('employees.other-employee-info')); ?>" class="flex items-center px-4 py-2.5 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors <?php echo e($activeRoute === 'employees.other-employee-info' ? 'bg-blue-50 text-blue-600' : ''); ?>">
                    <i class="fas fa-user-circle mr-3 text-gray-400"></i>
                    <span>Other Employee Info</span>
                </a>
                <a href="<?php echo e(route('employees.education-training-rating')); ?>" class="flex items-center px-4 py-2.5 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors <?php echo e($activeRoute === 'employees.education-training-rating' ? 'bg-blue-50 text-blue-600' : ''); ?>">
                    <i class="fas fa-book mr-3 text-gray-400"></i>
                    <span>Education/Training/Rating</span>
                </a>
                <a href="<?php echo e(route('employees.prev-emp-oth')); ?>" class="flex items-center px-4 py-2.5 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors <?php echo e($activeRoute === 'employees.prev-emp-oth' ? 'bg-blue-50 text-blue-600' : ''); ?>">
                    <i class="fas fa-briefcase mr-3 text-gray-400"></i>
                    <span>Previous Employer & Other</span>
                </a>
                <a href="#" class="flex items-center px-4 py-2.5 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors">
                    <i class="fas fa-circle text-red-500 mr-2 text-xs"></i>
                    <i class="fas fa-file-alt mr-3 text-gray-400"></i>
                    <span>Documents</span>
                </a>
                <a href="<?php echo e(route('employees.ytd-info')); ?>" class="flex items-center px-4 py-2.5 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors <?php echo e($activeRoute === 'employees.ytd-info' ? 'bg-blue-50 text-blue-600' : ''); ?>">
                    <i class="fas fa-passport mr-3 text-gray-400"></i>
                    <span>YTD - INFO</span>
                </a>
                <a href="<?php echo e(route('employees.bio-zk')); ?>" class="flex items-center px-4 py-2.5 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors <?php echo e($activeRoute === 'employees.bio-zk' ? 'bg-blue-50 text-blue-600' : ''); ?>">
                    <i class="fas fa-dna mr-3 text-gray-400"></i>
                    <span>Bio ZK</span>
                </a>
            </div>
        </div>
        
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
                <!-- Timekeeping and HRIS Reports -->
                <div class="border-t border-gray-200 my-2"></div>
                <div class="relative group timekeeping-report-group">
                    <button class="timekeepingReportBtn w-full flex items-center px-3 py-2 text-sm text-gray-700 hover:bg-white hover:text-blue-600 rounded-md transition-all duration-200 group">
                        <i class="fas fa-file-chart mr-3 text-sm text-gray-400 group-hover:text-blue-600"></i>
                        <span>Timekeeping & HRIS Reports</span>
                        <i class="fas fa-chevron-down ml-auto text-xs text-gray-400 group-hover:text-blue-600"></i>
                    </button>
                    
                    <!-- Timekeeping Reports Submenu -->
                    <div class="timekeepingReportSubMenu hidden absolute left-0 top-full mt-1 w-64 bg-white border border-gray-200 rounded-lg shadow-xl z-50 py-2 max-h-96 overflow-y-auto">
                        <a href="#" class="flex items-center px-4 py-2.5 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors">
                            <i class="fas fa-circle text-red-500 mr-2 text-xs"></i>
                            <i class="fas fa-hourglass-half mr-3 text-gray-400"></i>
                            <span>Time Summary</span>
                        </a>
                        <a href="#" class="flex items-center px-4 py-2.5 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors">
                            <i class="fas fa-circle text-red-500 mr-2 text-xs"></i>
                            <i class="fas fa-id-card mr-3 text-gray-400"></i>
                            <span>Time Card</span>
                        </a>
                        <a href="#" class="flex items-center px-4 py-2.5 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors">
                            <i class="fas fa-circle text-red-500 mr-2 text-xs"></i>
                            <i class="fas fa-list mr-3 text-gray-400"></i>
                            <span>Time Sheets</span>
                        </a>
                        <a href="#" class="flex items-center px-4 py-2.5 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors">
                            <i class="fas fa-circle text-red-500 mr-2 text-xs"></i>
                            <i class="fas fa-user-slash mr-3 text-gray-400"></i>
                            <span>Absences</span>
                        </a>
                        <a href="#" class="flex items-center px-4 py-2.5 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors">
                            <i class="fas fa-circle text-red-500 mr-2 text-xs"></i>
                            <i class="fas fa-exclamation-triangle mr-3 text-gray-400"></i>
                            <span>Undertime & Tardiness</span>
                        </a>
                        <a href="#" class="flex items-center px-4 py-2.5 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors">
                            <i class="fas fa-circle text-red-500 mr-2 text-xs"></i>
                            <i class="fas fa-chart-line mr-3 text-gray-400"></i>
                            <span>Overtime Report</span>
                        </a>
                        <a href="#" class="flex items-center px-4 py-2.5 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors">
                            <i class="fas fa-circle text-red-500 mr-2 text-xs"></i>
                            <i class="fas fa-users mr-3 text-gray-400"></i>
                            <span>Employee Reports</span>
                        </a>
                        <a href="#" class="flex items-center px-4 py-2.5 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors">
                            <i class="fas fa-circle text-red-500 mr-2 text-xs"></i>
                            <i class="fas fa-calendar-check mr-3 text-gray-400"></i>
                            <span>Balance of Leaves</span>
                        </a>
                        <a href="#" class="flex items-center px-4 py-2.5 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors">
                            <i class="fas fa-circle text-red-500 mr-2 text-xs"></i>
                            <i class="fas fa-file-contract mr-3 text-gray-400"></i>
                            <span>Employee Filings</span>
                        </a>
                        <a href="#" class="flex items-center px-4 py-2.5 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors">
                            <i class="fas fa-circle text-red-500 mr-2 text-xs"></i>
                            <i class="fas fa-birthday-cake mr-3 text-gray-400"></i>
                            <span>Birthdays per Month</span>
                        </a>
                        <a href="#" class="flex items-center px-4 py-2.5 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors">
                            <i class="fas fa-circle text-red-500 mr-2 text-xs"></i>
                            <i class="fas fa-user-plus mr-3 text-gray-400"></i>
                            <span>New Employees</span>
                        </a>
                        <a href="#" class="flex items-center px-4 py-2.5 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors">
                            <i class="fas fa-circle text-red-500 mr-2 text-xs"></i>
                            <i class="fas fa-hourglass-end mr-3 text-gray-400"></i>
                            <span>End of Contracts</span>
                        </a>
                        <a href="#" class="flex items-center px-4 py-2.5 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors">
                            <i class="fas fa-circle text-red-500 mr-2 text-xs"></i>
                            <i class="fas fa-star mr-3 text-gray-400"></i>
                            <span>Perfect Attendance</span>
                        </a>
                        <a href="#" class="flex items-center px-4 py-2.5 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors">
                            <i class="fas fa-circle text-red-500 mr-2 text-xs"></i>
                            <i class="fas fa-barcode mr-3 text-gray-400"></i>
                            <span>ID with Barcode</span>
                        </a>
                        <a href="#" class="flex items-center px-4 py-2.5 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors">
                            <i class="fas fa-circle text-red-500 mr-2 text-xs"></i>
                            <i class="fas fa-chart-star mr-3 text-gray-400"></i>
                            <span>Performance Evaluation</span>
                        </a>
                        <a href="#" class="flex items-center px-4 py-2.5 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors">
                            <i class="fas fa-circle text-red-500 mr-2 text-xs"></i>
                            <i class="fas fa-certificate mr-3 text-gray-400"></i>
                            <span>Employment Certificates</span>
                        </a>
                        <a href="#" class="flex items-center px-4 py-2.5 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors">
                            <i class="fas fa-circle text-red-500 mr-2 text-xs"></i>
                            <i class="fas fa-gavel mr-3 text-gray-400"></i>
                            <span>Employee Offences</span>
                        </a>
                        <a href="#" class="flex items-center px-4 py-2.5 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors">
                            <i class="fas fa-circle text-red-500 mr-2 text-xs"></i>
                            <i class="fas fa-globe mr-3 text-gray-400"></i>
                            <span>Print HRIS Web Groups</span>
                        </a>
                        <a href="#" class="flex items-center px-4 py-2.5 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors">
                            <i class="fas fa-circle text-red-500 mr-2 text-xs"></i>
                            <i class="fas fa-shuttle-van mr-3 text-gray-400"></i>
                            <span>Shuttle List</span>
                        </a>
                        <a href="#" class="flex items-center px-4 py-2.5 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors">
                            <i class="fas fa-circle text-red-500 mr-2 text-xs"></i>
                            <i class="fas fa-sync mr-3 text-gray-400"></i>
                            <span>Generated Time Summary</span>
                        </a>
                        <a href="#" class="flex items-center px-4 py-2.5 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors">
                            <i class="fas fa-circle text-red-500 mr-2 text-xs"></i>
                            <i class="fas fa-print mr-3 text-gray-400"></i>
                            <span>Print Training</span>
                        </a>
                        <a href="#" class="flex items-center px-4 py-2.5 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors">
                            <i class="fas fa-circle text-red-500 mr-2 text-xs"></i>
                            <i class="fas fa-people-arrows mr-3 text-gray-400"></i>
                            <span>Print Manpower Movement</span>
                        </a>
                    </div>
                </div>
                
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
        
        <!-- Reports Dropdown -->
        <div class="relative group">
            <button id="reportMenuBtn" class="w-full flex items-center px-4 py-3 text-sm font-medium text-gray-700 hover:bg-gray-50 hover:text-blue-600 rounded-lg transition-all duration-200 group">
                <i class="fas fa-chart-bar mr-3 text-lg text-gray-400 group-hover:text-blue-600"></i>
                <span>Reports</span>
                <i class="fas fa-chevron-down ml-auto text-xs text-gray-400 group-hover:text-blue-600 transition-transform duration-200" id="reportChevron"></i>
            </button>

            <!-- Reports Dropdown Menu -->
            <div id="reportSubMenu" class="hidden absolute left-0 mt-0 w-72 bg-white border border-gray-200 rounded-lg shadow-xl z-50 py-2 max-h-96 overflow-y-auto">
                <a href="<?php echo e(route('attendance.reports')); ?>" class="flex items-center px-4 py-2.5 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors">
                    <i class="fas fa-hourglass-half mr-3 text-gray-400"></i>
                    <span>Time Summary</span>
                </a>
                <a href="<?php echo e(route('payroll.index')); ?>" class="flex items-center px-4 py-2.5 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors">
                    <i class="fas fa-calculator mr-3 text-gray-400"></i>
                    <span>Payroll Summary</span>
                </a>
                <a href="#" class="flex items-center px-4 py-2.5 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors">
                    <i class="fas fa-circle text-red-500 mr-2 text-xs"></i>
                    <i class="fas fa-receipt mr-3 text-gray-400"></i>
                    <span>Payslips</span>
                </a>
                <a href="#" class="flex items-center px-4 py-2.5 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors">
                    <i class="fas fa-circle text-red-500 mr-2 text-xs"></i>
                    <i class="fas fa-university mr-3 text-gray-400"></i>
                    <span>Bank Remittance</span>
                </a>
                <a href="#" class="flex items-center px-4 py-2.5 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors">
                    <i class="fas fa-circle text-red-500 mr-2 text-xs"></i>
                    <i class="fas fa-coins mr-3 text-gray-400"></i>
                    <span>Denominations</span>
                </a>
                <a href="#" class="flex items-center px-4 py-2.5 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors">
                    <i class="fas fa-circle text-red-500 mr-2 text-xs"></i>
                    <i class="fas fa-list mr-3 text-gray-400"></i>
                    <span>Received List</span>
                </a>
                <a href="#" class="flex items-center px-4 py-2.5 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors">
                    <i class="fas fa-circle text-red-500 mr-2 text-xs"></i>
                    <i class="fas fa-gift mr-3 text-gray-400"></i>
                    <span>Allowances</span>
                </a>
                <a href="#" class="flex items-center px-4 py-2.5 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors">
                    <i class="fas fa-circle text-red-500 mr-2 text-xs"></i>
                    <i class="fas fa-star mr-3 text-gray-400"></i>
                    <span>Allowance Special Report</span>
                </a>
                <a href="#" class="flex items-center px-4 py-2.5 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors">
                    <i class="fas fa-circle text-red-500 mr-2 text-xs"></i>
                    <i class="fas fa-piggy-bank mr-3 text-gray-400"></i>
                    <span>Loan Balances</span>
                </a>
                <a href="#" class="flex items-center px-4 py-2.5 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors">
                    <i class="fas fa-circle text-red-500 mr-2 text-xs"></i>
                    <i class="fas fa-book mr-3 text-gray-400"></i>
                    <span>Deduction Register</span>
                </a>
                <a href="#" class="flex items-center px-4 py-2.5 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors">
                    <i class="fas fa-circle text-red-500 mr-2 text-xs"></i>
                    <i class="fas fa-file-pdf mr-3 text-gray-400"></i>
                    <span>SSS Report</span>
                </a>
                <a href="#" class="flex items-center px-4 py-2.5 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors">
                    <i class="fas fa-circle text-red-500 mr-2 text-xs"></i>
                    <i class="fas fa-heartbeat mr-3 text-gray-400"></i>
                    <span>Philhealth Report & RF-1</span>
                </a>
                <a href="#" class="flex items-center px-4 py-2.5 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors">
                    <i class="fas fa-circle text-red-500 mr-2 text-xs"></i>
                    <i class="fas fa-percent mr-3 text-gray-400"></i>
                    <span>Tax Report</span>
                </a>
                <a href="#" class="flex items-center px-4 py-2.5 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors">
                    <i class="fas fa-circle text-red-500 mr-2 text-xs"></i>
                    <i class="fas fa-shield-alt mr-3 text-gray-400"></i>
                    <span>Pag Ibig Report</span>
                </a>
                <a href="#" class="flex items-center px-4 py-2.5 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors">
                    <i class="fas fa-circle text-red-500 mr-2 text-xs"></i>
                    <i class="fas fa-ledger mr-3 text-gray-400"></i>
                    <span>Account Entries</span>
                </a>
                <a href="#" class="flex items-center px-4 py-2.5 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors">
                    <i class="fas fa-circle text-red-500 mr-2 text-xs"></i>
                    <i class="fas fa-file-alt mr-3 text-gray-400"></i>
                    <span>Text File Reports</span>
                </a>
                <a href="#" class="flex items-center px-4 py-2.5 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors">
                    <i class="fas fa-circle text-red-500 mr-2 text-xs"></i>
                    <i class="fas fa-print mr-3 text-gray-400"></i>
                    <span>Print Undeducted Items</span>
                </a>
            </div>
        </div>
        <?php endif; ?>
        
        <?php if($user->role === 'admin'): ?>
        <!-- Settings -->
        <a href="<?php echo e(route('hr.settings')); ?>" class="flex items-center px-4 py-3 text-sm font-medium <?php echo e($activeRoute === 'hr.settings' ? 'text-blue-600 bg-blue-50 border-r-4 border-blue-600' : 'text-gray-700 hover:bg-gray-50 hover:text-blue-600'); ?> rounded-lg transition-all duration-200 group">
            <i class="fas fa-cog mr-3 text-lg <?php echo e($activeRoute === 'hr.settings' ? 'text-blue-600' : 'text-gray-400 group-hover:text-blue-600'); ?>"></i>
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
            <?php if(!$todayAttendance || !$todayAttendance->time_in || $todayAttendance->time_out): ?>
                <button onclick="sidebarConfirmTimeIn()" class="w-full flex items-center px-4 py-3 text-sm font-medium text-gray-700 hover:bg-gray-50 hover:text-green-600 rounded-lg transition-all duration-200 group">
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
                <button onclick="sidebarConfirmTimeOut()" class="w-full flex items-center px-4 py-3 text-sm font-medium text-gray-700 hover:bg-gray-50 hover:text-red-600 rounded-lg transition-all duration-200 group">
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

            <!-- Payslip Download Button for Employees -->
            <?php
                // Simple check - if we can't query database, just show disabled button
                $latestPayroll = null;
                try {
                    if (isset($user->employee) && $user->employee) {
                        $latestPayroll = \App\Models\Payroll::where('employee_id', $user->employee->id)
                            ->whereIn('status', ['approved', 'processed', 'paid'])
                            ->latest()
                            ->first();
                    }
                } catch (\Exception $e) {
                    // If there's an error, just show disabled button
                    $latestPayroll = null;
                }
            ?>
            
            <?php if($latestPayroll): ?>
                <button id="nav-download-payslip-btn" 
                        onclick="downloadEmployeePayslip('<?php echo e($latestPayroll->id); ?>')" 
                        class="w-full flex items-center justify-center px-4 py-3 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition-all duration-200 group">
                    <i class="fas fa-download mr-3 text-lg"></i>
                    <span>Download Payslip</span>
                </button>
            <?php else: ?>
                <button disabled class="w-full flex items-center justify-center px-4 py-3 text-sm font-medium text-gray-500 bg-gray-100 rounded-lg cursor-not-allowed group">
                    <i class="fas fa-download mr-3 text-lg text-gray-400"></i>
                    <span>No Payslip Available</span>
                </button>
            <?php endif; ?>

            <!-- Update Profile -->
            <a href="<?php echo e(route('hr.profile')); ?>" class="flex items-center px-4 py-3 text-sm font-medium text-gray-700 hover:bg-gray-50 hover:text-blue-600 rounded-lg transition-all duration-200 group">
                <i class="fas fa-edit mr-3 text-lg text-gray-400 group-hover:text-blue-600"></i>
                <span>Update Profile</span>
            </a>

            <!-- Contact HR -->
            <a href="<?php echo e(route('hr.contact.index')); ?>" class="flex items-center px-4 py-3 text-sm font-medium text-gray-700 hover:bg-gray-50 hover:text-blue-600 rounded-lg transition-all duration-200 group">
                <i class="fas fa-question-circle mr-3 text-lg text-gray-400 group-hover:text-blue-600"></i>
                <span>Contact HR</span>
            </a>

            <!-- Help & Support -->
            <a href="<?php echo e(route('hr.help-support')); ?>" class="flex items-center px-4 py-3 text-sm font-medium text-gray-700 hover:bg-gray-50 hover:text-blue-600 rounded-lg transition-all duration-200 group">
                <i class="fas fa-life-ring mr-3 text-lg text-gray-400 group-hover:text-blue-600"></i>
                <span>Help & Support</span>
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

        <a href="<?php echo e(route('employees.index')); ?>" class="flex items-center px-4 py-3 text-sm font-medium text-gray-700 hover:bg-gray-50 hover:text-blue-600 rounded-lg transition-all duration-200 group">
            <i class="fas fa-user-friends mr-3 text-lg text-gray-400 group-hover:text-blue-600"></i>
            <span>Team Directory</span>
        </a>

        <a href="<?php echo e(route('notifications.index')); ?>" class="flex items-center px-4 py-3 text-sm font-medium <?php echo e($activeRoute === 'notifications.index' ? 'text-blue-600 bg-blue-50 border-r-4 border-blue-600' : 'text-gray-700 hover:bg-gray-50 hover:text-blue-600'); ?> rounded-lg transition-all duration-200 group">
            <i class="fas fa-bell mr-3 text-lg <?php echo e($activeRoute === 'notifications.index' ? 'text-blue-600' : 'text-gray-400 group-hover:text-blue-600'); ?>"></i>
            <span>Notifications</span>
        </a>

        <a href="<?php echo e(route('hr.contacts.admin')); ?>" class="flex items-center px-4 py-3 text-sm font-medium <?php echo e($activeRoute === 'hr.contacts.admin' ? 'text-blue-600 bg-blue-50 border-r-4 border-blue-600' : 'text-gray-700 hover:bg-gray-50 hover:text-blue-600'); ?> rounded-lg transition-all duration-200 group">
            <i class="fas fa-inbox mr-3 text-lg <?php echo e($activeRoute === 'hr.contacts.admin' ? 'text-blue-600' : 'text-gray-400 group-hover:text-blue-600'); ?>"></i>
            <span>Inbox</span>
            <span class="ml-auto text-xs font-semibold px-2 py-0.5 bg-green-500 text-white rounded-full">NEW</span>
        </a>

        <a href="<?php echo e(route('hr.help-support')); ?>" class="flex items-center px-4 py-3 text-sm font-medium text-gray-700 hover:bg-gray-50 hover:text-blue-600 rounded-lg transition-all duration-200 group">
            <i class="fas fa-question-circle mr-3 text-lg text-gray-400 group-hover:text-blue-600"></i>
            <span>Help & Support</span>
            <span class="ml-auto text-xs font-semibold px-2 py-0.5 bg-green-500 text-white rounded-full">NEW</span>
        </a>
        <?php endif; ?>
    </div>
</nav>

<script>
// Function to handle payslip download
function downloadEmployeePayslip(payrollId) {
    // Check if the function exists in the main dashboard
    if (typeof window.downloadEmployeePayslip === 'function') {
        // Use the dashboard's function
        window.downloadEmployeePayslip(payrollId);
    } else {
        // Fallback: direct download
        window.open(`/employee/payslip/download/${payrollId}`, '_blank');
    }
}

// Get Philippine Standard Time (UTC+8)
function getPhilippineTime() {
    const now = new Date();
    return new Date(now.toLocaleString("en-US", {timeZone: "Asia/Manila"}));
}

// Format time in 12-hour format with AM/PM
function format12HourTime(date) {
    let hours = date.getHours();
    const minutes = date.getMinutes().toString().padStart(2, '0');
    const seconds = date.getSeconds().toString().padStart(2, '0');
    const ampm = hours >= 12 ? 'PM' : 'AM';
    hours = hours % 12;
    hours = hours ? hours : 12;
    const hoursStr = hours.toString().padStart(2, '0');
    return `${hoursStr}:${minutes}:${seconds} ${ampm}`;
}

// Show confirmation modal (similar to dashboard)
function showConfirmationModal(title, message, confirmAction, options = {}) {
    // Check if the modal exists in the main dashboard
    if (typeof window.showConfirmationModal === 'function') {
        // Use the dashboard's modal function
        window.showConfirmationModal(title, message, confirmAction, options);
        return;
    }
    
    // Fallback: Use browser's native confirm dialog
    if (confirm(`${title}\n\n${message}`)) {
        confirmAction();
    }
}

// Hide confirmation modal
function hideConfirmationModal() {
    if (typeof window.hideConfirmationModal === 'function') {
        window.hideConfirmationModal();
    }
}

// ============================================================
// SIDEBAR TIME IN/OUT CONFIRMATION FUNCTIONS
// ============================================================

// Confirm Time In from sidebar
function sidebarConfirmTimeIn() {
    // Get current time for the confirmation message
    const currentTime = getPhilippineTime();
    const formattedTime = format12HourTime(currentTime);
    
    showConfirmationModal(
        'Confirm Time In',
        `Are you sure you want to clock in at ${formattedTime}?`,
        sidebarTimeIn, // This will be called after confirmation
        {
            color: 'green',
            icon: 'fa-sign-in-alt'
        }
    );
}

// Confirm Time Out from sidebar
function sidebarConfirmTimeOut() {
    // Get current time for the confirmation message
    const currentTime = getPhilippineTime();
    const formattedTime = format12HourTime(currentTime);
    
    showConfirmationModal(
        'Confirm Time Out',
        `Are you sure you want to clock out at ${formattedTime}?`,
        sidebarTimeOut, // This will be called after confirmation
        {
            color: 'red',
            icon: 'fa-sign-out-alt'
        }
    );
}

// Time In function for sidebar (called after confirmation)
async function sidebarTimeIn() {
    try {
        const response = await fetch('<?php echo e(route("attendance.time-in")); ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>'
            }
        });

        const data = await response.json();

        if (response.ok) {
            showSuccess(data.message);
            // Refresh the page to show updated data
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            showError(data.error || 'Failed to clock in');
        }
    } catch (error) {
        console.error('Error clocking in:', error);
        showError('Failed to clock in');
    }
}

// Time Out function for sidebar (called after confirmation)
async function sidebarTimeOut() {
    try {
        const response = await fetch('<?php echo e(route("attendance.time-out")); ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>'
            }
        });

        const data = await response.json();

        if (response.ok) {
            showSuccess(data.message);
            // Refresh the page to show updated data
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            showError(data.error || 'Failed to clock out');
        }
    } catch (error) {
        console.error('Error clocking out:', error);
        showError('Failed to clock out');
    }
}


// Show success message
function showSuccess(message) {
    // Check if the function exists in the main dashboard
    if (typeof window.showSuccess === 'function') {
        window.showSuccess(message);
        return;
    }
    
    // Fallback: alert
    alert('Success: ' + message);
}

// Show error message
function showError(message) {
    // Check if the function exists in the main dashboard
    if (typeof window.showError === 'function') {
        window.showError(message);
        return;
    }
    
    // Fallback: alert
    alert('Error: ' + message);
}

// Employee Menu Dropdown Handler
document.addEventListener('DOMContentLoaded', function() {
    const employeeMenuBtn = document.getElementById('employeeMenuBtn');
    const employeeSubMenu = document.getElementById('employeeSubMenu');
    const employeeChevron = document.getElementById('employeeChevron');
    
    if (employeeMenuBtn && employeeSubMenu) {
        // Toggle menu on button click
        employeeMenuBtn.addEventListener('click', function(e) {
            e.preventDefault();
            employeeSubMenu.classList.toggle('hidden');
            employeeChevron.style.transform = employeeSubMenu.classList.contains('hidden') ? 'rotate(0deg)' : 'rotate(180deg)';
        });
        
        // Close menu when clicking outside
        document.addEventListener('click', function(e) {
            if (!employeeMenuBtn.contains(e.target) && !employeeSubMenu.contains(e.target)) {
                employeeSubMenu.classList.add('hidden');
                employeeChevron.style.transform = 'rotate(0deg)';
            }
        });
        
        // Close menu when clicking on a submenu item
        const submenuItems = employeeSubMenu.querySelectorAll('a');
        submenuItems.forEach(item => {
            item.addEventListener('click', function() {
                employeeSubMenu.classList.add('hidden');
                employeeChevron.style.transform = 'rotate(0deg)';
            });
        });
    }

    // Report Menu Dropdown Handler
    const reportMenuBtn = document.getElementById('reportMenuBtn');
    const reportSubMenu = document.getElementById('reportSubMenu');
    const reportChevron = document.getElementById('reportChevron');
    
    if (reportMenuBtn && reportSubMenu) {
        // Toggle menu on button click
        reportMenuBtn.addEventListener('click', function(e) {
            e.preventDefault();
            reportSubMenu.classList.toggle('hidden');
            reportChevron.style.transform = reportSubMenu.classList.contains('hidden') ? 'rotate(0deg)' : 'rotate(180deg)';
        });
        
        // Close menu when clicking outside
        document.addEventListener('click', function(e) {
            if (!reportMenuBtn.contains(e.target) && !reportSubMenu.contains(e.target)) {
                reportSubMenu.classList.add('hidden');
                reportChevron.style.transform = 'rotate(0deg)';
            }
        });
        
        // Close menu when clicking on a submenu item
        const reportSubmenuItems = reportSubMenu.querySelectorAll('a');
        reportSubmenuItems.forEach(item => {
            item.addEventListener('click', function() {
                reportSubMenu.classList.add('hidden');
                reportChevron.style.transform = 'rotate(0deg)';
            });
        });
    }

    // Timekeeping and HRIS Reports Submenu Handler
    const timekeepingBtns = document.querySelectorAll('.timekeepingReportBtn');
    timekeepingBtns.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const submenu = this.closest('.timekeeping-report-group')?.querySelector('.timekeepingReportSubMenu');
            if (submenu) {
                submenu.classList.toggle('hidden');
            }
        });

        // Show submenu on hover
        btn.parentElement.addEventListener('mouseenter', function() {
            const submenu = this.querySelector('.timekeepingReportSubMenu');
            if (submenu) {
                submenu.classList.remove('hidden');
            }
        });

        // Hide submenu on mouse leave
        btn.parentElement.addEventListener('mouseleave', function() {
            const submenu = this.querySelector('.timekeepingReportSubMenu');
            if (submenu) {
                submenu.classList.add('hidden');
            }
        });
    });
});
</script><?php /**PATH C:\internship\Aeternitas-Desktop app\backend\resources\views/components/dashboard/sidebar/navigation.blade.php ENDPATH**/ ?>
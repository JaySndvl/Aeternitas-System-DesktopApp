@props(['user', 'activeRoute' => 'dashboard'])

@php
    $currentCompany = \App\Helpers\CompanyHelper::getCurrentCompany();
    
    // Check if employee is currently timed in (for employee role users)
    $isCurrentlyTimedIn = false;
    $todayAttendance = null;
    if ($user->role === 'employee' && $user->employee) {
        $todayAttendance = $user->employee->getTodayAttendance();
        $isCurrentlyTimedIn = $todayAttendance && $todayAttendance->time_in && !$todayAttendance->time_out;
    }
@endphp

<nav class="mt-8 px-4 pb-4">
    <div class="space-y-2">
        <!-- Dashboard -->
        <a href="{{ route('dashboard') }}" class="flex items-center px-4 py-3 text-sm font-medium {{ $activeRoute === 'dashboard' ? 'text-blue-600 bg-blue-50 border-r-4 border-blue-600' : 'text-gray-700 hover:bg-gray-50 hover:text-blue-600' }} rounded-lg transition-all duration-200 group">
            <i class="fas fa-tachometer-alt mr-3 text-lg {{ $activeRoute === 'dashboard' ? 'text-blue-600' : 'text-gray-400 group-hover:text-blue-600' }}"></i>
            <span>Dashboard</span>
        </a>
        
        @if($user->role === 'admin' || $user->role === 'hr')
        <!-- Employees -->
        @php
            $employeeCount = $currentCompany 
                ? \App\Models\Employee::forCompany($currentCompany->id)->count() 
                : \App\Models\Employee::count();
        @endphp
        <a href="{{ route('employees.index') }}" class="flex items-center px-4 py-3 text-sm font-medium {{ $activeRoute === 'employees.index' ? 'text-blue-600 bg-blue-50 border-r-4 border-blue-600' : 'text-gray-700 hover:bg-gray-50 hover:text-blue-600' }} rounded-lg transition-all duration-200 group">
            <i class="fas fa-users mr-3 text-lg {{ $activeRoute === 'employees.index' ? 'text-blue-600' : 'text-gray-400 group-hover:text-blue-600' }}"></i>
            <span>Employees</span>
            <span class="ml-auto bg-blue-100 text-blue-600 text-xs px-2 py-1 rounded-full">{{ $employeeCount }}</span>
        </a>
        
        <!-- Departments -->
        @php
            $departmentCount = $currentCompany 
                ? \App\Models\Department::forCompany($currentCompany->id)->count() 
                : \App\Models\Department::count();
        @endphp
        <a href="{{ route('departments.index') }}" class="flex items-center px-4 py-3 text-sm font-medium {{ $activeRoute === 'departments.index' ? 'text-blue-600 bg-blue-50 border-r-4 border-blue-600' : 'text-gray-700 hover:bg-gray-50 hover:text-blue-600' }} rounded-lg transition-all duration-200 group">
            <i class="fas fa-building mr-3 text-lg {{ $activeRoute === 'departments.index' ? 'text-blue-600' : 'text-gray-400 group-hover:text-blue-600' }}"></i>
            <span>Departments</span>
            <span class="ml-auto bg-blue-100 text-blue-600 text-xs px-2 py-1 rounded-full">{{ $departmentCount }}</span>
        </a>
        
        <!-- Positions -->
        @php
            $positionCount = $currentCompany 
                ? \App\Models\Position::forCompany($currentCompany->id)->count() 
                : \App\Models\Position::count();
        @endphp
        <a href="{{ route('positions.index') }}" class="flex items-center px-4 py-3 text-sm font-medium {{ $activeRoute === 'positions.index' ? 'text-blue-600 bg-blue-50 border-r-4 border-blue-600' : 'text-gray-700 hover:bg-gray-50 hover:text-blue-600' }} rounded-lg transition-all duration-200 group">
            <i class="fas fa-briefcase mr-3 text-lg {{ $activeRoute === 'positions.index' ? 'text-blue-600' : 'text-gray-400 group-hover:text-blue-600' }}"></i>
            <span>Positions</span>
            <span class="ml-auto bg-blue-100 text-blue-600 text-xs px-2 py-1 rounded-full">{{ $positionCount }}</span>
        </a>
        
        <!-- Companies -->
        <a href="{{ route('companies.index') }}" class="flex items-center px-4 py-3 text-sm font-medium {{ $activeRoute === 'companies.index' ? 'text-blue-600 bg-blue-50 border-r-4 border-blue-600' : 'text-gray-700 hover:bg-gray-50 hover:text-blue-600' }} rounded-lg transition-all duration-200 group">
            <i class="fas fa-industry mr-3 text-lg {{ $activeRoute === 'companies.index' ? 'text-blue-600' : 'text-gray-400 group-hover:text-blue-600' }}"></i>
            <span>Companies</span>
            <span class="ml-auto bg-blue-100 text-blue-600 text-xs px-2 py-1 rounded-full">{{ \App\Models\Company::count() }}</span>
        </a>
        @endif
        
        @if($user->role === 'admin' || $user->role === 'hr' || $user->role === 'manager')
        <!-- Payroll -->
        <a href="{{ route('payroll.index') }}" class="flex items-center px-4 py-3 text-sm font-medium {{ $activeRoute === 'payroll.index' ? 'text-blue-600 bg-blue-50 border-r-4 border-blue-600' : 'text-gray-700 hover:bg-gray-50 hover:text-blue-600' }} rounded-lg transition-all duration-200 group">
            <i class="fas fa-money-bill-wave mr-3 text-lg {{ $activeRoute === 'payroll.index' ? 'text-blue-600' : 'text-gray-400 group-hover:text-blue-600' }}"></i>
            <span>Payroll</span>
            <span class="ml-auto bg-green-100 text-green-600 text-xs px-2 py-1 rounded-full">New</span>
        </a>
        @endif
        
        <!-- Attendance Dropdown -->
        <div class="relative" x-data="{ 
            open: {{ in_array($activeRoute, ['attendance.time-in-out', 'attendance.daily', 'attendance.timekeeping', 'attendance.import-dtr', 'schedule-v2.index', 'schedule-v2.create', 'schedule-v2.show', 'schedule-v2.edit', 'attendance.schedule.reports', 'attendance.schedule.templates', 'attendance.overtime', 'attendance.leave-management', 'attendance.reports', 'attendance.settings', 'attendance.period-management.index', 'attendance.period-management.create', 'attendance.period-management.show']) ? 'true' : 'false' }}
        }">
            <button @click="open = !open" class="w-full flex items-center justify-between px-4 py-3 text-sm font-medium {{ in_array($activeRoute, ['attendance.time-in-out', 'attendance.daily', 'attendance.timekeeping', 'attendance.import-dtr', 'schedule-v2.index', 'schedule-v2.create', 'schedule-v2.show', 'schedule-v2.edit', 'attendance.schedule.reports', 'attendance.schedule.templates', 'attendance.overtime', 'attendance.leave-management', 'attendance.reports', 'attendance.settings', 'attendance.period-management.index', 'attendance.period-management.create', 'attendance.period-management.show']) ? 'text-blue-600 bg-blue-50 border-r-4 border-blue-600' : 'text-gray-700 hover:bg-gray-50 hover:text-blue-600' }} rounded-lg transition-all duration-200 group">
                <div class="flex items-center">
                    <i class="fas fa-clock mr-3 text-lg {{ in_array($activeRoute, ['attendance.time-in-out', 'attendance.daily', 'attendance.timekeeping', 'attendance.import-dtr', 'schedule-v2.index', 'schedule-v2.create', 'schedule-v2.show', 'schedule-v2.edit', 'attendance.schedule.reports', 'attendance.schedule.templates', 'attendance.overtime', 'attendance.leave-management', 'attendance.reports', 'attendance.settings', 'attendance.period-management.index', 'attendance.period-management.create', 'attendance.period-management.show']) ? 'text-blue-600' : 'text-gray-400 group-hover:text-blue-600' }}"></i>
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
                @if($user->role === 'employee')
                @php
                    $timeInOutDisabled = !$isCurrentlyTimedIn;
                @endphp
                <a href="{{ $timeInOutDisabled ? '#' : route('attendance.time-in-out') }}" 
                   class="flex items-center px-3 py-2 text-sm {{ $timeInOutDisabled ? 'text-gray-400 cursor-not-allowed' : 'text-gray-700 hover:bg-white hover:text-blue-600' }} rounded-md transition-all duration-200 group {{ $activeRoute === 'attendance.time-in-out' ? 'bg-white text-blue-600' : '' }}"
                   {{ $timeInOutDisabled ? 'onclick="return false;"' : '' }}>
                    <i class="fas fa-sign-in-alt mr-3 text-sm {{ $timeInOutDisabled ? 'text-gray-300' : 'text-gray-400 group-hover:text-blue-600' }} {{ $activeRoute === 'attendance.time-in-out' ? 'text-blue-600' : '' }}"></i>
                    <span>Time In/Out</span>
                    <span class="ml-auto bg-blue-100 text-blue-600 text-xs px-2 py-1 rounded-full">Live</span>
                </a>
                @endif
                
                <!-- Attendance Record -->
                @php
                    $dailyDisabled = $user->role === 'employee' && !$isCurrentlyTimedIn;
                @endphp
                <a href="{{ $dailyDisabled ? '#' : route('attendance.daily') }}" 
                   class="flex items-center px-3 py-2 text-sm {{ $dailyDisabled ? 'text-gray-400 cursor-not-allowed' : 'text-gray-700 hover:bg-white hover:text-blue-600' }} rounded-md transition-all duration-200 group {{ $activeRoute === 'attendance.daily' ? 'bg-white text-blue-600' : '' }}"
                   {{ $dailyDisabled ? 'onclick="return false;"' : '' }}>
                    <i class="fas fa-calendar-day mr-3 text-sm {{ $dailyDisabled ? 'text-gray-300' : 'text-gray-400 group-hover:text-blue-600' }} {{ $activeRoute === 'attendance.daily' ? 'text-blue-600' : '' }}"></i>
                    <span>Attendance Record</span>
                </a>
                
                <!-- Timekeeping -->
                @php
                    $timekeepingDisabled = $user->role === 'employee' && !$isCurrentlyTimedIn;
                @endphp
                <a href="{{ $timekeepingDisabled ? '#' : route('attendance.timekeeping') }}" 
                   class="flex items-center px-3 py-2 text-sm {{ $timekeepingDisabled ? 'text-gray-400 cursor-not-allowed' : 'text-gray-700 hover:bg-white hover:text-blue-600' }} rounded-md transition-all duration-200 group {{ $activeRoute === 'attendance.timekeeping' ? 'bg-white text-blue-600' : '' }}"
                   {{ $timekeepingDisabled ? 'onclick="return false;"' : '' }}>
                    <i class="fas fa-stopwatch mr-3 text-sm {{ $timekeepingDisabled ? 'text-gray-300' : 'text-gray-400 group-hover:text-blue-600' }} {{ $activeRoute === 'attendance.timekeeping' ? 'text-blue-600' : '' }}"></i>
                    <span>Timekeeping</span>
                </a>
                
                <!-- Import DTR -->
                @php
                    $importDtrDisabled = $user->role === 'employee' && !$isCurrentlyTimedIn;
                @endphp
                <a href="{{ $importDtrDisabled ? '#' : route('attendance.import-dtr') }}" 
                   class="flex items-center px-3 py-2 text-sm {{ $importDtrDisabled ? 'text-gray-400 cursor-not-allowed' : 'text-gray-700 hover:bg-white hover:text-blue-600' }} rounded-md transition-all duration-200 group {{ $activeRoute === 'attendance.import-dtr' ? 'bg-white text-blue-600' : '' }}"
                   {{ $importDtrDisabled ? 'onclick="return false;"' : '' }}>
                    <i class="fas fa-file-import mr-3 text-sm {{ $importDtrDisabled ? 'text-gray-300' : 'text-gray-400 group-hover:text-blue-600' }} {{ $activeRoute === 'attendance.import-dtr' ? 'text-blue-600' : '' }}"></i>
                    <span>Import DTR</span>
                    <span class="ml-auto bg-orange-100 text-orange-600 text-xs px-2 py-1 rounded-full">New</span>
                </a>
                
                <!-- Schedule Management -->
                @if($user->role !== 'employee')
                @php
                    $scheduleDisabled = $user->role === 'employee' && !$isCurrentlyTimedIn;
                @endphp
                <a href="{{ $scheduleDisabled ? '#' : route('schedule-v2.index') }}" 
                   class="flex items-center px-3 py-2 text-sm {{ $scheduleDisabled ? 'text-gray-400 cursor-not-allowed' : 'text-gray-700 hover:bg-white hover:text-blue-600' }} rounded-md transition-all duration-200 group {{ $activeRoute === 'schedule-v2.index' ? 'bg-white text-blue-600' : '' }}"
                   {{ $scheduleDisabled ? 'onclick="return false;"' : '' }}>
                    <i class="fas fa-calendar-plus mr-3 text-sm {{ $scheduleDisabled ? 'text-gray-300' : 'text-gray-400 group-hover:text-blue-600' }} {{ $activeRoute === 'schedule-v2.index' ? 'text-blue-600' : '' }}"></i>
                    <span>Schedule Management</span>
                </a>
                @endif
                
                <!-- Overtime -->
                @php
                    $overtimeDisabled = $user->role === 'employee' && !$isCurrentlyTimedIn;
                @endphp
                <a href="{{ $overtimeDisabled ? '#' : route('attendance.overtime') }}" 
                   class="flex items-center px-3 py-2 text-sm {{ $overtimeDisabled ? 'text-gray-400 cursor-not-allowed' : 'text-gray-700 hover:bg-white hover:text-blue-600' }} rounded-md transition-all duration-200 group {{ $activeRoute === 'attendance.overtime' ? 'bg-white text-blue-600' : '' }}"
                   {{ $overtimeDisabled ? 'onclick="return false;"' : '' }}>
                    <i class="fas fa-clock mr-3 text-sm {{ $overtimeDisabled ? 'text-gray-300' : 'text-gray-400 group-hover:text-blue-600' }} {{ $activeRoute === 'attendance.overtime' ? 'text-blue-600' : '' }}"></i>
                    <span>Overtime</span>
                </a>
                
                <!-- Leave Management -->
                @php
                    $leaveDisabled = $user->role === 'employee' && !$isCurrentlyTimedIn;
                @endphp
                <a href="{{ $leaveDisabled ? '#' : route('attendance.leave-management') }}" 
                   class="flex items-center px-3 py-2 text-sm {{ $leaveDisabled ? 'text-gray-400 cursor-not-allowed' : 'text-gray-700 hover:bg-white hover:text-blue-600' }} rounded-md transition-all duration-200 group {{ $activeRoute === 'attendance.leave-management' ? 'bg-white text-blue-600' : '' }}"
                   {{ $leaveDisabled ? 'onclick="return false;"' : '' }}>
                    <i class="fas fa-calendar-times mr-3 text-sm {{ $leaveDisabled ? 'text-gray-300' : 'text-gray-400 group-hover:text-blue-600' }} {{ $activeRoute === 'attendance.leave-management' ? 'text-blue-600' : '' }}"></i>
                    <span>Leave Management</span>
                </a>
                
                <!-- Period Management -->
                <a href="{{ route('attendance.period-management.index') }}" 
                   class="flex items-center px-3 py-2 text-sm text-gray-700 hover:bg-white hover:text-blue-600 rounded-md transition-all duration-200 group {{ $activeRoute === 'attendance.period-management.index' ? 'bg-white text-blue-600' : '' }}">
                    <i class="fas fa-calendar-week mr-3 text-sm text-gray-400 group-hover:text-blue-600 {{ $activeRoute === 'attendance.period-management.index' ? 'text-blue-600' : '' }}"></i>
                    <span>Period Management</span>
                    @if($user->role !== 'employee')
                    <span class="ml-auto bg-purple-100 text-purple-600 text-xs px-2 py-1 rounded-full">New</span>
                    @endif
                </a>
                
                @if($user->role === 'admin' || $user->role === 'hr')
                <!-- Attendance Reports -->
                <div class="border-t border-gray-200 my-2"></div>
                <a href="{{ route('attendance.reports') }}" class="flex items-center px-3 py-2 text-sm text-gray-700 hover:bg-white hover:text-blue-600 rounded-md transition-all duration-200 group {{ $activeRoute === 'attendance.reports' ? 'bg-white text-blue-600' : '' }}">
                    <i class="fas fa-chart-line mr-3 text-sm text-gray-400 group-hover:text-blue-600 {{ $activeRoute === 'attendance.reports' ? 'text-blue-600' : '' }}"></i>
                    <span>Attendance Reports</span>
                </a>
                
                <!-- Attendance Settings -->
                <a href="{{ route('attendance.settings') }}" class="flex items-center px-3 py-2 text-sm text-gray-700 hover:bg-white hover:text-blue-600 rounded-md transition-all duration-200 group {{ $activeRoute === 'attendance.settings' ? 'bg-white text-blue-600' : '' }}">
                    <i class="fas fa-cog mr-3 text-sm text-gray-400 group-hover:text-blue-600 {{ $activeRoute === 'attendance.settings' ? 'text-blue-600' : '' }}"></i>
                    <span>Attendance Settings</span>
                </a>
                @endif
            </div>
        </div>
        
        @if($user->role === 'admin' || $user->role === 'hr')
        <!-- Tax Brackets -->
        <a href="{{ route('tax-brackets.index') }}" class="flex items-center px-4 py-3 text-sm font-medium {{ $activeRoute === 'tax-brackets.index' ? 'text-blue-600 bg-blue-50 border-r-4 border-blue-600' : 'text-gray-700 hover:bg-gray-50 hover:text-blue-600' }} rounded-lg transition-all duration-200 group">
            <i class="fas fa-percentage mr-3 text-lg {{ $activeRoute === 'tax-brackets.index' ? 'text-blue-600' : 'text-gray-400 group-hover:text-blue-600' }}"></i>
            <span>Tax Brackets</span>
            <span class="ml-auto bg-green-100 text-green-600 text-xs px-2 py-1 rounded-full">New</span>
        </a>
        
        <!-- Reports -->
        <a href="#" class="flex items-center px-4 py-3 text-sm font-medium text-gray-700 hover:bg-gray-50 hover:text-blue-600 rounded-lg transition-all duration-200 group">
            <i class="fas fa-chart-bar mr-3 text-lg text-gray-400 group-hover:text-blue-600"></i>
            <span>Reports</span>
        </a>
        @endif
        
        @if($user->role === 'admin')
        <!-- Settings -->
        <a href="#" class="flex items-center px-4 py-3 text-sm font-medium text-gray-700 hover:bg-gray-50 hover:text-blue-600 rounded-lg transition-all duration-200 group">
            <i class="fas fa-cog mr-3 text-lg text-gray-400 group-hover:text-blue-600"></i>
            <span>Settings</span>
        </a>
        @endif
        
        <!-- Divider -->
        <div class="my-6 border-t border-gray-200"></div>
        
        <!-- Quick Actions -->
        <div class="px-4 mb-2">
            <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Quick Actions</h3>
        </div>
        
        @if($user->role === 'admin' || $user->role === 'hr')
        <a href="{{ route('employees.create') }}" class="flex items-center px-4 py-3 text-sm font-medium text-gray-700 hover:bg-gray-50 hover:text-blue-600 rounded-lg transition-all duration-200 group">
            <i class="fas fa-user-plus mr-3 text-lg text-gray-400 group-hover:text-blue-600"></i>
            <span>Add Employee</span>
        </a>
        
        <a href="#" class="flex items-center px-4 py-3 text-sm font-medium text-gray-700 hover:bg-gray-50 hover:text-blue-600 rounded-lg transition-all duration-200 group">
            <i class="fas fa-file-export mr-3 text-lg text-gray-400 group-hover:text-blue-600"></i>
            <span>Export Data</span>
        </a>
        @endif
        
        @if($user->role === 'employee')
            <!-- Time In Button -->
            @if(!$todayAttendance || !$todayAttendance->time_in || $todayAttendance->time_out)
                <button onclick="sidebarConfirmTimeIn()" class="w-full flex items-center px-4 py-3 text-sm font-medium text-gray-700 hover:bg-gray-50 hover:text-green-600 rounded-lg transition-all duration-200 group">
                    <i class="fas fa-sign-in-alt mr-3 text-lg text-gray-400 group-hover:text-green-600"></i>
                    <span>Time In</span>
                </button>
            @else
                <div class="flex items-center px-4 py-3 text-sm font-medium text-gray-400 rounded-lg cursor-not-allowed">
                    <i class="fas fa-check mr-3 text-lg text-gray-400"></i>
                    <span>Already Clocked In</span>
                </div>
            @endif

            <!-- Time Out Button -->
            @if($todayAttendance && $todayAttendance->time_in && !$todayAttendance->time_out)
                <button onclick="sidebarConfirmTimeOut()" class="w-full flex items-center px-4 py-3 text-sm font-medium text-gray-700 hover:bg-gray-50 hover:text-red-600 rounded-lg transition-all duration-200 group">
                    <i class="fas fa-sign-out-alt mr-3 text-lg text-gray-400 group-hover:text-red-600"></i>
                    <span>Time Out</span>
                </button>
            @elseif($todayAttendance && $todayAttendance->time_out)
                <div class="flex items-center px-4 py-3 text-sm font-medium text-gray-400 rounded-lg cursor-not-allowed">
                    <i class="fas fa-check mr-3 text-lg text-gray-400"></i>
                    <span>Already Clocked Out</span>
                </div>
            @else
                <div class="flex items-center px-4 py-3 text-sm font-medium text-gray-400 rounded-lg cursor-not-allowed">
                    <i class="fas fa-sign-out-alt mr-3 text-lg text-gray-400"></i>
                    <span>Time Out (Clock In First)</span>
                </div>
            @endif

            <!-- Payslip Download Button for Employees -->
            @php
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
            @endphp
            
            @if($latestPayroll)
                <button id="nav-download-payslip-btn" 
                        onclick="downloadEmployeePayslip('{{ $latestPayroll->id }}')" 
                        class="w-full flex items-center justify-center px-4 py-3 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition-all duration-200 group">
                    <i class="fas fa-download mr-3 text-lg"></i>
                    <span>Download Payslip</span>
                </button>
            @else
                <button disabled class="w-full flex items-center justify-center px-4 py-3 text-sm font-medium text-gray-500 bg-gray-100 rounded-lg cursor-not-allowed group">
                    <i class="fas fa-download mr-3 text-lg text-gray-400"></i>
                    <span>No Payslip Available</span>
                </button>
            @endif

            <!-- Update Profile -->
            <a href="{{ route('hr.profile') }}" class="flex items-center px-4 py-3 text-sm font-medium text-gray-700 hover:bg-gray-50 hover:text-blue-600 rounded-lg transition-all duration-200 group">
                <i class="fas fa-edit mr-3 text-lg text-gray-400 group-hover:text-blue-600"></i>
                <span>Update Profile</span>
            </a>

            <!-- Contact HR -->
            <button onclick="contactHR()" class="w-full flex items-center px-4 py-3 text-sm font-medium text-gray-700 hover:bg-gray-50 hover:text-blue-600 rounded-lg transition-all duration-200 group">
                <i class="fas fa-question-circle mr-3 text-lg text-gray-400 group-hover:text-blue-600"></i>
                <span>Contact HR</span>
            </button>
        @endif
        
        <!-- Additional test items to ensure scrolling -->
        @if($user->role === 'admin' || $user->role === 'hr')
        <!-- Documents -->
        <a href="{{ route('documents.index') }}" class="flex items-center px-4 py-3 text-sm font-medium {{ request()->routeIs('documents.*') ? 'text-blue-600 bg-blue-50 border-r-4 border-blue-600' : 'text-gray-700 hover:bg-gray-50 hover:text-blue-600' }} rounded-lg transition-all duration-200 group">
            <i class="fas fa-folder mr-3 text-lg {{ request()->routeIs('documents.*') ? 'text-blue-600' : 'text-gray-400 group-hover:text-blue-600' }}"></i>
            <span>Documents</span>
        </a>

        <a href="{{ route('attendance.leave-management.create') }}" class="flex items-center px-4 py-3 text-sm font-medium text-gray-700 hover:bg-gray-50 hover:text-blue-600 rounded-lg transition-all duration-200 group">
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
        @endif
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
        const response = await fetch('{{ route("attendance.time-in") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
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
        const response = await fetch('{{ route("attendance.time-out") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
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

// ============================================================
// CONTACT HR FUNCTION (PLACEHOLDER)
// ============================================================

function contactHR() {
    // Placeholder function - Add your Contact HR functionality here
    alert('Contact HR functionality will be implemented here.\n\nThis could open a contact form, show HR contact information, or open a chat interface.');
    
    // Example of what you could do:
    // 1. Open a modal with HR contact information
    // 2. Show a form to send a message to HR
    // 3. Open an email client with HR email pre-filled
    // 4. Show HR office location and contact numbers
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
</script>
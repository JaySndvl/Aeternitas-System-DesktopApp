@props(['user', 'activeRoute' => 'dashboard'])

<nav class="mt-8 px-4 pb-4">
    <div class="space-y-2">
        <!-- Dashboard -->
        <a href="{{ route('dashboard') }}" class="flex items-center px-4 py-3 text-sm font-medium {{ $activeRoute === 'dashboard' ? 'text-blue-600 bg-blue-50 border-r-4 border-blue-600' : 'text-gray-700 hover:bg-gray-50 hover:text-blue-600' }} rounded-lg transition-all duration-200 group">
            <i class="fas fa-tachometer-alt mr-3 text-lg {{ $activeRoute === 'dashboard' ? 'text-blue-600' : 'text-gray-400 group-hover:text-blue-600' }}"></i>
            <span>Dashboard</span>
        </a>
        
        @if($user->role === 'admin' || $user->role === 'hr')
        <!-- Employees -->
        <a href="{{ route('employees.index') }}" class="flex items-center px-4 py-3 text-sm font-medium {{ $activeRoute === 'employees.index' ? 'text-blue-600 bg-blue-50 border-r-4 border-blue-600' : 'text-gray-700 hover:bg-gray-50 hover:text-blue-600' }} rounded-lg transition-all duration-200 group">
            <i class="fas fa-users mr-3 text-lg {{ $activeRoute === 'employees.index' ? 'text-blue-600' : 'text-gray-400 group-hover:text-blue-600' }}"></i>
            <span>Employees</span>
            <span class="ml-auto bg-blue-100 text-blue-600 text-xs px-2 py-1 rounded-full">{{ \App\Models\Employee::count() }}</span>
        </a>
        
        <!-- Departments -->
        <a href="{{ route('departments.index') }}" class="flex items-center px-4 py-3 text-sm font-medium {{ $activeRoute === 'departments.index' ? 'text-blue-600 bg-blue-50 border-r-4 border-blue-600' : 'text-gray-700 hover:bg-gray-50 hover:text-blue-600' }} rounded-lg transition-all duration-200 group">
            <i class="fas fa-building mr-3 text-lg {{ $activeRoute === 'departments.index' ? 'text-blue-600' : 'text-gray-400 group-hover:text-blue-600' }}"></i>
            <span>Departments</span>
            <span class="ml-auto bg-blue-100 text-blue-600 text-xs px-2 py-1 rounded-full">{{ \App\Models\Department::count() }}</span>
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
                
                <!-- Time In/Out -->
                <a href="{{ route('attendance.time-in-out') }}" class="flex items-center px-3 py-2 text-sm text-gray-700 hover:bg-white hover:text-blue-600 rounded-md transition-all duration-200 group {{ $activeRoute === 'attendance.time-in-out' ? 'bg-white text-blue-600' : '' }}">
                    <i class="fas fa-sign-in-alt mr-3 text-sm text-gray-400 group-hover:text-blue-600 {{ $activeRoute === 'attendance.time-in-out' ? 'text-blue-600' : '' }}"></i>
                    <span>Time In/Out</span>
                    <span class="ml-auto bg-blue-100 text-blue-600 text-xs px-2 py-1 rounded-full">Live</span>
                </a>
                
                <!-- Daily Attendance -->
                <a href="{{ route('attendance.daily') }}" class="flex items-center px-3 py-2 text-sm text-gray-700 hover:bg-white hover:text-blue-600 rounded-md transition-all duration-200 group {{ $activeRoute === 'attendance.daily' ? 'bg-white text-blue-600' : '' }}">
                    <i class="fas fa-calendar-day mr-3 text-sm text-gray-400 group-hover:text-blue-600 {{ $activeRoute === 'attendance.daily' ? 'text-blue-600' : '' }}"></i>
                    <span>Daily Attendance</span>
                </a>
                
                <!-- Timekeeping -->
                <a href="{{ route('attendance.timekeeping') }}" class="flex items-center px-3 py-2 text-sm text-gray-700 hover:bg-white hover:text-blue-600 rounded-md transition-all duration-200 group {{ $activeRoute === 'attendance.timekeeping' ? 'bg-white text-blue-600' : '' }}">
                    <i class="fas fa-stopwatch mr-3 text-sm text-gray-400 group-hover:text-blue-600 {{ $activeRoute === 'attendance.timekeeping' ? 'text-blue-600' : '' }}"></i>
                    <span>Timekeeping</span>
                </a>
                
                <!-- Import DTR -->
                <a href="{{ route('attendance.import-dtr') }}" class="flex items-center px-3 py-2 text-sm text-gray-700 hover:bg-white hover:text-blue-600 rounded-md transition-all duration-200 group {{ $activeRoute === 'attendance.import-dtr' ? 'bg-white text-blue-600' : '' }}">
                    <i class="fas fa-file-import mr-3 text-sm text-gray-400 group-hover:text-blue-600 {{ $activeRoute === 'attendance.import-dtr' ? 'text-blue-600' : '' }}"></i>
                    <span>Import DTR</span>
                    <span class="ml-auto bg-orange-100 text-orange-600 text-xs px-2 py-1 rounded-full">New</span>
                </a>
                
                <!-- Schedule Management -->
                <a href="{{ route('schedule-v2.index') }}" class="flex items-center px-3 py-2 text-sm text-gray-700 hover:bg-white hover:text-blue-600 rounded-md transition-all duration-200 group {{ $activeRoute === 'schedule-v2.index' ? 'bg-white text-blue-600' : '' }}">
                    <i class="fas fa-calendar-plus mr-3 text-sm text-gray-400 group-hover:text-blue-600 {{ $activeRoute === 'schedule-v2.index' ? 'text-blue-600' : '' }}"></i>
                    <span>Schedule Management</span>
                </a>
                
                <!-- Overtime -->
                <a href="{{ route('attendance.overtime') }}" class="flex items-center px-3 py-2 text-sm text-gray-700 hover:bg-white hover:text-blue-600 rounded-md transition-all duration-200 group {{ $activeRoute === 'attendance.overtime' ? 'bg-white text-blue-600' : '' }}">
                    <i class="fas fa-clock mr-3 text-sm text-gray-400 group-hover:text-blue-600 {{ $activeRoute === 'attendance.overtime' ? 'text-blue-600' : '' }}"></i>
                    <span>Overtime</span>
                </a>
                
                <!-- Leave Management -->
                <a href="{{ route('attendance.leave-management') }}" class="flex items-center px-3 py-2 text-sm text-gray-700 hover:bg-white hover:text-blue-600 rounded-md transition-all duration-200 group {{ $activeRoute === 'attendance.leave-management' ? 'bg-white text-blue-600' : '' }}">
                    <i class="fas fa-calendar-times mr-3 text-sm text-gray-400 group-hover:text-blue-600 {{ $activeRoute === 'attendance.leave-management' ? 'text-blue-600' : '' }}"></i>
                    <span>Leave Management</span>
                </a>
                
                <!-- Period Management -->
                <a href="{{ route('attendance.period-management.index') }}" class="flex items-center px-3 py-2 text-sm text-gray-700 hover:bg-white hover:text-blue-600 rounded-md transition-all duration-200 group {{ $activeRoute === 'attendance.period-management.index' ? 'bg-white text-blue-600' : '' }}">
                    <i class="fas fa-calendar-week mr-3 text-sm text-gray-400 group-hover:text-blue-600 {{ $activeRoute === 'attendance.period-management.index' ? 'text-blue-600' : '' }}"></i>
                    <span>Period Management</span>
                    <span class="ml-auto bg-purple-100 text-purple-600 text-xs px-2 py-1 rounded-full">New</span>
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
        <a href="#" class="flex items-center px-4 py-3 text-sm font-medium text-gray-700 hover:bg-gray-50 hover:text-blue-600 rounded-lg transition-all duration-200 group">
            <i class="fas fa-user-plus mr-3 text-lg text-gray-400 group-hover:text-blue-600"></i>
            <span>Add Employee</span>
        </a>
        
        <a href="#" class="flex items-center px-4 py-3 text-sm font-medium text-gray-700 hover:bg-gray-50 hover:text-blue-600 rounded-lg transition-all duration-200 group">
            <i class="fas fa-file-export mr-3 text-lg text-gray-400 group-hover:text-blue-600"></i>
            <span>Export Data</span>
        </a>
        @endif
        
        @if($user->role === 'employee')
        <a href="#" class="flex items-center px-4 py-3 text-sm font-medium text-gray-700 hover:bg-gray-50 hover:text-blue-600 rounded-lg transition-all duration-200 group">
            <i class="fas fa-download mr-3 text-lg text-gray-400 group-hover:text-blue-600"></i>
            <span>Download Payslip</span>
        </a>
        
        <a href="#" class="flex items-center px-4 py-3 text-sm font-medium text-gray-700 hover:bg-gray-50 hover:text-blue-600 rounded-lg transition-all duration-200 group">
            <i class="fas fa-edit mr-3 text-lg text-gray-400 group-hover:text-blue-600"></i>
            <span>Update Profile</span>
        </a>
        @endif
        
        <!-- Additional test items to ensure scrolling -->
        <a href="#" class="flex items-center px-4 py-3 text-sm font-medium text-gray-700 hover:bg-gray-50 hover:text-blue-600 rounded-lg transition-all duration-200 group">
            <i class="fas fa-file-alt mr-3 text-lg text-gray-400 group-hover:text-blue-600"></i>
            <span>Documents</span>
        </a>
        
        <a href="#" class="flex items-center px-4 py-3 text-sm font-medium text-gray-700 hover:bg-gray-50 hover:text-blue-600 rounded-lg transition-all duration-200 group">
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
    </div>
</nav>

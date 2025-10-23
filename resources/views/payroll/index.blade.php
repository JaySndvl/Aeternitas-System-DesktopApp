@extends('layouts.dashboard-base', ['user' => auth()->user(), 'activeRoute' => 'payroll.index'])

@section('title', 'Payroll Management')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Payroll Management</h1>
            <p class="mt-1 text-sm text-gray-600">Manage employee salaries, deductions, and payments</p>
        </div>
        <div class="mt-4 sm:mt-0 flex space-x-3">
            <button class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                <i class="fas fa-download mr-2"></i>
                Export Payroll
            </button>
            <button class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-lg font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                <i class="fas fa-plus mr-2"></i>
                Generate Payroll
            </button>
        </div>
    </div>

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
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors text-left flex items-center justify-between">
                            <span id="dateRangeText">{{ date('M d, Y') }} - {{ date('M d, Y') }}</span>
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
        
        <!-- Selected Period Display -->
        <div id="selectedPeriod" class="mt-4 p-3 bg-blue-50 border border-blue-200 rounded-lg">
            <div class="flex items-center">
                <i class="fas fa-calendar-check text-blue-600 mr-2"></i>
                <span class="text-sm font-medium text-blue-800">
                    Selected Period: <span id="periodDisplay">{{ date('M d, Y') }} - {{ date('M d, Y') }}</span>
                </span>
            </div>
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
                    <p class="text-lg font-semibold text-gray-900">{{ number_format($summary['total_employees']) }}</p>
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
                    <p class="text-lg font-semibold text-gray-900">₱{{ number_format($summary['gross_pay'], 2) }}</p>
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
                    <p class="text-lg font-semibold text-gray-900">₱{{ number_format($summary['total_deductions'], 2) }}</p>
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
                    <p class="text-lg font-semibold text-gray-900">₱{{ number_format($summary['net_pay'], 2) }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Employee Rate Information -->
    @if($employees->count() > 0)
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 sm:p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-medium text-gray-900">
                <i class="fas fa-calculator mr-2 text-blue-600"></i>
                Employee Daily & Hourly Rates
            </h3>
            <div class="flex items-center space-x-2">
                <span id="employeePaginationInfo" class="text-sm text-gray-500">
                    Showing 1-6 of {{ $employees->count() }} employees
                </span>
            </div>
        </div>
        
        <!-- Employee Cards Container -->
        <div id="employeeCardsContainer" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($employees->take(6) as $employee)
            <div class="border border-gray-200 rounded-lg p-4 employee-card">
                <div class="flex items-center justify-between mb-2">
                    <h4 class="font-medium text-gray-900">{{ $employee->full_name }}</h4>
                    <span class="text-xs text-gray-500">{{ $employee->employee_id }}</span>
                </div>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Monthly Salary:</span>
                        <span class="font-medium">₱{{ number_format($employee->salary, 2) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Daily Rate:</span>
                        <span class="font-medium text-blue-600">₱{{ number_format($employee->daily_rate, 2) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Hourly Rate:</span>
                        <span class="font-medium text-green-600">₱{{ number_format($employee->hourly_rate, 2) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Overtime Rate:</span>
                        <span class="font-medium text-orange-600">₱{{ number_format($employee->overtime_rate, 2) }}</span>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        
        <!-- Hidden data for JavaScript -->
        <script type="application/json" id="employeesData">
            {!! json_encode($employees->map(function($employee) {
                return [
                    'id' => $employee->id,
                    'full_name' => $employee->full_name,
                    'employee_id' => $employee->employee_id,
                    'salary' => $employee->salary,
                    'daily_rate' => $employee->daily_rate,
                    'hourly_rate' => $employee->hourly_rate,
                    'overtime_rate' => $employee->overtime_rate
                ];
            })) !!}
        </script>
        
        <!-- Pagination Controls -->
        @if($employees->count() > 6)
        <div class="mt-6 flex items-center justify-between">
            <div class="flex items-center space-x-2">
                <button id="prevEmployeePage" 
                        onclick="changeEmployeePage(-1)" 
                        class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 hover:text-gray-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                        disabled>
                    <i class="fas fa-chevron-left mr-1"></i>
                    Previous
                </button>
                <button id="nextEmployeePage" 
                        onclick="changeEmployeePage(1)" 
                        class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 hover:text-gray-700 transition-colors">
                    Next
                    <i class="fas fa-chevron-right ml-1"></i>
                </button>
            </div>
            
            <div class="flex items-center space-x-2">
                <span class="text-sm text-gray-500">Page</span>
                <span id="currentEmployeePage" class="text-sm font-medium text-gray-900">1</span>
                <span class="text-sm text-gray-500">of</span>
                <span id="totalEmployeePages" class="text-sm font-medium text-gray-900">{{ ceil($employees->count() / 6) }}</span>
            </div>
        </div>
        @endif
    </div>
    @endif

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
                <div class="text-2xl font-bold text-gray-900">{{ $summary['pending_count'] }}</div>
                <div class="text-sm text-gray-600">Pending Review</div>
            </div>
            <div class="text-center p-4 bg-gray-50 rounded-lg">
                <div class="text-2xl font-bold text-gray-900">{{ $summary['approved_count'] }}</div>
                <div class="text-sm text-gray-600">Approved</div>
            </div>
            <div class="text-center p-4 bg-gray-50 rounded-lg">
                <div class="text-2xl font-bold text-gray-900">{{ $summary['paid_count'] }}</div>
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
                    <button class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition-colors">
                        <i class="fas fa-filter mr-2"></i>
                        Filter
                    </button>
                    <button class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition-colors">
                        <i class="fas fa-sort mr-2"></i>
                        Sort
                    </button>
                </div>
            </div>
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
                            Basic Salary
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Overtime
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Allowances
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Deductions
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Net Pay
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
                    @forelse($payrolls as $payroll)
                        @php
                            $initials = strtoupper(substr($payroll->employee->first_name, 0, 1) . substr($payroll->employee->last_name, 0, 1));
                            $statusColors = [
                                'pending' => 'bg-yellow-100 text-yellow-800',
                                'approved' => 'bg-green-100 text-green-800',
                                'paid' => 'bg-blue-100 text-blue-800',
                                'rejected' => 'bg-red-100 text-red-800'
                            ];
                            $statusColor = $statusColors[$payroll->status] ?? 'bg-gray-100 text-gray-800';
                        @endphp
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        <div class="h-10 w-10 rounded-full bg-gradient-to-r from-blue-500 to-blue-600 flex items-center justify-center">
                                            <span class="text-sm font-medium text-white">{{ $initials }}</span>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">{{ $payroll->employee->full_name }}</div>
                                        <div class="text-sm text-gray-500">{{ $payroll->employee->employee_id }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $payroll->employee->department->name ?? 'N/A' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">₱{{ number_format($payroll->basic_salary, 2) }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">₱{{ number_format($payroll->overtime_pay, 2) }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">₱{{ number_format($payroll->allowances, 2) }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">₱{{ number_format($payroll->deductions, 2) }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">₱{{ number_format($payroll->net_pay, 2) }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusColor }}">
                                    <div class="w-1.5 h-1.5 rounded-full mr-1.5 {{ str_replace('text-', 'bg-', $statusColor) }}"></div>
                                    {{ ucfirst($payroll->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex space-x-2">
                                    <button onclick="openPayrollModal('{{ $payroll->id }}')" class="text-blue-600 hover:text-blue-900 transition-colors" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    @if($payroll->status === 'pending')
                                        <button class="text-green-600 hover:text-green-900 transition-colors" title="Approve">
                                            <i class="fas fa-check"></i>
                                        </button>
                                        <button class="text-red-600 hover:text-red-900 transition-colors" title="Reject">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    @elseif($payroll->status === 'approved')
                                        <button class="text-purple-600 hover:text-purple-900 transition-colors" title="Pay">
                                            <i class="fas fa-credit-card"></i>
                                        </button>
                                    @elseif($payroll->status === 'paid')
                                        <button class="text-gray-600 hover:text-gray-900 transition-colors" title="Print Payslip">
                                            <i class="fas fa-print"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-6 py-4 text-center text-gray-500">
                                No payroll records found
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="px-4 sm:px-6 py-4 border-t border-gray-200">
            <div class="flex items-center justify-between">
                <div class="flex-1 flex justify-between sm:hidden">
                    {{ $payrolls->appends(request()->query())->links('pagination::simple-tailwind') }}
                </div>
                <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm text-gray-700">
                            Showing
                            <span class="font-medium">{{ $payrolls->firstItem() }}</span>
                            to
                            <span class="font-medium">{{ $payrolls->lastItem() }}</span>
                            of
                            <span class="font-medium">{{ $payrolls->total() }}</span>
                            results
                        </p>
                    </div>
                    <div>
                        {{ $payrolls->appends(request()->query())->links('pagination::tailwind') }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Mobile Cards -->
        <div class="lg:hidden">
            <div class="p-4 space-y-4">
                @forelse($payrolls as $payroll)
                    @php
                        $initials = strtoupper(substr($payroll->employee->first_name, 0, 1) . substr($payroll->employee->last_name, 0, 1));
                        $statusColors = [
                            'pending' => 'bg-yellow-100 text-yellow-800',
                            'approved' => 'bg-green-100 text-green-800',
                            'paid' => 'bg-blue-100 text-blue-800',
                            'rejected' => 'bg-red-100 text-red-800'
                        ];
                        $statusColor = $statusColors[$payroll->status] ?? 'bg-gray-100 text-gray-800';
                    @endphp
                    <div class="border border-gray-200 rounded-lg p-4">
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center space-x-3">
                                <div class="h-10 w-10 rounded-full bg-gradient-to-r from-blue-500 to-blue-600 flex items-center justify-center">
                                    <span class="text-sm font-medium text-white">{{ $initials }}</span>
                                </div>
                                <div>
                                    <div class="font-medium text-gray-900">{{ $payroll->employee->full_name }}</div>
                                    <div class="text-sm text-gray-500">{{ $payroll->employee->department->name ?? 'N/A' }}</div>
                                </div>
                            </div>
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $statusColor }}">
                                <div class="w-1.5 h-1.5 rounded-full mr-1 {{ str_replace('text-', 'bg-', $statusColor) }}"></div>
                                {{ ucfirst($payroll->status) }}
                            </span>
                        </div>
                        <div class="grid grid-cols-2 gap-4 text-sm mb-3">
                            <div>
                                <div class="text-gray-500">Basic Salary</div>
                                <div class="font-medium">₱{{ number_format($payroll->basic_salary, 2) }}</div>
                            </div>
                            <div>
                                <div class="text-gray-500">Net Pay</div>
                                <div class="font-medium">₱{{ number_format($payroll->net_pay, 2) }}</div>
                            </div>
                            <div>
                                <div class="text-gray-500">Overtime</div>
                                <div class="font-medium">₱{{ number_format($payroll->overtime_pay, 2) }}</div>
                            </div>
                            <div>
                                <div class="text-gray-500">Deductions</div>
                                <div class="font-medium">₱{{ number_format($payroll->deductions, 2) }}</div>
                            </div>
                        </div>
                        <div class="flex justify-end space-x-2">
                            <button onclick="openPayrollModal('{{ $payroll->id }}')" class="text-blue-600 hover:text-blue-900 transition-colors">
                                <i class="fas fa-eye mr-1"></i>View
                            </button>
                            @if($payroll->status === 'pending')
                                <button class="text-green-600 hover:text-green-900 transition-colors">
                                    <i class="fas fa-check mr-1"></i>Approve
                                </button>
                            @elseif($payroll->status === 'approved')
                                <button class="text-purple-600 hover:text-purple-900 transition-colors">
                                    <i class="fas fa-credit-card mr-1"></i>Pay
                                </button>
                            @elseif($payroll->status === 'paid')
                                <button class="text-gray-600 hover:text-gray-900 transition-colors">
                                    <i class="fas fa-print mr-1"></i>Print
                                </button>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="text-center text-gray-500 py-8">
                        No payroll records found
                    </div>
                @endforelse
            </div>
            
            <!-- Mobile Pagination -->
            <div class="px-4 py-4 border-t border-gray-200">
                <div class="flex items-center justify-between">
                    <div class="flex-1 flex justify-between">
                        {{ $payrolls->appends(request()->query())->links('pagination::simple-tailwind') }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Payroll Actions -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 sm:p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Bulk Actions</h3>
        <div class="flex flex-col sm:flex-row space-y-3 sm:space-y-0 sm:space-x-3">
            <button class="inline-flex items-center justify-center px-4 py-2 border border-transparent rounded-lg font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors">
                <i class="fas fa-check-double mr-2"></i>
                Approve All Pending
            </button>
            <button class="inline-flex items-center justify-center px-4 py-2 border border-transparent rounded-lg font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                <i class="fas fa-credit-card mr-2"></i>
                Process Payments
            </button>
            <button class="inline-flex items-center justify-center px-4 py-2 border border-gray-300 rounded-lg font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                <i class="fas fa-file-pdf mr-2"></i>
                Generate Payslips
            </button>
            <button class="inline-flex items-center justify-center px-4 py-2 border border-gray-300 rounded-lg font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                <i class="fas fa-download mr-2"></i>
                Export to Excel
            </button>
        </div>
    </div>
</div>

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
                <button class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    <i class="fas fa-print mr-2"></i>Print Payslip
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function openPayrollModal() {
    document.getElementById('payrollModal').classList.remove('hidden');
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
    generateCalendar();
}

function clearDateRange() {
    selectedFromDate = null;
    selectedToDate = null;
    isSelectingFrom = true;
    
    // Update the display first
    updateDateRangeDisplay();
    updatePeriodDisplay();
    
    // Only regenerate calendar if it's visible
    const calendarPopup = document.getElementById('calendarPopup');
    if (calendarPopup && !calendarPopup.classList.contains('hidden')) {
        generateCalendar();
    }
}

function applyDateRange() {
    if (selectedFromDate && selectedToDate) {
        updatePeriodDisplay();
        toggleCalendar();
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
    return date.toISOString().split('T')[0];
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
    // Get all employee data from the server-side JSON
    const employeesDataScript = document.getElementById('employeesData');
    if (employeesDataScript) {
        const employeesData = JSON.parse(employeesDataScript.textContent);
        allEmployees = employeesData.map(employee => ({
            html: generateEmployeeCardHTML(employee),
            name: employee.full_name,
            id: employee.employee_id
        }));
        updatePaginationControls();
    } else {
        // Fallback: use the employees that are already on the page
        const employeeCards = document.querySelectorAll('.employee-card');
        allEmployees = Array.from(employeeCards).map(card => ({
            html: card.outerHTML,
            name: card.querySelector('h4').textContent,
            id: card.querySelector('span').textContent
        }));
        updatePaginationControls();
    }
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

function changeEmployeePage(direction) {
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
    const totalPages = Math.ceil(allEmployees.length / employeesPerPage);
    const startIndex = (currentEmployeePage - 1) * employeesPerPage + 1;
    const endIndex = Math.min(currentEmployeePage * employeesPerPage, allEmployees.length);
    
    // Update page info
    document.getElementById('employeePaginationInfo').textContent = 
        `Showing ${startIndex}-${endIndex} of ${allEmployees.length} employees`;
    
    // Update page numbers
    document.getElementById('currentEmployeePage').textContent = currentEmployeePage;
    document.getElementById('totalEmployeePages').textContent = totalPages;
    
    // Update button states
    const prevButton = document.getElementById('prevEmployeePage');
    const nextButton = document.getElementById('nextEmployeePage');
    
    prevButton.disabled = currentEmployeePage === 1;
    nextButton.disabled = currentEmployeePage === totalPages;
    
    if (prevButton.disabled) {
        prevButton.classList.add('opacity-50', 'cursor-not-allowed');
    } else {
        prevButton.classList.remove('opacity-50', 'cursor-not-allowed');
    }
    
    if (nextButton.disabled) {
        nextButton.classList.add('opacity-50', 'cursor-not-allowed');
    } else {
        nextButton.classList.remove('opacity-50', 'cursor-not-allowed');
    }
}

// Initialize calendar
document.addEventListener('DOMContentLoaded', function() {
    // Initialize with current month
    setDateRange('thisMonth');
    
    // Initialize employee pagination
    initializeEmployeeData();
    updatePaginationControls();
    
    // Close calendar when clicking outside
    document.addEventListener('click', function(e) {
        const calendarPopup = document.getElementById('calendarPopup');
        const dateRangeButton = document.getElementById('dateRangeButton');
        
        if (!calendarPopup.contains(e.target) && !dateRangeButton.contains(e.target)) {
            calendarPopup.classList.add('hidden');
        }
    });
});
</script>
@endsection

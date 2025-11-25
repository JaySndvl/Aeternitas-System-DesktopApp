@extends('layouts.dashboard-base', ['user' => $user, 'activeRoute' => 'attendance.period-management.index'])

@section('title', 'Period Details - ' . $period->name)

@php
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
@endphp

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white shadow-sm border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="py-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">{{ $period->name }}</h1>
                        <p class="mt-1 text-sm text-gray-600">
                            {{ $period->start_date->format('M j, Y') }} - 
                            {{ $period->end_date->format('M j, Y') }}
                        </p>
                        @if($period->description)
                            <p class="mt-1 text-sm text-gray-500">{{ $period->description }}</p>
                        @endif
                        @if(!empty($period->department_id) || !empty($period->employee_ids))
                            <div class="mt-2">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                    <i class="fas fa-filter mr-1"></i>
                                    @if(!empty($period->employee_ids) && count($period->employee_ids) > 0)
                                        {{ count($period->employee_ids) }} Employee(s) Analysis
                                    @elseif(!empty($period->department_id))
                                        Department Filtered Analysis
                                    @endif
                                </span>
                            </div>
                        @endif
                    </div>
                    <div class="flex space-x-3">
                        <a href="{{ route('attendance.period-management.preview-payroll', $period->id) }}?refresh={{ time() }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-lg font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                            <i class="fas fa-eye mr-2"></i>
                            Preview Payroll
                        </a>
                        <a href="{{ route('attendance.period-management.payroll-summary', $period->id) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-lg font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                            <i class="fas fa-chart-bar mr-2"></i>
                            Payroll Summary
                        </a>
                        <a href="{{ route('attendance.period-management.export-payroll', $period->id) }}" class="inline-flex items-center px-4 py-2 bg-purple-600 border border-transparent rounded-lg font-medium text-white hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 transition-colors">
                            <i class="fas fa-download mr-2"></i>
                            Export Payroll
                        </a>
                        <a href="{{ route('attendance.period-management.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
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
                        <h3 class="text-xl font-semibold text-gray-900">{{ $summaryData['total_employees'] }}</h3>
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
                        <h3 class="text-xl font-semibold text-gray-900">{{ $summaryData['present_days'] }}</h3>
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
                        <h3 class="text-xl font-semibold text-gray-900">{{ $summaryData['absent_days'] }}</h3>
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
                        <h3 class="text-lg font-semibold text-gray-900">{{ formatHoursToReadable($summaryData['total_scheduled_hours']) }}</h3>
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
                        <h3 class="text-lg font-semibold text-gray-900">{{ formatHoursToReadable($summaryData['total_morning_overtime_hours'] + $summaryData['total_evening_overtime_hours']) }}</h3>
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

            @php
                // Group data by employee
                $groupedData = collect($comprehensiveData)->groupBy('employee_id');
            @endphp

            <div class="space-y-4 p-6">
                @foreach($groupedData as $employeeId => $employeeRecords)
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
                                        {{ $employeeRecords->first()['employee_code'] }} - {{ $employeeRecords->first()['employee_name'] }}
                                    </h4>
                                    <p class="text-sm text-gray-500">{{ $employeeRecords->count() }} record(s)</p>
                                </div>
                            </div>
                            <div class="flex items-center space-x-4">
                                <div class="text-sm text-gray-500">
                                    @php
                                        $presentCount = $employeeRecords->where('attendance_status', 'Present')->count();
                                        $absentCount = $employeeRecords->where('attendance_status', 'Absent')->count();
                                    @endphp
                                    <span class="text-green-600 font-medium">{{ $presentCount }}P</span>
                                    <span class="text-red-600 font-medium">{{ $absentCount }}A</span>
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
                                    @foreach($employeeRecords as $index => $record)
                                    <tr class="{{ $index % 2 === 0 ? 'bg-white' : 'bg-gray-50' }} hover:bg-blue-50 cursor-pointer" onclick="showEmployeeDetails('{{ $record['employee_id'] }}', '{{ $record['date'] }}')">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $record['date_formatted'] }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            @if($record['schedule_status'] === 'Regular Holiday')
                                                <span class="text-yellow-600 font-semibold">{{ $record['schedule_in_out'] }}</span>
                                            @elseif($record['schedule_status'] === 'Special Holiday')
                                                <span class="text-pink-600 font-semibold">{{ $record['schedule_in_out'] }}</span>
                                            @elseif($record['schedule_status'] === 'Day Off')
                                                <span class="text-slate-600 font-medium">{{ $record['schedule_in_out'] }}</span>
                                            @elseif($record['schedule_status'] === 'Leave')
                                                <span class="text-purple-600 font-medium">{{ $record['schedule_in_out'] }}</span>
                                            @elseif($record['schedule_status'] === 'Holiday')
                                                <span class="text-red-600 font-medium">{{ $record['schedule_in_out'] }}</span>
                                            @else
                                                {{ $record['schedule_in_out'] }}
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            @if($record['schedule_status'] === 'Regular Holiday')
                                                <span class="text-yellow-600 font-semibold">{{ $record['working_hours'] }}</span>
                                            @elseif($record['schedule_status'] === 'Special Holiday')
                                                <span class="text-pink-600 font-semibold">{{ $record['working_hours'] }}</span>
                                            @elseif($record['schedule_status'] === 'Day Off')
                                                <span class="text-slate-600 font-medium">{{ $record['working_hours'] }}</span>
                                            @elseif($record['schedule_status'] === 'Leave')
                                                <span class="text-purple-600 font-medium">{{ $record['working_hours'] }}</span>
                                            @elseif($record['schedule_status'] === 'Holiday')
                                                <span class="text-red-600 font-medium">{{ $record['working_hours'] }}</span>
                                            @else
                                                {{ $record['working_hours'] }}
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $record['actual_in_out'] }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            @if($record['worked_hours'] === '—')
                                                <span class="text-gray-400">{{ $record['worked_hours'] }}</span>
                                            @else
                                                {{ $record['worked_hours'] }}
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            @if($record['scheduled_hours'] === '—')
                                                <span class="text-gray-400">{{ $record['scheduled_hours'] }}</span>
                                            @else
                                                {{ $record['scheduled_hours'] }}
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            @if($record['morning_overtime'] > 0)
                                                {{ formatHoursToReadable($record['morning_overtime']) }}
                                            @else
                                                <span class="text-gray-400">0 hrs</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            @if($record['evening_overtime'] > 0)
                                                {{ formatHoursToReadable($record['evening_overtime']) }}
                                            @else
                                                <span class="text-gray-400">0 hrs</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            @if($record['late_minutes'] > 0)
                                                <span class="text-red-600 font-medium">{{ $record['late_minutes'] }} min</span>
                                            @else
                                                <span class="text-gray-400">0</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            @if($record['is_night_shift'] && $record['night_differential_hours'] > 0)
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                                                     {{ formatHoursToReadable($record['night_differential_hours']) }}
                                                </span>
                                            @else
                                                <span class="text-gray-400">—</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                @if($record['attendance_status'] === 'Present') bg-green-100 text-green-800
                                                @elseif($record['attendance_status'] === 'Absent') bg-red-100 text-red-800
                                                @elseif($record['attendance_status'] === 'Error') bg-yellow-100 text-yellow-800
                                                @elseif($record['attendance_status'] === 'Day Off') bg-gray-100 text-gray-800
                                                @elseif($record['attendance_status'] === 'No Schedule') bg-gray-100 text-gray-500
                                                @else bg-gray-100 text-gray-800
                                                @endif">
                                                @if($record['attendance_status'] === 'Present')
                                                    @if($record['schedule_status'] === 'Regular Holiday')
                                                        🟢 <span class="text-yellow-600 font-semibold">{{ $record['combined_status'] }}</span>
                                                    @elseif($record['schedule_status'] === 'Special Holiday')
                                                        🟢 <span class="text-pink-600 font-semibold">{{ $record['combined_status'] }}</span>
                                                    @else
                                                        🟢 {{ $record['combined_status'] }}
                                                    @endif
                                                @elseif($record['attendance_status'] === 'Absent')
                                                    @if($record['schedule_status'] === 'Regular Holiday')
                                                        🔴 <span class="text-yellow-600 font-semibold">{{ $record['combined_status'] }}</span>
                                                    @elseif($record['schedule_status'] === 'Special Holiday')
                                                        🔴 <span class="text-pink-600 font-semibold">{{ $record['combined_status'] }}</span>
                                                    @else
                                                        🔴 {{ $record['combined_status'] }}
                                                    @endif
                                                @elseif($record['attendance_status'] === 'Error')
                                                    @if($record['schedule_status'] === 'Regular Holiday')
                                                        🟡 <span class="text-yellow-600 font-semibold">{{ $record['combined_status'] }}</span>
                                                    @elseif($record['schedule_status'] === 'Special Holiday')
                                                        🟡 <span class="text-pink-600 font-semibold">{{ $record['combined_status'] }}</span>
                                                    @else
                                                        🟡{{ $record['combined_status'] }}
                                                    @endif
                                                @elseif($record['attendance_status'] === 'Day Off')
                                                    ⚪ {{ $record['combined_status'] }}
                                                @elseif($record['attendance_status'] === 'No Schedule')
                                                    <span class="text-gray-500">{{ $record['combined_status'] }}</span>
                                                @else
                                                    ⚪ {{ $record['combined_status'] }}
                                                @endif
                                            </span>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            @if(empty($comprehensiveData))
                <div class="text-center py-12">
                    <div class="mx-auto h-16 w-16 text-gray-400">
                        <i class="fas fa-calendar-times text-4xl"></i>
                                </div>
                    <h3 class="mt-4 text-lg font-medium text-gray-900">No attendance records found</h3>
                    <p class="mt-2 text-sm text-gray-600">No attendance data available for the selected period.</p>
                                </div>
            @endif
        </div>

        <!-- Payroll Preview Section -->
        @php
            $startDate = \Carbon\Carbon::parse($period['start_date']);
            $endDate = \Carbon\Carbon::parse($period['end_date']);
            $existingPayrolls = \App\Models\Payroll::where('pay_period_start', $startDate->format('Y-m-d'))
                ->where('pay_period_end', $endDate->format('Y-m-d'))
                ->with('employee.department')
                ->get();
        @endphp

        @if($existingPayrolls->count() > 0)
        <div class="mt-6 bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900">Payroll Preview</h3>
                    <div class="flex space-x-3">
                        <a href="{{ route('attendance.period-management.payroll-summary', $period['id']) }}" class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                            <i class="fas fa-eye mr-2"></i>
                            View Details
                        </a>
                        <a href="{{ route('attendance.period-management.export-payroll', $period['id']) }}" class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                            <i class="fas fa-download mr-2"></i>
                            Export CSV
                        </a>
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
                                <h4 class="text-lg font-semibold text-gray-900">{{ $existingPayrolls->count() }}</h4>
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
                                <h4 class="text-lg font-semibold text-gray-900">₱{{ number_format($existingPayrolls->sum('gross_pay'), 2) }}</h4>
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
                                <h4 class="text-lg font-semibold text-gray-900">₱{{ number_format($existingPayrolls->sum('deductions'), 2) }}</h4>
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
                                <h4 class="text-lg font-semibold text-gray-900">₱{{ number_format($existingPayrolls->sum('net_pay'), 2) }}</h4>
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
                            @foreach($existingPayrolls->take(5) as $payroll)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ $payroll->employee->employee_id }}</div>
                                    <div class="text-sm text-gray-500">{{ $payroll->employee->full_name }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $payroll->employee->department->name ?? 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    ₱{{ number_format($payroll->basic_salary, 2) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ number_format($payroll->overtime_hours, 1) }} hrs
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    ₱{{ number_format($payroll->net_pay, 2) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        @if($payroll->status === 'pending') bg-yellow-100 text-yellow-800
                                        @elseif($payroll->status === 'processed') bg-blue-100 text-blue-800
                                        @elseif($payroll->status === 'paid') bg-green-100 text-green-800
                                        @else bg-gray-100 text-gray-800
                                        @endif">
                                        {{ ucfirst($payroll->status) }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($existingPayrolls->count() > 5)
                <div class="mt-4 text-center">
                    <p class="text-sm text-gray-500">Showing 5 of {{ $existingPayrolls->count() }} payroll records</p>
                </div>
                @endif
            </div>
        </div>
        @endif
    </div>
</div>

<script>
// Export functions
function exportToCSV() {
    const data = @json($comprehensiveData);
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
    link.setAttribute('download', 'attendance_records_{{ $period["name"] }}.csv');
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
@endsection

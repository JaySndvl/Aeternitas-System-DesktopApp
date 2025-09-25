@extends('layouts.dashboard-base', ['user' => $user, 'activeRoute' => 'attendance.timekeeping'])

@section('title', 'Timekeeping')

@section('content')
<div class="space-y-6">
    <!-- Success/Error Messages -->
    @if(session('success'))
        <div class="bg-green-50 border border-green-200 rounded-lg p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-check-circle text-green-400"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                </div>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-50 border border-red-200 rounded-lg p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-exclamation-circle text-red-400"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
                </div>
            </div>
        </div>
    @endif
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Timekeeping</h1>
            <p class="mt-1 text-sm text-gray-600">Track and manage employee time records</p>
        </div>
        <div class="mt-4 sm:mt-0 flex space-x-3">
            <button class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                <i class="fas fa-download mr-2"></i>
                Export
            </button>
            <a href="{{ route('attendance.create-record') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-lg font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                <i class="fas fa-plus mr-2"></i>
                Add Record
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 sm:p-6">
        <form method="GET" action="{{ route('attendance.timekeeping') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div>
                <label for="employee_id" class="block text-sm font-medium text-gray-700 mb-2">Employee</label>
                <select name="employee_id" id="employee_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors">
                    <option value="">All Employees</option>
                    @foreach($employees as $employee)
                        <option value="{{ $employee->id }}" {{ request('employee_id') == $employee->id ? 'selected' : '' }}>
                            {{ $employee->full_name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="department_id" class="block text-sm font-medium text-gray-700 mb-2">Department</label>
                <select name="department_id" id="department_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors">
                    <option value="">All Departments</option>
                    @foreach($departments as $department)
                        <option value="{{ $department->id }}" {{ request('department_id') == $department->id ? 'selected' : '' }}>
                            {{ $department->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="date_from" class="block text-sm font-medium text-gray-700 mb-2">From Date</label>
                <input type="date" name="date_from" id="date_from" value="{{ request('date_from') }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors">
            </div>
            <div>
                <label for="date_to" class="block text-sm font-medium text-gray-700 mb-2">To Date</label>
                <input type="date" name="date_to" id="date_to" value="{{ request('date_to') }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors">
            </div>
        </div>
        <div class="mt-4 flex justify-end space-x-3">
            <a href="{{ route('attendance.timekeeping') }}" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
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
                    <p class="text-lg font-semibold text-gray-900">{{ \App\Helpers\TimezoneHelper::formatHours($summary['total_hours']) }}</p>
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
                    <p class="text-lg font-semibold text-gray-900">{{ \App\Helpers\TimezoneHelper::formatHours($summary['regular_hours']) }}</p>
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
                    <p class="text-lg font-semibold text-gray-900">{{ \App\Helpers\TimezoneHelper::formatHours($summary['overtime_hours']) }}</p>
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
                    <p class="text-lg font-semibold text-gray-900">{{ \App\Helpers\TimezoneHelper::formatHours($summary['average_hours']) }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Timekeeping Records -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-4 sm:px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Timekeeping Records</h3>
            <p class="mt-1 text-sm text-gray-600">Employee time records for the selected period</p>
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
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($attendanceRecords as $record)
                        @php
                            $initials = strtoupper(substr($record->employee->first_name, 0, 1) . substr($record->employee->last_name, 0, 1));
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
                                        <div class="text-sm font-medium text-gray-900">{{ $record->employee->full_name }}</div>
                                        <div class="text-sm text-gray-500">{{ $record->employee->department->name ?? 'N/A' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ \Carbon\Carbon::parse($record->date)->format('M d, Y') }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">
                                    @if($record->time_in)
                                        {{ \Carbon\Carbon::parse($record->time_in)->format('g:i A') }}
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">
                                    @if($record->time_out)
                                        {{ \Carbon\Carbon::parse($record->time_out)->format('g:i A') }}
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">
                                    @php
                                        $breakDuration = 0;
                                        
                                        if ($record->break_start && $record->break_end) {
                                            $breakStart = \Carbon\Carbon::parse($record->break_start);
                                            $breakEnd = \Carbon\Carbon::parse($record->break_end);
                                            $breakMinutes = $breakStart->diffInMinutes($breakEnd);
                                            $breakDuration = $breakMinutes / 60;
                                            
                                        }
                                    @endphp
                                    @if($breakDuration > 0)
                                        {{ \App\Helpers\TimezoneHelper::formatHours($breakDuration) }}
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">
                                    @if($record->total_hours)
                                        {{ \App\Helpers\TimezoneHelper::formatHours($record->total_hours) }}
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">
                                    @if($record->overtime_hours > 0)
                                        {{ \App\Helpers\TimezoneHelper::formatHours($record->overtime_hours) }}
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php
                                    $statusColors = [
                                        'present' => 'bg-green-100 text-green-800',
                                        'absent' => 'bg-red-100 text-red-800',
                                        'late' => 'bg-yellow-100 text-yellow-800',
                                        'half_day' => 'bg-blue-100 text-blue-800'
                                    ];
                                    $statusColor = $statusColors[$record->status] ?? 'bg-gray-100 text-gray-800';
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusColor }}">
                                    <div class="w-1.5 h-1.5 rounded-full mr-1.5 {{ str_replace('text-', 'bg-', $statusColor) }}"></div>
                                    {{ ucfirst(str_replace('_', ' ', $record->status)) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex space-x-2">
                                    <button class="text-blue-600 hover:text-blue-900 transition-colors" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="text-red-600 hover:text-red-900 transition-colors" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-4 text-center text-gray-500">
                                No attendance records found
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="px-4 sm:px-6 py-4 border-t border-gray-200">
            <!-- Mobile Pagination -->
            <div class="sm:hidden">
                {{ $attendanceRecords->appends(request()->query())->links('pagination::default') }}
            </div>
            
            <!-- Desktop Pagination -->
            <div class="hidden sm:block">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-700">
                            Showing
                            <span class="font-medium">{{ $attendanceRecords->firstItem() }}</span>
                            to
                            <span class="font-medium">{{ $attendanceRecords->lastItem() }}</span>
                            of
                            <span class="font-medium">{{ $attendanceRecords->total() }}</span>
                            results
                        </p>
                    </div>
                    <div>
                        @if($attendanceRecords->hasPages())
                            <div class="flex items-center space-x-2">
                                @if($attendanceRecords->onFirstPage())
                                    <span class="px-3 py-2 text-sm text-gray-400 bg-gray-100 rounded">Previous</span>
                                @else
                                    <a href="{{ $attendanceRecords->previousPageUrl() }}" class="px-3 py-2 text-sm text-blue-600 bg-white border border-gray-300 rounded hover:bg-gray-50">Previous</a>
                                @endif
                                
                                @for($i = 1; $i <= $attendanceRecords->lastPage(); $i++)
                                    @if($i == $attendanceRecords->currentPage())
                                        <span class="px-3 py-2 text-sm text-white bg-blue-600 rounded">{{ $i }}</span>
                                    @else
                                        <a href="{{ $attendanceRecords->url($i) }}" class="px-3 py-2 text-sm text-blue-600 bg-white border border-gray-300 rounded hover:bg-gray-50">{{ $i }}</a>
                                    @endif
                                @endfor
                                
                                @if($attendanceRecords->hasMorePages())
                                    <a href="{{ $attendanceRecords->nextPageUrl() }}" class="px-3 py-2 text-sm text-blue-600 bg-white border border-gray-300 rounded hover:bg-gray-50">Next</a>
                                @else
                                    <span class="px-3 py-2 text-sm text-gray-400 bg-gray-100 rounded">Next</span>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Mobile Cards -->
        <div class="lg:hidden">
            <div class="p-4 space-y-4">
                @forelse($attendanceRecords as $record)
                    @php
                        $initials = strtoupper(substr($record->employee->first_name, 0, 1) . substr($record->employee->last_name, 0, 1));
                    @endphp
                    <div class="border border-gray-200 rounded-lg p-4">
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center space-x-3">
                                <div class="h-10 w-10 rounded-full bg-gradient-to-r from-blue-500 to-blue-600 flex items-center justify-center">
                                    <span class="text-sm font-medium text-white">{{ $initials }}</span>
                                </div>
                                <div>
                                    <div class="font-medium text-gray-900">{{ $record->employee->full_name }}</div>
                                    <div class="text-sm text-gray-500">{{ $record->employee->department->name ?? 'N/A' }}</div>
                                </div>
                            </div>
                            @php
                                $statusColors = [
                                    'present' => 'bg-green-100 text-green-800',
                                    'absent' => 'bg-red-100 text-red-800',
                                    'late' => 'bg-yellow-100 text-yellow-800',
                                    'half_day' => 'bg-blue-100 text-blue-800'
                                ];
                                $statusColor = $statusColors[$record->status] ?? 'bg-gray-100 text-gray-800';
                            @endphp
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $statusColor }}">
                                <div class="w-1.5 h-1.5 rounded-full mr-1 {{ str_replace('text-', 'bg-', $statusColor) }}"></div>
                                {{ ucfirst(str_replace('_', ' ', $record->status)) }}
                            </span>
                        </div>
                        <div class="grid grid-cols-2 gap-4 text-sm mb-3">
                            <div>
                                <div class="text-gray-500">Date</div>
                                <div class="font-medium">{{ \Carbon\Carbon::parse($record->date)->format('M d, Y') }}</div>
                            </div>
                            <div>
                                <div class="text-gray-500">Total Hours</div>
                                <div class="font-medium">
                                    @if($record->total_hours)
                                        {{ \App\Helpers\TimezoneHelper::formatHours($record->total_hours) }}
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </div>
                            </div>
                            <div>
                                <div class="text-gray-500">Time In</div>
                                <div class="font-medium">
                                    @if($record->time_in)
                                        {{ \Carbon\Carbon::parse($record->time_in)->format('g:i A') }}
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </div>
                            </div>
                            <div>
                                <div class="text-gray-500">Time Out</div>
                                <div class="font-medium">
                                    @if($record->time_out)
                                        {{ \Carbon\Carbon::parse($record->time_out)->format('g:i A') }}
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </div>
                            </div>
                            <div>
                                <div class="text-gray-500">Break Time</div>
                                <div class="font-medium">
                                    @php
                                        $breakDuration = 0;
                                        if ($record->break_start && $record->break_end) {
                                            $breakStart = \Carbon\Carbon::parse($record->break_start);
                                            $breakEnd = \Carbon\Carbon::parse($record->break_end);
                                            $breakMinutes = $breakStart->diffInMinutes($breakEnd);
                                            $breakDuration = $breakMinutes / 60;
                                        }
                                    @endphp
                                    @if($breakDuration > 0)
                                        {{ \App\Helpers\TimezoneHelper::formatHours($breakDuration) }}
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="flex justify-end space-x-2">
                            <button class="text-blue-600 hover:text-blue-900 transition-colors">
                                <i class="fas fa-edit mr-1"></i>Edit
                            </button>
                            <button class="text-red-600 hover:text-red-900 transition-colors">
                                <i class="fas fa-trash mr-1"></i>Delete
                            </button>
                        </div>
                    </div>
                @empty
                    <div class="text-center text-gray-500 py-8">
                        No attendance records found
                    </div>
                @endforelse
            </div>
            
            <!-- Mobile Pagination -->
            <div class="px-4 py-4 border-t border-gray-200">
                <div class="flex items-center justify-between">
                    <div class="flex-1 flex justify-between">
                        {{ $attendanceRecords->appends(request()->query())->links('pagination::default') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function applyFilters() {
    const employee = document.getElementById('employee').value;
    const department = document.getElementById('department').value;
    const dateFrom = document.getElementById('dateFrom').value;
    const dateTo = document.getElementById('dateTo').value;
    
    // This will be implemented when backend is ready
    console.log('Applying filters:', { employee, department, dateFrom, dateTo });
}
</script>
@endsection

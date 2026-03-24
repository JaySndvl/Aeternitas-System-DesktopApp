@extends('layouts.dashboard-base', ['user' => $user, 'activeRoute' => 'attendance.temp-timekeeping'])

@section('title', 'Temporary Timekeeping Records')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Temporary Timekeeping Records</h1>
            <p class="mt-1 text-sm text-gray-600">Review and manage imported DTR data before final processing</p>
            @if($batchInfo)
                <div class="mt-2 inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                    <i class="fas fa-filter mr-1"></i>
                    Filtered by Batch: {{ substr($batchInfo['batch_id'], 0, 20) }}...
                </div>
            @endif
        </div>
        <div class="mt-4 sm:mt-0 flex space-x-2">
            @if($batchInfo)
                <a href="{{ route('attendance.temp-timekeeping') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                    <i class="fas fa-times mr-2"></i>
                    Clear Filter
                </a>
            @endif
            <a href="{{ route('attendance.timekeeping') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                <i class="fas fa-arrow-left mr-2"></i>
                Back to Timekeeping
            </a>
        </div>
    </div>

    @if($batchInfo)
    <!-- Batch Information -->
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
        <div class="flex items-start">
            <div class="flex-shrink-0">
                <i class="fas fa-info-circle text-blue-500"></i>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-blue-800">Batch Information</h3>
                <div class="mt-2 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 text-sm">
                    <div>
                        <p class="text-blue-700 font-medium">Total Records</p>
                        <p class="text-blue-900 text-lg font-bold">{{ $batchInfo['total_records'] }}</p>
                    </div>
                    <div>
                        <p class="text-blue-700 font-medium">Employees</p>
                        <p class="text-blue-900 text-lg font-bold">{{ $batchInfo['employees'] }}</p>
                    </div>
                    <div>
                        <p class="text-blue-700 font-medium">Date Range</p>
                        <p class="text-blue-900">{{ \Carbon\Carbon::parse($batchInfo['date_range']['start'])->format('M d') }} - {{ \Carbon\Carbon::parse($batchInfo['date_range']['end'])->format('M d, Y') }}</p>
                    </div>
                    <div>
                        <p class="text-blue-700 font-medium">Created</p>
                        <p class="text-blue-900">{{ $batchInfo['created_at']->diffForHumans() }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
        <div class="bg-blue-50 rounded-lg p-4">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <i class="fas fa-database text-blue-600"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-blue-800">Total Records</p>
                    <p class="text-2xl font-bold text-blue-900">{{ $allRecords->count() }}</p>
                </div>
            </div>
        </div>
        
        <div class="bg-green-50 rounded-lg p-4">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <i class="fas fa-check-circle text-green-600"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-green-800">Processed</p>
                    <p class="text-2xl font-bold text-green-900">{{ $allRecords->where('is_processed', true)->count() }}</p>
                </div>
            </div>
        </div>
        
        <div class="bg-yellow-50 rounded-lg p-4">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <i class="fas fa-clock text-yellow-600"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-yellow-800">Pending</p>
                    <p class="text-2xl font-bold text-yellow-900">{{ $allRecords->where('is_processed', false)->count() }}</p>
                </div>
            </div>
        </div>
        
        <div class="bg-purple-50 rounded-lg p-4">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <i class="fas fa-layer-group text-purple-600"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-purple-800">Batches</p>
                    <p class="text-2xl font-bold text-purple-900">{{ $allRecords->groupBy('import_batch_id')->count() }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Records by Employee -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="px-4 py-5 sm:p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">Records by Employee</h3>
                <div class="text-sm text-gray-500">
                    Page {{ $paginationInfo['current_page'] }} of {{ $paginationInfo['total_pages'] }} • 
                    Showing {{ $groupedRecords->count() }} of {{ $paginationInfo['total_employees'] }} employees • {{ $allRecords->count() }} total records
                </div>
            </div>
            
            @if($groupedRecords->count() > 0)
                <div class="space-y-4">
                    @foreach($groupedRecords as $employeeId => $employeeData)
                    <div class="border border-gray-200 rounded-lg" x-data="{ open: false }">
                        <!-- Employee Header (Always Visible) -->
                        <div class="bg-gray-50 px-4 py-3 cursor-pointer hover:bg-gray-100 transition-colors" @click="open = !open">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-3">
                                    <div class="flex-shrink-0">
                                        <i class="fas fa-user text-gray-400"></i>
                                    </div>
                                    <div>
                                        <h4 class="text-sm font-medium text-gray-900">{{ $employeeData['employee_id'] }}</h4>
                                        <p class="text-sm text-gray-500">{{ $employeeData['employee_name'] ?? 'Unknown Employee' }}</p>
                                        <div class="flex items-center space-x-4 mt-1">
                                            <span class="text-xs text-gray-500">{{ $employeeData['total_records'] }} records</span>
                                            <span class="text-xs text-gray-500">
                                                {{ \Carbon\Carbon::parse($employeeData['date_range']['start'])->format('M d') }} - 
                                                {{ \Carbon\Carbon::parse($employeeData['date_range']['end'])->format('M d, Y') }}
                                            </span>
                                            @if($employeeData['pending_records'] > 0)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                    {{ $employeeData['pending_records'] }} Pending
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                    All Processed
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-4">
                                    <!-- Status Summary -->
                                    <div class="flex space-x-1">
                                        @foreach($employeeData['status_summary'] as $status => $count)
                                            @php
                                                $statusColors = [
                                                    'present' => 'bg-green-100 text-green-800',
                                                    'late' => 'bg-yellow-100 text-yellow-800',
                                                    'absent' => 'bg-red-100 text-red-800',
                                                    'half_day' => 'bg-orange-100 text-orange-800',
                                                    'day_off' => 'bg-gray-100 text-gray-800',
                                                    'leave' => 'bg-yellow-100 text-yellow-800',
                                                    'holiday' => 'bg-purple-100 text-purple-800',
                                                    'overtime' => 'bg-orange-100 text-orange-800',
                                                    'error' => 'bg-red-200 text-red-900'
                                                ];
                                            @endphp
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium {{ $statusColors[$status] ?? 'bg-gray-100 text-gray-800' }}">
                                                {{ $count }} {{ ucfirst(str_replace('_', ' ', $status)) }}
                                            </span>
                                        @endforeach
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
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                <input type="checkbox" id="select-all-{{ $employeeId }}" class="select-all-checkbox rounded border-gray-300 text-blue-600 focus:ring-blue-500" onchange="toggleAllRecords('{{ $employeeId }}')">
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Time In</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Time Out</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Hours</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Overtime</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Processed</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($employeeData['records'] as $record)
                                        <tr class="{{ $record->is_processed ? 'bg-green-50' : 'bg-white' }}">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                <input type="checkbox" 
                                                       name="selected_records[]" 
                                                       value="{{ $record->id }}" 
                                                       class="record-checkbox rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                                       data-employee="{{ $employeeId }}"
                                                       {{ $record->is_processed ? 'disabled' : '' }}>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ \Carbon\Carbon::parse($record->date)->format('M d, Y') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $record->time_in ? \Carbon\Carbon::parse($record->time_in)->format('H:i') : '-' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $record->time_out ? \Carbon\Carbon::parse($record->time_out)->format('H:i') : '-' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $record->total_hours }}h
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $record->overtime_hours ?? 0 }}h
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                @php
                                                    $statusColors = [
                                                        'present' => 'bg-green-100 text-green-800',
                                                        'late' => 'bg-yellow-100 text-yellow-800',
                                                        'absent' => 'bg-red-100 text-red-800',
                                                        'half_day' => 'bg-orange-100 text-orange-800',
                                                        'day_off' => 'bg-gray-100 text-gray-800',
                                                        'leave' => 'bg-yellow-100 text-yellow-800',
                                                        'holiday' => 'bg-purple-100 text-purple-800',
                                                        'overtime' => 'bg-orange-100 text-orange-800',
                                                        'error' => 'bg-red-200 text-red-900 border border-red-300'
                                                    ];
                                                @endphp
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$record->status] ?? 'bg-gray-100 text-gray-800' }}">
                                                    {{ ucfirst(str_replace('_', ' ', $record->status)) }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                @if($record->is_processed)
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                        <i class="fas fa-check mr-1"></i>
                                                        Processed
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                        <i class="fas fa-clock mr-1"></i>
                                                        Pending
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $record->created_at->format('M d, Y H:i') }}
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
                
                <!-- Employee Pagination -->
                @if($paginationInfo['total_pages'] > 1)
                <div class="mt-6 flex items-center justify-between">
                    <div class="flex items-center space-x-2">
                        @if($paginationInfo['has_prev_page'])
                            <a href="{{ request()->fullUrlWithQuery(['page' => $paginationInfo['prev_page']]) }}" 
                               class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                <i class="fas fa-chevron-left mr-1"></i>
                                Previous
                            </a>
                        @else
                            <span class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-400 bg-gray-100 cursor-not-allowed">
                                <i class="fas fa-chevron-left mr-1"></i>
                                Previous
                            </span>
                        @endif
                        
                        <span class="text-sm text-gray-700">
                            Page {{ $paginationInfo['current_page'] }} of {{ $paginationInfo['total_pages'] }}
                        </span>
                        
                        @if($paginationInfo['has_next_page'])
                            <a href="{{ request()->fullUrlWithQuery(['page' => $paginationInfo['next_page']]) }}" 
                               class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                Next
                                <i class="fas fa-chevron-right ml-1"></i>
                            </a>
                        @else
                            <span class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-400 bg-gray-100 cursor-not-allowed">
                                Next
                                <i class="fas fa-chevron-right ml-1"></i>
                            </span>
                        @endif
                    </div>
                    
                    <div class="flex items-center space-x-1">
                        @for($i = 1; $i <= $paginationInfo['total_pages']; $i++)
                            @if($i == $paginationInfo['current_page'])
                                <span class="inline-flex items-center px-3 py-2 border border-blue-500 rounded-md text-sm font-medium text-blue-600 bg-blue-50">
                                    {{ $i }}
                                </span>
                            @else
                                <a href="{{ request()->fullUrlWithQuery(['page' => $i]) }}" 
                                   class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                    {{ $i }}
                                </a>
                            @endif
                        @endfor
                    </div>
                </div>
                @endif
            @else
                <div class="text-center py-12">
                    <i class="fas fa-database text-gray-400 text-4xl mb-4"></i>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No temporary records found</h3>
                    <p class="text-gray-500 mb-6">Import DTR data to see temporary records here.</p>
                    <a href="{{ route('attendance.import-dtr') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-lg font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                        <i class="fas fa-upload mr-2"></i>
                        Import DTR Data
                    </a>
                </div>
            @endif
        </div>
    </div>

    <!-- Approval Section -->
    @if($groupedRecords->count() > 0)
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="px-4 py-5 sm:p-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-lg font-medium text-gray-900">Approve Selected Records</h3>
                    <p class="text-sm text-gray-600">Select records to approve and save to attendance records</p>
                </div>
                <div class="text-sm text-gray-500">
                    <span id="selected-count">0</span> records selected
                </div>
            </div>
            
            <form id="approve-form" method="POST" action="{{ route('attendance.temp-timekeeping.approve') }}">
                @csrf
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <button type="button" onclick="selectAllRecords()" class="text-sm text-blue-600 hover:text-blue-800 font-medium">
                            <i class="fas fa-check-square mr-1"></i>
                            Select All
                        </button>
                        <button type="button" onclick="deselectAllRecords()" class="text-sm text-gray-600 hover:text-gray-800 font-medium">
                            <i class="fas fa-square mr-1"></i>
                            Deselect All
                        </button>
                    </div>
                    
                    <div class="flex items-center space-x-3">
                        <button type="button" onclick="deselectAllRecords()" class="px-4 py-2 border border-gray-300 rounded-lg font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                            Cancel
                        </button>
                        <button type="submit" id="approve-btn" disabled class="px-6 py-2 bg-green-600 border border-transparent rounded-lg font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                            <i class="fas fa-check mr-2"></i>
                            Approve & Save to Attendance Records
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    @endif
</div>
<script>
// Make functions globally available
window.toggleAllRecords = function(employeeId) {
    const selectAllCheckbox = document.getElementById('select-all-' + employeeId);
    const recordCheckboxes = document.querySelectorAll(`input[data-employee="${employeeId}"].record-checkbox`);
    
    recordCheckboxes.forEach(checkbox => {
        if (!checkbox.disabled) {
            checkbox.checked = selectAllCheckbox.checked;
        }
    });
    
    updateSelectedCount();
}

// Select all records across all employees
window.selectAllRecords = function() {
    const recordCheckboxes = document.querySelectorAll('.record-checkbox:not([disabled])');
    recordCheckboxes.forEach(checkbox => {
        checkbox.checked = true;
    });
    
    // Update all "Select All" checkboxes for each employee
    const employeeIds = [...new Set(Array.from(document.querySelectorAll('.record-checkbox')).map(cb => cb.dataset.employee))];
    employeeIds.forEach(employeeId => {
        const selectAllCheckbox = document.getElementById('select-all-' + employeeId);
        const employeeCheckboxes = document.querySelectorAll(`input[data-employee="${employeeId}"].record-checkbox:not([disabled])`);
        const allChecked = Array.from(employeeCheckboxes).every(cb => cb.checked);
        selectAllCheckbox.checked = allChecked;
    });
    
    updateSelectedCount();
}

// Deselect all records
window.deselectAllRecords = function() {
    const recordCheckboxes = document.querySelectorAll('.record-checkbox');
    recordCheckboxes.forEach(checkbox => {
        checkbox.checked = false;
    });
    
    // Update all "Select All" checkboxes
    const selectAllCheckboxes = document.querySelectorAll('.select-all-checkbox');
    selectAllCheckboxes.forEach(checkbox => {
        checkbox.checked = false;
    });
    
    updateSelectedCount();
}

// Update the selected count display
window.updateSelectedCount = function() {
    const selectedCheckboxes = document.querySelectorAll('.record-checkbox:checked:not([disabled])');
    const selectedCount = selectedCheckboxes.length;
    
    document.getElementById('selected-count').textContent = selectedCount;
    
    // Enable/disable approve button
    const approveBtn = document.getElementById('approve-btn');
    if (approveBtn) {
        approveBtn.disabled = selectedCount === 0;
    }
}

// Handle individual checkbox changes
document.addEventListener('DOMContentLoaded', function() {
    // Add event listeners to all record checkboxes
    document.querySelectorAll('.record-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const employeeId = this.dataset.employee;
            const selectAllCheckbox = document.getElementById('select-all-' + employeeId);
            const employeeCheckboxes = document.querySelectorAll(`input[data-employee="${employeeId}"].record-checkbox`);
            const enabledCheckboxes = Array.from(employeeCheckboxes).filter(cb => !cb.disabled);
            const allChecked = enabledCheckboxes.length > 0 && enabledCheckboxes.every(cb => cb.checked);
            const someChecked = enabledCheckboxes.some(cb => cb.checked);
            
            selectAllCheckbox.checked = allChecked;
            selectAllCheckbox.indeterminate = someChecked && !allChecked;
            
            updateSelectedCount();
        });
    });
    
    // Handle form submission
    document.getElementById('approve-form').addEventListener('submit', function(e) {
        const selectedCheckboxes = document.querySelectorAll('.record-checkbox:checked:not([disabled])');
        
        if (selectedCheckboxes.length === 0) {
            e.preventDefault();
            alert('Please select at least one record to approve.');
            return;
        }
        
        // Add selected record IDs to form
        selectedCheckboxes.forEach(checkbox => {
            const hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.name = 'selected_records[]';
            hiddenInput.value = checkbox.value;
            this.appendChild(hiddenInput);
        });
        
        // Show confirmation
        if (!confirm(`Are you sure you want to approve ${selectedCheckboxes.length} selected records? This will save them to the attendance records table.`)) {
            e.preventDefault();
        }
    });
    
    // Initial count update
    updateSelectedCount();
});
</script>
@endsection

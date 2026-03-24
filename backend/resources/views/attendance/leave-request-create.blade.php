@extends('layouts.dashboard-base', ['user' => $user, 'activeRoute' => 'attendance.leave-management'])

@section('title', 'New Leave Request')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between space-y-4 sm:space-y-0">
            <div>
                <h1 class="text-xl sm:text-2xl font-bold text-gray-900">New Leave Request</h1>
                <p class="mt-1 text-sm text-gray-500">Submit a leave request for approval</p>
            </div>
            <a href="{{ route('attendance.leave-management') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                <i class="fas fa-arrow-left mr-2"></i>
                <span class="hidden sm:inline">Back to Leave Management</span>
                <span class="sm:hidden">Back</span>
            </a>
        </div>

        <!-- Success/Error Messages -->
        @if(session('success'))
        <div class="bg-green-50 border border-green-200 rounded-lg p-4">
            <div class="flex items-center">
                <i class="fas fa-check-circle text-green-600 mr-3"></i>
                <p class="text-sm font-medium text-green-900">{{ session('success') }}</p>
            </div>
        </div>
        @endif

        @if(session('error'))
        <div class="bg-red-50 border border-red-200 rounded-lg p-4">
            <div class="flex items-center">
                <i class="fas fa-exclamation-circle text-red-600 mr-3"></i>
                <p class="text-sm font-medium text-red-900">{{ session('error') }}</p>
            </div>
        </div>
        @endif

        @if($errors->any())
        <div class="bg-red-50 border border-red-200 rounded-lg p-4">
            <div class="flex items-start">
                <i class="fas fa-exclamation-circle text-red-600 mr-3 mt-0.5"></i>
                <div>
                    <h4 class="text-sm font-medium text-red-900">Please fix the following errors:</h4>
                    <ul class="mt-2 text-sm text-red-700 list-disc list-inside">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
        @endif

        <!-- Leave Balance Info (for employees) -->
        <div id="leaveBalanceContainer" class="bg-blue-50 border border-blue-200 rounded-lg p-4 {{ ($employee && $leaveBalance) ? '' : 'hidden' }}">
            <h3 class="text-sm font-medium text-blue-900 mb-3" id="balanceTitle">{{ in_array($user->role, ['admin', 'hr']) ? 'Employee Leave Balance' : 'Your Leave Balance' }}</h3>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3" id="balanceGrid">
                @if($employee && $leaveBalance)
                <div>
                    <div class="text-xs text-blue-700">Vacation</div>
                    <div class="text-sm font-semibold text-blue-900" id="balance-vacation">{{ $availableDays['vacation'] ?? 0 }} days</div>
                </div>
                <div>
                    <div class="text-xs text-blue-700">Sick</div>
                    <div class="text-sm font-semibold text-blue-900" id="balance-sick">{{ $availableDays['sick'] ?? 0 }} days</div>
                </div>
                <div>
                    <div class="text-xs text-blue-700">Personal</div>
                    <div class="text-sm font-semibold text-blue-900" id="balance-personal">{{ $availableDays['personal'] ?? 0 }} days</div>
                </div>
                <div>
                    <div class="text-xs text-blue-700">Emergency</div>
                    <div class="text-sm font-semibold text-blue-900" id="balance-emergency">{{ $availableDays['emergency'] ?? 0 }} days</div>
                </div>
                @endif
            </div>
        </div>

        <!-- Form -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <!-- Hidden data for JavaScript -->
            <div id="leave-request-data" 
                 data-available-days='{!! json_encode($availableDays ?? []) !!}' 
                 data-should-check-balance='{!! json_encode($employee && $leaveBalance ? true : false) !!}'
                 style="display: none;"></div>
            <form method="POST" action="{{ route('attendance.leave-management.store') }}" class="p-4 sm:p-6 space-y-6" id="leaveRequestForm">
                @csrf
                
                <!-- Employee Selection (HR/Admin only) -->
                @if(in_array($user->role, ['admin', 'hr']))
                <div>
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Employee Information</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                        <div class="sm:col-span-2">
                            <label for="employee_id" class="block text-sm font-medium text-gray-700 mb-2">Select Employee</label>
                            <select name="employee_id" id="employee_id" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-white text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('employee_id') border-red-500 @enderror">
                                <option value="">Select Employee</option>
                                @foreach($employees as $emp)
                                    <option value="{{ $emp->id }}" {{ old('employee_id') == $emp->id ? 'selected' : '' }}>
                                        {{ $emp->full_name }} - {{ $emp->department->name ?? 'No Department' }}
                                    </option>
                                @endforeach
                            </select>
                            @error('employee_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
                @else
                    <input type="hidden" name="employee_id" value="{{ $employee->id }}">
                @endif

                <!-- Leave Request Details -->
                <div>
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Leave Request Details</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                        <div>
                            <label for="leave_type" class="block text-sm font-medium text-gray-700 mb-2">Leave Type</label>
                            <select name="leave_type" id="leave_type" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-white text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('leave_type') border-red-500 @enderror">
                                <option value="">Select Leave Type</option>
                                <option value="vacation" {{ old('leave_type') == 'vacation' ? 'selected' : '' }}>Vacation Leave</option>
                                <option value="sick" {{ old('leave_type') == 'sick' ? 'selected' : '' }}>Sick Leave</option>
                                <option value="personal" {{ old('leave_type') == 'personal' ? 'selected' : '' }}>Personal Leave</option>
                                <option value="emergency" {{ old('leave_type') == 'emergency' ? 'selected' : '' }}>Emergency Leave</option>
                                <option value="maternity" {{ old('leave_type') == 'maternity' ? 'selected' : '' }}>Maternity Leave</option>
                                <option value="paternity" {{ old('leave_type') == 'paternity' ? 'selected' : '' }}>Paternity Leave</option>
                                <option value="bereavement" {{ old('leave_type') == 'bereavement' ? 'selected' : '' }}>Bereavement Leave</option>
                                <option value="study" {{ old('leave_type') == 'study' ? 'selected' : '' }}>Study Leave</option>
                            </select>
                            @error('leave_type')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="days_requested" class="block text-sm font-medium text-gray-700 mb-2">Duration (Days)</label>
                            <input type="number" name="days_requested" id="days_requested" min="1" readonly
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 text-gray-700 cursor-not-allowed @error('days_requested') border-red-500 @enderror">
                            <p class="mt-1 text-xs text-gray-500">Auto-calculated from dates</p>
                            @error('days_requested')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="start_date" class="block text-sm font-medium text-gray-700 mb-2">Start Date</label>
                            <input type="text" name="start_date" id="start_date" value="{{ old('start_date') }}" required
                                placeholder="Select start date"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-white text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('start_date') border-red-500 @enderror">
                            @error('start_date')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="end_date" class="block text-sm font-medium text-gray-700 mb-2">End Date</label>
                            <input type="text" name="end_date" id="end_date" value="{{ old('end_date') }}" required
                                placeholder="Select end date"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-white text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('end_date') border-red-500 @enderror">
                            @error('end_date')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="sm:col-span-2">
                            <label for="reason" class="block text-sm font-medium text-gray-700 mb-2">Reason</label>
                            <textarea name="reason" id="reason" rows="4" required
                                placeholder="Please provide a reason for your leave request..."
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-white text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('reason') border-red-500 @enderror">{{ old('reason') }}</textarea>
                            <p class="mt-1 text-xs text-gray-500">Maximum 500 characters</p>
                            @error('reason')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Available Balance Warning -->
                @if($employee && $leaveBalance)
                <div id="balanceWarning" class="hidden bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                    <div class="flex items-start">
                        <i class="fas fa-exclamation-triangle text-yellow-600 mt-0.5 mr-3"></i>
                        <div>
                            <h4 class="text-sm font-medium text-yellow-900">Insufficient Leave Balance</h4>
                            <p class="mt-1 text-sm text-yellow-700">You don't have enough leave balance for the selected leave type and duration.</p>
                            <p class="mt-1 text-sm text-yellow-700" id="balanceInfo"></p>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Submit Button -->
                <div class="flex justify-end space-x-3 pt-6 border-t border-gray-200">
                    <a href="{{ route('attendance.leave-management') }}" class="px-4 py-2 border border-gray-300 rounded-lg font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                        Cancel
                    </a>
                    <button type="submit" class="px-4 py-2 bg-blue-600 border border-transparent rounded-lg font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                        <i class="fas fa-paper-plane mr-2"></i>
                        Submit Request
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Flatpickr CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

<style>
    /* Ensure select dropdowns and options are visible */
    select {
        color: #111827 !important; /* text-gray-900 */
        background-color: #ffffff !important; /* bg-white */
    }
    
    /* Force specific select dropdowns to show dark text */
    #employee_id,
    #leave_type {
        color: #111827 !important;
        background-color: #ffffff !important;
    }
    
    select option {
        color: #111827 !important; /* text-gray-900 */
        background-color: #ffffff !important; /* bg-white */
    }
    
    select option:checked {
        color: #111827 !important;
        background-color: #f3f4f6 !important;
    }
    
    select option:hover {
        background-color: #e5e7eb !important;
        color: #111827 !important;
    }
    
    select:focus {
        color: #111827 !important;
        background-color: #ffffff !important;
    }
    
    /* Ensure date inputs are visible */
    input[type="date"] {
        color: #111827 !important; /* text-gray-900 */
        background-color: #ffffff !important; /* bg-white */
    }
    
    input[type="date"]:focus {
        color: #111827 !important;
        background-color: #ffffff !important;
    }
    
    /* Ensure all text inputs are visible */
    input[type="text"],
    input[type="number"],
    textarea {
        color: #111827 !important;
        background-color: #ffffff !important;
    }
    
    input[type="text"]:focus,
    input[type="number"]:focus,
    textarea:focus {
        color: #111827 !important;
        background-color: #ffffff !important;
    }
    
    /* Placeholder text */
    input::placeholder,
    textarea::placeholder {
        color: #9ca3af !important; /* text-gray-400 */
        opacity: 1;
    }
    
    /* Flatpickr Calendar Styling */
    .flatpickr-calendar {
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 0.5rem;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        font-family: inherit;
    }
    
    .flatpickr-months {
        background: #ffffff;
        border-radius: 0.5rem 0.5rem 0 0;
        padding: 0.5rem;
    }
    
    .flatpickr-month {
        color: #111827;
    }
    
    .flatpickr-current-month {
        color: #111827;
        font-weight: 600;
    }
    
    .flatpickr-weekdays {
        background: #f9fafb;
        border-bottom: 1px solid #e5e7eb;
    }
    
    .flatpickr-weekday {
        color: #6b7280;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.75rem;
    }
    
    .flatpickr-day {
        color: #111827;
        border-radius: 0.375rem;
    }
    
    .flatpickr-day:hover {
        background: #f3f4f6;
        border-color: #d1d5db;
    }
    
    .flatpickr-day.selected,
    .flatpickr-day.startRange,
    .flatpickr-day.endRange {
        background: #2563eb;
        border-color: #2563eb;
        color: #ffffff;
    }
    
    .flatpickr-day.selected:hover,
    .flatpickr-day.startRange:hover,
    .flatpickr-day.endRange:hover {
        background: #1d4ed8;
        border-color: #1d4ed8;
    }
    
    .flatpickr-day.flatpickr-disabled,
    .flatpickr-day.prevMonthDay,
    .flatpickr-day.nextMonthDay {
        color: #d1d5db;
    }
    
    .flatpickr-day.today {
        border-color: #2563eb;
        font-weight: 600;
    }
    
    .flatpickr-prev-month,
    .flatpickr-next-month {
        color: #6b7280;
    }
    
    .flatpickr-prev-month:hover,
    .flatpickr-next-month:hover {
        color: #2563eb;
    }
</style>

<!-- Flatpickr JS -->
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Available days data from PHP (will be updated via AJAX for HR/Admin)
    const dataElement = document.getElementById('leave-request-data');
    let availableDays = dataElement ? JSON.parse(dataElement.getAttribute('data-available-days') || '{}') : {};
    
    // Balance check flag from PHP
    const shouldCheckBalance = dataElement ? JSON.parse(dataElement.getAttribute('data-should-check-balance') || 'false') : false;
    // Initialize Flatpickr for date inputs
    const startDatePicker = flatpickr("#start_date", {
        dateFormat: "Y-m-d",
        minDate: "today",
        onChange: function(selectedDates, dateStr, instance) {
            // Update end date minimum when start date changes
            if (selectedDates.length > 0) {
                endDatePicker.set('minDate', dateStr);
                calculateDays();
            }
        },
        onReady: function(selectedDates, dateStr, instance) {
            // Ensure text is visible
            instance.calendarContainer.style.color = '#111827';
        }
    });

    const endDatePicker = flatpickr("#end_date", {
        dateFormat: "Y-m-d",
        minDate: "today",
        onChange: function(selectedDates, dateStr, instance) {
            calculateDays();
        },
        onReady: function(selectedDates, dateStr, instance) {
            // Ensure text is visible
            instance.calendarContainer.style.color = '#111827';
        }
    });

    // Get form elements
    const startDateInput = document.getElementById('start_date');
    const endDateInput = document.getElementById('end_date');
    const daysRequestedInput = document.getElementById('days_requested');
    const leaveTypeSelect = document.getElementById('leave_type');
    const balanceWarning = document.getElementById('balanceWarning');
    const balanceInfo = document.getElementById('balanceInfo');
    const form = document.getElementById('leaveRequestForm');
    const leaveTypeLabels = {
        'vacation': 'Vacation',
        'sick': 'Sick',
        'personal': 'Personal',
        'emergency': 'Emergency',
        'maternity': 'Maternity',
        'paternity': 'Paternity',
        'bereavement': 'Bereavement',
        'study': 'Study'
    };

    function calculateDays() {
        const startDate = startDatePicker.selectedDates[0];
        const endDate = endDatePicker.selectedDates[0];

        if (startDate && endDate) {
            const start = new Date(startDate);
            const end = new Date(endDate);
            
            if (end >= start) {
                const diffTime = Math.abs(end - start);
                const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
                daysRequestedInput.value = diffDays;
                
                // Check balance if available days data exists
                if (Object.keys(availableDays).length > 0) {
                    checkBalance(diffDays);
                }
            } else {
                daysRequestedInput.value = '';
                if (balanceWarning) balanceWarning.classList.add('hidden');
            }
        } else {
            daysRequestedInput.value = '';
            if (balanceWarning) balanceWarning.classList.add('hidden');
        }
    }

    function checkBalance(days) {
        const leaveType = leaveTypeSelect.value;
        if (!leaveType || !availableDays[leaveType]) {
            if (balanceWarning) balanceWarning.classList.add('hidden');
            return;
        }

        const available = availableDays[leaveType];
        if (days > available) {
            if (balanceWarning) {
                balanceWarning.classList.remove('hidden');
                balanceInfo.textContent = `Available ${leaveTypeLabels[leaveType]} Leave: ${available} days. Requested: ${days} days.`;
            }
        } else {
            if (balanceWarning) balanceWarning.classList.add('hidden');
        }
    }

    // Recalculate when leave type changes
    leaveTypeSelect.addEventListener('change', function() {
        if (startDatePicker.selectedDates[0] && endDatePicker.selectedDates[0]) {
            calculateDays();
        }
    });

    // Form validation
    form.addEventListener('submit', function(e) {
        const days = parseInt(daysRequestedInput.value);
        const leaveType = leaveTypeSelect.value;
        
        if (shouldCheckBalance && leaveType && availableDays[leaveType] && days > availableDays[leaveType]) {
            e.preventDefault();
            alert('You don\'t have enough leave balance for this request. Please adjust your dates or select a different leave type.');
            return false;
        }

        const startDate = startDatePicker.selectedDates[0];
        const endDate = endDatePicker.selectedDates[0];
        
        if (!startDate || !endDate || !leaveTypeSelect.value) {
            e.preventDefault();
            alert('Please fill in all required fields.');
            return false;
        }
        
        // Ensure dates are in correct format for form submission
        if (startDate) {
            startDateInput.value = startDatePicker.formatDate(startDate, 'Y-m-d');
        }
        if (endDate) {
            endDateInput.value = endDatePicker.formatDate(endDate, 'Y-m-d');
        }
    });
});
</script>

@if(in_array($user->role, ['admin', 'hr']))
<script>
document.addEventListener('DOMContentLoaded', function() {
    // For HR/Admin: Load balance when employee changes (AJAX - no page reload)
    const employeeSelect = document.getElementById('employee_id');
    const leaveBalanceContainer = document.getElementById('leaveBalanceContainer');
    const balanceGrid = document.getElementById('balanceGrid');
    
    if (employeeSelect) {
        employeeSelect.addEventListener('change', function() {
            const employeeId = this.value;
            if (employeeId) {
                // Show loading state
                balanceGrid.innerHTML = '<div class="col-span-4 text-center text-sm text-blue-700">Loading leave balance...</div>';
                leaveBalanceContainer.classList.remove('hidden');
                
                // Fetch leave balance via AJAX
                fetch('{{ route("attendance.leave-management.balance") }}?employee_id=' + employeeId + '&year=' + new Date().getFullYear())
                    .then(response => response.json())
                    .then(data => {
                        if (data.leave_balance && data.available_days) {
                            // Update available days (update the global variable)
                            if (typeof availableDays !== 'undefined') {
                                availableDays = data.available_days;
                            }
                            
                            // Update balance display
                            balanceGrid.innerHTML = `
                                <div>
                                    <div class="text-xs text-blue-700">Vacation</div>
                                    <div class="text-sm font-semibold text-blue-900" id="balance-vacation">${data.available_days.vacation || 0} days</div>
                                </div>
                                <div>
                                    <div class="text-xs text-blue-700">Sick</div>
                                    <div class="text-sm font-semibold text-blue-900" id="balance-sick">${data.available_days.sick || 0} days</div>
                                </div>
                                <div>
                                    <div class="text-xs text-blue-700">Personal</div>
                                    <div class="text-sm font-semibold text-blue-900" id="balance-personal">${data.available_days.personal || 0} days</div>
                                </div>
                                <div>
                                    <div class="text-xs text-blue-700">Emergency</div>
                                    <div class="text-sm font-semibold text-blue-900" id="balance-emergency">${data.available_days.emergency || 0} days</div>
                                </div>
                            `;
                            
                            // Recalculate days if dates are already selected
                            const startDatePicker = flatpickr.getInstance(document.getElementById('start_date'));
                            const endDatePicker = flatpickr.getInstance(document.getElementById('end_date'));
                            if (startDatePicker && endDatePicker && startDatePicker.selectedDates[0] && endDatePicker.selectedDates[0]) {
                                // Trigger recalculation if calculateDays function exists
                                if (typeof calculateDays === 'function') {
                                    calculateDays();
                                }
                            }
                        } else {
                            balanceGrid.innerHTML = '<div class="col-span-4 text-center text-sm text-red-600">No leave balance found for this employee.</div>';
                        }
                    })
                    .catch(error => {
                        console.error('Error loading leave balance:', error);
                        balanceGrid.innerHTML = '<div class="col-span-4 text-center text-sm text-red-600">Error loading leave balance.</div>';
                    });
            } else {
                // Hide balance container if no employee selected
                leaveBalanceContainer.classList.add('hidden');
                if (typeof availableDays !== 'undefined') {
                    availableDays = {};
                }
            }
        });
    }
});
</script>
@endif
@endsection


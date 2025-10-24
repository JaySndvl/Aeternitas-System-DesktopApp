@extends('layouts.dashboard-base', ['user' => $user, 'activeRoute' => 'attendance.timekeeping'])

@section('title', 'Edit Schedule')

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white shadow-sm border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="py-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Edit Employee Schedule</h1>
                        <p class="mt-1 text-sm text-gray-600">Update work schedule for {{ $schedule->employee->full_name }}</p>
                    </div>
                    <div class="flex space-x-3">
                        <a href="{{ route('schedule-v2.show', $schedule) }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                            <i class="fas fa-eye mr-2"></i>
                            View Schedule
                        </a>
                        <a href="{{ isset($currentFilters) ? route('schedule-v2.index', array_filter($currentFilters)) : route('schedule-v2.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                            <i class="fas fa-arrow-left mr-2"></i>
                            Back to Schedules
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Form -->
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <form id="scheduleForm" class="p-6 space-y-6">
                @csrf
                @method('PUT')
                
                <!-- Hidden inputs to preserve filter state -->
                @if(isset($currentFilters))
                    @foreach($currentFilters as $key => $value)
                        @if($value)
                            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                        @endif
                    @endforeach
                @endif
                
                <!-- Employee Info (Read-only) -->
                <div class="bg-gray-50 rounded-lg p-4">
                    <div class="flex items-center space-x-4">
                        <div class="flex-shrink-0 h-12 w-12">
                            <div class="h-12 w-12 rounded-full bg-blue-100 flex items-center justify-center">
                                <span class="text-lg font-medium text-blue-600">
                                    {{ substr($schedule->employee->first_name, 0, 1) }}{{ substr($schedule->employee->last_name, 0, 1) }}
                                </span>
                            </div>
                        </div>
                        <div>
                            <h3 class="text-lg font-medium text-gray-900">{{ $schedule->employee->full_name }}</h3>
                            <p class="text-sm text-gray-600">{{ $schedule->employee->position }} - {{ $schedule->employee->department->name }}</p>
                            <p class="text-sm text-gray-500">{{ $schedule->date->format('l, F j, Y') }}</p>
                        </div>
                    </div>
                </div>

                <!-- Status -->
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <select name="status" id="status" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('status') border-red-500 @enderror">
                        <option value="">Select status</option>
                        <option value="Working" {{ old('status', $schedule->status) == 'Working' ? 'selected' : '' }}>Working</option>
                        <option value="Day Off" {{ old('status', $schedule->status) == 'Day Off' ? 'selected' : '' }}>Day Off</option>
                        <option value="Leave" {{ old('status', $schedule->status) == 'Leave' ? 'selected' : '' }}>Leave</option>
                        <option value="Absent" {{ old('status', $schedule->status) == 'Absent' ? 'selected' : '' }}>Absent</option>
                        <option value="Regular Holiday" {{ old('status', $schedule->status) == 'Regular Holiday' ? 'selected' : '' }}>Regular Holiday</option>
                        <option value="Special Holiday" {{ old('status', $schedule->status) == 'Special Holiday' ? 'selected' : '' }}>Special Holiday</option>
                        <option value="Overtime" {{ old('status', $schedule->status) == 'Overtime' ? 'selected' : '' }}>Overtime</option>
                    </select>
                    @error('status')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Time In/Out (only show for working status) -->
                <div id="timeFields" class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div>
                        <label for="time_in" class="block text-sm font-medium text-gray-700 mb-2">Time In</label>
                        <input type="time" name="time_in" id="time_in" value="{{ old('time_in', $schedule->time_in ? \Carbon\Carbon::createFromFormat('H:i:s', $schedule->time_in)->format('H:i') : '') }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('time_in') border-red-500 @enderror">
                        @error('time_in')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="time_out" class="block text-sm font-medium text-gray-700 mb-2">Time Out</label>
                        <input type="time" name="time_out" id="time_out" value="{{ old('time_out', $schedule->time_out ? \Carbon\Carbon::createFromFormat('H:i:s', $schedule->time_out)->format('H:i') : '') }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('time_out') border-red-500 @enderror">
                        @error('time_out')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Notes -->
                <div>
                    <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
                    <textarea name="notes" id="notes" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('notes') border-red-500 @enderror" placeholder="Optional notes about this schedule...">{{ old('notes', $schedule->notes) }}</textarea>
                    @error('notes')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Schedule Info -->
                <div class="bg-blue-50 rounded-lg p-4">
                    <h4 class="text-sm font-medium text-blue-900 mb-2">Schedule Information</h4>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                        <div>
                            <span class="font-medium text-blue-800">Created:</span>
                            <span class="text-blue-700">{{ $schedule->created_at->format('M j, Y g:i A') }}</span>
                        </div>
                        @if($schedule->creator)
                            <div>
                                <span class="font-medium text-blue-800">Created by:</span>
                                <span class="text-blue-700">{{ $schedule->creator->full_name }}</span>
                            </div>
                        @endif
                        @if($schedule->time_in && $schedule->time_out)
                            <div>
                                <span class="font-medium text-blue-800">Working Hours:</span>
                                <span class="text-blue-700">{{ $schedule->working_hours }} hours</span>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="flex justify-between items-center pt-6 border-t border-gray-200">
                    <!-- Delete Button (left side) -->
                    <button type="button" id="deleteBtn" class="px-4 py-2 bg-red-600 border border-transparent rounded-lg font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors">
                        <i class="fas fa-trash mr-2"></i>
                        Delete Schedule
                    </button>
                    
                    <!-- Update and Cancel Buttons (right side) -->
                    <div class="flex space-x-3">
                        <a href="{{ isset($currentFilters) ? route('schedule-v2.index', array_filter($currentFilters)) : route('schedule-v2.index') }}" class="px-4 py-2 border border-gray-300 rounded-lg font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                            Cancel
                        </a>
                        <button type="submit" id="updateBtn" class="px-4 py-2 bg-blue-600 border border-transparent rounded-lg font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                            <i class="fas fa-save mr-2"></i>
                            Update Schedule
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Handle form actions dynamically and initialize
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('scheduleForm');
    const updateBtn = document.getElementById('updateBtn');
    const deleteBtn = document.getElementById('deleteBtn');
    const statusSelect = document.getElementById('status');
    
    // Initialize time fields visibility
    if (statusSelect.value === 'Working' || statusSelect.value === 'Overtime' || statusSelect.value === 'Regular Holiday' || statusSelect.value === 'Special Holiday') {
        document.getElementById('timeFields').style.display = 'grid';
    } else {
        document.getElementById('timeFields').style.display = 'none';
    }
    
    // Update button - set form to update action
    updateBtn.addEventListener('click', function(e) {
        e.preventDefault();
        form.action = '{{ route("schedule-v2.update", $schedule) }}';
        form.method = 'POST';
        
        // Add method override for PUT
        const methodInput = document.createElement('input');
        methodInput.type = 'hidden';
        methodInput.name = '_method';
        methodInput.value = 'PUT';
        form.appendChild(methodInput);
        
        form.submit();
    });
    
    // Delete button - set form to delete action
    deleteBtn.addEventListener('click', function(e) {
        e.preventDefault();
        
        if (confirm('Are you sure you want to delete this schedule?')) {
            form.action = '{{ route("schedule-v2.destroy", $schedule) }}';
            form.method = 'POST';
            
            // Add method override for DELETE
            const methodInput = document.createElement('input');
            methodInput.type = 'hidden';
            methodInput.name = '_method';
            methodInput.value = 'DELETE';
            form.appendChild(methodInput);
            
            form.submit();
        }
    });
});

// Show/hide time fields based on status
document.getElementById('status').addEventListener('change', function() {
    const timeFields = document.getElementById('timeFields');
    const timeInField = document.getElementById('time_in');
    const timeOutField = document.getElementById('time_out');
    
    if (this.value === 'Working' || this.value === 'Overtime' || this.value === 'Regular Holiday' || this.value === 'Special Holiday') {
        timeFields.style.display = 'grid';
        // Don't make fields required - let backend validation handle it
        timeInField.required = false;
        timeOutField.required = false;
    } else {
        timeFields.style.display = 'none';
        timeInField.required = false;
        timeOutField.required = false;
        // Clear the time values for non-working statuses to prevent conflicts
        timeInField.value = '';
        timeOutField.value = '';
    }
});

</script>
@endsection

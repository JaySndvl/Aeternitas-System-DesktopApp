@extends('layouts.dashboard-base', ['user' => $user, 'activeRoute' => 'attendance.timekeeping'])

@section('title', 'Edit Attendance Record')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Edit Attendance Record</h1>
            <p class="mt-1 text-sm text-gray-600">Update attendance record for {{ $attendanceRecord->employee->full_name }}</p>
        </div>
        <div class="mt-4 sm:mt-0">
            <a href="{{ route('attendance.timekeeping') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                <i class="fas fa-arrow-left mr-2"></i>
                Back to Timekeeping
            </a>
        </div>
    </div>

    <!-- Form -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 sm:p-6">
        <form action="{{ route('attendance.update-record', $attendanceRecord->id) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <!-- Employee Selection -->
                @if($user->role !== 'employee')
                <div class="sm:col-span-2">
                    <label for="employee_id" class="block text-sm font-medium text-gray-700 mb-2">
                        Employee <span class="text-red-500">*</span>
                    </label>
                    <select name="employee_id" id="employee_id" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors @error('employee_id') border-red-500 @enderror">
                        <option value="">Select an employee</option>
                        @foreach($employees as $employee)
                            <option value="{{ $employee->id }}" {{ (old('employee_id', $attendanceRecord->employee_id) == $employee->id) ? 'selected' : '' }}>
                                {{ $employee->full_name }} - {{ $employee->department->name ?? 'No Department' }}
                            </option>
                        @endforeach
                    </select>
                    @error('employee_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                @else
                <!-- For employees, hide the dropdown and use their own ID -->
                <input type="hidden" name="employee_id" value="{{ $attendanceRecord->employee_id }}">
                @endif

                <!-- Date -->
                <div>
                    <label for="date" class="block text-sm font-medium text-gray-700 mb-2">
                        Date <span class="text-red-500">*</span>
                    </label>
                    <input type="date" name="date" id="date" value="{{ old('date', $attendanceRecord->date->format('Y-m-d')) }}" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors bg-white text-gray-900 @error('date') border-red-500 @enderror" style="background-color: white !important; color: #111827 !important;">
                    @error('date')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Status -->
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-2">
                        Status <span class="text-red-500">*</span>
                    </label>
                    <select name="status" id="status" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors @error('status') border-red-500 @enderror">
                        <option value="">Select status</option>
                        <option value="present" {{ old('status', $attendanceRecord->status) == 'present' ? 'selected' : '' }}>Present</option>
                        <option value="absent" {{ old('status', $attendanceRecord->status) == 'absent' ? 'selected' : '' }}>Absent</option>
                        <option value="late" {{ old('status', $attendanceRecord->status) == 'late' ? 'selected' : '' }}>Late</option>
                        <option value="half_day" {{ old('status', $attendanceRecord->status) == 'half_day' ? 'selected' : '' }}>Half Day</option>
                    </select>
                    @error('status')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Time In -->
                <div>
                    <label for="time_in" class="block text-sm font-medium text-gray-700 mb-2">
                        Time In <span class="text-red-500">*</span>
                    </label>
                    <input type="time" name="time_in" id="time_in" value="{{ old('time_in', $attendanceRecord->time_in ? $attendanceRecord->time_in->format('H:i') : '') }}" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors @error('time_in') border-red-500 @enderror">
                    @error('time_in')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Time Out -->
                <div>
                    <label for="time_out" class="block text-sm font-medium text-gray-700 mb-2">
                        Time Out
                    </label>
                    <input type="time" name="time_out" id="time_out" value="{{ old('time_out', $attendanceRecord->time_out ? $attendanceRecord->time_out->format('H:i') : '') }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors @error('time_out') border-red-500 @enderror">
                    @error('time_out')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Break Start -->
                <div>
                    <label for="break_start" class="block text-sm font-medium text-gray-700 mb-2">
                        Break Start
                    </label>
                    <input type="time" name="break_start" id="break_start" value="{{ old('break_start', $attendanceRecord->break_start ? $attendanceRecord->break_start->format('H:i') : '12:00') }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors @error('break_start') border-red-500 @enderror">
                    @error('break_start')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Break End -->
                <div>
                    <label for="break_end" class="block text-sm font-medium text-gray-700 mb-2">
                        Break End
                    </label>
                    <input type="time" name="break_end" id="break_end" value="{{ old('break_end', $attendanceRecord->break_end ? $attendanceRecord->break_end->format('H:i') : '13:00') }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors @error('break_end') border-red-500 @enderror">
                    @error('break_end')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Notes -->
                <div class="sm:col-span-2">
                    <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">
                        Notes
                    </label>
                    <textarea name="notes" id="notes" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors @error('notes') border-red-500 @enderror" placeholder="Optional notes about this attendance record...">{{ old('notes', $attendanceRecord->notes) }}</textarea>
                    @error('notes')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Current Record Info -->
            <div class="bg-gray-50 rounded-lg p-4">
                <h3 class="text-sm font-medium text-gray-900 mb-2">Current Record Information</h3>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-sm">
                    <div>
                        <span class="text-gray-500">Total Hours:</span>
                        <span class="font-medium">{{ $attendanceRecord->total_hours ? \App\Helpers\TimezoneHelper::formatHours($attendanceRecord->total_hours) : 'N/A' }}</span>
                    </div>
                    <div>
                        <span class="text-gray-500">Regular Hours:</span>
                        <span class="font-medium">{{ $attendanceRecord->regular_hours ? \App\Helpers\TimezoneHelper::formatHours($attendanceRecord->regular_hours) : 'N/A' }}</span>
                    </div>
                    <div>
                        <span class="text-gray-500">Overtime Hours:</span>
                        <span class="font-medium">{{ $attendanceRecord->overtime_hours ? \App\Helpers\TimezoneHelper::formatHours($attendanceRecord->overtime_hours) : 'N/A' }}</span>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="flex flex-col sm:flex-row sm:justify-end space-y-3 sm:space-y-0 sm:space-x-3 pt-6 border-t border-gray-200">
                <a href="{{ route('attendance.timekeeping') }}" class="inline-flex items-center justify-center px-4 py-2 border border-gray-300 rounded-lg font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                    Cancel
                </a>
                <button type="submit" class="inline-flex items-center justify-center px-4 py-2 border border-transparent rounded-lg font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                    <i class="fas fa-save mr-2"></i>
                    Update Record
                </button>
            </div>
        </form>
    </div>

    <!-- Help Section -->
    <div class="bg-blue-50 rounded-lg p-4 sm:p-6">
        <div class="flex">
            <div class="flex-shrink-0">
                <i class="fas fa-info-circle text-blue-400"></i>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-blue-800">Tips for Editing Attendance Records</h3>
                <div class="mt-2 text-sm text-blue-700">
                    <ul class="list-disc list-inside space-y-1">
                        <li>Make sure to select the correct employee and date</li>
                        <li>Time In is required, but Time Out is optional</li>
                        <li>Break duration is automatically calculated if you provide both Time In and Time Out</li>
                        <li>Total hours will be recalculated as: (Time Out - Time In) - Break Duration</li>
                        <li>You cannot have duplicate records for the same employee on the same date</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Auto-calculate total hours when time fields change
document.addEventListener('DOMContentLoaded', function() {
    const timeInInput = document.getElementById('time_in');
    const timeOutInput = document.getElementById('time_out');
    const dateInput = document.getElementById('date');

    function calculateTotalHours() {
        const timeIn = timeInInput.value;
        const timeOut = timeOutInput.value;
        const breakStart = document.getElementById('break_start').value;
        const breakEnd = document.getElementById('break_end').value;
        const date = dateInput.value;

        if (timeIn && timeOut && date) {
            const timeInDate = new Date(date + 'T' + timeIn);
            const timeOutDate = new Date(date + 'T' + timeOut);
            
            if (timeOutDate > timeInDate) {
                const diffInMs = timeOutDate - timeInDate;
                const diffInMinutes = diffInMs / (1000 * 60);
                const diffInHours = diffInMinutes / 60;
                
                let breakDuration = 0;
                if (breakStart && breakEnd) {
                    const breakStartDate = new Date(date + 'T' + breakStart);
                    const breakEndDate = new Date(date + 'T' + breakEnd);
                    if (breakEndDate > breakStartDate) {
                        const breakMs = breakEndDate - breakStartDate;
                        const breakMinutes = breakMs / (1000 * 60);
                        breakDuration = breakMinutes / 60;
                    }
                }
                
                const totalHours = Math.max(0, diffInHours - breakDuration);
                
                // Business Rules Calculation
                const standardEnd = new Date(date + 'T17:00');
                const overtimeStart = new Date(date + 'T17:30');
                
                let regularHours = 0;
                let overtimeHours = 0;
                
                if (timeOutDate <= standardEnd) {
                    // Worked within standard hours (8 AM - 5 PM)
                    regularHours = totalHours;
                    overtimeHours = 0;
                } else if (timeOutDate <= overtimeStart) {
                    // Worked until 5:30 PM (no overtime yet)
                    regularHours = totalHours;
                    overtimeHours = 0;
                } else {
                    // Worked beyond 5:30 PM (overtime applies)
                    const regularMs = overtimeStart - timeInDate;
                    const regularMinutes = regularMs / (1000 * 60);
                    regularHours = Math.max(0, (regularMinutes / 60) - breakDuration);
                    regularHours = Math.min(regularHours, 8); // Cap at 8 hours
                    
                    const overtimeMs = timeOutDate - overtimeStart;
                    const overtimeMinutes = overtimeMs / (1000 * 60);
                    overtimeHours = overtimeMinutes / 60;
                }
                
                // Show calculated hours
                console.log('Total Hours:', totalHours.toFixed(2));
                console.log('Regular Hours:', regularHours.toFixed(2));
                console.log('Overtime Hours:', overtimeHours.toFixed(2));
            }
        }
    }

    timeInInput.addEventListener('change', calculateTotalHours);
    timeOutInput.addEventListener('change', calculateTotalHours);
    document.getElementById('break_start').addEventListener('change', calculateTotalHours);
    document.getElementById('break_end').addEventListener('change', calculateTotalHours);
    dateInput.addEventListener('change', calculateTotalHours);
});
</script>
@endsection

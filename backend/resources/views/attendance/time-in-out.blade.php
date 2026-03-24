@extends('layouts.dashboard-base', ['user' => $user, 'activeRoute' => 'attendance.time-in-out'])

@section('title', 'Time In/Out')

@section('content')
<!-- Immediate clock start script -->
<script>
// Start clock immediately when this script loads
(function() {
    function getPhilippineTime() {
        // Get current time and convert to Philippine timezone
        const now = new Date();
        
        // Use a simpler, more reliable method: format directly without reconstructing
        // This avoids date parsing issues that cause flickering
        try {
            // Get the time string in Philippine timezone
            const philippineTimeString = now.toLocaleString('en-US', {
                timeZone: 'Asia/Manila',
                year: 'numeric',
                month: '2-digit',
                day: '2-digit',
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
                hour12: false
            });
            
            // Parse the formatted string back to a date
            // Format is typically "MM/DD/YYYY, HH:MM:SS"
            const [datePart, timePart] = philippineTimeString.split(', ');
            const [month, day, year] = datePart.split('/').map(Number);
            const [hour, minute, second] = timePart.split(':').map(Number);
            
            // Create date in local timezone with Philippine time values
            // This ensures the date components match what we want to display
            const philippineDate = new Date(year, month - 1, day, hour, minute, second);
            
            // Validate the date
            if (isNaN(philippineDate.getTime())) {
                throw new Error('Invalid date created');
            }
            
            return philippineDate;
        } catch (e) {
            // Fallback: use UTC offset calculation
            console.warn('Error in getPhilippineTime, using fallback:', e);
            const utcTime = now.getTime();
            const utcOffset = now.getTimezoneOffset() * 60000; // Local timezone offset in ms
            const philippineOffset = 8 * 60 * 60 * 1000; // UTC+8 in milliseconds
            return new Date(utcTime + utcOffset + philippineOffset);
        }
    }
    
    // Format time in 12-hour format with AM/PM
    function format12HourTime(date) {
        let hours = date.getHours();
        const minutes = date.getMinutes().toString().padStart(2, '0');
        const seconds = date.getSeconds().toString().padStart(2, '0');
        const ampm = hours >= 12 ? 'PM' : 'AM';
        hours = hours % 12;
        hours = hours ? hours : 12; // the hour '0' should be '12'
        const hoursStr = hours.toString().padStart(2, '0');
        return `${hoursStr}:${minutes}:${seconds} ${ampm}`;
    }
    
    function startClockNow() {
        try {
            const now = new Date();
        
            // Format date directly from current time using Philippine timezone
            // This avoids any date reconstruction issues
        const dateOptions = { 
                timeZone: 'Asia/Manila',
            weekday: 'long',
            year: 'numeric', 
            month: 'long', 
            day: 'numeric'
        };
            const dateString = now.toLocaleDateString('en-US', dateOptions);
            
            // Format time directly from current time using Philippine timezone
            const timeOptions = {
                timeZone: 'Asia/Manila',
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
                hour12: true
            };
            const timeString = now.toLocaleTimeString('en-US', timeOptions);
            
            // Validate date string is not empty or invalid
            if (!dateString || dateString === 'Invalid Date' || dateString.includes('Invalid')) {
                console.warn('Invalid date string generated:', dateString);
                return; // Skip update if date string is invalid
            }
        
        const timeElement = document.getElementById('current-times');
        const dateElement = document.getElementById('current-date');
        const lastUpdatedElement = document.getElementById('last-updated');
        
        if (timeElement) {
            timeElement.textContent = timeString;
        }
            if (dateElement && dateString) {
            dateElement.textContent = dateString;
        }
        if (lastUpdatedElement) {
            lastUpdatedElement.textContent = `Last updated: ${timeString}`;
            }
        } catch (e) {
            console.error('Error updating clock:', e);
            // Don't update if there's an error to prevent glitching
        }
    }
    
    // Start immediately
    startClockNow();
    
    // Set up interval to update every second
    setInterval(startClockNow, 1000);
})();
</script>
<div class="max-w-4xl mx-auto">
    <div class="space-y-6">
        <!-- Header -->
        <div class="text-center">
            <h1 class="text-3xl font-bold text-gray-900">Time In/Out</h1>
            <p class="mt-2 text-lg text-gray-600">Record your daily attendance</p>
            @if($user->employee)
                <div class="mt-4 flex flex-col sm:flex-row items-center justify-center gap-4">
                    <div class="inline-flex items-center bg-blue-50 px-4 py-2 rounded-full">
                        <i class="fas fa-user text-blue-600 mr-2"></i>
                        <span class="text-blue-800 font-medium">{{ $user->employee->first_name }} {{ $user->employee->last_name }}</span>
                        @if($user->employee->department)
                            <span class="text-blue-600 ml-2">• {{ $user->employee->department->name }}</span>
                        @endif
                    </div>
                    <div class="inline-flex items-center bg-green-50 px-4 py-2 rounded-full">
                        <i class="fas fa-id-badge text-green-600 mr-2"></i>
                        <span class="text-green-800 font-medium">Employee ID: {{ $user->employee->employee_id }}</span>
                    </div>
                    @if($todayAttendance)
                        <div class="inline-flex items-center bg-blue-50 px-4 py-2 rounded-full">
                            <i class="fas fa-clock text-blue-600 mr-2"></i>
                            <span class="text-blue-800 font-medium">Status: {{ ucfirst($todayAttendance->status) }}</span>
                        </div>
                    @endif
                </div>
            @endif
        </div>

        <!-- Current Time Display -->
        <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-2xl p-8 text-center text-white shadow-lg">
            <div class="text-6xl font-bold mb-2" id="current-times">--:--:--</div>
            <div class="text-xl opacity-90" id="current-date">Loading...</div>
            <div class="text-sm opacity-75 mt-2">Philippine Standard Time</div>
            <div class="text-xs opacity-50 mt-1" id="last-updated">Last updated: --:--:--</div>
            <div class="mt-2">
                <div class="inline-flex items-center bg-white bg-opacity-20 px-3 py-1 rounded-full">
                    <div class="w-2 h-2 bg-green-400 rounded-full mr-2 animate-pulse" id="live-indicator"></div>
                    <span class="text-xs font-medium">LIVE</span>
                </div>
            </div>
            <!-- Working Time Indicator (controlled by JavaScript) -->
            <div class="mt-4 pt-4 border-t border-blue-400" id="time-tracking-section" style="display: none;">
                <!-- Break Time Display (shown when on break) -->
                <div id="break-time-section" style="display: none;">
                    <div class="text-lg opacity-90">On Break for:</div>
                    <div class="text-2xl font-bold text-yellow-300" id="break-time">0h 0m</div>
                </div>
                <!-- Working Time Display (shown when working, not on break) -->
                <div id="working-time-section">
                    <div class="text-lg opacity-90">Working for:</div>
                    <div class="text-2xl font-bold" id="working-time">0h 0m</div>
                </div>
            </div>
        </div>

        <!-- Attendance Status Card -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="text-center">
                <div class="inline-flex items-center justify-center w-20 h-20 bg-green-100 rounded-full mb-4" id="status-icon">
                    <i class="fas fa-check-circle text-3xl text-green-600"></i>
                </div>
                <h2 class="text-2xl font-bold text-gray-900 mb-2" id="status-title">Ready to Clock In</h2>
                <p class="text-gray-600" id="status-message">You haven't clocked in today yet</p>
            </div>
        </div>

        <!-- Time In/Out Actions -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Time In Card -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
                <div class="text-center">
                    <div class="inline-flex items-center justify-center w-16 h-16 bg-green-100 rounded-full mb-4">
                        <i class="fas fa-sign-in-alt text-2xl text-green-600"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">Time In</h3>
                    <p class="text-gray-600 mb-4">Start your workday</p>
                    <button id="time-in-btn" class="w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-3 px-6 rounded-lg transition-colors" onclick="timeIn()">
                        <i class="fas fa-play mr-2"></i>
                        Clock In Now
                    </button>
                </div>
            </div>

            <!-- Time Out Card -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow" id="time-out-card">
                <div class="text-center">
                    <div class="inline-flex items-center justify-center w-16 h-16 bg-red-100 rounded-full mb-4">
                        <i class="fas fa-sign-out-alt text-2xl text-red-600"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">Time Out</h3>
                    <p class="text-gray-600 mb-4">End your workday</p>
                    <button id="time-out-btn" class="w-full bg-gray-400 text-white font-semibold py-3 px-6 rounded-lg cursor-not-allowed" disabled onclick="timeOut()">
                        <i class="fas fa-stop mr-2"></i>
                        Clock Out
                    </button>
                </div>
            </div>
        </div>

        <!-- Break Actions -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6" id="break-section" style="display: none;">
            <!-- Break Start Card -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
                <div class="text-center">
                    <div class="inline-flex items-center justify-center w-16 h-16 bg-yellow-100 rounded-full mb-4">
                        <i class="fas fa-coffee text-2xl text-yellow-600"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">Start Break</h3>
                    <p class="text-gray-600 mb-4">Take a break from work</p>
                    <button id="break-start-btn" class="w-full bg-yellow-600 hover:bg-yellow-700 text-white font-semibold py-3 px-6 rounded-lg transition-colors" onclick="breakStart()">
                        <i class="fas fa-pause mr-2"></i>
                        Start Break
                    </button>
                </div>
            </div>

            <!-- Break End Card -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow" id="break-end-card">
                <div class="text-center">
                    <div class="inline-flex items-center justify-center w-16 h-16 bg-orange-100 rounded-full mb-4">
                        <i class="fas fa-play text-2xl text-orange-600"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">End Break</h3>
                    <p class="text-gray-600 mb-4">Resume working</p>
                    <button id="break-end-btn" class="w-full bg-gray-400 text-white font-semibold py-3 px-6 rounded-lg cursor-not-allowed" disabled onclick="breakEnd()">
                        <i class="fas fa-play mr-2"></i>
                        End Break
                    </button>
                </div>
            </div>
        </div>

        <!-- Today's Summary -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Today's Summary - {{ now()->format('M j, Y') }}</h3>
            
            <!-- Over Break Warning -->
            <div id="over-break-warning" class="hidden mb-4 p-4 bg-red-50 border border-red-200 rounded-lg">
                <div class="flex items-center">
                    <i class="fas fa-exclamation-triangle text-red-600 mr-3"></i>
                    <div>
                        <div class="font-semibold text-red-900">Over Break Notice</div>
                        <div class="text-sm text-red-700">You have exceeded the 1.5 hour break limit by <span id="over-break-minutes" class="font-bold">0</span> minutes.</div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 mb-4">
                <div class="text-center p-4 bg-blue-50 rounded-lg">
                    <div class="text-2xl font-bold text-blue-600" id="summary-time-in">--:--</div>
                    <div class="text-sm text-gray-600">Time In</div>
                </div>
                <div class="text-center p-4 bg-yellow-50 rounded-lg">
                    <div class="text-2xl font-bold text-yellow-600" id="summary-break-start">--:--</div>
                    <div class="text-sm text-gray-600">Active Break Start</div>
                </div>
                <div class="text-center p-4 bg-orange-50 rounded-lg">
                    <div class="text-2xl font-bold text-orange-600" id="summary-break-end">--:--</div>
                    <div class="text-sm text-gray-600">Last Break End</div>
                </div>
                <div class="text-center p-4 bg-gray-50 rounded-lg">
                    <div class="text-2xl font-bold text-gray-600" id="summary-time-out">--:--</div>
                    <div class="text-sm text-gray-600">Time Out</div>
                </div>
                <div class="text-center p-4 bg-green-50 rounded-lg">
                    <div class="text-2xl font-bold text-green-600" id="summary-total-hours">0h 0m</div>
                    <div class="text-sm text-gray-600">Total Hours</div>
                </div>
            </div>

            <!-- Break Information -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
                <div class="text-center p-4 bg-purple-50 rounded-lg">
                    <div class="text-2xl font-bold text-purple-600" id="summary-break-count">0</div>
                    <div class="text-sm text-gray-600">Break Count</div>
                </div>
                <div class="text-center p-4 bg-indigo-50 rounded-lg">
                    <div class="text-2xl font-bold text-indigo-600" id="summary-total-break">0h 0m</div>
                    <div class="text-sm text-gray-600">Total Break Time</div>
                </div>
                <div class="text-center p-4 bg-pink-50 rounded-lg">
                    <div class="text-2xl font-bold text-pink-600" id="summary-break-limit">1.5h</div>
                    <div class="text-sm text-gray-600">Break Limit</div>
                </div>
            </div>

            <!-- Today's Time Entries -->
            <div class="border-t border-gray-200 pt-4">
                <h4 class="text-md font-semibold text-gray-900 mb-3">
                    <i class="fas fa-list-ul text-blue-500 mr-2"></i>
                    Today's Time Entries
                    <span class="text-sm font-normal text-gray-500" id="entry-count-badge">(0 entries)</span>
                </h4>
                <div class="space-y-2" id="time-entries-list">
                    <div class="text-center py-4 text-gray-500">
                        <i class="fas fa-clock text-2xl mb-2"></i>
                        <p class="text-sm">No time entries yet. Clock in to start.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Recent Activity</h3>
            <div class="space-y-3" id="recent-activity">
                @if($recentActivity && $recentActivity->count() > 0)
                    @foreach($recentActivity as $record)
                        @if($record->time_in)
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                <div class="flex items-center">
                                    <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center mr-3">
                                        <i class="fas fa-sign-in-alt text-green-600 text-sm"></i>
                                    </div>
                                    <div>
                                        <div class="font-medium text-gray-900">Time In</div>
                                        <div class="text-sm text-gray-500">{{ $record->date->format('M j, Y') }}</div>
                                    </div>
                                </div>
                                <div class="text-sm text-gray-500">{{ $record->time_in->format('g:i A') }}</div>
                            </div>
                        @endif
                        
                        @if($record->break_start)
                            <div class="flex items-center justify-between p-3 bg-yellow-50 rounded-lg">
                                <div class="flex items-center">
                                    <div class="w-8 h-8 bg-yellow-100 rounded-full flex items-center justify-center mr-3">
                                        <i class="fas fa-coffee text-yellow-600 text-sm"></i>
                                    </div>
                                    <div>
                                        <div class="font-medium text-gray-900">Break Start</div>
                                        <div class="text-sm text-gray-500">{{ $record->date->format('M j, Y') }}</div>
                                    </div>
                                </div>
                                <div class="text-sm text-gray-500">{{ $record->break_start->format('g:i A') }}</div>
                            </div>
                        @endif
                        
                        @if($record->break_end)
                            <div class="flex items-center justify-between p-3 bg-orange-50 rounded-lg">
                                <div class="flex items-center">
                                    <div class="w-8 h-8 bg-orange-100 rounded-full flex items-center justify-center mr-3">
                                        <i class="fas fa-play text-orange-600 text-sm"></i>
                                    </div>
                                    <div>
                                        <div class="font-medium text-gray-900">Break End</div>
                                        <div class="text-sm text-gray-500">{{ $record->date->format('M j, Y') }}</div>
                                    </div>
                                </div>
                                <div class="text-sm text-gray-500">{{ $record->break_end->format('g:i A') }}</div>
                            </div>
                        @endif
                        
                        @if($record->time_out)
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                <div class="flex items-center">
                                    <div class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center mr-3">
                                        <i class="fas fa-sign-out-alt text-red-600 text-sm"></i>
                                    </div>
                                    <div>
                                        <div class="font-medium text-gray-900">Time Out</div>
                                        <div class="text-sm text-gray-500">{{ $record->date->format('M j, Y') }}</div>
                                    </div>
                                </div>
                                <div class="text-sm text-gray-500">{{ $record->time_out->format('g:i A') }}</div>
                            </div>
                        @endif
                    @endforeach
                @else
                    <div class="text-center py-8 text-gray-500">
                        <i class="fas fa-history text-4xl mb-4"></i>
                        <p>No recent activity found</p>
                        <p class="text-sm mt-2">Your attendance records will appear here</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Hidden data for JavaScript -->
<div id="attendance-data" 
     data-today-attendance='{!! json_encode($todayAttendance) !!}' 
     data-recent-activity='{!! json_encode($recentActivity) !!}'
     style="display: none;"></div>

<script>
// Global variables
let currentStatus = null;
const dataElement = document.getElementById('attendance-data');
let attendanceRecord = null;

// Safely parse attendance data with validation
if (dataElement) {
    try {
        const rawData = dataElement.getAttribute('data-today-attendance') || 'null';
        if (rawData && rawData !== 'null') {
            const parsed = JSON.parse(rawData);
            // Validate dates are in correct format before assigning
            if (parsed && typeof parsed === 'object') {
                // Ensure date strings are valid ISO8601 format
                if (parsed.time_in && typeof parsed.time_in === 'string') {
                    const testDate = new Date(parsed.time_in);
                    if (isNaN(testDate.getTime())) {
                        console.warn('Invalid time_in date:', parsed.time_in);
                        parsed.time_in = null;
                    }
                }
                if (parsed.time_out && typeof parsed.time_out === 'string') {
                    const testDate = new Date(parsed.time_out);
                    if (isNaN(testDate.getTime())) {
                        console.warn('Invalid time_out date:', parsed.time_out);
                        parsed.time_out = null;
                    }
                }
                if (parsed.break_start && typeof parsed.break_start === 'string') {
                    const testDate = new Date(parsed.break_start);
                    if (isNaN(testDate.getTime())) {
                        console.warn('Invalid break_start date:', parsed.break_start);
                        parsed.break_start = null;
                    }
                }
                if (parsed.break_end && typeof parsed.break_end === 'string') {
                    const testDate = new Date(parsed.break_end);
                    if (isNaN(testDate.getTime())) {
                        console.warn('Invalid break_end date:', parsed.break_end);
                        parsed.break_end = null;
                    }
                }
                attendanceRecord = parsed;
            }
        }
    } catch (e) {
        console.error('Error parsing attendance data:', e);
        attendanceRecord = null;
    }
}
let recentActivity = dataElement ? JSON.parse(dataElement.getAttribute('data-recent-activity') || '[]') : [];

// Get Philippine Standard Time (UTC+8)
function getPhilippineTime() {
    const now = new Date();
    
    // Use a simpler, more reliable method: format directly without reconstructing
    // This avoids date parsing issues that cause flickering
    try {
        // Get the time string in Philippine timezone
        const philippineTimeString = now.toLocaleString('en-US', {
            timeZone: 'Asia/Manila',
            year: 'numeric',
            month: '2-digit',
            day: '2-digit',
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit',
            hour12: false
        });
        
        // Parse the formatted string back to a date
        // Format is typically "MM/DD/YYYY, HH:MM:SS"
        const [datePart, timePart] = philippineTimeString.split(', ');
        const [month, day, year] = datePart.split('/').map(Number);
        const [hour, minute, second] = timePart.split(':').map(Number);
        
        // Create date in local timezone with Philippine time values
        // This ensures the date components match what we want to display
        const philippineDate = new Date(year, month - 1, day, hour, minute, second);
        
        // Validate the date
        if (isNaN(philippineDate.getTime())) {
            throw new Error('Invalid date created');
        }
        
        return philippineDate;
    } catch (e) {
        // Fallback: use UTC offset calculation
        console.warn('Error in getPhilippineTime, using fallback:', e);
        const utcTime = now.getTime();
        const utcOffset = now.getTimezoneOffset() * 60000; // Local timezone offset in ms
        const philippineOffset = 8 * 60 * 60 * 1000; // UTC+8 in milliseconds
        return new Date(utcTime + utcOffset + philippineOffset);
    }
}

// Format time in 12-hour format with AM/PM
function format12HourTime(date) {
    let hours = date.getHours();
    const minutes = date.getMinutes().toString().padStart(2, '0');
    const seconds = date.getSeconds().toString().padStart(2, '0');
    const ampm = hours >= 12 ? 'PM' : 'AM';
    hours = hours % 12;
    hours = hours ? hours : 12; // the hour '0' should be '12'
    const hoursStr = hours.toString().padStart(2, '0');
    return `${hoursStr}:${minutes}:${seconds} ${ampm}`;
}

// Update time every second
function updateTime() {
    try {
        const now = new Date();
        
        // Format date directly from current time using Philippine timezone
        // This avoids any date reconstruction issues that cause flickering
        const dateOptions = { 
            timeZone: 'Asia/Manila',
            weekday: 'long',
            year: 'numeric', 
            month: 'long', 
            day: 'numeric'
        };
        const dateString = now.toLocaleDateString('en-US', dateOptions);
        
        // Format time directly from current time using Philippine timezone
        const timeOptions = {
            timeZone: 'Asia/Manila',
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit',
            hour12: true
        };
        const timeString = now.toLocaleTimeString('en-US', timeOptions);
        
        // Validate date string is not empty or invalid
        if (!dateString || dateString === 'Invalid Date' || dateString.includes('Invalid')) {
            console.warn('Invalid date string generated:', dateString);
            return; // Skip update if date string is invalid
        }
        
        // Update the main clock display
        const timeElement = document.getElementById('current-times');
        const dateElement = document.getElementById('current-date');
        const lastUpdatedElement = document.getElementById('last-updated');
        
        if (timeElement) {
            timeElement.textContent = timeString;
        }
        
        if (dateElement && dateString) {
            dateElement.textContent = dateString;
        }
        
        if (lastUpdatedElement) {
            lastUpdatedElement.textContent = `Last updated: ${timeString}`;
        }
        
        // Update working time if employee is currently working
        updateWorkingTime();
        
        // Update total hours display in summary
        updateTotalHoursDisplay();
        
        // Update real-time status
        updateRealTimeStatus();
        
    } catch (error) {
        console.error('Error updating time:', error);
        // Don't update if there's an error to prevent glitching
    }
}


// AJAX-based time update (backup method)
async function updateTimeAjax() {
    try {
        const response = await fetch('{{ route("attendance.current-time") }}');
        const data = await response.json();
        
        if (response.ok) {
            const timeElement = document.getElementById('current-time');
            const dateElement = document.getElementById('current-date');
            
            if (timeElement) {
                timeElement.textContent = data.time;
            }
            
            if (dateElement) {
                dateElement.textContent = data.date;
            }
            
            // Update last updated timestamp
            updateLastUpdated();
            
            console.log('Time updated via AJAX:', data.time);
        }
    } catch (error) {
        console.error('Error updating time via AJAX:', error);
    }
}

// Update last updated timestamp
function updateLastUpdated() {
    const lastUpdatedElement = document.getElementById('last-updated');
    if (lastUpdatedElement) {
        const philippineTime = getPhilippineTime();
        const timeString = format12HourTime(philippineTime);
        lastUpdatedElement.textContent = `Last updated: ${timeString}`;
    }
}

// Update real-time status
function updateRealTimeStatus() {
    const liveIndicator = document.getElementById('live-indicator');
    if (liveIndicator) {
        // Change color based on current status
        if (currentStatus && currentStatus.time_in && !currentStatus.time_out) {
            liveIndicator.className = 'w-2 h-2 bg-green-400 rounded-full mr-2 animate-pulse';
        } else if (currentStatus && currentStatus.time_out) {
            liveIndicator.className = 'w-2 h-2 bg-blue-400 rounded-full mr-2 animate-pulse';
        } else {
            liveIndicator.className = 'w-2 h-2 bg-yellow-400 rounded-full mr-2 animate-pulse';
        }
    }
}

// Update working time display
function updateWorkingTime() {
    const workingTimeElement = document.getElementById('working-time');
    const breakTimeElement = document.getElementById('break-time');
    
    // Check both attendanceRecord and currentStatus for time_in and break_start
    let timeIn = null;
    let hasTimeOut = false;
    let breakStart = null;
    let hasBreakEnd = false;
    
    // For multiple entries system: Use the active time entry's time_in
    if (currentStatus && currentStatus.active_time_entry && currentStatus.active_time_entry.time_in) {
        timeIn = new Date(currentStatus.active_time_entry.time_in);
        hasTimeOut = !!currentStatus.active_time_entry.time_out;
        console.log('Using active time entry for working time:', currentStatus.active_time_entry);
    }
    // First check currentStatus (from API) - for backward compatibility
    else if (currentStatus && currentStatus.time_in && !currentStatus.time_out) {
        // Parse time_in from currentStatus (it might be a formatted string)
        timeIn = new Date(currentStatus.time_in);
        hasTimeOut = !!currentStatus.time_out;
    }
    // Fallback to attendanceRecord (from initial page load)
    else if (attendanceRecord && attendanceRecord.time_in && !attendanceRecord.time_out) {
        timeIn = new Date(attendanceRecord.time_in);
        hasTimeOut = !!attendanceRecord.time_out;
    }
    
    // Check for active break
    if (currentStatus) {
        if (currentStatus.break_start) {
            breakStart = new Date(currentStatus.break_start);
            hasBreakEnd = !!currentStatus.break_end;
        }
    } else if (attendanceRecord && attendanceRecord.break_start) {
        breakStart = new Date(attendanceRecord.break_start);
        hasBreakEnd = !!attendanceRecord.break_end;
    }
    
    // Update break time if on break
    if (breakTimeElement && breakStart && !hasBreakEnd && !isNaN(breakStart.getTime())) {
        const now = getPhilippineTime();
        const diffMs = now - breakStart;
        
        if (diffMs >= 0) {
            const diffHours = Math.floor(diffMs / (1000 * 60 * 60));
            const diffMinutes = Math.floor((diffMs % (1000 * 60 * 60)) / (1000 * 60));
            breakTimeElement.textContent = `${diffHours}h ${diffMinutes}m`;
        } else {
            breakTimeElement.textContent = '0h 0m';
        }
        
        // Show break time section, hide working time section
        const breakTimeSection = document.getElementById('break-time-section');
        const workingTimeSection = document.getElementById('working-time-section');
        if (breakTimeSection) breakTimeSection.style.display = 'block';
        if (workingTimeSection) workingTimeSection.style.display = 'none';
        
        // Hide working time when on break
        if (workingTimeElement) {
            workingTimeElement.textContent = '0h 0m';
        }
        return; // Don't update working time when on break
    }
    
    // Show working time section, hide break time section when not on break
    const breakTimeSection = document.getElementById('break-time-section');
    const workingTimeSection = document.getElementById('working-time-section');
    if (breakTimeSection) breakTimeSection.style.display = 'none';
    if (workingTimeSection) workingTimeSection.style.display = 'block';
    
    // Update working time if not on break
    if (workingTimeElement && timeIn && !hasTimeOut && !isNaN(timeIn.getTime())) {
        const now = getPhilippineTime(); // Use Philippine time for consistency
        const diffMs = now - timeIn;
        
        // Ensure we don't show negative time
        if (diffMs < 0) {
            workingTimeElement.textContent = '0h 0m';
            return;
        }
        
        const diffHours = Math.floor(diffMs / (1000 * 60 * 60));
        const diffMinutes = Math.floor((diffMs % (1000 * 60 * 60)) / (1000 * 60));
        const diffSeconds = Math.floor((diffMs % (1000 * 60)) / 1000);
        
        // Show hours and minutes (without seconds for cleaner display)
        workingTimeElement.textContent = `${diffHours}h ${diffMinutes}m`;
        
        // Add a subtle color change every minute
        if (diffMinutes % 5 === 0 && diffSeconds === 0) {
            workingTimeElement.style.color = '#10B981';
            setTimeout(() => {
                workingTimeElement.style.color = '';
            }, 1000);
        }
    } else if (workingTimeElement) {
        // If not working, show 0h 0m
        workingTimeElement.textContent = '0h 0m';
    }
    
    // Hide break time if not on break
    if (breakTimeElement && (!breakStart || hasBreakEnd)) {
        breakTimeElement.textContent = '0h 0m';
    }
}

// Update total hours display
function updateTotalHoursDisplay() {
    const summaryElement = document.getElementById('summary-total-hours');
    if (!summaryElement) return;
    
    if (currentStatus.total_hours && currentStatus.total_hours > 0 && currentStatus.time_out) {
        // If timed out, use the stored total_hours from database
        const hours = Math.floor(currentStatus.total_hours);
        const minutes = Math.round((currentStatus.total_hours - hours) * 60);
        summaryElement.textContent = `${hours}h ${minutes}m`;
    } else if (currentStatus.time_in && !currentStatus.time_out) {
        // Calculate current working time (excluding break time)
        let timeIn = null;
        let breakStart = null;
        let breakEnd = null;
        
        // Get time_in and break times from currentStatus or attendanceRecord
        if (currentStatus.time_in) {
            timeIn = new Date(currentStatus.time_in);
            if (currentStatus.break_start) {
                breakStart = new Date(currentStatus.break_start);
            }
            if (currentStatus.break_end) {
                breakEnd = new Date(currentStatus.break_end);
            }
        } else if (attendanceRecord && attendanceRecord.time_in) {
            timeIn = new Date(attendanceRecord.time_in);
            if (attendanceRecord.break_start) {
                breakStart = new Date(attendanceRecord.break_start);
            }
            if (attendanceRecord.break_end) {
                breakEnd = new Date(attendanceRecord.break_end);
            }
        }
        
        if (timeIn && !isNaN(timeIn.getTime())) {
            const now = getPhilippineTime();
            let totalMs = now - timeIn;
            
            // Subtract break time if break has ended
            if (breakStart && breakEnd) {
                const breakMs = breakEnd - breakStart;
                totalMs -= breakMs;
            }
            // If currently on break, only count time from time_in to break_start
            else if (breakStart && !breakEnd) {
                totalMs = breakStart - timeIn;
            }
            
            if (totalMs >= 0) {
                const diffHours = Math.floor(totalMs / (1000 * 60 * 60));
                const diffMinutes = Math.floor((totalMs % (1000 * 60 * 60)) / (1000 * 60));
                const diffSeconds = Math.floor((totalMs % (1000 * 60)) / 1000);
        
                // Show working hours (excluding break time)
        summaryElement.textContent = `${diffHours}h ${diffMinutes}m ${diffSeconds}s`;
            } else {
                summaryElement.textContent = '0h 0m';
            }
        } else {
            summaryElement.textContent = '0h 0m';
        }
    } else {
        summaryElement.textContent = '0h 0m';
    }
}

// Load current attendance status
async function loadAttendanceStatus() {
    try {
        const response = await fetch('{{ route("attendance.status") }}');
        const data = await response.json();
        
        if (response.ok) {
            // Only update if we have valid data to prevent glitching
            if (data && typeof data === 'object') {
                // Store previous status to prevent flickering
                const previousStatus = currentStatus;
                
            currentStatus = data;
                
                // Update attendanceRecord with fresh data for working time calculation
                if (data && (data.time_in || data.attendance_record)) {
                    const record = data.attendance_record || data;
                    
                    // Get active break start if available
                    let breakStart = null;
                    if (data.active_break && data.active_break.break_start) {
                        breakStart = data.active_break.break_start;
                    } else if (data.breaks && Array.isArray(data.breaks)) {
                        const activeBreak = data.breaks.find(b => b.is_active);
                        if (activeBreak) {
                            breakStart = activeBreak.break_start;
                        }
                    } else {
                        breakStart = record.break_start || data.break_start || null;
                    }
                    
                    attendanceRecord = {
                        time_in: record.time_in || data.time_in,
                        time_out: record.time_out || data.time_out || null,
                        break_start: breakStart,
                        break_end: record.break_end || data.break_end || null,
                        status: record.status || data.status || 'present',
                        breaks: data.breaks || [],
                        time_entries: data.time_entries || []
                    };
                }
                
                // Only update UI if we have valid status data
                if (currentStatus && (currentStatus.status || currentStatus.time_in || currentStatus.attendance_record)) {
            updateUI();
                    updateWorkingTime(); // Update working time and break time when status is refreshed
                }
            }
        } else {
            showError(data.error || 'Failed to load attendance status');
        }
    } catch (error) {
        console.error('Error loading attendance status:', error);
        showError('Failed to load attendance status');
    }
}

// Initialize UI with database data
function initializeUI() {
    if (attendanceRecord) {
        // Check if there are time entries
        const timeEntries = attendanceRecord.time_entries || [];
        const activeEntry = timeEntries.find(entry => !entry.time_out);
        const hasActiveEntry = activeEntry !== null && activeEntry !== undefined;
        
        console.log('Initializing UI with attendance record:', {
            hasRecord: !!attendanceRecord,
            timeEntriesCount: timeEntries.length,
            hasActiveEntry: hasActiveEntry,
            activeEntry: activeEntry
        });
        
        // Use database data to set initial status - keep as ISO8601 strings for proper parsing
        currentStatus = {
            status: attendanceRecord.status || 'not_started',
            time_in: attendanceRecord.time_in || null,
            time_out: attendanceRecord.time_out || null,
            break_start: attendanceRecord.break_start || null,
            break_end: attendanceRecord.break_end || null,
            total_hours: attendanceRecord.total_hours || 0,
            time_entries: timeEntries,
            active_time_entry: activeEntry || null,
            entry_count: timeEntries.length,
            breaks: attendanceRecord.breaks || [],
            // Set can_time_in and can_time_out based on active entry
            can_time_in: !hasActiveEntry,
            can_time_out: hasActiveEntry
        };
        
        console.log('Initial currentStatus:', {
            can_time_in: currentStatus.can_time_in,
            can_time_out: currentStatus.can_time_out,
            entry_count: currentStatus.entry_count
        });
    } else {
        console.log('No attendance record found, initializing empty status');
        currentStatus = {
            status: 'not_started',
            time_in: null,
            time_out: null,
            break_start: null,
            break_end: null,
            total_hours: 0,
            time_entries: [],
            active_time_entry: null,
            entry_count: 0,
            can_time_in: true,
            can_time_out: false
        };
    }
    updateUI();
    updateWorkingTime(); // Initialize working/break time display
}

// Format time for display (12-hour format with AM/PM)
function formatTime(timeString) {
    if (!timeString) return null;
    const date = new Date(timeString);
    return date.toLocaleTimeString('en-US', { 
        hour: '2-digit', 
        minute: '2-digit',
        hour12: true 
    });
}

// Format time for summary display (12-hour format with AM/PM, no seconds)
function formatTimeForSummary(timeString) {
    if (!timeString || timeString === 'null' || timeString === 'undefined') return '--:--';
    try {
        // Parse the date string
        const date = new Date(timeString);
        
        // Validate the date is actually valid
        if (isNaN(date.getTime())) return '--:--';
        
        // Check if date is reasonable (not in 1970 or far in the past/future)
        // This prevents showing dates like December 3, 1970 due to parsing errors
        const year = date.getFullYear();
        const currentYear = new Date().getFullYear();
        if (year < 2000 || year > currentYear + 1) {
            console.warn('Invalid date year detected:', year, 'from string:', timeString);
            return '--:--';
        }
        
        // Format with explicit timezone to avoid conversion issues
        return date.toLocaleTimeString('en-US', { 
            hour: 'numeric', 
            minute: '2-digit',
            hour12: true,
            timeZone: 'Asia/Manila' // Explicitly use Philippine timezone
        });
    } catch (e) {
        console.error('Error formatting time:', e, 'from string:', timeString);
        return '--:--';
    }
}

// Update UI based on current status
function updateUI() {
    if (!currentStatus) return;

    const statusIcon = document.getElementById('status-icon');
    const statusTitle = document.getElementById('status-title');
    const statusMessage = document.getElementById('status-message');
    const timeInBtn = document.getElementById('time-in-btn');
    const timeOutBtn = document.getElementById('time-out-btn');
    const timeOutCard = document.getElementById('time-out-card');
    const breakSection = document.getElementById('break-section');
    const breakStartBtn = document.getElementById('break-start-btn');
    const breakEndBtn = document.getElementById('break-end-btn');
    const breakEndCard = document.getElementById('break-end-card');

    // Use can_time_in/can_time_out from API if available (supports multiple entries)
    const canTimeIn = currentStatus.can_time_in !== undefined ? currentStatus.can_time_in : !currentStatus.time_in;
    const canTimeOut = currentStatus.can_time_out !== undefined ? currentStatus.can_time_out : (currentStatus.time_in && !currentStatus.time_out);
    const hasActiveEntry = currentStatus.active_time_entry !== null && currentStatus.active_time_entry !== undefined;
    const entryCount = currentStatus.entry_count || 0;
    
    console.log('UpdateUI - Button states:', {
        canTimeIn,
        canTimeOut,
        hasActiveEntry,
        entryCount,
        activeEntry: currentStatus.active_time_entry
    });
    
    // Show/hide working time tracking section based on active entry
    const timeTrackingSection = document.getElementById('time-tracking-section');
    if (timeTrackingSection) {
        if (hasActiveEntry) {
            timeTrackingSection.style.display = 'block';
            console.log('Showing working time tracker');
        } else {
            timeTrackingSection.style.display = 'none';
            console.log('Hiding working time tracker');
        }
    }

    // Update status display
    if (currentStatus.status === 'not_started' || (!currentStatus.time_in && entryCount === 0)) {
        statusIcon.className = 'inline-flex items-center justify-center w-20 h-20 bg-green-100 rounded-full mb-4';
        statusIcon.innerHTML = '<i class="fas fa-check-circle text-3xl text-green-600"></i>';
        statusTitle.textContent = 'Ready to Clock In';
        statusMessage.textContent = 'You haven\'t clocked in today yet';
        
        // Hide break section
        if (breakSection) breakSection.style.display = 'none';
    } else if (hasActiveEntry) {
        // Currently clocked in (has active time entry)
        statusIcon.className = 'inline-flex items-center justify-center w-20 h-20 bg-blue-100 rounded-full mb-4';
        statusIcon.innerHTML = '<i class="fas fa-clock text-3xl text-blue-600"></i>';
        
        const hasActiveBreak = currentStatus.active_break !== null || 
                              (currentStatus.breaks && currentStatus.breaks.some(b => b.is_active)) ||
                              (currentStatus.break_start && !currentStatus.break_end);
        
        if (hasActiveBreak) {
            statusTitle.textContent = 'On Break';
            statusMessage.textContent = `Break started - Entry #${entryCount}`;
        } else {
            statusTitle.textContent = entryCount > 1 ? `Working (Entry #${entryCount})` : 'Currently Working';
            statusMessage.textContent = entryCount > 1 
                ? `Time entry #${entryCount} in progress`
                : `Clocked in at ${formatTimeForSummary(currentStatus.time_in)}`;
        }
        
        // Show break section when working
        if (breakSection) breakSection.style.display = 'grid';
    } else if (entryCount > 0 && !hasActiveEntry) {
        // Has entries but none active - ready to clock in again
        statusIcon.className = 'inline-flex items-center justify-center w-20 h-20 bg-purple-100 rounded-full mb-4';
        statusIcon.innerHTML = '<i class="fas fa-history text-3xl text-purple-600"></i>';
        statusTitle.textContent = 'Ready for Another Entry';
        statusMessage.textContent = `${entryCount} ${entryCount === 1 ? 'entry' : 'entries'} recorded today. You can clock in again.`;
        
        // Show break section but breaks are tied to entries
        if (breakSection) breakSection.style.display = 'grid';
    }

    // Update Time In button based on can_time_in
    if (canTimeIn) {
        timeInBtn.disabled = false;
        timeInBtn.className = 'w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-3 px-6 rounded-lg transition-colors';
        timeInBtn.innerHTML = entryCount > 0 
            ? `<i class="fas fa-play mr-2"></i>Clock In Again (#${entryCount + 1})`
            : '<i class="fas fa-play mr-2"></i>Clock In Now';
        console.log('Time In button: ENABLED');
    } else {
        timeInBtn.disabled = true;
        timeInBtn.className = 'w-full bg-gray-400 text-white font-semibold py-3 px-6 rounded-lg cursor-not-allowed';
        timeInBtn.innerHTML = '<i class="fas fa-play mr-2"></i>Clock In Now';
        console.log('Time In button: DISABLED');
    }

    // Update Time Out button based on can_time_out
    if (canTimeOut) {
        timeOutCard.className = 'bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow';
        timeOutBtn.disabled = false;
        timeOutBtn.className = 'w-full bg-red-600 hover:bg-red-700 text-white font-semibold py-3 px-6 rounded-lg transition-colors';
        console.log('Time Out button: ENABLED');
    } else {
        timeOutCard.className = 'bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow opacity-50';
        timeOutBtn.disabled = true;
        timeOutBtn.className = 'w-full bg-gray-400 text-white font-semibold py-3 px-6 rounded-lg cursor-not-allowed';
        console.log('Time Out button: DISABLED');
    }
    
    // Update break buttons
    if (hasActiveEntry) {
        const hasActiveBreak = currentStatus.active_break !== null || 
                              (currentStatus.breaks && currentStatus.breaks.some(b => b.is_active)) ||
                              (currentStatus.break_start && !currentStatus.break_end);
        const breakCount = currentStatus.break_count || 
                          (currentStatus.breaks ? currentStatus.breaks.length : 0) ||
                          ((currentStatus.break_start || currentStatus.break_end) ? 1 : 0);
        const canStartBreak = currentStatus.can_break_start !== undefined ? currentStatus.can_break_start : 
                             (!hasActiveBreak && breakCount < 2);
        
        if (hasActiveBreak) {
            // On break - show end break button
            if (breakStartBtn) {
                breakStartBtn.disabled = true;
                breakStartBtn.className = 'w-full bg-gray-400 text-white font-semibold py-3 px-6 rounded-lg cursor-not-allowed';
            }
            if (breakEndBtn) {
                breakEndBtn.disabled = false;
                breakEndBtn.className = 'w-full bg-orange-600 hover:bg-orange-700 text-white font-semibold py-3 px-6 rounded-lg transition-colors';
            }
            if (breakEndCard) breakEndCard.className = 'bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow';
        } else {
            // Not on break - show start break button if allowed
            if (breakStartBtn) {
                breakStartBtn.disabled = !canStartBreak;
                if (canStartBreak) {
                    breakStartBtn.className = 'w-full bg-yellow-600 hover:bg-yellow-700 text-white font-semibold py-3 px-6 rounded-lg transition-colors';
                } else {
                    breakStartBtn.className = 'w-full bg-gray-400 text-white font-semibold py-3 px-6 rounded-lg cursor-not-allowed';
                    if (breakCount >= 2) {
                        breakStartBtn.title = 'Maximum of 2 breaks per day allowed';
                    }
                }
            }
            if (breakEndBtn) {
                breakEndBtn.disabled = true;
                breakEndBtn.className = 'w-full bg-gray-400 text-white font-semibold py-3 px-6 rounded-lg cursor-not-allowed';
            }
            if (breakEndCard) breakEndCard.className = 'bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow opacity-50';
        }
    } else {
        // No active entry - disable break buttons
        if (breakStartBtn) {
            breakStartBtn.disabled = true;
            breakStartBtn.className = 'w-full bg-gray-400 text-white font-semibold py-3 px-6 rounded-lg cursor-not-allowed';
        }
        if (breakEndBtn) {
            breakEndBtn.disabled = true;
            breakEndBtn.className = 'w-full bg-gray-400 text-white font-semibold py-3 px-6 rounded-lg cursor-not-allowed';
        }
    }

    // Update summary with formatted times - only update if currentStatus has valid data
    if (currentStatus && currentStatus.time_in) {
        const timeInEl = document.getElementById('summary-time-in');
        if (timeInEl) {
            timeInEl.textContent = formatTimeForSummary(currentStatus.time_in);
        }
    }
    
    // Handle breaks - use breaks array if available, otherwise fallback to old break_start/break_end
    let activeBreakStart = null;
    let lastBreakEnd = null;
    
    if (currentStatus.breaks && Array.isArray(currentStatus.breaks)) {
        // Find active break
        const activeBreak = currentStatus.breaks.find(b => b.is_active);
        if (activeBreak) {
            activeBreakStart = activeBreak.break_start;
        }
        
        // Find last completed break
        const completedBreaks = currentStatus.breaks.filter(b => !b.is_active && b.break_end);
        if (completedBreaks.length > 0) {
            lastBreakEnd = completedBreaks[completedBreaks.length - 1].break_end;
        }
        
        // Update break count
        const breakCountEl = document.getElementById('summary-break-count');
        if (breakCountEl) {
            breakCountEl.textContent = currentStatus.break_count || currentStatus.breaks.length;
        }
        
        // Update total break time
        const totalBreakMinutes = currentStatus.total_break_minutes || 0;
        const totalBreakHours = Math.floor(totalBreakMinutes / 60);
        const totalBreakMins = totalBreakMinutes % 60;
        const totalBreakEl = document.getElementById('summary-total-break');
        if (totalBreakEl) {
            totalBreakEl.textContent = `${totalBreakHours}h ${totalBreakMins}m`;
        }
        
        // Show/hide over break warning
        const overBreakWarning = document.getElementById('over-break-warning');
        const overBreakMinutes = document.getElementById('over-break-minutes');
        if (currentStatus.is_over_break) {
            if (overBreakWarning) overBreakWarning.classList.remove('hidden');
            if (overBreakMinutes) overBreakMinutes.textContent = currentStatus.over_break_minutes || 0;
        } else {
            if (overBreakWarning) overBreakWarning.classList.add('hidden');
        }
    } else {
        // Fallback to old break_start/break_end
        activeBreakStart = currentStatus.break_start;
        lastBreakEnd = currentStatus.break_end;
        
        // Update break count (default to 1 if break exists)
        const breakCountEl = document.getElementById('summary-break-count');
        if (breakCountEl) {
            breakCountEl.textContent = (currentStatus.break_start || currentStatus.break_end) ? '1' : '0';
        }
        
        // Calculate total break from old fields
        let totalBreakMinutes = 0;
        if (currentStatus.break_start && currentStatus.break_end) {
            const breakStart = new Date(currentStatus.break_start);
            const breakEnd = new Date(currentStatus.break_end);
            totalBreakMinutes = Math.floor((breakEnd - breakStart) / 60000);
        } else if (currentStatus.break_start) {
            const breakStart = new Date(currentStatus.break_start);
            totalBreakMinutes = Math.floor((new Date() - breakStart) / 60000);
        }
        
        const totalBreakHours = Math.floor(totalBreakMinutes / 60);
        const totalBreakMins = totalBreakMinutes % 60;
        const totalBreakEl = document.getElementById('summary-total-break');
        if (totalBreakEl) {
            totalBreakEl.textContent = `${totalBreakHours}h ${totalBreakMins}m`;
        }
        
        // Check over break
        const isOverBreak = totalBreakMinutes > 90;
        const overBreakWarning = document.getElementById('over-break-warning');
        const overBreakMinutes = document.getElementById('over-break-minutes');
        if (isOverBreak) {
            if (overBreakWarning) overBreakWarning.classList.remove('hidden');
            if (overBreakMinutes) overBreakMinutes.textContent = totalBreakMinutes - 90;
        } else {
            if (overBreakWarning) overBreakWarning.classList.add('hidden');
        }
    }
    
    // Safely update break and time out displays
    const breakStartEl = document.getElementById('summary-break-start');
    const breakEndEl = document.getElementById('summary-break-end');
    const timeOutEl = document.getElementById('summary-time-out');
    
    if (breakStartEl) {
        breakStartEl.textContent = formatTimeForSummary(activeBreakStart);
    }
    if (breakEndEl) {
        breakEndEl.textContent = formatTimeForSummary(lastBreakEnd);
    }
    if (timeOutEl && currentStatus) {
        timeOutEl.textContent = formatTimeForSummary(currentStatus.time_out);
    }
    
    // Update total hours display
    updateTotalHoursDisplay();
    
    // Update time entries list
    updateTimeEntriesList();
}

// Render time entries list
function updateTimeEntriesList() {
    const container = document.getElementById('time-entries-list');
    const countBadge = document.getElementById('entry-count-badge');
    
    if (!container) return;
    
    const timeEntries = currentStatus?.time_entries || [];
    
    // Update count badge
    if (countBadge) {
        countBadge.textContent = `(${timeEntries.length} ${timeEntries.length === 1 ? 'entry' : 'entries'})`;
    }
    
    if (timeEntries.length === 0) {
        container.innerHTML = `
            <div class="text-center py-4 text-gray-500">
                <i class="fas fa-clock text-2xl mb-2"></i>
                <p class="text-sm">No time entries yet. Clock in to start.</p>
            </div>
        `;
        return;
    }
    
    // Render each time entry
    let html = '';
    timeEntries.forEach((entry, index) => {
        const isActive = entry.is_active;
        const entryNum = index + 1;
        const timeIn = formatTimeForSummary(entry.time_in);
        const timeOut = entry.time_out ? formatTimeForSummary(entry.time_out) : '--:--';
        const duration = entry.duration || (entry.hours_worked ? formatHoursMinutes(entry.hours_worked) : 'In progress');
        
        const bgColor = isActive ? 'bg-blue-50 border-blue-200' : 'bg-gray-50 border-gray-200';
        const statusBadge = isActive 
            ? '<span class="px-2 py-0.5 text-xs bg-blue-500 text-white rounded-full">Active</span>'
            : '<span class="px-2 py-0.5 text-xs bg-green-100 text-green-700 rounded-full">Completed</span>';
        
        html += `
            <div class="flex items-center justify-between p-3 ${bgColor} border rounded-lg">
                <div class="flex items-center">
                    <div class="w-8 h-8 ${isActive ? 'bg-blue-100' : 'bg-gray-200'} rounded-full flex items-center justify-center mr-3">
                        <span class="text-sm font-bold ${isActive ? 'text-blue-600' : 'text-gray-600'}">#${entryNum}</span>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="font-medium text-gray-900">Entry ${entryNum}</span>
                            ${statusBadge}
                        </div>
                        <div class="text-sm text-gray-500">
                            <i class="fas fa-sign-in-alt text-green-500 mr-1"></i>${timeIn}
                            <span class="mx-2">→</span>
                            <i class="fas fa-sign-out-alt text-red-500 mr-1"></i>${timeOut}
                        </div>
                    </div>
                </div>
                <div class="text-right">
                    <div class="text-lg font-semibold ${isActive ? 'text-blue-600' : 'text-gray-700'}">${duration}</div>
                    <div class="text-xs text-gray-500">${entry.entry_type || 'regular'}</div>
                </div>
            </div>
        `;
    });
    
    container.innerHTML = html;
}

// Format hours as Xh Ym
function formatHoursMinutes(hours) {
    const h = Math.floor(hours);
    const m = Math.round((hours - h) * 60);
    if (h > 0 && m > 0) return `${h}h ${m}m`;
    if (h > 0) return `${h}h`;
    return `${m}m`;
}

// Time In function
async function timeIn() {
    const btn = document.getElementById('time-in-btn');
    const originalText = btn.innerHTML;
    
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Processing...';
    
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
            // Update status immediately without page reload
            await loadAttendanceStatus();
        } else {
            console.error('Time in failed:', data);
            showError(data.error || 'Failed to clock in');
        }
    } catch (error) {
        console.error('Error clocking in:', error);
        showError('Failed to clock in');
    } finally {
        btn.disabled = false;
        btn.innerHTML = originalText;
    }
}

// Time Out function
async function timeOut() {
    const btn = document.getElementById('time-out-btn');
    const originalText = btn.innerHTML;
    
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Processing...';
    
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
            // Update status immediately without page reload
            await loadAttendanceStatus();
        } else {
            showError(data.error || 'Failed to clock out');
        }
    } catch (error) {
        console.error('Error clocking out:', error);
        showError('Failed to clock out');
    } finally {
        btn.disabled = false;
        btn.innerHTML = originalText;
    }
}

// Break Start function
async function breakStart() {
    const btn = document.getElementById('break-start-btn');
    const originalText = btn.innerHTML;
    
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Processing...';
    
    try {
        const response = await fetch('{{ route("attendance.break-start") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        });
        
        const data = await response.json();
        
        if (response.ok) {
            showSuccess(data.message);
            await loadAttendanceStatus();
            
            // Show over break warning if applicable
            if (data.is_over_break) {
                const overBreakWarning = document.getElementById('over-break-warning');
                const overBreakMinutes = document.getElementById('over-break-minutes');
                if (overBreakWarning) overBreakWarning.classList.remove('hidden');
                if (overBreakMinutes) overBreakMinutes.textContent = data.over_break_minutes || 0;
            }
        } else {
            showError(data.error || 'Failed to start break');
        }
    } catch (error) {
        console.error('Error starting break:', error);
        showError('Failed to start break');
    } finally {
        btn.disabled = false;
        btn.innerHTML = originalText;
    }
}

// Break End function
async function breakEnd() {
    const btn = document.getElementById('break-end-btn');
    const originalText = btn.innerHTML;
    
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Processing...';
    
    try {
        const response = await fetch('{{ route("attendance.break-end") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        });
        
        const data = await response.json();
        
        if (response.ok) {
            showSuccess(data.message);
            await loadAttendanceStatus();
            
            // Show over break warning if applicable
            if (data.is_over_break) {
                const overBreakWarning = document.getElementById('over-break-warning');
                const overBreakMinutes = document.getElementById('over-break-minutes');
                if (overBreakWarning) overBreakWarning.classList.remove('hidden');
                if (overBreakMinutes) overBreakMinutes.textContent = data.over_break_minutes || 0;
            }
        } else {
            showError(data.error || 'Failed to end break');
        }
    } catch (error) {
        console.error('Error ending break:', error);
        showError('Failed to end break');
    } finally {
        btn.disabled = false;
        btn.innerHTML = originalText;
    }
}

// Show success message
function showSuccess(message) {
    // Create a simple toast notification
    const toast = document.createElement('div');
    toast.className = 'fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50';
    toast.textContent = message;
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.remove();
    }, 3000);
}

// Show error message
function showError(message) {
    // Create a simple toast notification
    const toast = document.createElement('div');
    toast.className = 'fixed top-4 right-4 bg-red-500 text-white px-6 py-3 rounded-lg shadow-lg z-50';
    toast.textContent = message;
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.remove();
    }, 5000);
}

// Update recent activity section
function updateRecentActivity() {
    // This would ideally fetch fresh data from the server
    // For now, we'll just refresh the page to show updated data
    // In a more advanced implementation, we could make an AJAX call to get fresh recent activity
}

// Initialize page
document.addEventListener('DOMContentLoaded', function() {
    console.log('Page loaded, initializing clock...');
    
    // Start clock immediately
    updateTime();
    
    // Set up interval for clock updates every second
    setInterval(updateTime, 1000);
    
    // Also set up AJAX-based updates every 5 seconds as backup
    setInterval(updateTimeAjax, 5000);
    
    // Initialize with database data
    initializeUI();
    
    // Load fresh status from API
    loadAttendanceStatus();
    
    // Refresh status every 30 seconds
    setInterval(loadAttendanceStatus, 30000);
    
    console.log('Clock initialized successfully');
    
    // Add a real-time status indicator
    const statusIndicator = document.createElement('div');
    statusIndicator.id = 'realtime-status';
    statusIndicator.className = 'fixed top-4 left-4 bg-green-500 text-white px-3 py-1 rounded-lg text-sm font-medium z-50';
    statusIndicator.textContent = '🟢 Real-time Active';
    document.body.appendChild(statusIndicator);
    
    // Hide status indicator after 3 seconds
    setTimeout(() => {
        statusIndicator.style.opacity = '0';
        setTimeout(() => {
            statusIndicator.remove();
        }, 500);
    }, 3000);
});

// Start clock immediately if DOM is already loaded
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function() {
        console.log('DOM loaded, starting clock...');
        updateTime();
        setInterval(updateTime, 1000);
        // setInterval(updateTimeAjax, 5000); // Disabled to prevent conflicts
    });
} else {
    // DOM is already loaded
    console.log('DOM already loaded, starting clock immediately...');
    updateTime();
    setInterval(updateTime, 1000);
    // setInterval(updateTimeAjax, 5000); // Disabled to prevent conflicts
}
</script>
@endsection

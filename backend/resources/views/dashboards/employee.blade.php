{{-- employee/dashboard.blade.php --}}

@extends('layouts.dashboard-base')

@section('title', 'Employee Dashboard')

@php
    $user = auth()->user();
    $pageTitle = 'Employee Dashboard';
    $activeRoute = 'dashboard';
@endphp

@section('content')
<!-- Confirmation Modal -->
<div id="confirmation-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <!-- Modal Icon -->
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 mb-4" id="modal-icon-container">
                <i id="modal-icon" class="fas fa-question-circle text-blue-600 text-xl"></i>
            </div>
            
            <!-- Modal Content -->
            <div class="text-center">
                <h3 id="modal-title" class="text-lg font-medium text-gray-900 mb-2"></h3>
                <p id="modal-message" class="text-sm text-gray-500 mb-4"></p>
                
                <!-- Action Buttons -->
                <div class="flex justify-center space-x-4 mt-6">
                    <button id="modal-cancel-btn" type="button" class="px-5 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-300 transition-colors">
                        Cancel
                    </button>
                    <button id="modal-confirm-btn" type="button" class="px-5 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors">
                        Confirm
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Forgot Time In/Out Modal -->
<div id="forgot-time-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-lg shadow-lg rounded-md bg-white">
        <div class="mt-1">
            <div class="flex items-center justify-center h-12 w-12 rounded-full bg-yellow-100 mx-auto mb-4">
                <i class="fas fa-clock text-yellow-700 text-xl"></i>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 text-center mb-1">Forgot to time in / out?</h3>
            <p class="text-sm text-gray-500 text-center mb-5">Please confirm what you forgot and provide your reason.</p>

            <form id="forgot-time-form" method="POST" action="{{ route('hr.help-support-ticket-store') }}" class="space-y-4">
                @csrf
                <input type="hidden" name="subject" id="forgot-subject">
                <input type="hidden" name="category" value="attendance">
                <input type="hidden" name="message" id="forgot-message">

                <div>
                    <label for="forgot-type" class="block text-sm font-medium text-gray-700 mb-2">What did you forget?</label>
                    <select id="forgot-type" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500" required>
                        <option value="">Select an option</option>
                        <option value="time in">I forgot to Time In</option>
                        <option value="time out">I forgot to Time Out</option>
                    </select>
                </div>

                <div>
                    <label for="forgot-reason" class="block text-sm font-medium text-gray-700 mb-2">Reason</label>
                    <textarea id="forgot-reason" rows="4" placeholder="Please explain why you forgot..." class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500" required></textarea>
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" onclick="closeForgotTimeModal()" class="px-5 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300 transition-colors">Cancel</button>
                    <button type="submit" class="px-5 py-2 bg-yellow-600 text-white rounded-md hover:bg-yellow-700 transition-colors">Submit Request</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Immediate clock start script -->
<script>
// Start clock immediately when this script loads
(function() {
    function getPhilippineTime() {
        // Get current UTC time
        const now = new Date();
        // Philippine Standard Time is UTC+8
        const philippineTime = new Date(now.toLocaleString("en-US", {timeZone: "Asia/Manila"}));
        return philippineTime;
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
        const philippineTime = getPhilippineTime();

        const timeString = format12HourTime(philippineTime);

        // Format date
        const dateOptions = {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        };
        const dateString = philippineTime.toLocaleDateString('en-US', dateOptions);

        const timeElement = document.getElementById('current-times');
        const dateElement = document.getElementById('current-date');
        const lastUpdatedElement = document.getElementById('last-updated');

        if (timeElement) {
            timeElement.textContent = timeString;
        }
        if (dateElement) {
            dateElement.textContent = dateString;
        }
        if (lastUpdatedElement) {
            lastUpdatedElement.textContent = `Last updated: ${timeString}`;
        }
    }

    // Start immediately
    startClockNow();

    // Set up interval to update every second
    setInterval(startClockNow, 1000);
})();
</script>

    <!-- Welcome Section -->
    <div class="mb-6 sm:mb-8">
        <h2 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-2">Welcome back, {{ $stats['employee_name'] }}!</h2>
        <p class="text-sm sm:text-base text-gray-600">Here's your personal information and payroll history.</p>
    </div>

    <!-- Time In/Out Section -->
    <div class="mb-6 sm:mb-8">
        <!-- Current Time Display -->
        <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-2xl p-6 text-center text-white shadow-lg mb-4">
            <div class="text-4xl font-bold mb-2" id="current-times">--:--:--</div>
            <div class="text-lg opacity-90" id="current-date">Loading...</div>
            <div class="text-sm opacity-75 mt-2">Philippine Standard Time</div>
            <div class="text-xs opacity-50 mt-1" id="last-updated">Last updated: --:--:--</div>
            <div class="mt-2">
                <div class="inline-flex items-center bg-white bg-opacity-20 px-3 py-1 rounded-full">
                    <div class="w-2 h-2 bg-green-400 rounded-full mr-2 animate-pulse" id="live-indicator"></div>
                    <span class="text-xs font-medium">LIVE</span>
                </div>
            </div>
            @if($todayAttendance && $todayAttendance->time_in && !$todayAttendance->time_out)
                <div class="mt-4 pt-4 border-t border-blue-400">
                    <div class="text-lg opacity-90">Working for:</div>
                    <div class="text-2xl font-bold" id="working-time">
                        @php
                            $timeIn = \Carbon\Carbon::parse($todayAttendance->time_in);
                            $now = \App\Helpers\TimezoneHelper::now();
                            $diffMinutes = $now->diffInMinutes($timeIn);
                            $hours = floor($diffMinutes / 60);
                            $minutes = $diffMinutes % 60;
                            echo "{$hours}h {$minutes}m";
                        @endphp
                    </div>
                </div>
            @endif
        </div>

        <!-- Time In/Out Actions -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Time In Card -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 hover:shadow-md transition-shadow">
                <div class="text-center">
                    <div class="inline-flex items-center justify-center w-12 h-12 bg-green-100 rounded-full mb-3">
                        <i class="fas fa-sign-in-alt text-xl text-green-600"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Time In</h3>
                    <p class="text-gray-600 text-sm mb-3">Start your workday</p>
                    <button id="time-in-btn" class="w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors text-sm" onclick="confirmTimeIn()">
                        <i class="fas fa-play mr-2"></i>
                        Clock In Now
                    </button>
                </div>
            </div>

            <!-- Time Out Card -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 hover:shadow-md transition-shadow" id="time-out-card">
                <div class="text-center">
                    <div class="inline-flex items-center justify-center w-12 h-12 bg-red-100 rounded-full mb-3">
                        <i class="fas fa-sign-out-alt text-xl text-red-600"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Time Out</h3>
                    <p class="text-gray-600 text-sm mb-3">End your workday</p>
                    <button id="time-out-btn" class="w-full bg-gray-400 text-white font-semibold py-2 px-4 rounded-lg cursor-not-allowed text-sm" disabled onclick="confirmTimeOut()">
                        <i class="fas fa-stop mr-2"></i>
                        Clock Out
                    </button>
                </div>
            </div>
        </div>

        @if(!$todayAttendance || !$todayAttendance->time_in || $todayAttendance->time_out)
            <div class="mt-4 bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-exclamation-triangle text-yellow-400"></i>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-yellow-800">
                            Access Restricted
                        </h3>
                        <div class="mt-2 text-sm text-yellow-700">
                            <p>You must be currently timed in to access other modules of the system. Please clock in to continue with your work.</p>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <!-- Personal Info Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6 mb-6 sm:mb-8">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-id-badge text-blue-600 text-xl"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Employee ID</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['employee_id'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-briefcase text-green-600 text-xl"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Position</p>
                    <p class="text-lg font-bold text-gray-900">{{ $stats['position'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-building text-purple-600 text-xl"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Department</p>
                    <p class="text-lg font-bold text-gray-900">{{ $stats['department'] }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Salary and Hire Date -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-money-bill-wave text-yellow-600 text-xl"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Monthly Salary</p>
                    <p class="text-2xl font-bold text-gray-900">₱{{ number_format($stats['salary'], 2) }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-12 h-12 bg-indigo-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-calendar-alt text-indigo-600 text-xl"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Hire Date</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['hire_date']->format('M d, Y') }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts and Tables Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
        <!-- Yearly Summary -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-semibold text-gray-900">Yearly Summary</h3>
                <button class="text-blue-600 hover:text-blue-700 text-sm font-medium">View All</button>
            </div>
            <div class="space-y-4">
                @forelse($yearly_summary as $summary)
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <div>
                        <p class="text-sm font-medium text-gray-900">{{ $summary->year }}</p>
                        <p class="text-xs text-gray-500">{{ $summary->payroll_count }} payrolls</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-medium text-gray-900">₱{{ number_format($summary->total_net_pay, 2) }}</p>
                        <p class="text-xs text-gray-500">Total Net Pay</p>
                    </div>
                </div>
                @empty
                <div class="text-center py-8">
                    <i class="fas fa-chart-line text-gray-400 text-4xl mb-4"></i>
                    <p class="text-gray-500">No payroll data available</p>
                </div>
                @endforelse
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-semibold text-gray-900">Quick Actions</h3>
            </div>
            <div class="space-y-4">
                <!-- Time In Button -->
                @if(!$todayAttendance || !$todayAttendance->time_in || $todayAttendance->time_out)
                <button id="quick-time-in-btn" onclick="confirmTimeIn()" class="w-full flex items-center justify-center px-4 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                    <i class="fas fa-sign-in-alt mr-2"></i>
                    Time In
                </button>
                @else
                <button disabled class="w-full flex items-center justify-center px-4 py-3 bg-gray-400 text-white rounded-lg cursor-not-allowed">
                    <i class="fas fa-check mr-2"></i>
                    Already Clocked In
                </button>
                @endif

                <!-- Time Out Button -->
                @if($todayAttendance && $todayAttendance->time_in && !$todayAttendance->time_out)
                <button id="quick-time-out-btn" onclick="confirmTimeOut()" class="w-full flex items-center justify-center px-4 py-3 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                    <i class="fas fa-sign-out-alt mr-2"></i>
                    Time Out
                </button>
                @elseif($todayAttendance && $todayAttendance->time_out)
                <button disabled class="w-full flex items-center justify-center px-4 py-3 bg-gray-400 text-white rounded-lg cursor-not-allowed">
                    <i class="fas fa-check mr-2"></i>
                    Already Clocked Out
                </button>
                @else
                <button disabled class="w-full flex items-center justify-center px-4 py-3 bg-gray-300 text-gray-500 rounded-lg cursor-not-allowed">
                    <i class="fas fa-sign-out-alt mr-2"></i>
                    Time Out (Clock In First)
                </button>
                @endif

                <!-- Update Profile Button -->
                <a href="{{ route('hr.profile') }}" class="w-full flex items-center justify-center px-4 py-3 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                    <i class="fas fa-edit mr-2"></i>
                    Update Profile
                </a>

                <!-- Forgot Time In/Out Button -->
                <button type="button" onclick="openForgotTimeModal()" class="w-full flex items-center justify-center px-4 py-3 bg-yellow-100 text-yellow-800 rounded-lg hover:bg-yellow-200 transition-colors">
                    <i class="fas fa-clock mr-2"></i>
                    Forgot to time in / out?
                </button>
                
                <!-- Contact HR Button -->
                <button class="w-full flex items-center justify-center px-4 py-3 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                    <i class="fas fa-question-circle mr-2"></i>
                    Contact HR
                </button>
            </div>
        </div>
    </div>

    <!-- Recent Payrolls Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900">Recent Payrolls</h3>
                <button class="text-blue-600 hover:text-blue-700 text-sm font-medium">View All</button>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pay Period</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Gross Pay</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Deductions</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Net Pay</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($recent_payrolls as $payroll)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div>
                                <div class="text-sm font-medium text-gray-900">{{ $payroll->pay_period_start->format('M d') }} - {{ $payroll->pay_period_end->format('M d, Y') }}</div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">₱{{ number_format($payroll->gross_pay, 2) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">₱{{ number_format($payroll->gross_pay - $payroll->net_pay, 2) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">₱{{ number_format($payroll->net_pay, 2) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $payroll->status === 'processed' ? 'bg-green-100 text-green-800' : ($payroll->status === 'paid' ? 'bg-blue-100 text-blue-800' : 'bg-yellow-100 text-yellow-800') }}">
                                {{ ucfirst($payroll->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            @if(in_array($payroll->status, ['approved', 'processed', 'paid']))
                                <button onclick="downloadSinglePayslip('{{ $payroll->id }}')" 
                                      class="text-blue-600 hover:text-blue-900">
                                    <i class="fas fa-download mr-1"></i> Download
                                </button>
                            @else
                                <span class="text-gray-400">Not available</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center">
                            <i class="fas fa-money-bill-wave text-gray-400 text-4xl mb-4"></i>
                            <p class="text-gray-500">No payroll records found</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
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
let attendanceRecord = dataElement ? JSON.parse(dataElement.getAttribute('data-today-attendance') || 'null') : null;
let recentActivity = dataElement ? JSON.parse(dataElement.getAttribute('data-recent-activity') || '[]') : [];

// Modal variables
let pendingAction = null; // Will store the function to execute after confirmation

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

// ============================================================
// CONFIRMATION MODAL FUNCTIONS
// ============================================================

// Show confirmation modal
function showConfirmationModal(title, message, confirmAction, options = {}) {
    // Store the action to execute after confirmation
    pendingAction = confirmAction;
    
    // Set modal color and icon based on options
    const modalColor = options.color || 'blue';
    const modalIcon = options.icon || 'fa-question-circle';
    
    // Update modal content
    document.getElementById('modal-title').textContent = title;
    document.getElementById('modal-message').textContent = message;
    document.getElementById('modal-icon').className = `fas ${modalIcon} text-${modalColor}-600 text-xl`;
    
    // Update modal styling
    const modalIconContainer = document.getElementById('modal-icon-container');
    const confirmBtn = document.getElementById('modal-confirm-btn');
    
    // Update modal background color
    modalIconContainer.className = `mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-${modalColor}-100 mb-4`;
    
    // Update confirm button color
    confirmBtn.className = `px-5 py-2 bg-${modalColor}-600 text-white rounded-md hover:bg-${modalColor}-700 focus:outline-none focus:ring-2 focus:ring-${modalColor}-500 transition-colors`;
    
    // Show modal
    const modal = document.getElementById('confirmation-modal');
    modal.classList.remove('hidden');
    modal.classList.add('block');
}

// Hide confirmation modal
function hideConfirmationModal() {
    const modal = document.getElementById('confirmation-modal');
    modal.classList.remove('block');
    modal.classList.add('hidden');
    pendingAction = null;
}

// ============================================================
// FORGOT TIME IN/OUT MODAL FUNCTIONS
// ============================================================
function openForgotTimeModal() {
    const modal = document.getElementById('forgot-time-modal');
    if (!modal) return;
    modal.classList.remove('hidden');
    modal.classList.add('block');
}

function closeForgotTimeModal() {
    const modal = document.getElementById('forgot-time-modal');
    if (!modal) return;
    modal.classList.remove('block');
    modal.classList.add('hidden');
}

// Initialize modal event listeners
function initializeModal() {
    const modal = document.getElementById('confirmation-modal');
    const cancelBtn = document.getElementById('modal-cancel-btn');
    const confirmBtn = document.getElementById('modal-confirm-btn');
    
    // Close modal when clicking cancel button
    cancelBtn.addEventListener('click', hideConfirmationModal);
    
    // Execute pending action when clicking confirm button
    confirmBtn.addEventListener('click', function() {
        if (pendingAction) {
            pendingAction();
        }
        hideConfirmationModal();
    });
    
    // Close modal when clicking outside of it
    modal.addEventListener('click', function(event) {
        if (event.target === modal) {
            hideConfirmationModal();
        }
    });
}

// ============================================================
// TIME IN/OUT CONFIRMATION FUNCTIONS
// ============================================================

// Confirm Time In
function confirmTimeIn() {
    // Get current time for the confirmation message
    const currentTime = getPhilippineTime();
    const formattedTime = format12HourTime(currentTime);
    
    showConfirmationModal(
        'Confirm Time In',
        `Are you sure you want to clock in at ${formattedTime}?`,
        timeIn, // This is the original timeIn function that will be called on confirmation
        {
            color: 'green',
            icon: 'fa-sign-in-alt'
        }
    );
}

// Confirm Time Out
function confirmTimeOut() {
    // Get current time for the confirmation message
    const currentTime = getPhilippineTime();
    const formattedTime = format12HourTime(currentTime);
    
    showConfirmationModal(
        'Confirm Time Out',
        `Are you sure you want to clock out at ${formattedTime}?`,
        timeOut, // This is the original timeOut function that will be called on confirmation
        {
            color: 'red',
            icon: 'fa-sign-out-alt'
        }
    );
}

// Update working time display
function updateWorkingTime() {
    if (!attendanceRecord || !attendanceRecord.time_in || attendanceRecord.time_out) {
        return;
    }

    const timeIn = new Date(attendanceRecord.time_in);
    const now = getPhilippineTime();
    const diffMs = now - timeIn;
    
    if (diffMs < 0) return;
    
    const diffHours = Math.floor(diffMs / (1000 * 60 * 60));
    const diffMinutes = Math.floor((diffMs % (1000 * 60 * 60)) / (1000 * 60));

    const workingTimeElement = document.getElementById('working-time');
    if (workingTimeElement) {
        workingTimeElement.textContent = `${diffHours}h ${diffMinutes}m`;
    }
}

// Update UI based on attendance status
function updateAttendanceUI() {
    const timeInBtn = document.getElementById('time-in-btn');
    const timeOutBtn = document.getElementById('time-out-btn');
    const timeOutCard = document.getElementById('time-out-card');

    if (attendanceRecord) {
        if (attendanceRecord.time_in && !attendanceRecord.time_out) {
            // Already timed in, enable time out
            if (timeInBtn) {
                timeInBtn.disabled = true;
                timeInBtn.className = 'w-full bg-gray-400 text-white font-semibold py-2 px-4 rounded-lg cursor-not-allowed text-sm';
                timeInBtn.innerHTML = '<i class="fas fa-check mr-2"></i>Already Clocked In';
            }
            if (timeOutBtn) {
                timeOutBtn.disabled = false;
                timeOutBtn.className = 'w-full bg-red-600 hover:bg-red-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors text-sm';
            }
            updateWorkingTime();
        } else if (attendanceRecord.time_out) {
            // Already timed out
            if (timeInBtn) {
                timeInBtn.disabled = true;
                timeInBtn.className = 'w-full bg-gray-400 text-white font-semibold py-2 px-4 rounded-lg cursor-not-allowed text-sm';
                timeInBtn.innerHTML = '<i class="fas fa-check mr-2"></i>Already Clocked In';
            }
            if (timeOutBtn) {
                timeOutBtn.disabled = true;
                timeOutBtn.className = 'w-full bg-gray-400 text-white font-semibold py-2 px-4 rounded-lg cursor-not-allowed text-sm';
                timeOutBtn.innerHTML = '<i class="fas fa-check mr-2"></i>Already Clocked Out';
            }
        }
    }
}

// Time In function (original - called after confirmation)
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
            attendanceRecord = data.attendance_record;
            updateAttendanceUI();
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
    } finally {
        btn.disabled = false;
        btn.innerHTML = originalText;
    }
}

// Time Out function (original - called after confirmation)
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
            attendanceRecord = data.attendance_record;
            updateAttendanceUI();
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
    } finally {
        btn.disabled = false;
        btn.innerHTML = originalText;
    }
}

// Show success message
function showSuccess(message) {
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
    const toast = document.createElement('div');
    toast.className = 'fixed top-4 right-4 bg-red-500 text-white px-6 py-3 rounded-lg shadow-lg z-50';
    toast.textContent = message;
    document.body.appendChild(toast);
    setTimeout(() => {
        toast.remove();
    }, 3000);
}

// ============================================================
// PAYSLIP DOWNLOAD FUNCTIONS
// ============================================================

// Get payslip download URL
function getPayslipDownloadUrl(payrollId) {
    return `/employee/payslip/download/${payrollId}`;
}

function getTestDownloadUrl(payrollId) {
    return `/employee/test-download/${payrollId}`;
}

// Test function to check if download works
async function testDownloadRoute(payrollId) {
    try {
        const url = getTestDownloadUrl(payrollId);
        console.log('Testing download route:', url);
        
        const response = await fetch(url);
        const data = await response.json();
        console.log('Test download route response:', data);
        
        if (data.success) {
            return { success: true, downloadable: data.downloadable, message: data.message };
        } else {
            return { success: false, error: data.error || 'Route test failed' };
        }
    } catch (error) {
        console.error('Route test failed:', error);
        return { success: false, error: 'Route test failed: ' + error.message };
    }
}

// Show loading overlay
function showLoadingOverlay(message = 'Generating PDF...') {
    hideLoadingOverlay();
    
    const overlay = document.createElement('div');
    overlay.id = 'loading-overlay-payslip';
    overlay.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.8);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 9999;
        flex-direction: column;
        color: white;
        font-size: 18px;
    `;
    
    overlay.innerHTML = `
        <div class="text-center">
            <i class="fas fa-spinner fa-spin fa-3x mb-4"></i>
            <div>${message}</div>
            <div class="text-sm mt-2 text-gray-300">Please wait while we generate your payslip...</div>
        </div>
    `;
    
    document.body.appendChild(overlay);
    document.body.style.overflow = 'hidden';
}

// Hide loading overlay
function hideLoadingOverlay() {
    const overlay = document.getElementById('loading-overlay-payslip');
    if (overlay) {
        overlay.remove();
        document.body.style.overflow = '';
    }
}

// Main download function (for navigation button)
async function downloadEmployeePayslip(payrollId) {
    console.log('Download Employee Payslip called for ID:', payrollId);
    
    // Show loading state for navigation button
    const navBtn = document.getElementById('nav-download-payslip-btn');
    if (navBtn) {
        const originalText = navBtn.innerHTML;
        navBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Generating...';
        navBtn.disabled = true;
        
        // Restore button after 5 seconds even if error
        setTimeout(() => {
            navBtn.innerHTML = originalText;
            navBtn.disabled = false;
        }, 5000);
    }
    
    try {
        // First test the route
        const testResult = await testDownloadRoute(payrollId);
        console.log('Test result:', testResult);
        
        if (!testResult.success) {
            throw new Error(testResult.error || 'Cannot connect to server');
        }
        
        if (!testResult.downloadable) {
            throw new Error('Payslip is not available for download yet. Status: ' + (testResult.payroll_status || 'unknown'));
        }
        
        // Direct download approach
        const downloadUrl = getPayslipDownloadUrl(payrollId);
        console.log('Opening download URL:', downloadUrl);
        
        // Open in new tab (most reliable)
        window.open(downloadUrl, '_blank');
        
        // Show success message
        showSuccess('Payslip download started!');
        
    } catch (error) {
        console.error('Download error:', error);
        showError('Error: ' + error.message);
    } finally {
        // Hide any loading overlay
        hideLoadingOverlay();
    }
}

// Download function for table row buttons
async function downloadSinglePayslip(payrollId) {
    console.log('Download Single Payslip called for ID:', payrollId);
    
    // Find and update the specific button
    const buttonSelector = `button[onclick*="downloadSinglePayslip('${payrollId}')"]`;
    const buttons = document.querySelectorAll(buttonSelector);
    
    let btn = null;
    let originalText = '';
    
    if (buttons.length > 0) {
        btn = buttons[0];
        originalText = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        btn.disabled = true;
        
        // Restore button after 5 seconds
        setTimeout(() => {
            if (btn) {
                btn.innerHTML = originalText;
                btn.disabled = false;
            }
        }, 5000);
    }
    
    try {
        // Test the route first
        const testResult = await testDownloadRoute(payrollId);
        console.log('Single test result:', testResult);
        
        if (!testResult.success) {
            throw new Error(testResult.error || 'Cannot connect to server');
        }
        
        if (!testResult.downloadable) {
            throw new Error('Payslip not available for download');
        }
        
        // Direct download in new tab
        const downloadUrl = getPayslipDownloadUrl(payrollId);
        window.open(downloadUrl, '_blank');
        
        showSuccess('Payslip download started in new tab!');
        
    } catch (error) {
        console.error('Single download error:', error);
        showError('Error: ' + error.message);
    } finally {
        hideLoadingOverlay();
        
        // Restore button after a short delay
        setTimeout(() => {
            if (btn) {
                btn.innerHTML = originalText;
                btn.disabled = false;
            }
        }, 1000);
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    // Initialize the confirmation modal
    initializeModal();
    
    // Update attendance UI and working time
    updateAttendanceUI();
    setInterval(updateWorkingTime, 30000);
    
    // Add event listener for page visibility
    document.addEventListener('visibilitychange', function() {
        if (document.visibilityState === 'visible') {
            hideLoadingOverlay();
        }
    });

    // Handle Forgot Time form submission
    const forgotForm = document.getElementById('forgot-time-form');
    if (forgotForm) {
        forgotForm.addEventListener('submit', function(event) {
            const forgotType = document.getElementById('forgot-type');
            const forgotReason = document.getElementById('forgot-reason');
            const subjectField = document.getElementById('forgot-subject');
            const messageField = document.getElementById('forgot-message');

            if (!forgotType.value || !forgotReason.value.trim()) {
                event.preventDefault();
                showError('Please select what you forgot and provide your reason.');
                return;
            }

            const typeLabel = forgotType.value;
            const submittedAtPh = new Date().toLocaleString('en-PH', {
                timeZone: 'Asia/Manila',
                year: 'numeric',
                month: 'long',
                day: '2-digit',
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
                hour12: true,
            });
            subjectField.value = `Forgot to ${typeLabel}`;
            messageField.value = `I forgot to ${typeLabel}.\n\nReason: ${forgotReason.value.trim()}\n\nSubmitted at (PH Time): ${submittedAtPh}`;
        });
    }

    // Close forgot-time modal when clicking outside
    const forgotModal = document.getElementById('forgot-time-modal');
    if (forgotModal) {
        forgotModal.addEventListener('click', function(event) {
            if (event.target === forgotModal) {
                closeForgotTimeModal();
            }
        });
    }
});
</script>
@endsection
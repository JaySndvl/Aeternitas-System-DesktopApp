<?php $__env->startSection('title', 'Employee Dashboard'); ?>

<?php
    $user = auth()->user();
    $pageTitle = 'Employee Dashboard';
    $activeRoute = 'dashboard';
?>

<?php $__env->startSection('content'); ?>
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
        <h2 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-2">Welcome back, <?php echo e($stats['employee_name']); ?>!</h2>
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
            <?php if($todayAttendance && $todayAttendance->time_in && !$todayAttendance->time_out): ?>
                <div class="mt-4 pt-4 border-t border-blue-400">
                    <div class="text-lg opacity-90">Working for:</div>
                    <div class="text-2xl font-bold" id="working-time">
                        <?php
                            $timeIn = \Carbon\Carbon::parse($todayAttendance->time_in);
                            $now = \App\Helpers\TimezoneHelper::now();
                            $diffMinutes = $now->diffInMinutes($timeIn);
                            $hours = floor($diffMinutes / 60);
                            $minutes = $diffMinutes % 60;
                            echo "{$hours}h {$minutes}m";
                        ?>
                    </div>
                </div>
            <?php endif; ?>
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
                    <button id="time-in-btn" class="w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors text-sm" onclick="timeIn()">
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
                    <button id="time-out-btn" class="w-full bg-gray-400 text-white font-semibold py-2 px-4 rounded-lg cursor-not-allowed text-sm" disabled onclick="timeOut()">
                        <i class="fas fa-stop mr-2"></i>
                        Clock Out
                    </button>
                </div>
            </div>
        </div>

        <?php if(!$todayAttendance || !$todayAttendance->time_in || $todayAttendance->time_out): ?>
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
        <?php endif; ?>
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
                    <p class="text-2xl font-bold text-gray-900"><?php echo e($stats['employee_id']); ?></p>
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
                    <p class="text-lg font-bold text-gray-900"><?php echo e($stats['position']); ?></p>
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
                    <p class="text-lg font-bold text-gray-900"><?php echo e($stats['department']); ?></p>
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
                    <p class="text-2xl font-bold text-gray-900">₱<?php echo e(number_format($stats['salary'], 2)); ?></p>
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
                    <p class="text-2xl font-bold text-gray-900"><?php echo e($stats['hire_date']->format('M d, Y')); ?></p>
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
                <?php $__empty_1 = true; $__currentLoopData = $yearly_summary; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $summary): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <div>
                        <p class="text-sm font-medium text-gray-900"><?php echo e($summary->year); ?></p>
                        <p class="text-xs text-gray-500"><?php echo e($summary->payroll_count); ?> payrolls</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-medium text-gray-900">₱<?php echo e(number_format($summary->total_net_pay, 2)); ?></p>
                        <p class="text-xs text-gray-500">Total Net Pay</p>
                    </div>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="text-center py-8">
                    <i class="fas fa-chart-line text-gray-400 text-4xl mb-4"></i>
                    <p class="text-gray-500">No payroll data available</p>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-semibold text-gray-900">Quick Actions</h3>
            </div>
            <div class="space-y-4">
                <!-- Time In Button -->
                <?php if(!$todayAttendance || !$todayAttendance->time_in || $todayAttendance->time_out): ?>
                <button id="quick-time-in-btn" onclick="timeIn()" class="w-full flex items-center justify-center px-4 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                    <i class="fas fa-sign-in-alt mr-2"></i>
                    Time In
                </button>
                <?php else: ?>
                <button disabled class="w-full flex items-center justify-center px-4 py-3 bg-gray-400 text-white rounded-lg cursor-not-allowed">
                    <i class="fas fa-check mr-2"></i>
                    Already Clocked In
                </button>
                <?php endif; ?>

                <!-- Time Out Button -->
                <?php if($todayAttendance && $todayAttendance->time_in && !$todayAttendance->time_out): ?>
                <button id="quick-time-out-btn" onclick="timeOut()" class="w-full flex items-center justify-center px-4 py-3 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                    <i class="fas fa-sign-out-alt mr-2"></i>
                    Time Out
                </button>
                <?php elseif($todayAttendance && $todayAttendance->time_out): ?>
                <button disabled class="w-full flex items-center justify-center px-4 py-3 bg-gray-400 text-white rounded-lg cursor-not-allowed">
                    <i class="fas fa-check mr-2"></i>
                    Already Clocked Out
                </button>
                <?php else: ?>
                <button disabled class="w-full flex items-center justify-center px-4 py-3 bg-gray-300 text-gray-500 rounded-lg cursor-not-allowed">
                    <i class="fas fa-sign-out-alt mr-2"></i>
                    Time Out (Clock In First)
                </button>
                <?php endif; ?>

                <button class="w-full flex items-center justify-center px-4 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    <i class="fas fa-download mr-2"></i>
                    Download Pay Slip
                </button>
                <a href="<?php echo e(route('hr.profile')); ?>" class="w-full flex items-center justify-center px-4 py-3 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                    <i class="fas fa-edit mr-2"></i>
                    Update Profile
                </a>
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
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php $__empty_1 = true; $__currentLoopData = $recent_payrolls; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $payroll): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div>
                                <div class="text-sm font-medium text-gray-900"><?php echo e($payroll->pay_period_start->format('M d')); ?> - <?php echo e($payroll->pay_period_end->format('M d, Y')); ?></div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">₱<?php echo e(number_format($payroll->gross_pay, 2)); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">₱<?php echo e(number_format($payroll->gross_pay - $payroll->net_pay, 2)); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">₱<?php echo e(number_format($payroll->net_pay, 2)); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo e($payroll->status === 'processed' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'); ?>">
                                <?php echo e(ucfirst($payroll->status)); ?>

                            </span>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center">
                            <i class="fas fa-money-bill-wave text-gray-400 text-4xl mb-4"></i>
                            <p class="text-gray-500">No payroll records found</p>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Hidden data for JavaScript -->
<div id="attendance-data"
     data-today-attendance='<?php echo json_encode($todayAttendance); ?>'
     data-recent-activity='<?php echo json_encode($recentActivity); ?>'
     style="display: none;"></div>

<script>
// Global variables
let currentStatus = null;
const dataElement = document.getElementById('attendance-data');
let attendanceRecord = dataElement ? JSON.parse(dataElement.getAttribute('data-today-attendance') || 'null') : null;
let recentActivity = dataElement ? JSON.parse(dataElement.getAttribute('data-recent-activity') || '[]') : [];

// Get Philippine Standard Time (UTC+8)
function getPhilippineTime() {
    const now = new Date();
    // Philippine Standard Time is UTC+8 (Asia/Manila timezone)
    return new Date(now.toLocaleString("en-US", {timeZone: "Asia/Manila"}));
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

// Format time for display (without seconds)
function formatTime(dateString) {
    if (!dateString) return null;
    const date = new Date(dateString);
    return format12HourTime(date).slice(0, -3); // Remove seconds
}

// Update working time display
function updateWorkingTime() {
    if (!attendanceRecord || !attendanceRecord.time_in || attendanceRecord.time_out) {
        return;
    }

    const timeIn = new Date(attendanceRecord.time_in);
    const now = getPhilippineTime();
    const diffMs = now - timeIn;
    
    // Ensure we don't show negative time
    if (diffMs < 0) {
        return;
    }
    
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
            if (timeOutCard) {
                timeOutCard.classList.remove('opacity-50');
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

// Time In function
async function timeIn() {
    const btn = document.getElementById('time-in-btn');
    const originalText = btn.innerHTML;

    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Processing...';

    try {
        const response = await fetch('<?php echo e(route("attendance.time-in")); ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>'
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

// Time Out function
async function timeOut() {
    const btn = document.getElementById('time-out-btn');
    const originalText = btn.innerHTML;

    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Processing...';

    try {
        const response = await fetch('<?php echo e(route("attendance.time-out")); ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>'
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
    }, 3000);
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    updateAttendanceUI();
    // Update working time every 30 seconds for more responsive updates
    setInterval(updateWorkingTime, 30000);
});
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.dashboard-base', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\caior\Documents\Mica files\Aeternitas-System-V2\resources\views/dashboards/employee.blade.php ENDPATH**/ ?>
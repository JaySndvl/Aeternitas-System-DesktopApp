<?php $__env->startSection('title', 'Attendance Settings'); ?>

<?php $__env->startSection('content'); ?>
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Attendance Settings</h1>
            <p class="mt-1 text-sm text-gray-600">Configure attendance policies and rules</p>
        </div>
        <div class="mt-4 sm:mt-0">
            <button class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-lg font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                <i class="fas fa-save mr-2"></i>
                Save Settings
            </button>
        </div>
    </div>

    <!-- General Settings -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">General Settings</h3>
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div>
                <label for="workHours" class="block text-sm font-medium text-gray-700 mb-2">Daily Work Hours</label>
                <input type="number" id="workHours" value="8" min="1" max="24" 
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors">
                <p class="mt-1 text-xs text-gray-500">Standard working hours per day</p>
            </div>
            <div>
                <label for="breakTime" class="block text-sm font-medium text-gray-700 mb-2">Break Time (minutes)</label>
                <input type="number" id="breakTime" value="60" min="0" max="480" 
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors">
                <p class="mt-1 text-xs text-gray-500">Total break time allowed per day</p>
            </div>
            <div>
                <label for="gracePeriod" class="block text-sm font-medium text-gray-700 mb-2">Grace Period (minutes)</label>
                <input type="number" id="gracePeriod" value="15" min="0" max="60" 
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors">
                <p class="mt-1 text-xs text-gray-500">Late arrival tolerance period</p>
            </div>
            <div>
                <label for="overtimeThreshold" class="block text-sm font-medium text-gray-700 mb-2">Overtime Threshold (hours)</label>
                <input type="number" id="overtimeThreshold" value="8" min="1" max="24" step="0.5" 
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors">
                <p class="mt-1 text-xs text-gray-500">Hours after which overtime applies</p>
            </div>
        </div>
    </div>

    <!-- Work Schedule -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Work Schedule</h3>
        <div class="space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Monday</label>
                    <div class="flex space-x-2">
                        <input type="time" value="08:00" class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors">
                        <input type="time" value="17:00" class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tuesday</label>
                    <div class="flex space-x-2">
                        <input type="time" value="08:00" class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors">
                        <input type="time" value="17:00" class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Wednesday</label>
                    <div class="flex space-x-2">
                        <input type="time" value="08:00" class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors">
                        <input type="time" value="17:00" class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Thursday</label>
                    <div class="flex space-x-2">
                        <input type="time" value="08:00" class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors">
                        <input type="time" value="17:00" class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Friday</label>
                    <div class="flex space-x-2">
                        <input type="time" value="08:00" class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors">
                        <input type="time" value="17:00" class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Saturday</label>
                    <div class="flex space-x-2">
                        <input type="time" value="09:00" class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors">
                        <input type="time" value="13:00" class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Sunday</label>
                    <div class="flex space-x-2">
                        <input type="time" value="" class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors" disabled>
                        <input type="time" value="" class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors" disabled>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Overtime Settings -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Overtime Settings</h3>
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div>
                <label for="overtimeRate" class="block text-sm font-medium text-gray-700 mb-2">Overtime Rate Multiplier</label>
                <input type="number" id="overtimeRate" value="1.5" min="1" max="3" step="0.1" 
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors">
                <p class="mt-1 text-xs text-gray-500">Rate multiplier for overtime hours (e.g., 1.5 = 150%)</p>
            </div>
            <div>
                <label for="maxOvertime" class="block text-sm font-medium text-gray-700 mb-2">Maximum Overtime (hours/day)</label>
                <input type="number" id="maxOvertime" value="4" min="1" max="12" 
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors">
                <p class="mt-1 text-xs text-gray-500">Maximum overtime hours allowed per day</p>
            </div>
            <div>
                <label class="flex items-center">
                    <input type="checkbox" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                    <span class="ml-2 text-sm text-gray-700">Require Overtime Approval</span>
                </label>
                <p class="mt-1 text-xs text-gray-500">Overtime must be approved by supervisor</p>
            </div>
            <div>
                <label class="flex items-center">
                    <input type="checkbox" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50" checked>
                    <span class="ml-2 text-sm text-gray-700">Auto-calculate Overtime</span>
                </label>
                <p class="mt-1 text-xs text-gray-500">Automatically calculate overtime hours</p>
            </div>
        </div>
    </div>

    <!-- Leave Settings -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Leave Settings</h3>
        <div class="space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div>
                    <label for="vacationDays" class="block text-sm font-medium text-gray-700 mb-2">Vacation Leave (days/year)</label>
                    <input type="number" id="vacationDays" value="15" min="0" max="365" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors">
                </div>
                <div>
                    <label for="sickDays" class="block text-sm font-medium text-gray-700 mb-2">Sick Leave (days/year)</label>
                    <input type="number" id="sickDays" value="10" min="0" max="365" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors">
                </div>
                <div>
                    <label for="personalDays" class="block text-sm font-medium text-gray-700 mb-2">Personal Leave (days/year)</label>
                    <input type="number" id="personalDays" value="5" min="0" max="365" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors">
                </div>
                <div>
                    <label for="emergencyDays" class="block text-sm font-medium text-gray-700 mb-2">Emergency Leave (days/year)</label>
                    <input type="number" id="emergencyDays" value="3" min="0" max="365" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors">
                </div>
            </div>
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div>
                    <label class="flex items-center">
                        <input type="checkbox" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50" checked>
                        <span class="ml-2 text-sm text-gray-700">Require Leave Approval</span>
                    </label>
                    <p class="mt-1 text-xs text-gray-500">Leave requests must be approved by supervisor</p>
                </div>
                <div>
                    <label class="flex items-center">
                        <input type="checkbox" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                        <span class="ml-2 text-sm text-gray-700">Allow Negative Leave Balance</span>
                    </label>
                    <p class="mt-1 text-xs text-gray-500">Allow employees to take leave beyond their balance</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Notification Settings -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Notification Settings</h3>
        <div class="space-y-4">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div>
                    <label class="flex items-center">
                        <input type="checkbox" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50" checked>
                        <span class="ml-2 text-sm text-gray-700">Late Arrival Notifications</span>
                    </label>
                    <p class="mt-1 text-xs text-gray-500">Notify supervisors when employees arrive late</p>
                </div>
                <div>
                    <label class="flex items-center">
                        <input type="checkbox" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50" checked>
                        <span class="ml-2 text-sm text-gray-700">Absence Notifications</span>
                    </label>
                    <p class="mt-1 text-xs text-gray-500">Notify supervisors when employees are absent</p>
                </div>
                <div>
                    <label class="flex items-center">
                        <input type="checkbox" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                        <span class="ml-2 text-sm text-gray-700">Overtime Notifications</span>
                    </label>
                    <p class="mt-1 text-xs text-gray-500">Notify supervisors when employees work overtime</p>
                </div>
                <div>
                    <label class="flex items-center">
                        <input type="checkbox" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50" checked>
                        <span class="ml-2 text-sm text-gray-700">Leave Request Notifications</span>
                    </label>
                    <p class="mt-1 text-xs text-gray-500">Notify supervisors of new leave requests</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Security Settings -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Security Settings</h3>
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div>
                <label class="flex items-center">
                    <input type="checkbox" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50" checked>
                    <span class="ml-2 text-sm text-gray-700">Require Location Verification</span>
                </label>
                <p class="mt-1 text-xs text-gray-500">Verify employee location when clocking in/out</p>
            </div>
            <div>
                <label class="flex items-center">
                    <input type="checkbox" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                    <span class="ml-2 text-sm text-gray-700">Require Photo Verification</span>
                </label>
                <p class="mt-1 text-xs text-gray-500">Require photo when clocking in/out</p>
            </div>
            <div>
                <label class="flex items-center">
                    <input type="checkbox" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50" checked>
                    <span class="ml-2 text-sm text-gray-700">Prevent Multiple Clock-ins</span>
                </label>
                <p class="mt-1 text-xs text-gray-500">Prevent employees from clocking in multiple times</p>
            </div>
            <div>
                <label class="flex items-center">
                    <input type="checkbox" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                    <span class="ml-2 text-sm text-gray-700">Enable IP Restrictions</span>
                </label>
                <p class="mt-1 text-xs text-gray-500">Restrict clock-in/out to specific IP addresses</p>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.dashboard-base', ['user' => $user, 'activeRoute' => 'attendance.settings'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\caior\Documents\Mica files\Aeternitas-System-V2\resources\views/attendance/settings.blade.php ENDPATH**/ ?>
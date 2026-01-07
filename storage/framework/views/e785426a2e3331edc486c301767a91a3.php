<?php
    $userPreferences = session('user_preferences', [
        'timezone' => 'Asia/Manila',
        'date_format' => 'MM/DD/YYYY',
        'dark_mode' => false,
        'email_notifications' => true,
        'auto_save' => true,
    ]);
?>

<?php $__env->startSection('title', 'HR Settings'); ?>

<?php $__env->startSection('content'); ?>
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 flex items-center">
                <i class="fas fa-cog mr-3 text-blue-600"></i>
                HR Settings
            </h1>
            <p class="mt-1 text-sm text-gray-600">Manage your HR system preferences and configurations</p>
        </div>
    </div>

    <!-- Settings Tabs -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <div class="border-b border-gray-200">
            <nav class="flex space-x-8 px-6" aria-label="Tabs">
                <button class="py-4 px-1 border-b-2 border-blue-500 font-medium text-sm text-blue-600" 
                        onclick="showTab('general')" id="general-tab">
                    <i class="fas fa-sliders-h mr-2"></i>
                    General
                </button>
                <button class="py-4 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300" 
                        onclick="showTab('notifications')" id="notifications-tab">
                    <i class="fas fa-bell mr-2"></i>
                    Notifications
                </button>
                <button class="py-4 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300" 
                        onclick="showTab('security')" id="security-tab">
                    <i class="fas fa-shield-alt mr-2"></i>
                    Security
                </button>
                <button class="py-4 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300" 
                        onclick="showTab('system')" id="system-tab">
                    <i class="fas fa-server mr-2"></i>
                    System
                </button>
            </nav>
        </div>

        <!-- General Settings Tab -->
        <div id="general-content" class="tab-content p-6">
            <form method="POST" action="<?php echo e(route('hr.settings.update')); ?>" class="space-y-6">
                <?php echo csrf_field(); ?>
                <?php echo method_field('PUT'); ?>
                <!-- Personal Information -->
                <div class="bg-gray-50 rounded-lg p-4">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Personal Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">First Name</label>
                            <input type="text" name="first_name" value="<?php echo e($employee->first_name ?? ''); ?>" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Last Name</label>
                            <input type="text" name="last_name" value="<?php echo e($employee->last_name ?? ''); ?>" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
                            <input type="email" name="email" value="<?php echo e($user->email); ?>" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Phone Number</label>
                            <input type="tel" name="phone" value="<?php echo e($employee->phone ?? ''); ?>" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Department</label>
                            <select name="department_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Select Department</option>
                                <?php $__currentLoopData = $departments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $department): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($department->id); ?>" 
                                            <?php echo e($employee && $employee->department_id == $department->id ? 'selected' : ''); ?>>
                                        <?php echo e($department->name); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- System Preferences -->
                <div class="bg-gray-50 rounded-lg p-4">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">System Preferences</h3>
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <label class="text-sm font-medium text-gray-700">Dark Mode</label>
                                <p class="text-xs text-gray-500">Switch to dark theme</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="dark_mode" value="1" <?php echo e($userPreferences['dark_mode'] ? 'checked' : ''); ?> class="sr-only peer">
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                            </label>
                        </div>
                        
                        <div class="flex items-center justify-between">
                            <div>
                                <label class="text-sm font-medium text-gray-700">Email Notifications</label>
                                <p class="text-xs text-gray-500">Receive email updates</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="email_notifications" value="1" <?php echo e($userPreferences['email_notifications'] ? 'checked' : ''); ?> class="sr-only peer">
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                            </label>
                        </div>

                        <div class="flex items-center justify-between">
                            <div>
                                <label class="text-sm font-medium text-gray-700">Auto-save Forms</label>
                                <p class="text-xs text-gray-500">Automatically save form data</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="auto_save" value="1" <?php echo e($userPreferences['auto_save'] ? 'checked' : ''); ?> class="sr-only peer">
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Timezone Settings -->
                <div class="bg-gray-50 rounded-lg p-4">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Timezone & Localization</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Timezone</label>
                            <select name="timezone" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="Asia/Manila" <?php echo e($userPreferences['timezone'] == 'Asia/Manila' ? 'selected' : ''); ?>>Asia/Manila (PHT)</option>
                                <option value="UTC" <?php echo e($userPreferences['timezone'] == 'UTC' ? 'selected' : ''); ?>>UTC</option>
                                <option value="America/New_York" <?php echo e($userPreferences['timezone'] == 'America/New_York' ? 'selected' : ''); ?>>America/New_York</option>
                                <option value="Europe/London" <?php echo e($userPreferences['timezone'] == 'Europe/London' ? 'selected' : ''); ?>>Europe/London</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Date Format</label>
                            <select name="date_format" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="MM/DD/YYYY" <?php echo e($userPreferences['date_format'] == 'MM/DD/YYYY' ? 'selected' : ''); ?>>MM/DD/YYYY</option>
                                <option value="DD/MM/YYYY" <?php echo e($userPreferences['date_format'] == 'DD/MM/YYYY' ? 'selected' : ''); ?>>DD/MM/YYYY</option>
                                <option value="YYYY-MM-DD" <?php echo e($userPreferences['date_format'] == 'YYYY-MM-DD' ? 'selected' : ''); ?>>YYYY-MM-DD</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end space-x-3">
                    <button type="button" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                        <i class="fas fa-save mr-2"></i>
                        Save Changes
                    </button>
                </div>
            </form>
        </div>

        <!-- Notifications Settings Tab -->
        <div id="notifications-content" class="tab-content p-6 hidden">
            <div class="space-y-6">
                <h3 class="text-lg font-medium text-gray-900">Notification Preferences</h3>
                
                <div class="space-y-4">
                    <div class="bg-gray-50 rounded-lg p-4">
                        <h4 class="font-medium text-gray-900 mb-3">Email Notifications</h4>
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-700">New employee registrations</span>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" checked class="sr-only peer">
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                </label>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-700">Payroll processing alerts</span>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" checked class="sr-only peer">
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                </label>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-700">Leave request approvals</span>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" class="sr-only peer">
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gray-50 rounded-lg p-4">
                        <h4 class="font-medium text-gray-900 mb-3">System Alerts</h4>
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-700">Attendance exceptions</span>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" checked class="sr-only peer">
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                </label>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-700">Overtime approvals</span>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" checked class="sr-only peer">
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Security Settings Tab -->
        <div id="security-content" class="tab-content p-6 hidden">
            <div class="space-y-6">
                <h3 class="text-lg font-medium text-gray-900">Security Settings</h3>
                
                <div class="bg-gray-50 rounded-lg p-6">
                    <div class="flex items-center mb-4">
                        <i class="fas fa-key text-blue-600 mr-3"></i>
                        <h4 class="font-medium text-gray-900">Change Password</h4>
                    </div>
                    
                    <?php if(session('success')): ?>
                        <div class="mb-4 p-3 bg-green-100 border border-green-400 text-green-700 rounded-lg">
                            <i class="fas fa-check-circle mr-2"></i>
                            <?php echo e(session('success')); ?>

                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="<?php echo e(route('hr.settings.password')); ?>" class="space-y-4">
                        <?php echo csrf_field(); ?>
                        <?php echo method_field('PUT'); ?>
                        
                        <div class="grid grid-cols-1 md:grid-cols-1 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-lock mr-1"></i>
                                    Current Password
                                </label>
                                <input type="password" 
                                       name="current_password" 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 <?php $__errorArgs = ['current_password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-500 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                       placeholder="Enter your current password"
                                       required>
                                <?php $__errorArgs = ['current_password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <p class="text-red-500 text-xs mt-1 flex items-center">
                                        <i class="fas fa-exclamation-circle mr-1"></i>
                                        <?php echo e($message); ?>

                                    </p>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-key mr-1"></i>
                                    New Password
                                </label>
                                <input type="password" 
                                       name="password" 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-500 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                       placeholder="Enter your new password"
                                       required>
                                <p class="text-xs text-gray-500 mt-1">Password must be at least 8 characters long</p>
                                <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <p class="text-red-500 text-xs mt-1 flex items-center">
                                        <i class="fas fa-exclamation-circle mr-1"></i>
                                        <?php echo e($message); ?>

                                    </p>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-check-circle mr-1"></i>
                                    Confirm New Password
                                </label>
                                <input type="password" 
                                       name="password_confirmation" 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                       placeholder="Confirm your new password"
                                       required>
                            </div>
                        </div>
                        
                        <div class="flex items-center justify-between pt-4 border-t border-gray-200">
                            <div class="text-sm text-gray-600">
                                <i class="fas fa-info-circle mr-1"></i>
                                Password requirements: At least 8 characters, must be different from current password
                            </div>
                            <button type="submit" 
                                    class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                                <i class="fas fa-save mr-2"></i>
                                Update Password
                            </button>
                        </div>
                    </form>
                </div>

                <div class="bg-gray-50 rounded-lg p-4">
                    <h4 class="font-medium text-gray-900 mb-3">Two-Factor Authentication</h4>
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-700">Enable 2FA for additional security</p>
                            <p class="text-xs text-gray-500">Use your mobile device to generate verification codes</p>
                        </div>
                        <button class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                            <i class="fas fa-shield-alt mr-2"></i>
                            Enable 2FA
                        </button>
                    </div>
                </div>

                <div class="bg-gray-50 rounded-lg p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center">
                            <i class="fas fa-shield-alt text-blue-600 mr-3"></i>
                            <h4 class="font-medium text-gray-900">Login Sessions</h4>
                        </div>
                        <button id="terminate-all-sessions" class="text-sm text-red-600 hover:text-red-700 font-medium">
                            <i class="fas fa-sign-out-alt mr-1"></i>
                            End All Other Sessions
                        </button>
                    </div>
                    
                    <div id="sessions-container" class="space-y-3">
                        <!-- Sessions will be loaded here via JavaScript -->
                        <div class="flex items-center justify-center py-8">
                            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                            <span class="ml-2 text-gray-600">Loading sessions...</span>
                        </div>
                    </div>
                    
                    <div class="mt-4 p-3 bg-blue-50 rounded-lg">
                        <div class="flex items-start">
                            <i class="fas fa-info-circle text-blue-600 mr-2 mt-0.5"></i>
                            <div class="text-sm text-blue-800">
                                <p class="font-medium">Security Tips:</p>
                                <ul class="mt-1 space-y-1 text-xs">
                                    <li>• Review your active sessions regularly</li>
                                    <li>• End sessions from devices you no longer use</li>
                                    <li>• If you notice suspicious activity, end all sessions immediately</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- System Settings Tab -->
        <div id="system-content" class="tab-content p-6 hidden">
            <div class="space-y-6">
                <h3 class="text-lg font-medium text-gray-900">System Configuration</h3>
                
                <div class="bg-gray-50 rounded-lg p-4">
                    <h4 class="font-medium text-gray-900 mb-3">Data Management</h4>
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-900">Export Employee Data</p>
                                <p class="text-xs text-gray-500">Download all employee information as CSV</p>
                            </div>
                            <button class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                                <i class="fas fa-download mr-2"></i>
                                Export
                            </button>
                        </div>
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-900">Backup System Data</p>
                                <p class="text-xs text-gray-500">Create a backup of all system data</p>
                            </div>
                            <button class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                                <i class="fas fa-database mr-2"></i>
                                Backup
                            </button>
                        </div>
                    </div>
                </div>

                <div class="bg-gray-50 rounded-lg p-4">
                    <h4 class="font-medium text-gray-900 mb-3">System Information</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="bg-white p-3 rounded-lg border">
                            <p class="text-xs text-gray-500">System Version</p>
                            <p class="text-sm font-medium text-gray-900">v2.1.0</p>
                        </div>
                        <div class="bg-white p-3 rounded-lg border">
                            <p class="text-xs text-gray-500">Last Updated</p>
                            <p class="text-sm font-medium text-gray-900">Oct 20, 2025</p>
                        </div>
                        <div class="bg-white p-3 rounded-lg border">
                            <p class="text-xs text-gray-500">Database Size</p>
                            <p class="text-sm font-medium text-gray-900">2.4 GB</p>
                        </div>
                        <div class="bg-white p-3 rounded-lg border">
                            <p class="text-xs text-gray-500">Active Users</p>
                            <p class="text-sm font-medium text-gray-900">156</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function showTab(tabName) {
    // Hide all tab contents
    const contents = document.querySelectorAll('.tab-content');
    contents.forEach(content => content.classList.add('hidden'));
    
    // Remove active class from all tabs
    const tabs = document.querySelectorAll('[id$="-tab"]');
    tabs.forEach(tab => {
        tab.classList.remove('border-blue-500', 'text-blue-600');
        tab.classList.add('border-transparent', 'text-gray-500');
    });
    
    // Show selected tab content
    document.getElementById(tabName + '-content').classList.remove('hidden');
    
    // Add active class to selected tab
    const activeTab = document.getElementById(tabName + '-tab');
    activeTab.classList.remove('border-transparent', 'text-gray-500');
    activeTab.classList.add('border-blue-500', 'text-blue-600');
}

// Password change functionality
document.addEventListener('DOMContentLoaded', function() {
    const passwordForm = document.querySelector('form[action*="password"]');
    if (passwordForm) {
        const currentPassword = passwordForm.querySelector('input[name="current_password"]');
        const newPassword = passwordForm.querySelector('input[name="password"]');
        const confirmPassword = passwordForm.querySelector('input[name="password_confirmation"]');
        const submitButton = passwordForm.querySelector('button[type="submit"]');
        
        // Real-time password validation
        function validatePasswords() {
            const newPass = newPassword.value;
            const confirmPass = confirmPassword.value;
            
            // Check if passwords match
            if (confirmPass && newPass !== confirmPass) {
                confirmPassword.setCustomValidity('Passwords do not match');
                confirmPassword.classList.add('border-red-500');
            } else {
                confirmPassword.setCustomValidity('');
                confirmPassword.classList.remove('border-red-500');
            }
            
            // Check minimum length
            if (newPass && newPass.length < 8) {
                newPassword.setCustomValidity('Password must be at least 8 characters');
                newPassword.classList.add('border-red-500');
            } else {
                newPassword.setCustomValidity('');
                newPassword.classList.remove('border-red-500');
            }
            
            // Enable/disable submit button
            const isValid = currentPassword.value && 
                           newPass.length >= 8 && 
                           newPass === confirmPass && 
                           newPass !== currentPassword.value;
            
            submitButton.disabled = !isValid;
            submitButton.classList.toggle('opacity-50', !isValid);
            submitButton.classList.toggle('cursor-not-allowed', !isValid);
        }
        
        // Add event listeners
        [currentPassword, newPassword, confirmPassword].forEach(input => {
            input.addEventListener('input', validatePasswords);
        });
        
        // Initial validation
        validatePasswords();
    }
    
    // Session management functionality
    let sessions = [];
    let currentSessionId = '';
    
    // Load sessions when Security tab is shown
    function loadSessions() {
        fetch('<?php echo e(route("hr.sessions")); ?>')
            .then(response => response.json())
            .then(data => {
                sessions = data.sessions;
                currentSessionId = data.current_session_id;
                renderSessions();
            })
            .catch(error => {
                console.error('Error loading sessions:', error);
                document.getElementById('sessions-container').innerHTML = 
                    '<div class="text-center py-4 text-red-600">Error loading sessions</div>';
            });
    }
    
    // Render sessions in the UI
    function renderSessions() {
        const container = document.getElementById('sessions-container');
        
        if (sessions.length === 0) {
            container.innerHTML = '<div class="text-center py-4 text-gray-500">No active sessions found</div>';
            return;
        }
        
        const sessionsHtml = sessions.map(session => {
            const isCurrent = session.session_id === currentSessionId;
            const deviceIcon = getDeviceIcon(session.device_type);
            const browserIcon = getBrowserIcon(session.browser);
            
            return `
                <div class="flex items-center justify-between p-4 bg-white rounded-lg border ${isCurrent ? 'border-green-200 bg-green-50' : ''}">
                    <div class="flex items-center">
                        <i class="${deviceIcon} text-gray-400 mr-3"></i>
                        <div>
                            <div class="flex items-center">
                                <p class="text-sm font-medium text-gray-900">${session.device_type.charAt(0).toUpperCase() + session.device_type.slice(1)} Session</p>
                                ${isCurrent ? '<span class="ml-2 px-2 py-1 bg-green-100 text-green-800 text-xs rounded-full">Current</span>' : ''}
                            </div>
                            <p class="text-xs text-gray-500">
                                <i class="${browserIcon} mr-1"></i>
                                ${session.browser} • ${session.os} • ${session.location}
                            </p>
                            <p class="text-xs text-gray-400">
                                Last active: ${formatLastActivity(session.last_activity)}
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-2">
                        <span class="text-xs text-gray-500">${session.ip_address}</span>
                        ${!isCurrent ? `<button onclick="terminateSession('${session.session_id}')" class="text-xs text-red-600 hover:text-red-700 font-medium">
                            <i class="fas fa-times mr-1"></i>End Session
                        </button>` : ''}
                    </div>
                </div>
            `;
        }).join('');
        
        container.innerHTML = sessionsHtml;
    }
    
    // Get device icon
    function getDeviceIcon(deviceType) {
        const icons = {
            'mobile': 'fas fa-mobile-alt',
            'tablet': 'fas fa-tablet-alt',
            'desktop': 'fas fa-desktop'
        };
        return icons[deviceType] || 'fas fa-laptop';
    }
    
    // Get browser icon
    function getBrowserIcon(browser) {
        const icons = {
            'Chrome': 'fab fa-chrome',
            'Firefox': 'fab fa-firefox-browser',
            'Safari': 'fab fa-safari',
            'Edge': 'fab fa-edge',
            'Opera': 'fab fa-opera'
        };
        return icons[browser] || 'fas fa-globe';
    }
    
    // Format last activity
    function formatLastActivity(lastActivity) {
        const date = new Date(lastActivity);
        const now = new Date();
        const diffMs = now - date;
        const diffMins = Math.floor(diffMs / 60000);
        
        if (diffMins < 1) return 'Just now';
        if (diffMins < 60) return `${diffMins}m ago`;
        if (diffMins < 1440) return `${Math.floor(diffMins / 60)}h ago`;
        return `${Math.floor(diffMins / 1440)}d ago`;
    }
    
    // Terminate a specific session
    function terminateSession(sessionId) {
        if (confirm('Are you sure you want to end this session?')) {
            const url = `/hr/sessions/${sessionId}`;
            
            fetch(url, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification(data.message, 'success');
                    loadSessions(); // Reload sessions
                } else {
                    showNotification(data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error terminating session:', error);
                showNotification('Error terminating session', 'error');
            });
        }
    }
    
    // Terminate all other sessions
    document.getElementById('terminate-all-sessions').addEventListener('click', function() {
        if (confirm('Are you sure you want to end all other sessions? This will log you out of all other devices.')) {
            fetch('<?php echo e(route("hr.sessions.terminate-all")); ?>', {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification(data.message, 'success');
                    loadSessions(); // Reload sessions
                } else {
                    showNotification(data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error terminating sessions:', error);
                showNotification('Error terminating sessions', 'error');
            });
        }
    });
    
    // Show notification
    function showNotification(message, type) {
        const notification = document.createElement('div');
        notification.className = `fixed top-4 right-4 p-4 rounded-lg shadow-lg z-50 ${
            type === 'success' ? 'bg-green-100 text-green-800 border border-green-200' : 'bg-red-100 text-red-800 border border-red-200'
        }`;
        notification.innerHTML = `
            <div class="flex items-center">
                <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'} mr-2"></i>
                ${message}
            </div>
        `;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.remove();
        }, 5000);
    }
    
    // Load sessions when Security tab is clicked
    document.getElementById('security-tab').addEventListener('click', function() {
        setTimeout(loadSessions, 100); // Small delay to ensure tab is visible
    });
});
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.dashboard-base', ['user' => $user, 'activeRoute' => 'hr.settings'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\caior\Documents\Mica files\Aeternitas-System-V2\resources\views/hr/settings.blade.php ENDPATH**/ ?>
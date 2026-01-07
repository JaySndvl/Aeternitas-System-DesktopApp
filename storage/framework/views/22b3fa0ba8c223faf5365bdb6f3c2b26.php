<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['title', 'user']));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter((['title', 'user']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    // Check if employee is currently timed in (for employee role users)
    $isCurrentlyTimedIn = false;
    $todayAttendance = null;
    if ($user->role === 'employee' && $user->employee) {
        $todayAttendance = $user->employee->getTodayAttendance();
        $isCurrentlyTimedIn = $todayAttendance && $todayAttendance->time_in && !$todayAttendance->time_out;
    }
?>

<header class="bg-gradient-to-r from-white/95 to-blue-50/95 backdrop-blur-sm shadow-sm border-b border-gray-200 sticky top-0 z-40">
    <div class="flex items-center justify-between h-16 sm:h-20 px-3 sm:px-4 lg:px-8">
        <div class="flex items-center flex-1 min-w-0">
            <button class="lg:hidden text-gray-500 hover:text-gray-700 p-2 rounded-lg hover:bg-gray-100 transition-colors flex-shrink-0" onclick="toggleSidebar()">
                <i class="fas fa-bars text-lg sm:text-xl"></i>
            </button>
            <div class="ml-2 sm:ml-4 lg:ml-0 min-w-0 flex-1">
                <h1 class="text-lg sm:text-xl lg:text-2xl font-bold text-gray-900 truncate"><?php echo e($title); ?></h1>
                <div class="flex flex-col sm:flex-row sm:items-center space-y-1 sm:space-y-0 sm:space-x-4">
                    <p class="text-xs sm:text-sm text-gray-500 truncate">Welcome back, <?php echo e($user->full_name); ?></p>
                    <div class="flex items-center text-xs text-gray-400">
                        <i class="fas fa-clock mr-1"></i>
                        <span id="current-time-desktop" class="hidden sm:inline"><?php echo e(\App\Helpers\TimezoneHelper::now()->format('M d, Y g:i A')); ?></span>
                        <span id="current-time-mobile" class="sm:hidden"><?php echo e(\App\Helpers\TimezoneHelper::now()->format('g:i A')); ?></span>
                        <span class="ml-1 text-blue-600">PHT</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="flex items-center space-x-1 sm:space-x-2 lg:space-x-3 flex-shrink-0">
            <?php
                $currentCompany = \App\Helpers\CompanyHelper::getCurrentCompany();
                $allCompanies = \App\Models\Company::where('is_active', true)->orderBy('name')->get();
            ?>
            
            <!-- Company Selector - Hidden on mobile, shown on tablet+ -->
            <div class="hidden md:block relative" x-data="{ open: false }">
                <button @click="open = !open" class="flex items-center px-4 py-2 bg-gradient-to-r from-blue-50 to-blue-100 border border-blue-200 hover:border-blue-300 rounded-lg transition-all group">
                    <i class="fas fa-building text-blue-600 mr-2"></i>
                    <span class="text-sm font-medium text-blue-800 group-hover:text-blue-900">
                        <?php echo e($currentCompany ? $currentCompany->name : 'No Company'); ?>

                    </span>
                    <span class="ml-2 px-2 py-0.5 bg-blue-600 text-white text-xs font-medium rounded-full">Active</span>
                    <i class="fas fa-chevron-down ml-2 text-blue-600 text-xs transition-transform" :class="{ 'rotate-180': open }"></i>
                </button>
                
                <!-- Dropdown Menu -->
                <div x-show="open" 
                     @click.away="open = false"
                     x-transition:enter="transition ease-out duration-100"
                     x-transition:enter-start="transform opacity-0 scale-95"
                     x-transition:enter-end="transform opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-75"
                     x-transition:leave-start="transform opacity-100 scale-100"
                     x-transition:leave-end="transform opacity-0 scale-95"
                     class="absolute right-0 mt-2 w-64 bg-white rounded-lg shadow-lg border border-gray-200 py-2 z-50 max-h-96 overflow-y-auto">
                    
                    <!-- Dropdown Header -->
                    <div class="px-4 py-2 border-b border-gray-100">
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Select Company</p>
                    </div>
                    
                    <!-- Company List -->
                    <?php $__currentLoopData = $allCompanies; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $company): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <form method="POST" action="<?php echo e(route('companies.switch')); ?>" class="block" 
                              onsubmit="handleCompanySwitch(event, '<?php echo e($company->name); ?>')">
                            <?php echo csrf_field(); ?>
                            <input type="hidden" name="company_id" value="<?php echo e($company->id); ?>">
                            <button type="submit" class="w-full flex items-center px-4 py-3 text-left hover:bg-gray-50 transition-colors <?php echo e($currentCompany && $company->id === $currentCompany->id ? 'bg-blue-50 cursor-not-allowed' : 'cursor-pointer'); ?>">
                                <div class="flex-1">
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm font-medium text-gray-900"><?php echo e($company->name); ?></span>
                                        <?php if($currentCompany && $company->id === $currentCompany->id): ?>
                                            <span class="px-2 py-0.5 bg-blue-600 text-white text-xs font-medium rounded-full">
                                                <i class="fas fa-check-circle mr-1"></i>Active
                                            </span>
                                        <?php else: ?>
                                            <span class="px-2 py-0.5 bg-gray-100 text-gray-600 text-xs font-medium rounded-full">Switch</span>
                                        <?php endif; ?>
                                    </div>
                                    <?php if($company->code): ?>
                                        <p class="text-xs text-gray-500 mt-0.5"><?php echo e($company->code); ?></p>
                                    <?php endif; ?>
                                </div>
                            </button>
                        </form>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
            
            <!-- Success/Error Messages -->
            <?php if(session('success')): ?>
                <div class="fixed top-4 right-4 bg-green-500 text-white px-4 py-2 rounded-lg shadow-lg z-50" 
                     x-data="{ show: true }" 
                     x-show="show" 
                     x-init="setTimeout(() => show = false, 3000)">
                    <div class="flex items-center">
                        <i class="fas fa-check-circle mr-2"></i>
                        <?php echo e(session('success')); ?>

                    </div>
                </div>
            <?php endif; ?>
            
            <?php if(session('error')): ?>
                <div class="fixed top-4 right-4 bg-red-500 text-white px-4 py-2 rounded-lg shadow-lg z-50" 
                     x-data="{ show: true }" 
                     x-show="show" 
                     x-init="setTimeout(() => show = false, 3000)">
                    <div class="flex items-center">
                        <i class="fas fa-exclamation-circle mr-2"></i>
                        <?php echo e(session('error')); ?>

                    </div>
                </div>
            <?php endif; ?>
            
            <!-- Notifications -->
            <button class="relative p-1.5 sm:p-2 text-gray-500 hover:text-gray-700 rounded-lg hover:bg-gray-100 transition-colors">
                <i class="fas fa-bell text-lg sm:text-xl"></i>
                <span class="absolute -top-0.5 -right-0.5 sm:-top-1 sm:-right-1 block h-4 w-4 sm:h-5 sm:w-5 rounded-full bg-red-500 text-white text-xs flex items-center justify-center">3</span>
            </button>
            
            <!-- Messages - Hidden on small mobile -->
            <button class="relative p-1.5 sm:p-2 text-gray-500 hover:text-gray-700 rounded-lg hover:bg-gray-100 transition-colors hidden sm:block">
                <i class="fas fa-envelope text-lg sm:text-xl"></i>
                <span class="absolute -top-0.5 -right-0.5 sm:-top-1 sm:-right-1 block h-4 w-4 sm:h-5 sm:w-5 rounded-full bg-blue-500 text-white text-xs flex items-center justify-center">2</span>
            </button>
            
            <!-- User Menu Dropdown -->
            <div class="relative" x-data="{ open: false }">
                <button @click="open = !open" class="flex items-center space-x-1 sm:space-x-2 p-1.5 sm:p-2 rounded-lg hover:bg-gray-100 transition-colors">
                    <div class="w-6 h-6 sm:w-8 sm:h-8 bg-gradient-to-r from-blue-500 to-blue-600 rounded-full flex items-center justify-center">
                        <i class="fas fa-user text-white text-xs sm:text-sm"></i>
                    </div>
                    <span class="hidden lg:block text-sm font-medium text-gray-700"><?php echo e($user->full_name); ?></span>
                    <i class="fas fa-chevron-down text-gray-400 text-xs hidden sm:block" :class="{ 'rotate-180': open }"></i>
                </button>
                
                <!-- Dropdown Menu -->
                <div x-show="open" 
                     @click.away="open = false"
                     x-transition:enter="transition ease-out duration-100"
                     x-transition:enter-start="transform opacity-0 scale-95"
                     x-transition:enter-end="transform opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-75"
                     x-transition:leave-start="transform opacity-100 scale-100"
                     x-transition:leave-end="transform opacity-0 scale-95"
                     class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 py-1 z-50">
                    
                    <!-- User Info -->
                    <div class="px-4 py-3 border-b border-gray-100">
                        <p class="text-sm font-medium text-gray-900"><?php echo e($user->full_name); ?></p>
                        <p class="text-xs text-gray-500"><?php echo e($user->email); ?></p>
                    </div>
                    
                    <!-- Settings -->
                    <a href="<?php echo e(route('hr.settings')); ?>" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                        <i class="fas fa-cog w-4 h-4 mr-3 text-gray-400"></i>
                        Settings
                    </a>
                    
                    <!-- Profile -->
                    <a href="<?php echo e(route('hr.profile')); ?>" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                        <i class="fas fa-user-circle w-4 h-4 mr-3 text-gray-400"></i>
                        Profile
                    </a>
                    
                    <!-- Divider -->
                    <div class="border-t border-gray-100 my-1"></div>
                    
                    <!-- Logout -->
                    <form method="POST" action="<?php echo e(route('logout')); ?>" id="logout-form" class="block">
                        <?php echo csrf_field(); ?>
                        <button type="submit" onclick="handleLogout(event, <?php echo e($isCurrentlyTimedIn ? 'true' : 'false'); ?>)" class="flex items-center w-full px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition-colors">
                            <i class="fas fa-sign-out-alt w-4 h-4 mr-3"></i>
                            Logout
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</header>

<script>
// Real-time clock functionality
document.addEventListener('DOMContentLoaded', function() {
    function updateTime() {
        const timeElementDesktop = document.getElementById('current-time-desktop');
        const timeElementMobile = document.getElementById('current-time-mobile');
        
        if (timeElementDesktop || timeElementMobile) {
            // Get current time in Philippines timezone
            const now = new Date();
            const philippinesTime = new Date(now.toLocaleString("en-US", {timeZone: "Asia/Manila"}));
            
            // Format for desktop (full date and time)
            const desktopFormat = philippinesTime.toLocaleDateString('en-US', {
                month: 'short',
                day: 'numeric',
                year: 'numeric',
                hour: 'numeric',
                minute: '2-digit',
                hour12: true
            });
            
            // Format for mobile (time only)
            const mobileFormat = philippinesTime.toLocaleTimeString('en-US', {
                hour: 'numeric',
                minute: '2-digit',
                hour12: true
            });
            
            // Update elements
            if (timeElementDesktop) {
                timeElementDesktop.textContent = desktopFormat;
            }
            if (timeElementMobile) {
                timeElementMobile.textContent = mobileFormat;
            }
        }
    }
    
    // Update time immediately
    updateTime();
    
    // Update time every second
    setInterval(updateTime, 1000);

    // Company switch handler
    window.handleCompanySwitch = function(event, companyName) {
        // Show loading state
        const submitButton = event.target.querySelector('button[type="submit"]');
        if (submitButton) {
            submitButton.disabled = true;
            submitButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Switching...';
        }
        
        // The form will submit normally and page will reload
        return true;
    };

    // Logout handler - check if employee is timed in
    window.handleLogout = function(event, isTimedIn) {
        if (isTimedIn) {
            event.preventDefault();
            event.stopPropagation();
            
            // Show warning modal
            showLogoutWarningModal();
            return false;
        }
        // If not timed in, allow normal logout
        return true;
    };

    // Show logout warning modal
    function showLogoutWarningModal() {
        // Create modal overlay
        const overlay = document.createElement('div');
        overlay.id = 'logout-warning-overlay';
        overlay.className = 'fixed inset-0 bg-black bg-opacity-60 backdrop-blur-sm z-[9999] flex items-center justify-center p-4 animate-fadeIn';
        overlay.style.animation = 'fadeIn 0.2s ease-out';
        overlay.onclick = function(e) {
            if (e.target === overlay) {
                closeLogoutWarningModal();
            }
        };

        // Create modal content
        const modal = document.createElement('div');
        modal.className = 'bg-white rounded-2xl shadow-2xl max-w-md w-full overflow-hidden transform transition-all';
        modal.style.animation = 'slideUp 0.3s ease-out';
        modal.innerHTML = `
            <div class="p-6 sm:p-8">
                <!-- Icon -->
                <div class="flex items-center justify-center w-20 h-20 mx-auto mb-5 bg-gradient-to-br from-yellow-100 to-orange-100 rounded-full shadow-lg">
                    <i class="fas fa-exclamation-triangle text-yellow-600 text-3xl"></i>
                </div>
                
                <!-- Title -->
                <h3 class="text-2xl font-bold text-gray-900 text-center mb-3">Clock Out Required</h3>
                
                <!-- Message -->
                <p class="text-gray-600 text-center mb-8 leading-relaxed text-sm sm:text-base">
                    You are currently clocked in. Please clock out before logging out to ensure accurate time tracking.
                </p>
                
                <!-- Buttons -->
                <div class="space-y-3">
                    <button onclick="clockOutAndLogout()" class="w-full flex items-center justify-center px-5 py-3 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-xl hover:from-blue-700 hover:to-blue-800 transition-all duration-200 font-semibold shadow-lg hover:shadow-xl transform hover:scale-[1.02] active:scale-[0.98]">
                        <i class="fas fa-sign-out-alt mr-2"></i>
                        Clock Out & Logout
                    </button>
                    <button onclick="closeLogoutWarningModal()" class="w-full px-5 py-3 bg-gray-100 text-gray-700 rounded-xl hover:bg-gray-200 transition-all duration-200 font-medium border border-gray-200">
                        Cancel
                    </button>
                    <button onclick="logoutAnyway()" class="w-full flex items-center justify-center px-5 py-3 bg-gradient-to-r from-red-500 to-red-600 text-white rounded-xl hover:from-red-600 hover:to-red-700 transition-all duration-200 font-semibold shadow-lg hover:shadow-xl transform hover:scale-[1.02] active:scale-[0.98]">
                        <i class="fas fa-sign-out-alt mr-2"></i>
                        Logout Anyway
                    </button>
                </div>
            </div>
        `;

        overlay.appendChild(modal);
        document.body.appendChild(overlay);
        
        // Add CSS animations if not already added
        if (!document.getElementById('logout-modal-styles')) {
            const style = document.createElement('style');
            style.id = 'logout-modal-styles';
            style.textContent = `
                @keyframes fadeIn {
                    from { opacity: 0; }
                    to { opacity: 1; }
                }
                @keyframes slideUp {
                    from { 
                        opacity: 0;
                        transform: translateY(20px) scale(0.95);
                    }
                    to { 
                        opacity: 1;
                        transform: translateY(0) scale(1);
                    }
                }
            `;
            document.head.appendChild(style);
        }
    }

    // Close logout warning modal - make it global
    window.closeLogoutWarningModal = function() {
        const overlay = document.getElementById('logout-warning-overlay');
        if (overlay) {
            overlay.remove();
        }
    };

    // Clock out and then logout - make it global
    window.clockOutAndLogout = async function() {
        const overlay = document.getElementById('logout-warning-overlay');
        if (overlay) {
            const modal = overlay.querySelector('div');
            if (modal) {
                modal.innerHTML = `
                    <div class="p-8">
                        <div class="flex flex-col items-center justify-center">
                            <div class="w-16 h-16 mx-auto mb-4 bg-blue-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-spinner fa-spin text-blue-600 text-2xl"></i>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-2">Clocking Out...</h3>
                            <p class="text-gray-600 text-center text-sm">Please wait while we process your clock out.</p>
                        </div>
                    </div>
                `;
            }
        }

        try {
            // Clock out first
            const response = await fetch('<?php echo e(route("attendance.time-out")); ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });

            const data = await response.json();

            if (response.ok) {
                // Successfully clocked out, now logout
                setTimeout(() => {
                    document.getElementById('logout-form').submit();
                }, 500);
            } else {
                // Clock out failed, show error but allow logout
                alert('Failed to clock out: ' + (data.error || 'Unknown error') + '\n\nYou can still logout, but please contact HR to update your attendance record.');
                document.getElementById('logout-form').submit();
            }
        } catch (error) {
            console.error('Error clocking out:', error);
            alert('Error clocking out. You can still logout, but please contact HR to update your attendance record.');
            document.getElementById('logout-form').submit();
        }
    }

    // Logout anyway (without clocking out) - make it global
    window.logoutAnyway = function() {
        window.closeLogoutWarningModal();
        document.getElementById('logout-form').submit();
    };
});
</script>
<?php /**PATH C:\Users\caior\Documents\Mica files\Aeternitas-System-V2\resources\views/components/dashboard/header.blade.php ENDPATH**/ ?>
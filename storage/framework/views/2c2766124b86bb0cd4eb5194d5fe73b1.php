<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['user']));

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

foreach (array_filter((['user']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<div class="w-full p-4 border-t border-gray-200 bg-gray-50 flex-shrink-0">
    <div class="flex items-center">
        <div class="flex-shrink-0">
            <div class="w-10 h-10 bg-gradient-to-r from-blue-500 to-blue-600 rounded-xl flex items-center justify-center shadow-lg">
                <i class="fas fa-user text-white text-sm"></i>
            </div>
        </div>
        <div class="ml-3 flex-1">
            <p class="text-sm font-semibold text-gray-900"><?php echo e($user->full_name); ?></p>
            <p class="text-xs text-gray-500"><?php echo e(ucfirst($user->role)); ?> • <?php echo e($user->employee->department->name ?? 'System'); ?></p>
        </div>
        <div class="flex items-center space-x-2">
            <!-- Status indicator -->
            <div class="w-2 h-2 bg-green-400 rounded-full"></div>
            <span class="text-xs text-gray-500">Online</span>
        </div>
    </div>
    
    <!-- Quick stats for user -->
    <div class="mt-3 grid grid-cols-2 gap-2">
        <div class="bg-white rounded-lg p-2 text-center">
            <p class="text-xs text-gray-500">Last Login</p>
            <p class="text-xs font-medium text-gray-900"><?php echo e($user->last_login_at ? $user->last_login_at->format('M d') : 'Never'); ?></p>
        </div>
        <div class="bg-white rounded-lg p-2 text-center">
            <p class="text-xs text-gray-500">Status</p>
            <p class="text-xs font-medium text-green-600">Active</p>
        </div>
    </div>
</div>
<?php /**PATH C:\Users\caior\Documents\Mica files\Aeternitas-System-V2\resources\views/components/dashboard/sidebar/footer.blade.php ENDPATH**/ ?>
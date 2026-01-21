<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['title', 'value', 'icon', 'color' => 'blue', 'trend' => null]));

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

foreach (array_filter((['title', 'value', 'icon', 'color' => 'blue', 'trend' => null]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
$colorClasses = [
    'blue' => 'bg-blue-100 text-blue-600',
    'green' => 'bg-green-100 text-green-600',
    'yellow' => 'bg-yellow-100 text-yellow-600',
    'purple' => 'bg-purple-100 text-purple-600',
    'red' => 'bg-red-100 text-red-600',
    'indigo' => 'bg-indigo-100 text-indigo-600',
];
?>

<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 sm:p-6 hover:shadow-md transition-shadow">
    <div class="flex items-center">
        <div class="flex-shrink-0">
            <div class="w-10 h-10 sm:w-12 sm:h-12 <?php echo e($colorClasses[$color]); ?> rounded-lg flex items-center justify-center">
                <i class="<?php echo e($icon); ?> text-lg sm:text-xl"></i>
            </div>
        </div>
        <div class="ml-3 sm:ml-4 min-w-0 flex-1">
            <p class="text-xs sm:text-sm font-medium text-gray-500 truncate"><?php echo e($title); ?></p>
            <p class="text-xl sm:text-2xl font-bold text-gray-900 truncate"><?php echo e($value); ?></p>
            <?php if($trend): ?>
            <p class="text-xs <?php echo e($trend['positive'] ? 'text-green-600' : 'text-red-600'); ?>">
                <i class="fas fa-arrow-<?php echo e($trend['positive'] ? 'up' : 'down'); ?> mr-1"></i>
                <?php echo e($trend['value']); ?>

            </p>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php /**PATH C:\xampp\htdocs\Aeternitas-System-V2\resources\views/components/dashboard/stats-card.blade.php ENDPATH**/ ?>
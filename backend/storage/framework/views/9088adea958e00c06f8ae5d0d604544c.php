<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'title' => '',
    'description' => '',
    'actions' => [], // Array of action buttons
]));

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

foreach (array_filter(([
    'title' => '',
    'description' => '',
    'actions' => [], // Array of action buttons
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
        <div>
            <?php if($title): ?>
                <h1 class="text-2xl sm:text-3xl font-bold text-gray-900"><?php echo e($title); ?></h1>
            <?php endif; ?>
            <?php if($description): ?>
                <p class="mt-1 text-sm text-gray-600"><?php echo e($description); ?></p>
            <?php endif; ?>
        </div>
        
        <?php if(count($actions) > 0): ?>
            <div class="mt-4 sm:mt-0 flex gap-3">
                <?php $__currentLoopData = $actions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $action): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php if($action['type'] === 'link'): ?>
                        <a href="<?php echo e($action['href']); ?>" class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                            'inline-flex items-center px-4 py-2 rounded-lg font-medium transition-colors',
                            'bg-blue-600 text-white border border-transparent hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500' => ($action['variant'] ?? 'primary') === 'primary',
                            'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500' => ($action['variant'] ?? 'primary') === 'secondary',
                            'bg-red-600 text-white border border-transparent hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500' => ($action['variant'] ?? 'primary') === 'danger',
                        ]); ?>">
                            <?php if($action['icon'] ?? false): ?>
                                <i class="fas fa-<?php echo e($action['icon']); ?> mr-2"></i>
                            <?php endif; ?>
                            <?php echo e($action['label']); ?>

                        </a>
                    <?php elseif($action['type'] === 'button'): ?>
                        <button <?php if($action['onclick'] ?? false): ?> onclick="<?php echo e($action['onclick']); ?>" <?php endif; ?> class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                            'inline-flex items-center px-4 py-2 rounded-lg font-medium transition-colors',
                            'bg-blue-600 text-white border border-transparent hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500' => ($action['variant'] ?? 'primary') === 'primary',
                            'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500' => ($action['variant'] ?? 'primary') === 'secondary',
                            'bg-red-600 text-white border border-transparent hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500' => ($action['variant'] ?? 'primary') === 'danger',
                        ]); ?>">
                            <?php if($action['icon'] ?? false): ?>
                                <i class="fas fa-<?php echo e($action['icon']); ?> mr-2"></i>
                            <?php endif; ?>
                            <?php echo e($action['label']); ?>

                        </button>
                    <?php endif; ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        <?php endif; ?>
    </div>

    <?php echo e($slot); ?>

</div>
<?php /**PATH C:\internship\Aeternitas-Desktop app\backend\resources\views/components/page-header.blade.php ENDPATH**/ ?>
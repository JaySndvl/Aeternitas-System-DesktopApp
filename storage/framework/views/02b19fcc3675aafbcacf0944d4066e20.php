<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['user', 'activeRoute' => 'dashboard']));

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

foreach (array_filter((['user', 'activeRoute' => 'dashboard']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<div class="fixed inset-y-0 left-0 z-50 w-72 bg-white shadow-2xl transform -translate-x-full transition-all duration-300 ease-in-out lg:translate-x-0 border-r border-gray-100 flex flex-col" id="sidebar">
    <!-- Sidebar Header -->
    <?php if (isset($component)) { $__componentOriginal0e97772a699c8536fcba73d55a87111b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal0e97772a699c8536fcba73d55a87111b = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashboard.sidebar.header','data' => ['user' => $user]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashboard.sidebar.header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['user' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($user)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal0e97772a699c8536fcba73d55a87111b)): ?>
<?php $attributes = $__attributesOriginal0e97772a699c8536fcba73d55a87111b; ?>
<?php unset($__attributesOriginal0e97772a699c8536fcba73d55a87111b); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal0e97772a699c8536fcba73d55a87111b)): ?>
<?php $component = $__componentOriginal0e97772a699c8536fcba73d55a87111b; ?>
<?php unset($__componentOriginal0e97772a699c8536fcba73d55a87111b); ?>
<?php endif; ?>

    <!-- Navigation -->
    <div class="flex-1 overflow-y-auto scrollbar-thin scrollbar-thumb-gray-300 scrollbar-track-gray-100 hover:scrollbar-thumb-gray-400 scroll-smooth min-h-0">
        <!-- Navigation content -->
        <div class="pt-2 pb-8">
            <?php if (isset($component)) { $__componentOriginal9a13b8f66cc0dc946de5ee54bfdd0594 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9a13b8f66cc0dc946de5ee54bfdd0594 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashboard.sidebar.navigation','data' => ['user' => $user,'activeRoute' => $activeRoute]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashboard.sidebar.navigation'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['user' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($user),'activeRoute' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($activeRoute)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9a13b8f66cc0dc946de5ee54bfdd0594)): ?>
<?php $attributes = $__attributesOriginal9a13b8f66cc0dc946de5ee54bfdd0594; ?>
<?php unset($__attributesOriginal9a13b8f66cc0dc946de5ee54bfdd0594); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9a13b8f66cc0dc946de5ee54bfdd0594)): ?>
<?php $component = $__componentOriginal9a13b8f66cc0dc946de5ee54bfdd0594; ?>
<?php unset($__componentOriginal9a13b8f66cc0dc946de5ee54bfdd0594); ?>
<?php endif; ?>
        </div>
    </div>

    <!-- Sidebar Footer -->
    <?php if (isset($component)) { $__componentOriginalef9d02f0999baefc1d0131aa1ebc6336 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalef9d02f0999baefc1d0131aa1ebc6336 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashboard.sidebar.footer','data' => ['user' => $user]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashboard.sidebar.footer'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['user' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($user)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalef9d02f0999baefc1d0131aa1ebc6336)): ?>
<?php $attributes = $__attributesOriginalef9d02f0999baefc1d0131aa1ebc6336; ?>
<?php unset($__attributesOriginalef9d02f0999baefc1d0131aa1ebc6336); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalef9d02f0999baefc1d0131aa1ebc6336)): ?>
<?php $component = $__componentOriginalef9d02f0999baefc1d0131aa1ebc6336; ?>
<?php unset($__componentOriginalef9d02f0999baefc1d0131aa1ebc6336); ?>
<?php endif; ?>
</div><?php /**PATH C:\Users\caior\Documents\Mica files\Aeternitas-System-V2\resources\views/components/dashboard/sidebar.blade.php ENDPATH**/ ?>
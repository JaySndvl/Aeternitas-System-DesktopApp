<?php $__env->startSection('title', 'Admin Dashboard'); ?>

<?php
    $user = auth()->user();
    $pageTitle = 'Admin Dashboard';
    $activeRoute = 'dashboard';
?>

<?php $__env->startSection('content'); ?>
    <!-- Welcome Section -->
    <div class="mb-6 sm:mb-8">
        <h2 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-2">Welcome back, Admin!</h2>
        <p class="text-sm sm:text-base text-gray-600">Here's an overview of your entire organization.</p>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 mb-6 sm:mb-8">
        <?php if (isset($component)) { $__componentOriginalc196470d5436dac6266616cef2a92302 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc196470d5436dac6266616cef2a92302 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashboard.stats-card','data' => ['title' => 'Total Employees','value' => number_format($stats['total_employees']),'icon' => 'fas fa-users','color' => 'blue']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashboard.stats-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Total Employees','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(number_format($stats['total_employees'])),'icon' => 'fas fa-users','color' => 'blue']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc196470d5436dac6266616cef2a92302)): ?>
<?php $attributes = $__attributesOriginalc196470d5436dac6266616cef2a92302; ?>
<?php unset($__attributesOriginalc196470d5436dac6266616cef2a92302); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc196470d5436dac6266616cef2a92302)): ?>
<?php $component = $__componentOriginalc196470d5436dac6266616cef2a92302; ?>
<?php unset($__componentOriginalc196470d5436dac6266616cef2a92302); ?>
<?php endif; ?>
        
        <?php if (isset($component)) { $__componentOriginalc196470d5436dac6266616cef2a92302 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc196470d5436dac6266616cef2a92302 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashboard.stats-card','data' => ['title' => 'Departments','value' => number_format($stats['total_departments']),'icon' => 'fas fa-building','color' => 'green']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashboard.stats-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Departments','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(number_format($stats['total_departments'])),'icon' => 'fas fa-building','color' => 'green']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc196470d5436dac6266616cef2a92302)): ?>
<?php $attributes = $__attributesOriginalc196470d5436dac6266616cef2a92302; ?>
<?php unset($__attributesOriginalc196470d5436dac6266616cef2a92302); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc196470d5436dac6266616cef2a92302)): ?>
<?php $component = $__componentOriginalc196470d5436dac6266616cef2a92302; ?>
<?php unset($__componentOriginalc196470d5436dac6266616cef2a92302); ?>
<?php endif; ?>
        
        <?php if (isset($component)) { $__componentOriginalc196470d5436dac6266616cef2a92302 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc196470d5436dac6266616cef2a92302 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashboard.stats-card','data' => ['title' => 'Total Budget','value' => '₱' . number_format($stats['total_budget'], 2),'icon' => 'fas fa-money-bill-wave','color' => 'purple']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashboard.stats-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Total Budget','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('₱' . number_format($stats['total_budget'], 2)),'icon' => 'fas fa-money-bill-wave','color' => 'purple']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc196470d5436dac6266616cef2a92302)): ?>
<?php $attributes = $__attributesOriginalc196470d5436dac6266616cef2a92302; ?>
<?php unset($__attributesOriginalc196470d5436dac6266616cef2a92302); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc196470d5436dac6266616cef2a92302)): ?>
<?php $component = $__componentOriginalc196470d5436dac6266616cef2a92302; ?>
<?php unset($__componentOriginalc196470d5436dac6266616cef2a92302); ?>
<?php endif; ?>
        
        <?php if (isset($component)) { $__componentOriginalc196470d5436dac6266616cef2a92302 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc196470d5436dac6266616cef2a92302 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashboard.stats-card','data' => ['title' => 'Used Budget','value' => '₱' . number_format($stats['used_budget'], 2),'icon' => 'fas fa-chart-pie','color' => 'yellow']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashboard.stats-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Used Budget','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('₱' . number_format($stats['used_budget'], 2)),'icon' => 'fas fa-chart-pie','color' => 'yellow']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc196470d5436dac6266616cef2a92302)): ?>
<?php $attributes = $__attributesOriginalc196470d5436dac6266616cef2a92302; ?>
<?php unset($__attributesOriginalc196470d5436dac6266616cef2a92302); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc196470d5436dac6266616cef2a92302)): ?>
<?php $component = $__componentOriginalc196470d5436dac6266616cef2a92302; ?>
<?php unset($__componentOriginalc196470d5436dac6266616cef2a92302); ?>
<?php endif; ?>
    </div>

    <!-- Additional Stats -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
        <?php if (isset($component)) { $__componentOriginalc196470d5436dac6266616cef2a92302 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc196470d5436dac6266616cef2a92302 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashboard.stats-card','data' => ['title' => 'Remaining Budget','value' => '₱' . number_format($stats['remaining_budget'], 2),'icon' => 'fas fa-wallet','color' => 'green']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashboard.stats-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Remaining Budget','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('₱' . number_format($stats['remaining_budget'], 2)),'icon' => 'fas fa-wallet','color' => 'green']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc196470d5436dac6266616cef2a92302)): ?>
<?php $attributes = $__attributesOriginalc196470d5436dac6266616cef2a92302; ?>
<?php unset($__attributesOriginalc196470d5436dac6266616cef2a92302); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc196470d5436dac6266616cef2a92302)): ?>
<?php $component = $__componentOriginalc196470d5436dac6266616cef2a92302; ?>
<?php unset($__componentOriginalc196470d5436dac6266616cef2a92302); ?>
<?php endif; ?>
        
        <?php if (isset($component)) { $__componentOriginalc196470d5436dac6266616cef2a92302 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc196470d5436dac6266616cef2a92302 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashboard.stats-card','data' => ['title' => 'Average Salary','value' => '₱' . number_format($stats['average_salary'], 2),'icon' => 'fas fa-calculator','color' => 'indigo']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashboard.stats-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Average Salary','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('₱' . number_format($stats['average_salary'], 2)),'icon' => 'fas fa-calculator','color' => 'indigo']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc196470d5436dac6266616cef2a92302)): ?>
<?php $attributes = $__attributesOriginalc196470d5436dac6266616cef2a92302; ?>
<?php unset($__attributesOriginalc196470d5436dac6266616cef2a92302); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc196470d5436dac6266616cef2a92302)): ?>
<?php $component = $__componentOriginalc196470d5436dac6266616cef2a92302; ?>
<?php unset($__componentOriginalc196470d5436dac6266616cef2a92302); ?>
<?php endif; ?>
        
        <?php if (isset($component)) { $__componentOriginalc196470d5436dac6266616cef2a92302 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc196470d5436dac6266616cef2a92302 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashboard.stats-card','data' => ['title' => 'Total Accounts','value' => number_format($stats['total_accounts']),'icon' => 'fas fa-user-shield','color' => 'red']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashboard.stats-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Total Accounts','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(number_format($stats['total_accounts'])),'icon' => 'fas fa-user-shield','color' => 'red']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc196470d5436dac6266616cef2a92302)): ?>
<?php $attributes = $__attributesOriginalc196470d5436dac6266616cef2a92302; ?>
<?php unset($__attributesOriginalc196470d5436dac6266616cef2a92302); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc196470d5436dac6266616cef2a92302)): ?>
<?php $component = $__componentOriginalc196470d5436dac6266616cef2a92302; ?>
<?php unset($__componentOriginalc196470d5436dac6266616cef2a92302); ?>
<?php endif; ?>
    </div>

    <!-- Charts and Tables Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
        <!-- Department Statistics -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-semibold text-gray-900">Department Statistics</h3>
                <button class="text-blue-600 hover:text-blue-700 text-sm font-medium">View All</button>
            </div>
            <div class="space-y-4">
                <?php $__currentLoopData = $department_stats; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dept): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="w-3 h-3 bg-blue-500 rounded-full mr-3"></div>
                        <span class="text-sm font-medium text-gray-700"><?php echo e($dept->name); ?></span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <span class="text-sm text-gray-500"><?php echo e($dept->employees_count); ?> employees</span>
                        <span class="text-sm text-gray-500">₱<?php echo e(number_format($dept->employees_sum_salary, 2)); ?></span>
                    </div>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-semibold text-gray-900">System Activity</h3>
                <button class="text-blue-600 hover:text-blue-700 text-sm font-medium">View All</button>
            </div>
            <div class="space-y-4">
                <div class="flex items-center">
                    <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center mr-3">
                        <i class="fas fa-user-plus text-green-600 text-sm"></i>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-900">New employee added</p>
                        <p class="text-xs text-gray-500">2 hours ago</p>
                    </div>
                </div>
                <div class="flex items-center">
                    <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center mr-3">
                        <i class="fas fa-building text-blue-600 text-sm"></i>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-900">Department updated</p>
                        <p class="text-xs text-gray-500">4 hours ago</p>
                    </div>
                </div>
                <div class="flex items-center">
                    <div class="w-8 h-8 bg-yellow-100 rounded-full flex items-center justify-center mr-3">
                        <i class="fas fa-money-bill-wave text-yellow-600 text-sm"></i>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-900">Payroll processed</p>
                        <p class="text-xs text-gray-500">6 hours ago</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Employees Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900">Recent Employees</h3>
                <button class="text-blue-600 hover:text-blue-700 text-sm font-medium">View All</button>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Employee</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Department</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Position</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Salary</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Hire Date</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php $__currentLoopData = $recent_employees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $employee): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                                    <i class="fas fa-user text-blue-600"></i>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900"><?php echo e($employee->full_name); ?></div>
                                    <div class="text-sm text-gray-500"><?php echo e($employee->employee_id); ?></div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                <?php echo e($employee->department->name); ?>

                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo e($employee->position); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">₱<?php echo e(number_format($employee->salary, 2)); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo e($employee->hire_date->format('M d, Y')); ?></td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.dashboard-base', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\internship\Aeternitas-Desktop app\backend\resources\views/dashboards/admin.blade.php ENDPATH**/ ?>
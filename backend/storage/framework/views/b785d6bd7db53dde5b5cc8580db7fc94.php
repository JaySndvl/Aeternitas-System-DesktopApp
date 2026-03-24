

<?php $__env->startSection('title', 'Messages from Employees'); ?>

<?php $__env->startSection('content'); ?>
<div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Page Header -->
        <?php if (isset($component)) { $__componentOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.page-header','data' => ['title' => 'Messages from Employees','breadcrumbs' => [
                ['name' => 'Dashboard', 'route' => route('dashboard')],
                ['name' => 'HR', 'route' => '#'],
                ['name' => 'Messages', 'current' => true]
            ]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Messages from Employees','breadcrumbs' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute([
                ['name' => 'Dashboard', 'route' => route('dashboard')],
                ['name' => 'HR', 'route' => '#'],
                ['name' => 'Messages', 'current' => true]
            ])]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalf8d4ea307ab1e58d4e472a43c8548d8e)): ?>
<?php $attributes = $__attributesOriginalf8d4ea307ab1e58d4e472a43c8548d8e; ?>
<?php unset($__attributesOriginalf8d4ea307ab1e58d4e472a43c8548d8e); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalf8d4ea307ab1e58d4e472a43c8548d8e)): ?>
<?php $component = $__componentOriginalf8d4ea307ab1e58d4e472a43c8548d8e; ?>
<?php unset($__componentOriginalf8d4ea307ab1e58d4e472a43c8548d8e); ?>
<?php endif; ?>

        <!-- Tabs/Action Buttons -->
        <div class="flex gap-4 mb-8">
            <a href="<?php echo e(route('hr.contacts.admin')); ?>" class="flex items-center gap-2 px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                <i class="fas fa-inbox"></i>
                <span>All Contacts</span>
            </a>
            <a href="<?php echo e(route('hr.messages.index')); ?>" class="flex items-center gap-2 px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
                <i class="fas fa-envelope"></i>
                <span>Messages</span>
            </a>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-blue-100">
                        <i class="fas fa-envelope text-blue-600"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-gray-500 text-sm">Total Messages</p>
                        <p class="text-2xl font-bold text-gray-900"><?php echo e($totalMessages); ?></p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-yellow-100">
                        <i class="fas fa-exclamation-circle text-yellow-600"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-gray-500 text-sm">Awaiting Response</p>
                        <p class="text-2xl font-bold text-gray-900"><?php echo e($unreadCount); ?></p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-green-100">
                        <i class="fas fa-reply text-green-600"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-gray-500 text-sm">Responded</p>
                        <p class="text-2xl font-bold text-gray-900"><?php echo e($respondedCount); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Messages List -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <?php if($messages->count()): ?>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">From</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subject</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php $__currentLoopData = $messages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $message): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr class="hover:bg-gray-50 transition <?php echo e($message->status === 'pending' ? 'bg-blue-50' : ''); ?>">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div>
                                            <p class="text-sm font-medium text-gray-900">
                                                <?php if($message->employee): ?>
                                                    <?php echo e($message->employee->first_name); ?> <?php echo e($message->employee->last_name); ?>

                                                <?php else: ?>
                                                    <?php echo e($message->user->name ?? 'Unknown'); ?>

                                                <?php endif; ?>
                                            </p>
                                            <p class="text-xs text-gray-500"><?php echo e($message->user->email ?? '-'); ?></p>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <p class="text-sm font-medium text-gray-900"><?php echo e($message->subject); ?></p>
                                        <p class="text-xs text-gray-600 mt-1"><?php echo e(Str::limit($message->message, 50)); ?></p>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-3 py-1 text-xs font-medium rounded-full 
                                            <?php switch($message->category):
                                                case ('leave'): ?> bg-purple-100 text-purple-800 <?php break; ?>
                                                <?php case ('payroll'): ?> bg-green-100 text-green-800 <?php break; ?>
                                                <?php case ('benefits'): ?> bg-blue-100 text-blue-800 <?php break; ?>
                                                <?php case ('schedule'): ?> bg-orange-100 text-orange-800 <?php break; ?>
                                                <?php case ('complaint'): ?> bg-red-100 text-red-800 <?php break; ?>
                                                <?php case ('request'): ?> bg-yellow-100 text-yellow-800 <?php break; ?>
                                                <?php default: ?> bg-gray-100 text-gray-800
                                            <?php endswitch; ?>
                                        ">
                                            <?php echo e(ucfirst($message->category)); ?>

                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-3 py-1 text-xs font-medium rounded-full 
                                            <?php switch($message->status):
                                                case ('pending'): ?> bg-yellow-100 text-yellow-800 <?php break; ?>
                                                <?php case ('in_progress'): ?> bg-blue-100 text-blue-800 <?php break; ?>
                                                <?php case ('resolved'): ?> bg-green-100 text-green-800 <?php break; ?>
                                                <?php case ('closed'): ?> bg-gray-100 text-gray-800 <?php break; ?>
                                            <?php endswitch; ?>
                                        ">
                                            <?php echo e(ucfirst(str_replace('_', ' ', $message->status))); ?>

                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <div>
                                            <p><?php echo e($message->created_at->format('M d, Y')); ?></p>
                                            <p class="text-xs"><?php echo e($message->created_at->format('h:i A')); ?></p>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <a href="<?php echo e(route('hr.contact.show', $message->id)); ?>" class="text-blue-600 hover:text-blue-900 font-medium inline-flex items-center gap-1">
                                            <i class="fas fa-envelope-open"></i> Open
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="bg-white px-6 py-4 border-t border-gray-200">
                    <?php echo e($messages->links()); ?>

                </div>
            <?php else: ?>
                <div class="text-center py-12">
                    <i class="fas fa-inbox text-4xl text-gray-300 mb-4"></i>
                    <p class="text-gray-500 text-lg">No messages yet</p>
                    <p class="text-gray-400 text-sm mt-2">All employee messages will appear here</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.dashboard-base', ['user' => $user, 'activeRoute' => 'hr.messages.index'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\internship\Aeternitas-Desktop app\backend\resources\views/hr/messages.blade.php ENDPATH**/ ?>
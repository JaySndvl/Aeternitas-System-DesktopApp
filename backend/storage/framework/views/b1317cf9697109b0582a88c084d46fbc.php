<?php $__env->startSection('title', 'HR Contact Management'); ?>

<?php $__env->startSection('content'); ?>
<div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Page Header -->
        <?php if (isset($component)) { $__componentOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.page-header','data' => ['title' => 'Contact Management','breadcrumbs' => [
                ['name' => 'Dashboard', 'route' => route('dashboard')],
                ['name' => 'HR', 'route' => '#'],
                ['name' => 'Contacts', 'current' => true]
            ]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Contact Management','breadcrumbs' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute([
                ['name' => 'Dashboard', 'route' => route('dashboard')],
                ['name' => 'HR', 'route' => '#'],
                ['name' => 'Contacts', 'current' => true]
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
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-blue-100">
                        <i class="fas fa-inbox text-blue-600"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-gray-500 text-sm">Total Contacts</p>
                        <p class="text-2xl font-bold text-gray-900"><?php echo e($totalContacts); ?></p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-yellow-100">
                        <i class="fas fa-clock text-yellow-600"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-gray-500 text-sm">Pending</p>
                        <p class="text-2xl font-bold text-gray-900"><?php echo e($pendingCount); ?></p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-blue-100">
                        <i class="fas fa-spinner text-blue-600"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-gray-500 text-sm">In Progress</p>
                        <p class="text-2xl font-bold text-gray-900"><?php echo e($inProgressCount); ?></p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-green-100">
                        <i class="fas fa-check-circle text-green-600"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-gray-500 text-sm">Resolved</p>
                        <p class="text-2xl font-bold text-gray-900"><?php echo e($resolvedCount); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-lg shadow mb-8 p-6">
            <form action="<?php echo e(route('hr.contacts.admin')); ?>" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label for="status_filter" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <select id="status_filter" name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">All Statuses</option>
                        <option value="pending" <?php if(request('status') === 'pending'): echo 'selected'; endif; ?>>Pending</option>
                        <option value="in_progress" <?php if(request('status') === 'in_progress'): echo 'selected'; endif; ?>>In Progress</option>
                        <option value="resolved" <?php if(request('status') === 'resolved'): echo 'selected'; endif; ?>>Resolved</option>
                        <option value="closed" <?php if(request('status') === 'closed'): echo 'selected'; endif; ?>>Closed</option>
                    </select>
                </div>

                <div>
                    <label for="category_filter" class="block text-sm font-medium text-gray-700 mb-2">Category</label>
                    <select id="category_filter" name="category" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">All Categories</option>
                        <option value="attendance" <?php if(request('category') === 'attendance'): echo 'selected'; endif; ?>>Attendance</option>
                        <option value="leave" <?php if(request('category') === 'leave'): echo 'selected'; endif; ?>>Leave</option>
                        <option value="payroll" <?php if(request('category') === 'payroll'): echo 'selected'; endif; ?>>Payroll</option>
                        <option value="benefits" <?php if(request('category') === 'benefits'): echo 'selected'; endif; ?>>Benefits</option>
                        <option value="schedule" <?php if(request('category') === 'schedule'): echo 'selected'; endif; ?>>Schedule</option>
                        <option value="general" <?php if(request('category') === 'general'): echo 'selected'; endif; ?>>General</option>
                        <option value="complaint" <?php if(request('category') === 'complaint'): echo 'selected'; endif; ?>>Complaint</option>
                        <option value="request" <?php if(request('category') === 'request'): echo 'selected'; endif; ?>>Request</option>
                    </select>
                </div>

                <div>
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                    <input type="text" id="search" name="search" placeholder="Search by subject..." value="<?php echo e(request('search')); ?>" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>

                <div class="flex items-end gap-2">
                    <button type="submit" class="flex-1 bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition flex items-center justify-center gap-2">
                        <i class="fas fa-search"></i> Filter
                    </button>
                    <a href="<?php echo e(route('hr.contacts.admin')); ?>" class="flex-1 bg-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-400 transition text-center">
                        Clear
                    </a>
                </div>
            </form>
        </div>

        <!-- Contacts Table -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <?php if($contacts->count()): ?>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Employee</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subject</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php $__currentLoopData = $contacts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $contact): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div>
                                            <p class="text-sm font-medium text-gray-900">
                                                <?php if($contact->employee): ?>
                                                    <?php echo e($contact->employee->first_name); ?> <?php echo e($contact->employee->last_name); ?>

                                                <?php else: ?>
                                                    <?php echo e($contact->user->name ?? 'Unknown'); ?>

                                                <?php endif; ?>
                                            </p>
                                            <p class="text-xs text-gray-500"><?php echo e($contact->user->email ?? '-'); ?></p>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <p class="text-sm text-gray-900"><?php echo e(\Illuminate\Support\Str::limit($contact->subject, 30)); ?></p>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-3 py-1 text-xs font-medium rounded-full 
                                            <?php switch($contact->category):
                                                case ('attendance'): ?> bg-indigo-100 text-indigo-800 <?php break; ?>
                                                <?php case ('leave'): ?> bg-purple-100 text-purple-800 <?php break; ?>
                                                <?php case ('payroll'): ?> bg-green-100 text-green-800 <?php break; ?>
                                                <?php case ('benefits'): ?> bg-blue-100 text-blue-800 <?php break; ?>
                                                <?php case ('schedule'): ?> bg-orange-100 text-orange-800 <?php break; ?>
                                                <?php case ('complaint'): ?> bg-red-100 text-red-800 <?php break; ?>
                                                <?php case ('request'): ?> bg-yellow-100 text-yellow-800 <?php break; ?>
                                                <?php default: ?> bg-gray-100 text-gray-800
                                            <?php endswitch; ?>
                                        ">
                                            <?php echo e(ucfirst($contact->category)); ?>

                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-3 py-1 text-xs font-medium rounded-full 
                                            <?php switch($contact->status):
                                                case ('pending'): ?> bg-yellow-100 text-yellow-800 <?php break; ?>
                                                <?php case ('in_progress'): ?> bg-blue-100 text-blue-800 <?php break; ?>
                                                <?php case ('resolved'): ?> bg-green-100 text-green-800 <?php break; ?>
                                                <?php case ('closed'): ?> bg-gray-100 text-gray-800 <?php break; ?>
                                            <?php endswitch; ?>
                                        ">
                                            <?php echo e(ucfirst(str_replace('_', ' ', $contact->status))); ?>

                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <div>
                                            <?php echo e($contact->created_at->format('M d, Y')); ?>

                                            <p class="text-xs"><?php echo e($contact->created_at->format('h:i A')); ?></p>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <a href="<?php echo e(route('hr.contact.show', $contact->id)); ?>" class="text-blue-600 hover:text-blue-900 font-medium">
                                            <i class="fas fa-eye mr-1"></i> View
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="bg-white px-6 py-4 border-t border-gray-200">
                    <?php echo e($contacts->links()); ?>

                </div>
            <?php else: ?>
                <div class="text-center py-12">
                    <i class="fas fa-inbox text-4xl text-gray-300 mb-4"></i>
                    <p class="text-gray-500 text-lg">No contacts found</p>
                    <p class="text-gray-400 text-sm mt-2">Try adjusting your filters</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.dashboard-base', ['user' => $user, 'activeRoute' => 'hr.contacts.admin'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\internship\Aeternitas-Desktop app\backend\resources\views/hr/contacts-admin.blade.php ENDPATH**/ ?>
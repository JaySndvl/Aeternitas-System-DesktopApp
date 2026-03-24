<?php $__env->startSection('title', 'Contact Details'); ?>

<?php $__env->startSection('content'); ?>
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div class="flex items-center space-x-4">
            <a href="<?php echo e(route('hr.contact.index')); ?>" class="inline-flex items-center px-4 py-2 text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">
                <i class="fas fa-arrow-left mr-2"></i>
                Back
            </a>
            <div>
                <h1 class="text-2xl font-bold text-gray-900"><?php echo e($hrContact->subject); ?></h1>
                <p class="mt-1 text-sm text-gray-500"><?php echo e($hrContact->created_at->format('M d, Y \a\t H:i')); ?></p>
            </div>
        </div>
        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold
            <?php if($hrContact->status === 'pending'): ?> bg-yellow-100 text-yellow-800
            <?php elseif($hrContact->status === 'in_progress'): ?> bg-blue-100 text-blue-800
            <?php elseif($hrContact->status === 'resolved'): ?> bg-green-100 text-green-800
            <?php else: ?> bg-gray-100 text-gray-800
            <?php endif; ?>">
            <?php echo e(ucfirst(str_replace('_', ' ', $hrContact->status))); ?>

        </span>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Original Message -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <h2 class="text-lg font-semibold text-gray-900">Your Message</h2>
                </div>
                
                <div class="p-6">
                    <p class="text-gray-700 whitespace-pre-wrap"><?php echo e($hrContact->message); ?></p>
                </div>
            </div>

            <!-- HR Response -->
            <?php if($hrContact->response): ?>
                <div class="bg-white rounded-lg shadow-sm border border-green-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-green-200 bg-green-50">
                        <div class="flex items-center justify-between">
                            <h2 class="text-lg font-semibold text-green-900">
                                <i class="fas fa-check-circle mr-2"></i>HR Response
                            </h2>
                            <?php if($hrContact->responder): ?>
                                <span class="text-sm text-green-700">From: <?php echo e($hrContact->responder->email); ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="p-6">
                        <p class="text-gray-700 whitespace-pre-wrap"><?php echo e($hrContact->response); ?></p>
                        <?php if($hrContact->responded_at): ?>
                            <p class="mt-4 text-sm text-gray-500">
                                <i class="fas fa-clock mr-1"></i>
                                Responded on <?php echo e($hrContact->responded_at->format('M d, Y \a\t H:i')); ?>

                            </p>
                        <?php endif; ?>
                    </div>
                </div>
            <?php elseif(in_array(strtolower($user->role), ['hr', 'admin', 'administrator'])): ?>
                <!-- HR Response Form -->
                <form method="POST" action="<?php echo e(route('hr.contact.respond', $hrContact)); ?>" class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                    <?php echo csrf_field(); ?>
                    
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                        <h2 class="text-lg font-semibold text-gray-900">Send Response</h2>
                    </div>
                    
                    <div class="p-6 space-y-6">
                        <!-- Status -->
                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Update Status</label>
                            <select id="status" name="status" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option value="pending" <?php if($hrContact->status === 'pending'): echo 'selected'; endif; ?>>Pending</option>
                                <option value="in_progress" <?php if($hrContact->status === 'in_progress'): echo 'selected'; endif; ?>>In Progress</option>
                                <option value="resolved" <?php if($hrContact->status === 'resolved'): echo 'selected'; endif; ?>>Resolved</option>
                                <option value="closed" <?php if($hrContact->status === 'closed'): echo 'selected'; endif; ?>>Closed</option>
                            </select>
                        </div>

                        <!-- Response -->
                        <div>
                            <label for="response" class="block text-sm font-medium text-gray-700 mb-2">Your Response</label>
                            <textarea id="response" name="response" rows="8" required placeholder="Type your response here..."
                                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"><?php echo e(old('response')); ?></textarea>
                            <?php $__errorArgs = ['response'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>

                        <!-- Submit Button -->
                        <div class="flex justify-end space-x-3">
                            <a href="<?php echo e(route('hr.contact.index')); ?>" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg font-medium text-gray-700 hover:bg-gray-50 transition-colors">
                                Cancel
                            </a>
                            <button type="submit" class="inline-flex items-center px-6 py-2 bg-green-600 border border-transparent rounded-lg font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors">
                                <i class="fas fa-check mr-2"></i>
                                Send Response
                            </button>
                        </div>
                    </div>
                </form>
            <?php else: ?>
                <div class="text-center py-12 bg-white rounded-lg shadow-sm border border-gray-200">
                    <i class="fas fa-hourglass text-4xl text-gray-400 mb-4"></i>
                    <p class="text-gray-600">HR will review your message and respond soon.</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Sidebar -->
        <div class="lg:col-span-1 space-y-6">
            <!-- Details Card -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <h3 class="text-lg font-semibold text-gray-900">Details</h3>
                </div>
                
                <div class="p-6 space-y-4">
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Category</p>
                        <p class="mt-1 text-sm text-gray-900"><?php echo e(ucfirst($hrContact->category)); ?></p>
                    </div>

                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Status</p>
                        <p class="mt-1 text-sm text-gray-900"><?php echo e(ucfirst(str_replace('_', ' ', $hrContact->status))); ?></p>
                    </div>

                    <?php if($hrContact->employee): ?>
                        <div>
                            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Employee</p>
                            <p class="mt-1 text-sm text-gray-900"><?php echo e($hrContact->employee->full_name); ?></p>
                        </div>
                    <?php endif; ?>

                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Submitted</p>
                        <p class="mt-1 text-sm text-gray-900"><?php echo e($hrContact->created_at->format('M d, Y')); ?></p>
                    </div>

                    <?php if($hrContact->responded_at): ?>
                        <div>
                            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Responded</p>
                            <p class="mt-1 text-sm text-gray-900"><?php echo e($hrContact->responded_at->format('M d, Y')); ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Actions Card (HR only) -->
            <?php if(in_array(strtolower($user->role), ['hr', 'admin', 'administrator'])): ?>
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                        <h3 class="text-lg font-semibold text-gray-900">Quick Actions</h3>
                    </div>
                    
                    <div class="p-6 space-y-2">
                        <a href="<?php echo e(route('hr.contacts.admin')); ?>" class="block w-full text-center px-4 py-2 text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">
                            <i class="fas fa-list mr-2"></i>
                            View All Contacts
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.dashboard-base', ['user' => $user, 'activeRoute' => 'hr.contact.show'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\internship\Aeternitas-Desktop app\backend\resources\views/hr/contact-show.blade.php ENDPATH**/ ?>
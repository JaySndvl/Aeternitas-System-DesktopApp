

<?php $__env->startSection('title', 'Employee Documents - ' . $employee->full_name); ?>

<?php $__env->startSection('content'); ?>
<div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white shadow-sm border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="py-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900"><?php echo e($employee->full_name); ?> - Documents</h1>
                        <p class="mt-1 text-sm text-gray-600">Employee ID: <?php echo e($employee->employee_id); ?></p>
                    </div>
                    <div class="flex space-x-3">
                        <a href="<?php echo e(route('documents.index')); ?>" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                            <i class="fas fa-arrow-left mr-2"></i>
                            Back to Documents
                        </a>
                        <a href="<?php echo e(route('employees.show', $employee)); ?>" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-lg font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                            <i class="fas fa-user mr-2"></i>
                            View Profile
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Employee Info -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Employee Information</h3>
                    </div>
                    <div class="p-6">
                        <div class="flex items-center space-x-4 mb-4">
                            <div class="flex-shrink-0 h-16 w-16">
                                <div class="h-16 w-16 rounded-full bg-blue-100 flex items-center justify-center">
                                    <span class="text-xl font-medium text-blue-600">
                                        <?php echo e(substr($employee->first_name, 0, 1)); ?><?php echo e(substr($employee->last_name, 0, 1)); ?>

                                    </span>
                                </div>
                            </div>
                            <div>
                                <h4 class="text-lg font-semibold text-gray-900"><?php echo e($employee->full_name); ?></h4>
                                <p class="text-sm text-gray-600"><?php echo e($employee->position ?? 'No position'); ?></p>
                                <p class="text-sm text-gray-500"><?php echo e($employee->department->name ?? 'No Department'); ?></p>
                            </div>
                        </div>
                        
                        <div class="space-y-3">
                            <div>
                                <span class="text-sm font-medium text-gray-700">Employee ID:</span>
                                <p class="text-sm text-gray-900"><?php echo e($employee->employee_id); ?></p>
                            </div>
                            <div>
                                <span class="text-sm font-medium text-gray-700">Email:</span>
                                <p class="text-sm text-gray-900"><?php echo e($employee->email ?? 'Not provided'); ?></p>
                            </div>
                            <div>
                                <span class="text-sm font-medium text-gray-700">Phone:</span>
                                <p class="text-sm text-gray-900"><?php echo e($employee->phone ?? 'Not provided'); ?></p>
                            </div>
                            <div>
                                <span class="text-sm font-medium text-gray-700">Status:</span>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                    <?php if($employee->status == 'active'): ?> bg-green-100 text-green-800
                                    <?php elseif($employee->status == 'on-leave'): ?> bg-yellow-100 text-yellow-800
                                    <?php else: ?> bg-red-100 text-red-800 <?php endif; ?>">
                                    <?php echo e(ucfirst($employee->status ?? 'active')); ?>

                                </span>
                            </div>
                            <div>
                                <span class="text-sm font-medium text-gray-700">Salary:</span>
                                <p class="text-sm text-gray-900">
                                    <?php if($employee->salary): ?>
                                        ₱<?php echo e(number_format($employee->salary, 2)); ?>

                                    <?php else: ?>
                                        Not set
                                    <?php endif; ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Documents List -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                        <h3 class="text-lg font-medium text-gray-900">Documents</h3>
                        <button class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-lg font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors">
                            <i class="fas fa-plus mr-2"></i>
                            Upload Document
                        </button>
                    </div>
                    
                    <div class="p-6">
                        <?php if(count($documents) > 0): ?>
                            <div class="space-y-3">
                                <?php $__currentLoopData = $documents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $document): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg border border-gray-200">
                                    <div class="flex items-center">
                                        <i class="fas fa-file-pdf text-red-500 text-xl mr-4"></i>
                                        <div>
                                            <h4 class="font-medium text-gray-900"><?php echo e($document->name); ?></h4>
                                            <p class="text-sm text-gray-500"><?php echo e($document->type); ?> • <?php echo e($document->created_at->format('M j, Y')); ?></p>
                                        </div>
                                    </div>
                                    <div class="flex space-x-2">
                                        <a href="#" target="_blank" 
                                           class="inline-flex items-center px-3 py-1 bg-blue-100 text-blue-700 rounded-lg hover:bg-blue-200">
                                            <i class="fas fa-eye mr-1"></i> View
                                        </a>
                                        <a href="#" download
                                           class="inline-flex items-center px-3 py-1 bg-green-100 text-green-700 rounded-lg hover:bg-green-200">
                                            <i class="fas fa-download mr-1"></i> Download
                                        </a>
                                    </div>
                                </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-12">
                                <i class="fas fa-file text-gray-300 text-4xl mb-4"></i>
                                <h3 class="text-lg font-medium text-gray-900 mb-2">No documents yet</h3>
                                <p class="text-gray-500">Upload documents to view them here.</p>
                                <div class="mt-4">
                                    <button class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-lg font-medium text-white hover:bg-blue-700">
                                        <i class="fas fa-upload mr-2"></i>
                                        Upload First Document
                                    </button>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Sample documents for demonstration -->
                        <div class="mt-8 border-t border-gray-200 pt-8">
                            <h4 class="text-lg font-medium text-gray-900 mb-4">Document Types Needed</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="border border-gray-200 rounded-lg p-4">
                                    <div class="flex items-center mb-2">
                                        <i class="fas fa-file-contract text-blue-500 mr-2"></i>
                                        <span class="font-medium text-gray-900">Employment Contract</span>
                                        <span class="ml-auto text-sm text-red-500">Missing</span>
                                    </div>
                                    <p class="text-sm text-gray-600">Signed employment agreement</p>
                                </div>
                                <div class="border border-gray-200 rounded-lg p-4">
                                    <div class="flex items-center mb-2">
                                        <i class="fas fa-id-card text-green-500 mr-2"></i>
                                        <span class="font-medium text-gray-900">Government IDs</span>
                                        <span class="ml-auto text-sm text-red-500">Missing</span>
                                    </div>
                                    <p class="text-sm text-gray-600">SSS, PhilHealth, Pag-IBIG, TIN</p>
                                </div>
                                <div class="border border-gray-200 rounded-lg p-4">
                                    <div class="flex items-center mb-2">
                                        <i class="fas fa-graduation-cap text-purple-500 mr-2"></i>
                                        <span class="font-medium text-gray-900">Educational Records</span>
                                        <span class="ml-auto text-sm text-yellow-500">Partial</span>
                                    </div>
                                    <p class="text-sm text-gray-600">Diploma, Transcript of Records</p>
                                </div>
                                <div class="border border-gray-200 rounded-lg p-4">
                                    <div class="flex items-center mb-2">
                                        <i class="fas fa-file-medical text-red-500 mr-2"></i>
                                        <span class="font-medium text-gray-900">Medical Certificate</span>
                                        <span class="ml-auto text-sm text-red-500">Missing</span>
                                    </div>
                                    <p class="text-sm text-gray-600">Pre-employment medical exam</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.dashboard-base', ['user' => auth()->user(), 'activeRoute' => 'documents.index'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\Aeternitas-System-V2\resources\views/employees/documents.blade.php ENDPATH**/ ?>
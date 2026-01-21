<?php $__env->startSection('title', 'Companies'); ?>

<?php $__env->startSection('content'); ?>
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Companies</h1>
            <p class="text-gray-600">Manage company information and settings</p>
        </div>
        <a href="<?php echo e(route('companies.create')); ?>" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-lg font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
            <i class="fas fa-plus mr-2"></i>
            Add Company
        </a>
    </div>

    <!-- Companies List -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-medium text-gray-900">All Companies</h2>
        </div>
        
        <?php if($companies->count() > 0): ?>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Company</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Code</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Location</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contact</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Switch Company</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php $__currentLoopData = $companies; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $company): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                                            <i class="fas fa-building text-blue-600"></i>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900"><?php echo e($company->name); ?></div>
                                        <?php if($company->description): ?>
                                            <div class="text-sm text-gray-500"><?php echo e(Str::limit($company->description, 50)); ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                    <?php echo e($company->code); ?>

                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php if($company->city && $company->country): ?>
                                    <?php echo e($company->city); ?>, <?php echo e($company->country); ?>

                                <?php elseif($company->city): ?>
                                    <?php echo e($company->city); ?>

                                <?php elseif($company->country): ?>
                                    <?php echo e($company->country); ?>

                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php if($company->email): ?>
                                    <div class="flex items-center">
                                        <i class="fas fa-envelope mr-2 text-gray-400"></i>
                                        <?php echo e($company->email); ?>

                                    </div>
                                <?php endif; ?>
                                <?php if($company->phone): ?>
                                    <div class="flex items-center mt-1">
                                        <i class="fas fa-phone mr-2 text-gray-400"></i>
                                        <?php echo e($company->phone); ?>

                                    </div>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php if($company->is_active): ?>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        <i class="fas fa-check-circle mr-1"></i>
                                        Active
                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        <i class="fas fa-times-circle mr-1"></i>
                                        Inactive
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <?php
                                    $currentCompany = \App\Helpers\CompanyHelper::getCurrentCompany();
                                    $isCurrentCompany = $currentCompany && $company->id === $currentCompany->id;
                                ?>
                                
                                <?php if($isCurrentCompany): ?>
                                    <span class="inline-flex items-center px-3 py-2 bg-blue-600 text-white rounded-lg font-medium text-sm">
                                        <i class="fas fa-check-circle mr-2"></i>
                                        Current
                                    </span>
                                <?php else: ?>
                                    <form method="POST" action="<?php echo e(route('companies.switch')); ?>" class="inline" 
                                          onsubmit="handleCompanySwitch(event, '<?php echo e($company->name); ?>')">
                                        <?php echo csrf_field(); ?>
                                        <input type="hidden" name="company_id" value="<?php echo e($company->id); ?>">
                                        <button type="submit" 
                                                class="inline-flex items-center px-3 py-2 bg-green-600 border border-transparent rounded-lg font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors text-sm">
                                            <i class="fas fa-exchange-alt mr-2"></i>
                                            Switch
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex items-center space-x-2">
                                    <a href="<?php echo e(route('companies.show', $company)); ?>" class="text-blue-600 hover:text-blue-900" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="<?php echo e(route('companies.edit', $company)); ?>" class="text-indigo-600 hover:text-indigo-900" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="<?php echo e(route('companies.destroy', $company)); ?>" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this company?')">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('DELETE'); ?>
                                        <button type="submit" class="text-red-600 hover:text-red-900" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="px-6 py-4 border-t border-gray-200">
                <?php echo e($companies->links()); ?>

            </div>
        <?php else: ?>
            <div class="text-center py-12">
                <i class="fas fa-building text-gray-400 text-4xl mb-4"></i>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No companies found</h3>
                <p class="text-gray-500 mb-6">Get started by creating your first company.</p>
                <a href="<?php echo e(route('companies.create')); ?>" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-lg font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                    <i class="fas fa-plus mr-2"></i>
                    Add Company
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
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
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.dashboard-base', ['user' => $user, 'activeRoute' => 'companies.index'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\Aeternitas-System-V2\resources\views/companies/index.blade.php ENDPATH**/ ?>
<?php $__env->startSection('title', 'Period Management'); ?>

<?php $__env->startSection('content'); ?>
<div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white shadow-sm border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="py-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">
                            <?php if($user->role === 'employee'): ?>
                                My Periods
                            <?php else: ?>
                                Period Management
                            <?php endif; ?>
                        </h1>
                        <p class="mt-1 text-sm text-gray-600">
                            <?php if($user->role === 'employee'): ?>
                                View periods that include your attendance records
                            <?php else: ?>
                                Manage and analyze employee attendance records by time periods
                            <?php endif; ?>
                        </p>
                    </div>
                    <div class="flex space-x-3">
                        <?php if($user->role !== 'employee'): ?>
                        <a href="<?php echo e(route('attendance.period-management.create')); ?>" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-lg font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                            <i class="fas fa-plus mr-2"></i>
                            Create New Period
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <?php if(session('success')): ?>
            <div class="mb-6 bg-green-50 border border-green-200 rounded-lg p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-check-circle text-green-400"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-green-800"><?php echo e(session('success')); ?></p>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if(session('error')): ?>
            <div class="mb-6 bg-red-50 border border-red-200 rounded-lg p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-exclamation-circle text-red-400"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-red-800"><?php echo e(session('error')); ?></p>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Search and Filter Bar -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
            <div class="p-4">
                <div class="flex flex-col sm:flex-row gap-4">
                    <!-- Search Input -->
                    <div class="flex-1">
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-search text-gray-400"></i>
                            </div>
                            <input type="text" id="searchInput" placeholder="Search periods by name..." 
                                   class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>
                    
                    <!-- Filter Options -->
                    <div class="flex gap-2">
                        <select id="filterByDuration" class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">All Durations</option>
                            <option value="1-7">1-7 days</option>
                            <option value="8-30">8-30 days</option>
                            <option value="31-90">31-90 days</option>
                            <option value="90+">90+ days</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <?php if(empty($periods)): ?>
            <!-- Empty State -->
            <div class="text-center py-12">
                <div class="mx-auto h-24 w-24 text-gray-400">
                    <i class="fas fa-calendar-alt text-6xl"></i>
                </div>
                <h3 class="mt-4 text-lg font-medium text-gray-900">
                    <?php if($user->role === 'employee'): ?>
                        No periods found
                    <?php else: ?>
                        No periods created yet
                    <?php endif; ?>
                </h3>
                <p class="mt-2 text-sm text-gray-600">
                    <?php if($user->role === 'employee'): ?>
                        You are not currently included in any active periods.
                    <?php else: ?>
                        Get started by creating your first period to analyze attendance records.
                    <?php endif; ?>
                </p>
                <div class="mt-6">
                    <?php if($user->role !== 'employee'): ?>
                    <a href="<?php echo e(route('attendance.period-management.create')); ?>" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-lg font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                        <i class="fas fa-plus mr-2"></i>
                        Create Your First Period
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        <?php else: ?>
            <!-- Periods Table -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Period Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date Range</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created At</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="periodsTableBody" class="bg-white divide-y divide-gray-200">
                            <?php $__currentLoopData = $periods; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $period): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr class="period-row hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900"><?php echo e($period->name); ?></div>
                                        <?php if($period->description): ?>
                                            <div class="text-xs text-gray-500 mt-1"><?php echo e($period->description); ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">
                                            <?php echo e($period->start_date->format('M j, Y')); ?> - 
                                            <?php echo e($period->end_date->format('M j, Y')); ?>

                                        </div>
                                        <div class="text-xs text-gray-500">
                                            <?php echo e($period->duration); ?> days
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">
                                            <?php echo e($period->created_at->format('M j, Y g:i A')); ?>

                                        </div>
                                        <div class="text-xs text-gray-500">
                                            by <?php echo e($period->created_by); ?>

                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex items-center justify-end gap-2">
                                            <a href="<?php echo e(route('attendance.period-management.show', $period->id)); ?>" 
                                               class="inline-flex items-center px-3 py-1.5 bg-blue-100 text-blue-700 rounded-md text-sm font-medium hover:bg-blue-200 transition-colors">
                                                <i class="fas fa-eye mr-1"></i>
                                                View
                                            </a>
                                            
                                            <?php if($user->role !== 'employee'): ?>
                                            <button type="button" onclick="openDeleteModal('<?php echo e($period->id); ?>', '<?php echo e(addslashes($period->name)); ?>')" 
                                                    class="inline-flex items-center px-3 py-1.5 bg-red-100 text-red-700 rounded-md text-sm font-medium hover:bg-red-200 transition-colors">
                                                <i class="fas fa-trash mr-1"></i>
                                                Delete
                                            </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- No Results Message -->
                <div id="noResults" class="hidden text-center py-8">
                    <div class="mx-auto h-16 w-16 text-gray-400">
                        <i class="fas fa-search text-4xl"></i>
                    </div>
                    <h3 class="mt-4 text-lg font-medium text-gray-900">No periods found</h3>
                    <p class="mt-2 text-sm text-gray-600">Try adjusting your search criteria.</p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const filterByDuration = document.getElementById('filterByDuration');
    const periodsTableBody = document.getElementById('periodsTableBody');
    const noResults = document.getElementById('noResults');
    
    // Store all period rows for filtering
    const allPeriodRows = Array.from(document.querySelectorAll('.period-row'));
    
    function filterPeriods() {
        const searchTerm = searchInput.value.toLowerCase();
        const durationFilter = filterByDuration.value;
        
        let visibleRows = 0;
        
        allPeriodRows.forEach(row => {
            let showRow = true;
            
            // Search filter
            if (searchTerm) {
                const name = row.querySelector('td:first-child').textContent.toLowerCase();
                
                if (!name.includes(searchTerm)) {
                    showRow = false;
                }
            }
            
            // Duration filter
            if (durationFilter && showRow) {
                const durationText = row.querySelector('td:nth-child(2)').textContent;
                const durationMatch = durationText.match(/(\d+) days/);
                const duration = durationMatch ? parseInt(durationMatch[1]) : 0;
                
                switch (durationFilter) {
                    case '1-7':
                        if (duration < 1 || duration > 7) showRow = false;
                        break;
                    case '8-30':
                        if (duration < 8 || duration > 30) showRow = false;
                        break;
                    case '31-90':
                        if (duration < 31 || duration > 90) showRow = false;
                        break;
                    case '90+':
                        if (duration <= 90) showRow = false;
                        break;
                }
            }
            
            if (showRow) {
                row.style.display = '';
                visibleRows++;
            } else {
                row.style.display = 'none';
            }
        });
        
        // Show/hide no results message
        if (visibleRows === 0) {
            periodsTableBody.style.display = 'none';
            noResults.classList.remove('hidden');
        } else {
            periodsTableBody.style.display = '';
            noResults.classList.add('hidden');
        }
    }
    
    // Add event listeners
    searchInput.addEventListener('input', filterPeriods);
    filterByDuration.addEventListener('change', filterPeriods);
    
    // Initial filter
    filterPeriods();
});

// Delete Modal Functions
function openDeleteModal(periodId, periodName) {
    document.getElementById('deletePeriodId').value = periodId;
    document.getElementById('deletePeriodName').textContent = periodName;
    document.getElementById('deleteModal').classList.remove('hidden');
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.add('hidden');
}

function confirmDelete() {
    const periodId = document.getElementById('deletePeriodId').value;
    
    // Show loading state
    const confirmBtn = document.getElementById('confirmDeleteBtn');
    const originalText = confirmBtn.innerHTML;
    confirmBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Deleting...';
    confirmBtn.disabled = true;
    
    // Create and submit form with proper route
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = `/attendance/period-management/${periodId}`;
    
    // Add CSRF token
    const csrfToken = document.createElement('input');
    csrfToken.type = 'hidden';
    csrfToken.name = '_token';
    csrfToken.value = '<?php echo e(csrf_token()); ?>';
    form.appendChild(csrfToken);
    
    // Add method override for DELETE
    const methodField = document.createElement('input');
    methodField.type = 'hidden';
    methodField.name = '_method';
    methodField.value = 'DELETE';
    form.appendChild(methodField);
    
    // Submit the form
    document.body.appendChild(form);
    form.submit();
}
</script>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-6 border w-96 shadow-lg rounded-lg bg-white">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                    <i class="fas fa-exclamation-triangle mr-2 text-red-600"></i>
                    Delete Period
                </h3>
            </div>
            <button onclick="closeDeleteModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        
        <div class="mb-6">
            <p class="text-gray-700">
                Are you sure you want to delete the period <span id="deletePeriodName" class="font-semibold text-red-600"></span>?
            </p>
            <p class="text-sm text-gray-500 mt-2">
                <i class="fas fa-info-circle mr-1"></i>
                This action cannot be undone. All period data and analysis will be permanently removed.
            </p>
        </div>
        
        <div class="flex justify-end space-x-3">
            <button onclick="closeDeleteModal()" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
                Cancel
            </button>
            <button id="confirmDeleteBtn" onclick="confirmDelete()" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                <i class="fas fa-trash mr-2"></i>
                Delete Period
            </button>
        </div>
        
        <!-- Hidden input to store period ID -->
        <input type="hidden" id="deletePeriodId">
    </div>
</div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.dashboard-base', ['user' => $user, 'activeRoute' => 'attendance.period-management.index'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\caior\Documents\Mica files\Aeternitas-System-V2\resources\views/attendance/period-management/index.blade.php ENDPATH**/ ?>
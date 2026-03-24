<?php $__env->startSection('title', 'Previous Employer & Other'); ?>

<?php $__env->startSection('content'); ?>
<div class="max-w-5xl mx-auto space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Previous Employer & Other</h1>
        <p class="mt-1 text-sm text-gray-500">Employee previous employment information</p>
    </div>

    <?php if(isset($hasPreviousEmploymentsTable) && !$hasPreviousEmploymentsTable): ?>
        <div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-amber-800 text-sm">
            Previous employment database table is not ready. Run migrations to enable loading and saving data.
        </div>
    <?php endif; ?>

    <form method="GET" action="<?php echo e(route('employees.prev-emp-oth')); ?>" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="md:col-span-2">
                <label for="employee_picker" class="block text-sm font-medium text-gray-700 mb-2">Employee Name</label>
                <select id="employee_picker" name="employee_id" onchange="this.form.submit()" class="w-full h-10 px-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="">Select employee</option>
                    <?php $__currentLoopData = $employees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $emp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e(data_get($emp, 'id')); ?>" data-empno="<?php echo e(data_get($emp, 'employee_id')); ?>" <?php echo e(($selectedEmployeeId ?? '') === data_get($emp, 'id') ? 'selected' : ''); ?>>
                            <?php echo e(data_get($emp, 'first_name')); ?> <?php echo e(data_get($emp, 'last_name')); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div>
                <label for="empno" class="block text-sm font-medium text-gray-700 mb-2">Empno</label>
                <input type="text" id="empno" class="w-full h-10 px-3 border border-gray-300 rounded-lg bg-gray-50" value="<?php echo e(old('empno', data_get($selectedEmployee, 'employee_id'))); ?>" readonly>
            </div>
        </div>
    </form>

    <form method="POST" action="<?php echo e(route('employees.prev-emp-oth.save')); ?>" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 space-y-6">
        <?php echo csrf_field(); ?>
        <input type="hidden" name="employee_id" value="<?php echo e(old('employee_id', $selectedEmployeeId)); ?>">

        <div class="border border-gray-200 rounded-lg p-5">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Previous Employment</h2>

            <div class="space-y-6">
                <?php for($i = 1; $i <= 5; $i++): ?>
                    <?php
                        $row = data_get($prefilledRows, $i);
                    ?>
                    <div class="border border-gray-100 rounded-lg p-4 space-y-3">
                        <div class="flex items-center gap-2">
                            <label class="w-52 shrink-0 text-sm font-medium text-gray-700 whitespace-nowrap text-right">Previous Employment <?php echo e($i); ?></label>
                            <span class="text-gray-500 shrink-0">:</span>
                            <input type="text" name="rows[<?php echo e($i - 1); ?>][employment_name]" class="w-full h-10 px-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" value="<?php echo e(old('rows.' . ($i - 1) . '.employment_name', data_get($row, 'employment_name'))); ?>" placeholder="Enter Previous Employment <?php echo e($i); ?>">
                        </div>

                        <div class="flex items-center gap-2">
                            <label class="w-52 shrink-0 text-sm font-medium text-gray-700 whitespace-nowrap text-right">Position</label>
                            <span class="text-gray-500 shrink-0">:</span>
                            <input type="text" name="rows[<?php echo e($i - 1); ?>][position]" class="w-full h-10 px-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" value="<?php echo e(old('rows.' . ($i - 1) . '.position', data_get($row, 'position'))); ?>" placeholder="Enter Position">
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="flex items-center gap-2">
                                <label class="w-52 shrink-0 text-sm font-medium text-gray-700 whitespace-nowrap text-right">Date Started</label>
                                <span class="text-gray-500 shrink-0">:</span>
                                <div class="grid grid-cols-2 gap-2 w-full">
                                    <select name="rows[<?php echo e($i - 1); ?>][start_month]" class="w-full h-10 px-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                        <option value="">Month</option>
                                        <?php for($m = 1; $m <= 12; $m++): ?>
                                            <option value="<?php echo e($m); ?>" <?php echo e((string) old('rows.' . ($i - 1) . '.start_month', data_get($row, 'start_month')) === (string) $m ? 'selected' : ''); ?>><?php echo e(\Carbon\Carbon::create()->month($m)->format('F')); ?></option>
                                        <?php endfor; ?>
                                    </select>
                                    <select name="rows[<?php echo e($i - 1); ?>][start_year]" class="w-full h-10 px-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                        <option value="">Year</option>
                                        <?php $__currentLoopData = $years; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $year): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($year); ?>" <?php echo e((string) old('rows.' . ($i - 1) . '.start_year', data_get($row, 'start_year')) === (string) $year ? 'selected' : ''); ?>><?php echo e($year); ?></option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                </div>
                            </div>

                            <div class="flex items-center gap-2">
                                <label class="w-52 shrink-0 text-sm font-medium text-gray-700 whitespace-nowrap text-right">Date Ended</label>
                                <span class="text-gray-500 shrink-0">:</span>
                                <div class="grid grid-cols-2 gap-2 w-full">
                                    <select name="rows[<?php echo e($i - 1); ?>][end_month]" class="w-full h-10 px-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                        <option value="">Month</option>
                                        <?php for($m = 1; $m <= 12; $m++): ?>
                                            <option value="<?php echo e($m); ?>" <?php echo e((string) old('rows.' . ($i - 1) . '.end_month', data_get($row, 'end_month')) === (string) $m ? 'selected' : ''); ?>><?php echo e(\Carbon\Carbon::create()->month($m)->format('F')); ?></option>
                                        <?php endfor; ?>
                                    </select>
                                    <select name="rows[<?php echo e($i - 1); ?>][end_year]" class="w-full h-10 px-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                        <option value="">Year</option>
                                        <?php $__currentLoopData = $years; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $year): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($year); ?>" <?php echo e((string) old('rows.' . ($i - 1) . '.end_year', data_get($row, 'end_year')) === (string) $year ? 'selected' : ''); ?>><?php echo e($year); ?></option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endfor; ?>
            </div>
        </div>

        <div class="flex justify-end">
            <button type="submit" class="inline-flex items-center px-6 h-10 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors" <?php echo e(empty($selectedEmployeeId) ? 'disabled' : ''); ?>>
                Save
            </button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const picker = document.getElementById('employee_picker');
    const empnoInput = document.getElementById('empno');

    if (!picker || !empnoInput) {
        return;
    }

    const selectedOption = picker.options[picker.selectedIndex];
    empnoInput.value = selectedOption ? (selectedOption.getAttribute('data-empno') || empnoInput.value) : empnoInput.value;
});
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.dashboard-base', ['user' => $user, 'activeRoute' => 'employees.prev-emp-oth'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\internship\Aeternitas-Desktop app\backend\resources\views/employees/prev-emp-oth.blade.php ENDPATH**/ ?>
<?php $__env->startSection('title', 'Other Employee Info'); ?>

<?php $__env->startSection('content'); ?>
<div class="max-w-6xl mx-auto space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Other Employee Info</h1>
        <p class="mt-1 text-sm text-gray-500">Additional employee profile details</p>
    </div>

    <?php if(isset($hasOtherInfoTable) && !$hasOtherInfoTable): ?>
        <div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-amber-800 text-sm">
            Other employee info database table is not ready. Run migrations to enable loading and saving data.
        </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <form method="GET" action="<?php echo e(route('employees.other-employee-info')); ?>" class="lg:col-span-2 bg-white rounded-lg shadow-sm border border-gray-200 p-6">
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

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h2 class="text-sm font-semibold text-gray-800 mb-3">Employee Photo</h2>
            <div class="w-full h-52 border border-gray-300 rounded-lg bg-gray-50 overflow-hidden flex items-center justify-center mb-3">
                <?php if(data_get($otherInfo, 'photo_path')): ?>
                    <img src="<?php echo e(asset('storage/' . data_get($otherInfo, 'photo_path'))); ?>" alt="Employee Photo" class="w-full h-full object-cover">
                <?php else: ?>
                    <span class="text-sm text-gray-500">No photo uploaded</span>
                <?php endif; ?>
            </div>

            <?php if($user->role === 'admin'): ?>
                <form method="POST" action="<?php echo e(route('employees.other-employee-info.photo')); ?>" enctype="multipart/form-data" class="space-y-2">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="employee_id" value="<?php echo e($selectedEmployeeId); ?>">
                    <input type="file" name="photo" accept="image/*" class="block w-full text-sm text-gray-700">
                    <button type="submit" class="w-full h-10 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors" <?php echo e(empty($selectedEmployeeId) || (isset($hasOtherInfoTable) && !$hasOtherInfoTable) ? 'disabled' : ''); ?>>
                        Upload / Change Photo
                    </button>
                </form>

                <form method="POST" action="<?php echo e(route('employees.other-employee-info.photo.clear')); ?>" class="mt-2">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="employee_id" value="<?php echo e($selectedEmployeeId); ?>">
                    <button type="submit" class="w-full h-10 border border-red-300 text-red-600 text-sm font-medium rounded-lg hover:bg-red-50 transition-colors" <?php echo e(empty($selectedEmployeeId) || (isset($hasOtherInfoTable) && !$hasOtherInfoTable) ? 'disabled' : ''); ?>>
                        Clear / Remove Photo
                    </button>
                </form>
            <?php else: ?>
                <p class="text-xs text-gray-500">Only admin can upload or remove photo.</p>
            <?php endif; ?>
        </div>
    </div>

    <form method="POST" action="<?php echo e(route('employees.other-employee-info.save')); ?>" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 space-y-8">
        <?php echo csrf_field(); ?>
        <input type="hidden" name="employee_id" value="<?php echo e(old('employee_id', $selectedEmployeeId)); ?>">

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Address</label>
                <input type="text" name="address" value="<?php echo e(old('address', data_get($otherInfo, 'address'))); ?>" class="w-full h-10 px-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Pov Address</label>
                <input type="text" name="pov_address" value="<?php echo e(old('pov_address', data_get($otherInfo, 'pov_address'))); ?>" class="w-full h-10 px-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">No street</label>
                <input type="text" name="no_street" value="<?php echo e(old('no_street', data_get($otherInfo, 'no_street'))); ?>" class="w-full h-10 px-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Barangay</label>
                <input type="text" name="barangay" value="<?php echo e(old('barangay', data_get($otherInfo, 'barangay'))); ?>" class="w-full h-10 px-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Town/District</label>
                <input type="text" name="town_district" value="<?php echo e(old('town_district', data_get($otherInfo, 'town_district'))); ?>" class="w-full h-10 px-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">City/province</label>
                <input type="text" name="city_province" value="<?php echo e(old('city_province', data_get($otherInfo, 'city_province'))); ?>" class="w-full h-10 px-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Birthplace</label>
                <input type="text" name="birthplace" value="<?php echo e(old('birthplace', data_get($otherInfo, 'birthplace'))); ?>" class="w-full h-10 px-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Religion</label>
                <input type="text" name="religion" value="<?php echo e(old('religion', data_get($otherInfo, 'religion'))); ?>" class="w-full h-10 px-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Blood Type</label>
                <input type="text" name="blood_type" value="<?php echo e(old('blood_type', data_get($otherInfo, 'blood_type'))); ?>" class="w-full h-10 px-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Citezenship</label>
                <input type="text" name="citizenship" value="<?php echo e(old('citizenship', data_get($otherInfo, 'citizenship'))); ?>" class="w-full h-10 px-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Height</label>
                <input type="text" name="height" value="<?php echo e(old('height', data_get($otherInfo, 'height'))); ?>" class="w-full h-10 px-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Weight</label>
                <input type="text" name="weight" value="<?php echo e(old('weight', data_get($otherInfo, 'weight'))); ?>" class="w-full h-10 px-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Phone</label>
                <input type="text" name="phone" value="<?php echo e(old('phone', data_get($otherInfo, 'phone'))); ?>" class="w-full h-10 px-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Mobile</label>
                <input type="text" name="mobile" value="<?php echo e(old('mobile', data_get($otherInfo, 'mobile'))); ?>" class="w-full h-10 px-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Driver's License</label>
                <input type="text" name="drivers_license" value="<?php echo e(old('drivers_license', data_get($otherInfo, 'drivers_license'))); ?>" class="w-full h-10 px-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Prc No:</label>
                <input type="text" name="prc_no" value="<?php echo e(old('prc_no', data_get($otherInfo, 'prc_no'))); ?>" class="w-full h-10 px-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
        </div>

        <div class="pt-2">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Parent's / spouse</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Father</label>
                    <input type="text" name="father" value="<?php echo e(old('father', data_get($otherInfo, 'father'))); ?>" class="w-full h-10 px-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Mother</label>
                    <input type="text" name="mother" value="<?php echo e(old('mother', data_get($otherInfo, 'mother'))); ?>" class="w-full h-10 px-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Spouse</label>
                    <div class="flex items-center gap-4">
                        <input type="text" name="spouse" value="<?php echo e(old('spouse', data_get($otherInfo, 'spouse'))); ?>" class="flex-1 h-10 px-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <label class="inline-flex items-center gap-2 text-sm text-gray-700 whitespace-nowrap">
                            <input type="checkbox" name="spouse_employed" value="1" <?php echo e(old('spouse_employed', data_get($otherInfo, 'spouse_employed')) ? 'checked' : ''); ?> class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            <span>Spouse employed</span>
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex justify-end">
            <button type="submit" class="inline-flex items-center px-6 h-10 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors" <?php echo e(empty($selectedEmployeeId) || (isset($hasOtherInfoTable) && !$hasOtherInfoTable) ? 'disabled' : ''); ?>>
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

<?php echo $__env->make('layouts.dashboard-base', ['user' => $user, 'activeRoute' => 'employees.other-employee-info'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\internship\Aeternitas-Desktop app\backend\resources\views/employees/other-employee-info.blade.php ENDPATH**/ ?>
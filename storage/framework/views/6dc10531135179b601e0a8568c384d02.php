<?php $__env->startSection('title', 'Import DTR'); ?>

<?php $__env->startSection('content'); ?>
<div class="space-y-6">
    <!-- Success/Error Messages -->
    <?php if(session('success')): ?>
        <div class="bg-green-50 border border-green-200 rounded-lg p-4">
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
        <div class="bg-red-50 border border-red-200 rounded-lg p-4">
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

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Import DTR</h1>
            <p class="mt-1 text-sm text-gray-600">Upload and review Daily Time Records before importing to timekeeping</p>
        </div>
        <div class="mt-4 sm:mt-0">
            <a href="<?php echo e(route('attendance.timekeeping')); ?>" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                <i class="fas fa-arrow-left mr-2"></i>
                Back to Timekeeping
            </a>
        </div>
    </div>

    <!-- File Upload Section -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="p-4 sm:p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Upload DTR File</h3>
            
            <form method="POST" action="<?php echo e(route('attendance.import-dtr.process')); ?>" enctype="multipart/form-data" class="space-y-6">
                <?php echo csrf_field(); ?>
                
                <!-- File Upload Area -->
                <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-gray-400 transition-colors">
                    <div class="space-y-4">
                        <div class="mx-auto w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-file-excel text-blue-600 text-xl"></i>
                        </div>
                        
                        <div>
                            <label for="dtr_file" class="cursor-pointer">
                                <span class="text-lg font-medium text-gray-900">Choose DTR file to upload</span>
                                <p class="text-sm text-gray-500 mt-1">Excel files (.xlsx, .xls) and CSV files (.csv) up to 10MB</p>
                            </label>
                            <input type="file" name="dtr_file" id="dtr_file" accept=".xlsx,.xls,.csv" required
                                class="hidden" onchange="handleFileSelect(this)">
                        </div>
                        
                        <div id="file-info" class="hidden">
                            <div class="flex items-center justify-center space-x-2 text-sm text-gray-600">
                                <i class="fas fa-file text-blue-500"></i>
                                <span id="file-name"></span>
                                <span id="file-size" class="text-gray-400"></span>
                            </div>
                        </div>
                    </div>
                </div>

                <?php $__errorArgs = ['dtr_file'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <p class="text-sm text-red-600"><?php echo e($message); ?></p>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>

                <!-- File Format Instructions -->
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-info-circle text-blue-400"></i>
                        </div>
                <div class="ml-3">
                    <h4 class="text-sm font-medium text-blue-800">File Format Instructions</h4>
                    <div class="mt-2 text-sm text-blue-700">
                        <p><strong>Supported formats:</strong> Excel files (.xlsx, .xls) and CSV files (.csv)</p>
                        <div class="mt-3 p-3 bg-yellow-100 rounded-lg">
                            <p class="text-xs font-medium text-yellow-800">📋 For Excel files:</p>
                            <p class="text-xs text-yellow-700 mt-1">If you get an error, please save your Excel file as CSV format (.csv) and try again.</p>
                        </div>
                        <div class="mt-3 p-3 bg-blue-100 rounded-lg">
                            <p class="text-xs font-medium text-blue-800">💡 Expected format:</p>
                            <p class="text-xs text-blue-700 mt-1">Employee ID, Date, Time entries (multiple per day)</p>
                        </div>
                    </div>
                </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200">
                    <button type="button" onclick="clearFile()" class="px-4 py-2 border border-gray-300 rounded-lg font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                        Clear
                    </button>
                    <button type="submit" id="upload-btn" disabled
                        class="px-4 py-2 bg-blue-600 border border-transparent rounded-lg font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                        <i class="fas fa-upload mr-2"></i>
                        Upload & Review
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Preview Section (Hidden initially) -->
    <div id="preview-section" class="hidden bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="p-4 sm:p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">DTR Preview</h3>
            <p class="text-sm text-gray-600 mb-4">Review the imported data before confirming the import to timekeeping system.</p>
            
            <!-- Preview Table Placeholder -->
            <div class="border border-gray-200 rounded-lg overflow-hidden">
                <div class="bg-gray-50 px-4 py-3 border-b border-gray-200">
                    <h4 class="text-sm font-medium text-gray-900">Imported Records (Preview)</h4>
                </div>
                <div class="p-4">
                    <div class="text-center py-8">
                        <i class="fas fa-table text-gray-400 text-3xl mb-3"></i>
                        <p class="text-gray-500">Upload a file to see the preview</p>
                    </div>
                </div>
            </div>

            <!-- Preview Actions -->
            <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200 mt-4">
                <button type="button" class="px-4 py-2 border border-gray-300 rounded-lg font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                    Cancel
                </button>
                <button type="button" class="px-4 py-2 bg-green-600 border border-transparent rounded-lg font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors">
                    <i class="fas fa-check mr-2"></i>
                    Confirm Import
                </button>
            </div>
        </div>
    </div>

    <!-- Recent Imports Section -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="p-4 sm:p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">Recent Imports</h3>
                <a href="<?php echo e(route('attendance.temp-timekeeping')); ?>" class="text-sm text-blue-600 hover:text-blue-800 font-medium">
                    View All →
                </a>
            </div>
            
            <?php if($recentImports->count() > 0): ?>
                <div class="space-y-3">
                    <?php $__currentLoopData = $recentImports; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $import): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                            <div class="flex items-center space-x-3">
                                <div class="w-8 h-8 <?php echo e($import['pending_records'] > 0 ? 'bg-yellow-100' : 'bg-green-100'); ?> rounded-lg flex items-center justify-center">
                                    <i class="fas <?php echo e($import['pending_records'] > 0 ? 'fa-clock text-yellow-600' : 'fa-check text-green-600'); ?>"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">
                                        Batch: <?php echo e(substr($import['batch_id'], 0, 20)); ?>...
                                    </p>
                                    <p class="text-xs text-gray-500">
                                        <?php echo e($import['total_records']); ?> records • 
                                        <?php echo e($import['employees']); ?> employees • 
                                        <?php echo e($import['created_at']->diffForHumans()); ?>

                                    </p>
                                    <p class="text-xs text-gray-500">
                                        Date range: <?php echo e(\Carbon\Carbon::parse($import['date_range']['start'])->format('M d')); ?> - <?php echo e(\Carbon\Carbon::parse($import['date_range']['end'])->format('M d, Y')); ?>

                                    </p>
                                </div>
                            </div>
                            <div class="flex items-center space-x-2">
                                <?php if($import['pending_records'] > 0): ?>
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                        <?php echo e($import['pending_records']); ?> Pending
                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        All Processed
                                    </span>
                                <?php endif; ?>
                                
                                <a href="<?php echo e(route('attendance.temp-timekeeping')); ?>?batch=<?php echo e($import['batch_id']); ?>" 
                                   class="text-gray-400 hover:text-gray-600" 
                                   title="View Details">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            <?php else: ?>
                <div class="text-center py-8">
                    <i class="fas fa-database text-gray-400 text-3xl mb-3"></i>
                    <h4 class="text-sm font-medium text-gray-900 mb-1">No Recent Imports</h4>
                    <p class="text-sm text-gray-500">Upload DTR files to see recent imports here</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function handleFileSelect(input) {
    const file = input.files[0];
    if (file) {
        const fileInfo = document.getElementById('file-info');
        const fileName = document.getElementById('file-name');
        const fileSize = document.getElementById('file-size');
        const uploadBtn = document.getElementById('upload-btn');
        
        fileName.textContent = file.name;
        fileSize.textContent = `(${formatFileSize(file.size)})`;
        fileInfo.classList.remove('hidden');
        uploadBtn.disabled = false;
        
        // Show preview section
        document.getElementById('preview-section').classList.remove('hidden');
    }
}

function clearFile() {
    const fileInput = document.getElementById('dtr_file');
    const fileInfo = document.getElementById('file-info');
    const uploadBtn = document.getElementById('upload-btn');
    const previewSection = document.getElementById('preview-section');
    
    fileInput.value = '';
    fileInfo.classList.add('hidden');
    uploadBtn.disabled = true;
    previewSection.classList.add('hidden');
}

function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

// Drag and drop functionality
const dropArea = document.querySelector('.border-dashed');
const fileInput = document.getElementById('dtr_file');

dropArea.addEventListener('dragover', (e) => {
    e.preventDefault();
    dropArea.classList.add('border-blue-400', 'bg-blue-50');
});

dropArea.addEventListener('dragleave', (e) => {
    e.preventDefault();
    dropArea.classList.remove('border-blue-400', 'bg-blue-50');
});

dropArea.addEventListener('drop', (e) => {
    e.preventDefault();
    dropArea.classList.remove('border-blue-400', 'bg-blue-50');
    
    const files = e.dataTransfer.files;
    if (files.length > 0) {
        fileInput.files = files;
        handleFileSelect(fileInput);
    }
});
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.dashboard-base', ['user' => $user, 'activeRoute' => 'attendance.import-dtr'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\caior\Documents\Mica files\Aeternitas-System-V2\resources\views/attendance/import-dtr.blade.php ENDPATH**/ ?>
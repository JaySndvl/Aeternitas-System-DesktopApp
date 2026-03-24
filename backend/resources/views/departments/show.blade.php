@extends('layouts.dashboard-base', ['user' => $user, 'activeRoute' => 'departments.index'])

@section('title', 'Department Details')

@section('content')
<div class="max-w-6xl mx-auto">
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">{{ $department->name }}</h1>
                <p class="mt-1 text-sm text-gray-600">Department Details & Information</p>
            </div>
            <div class="mt-4 sm:mt-0 flex space-x-3">
                <a href="{{ route('departments.edit', $department) }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                    <i class="fas fa-edit mr-2"></i>
                    Edit Department
                </a>
                <a href="{{ route('departments.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Back to Departments
                </a>
            </div>
        </div>

        <!-- Department Information -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Info -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Department Details -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Department Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Department ID</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $department->department_id }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Department Name</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $department->name }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Location</label>
                            <p class="mt-1 text-sm text-gray-900 flex items-center">
                                <i class="fas fa-map-marker-alt mr-2 text-gray-400"></i>
                                {{ $department->location ?: 'Not specified' }}
                            </p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Annual Budget</label>
                            <p class="mt-1 text-lg font-semibold text-gray-900">₱{{ number_format($department->budget, 2) }}</p>
                        </div>
                    </div>
                </div>

                <!-- Description -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Description</h3>
                    <p class="text-sm text-gray-600 leading-relaxed">
                        {{ $department->description ?: 'No description provided for this department.' }}
                    </p>
                </div>

                <!-- Department Statistics -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Department Statistics</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                        <div class="text-center">
                            <div class="text-2xl font-bold text-blue-600">{{ $department->employees->count() }}</div>
                            <div class="text-sm text-gray-500">Total Employees</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-green-600">₱{{ number_format($department->employees->sum('salary'), 2) }}</div>
                            <div class="text-sm text-gray-500">Monthly Payroll</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-purple-600">₱{{ number_format($department->employees->sum('salary') * 12, 2) }}</div>
                            <div class="text-sm text-gray-500">Annual Payroll</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Quick Actions -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Quick Actions</h3>
                    <div class="space-y-3">
                        <a href="{{ route('departments.edit', $department) }}" class="w-full flex items-center justify-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition-colors">
                            <i class="fas fa-edit mr-2"></i>
                            Edit Department
                        </a>
                        <a href="{{ route('departments.employees', $department) }}" class="w-full flex items-center justify-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition-colors">
                            <i class="fas fa-users mr-2"></i>
                            View Employees
                        </a>
                        <button type="button" onclick="openDeleteModal('{{ $department->id }}', '{{ $department->name }}')" class="w-full flex items-center justify-center px-4 py-2 border border-red-300 rounded-lg text-sm font-medium text-red-700 bg-white hover:bg-red-50 transition-colors">
                            <i class="fas fa-trash mr-2"></i>
                            Delete Department
                        </button>
                    </div>
                </div>

                <!-- Recent Employees -->
                @if($department->employees->count() > 0)
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Recent Employees</h3>
                    <div class="space-y-3">
                        @foreach($department->employees->take(5) as $employee)
                        <div class="flex items-center space-x-3">
                            <div class="flex-shrink-0 h-8 w-8">
                                <div class="h-8 w-8 rounded-full bg-gradient-to-r from-blue-500 to-blue-600 flex items-center justify-center">
                                    <span class="text-xs font-medium text-white">
                                        {{ strtoupper(substr($employee->first_name, 0, 1) . substr($employee->last_name, 0, 1)) }}
                                    </span>
                                </div>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-medium text-gray-900 truncate">{{ $employee->full_name }}</p>
                                <p class="text-xs text-gray-500">{{ $employee->position }}</p>
                            </div>
                            <div class="flex-shrink-0">
                                <a href="{{ route('employees.show', $employee) }}" class="text-blue-600 hover:text-blue-900">
                                    <i class="fas fa-external-link-alt text-xs"></i>
                                </a>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @if($department->employees->count() > 5)
                    <div class="mt-4">
                        <a href="{{ route('departments.employees', $department) }}" class="text-sm text-blue-600 hover:text-blue-500">View all employees →</a>
                    </div>
                    @endif
                </div>
                @endif

                <!-- Department Info -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Department Info</h3>
                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-500">Created:</span>
                            <span class="text-gray-900">{{ $department->created_at->format('M d, Y') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Last Updated:</span>
                            <span class="text-gray-900">{{ $department->updated_at->format('M d, Y') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Budget Utilization:</span>
                            <span class="text-gray-900">
                                {{ $department->budget > 0 ? number_format(($department->employees->sum('salary') * 12 / $department->budget) * 100, 1) : 0 }}%
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Delete Modal Functions
function openDeleteModal(departmentId, departmentName) {
    document.getElementById('deleteDepartmentId').value = departmentId;
    document.getElementById('deleteDepartmentName').textContent = departmentName;
    document.getElementById('deleteForm').action = `/departments/${departmentId}`;
    document.getElementById('deleteModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.add('hidden');
    document.body.style.overflow = 'auto';
}

// Close modal when clicking outside
document.getElementById('deleteModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeDeleteModal();
    }
});

// Close modal with Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeDeleteModal();
    }
});
</script>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <!-- Background overlay -->
        <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" onclick="closeDeleteModal()"></div>

        <!-- Modal panel -->
        <div class="inline-block w-full max-w-md p-6 my-8 overflow-hidden text-left align-middle transition-all transform bg-white shadow-xl rounded-2xl">
            <!-- Modal header -->
            <div class="flex items-center justify-center w-12 h-12 mx-auto mb-4 bg-red-100 rounded-full">
                <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
            </div>

            <!-- Modal content -->
            <div class="text-center">
                <h3 class="text-lg font-medium text-gray-900 mb-2">Delete Department</h3>
                <p class="text-sm text-gray-500 mb-6">
                    Are you sure you want to delete <span id="deleteDepartmentName" class="font-semibold text-gray-900"></span>? 
                    This action cannot be undone and will permanently remove the department and all its data.
                </p>
            </div>

            <!-- Modal actions -->
            <div class="flex flex-col sm:flex-row gap-3 sm:gap-3">
                <button type="button" onclick="closeDeleteModal()" 
                    class="flex-1 px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-colors">
                    Cancel
                </button>
                <form id="deleteForm" method="POST" class="flex-1">
                    @csrf
                    @method('DELETE')
                    <input type="hidden" id="deleteDepartmentId" name="department_id" value="">
                    <button type="submit" 
                        class="w-full px-4 py-2 text-sm font-medium text-white bg-red-600 border border-transparent rounded-lg hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors">
                        Delete Department
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

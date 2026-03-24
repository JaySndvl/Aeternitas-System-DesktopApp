@extends('layouts.dashboard-base', ['user' => $user, 'activeRoute' => 'employees.index'])

@section('title', 'Employees')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Employees</h1>
            <p class="mt-1 text-sm text-gray-500">Manage your organization's employees</p>
        </div>
        <div class="mt-4 sm:mt-0">
            <a href="{{ route('employees.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-lg font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                <i class="fas fa-plus mr-2"></i>
                Add Employee
            </a>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <x-dashboard.stats-card 
            title="Total Employees" 
            :value="$employees->total()" 
            icon="fas fa-users" 
            color="blue" 
        />
        <x-dashboard.stats-card 
            title="Active Employees" 
            :value="$employees->where('account.is_active', true)->count()" 
            icon="fas fa-user-check" 
            color="green" 
        />
        <x-dashboard.stats-card 
            title="Departments" 
            :value="\App\Models\Department::count()" 
            icon="fas fa-building" 
            color="purple" 
        />
        <x-dashboard.stats-card 
            title="Avg Salary" 
            :value="'₱' . number_format($employees->avg('salary'), 2)" 
            icon="fas fa-money-bill-wave" 
            color="yellow" 
        />
    </div>

    <!-- Search and Filters -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 sm:p-6">
        <div class="flex flex-col lg:flex-row lg:items-center space-y-4 lg:space-y-0 lg:space-x-4">
            <!-- Search Input -->
            <div class="flex-1 lg:max-w-md">
                <div class="relative">
                    <input 
                        type="text" 
                        id="searchInput"
                        placeholder="Search employees..." 
                        class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
                    >
                    <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                </div>
            </div>
            
            <!-- Filters -->
            <div class="flex flex-col sm:flex-row space-y-3 sm:space-y-0 sm:space-x-3 lg:flex-shrink-0">
                <select id="departmentFilter" class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm">
                    <option value="">All Departments</option>
                    @foreach(\App\Models\Department::all() as $department)
                        <option value="{{ $department->id }}">{{ $department->name }}</option>
                    @endforeach
                </select>
                <select id="roleFilter" class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm">
                    <option value="">All Roles</option>
                    <option value="admin">Admin</option>
                    <option value="hr">HR</option>
                    <option value="manager">Manager</option>
                    <option value="employee">Employee</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Employees Table -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <!-- Desktop Table -->
        <div class="hidden lg:block overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Employee
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Employee ID
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Department
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Position
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Salary
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Status
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($employees as $employee)
                    <tr class="hover:bg-gray-50 transition-colors employee-row" 
                        data-name="{{ strtolower($employee->full_name) }}"
                        data-email="{{ strtolower($employee->account?->email ?? '') }}"
                        data-department="{{ $employee->department?->id ?? '' }}"
                        data-role="{{ $employee->account?->role ?? '' }}"
                        data-position="{{ strtolower($employee->position) }}">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10">
                                    <div class="h-10 w-10 rounded-full bg-gradient-to-r from-blue-500 to-blue-600 flex items-center justify-center">
                                        <span class="text-sm font-medium text-white">
                                            {{ strtoupper(substr($employee->first_name, 0, 1) . substr($employee->last_name, 0, 1)) }}
                                        </span>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900">
                                        {{ $employee->full_name }}
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        {{ $employee->account?->email ?? 'No email' }}
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ $employee->employee_id }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ $employee->department?->name ?? 'No department' }}</div>
                            <div class="text-sm text-gray-500">{{ $employee->department?->location ?? '' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ $employee->position }}</div>
                            <div class="text-sm text-gray-500">
                                {{ $employee->hire_date->format('M d, Y') }}
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">
                                ₱{{ number_format($employee->salary, 2) }}
                            </div>
                            <div class="text-sm text-gray-500">Monthly</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                {{ $employee->account?->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                <div class="w-1.5 h-1.5 rounded-full mr-1.5 {{ $employee->account?->is_active ? 'bg-green-400' : 'bg-red-400' }}"></div>
                                {{ $employee->account?->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex space-x-2">
                                <a href="{{ route('employees.show', $employee) }}" class="text-blue-600 hover:text-blue-900 transition-colors">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('employees.edit', $employee) }}" class="text-indigo-600 hover:text-indigo-900 transition-colors">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="{{ route('employees.payroll', $employee) }}" class="text-green-600 hover:text-green-900 transition-colors">
                                    <i class="fas fa-money-bill-wave"></i>
                                </a>
                                <button type="button" onclick="openDeleteModal('{{ $employee->id }}', '{{ $employee->full_name }}')" class="text-red-600 hover:text-red-900 transition-colors">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center">
                            <div class="text-gray-500">
                                <i class="fas fa-users text-4xl mb-4"></i>
                                <p class="text-lg font-medium">No employees found</p>
                                <p class="text-sm">Get started by adding your first employee.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Mobile Cards -->
        <div class="lg:hidden">
            @forelse($employees as $employee)
            <div class="border-b border-gray-200 p-4 hover:bg-gray-50 transition-colors employee-row" 
                 data-name="{{ strtolower($employee->full_name) }}"
                 data-email="{{ strtolower($employee->account?->email ?? '') }}"
                 data-department="{{ $employee->department?->id ?? '' }}"
                 data-role="{{ $employee->account?->role ?? '' }}"
                 data-position="{{ strtolower($employee->position) }}">
                <div class="flex items-start justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="flex-shrink-0 h-12 w-12">
                            <div class="h-12 w-12 rounded-full bg-gradient-to-r from-blue-500 to-blue-600 flex items-center justify-center">
                                <span class="text-sm font-medium text-white">
                                    {{ strtoupper(substr($employee->first_name, 0, 1) . substr($employee->last_name, 0, 1)) }}
                                </span>
                            </div>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="text-sm font-medium text-gray-900 truncate">
                                {{ $employee->full_name }}
                            </div>
                            <div class="text-sm text-gray-500 truncate">
                                {{ $employee->account?->email ?? 'No email' }}
                            </div>
                            <div class="text-sm text-gray-500">
                                <span class="font-medium">ID:</span> {{ $employee->employee_id }}
                            </div>
                            <div class="text-sm text-gray-500">
                                {{ $employee->department?->name ?? 'No department' }}
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center space-x-2">
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                            {{ $employee->account?->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            <div class="w-1.5 h-1.5 rounded-full mr-1 {{ $employee->account?->is_active ? 'bg-green-400' : 'bg-red-400' }}"></div>
                            {{ $employee->account?->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </div>
                </div>
                
                <div class="mt-3 grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <div class="text-gray-500">Position</div>
                        <div class="font-medium text-gray-900">{{ $employee->position }}</div>
                    </div>
                    <div>
                        <div class="text-gray-500">Salary</div>
                        <div class="font-medium text-gray-900">₱{{ number_format($employee->salary, 2) }}</div>
                    </div>
                </div>
                
                <div class="mt-3 flex justify-end space-x-2">
                    <a href="{{ route('employees.show', $employee) }}" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-blue-600 hover:text-blue-900 transition-colors">
                        <i class="fas fa-eye mr-1"></i>View
                    </a>
                    <a href="{{ route('employees.edit', $employee) }}" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-indigo-600 hover:text-indigo-900 transition-colors">
                        <i class="fas fa-edit mr-1"></i>Edit
                    </a>
                    <a href="{{ route('employees.payroll', $employee) }}" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-green-600 hover:text-green-900 transition-colors">
                        <i class="fas fa-money-bill-wave mr-1"></i>Payroll
                    </a>
                    <button type="button" onclick="openDeleteModal('{{ $employee->id }}', '{{ $employee->full_name }}')" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-red-600 hover:text-red-900 transition-colors">
                        <i class="fas fa-trash mr-1"></i>Delete
                    </button>
                </div>
            </div>
            @empty
            <div class="p-8 text-center">
                <div class="text-gray-500">
                    <i class="fas fa-users text-4xl mb-4"></i>
                    <p class="text-lg font-medium">No employees found</p>
                    <p class="text-sm">Get started by adding your first employee.</p>
                </div>
            </div>
            @endforelse
        </div>

        <!-- Pagination -->
        @if($employees->hasPages())
        <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
            {{ $employees->links() }}
        </div>
        @endif
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const departmentFilter = document.getElementById('departmentFilter');
    const roleFilter = document.getElementById('roleFilter');
    const employeeRows = document.querySelectorAll('.employee-row');

    function filterEmployees() {
        const searchTerm = searchInput.value.toLowerCase();
        const selectedDepartment = departmentFilter.value;
        const selectedRole = roleFilter.value;

        employeeRows.forEach(row => {
            const name = row.dataset.name || '';
            const email = row.dataset.email || '';
            const position = row.dataset.position || '';
            const department = row.dataset.department || '';
            const role = row.dataset.role || '';

            // Check search term
            const matchesSearch = searchTerm === '' || 
                name.includes(searchTerm) || 
                email.includes(searchTerm) || 
                position.includes(searchTerm);

            // Check department filter
            const matchesDepartment = selectedDepartment === '' || department === selectedDepartment;

            // Check role filter
            const matchesRole = selectedRole === '' || role === selectedRole;

            // Show/hide row based on all filters
            if (matchesSearch && matchesDepartment && matchesRole) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });

        // Update "No employees found" message
        updateNoResultsMessage();
    }

    function updateNoResultsMessage() {
        const visibleRows = Array.from(employeeRows).filter(row => row.style.display !== 'none');
        const noResultsMessage = document.querySelector('.no-results-message');
        
        if (visibleRows.length === 0) {
            if (!noResultsMessage) {
                // Create no results message
                const messageDiv = document.createElement('div');
                messageDiv.className = 'no-results-message p-8 text-center';
                messageDiv.innerHTML = `
                    <div class="text-gray-500">
                        <i class="fas fa-search text-4xl mb-4"></i>
                        <p class="text-lg font-medium">No employees found</p>
                        <p class="text-sm">Try adjusting your search or filters.</p>
                    </div>
                `;
                
                // Insert after the table/cards container
                const tableContainer = document.querySelector('.bg-white.rounded-lg.shadow-sm.border.border-gray-200.overflow-hidden');
                tableContainer.appendChild(messageDiv);
            }
        } else {
            if (noResultsMessage) {
                noResultsMessage.remove();
            }
        }
    }

    // Add event listeners
    searchInput.addEventListener('input', filterEmployees);
    departmentFilter.addEventListener('change', filterEmployees);
    roleFilter.addEventListener('change', filterEmployees);

    // Clear filters button (optional)
    function addClearFiltersButton() {
        const filtersContainer = document.querySelector('.flex.flex-col.sm\\:flex-row.space-y-3.sm\\:space-y-0.sm\\:space-x-3.lg\\:flex-shrink-0');
        
        const clearButton = document.createElement('button');
        clearButton.type = 'button';
        clearButton.className = 'px-3 py-2 text-sm text-gray-600 hover:text-gray-800 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors';
        clearButton.innerHTML = '<i class="fas fa-times mr-1"></i>Clear';
        
        clearButton.addEventListener('click', function() {
            searchInput.value = '';
            departmentFilter.value = '';
            roleFilter.value = '';
            filterEmployees();
        });
        
        filtersContainer.appendChild(clearButton);
    }

    // Add clear button
    addClearFiltersButton();
});

// Delete Modal Functions
function openDeleteModal(employeeId, employeeName) {
    document.getElementById('deleteEmployeeId').value = employeeId;
    document.getElementById('deleteEmployeeName').textContent = employeeName;
    document.getElementById('deleteForm').action = `/employees/${employeeId}`;
    document.getElementById('deleteModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden'; // Prevent background scrolling
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.add('hidden');
    document.body.style.overflow = 'auto'; // Restore scrolling
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
                <h3 class="text-lg font-medium text-gray-900 mb-2">Delete Employee</h3>
                <p class="text-sm text-gray-500 mb-6">
                    Are you sure you want to delete <span id="deleteEmployeeName" class="font-semibold text-gray-900"></span>? 
                    This action cannot be undone and will permanently remove all employee data including payroll records.
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
                    <input type="hidden" id="deleteEmployeeId" name="employee_id" value="">
                    <button type="submit" 
                        class="w-full px-4 py-2 text-sm font-medium text-white bg-red-600 border border-transparent rounded-lg hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors">
                        Delete Employee
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

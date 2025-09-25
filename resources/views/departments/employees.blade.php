@extends('layouts.dashboard-base', ['user' => $user, 'activeRoute' => 'departments.index'])

@section('title', 'Department Employees')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">{{ $department->name }} Employees</h1>
            <p class="mt-1 text-sm text-gray-600">All employees in the {{ $department->name }} department</p>
        </div>
        <div class="mt-4 sm:mt-0 flex space-x-3">
            <a href="{{ route('departments.show', $department) }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                <i class="fas fa-building mr-2"></i>
                Department Details
            </a>
            <a href="{{ route('departments.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                <i class="fas fa-arrow-left mr-2"></i>
                Back to Departments
            </a>
        </div>
    </div>

    <!-- Department Info Card -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center space-x-6">
                <div class="flex-shrink-0">
                    <div class="h-16 w-16 sm:h-20 sm:w-20 rounded-full bg-gradient-to-r from-blue-500 to-blue-600 flex items-center justify-center shadow-lg border-4 border-white">
                        <span class="text-xl sm:text-2xl font-bold text-white">
                            {{ strtoupper(substr($department->name, 0, 2)) }}
                        </span>
                    </div>
                </div>
                <div class="min-w-0 flex-1 space-y-2">
                    <h2 class="text-xl sm:text-2xl font-bold text-gray-900">{{ $department->name }}</h2>
                    <p class="text-base text-gray-600 font-medium">{{ $department->department_id }}</p>
                    <p class="text-sm text-gray-500 flex items-center">
                        <i class="fas fa-map-marker-alt mr-2"></i>
                        {{ $department->location ?: 'Location not specified' }}
                    </p>
                </div>
            </div>
            <div class="mt-6 sm:mt-0 text-right space-y-2">
                <div class="text-sm text-gray-500">Total Employees</div>
                <div class="text-2xl sm:text-3xl font-bold text-gray-900">{{ $employees->total() }}</div>
                <div class="text-sm text-gray-500">Annual Budget: ₱{{ number_format($department->budget, 2) }}</div>
            </div>
        </div>
    </div>

    <!-- Search and Filters -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 sm:p-6">
        <div class="flex flex-col sm:flex-row space-y-3 sm:space-y-0 sm:space-x-4">
            <div class="flex-1">
                <input type="text" id="searchInput" placeholder="Search employees..." 
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors">
            </div>
            <div class="flex flex-col sm:flex-row space-y-3 sm:space-y-0 sm:space-x-3 lg:flex-shrink-0">
                <select id="roleFilter" class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors">
                    <option value="">All Roles</option>
                    <option value="admin">Admin</option>
                    <option value="hr">HR</option>
                    <option value="manager">Manager</option>
                    <option value="employee">Employee</option>
                </select>
                <select id="statusFilter" class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors">
                    <option value="">All Status</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
                <button type="button" onclick="clearFilters()" class="px-3 py-2 text-sm text-gray-600 hover:text-gray-800 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                    <i class="fas fa-times mr-1"></i>Clear
                </button>
            </div>
        </div>
    </div>

    <!-- Employees List -->
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
                            Position
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Salary
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Hire Date
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
                        data-role="{{ $employee->account?->role ?? '' }}"
                        data-position="{{ strtolower($employee->position) }}"
                        data-status="{{ $employee->account?->is_active ? 'active' : 'inactive' }}">
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
                            <div class="text-sm text-gray-900">{{ $employee->position }}</div>
                            <div class="text-sm text-gray-500">{{ ucfirst($employee->account?->role ?? 'No role') }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">
                                ₱{{ number_format($employee->salary, 2) }}
                            </div>
                            <div class="text-sm text-gray-500">Monthly</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">
                                {{ $employee->hire_date->format('M d, Y') }}
                            </div>
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
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center">
                            <div class="text-gray-500">
                                <i class="fas fa-users text-4xl mb-4"></i>
                                <p class="text-lg font-medium">No employees found</p>
                                <p class="text-sm">This department doesn't have any employees yet.</p>
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
                 data-role="{{ $employee->account?->role ?? '' }}"
                 data-position="{{ strtolower($employee->position) }}"
                 data-status="{{ $employee->account?->is_active ? 'active' : 'inactive' }}">
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
                                {{ $employee->position }}
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
                        <div class="text-gray-500">Salary</div>
                        <div class="font-medium text-gray-900">₱{{ number_format($employee->salary, 2) }}</div>
                    </div>
                    <div>
                        <div class="text-gray-500">Hire Date</div>
                        <div class="font-medium text-gray-900">{{ $employee->hire_date->format('M d, Y') }}</div>
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
                </div>
            </div>
            @empty
            <div class="p-8 text-center">
                <div class="text-gray-500">
                    <i class="fas fa-users text-4xl mb-4"></i>
                    <p class="text-lg font-medium">No employees found</p>
                    <p class="text-sm">This department doesn't have any employees yet.</p>
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
    const roleFilter = document.getElementById('roleFilter');
    const statusFilter = document.getElementById('statusFilter');
    const employeeRows = document.querySelectorAll('.employee-row');

    function filterEmployees() {
        const searchTerm = searchInput.value.toLowerCase();
        const selectedRole = roleFilter.value;
        const selectedStatus = statusFilter.value;

        employeeRows.forEach(row => {
            const name = row.dataset.name || '';
            const email = row.dataset.email || '';
            const position = row.dataset.position || '';
            const role = row.dataset.role || '';
            const status = row.dataset.status || '';

            // Check search term
            const matchesSearch = searchTerm === '' || 
                name.includes(searchTerm) || 
                email.includes(searchTerm) || 
                position.includes(searchTerm);

            // Check role filter
            const matchesRole = selectedRole === '' || role === selectedRole;

            // Check status filter
            const matchesStatus = selectedStatus === '' || status === selectedStatus;

            // Show/hide row based on all filters
            if (matchesSearch && matchesRole && matchesStatus) {
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
    roleFilter.addEventListener('change', filterEmployees);
    statusFilter.addEventListener('change', filterEmployees);
});

// Clear filters function
function clearFilters() {
    document.getElementById('searchInput').value = '';
    document.getElementById('roleFilter').value = '';
    document.getElementById('statusFilter').value = '';
    
    // Trigger filter function
    const event = new Event('input');
    document.getElementById('searchInput').dispatchEvent(event);
}
</script>
@endsection

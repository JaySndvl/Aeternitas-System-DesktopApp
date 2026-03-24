@extends('layouts.dashboard-base', ['user' => $user, 'activeRoute' => 'attendance.period-management.index'])

@section('title', 'Create New Period')

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white shadow-sm border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="py-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Create New Period</h1>
                        <p class="mt-1 text-sm text-gray-600">Define a time period to analyze employee attendance records</p>
                    </div>
                    <div class="flex space-x-3">
                        <a href="{{ route('attendance.period-management.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                            <i class="fas fa-arrow-left mr-2"></i>
                            Back to Periods
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Form -->
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <form method="POST" action="{{ route('attendance.period-management.store') }}" class="p-6 space-y-6">
                @csrf
                
                <!-- Hidden inputs to preserve filter state -->
                @if(isset($currentFilters))
                    @foreach($currentFilters as $key => $value)
                        @if($value)
                            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                        @endif
                    @endforeach
                @endif
                
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Period Details Panel -->
                    <div class="space-y-4">
                        <div class="bg-gray-50 rounded-lg p-4">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">
                                <i class="fas fa-calendar-alt mr-2 text-blue-600"></i>
                                Period Details
                            </h3>
                            
                            <!-- Period Name -->
                            <div class="mb-4">
                                <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Period Name</label>
                                <input type="text" name="name" id="name" required 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('name') border-red-500 @enderror" 
                                       placeholder="e.g., Q1 2024, January 2024, Holiday Period"
                                       value="{{ old('name') }}">
                                @error('name')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Description -->
                            <div class="mb-4">
                                <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Description (Optional)</label>
                                <textarea name="description" id="description" rows="3" 
                                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('description') border-red-500 @enderror" 
                                          placeholder="Add a description for this period...">{{ old('description') }}</textarea>
                                @error('description')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Date Range -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label for="start_date" class="block text-sm font-medium text-gray-700 mb-2">Start Date</label>
                                    <input type="date" name="start_date" id="start_date" required 
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('start_date') border-red-500 @enderror" 
                                           value="{{ old('start_date') }}">
                                    @error('start_date')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="end_date" class="block text-sm font-medium text-gray-700 mb-2">End Date</label>
                                    <input type="date" name="end_date" id="end_date" required 
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('end_date') border-red-500 @enderror" 
                                           value="{{ old('end_date') }}">
                                    @error('end_date')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <!-- Period Preview -->
                            <div id="periodPreview" class="bg-blue-50 rounded-lg p-4 hidden mt-4">
                                <h4 class="text-sm font-medium text-blue-900 mb-2">Period Preview</h4>
                                <div class="text-sm text-blue-700">
                                    <p><strong>Duration:</strong> <span id="duration"></span> days</p>
                                    <p><strong>Date Range:</strong> <span id="dateRange"></span></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Employee Filter Panel -->
                    <div class="space-y-4">
                        <div class="bg-gray-50 rounded-lg p-4">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">
                                <i class="fas fa-filter mr-2 text-green-600"></i>
                                Employee Filter (Optional)
                            </h3>
                            <p class="text-sm text-gray-600 mb-4">Filter which employees to include in this period analysis. Leave empty to include all employees.</p>
                            
                            <!-- Department Selection -->
                            <div class="mb-4">
                                <label for="department_id" class="block text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-building mr-1"></i>Department
                                </label>
                                <select name="department_id" id="department_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">All Departments</option>
                                    @foreach($departments as $dept)
                                        <option value="{{ $dept->id }}" {{ old('department_id') == $dept->id ? 'selected' : '' }}>
                                            {{ $dept->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Employee Search -->
                            <div class="mb-4">
                                <label for="employeeSearch" class="block text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-search mr-1"></i>Search Employees
                                </label>
                                <input type="text" id="employeeSearch" placeholder="Search by name..." class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>

                            <!-- Select All Checkbox -->
                            <div class="flex items-center space-x-2 p-3 bg-blue-50 rounded-lg border border-blue-200 mb-4">
                                <input type="checkbox" id="selectAllEmployees" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                <label for="selectAllEmployees" class="text-sm font-medium text-blue-800 cursor-pointer">
                                    Select All Visible Employees
                                </label>
                            </div>

                            <!-- Employee List -->
                            <div class="max-h-64 overflow-y-auto border border-gray-200 rounded-lg bg-white">
                                <div id="employeeList" class="p-2">
                                    <p class="text-sm text-gray-500 text-center py-4">
                                        <i class="fas fa-info-circle mr-1"></i>Select a department to see employees
                                    </p>
                                </div>
                            </div>

                            <!-- Selected Count -->
                            <div id="selectedCount" class="hidden mt-3 p-2 bg-green-50 rounded-lg border border-green-200">
                                <div class="flex items-center">
                                    <i class="fas fa-check-circle text-green-600 mr-2"></i>
                                    <span id="countText" class="text-sm font-medium text-green-800"></span>
                                </div>
                            </div>
                            
                         
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="flex justify-end space-x-3 pt-6 border-t border-gray-200">
                    <a href="{{ route('attendance.period-management.index') }}" class="px-4 py-2 border border-gray-300 rounded-lg font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                        Cancel
                    </a>
                    <button type="submit" class="px-4 py-2 bg-blue-600 border border-transparent rounded-lg font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                        <i class="fas fa-save mr-2"></i>
                        Create Period
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Global variables for employee management
let allEmployees = @json($employees);
let filteredEmployees = [];
let selectedEmployees = new Set();

document.addEventListener('DOMContentLoaded', function() {
    const startDateInput = document.getElementById('start_date');
    const endDateInput = document.getElementById('end_date');
    const previewDiv = document.getElementById('periodPreview');
    const durationSpan = document.getElementById('duration');
    const dateRangeSpan = document.getElementById('dateRange');

    function updatePreview() {
        const startDate = startDateInput.value;
        const endDate = endDateInput.value;
        
        if (startDate && endDate) {
            const start = new Date(startDate);
            const end = new Date(endDate);
            
            if (end >= start) {
                const diffTime = Math.abs(end - start);
                const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
                
                durationSpan.textContent = diffDays;
                dateRangeSpan.textContent = `${start.toLocaleDateString()} - ${end.toLocaleDateString()}`;
                previewDiv.classList.remove('hidden');
            } else {
                previewDiv.classList.add('hidden');
            }
        } else {
            previewDiv.classList.add('hidden');
        }
    }

    startDateInput.addEventListener('change', updatePreview);
    endDateInput.addEventListener('change', updatePreview);
    
    // Initial preview if values are already set
    updatePreview();
});

// Load employees when department is selected
document.getElementById('department_id').addEventListener('change', function() {
    const departmentId = this.value;
    const employeeList = document.getElementById('employeeList');
    const employeeSearch = document.getElementById('employeeSearch');
    const selectAllCheckbox = document.getElementById('selectAllEmployees');
    
    if (departmentId) {
        // Filter employees by department
        filteredEmployees = allEmployees.filter(emp => emp.department_id === departmentId);
        
        // Clear search and reset selections
        employeeSearch.value = '';
        selectedEmployees.clear();
        selectAllCheckbox.checked = false;
        
        // Render employee list
        renderEmployeeList();
    } else {
        // Show all employees if no department selected
        filteredEmployees = allEmployees;
        employeeSearch.value = '';
        selectedEmployees.clear();
        selectAllCheckbox.checked = false;
        renderEmployeeList();
    }
});

// Search functionality
document.getElementById('employeeSearch').addEventListener('input', function() {
    const searchTerm = this.value.toLowerCase();
    const departmentId = document.getElementById('department_id').value;
    
    if (departmentId) {
        const departmentEmployees = allEmployees.filter(emp => emp.department_id === departmentId);
        filteredEmployees = departmentEmployees.filter(emp => 
            emp.first_name.toLowerCase().includes(searchTerm) ||
            emp.last_name.toLowerCase().includes(searchTerm) ||
            `${emp.first_name} ${emp.last_name}`.toLowerCase().includes(searchTerm)
        );
    } else {
        filteredEmployees = allEmployees.filter(emp => 
            emp.first_name.toLowerCase().includes(searchTerm) ||
            emp.last_name.toLowerCase().includes(searchTerm) ||
            `${emp.first_name} ${emp.last_name}`.toLowerCase().includes(searchTerm)
        );
    }
    
    renderEmployeeList();
});

// Select All functionality
document.getElementById('selectAllEmployees').addEventListener('change', function() {
    if (this.checked) {
        filteredEmployees.forEach(emp => selectedEmployees.add(emp.id));
    } else {
        filteredEmployees.forEach(emp => selectedEmployees.delete(emp.id));
    }
    renderEmployeeList();
    updateSelectedCount();
});

// Render employee list with checkboxes
function renderEmployeeList() {
    const employeeList = document.getElementById('employeeList');
    const selectAllCheckbox = document.getElementById('selectAllEmployees');
    
    employeeList.innerHTML = '';
    
    if (filteredEmployees.length === 0) {
        employeeList.innerHTML = '<p class="text-sm text-gray-500 text-center py-4"><i class="fas fa-info-circle mr-1"></i>No employees found</p>';
        return;
    }
    
    // Check if all visible employees are selected
    const allSelected = filteredEmployees.every(emp => selectedEmployees.has(emp.id));
    selectAllCheckbox.checked = allSelected;
    
    filteredEmployees.forEach(employee => {
        const isSelected = selectedEmployees.has(employee.id);
        const checkbox = document.createElement('div');
        checkbox.className = 'flex items-center space-x-3 py-2 px-3 hover:bg-gray-50 rounded-lg transition-colors';
        checkbox.innerHTML = `
            <input type="checkbox" name="employee_ids[]" value="${employee.id}" id="emp_${employee.id}" 
                   class="rounded border-gray-300 text-blue-600 focus:ring-blue-500" ${isSelected ? 'checked' : ''}>
            <label for="emp_${employee.id}" class="text-sm text-gray-700 cursor-pointer flex-1">
                <div class="font-medium">${employee.first_name} ${employee.last_name}</div>
                <div class="text-xs text-gray-500">${employee.position} - ${employee.department.name}</div>
            </label>
        `;
        
        // Add click handler for individual checkboxes
        const checkboxInput = checkbox.querySelector('input[type="checkbox"]');
        checkboxInput.addEventListener('change', function() {
            if (this.checked) {
                selectedEmployees.add(employee.id);
            } else {
                selectedEmployees.delete(employee.id);
            }
            updateSelectedCount();
            
            // Update select all checkbox
            const allSelected = filteredEmployees.every(emp => selectedEmployees.has(emp.id));
            selectAllCheckbox.checked = allSelected;
        });
        
        employeeList.appendChild(checkbox);
    });
}

// Update selected count
function updateSelectedCount() {
    const countElement = document.getElementById('selectedCount');
    const countText = document.getElementById('countText');
    
    if (selectedEmployees.size > 0) {
        countElement.classList.remove('hidden');
        countText.textContent = `${selectedEmployees.size} employee${selectedEmployees.size !== 1 ? 's' : ''} selected`;
    } else {
        countElement.classList.add('hidden');
    }
}

// Initialize with all employees if no department filter
document.addEventListener('DOMContentLoaded', function() {
    const departmentSelect = document.getElementById('department_id');
    if (!departmentSelect.value) {
        filteredEmployees = allEmployees;
        renderEmployeeList();
    }
});
</script>
@endsection

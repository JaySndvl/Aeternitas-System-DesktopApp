@extends('layouts.dashboard-base', ['user' => $user, 'activeRoute' => 'employees.index'])

@section('title', 'Add Employee')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between space-y-4 sm:space-y-0">
            <div>
                <h1 class="text-xl sm:text-2xl font-bold text-gray-900">Add New Employee</h1>
                <p class="mt-1 text-sm text-gray-500">Create a new employee account and profile</p>
            </div>
            <a href="{{ route('employees.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                <i class="fas fa-arrow-left mr-2"></i>
                <span class="hidden sm:inline">Back to Employees</span>
                <span class="sm:hidden">Back</span>
            </a>
        </div>

        <!-- Form -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <form method="POST" action="{{ route('employees.store') }}" class="p-4 sm:p-6 space-y-6">
                @csrf
                
                <!-- Personal Information -->
                <div>
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Personal Information</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                        <div>
                            <label for="first_name" class="block text-sm font-medium text-gray-700 mb-2">First Name</label>
                            <input type="text" name="first_name" id="first_name" value="{{ old('first_name') }}" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-white text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('first_name') border-red-500 @enderror">
                            @error('first_name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div>
                            <label for="last_name" class="block text-sm font-medium text-gray-700 mb-2">Last Name</label>
                            <input type="text" name="last_name" id="last_name" value="{{ old('last_name') }}" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-white text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('last_name') border-red-500 @enderror">
                            @error('last_name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
                            <input type="email" name="email" id="email" value="{{ old('email') }}" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-white text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('email') border-red-500 @enderror">
                            @error('email')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div>
                            <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">Phone Number</label>
                            <input type="text" name="phone" id="phone" value="{{ old('phone') }}" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-white text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('phone') border-red-500 @enderror">
                            @error('phone')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div>
                            <label for="employee_id" class="block text-sm font-medium text-gray-700 mb-2">Employee ID</label>
                            <input type="text" name="employee_id" id="employee_id" value="{{ old('employee_id') }}" 
                                placeholder="e.g., EMP-0001 or leave blank for auto-generation"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-white text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('employee_id') border-red-500 @enderror">
                            <p class="mt-1 text-xs text-gray-500">Leave blank to auto-generate (EMP-XXXX format)</p>
                            @error('employee_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Work Information -->
                <div>
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Work Information</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                        <div>
                            <label for="position" class="block text-sm font-medium text-gray-700 mb-2">Position</label>
                            <select name="position" id="position" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-white text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('position') border-red-500 @enderror">
                                <option value="">Select Position</option>
                                @foreach($positions as $pos)
                                    <option value="{{ $pos->name }}" 
                                        data-department-id="{{ $pos->department_id }}"
                                        {{ old('position') == $pos->name ? 'selected' : '' }}>
                                        {{ $pos->name }}@if($pos->level) ({{ $pos->level }})@endif - {{ $pos->department->name ?? 'No Department' }}
                                    </option>
                                @endforeach
                            </select>
                            <p class="mt-1 text-xs text-gray-500">Positions filtered by selected department</p>
                            @error('position')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div>
                            <label for="department_id" class="block text-sm font-medium text-gray-700 mb-2">Department</label>
                            <select name="department_id" id="department_id" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-white text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('department_id') border-red-500 @enderror">
                                <option value="">Select Department</option>
                                @foreach($departments as $department)
                                    <option value="{{ $department->id }}" {{ old('department_id') == $department->id ? 'selected' : '' }}>
                                        {{ $department->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('department_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div>
                            <label for="salary" class="block text-sm font-medium text-gray-700 mb-2">Monthly Salary (₱)</label>
                            <input type="number" name="salary" id="salary" value="{{ old('salary') }}" min="0" step="0.01" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-white text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('salary') border-red-500 @enderror">
                            @error('salary')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div>
                            <label for="hire_date" class="block text-sm font-medium text-gray-700 mb-2">Hire Date</label>
                            <input type="date" name="hire_date" id="hire_date" value="{{ old('hire_date') }}" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-white text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('hire_date') border-red-500 @enderror">
                            @error('hire_date')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Account Information -->
                <div>
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Account Information</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                        <div>
                            <label for="role" class="block text-sm font-medium text-gray-700 mb-2">Role</label>
                            <select name="role" id="role"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-white text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('role') border-red-500 @enderror">
                                <option value="employee" {{ old('role') == 'employee' ? 'selected' : '' }}>Employee</option>
                                <option value="manager" {{ old('role') == 'manager' ? 'selected' : '' }}>Manager</option>
                                <option value="hr" {{ old('role') == 'hr' ? 'selected' : '' }}>HR</option>
                                <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                            </select>
                            @error('role')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700 mb-2">Password</label>
                            <div class="relative">
                                <input type="text" name="password" id="password" required readonly
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 text-gray-700 cursor-not-allowed @error('password') border-red-500 @enderror">
                                <div class="absolute right-2 top-1/2 transform -translate-y-1/2">
                                    <i class="fas fa-lock text-gray-400"></i>
                                </div>
                            </div>
                            <p class="mt-1 text-xs text-gray-500">Auto-generated: FirstName + LastName + HireDate (e.g., JohnSmith20230115)</p>
                            @error('password')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="flex justify-end space-x-3 pt-6 border-t border-gray-200">
                    <a href="{{ route('employees.index') }}" class="px-4 py-2 border border-gray-300 rounded-lg font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                        Cancel
                    </a>
                    <button type="submit" class="px-4 py-2 bg-blue-600 border border-transparent rounded-lg font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                        <i class="fas fa-save mr-2"></i>
                        Create Employee
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    /* Ensure all form inputs are visible */
    input[type="text"],
    input[type="email"],
    input[type="number"],
    input[type="date"],
    select,
    textarea {
        color: #111827 !important; /* text-gray-900 */
        background-color: #ffffff !important; /* bg-white */
    }
    
    /* Force select dropdown to show dark text */
    #position,
    #department_id,
    #role {
        color: #111827 !important;
        background-color: #ffffff !important;
    }
    
    input[type="text"]:focus,
    input[type="email"]:focus,
    input[type="number"]:focus,
    input[type="date"]:focus,
    select:focus,
    textarea:focus {
        color: #111827 !important;
        background-color: #ffffff !important;
    }
    
    select option {
        color: #111827 !important;
        background-color: #ffffff !important;
    }
    
    /* Ensure select dropdown text is visible */
    select {
        color: #111827 !important;
    }
    
    select:not(:focus) {
        color: #111827 !important;
    }
    
    /* Make sure option text is dark */
    select option:checked {
        color: #111827 !important;
        background-color: #ffffff !important;
    }
    
    select option:hover {
        background-color: #f3f4f6 !important;
        color: #111827 !important;
    }
    
    /* Date picker calendar text */
    input[type="date"]::-webkit-calendar-picker-indicator {
        filter: invert(0);
    }
    
    input[type="date"]::-webkit-datetime-edit-text,
    input[type="date"]::-webkit-datetime-edit-month-field,
    input[type="date"]::-webkit-datetime-edit-day-field,
    input[type="date"]::-webkit-datetime-edit-year-field {
        color: #111827 !important;
    }
    
    /* Placeholder text */
    input::placeholder,
    textarea::placeholder {
        color: #9ca3af !important; /* text-gray-400 */
        opacity: 1;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const firstNameInput = document.getElementById('first_name');
    const lastNameInput = document.getElementById('last_name');
    const hireDateInput = document.getElementById('hire_date');
    const passwordInput = document.getElementById('password');

    function generatePassword() {
        const firstName = firstNameInput.value.trim().replace(/\s+/g, '');
        const lastName = lastNameInput.value.trim().replace(/\s+/g, '');
        const hireDate = hireDateInput.value;

        if (firstName && lastName && hireDate) {
            // Format: FirstName + LastName + HireDate (YYYYMMDD) - no dashes, no spaces
            const dateWithoutDashes = hireDate.replace(/-/g, '');
            const generatedPassword = firstName + lastName + dateWithoutDashes;
            passwordInput.value = generatedPassword;
        } else {
            // Clear password if not all fields are filled
            passwordInput.value = '';
        }
    }

    // Auto-generate password whenever any field changes
    function autoGeneratePassword() {
        generatePassword();
    }

    // Auto-generate when fields change (real-time)
    firstNameInput.addEventListener('input', autoGeneratePassword);
    lastNameInput.addEventListener('input', autoGeneratePassword);
    hireDateInput.addEventListener('change', autoGeneratePassword);

    // Also generate on blur events for better UX
    firstNameInput.addEventListener('blur', autoGeneratePassword);
    lastNameInput.addEventListener('blur', autoGeneratePassword);

    // Filter positions by selected department
    const departmentSelect = document.getElementById('department_id');
    const positionSelect = document.getElementById('position');
    
    function filterPositionsByDepartment() {
        const selectedDepartmentId = departmentSelect.value;
        const allOptions = positionSelect.querySelectorAll('option');
        
        // Show/hide position options based on department
        allOptions.forEach(option => {
            if (option.value === '') {
                // Always show the "Select Position" option
                option.style.display = '';
            } else {
                const optionDepartmentId = option.getAttribute('data-department-id');
                if (selectedDepartmentId && optionDepartmentId !== selectedDepartmentId) {
                    option.style.display = 'none';
                } else {
                    option.style.display = '';
                }
            }
        });
        
        // Reset position selection if current selection is not available for selected department
        const selectedPosition = positionSelect.value;
        const selectedOption = positionSelect.querySelector(`option[value="${selectedPosition}"]`);
        if (selectedPosition && selectedOption && selectedOption.style.display === 'none') {
            positionSelect.value = '';
        }
    }

    // Filter positions when department changes
    if (departmentSelect && positionSelect) {
        departmentSelect.addEventListener('change', filterPositionsByDepartment);
        
        // Initial filter on page load
        filterPositionsByDepartment();
    }
});
</script>
@endsection

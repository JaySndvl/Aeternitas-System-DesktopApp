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
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('first_name') border-red-500 @enderror">
                            @error('first_name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div>
                            <label for="last_name" class="block text-sm font-medium text-gray-700 mb-2">Last Name</label>
                            <input type="text" name="last_name" id="last_name" value="{{ old('last_name') }}" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('last_name') border-red-500 @enderror">
                            @error('last_name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
                            <input type="email" name="email" id="email" value="{{ old('email') }}" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('email') border-red-500 @enderror">
                            @error('email')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div>
                            <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">Phone Number</label>
                            <input type="text" name="phone" id="phone" value="{{ old('phone') }}" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('phone') border-red-500 @enderror">
                            @error('phone')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div>
                            <label for="employee_id" class="block text-sm font-medium text-gray-700 mb-2">Employee ID</label>
                            <input type="text" name="employee_id" id="employee_id" value="{{ old('employee_id') }}" 
                                placeholder="e.g., EMP-0001 or leave blank for auto-generation"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('employee_id') border-red-500 @enderror">
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
                            <label for="department_id" class="block text-sm font-medium text-gray-700 mb-2">Department</label>
                            <select name="department_id" id="department_id" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('department_id') border-red-500 @enderror">
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
                            <label for="position" class="block text-sm font-medium text-gray-700 mb-2">Position</label>
                            <input type="text" name="position" id="position" value="{{ old('position') }}" required
                                placeholder="Enter position name (e.g., Software Engineer)"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('position') border-red-500 @enderror">
                            @error('position')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div>
                            <label for="salary" class="block text-sm font-medium text-gray-700 mb-2">Monthly Salary (₱)</label>
                            <input type="number" name="salary" id="salary" value="{{ old('salary') }}" min="0" step="0.01" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('salary') border-red-500 @enderror">
                            @error('salary')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div>
                            <label for="hire_date" class="block text-sm font-medium text-gray-700 mb-2">Hire Date</label>
                            <input type="date" name="hire_date" id="hire_date" value="{{ old('hire_date') }}" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('hire_date') border-red-500 @enderror">
                            @error('hire_date')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Additional Employee Details -->
                <div>
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Additional Employee Details</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                        <div>
                            <label for="date_of_birth" class="block text-sm font-medium text-gray-700 mb-2">Date of Birth</label>
                            <input type="date" name="date_of_birth" id="date_of_birth" value="{{ old('date_of_birth') }}"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('date_of_birth') border-red-500 @enderror">
                            @error('date_of_birth')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="civil_status" class="block text-sm font-medium text-gray-700 mb-2">Employee's Civil Status</label>
                            <input type="text" name="civil_status" id="civil_status" value="{{ old('civil_status') }}"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('civil_status') border-red-500 @enderror">
                            @error('civil_status')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="sm:col-span-2">
                            <label for="home_address" class="block text-sm font-medium text-gray-700 mb-2">Employee's Home Address</label>
                            <textarea name="home_address" id="home_address" rows="2"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('home_address') border-red-500 @enderror">{{ old('home_address') }}</textarea>
                            @error('home_address')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="sm:col-span-2">
                            <label for="current_address" class="block text-sm font-medium text-gray-700 mb-2">Employee's Current Address</label>
                            <textarea name="current_address" id="current_address" rows="2"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('current_address') border-red-500 @enderror">{{ old('current_address') }}</textarea>
                            @error('current_address')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="mobile_number" class="block text-sm font-medium text-gray-700 mb-2">Employee's Mobile Number</label>
                            <input type="text" name="mobile_number" id="mobile_number" value="{{ old('mobile_number') }}"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('mobile_number') border-red-500 @enderror">
                            @error('mobile_number')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="facebook_link" class="block text-sm font-medium text-gray-700 mb-2">Facebook Link</label>
                            <input type="url" name="facebook_link" id="facebook_link" value="{{ old('facebook_link') }}"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('facebook_link') border-red-500 @enderror">
                            @error('facebook_link')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="linkedin_link" class="block text-sm font-medium text-gray-700 mb-2">LinkedIn Link</label>
                            <input type="url" name="linkedin_link" id="linkedin_link" value="{{ old('linkedin_link') }}"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('linkedin_link') border-red-500 @enderror">
                            @error('linkedin_link')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="ig_link" class="block text-sm font-medium text-gray-700 mb-2">IG Link</label>
                            <input type="url" name="ig_link" id="ig_link" value="{{ old('ig_link') }}"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('ig_link') border-red-500 @enderror">
                            @error('ig_link')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="other_link" class="block text-sm font-medium text-gray-700 mb-2">Others Link</label>
                            <input type="url" name="other_link" id="other_link" value="{{ old('other_link') }}"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('other_link') border-red-500 @enderror">
                            @error('other_link')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- In Case of Emergency -->
                <div>
                    <h3 class="text-lg font-medium text-gray-900 mb-4">In Case of an Emergency</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                        <div>
                            <label for="emergency_full_name" class="block text-sm font-medium text-gray-700 mb-2">Full Name</label>
                            <input type="text" name="emergency_full_name" id="emergency_full_name" value="{{ old('emergency_full_name') }}"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('emergency_full_name') border-red-500 @enderror">
                            @error('emergency_full_name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="emergency_relationship" class="block text-sm font-medium text-gray-700 mb-2">Relationship</label>
                            <input type="text" name="emergency_relationship" id="emergency_relationship" value="{{ old('emergency_relationship') }}"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('emergency_relationship') border-red-500 @enderror">
                            @error('emergency_relationship')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="sm:col-span-2">
                            <label for="emergency_home_address" class="block text-sm font-medium text-gray-700 mb-2">Home Address</label>
                            <textarea name="emergency_home_address" id="emergency_home_address" rows="2"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('emergency_home_address') border-red-500 @enderror">{{ old('emergency_home_address') }}</textarea>
                            @error('emergency_home_address')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="sm:col-span-2">
                            <label for="emergency_current_address" class="block text-sm font-medium text-gray-700 mb-2">Current Address</label>
                            <textarea name="emergency_current_address" id="emergency_current_address" rows="2"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('emergency_current_address') border-red-500 @enderror">{{ old('emergency_current_address') }}</textarea>
                            @error('emergency_current_address')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="emergency_mobile_number" class="block text-sm font-medium text-gray-700 mb-2">Mobile Number</label>
                            <input type="text" name="emergency_mobile_number" id="emergency_mobile_number" value="{{ old('emergency_mobile_number') }}"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('emergency_mobile_number') border-red-500 @enderror">
                            @error('emergency_mobile_number')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="emergency_email" class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
                            <input type="email" name="emergency_email" id="emergency_email" value="{{ old('emergency_email') }}"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('emergency_email') border-red-500 @enderror">
                            @error('emergency_email')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="sm:col-span-2">
                            <label for="emergency_facebook_link" class="block text-sm font-medium text-gray-700 mb-2">Facebook Link</label>
                            <input type="url" name="emergency_facebook_link" id="emergency_facebook_link" value="{{ old('emergency_facebook_link') }}"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('emergency_facebook_link') border-red-500 @enderror">
                            @error('emergency_facebook_link')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Employee Loans -->
                <div>
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Employee Loans</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                        <div>
                            <label for="loan_start_date" class="block text-sm font-medium text-gray-700 mb-2">Start Date</label>
                            <input type="date" name="loan_start_date" id="loan_start_date" value="{{ old('loan_start_date') }}"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('loan_start_date') border-red-500 @enderror">
                            @error('loan_start_date')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="loan_end_date" class="block text-sm font-medium text-gray-700 mb-2">End Date</label>
                            <input type="date" name="loan_end_date" id="loan_end_date" value="{{ old('loan_end_date') }}"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('loan_end_date') border-red-500 @enderror">
                            @error('loan_end_date')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="loan_total_amount" class="block text-sm font-medium text-gray-700 mb-2">Total Amount</label>
                            <input type="number" step="0.01" min="0" name="loan_total_amount" id="loan_total_amount" value="{{ old('loan_total_amount') }}"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('loan_total_amount') border-red-500 @enderror">
                            @error('loan_total_amount')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="loan_monthly_amortization" class="block text-sm font-medium text-gray-700 mb-2">Monthly Amortization</label>
                            <input type="number" step="0.01" min="0" name="loan_monthly_amortization" id="loan_monthly_amortization" value="{{ old('loan_monthly_amortization') }}"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('loan_monthly_amortization') border-red-500 @enderror">
                            @error('loan_monthly_amortization')
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
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('role') border-red-500 @enderror">
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
});
</script>
@endsection
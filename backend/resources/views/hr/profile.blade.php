@extends('layouts.dashboard-base', ['user' => $user, 'activeRoute' => 'hr.profile'])

@section('title', 'HR Profile')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 flex items-center">
                <i class="fas fa-user-circle mr-3 text-blue-600"></i>
                HR Profile
            </h1>
            <p class="mt-1 text-sm text-gray-600">Manage your personal information and professional details</p>
        </div>
        <div class="mt-4 sm:mt-0">
            <button class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                <i class="fas fa-edit mr-2"></i>
                Edit Profile
            </button>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Profile Card -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <!-- Profile Picture -->
                <div class="text-center">
                    <div class="w-24 h-24 bg-gradient-to-r from-blue-500 to-blue-600 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-user text-white text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900">{{ $employee ? $employee->full_name : $user->full_name }}</h3>
                    <p class="text-sm text-gray-500">{{ $user->email }}</p>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 mt-2">
                        <i class="fas fa-crown mr-1"></i>
                        {{ ucfirst($user->role) }}
                    </span>
                </div>

                <!-- Quick Stats -->
                <div class="mt-6 space-y-4">
                    <div class="flex items-center justify-between py-2 border-b border-gray-100">
                        <span class="text-sm text-gray-600">Employee ID</span>
                        <span class="text-sm font-medium text-gray-900">{{ $employee?->employee_id ?? 'N/A' }}</span>
                    </div>
                    <div class="flex items-center justify-between py-2 border-b border-gray-100">
                        <span class="text-sm text-gray-600">Department</span>
                        <span class="text-sm font-medium text-gray-900">{{ $employee && $employee->department ? $employee->department->name : 'N/A' }}</span>
                    </div>
                    <div class="flex items-center justify-between py-2 border-b border-gray-100">
                        <span class="text-sm text-gray-600">Hire Date</span>
                        <span class="text-sm font-medium text-gray-900">{{ $employee && $employee->hire_date ? $employee->hire_date->format('M d, Y') : 'N/A' }}</span>
                    </div>
                    <div class="flex items-center justify-between py-2">
                        <span class="text-sm text-gray-600">Status</span>
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                            <i class="fas fa-check-circle mr-1"></i>
                            {{ $user->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </div>
                </div>

                <!-- Contact Information -->
                <div class="mt-6">
                    <h4 class="text-sm font-medium text-gray-900 mb-3">Contact Information</h4>
                    <div class="space-y-2">
                        <div class="flex items-center text-sm text-gray-600">
                            <i class="fas fa-phone w-4 h-4 mr-3 text-gray-400"></i>
                            {{ $employee?->phone ?? 'N/A' }}
                        </div>
                        <div class="flex items-center text-sm text-gray-600">
                            <i class="fas fa-envelope w-4 h-4 mr-3 text-gray-400"></i>
                            {{ $user->email }}
                        </div>
                        <div class="flex items-center text-sm text-gray-600">
                            <i class="fas fa-map-marker-alt w-4 h-4 mr-3 text-gray-400"></i>
                            Manila, Philippines
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        @php($canEditRestricted = in_array($user->role, ['admin', 'hr']))
        <div class="lg:col-span-2 space-y-6">
            <!-- Personal Information -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Personal Information</h3>
                </div>
                <div class="p-6">
                    <form id="profile-form" method="POST" action="{{ route('hr.profile.update') }}" class="space-y-6">
                        @csrf
                        @method('PUT')
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">First Name</label>
                                <input type="text" name="first_name" value="{{ $employee?->first_name ?? '' }}" 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                @error('first_name')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Last Name</label>
                                <input type="text" name="last_name" value="{{ $employee?->last_name ?? '' }}" 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                @error('last_name')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
                                <input type="email" name="email" value="{{ $user->email }}" 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                @error('email')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Phone Number</label>
                                <input type="tel" name="phone" value="{{ $employee?->phone ?? '' }}" 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                @error('phone')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Date of Birth</label>
                                <input type="date" name="date_of_birth" value="{{ old('date_of_birth', $employee?->date_of_birth?->format('Y-m-d')) }}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                @error('date_of_birth')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Employee's Civil Status</label>
                                <input type="text" name="civil_status" value="{{ old('civil_status', $employee?->civil_status) }}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                @error('civil_status')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Gender</label>
                                <select name="gender" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">Select Gender</option>
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                    <option value="Other">Other</option>
                                </select>
                                @error('gender')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Employee's Mobile Number</label>
                                <input type="text" name="mobile_number" value="{{ old('mobile_number', $employee?->mobile_number) }}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                @error('mobile_number')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Facebook Link</label>
                                <input type="url" name="facebook_link" value="{{ old('facebook_link', $employee?->facebook_link) }}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                @error('facebook_link')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">LinkedIn Link</label>
                                <input type="url" name="linkedin_link" value="{{ old('linkedin_link', $employee?->linkedin_link) }}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                @error('linkedin_link')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">IG Link</label>
                                <input type="url" name="ig_link" value="{{ old('ig_link', $employee?->ig_link) }}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                @error('ig_link')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Others Link</label>
                                <input type="url" name="other_link" value="{{ old('other_link', $employee?->other_link) }}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                @error('other_link')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Employee's Home Address</label>
                            <textarea name="home_address" rows="3" placeholder="Enter home address" 
                                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">{{ old('home_address', $employee?->home_address) }}</textarea>
                            @error('home_address')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Employee's Current Address</label>
                            <textarea name="current_address" rows="3" placeholder="Enter current address" 
                                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">{{ old('current_address', $employee?->current_address) }}</textarea>
                            @error('current_address')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Address</label>
                            <textarea name="address" rows="3" placeholder="Enter your full address" 
                                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"></textarea>
                            @error('address')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </form>
                </div>
            </div>

            <!-- Professional Information -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Professional Information</h3>
                </div>
                <div class="p-6">
                    @unless($canEditRestricted)
                        <div class="mb-4 rounded-lg border border-yellow-200 bg-yellow-50 px-4 py-3 text-sm text-yellow-800">
                            Only Admin/HR can edit Professional Information.
                        </div>
                    @endunless
                    <form method="POST" action="{{ route('hr.profile.update') }}" class="space-y-6">
                        @csrf
                        @method('PUT')
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Employee ID</label>
                                <input type="text" value="{{ $employee?->employee_id ?? 'N/A' }}" readonly 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 text-gray-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Position</label>
                                <input type="text" name="position" value="{{ $employee?->position ?? '' }}" {{ $canEditRestricted ? '' : 'readonly' }}
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                @error('position')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Department</label>
                                <select name="department_id" {{ $canEditRestricted ? '' : 'disabled' }} class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">Select Department</option>
                                    @foreach($departments as $department)
                                        <option value="{{ $department->id }}" 
                                                {{ $employee && $employee->department_id == $department->id ? 'selected' : '' }}>
                                            {{ $department->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('department_id')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Employment Type</label>
                                <select name="employment_type" {{ $canEditRestricted ? '' : 'disabled' }} class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">Select Type</option>
                                    <option value="Full-time">Full-time</option>
                                    <option value="Part-time">Part-time</option>
                                    <option value="Contract">Contract</option>
                                </select>
                                @error('employment_type')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Hire Date</label>
                                <input type="date" name="hire_date" value="{{ $employee && $employee->hire_date ? $employee->hire_date->format('Y-m-d') : '' }}" {{ $canEditRestricted ? '' : 'readonly' }}
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                @error('hire_date')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Salary</label>
                                <input type="number" name="salary" value="{{ $employee?->salary ?? '' }}" step="0.01" {{ $canEditRestricted ? '' : 'readonly' }}
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                @error('salary')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Job Description</label>
                            <textarea name="job_description" rows="4" placeholder="Describe your role and responsibilities" {{ $canEditRestricted ? '' : 'readonly' }}
                                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"></textarea>
                            @error('job_description')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </form>
                </div>
            </div>

            <!-- Emergency Contact -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Emergency Contact</h3>
                </div>
                <div class="p-6">
                    <form class="space-y-6" id="emergency-form" onsubmit="return false;">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Contact Name</label>
                                <input type="text" name="emergency_full_name" form="profile-form" value="{{ old('emergency_full_name', $employee?->emergency_full_name) }}" placeholder="Full name" 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Relationship</label>
                                <input type="text" name="emergency_relationship" form="profile-form" value="{{ old('emergency_relationship', $employee?->emergency_relationship) }}" placeholder="Relationship"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Home Address</label>
                                <input type="text" name="emergency_home_address" form="profile-form" value="{{ old('emergency_home_address', $employee?->emergency_home_address) }}" placeholder="Home address"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Current Address</label>
                                <input type="text" name="emergency_current_address" form="profile-form" value="{{ old('emergency_current_address', $employee?->emergency_current_address) }}" placeholder="Current address"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Phone Number</label>
                                <input type="tel" name="emergency_mobile_number" form="profile-form" value="{{ old('emergency_mobile_number', $employee?->emergency_mobile_number) }}" placeholder="+63 912 345 6789" 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
                                <input type="email" name="emergency_email" form="profile-form" value="{{ old('emergency_email', $employee?->emergency_email) }}" placeholder="contact@example.com" 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Facebook Link</label>
                                <input type="url" name="emergency_facebook_link" form="profile-form" value="{{ old('emergency_facebook_link', $employee?->emergency_facebook_link) }}" placeholder="https://facebook.com/..."
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Employee Loans -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Employee Loans</h3>
                </div>
                <div class="p-6">
                    @unless($canEditRestricted)
                        <div class="mb-4 rounded-lg border border-yellow-200 bg-yellow-50 px-4 py-3 text-sm text-yellow-800">
                            Only Admin/HR can edit Employee Loans.
                        </div>
                    @endunless
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Start Date</label>
                            <input type="date" name="loan_start_date" form="profile-form" value="{{ old('loan_start_date', $employee?->loan_start_date?->format('Y-m-d')) }}" {{ $canEditRestricted ? '' : 'readonly' }}
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">End Date</label>
                            <input type="date" name="loan_end_date" form="profile-form" value="{{ old('loan_end_date', $employee?->loan_end_date?->format('Y-m-d')) }}" {{ $canEditRestricted ? '' : 'readonly' }}
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Total Amount</label>
                            <input type="number" step="0.01" min="0" name="loan_total_amount" form="profile-form" value="{{ old('loan_total_amount', $employee?->loan_total_amount) }}" {{ $canEditRestricted ? '' : 'readonly' }}
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Monthly Amortization</label>
                            <input type="number" step="0.01" min="0" name="loan_monthly_amortization" form="profile-form" value="{{ old('loan_monthly_amortization', $employee?->loan_monthly_amortization) }}" {{ $canEditRestricted ? '' : 'readonly' }}
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Skills & Certifications -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900">Skills & Certifications</h3>
                        <button class="text-blue-600 hover:text-blue-700 text-sm font-medium">
                            <i class="fas fa-plus mr-1"></i>
                            Add New
                        </button>
                    </div>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        <!-- Skills -->
                        <div>
                            <h4 class="text-sm font-medium text-gray-900 mb-3">Skills</h4>
                            <div class="flex flex-wrap gap-2">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm bg-blue-100 text-blue-800">
                                    Human Resources Management
                                    <button class="ml-2 text-blue-600 hover:text-blue-800">
                                        <i class="fas fa-times text-xs"></i>
                                    </button>
                                </span>
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm bg-green-100 text-green-800">
                                    Payroll Processing
                                    <button class="ml-2 text-green-600 hover:text-green-800">
                                        <i class="fas fa-times text-xs"></i>
                                    </button>
                                </span>
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm bg-purple-100 text-purple-800">
                                    Employee Relations
                                    <button class="ml-2 text-purple-600 hover:text-purple-800">
                                        <i class="fas fa-times text-xs"></i>
                                    </button>
                                </span>
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm bg-yellow-100 text-yellow-800">
                                    Labor Law Compliance
                                    <button class="ml-2 text-yellow-600 hover:text-yellow-800">
                                        <i class="fas fa-times text-xs"></i>
                                    </button>
                                </span>
                            </div>
                        </div>

                        <!-- Certifications -->
                        <div>
                            <h4 class="text-sm font-medium text-gray-900 mb-3">Certifications</h4>
                            <div class="space-y-3">
                                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">Professional in Human Resources (PHR)</p>
                                        <p class="text-xs text-gray-500">HR Certification Institute • Issued 2022</p>
                                    </div>
                                    <button class="text-red-600 hover:text-red-700 text-sm">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">Certified Payroll Professional (CPP)</p>
                                        <p class="text-xs text-gray-500">American Payroll Association • Issued 2021</p>
                                    </div>
                                    <button class="text-red-600 hover:text-red-700 text-sm">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex justify-end space-x-3">
                <button type="button" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
                    Cancel
                </button>
                <button type="submit" form="profile-form" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    <i class="fas fa-save mr-2"></i>
                    Save Changes
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

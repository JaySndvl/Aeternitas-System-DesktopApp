<div class="space-y-6">
    <!-- Employee Header -->
    <div class="flex items-center space-x-4">
        <div class="flex-shrink-0 h-20 w-20">
            <div class="h-20 w-20 rounded-full bg-blue-100 flex items-center justify-center">
                <span class="text-2xl font-medium text-blue-600">
                    {{ substr($employee->first_name, 0, 1) }}{{ substr($employee->last_name, 0, 1) }}
                </span>
            </div>
        </div>
        <div>
            <h4 class="text-2xl font-bold text-gray-900">{{ $employee->full_name }}</h4>
            <p class="text-lg text-gray-600">{{ $employee->position ?? 'No position' }}</p>
            <p class="text-gray-500">{{ $employee->department->name ?? 'No Department' }}</p>
            @if($employee->company)
                <p class="text-sm text-gray-400">
                    <i class="fas fa-building mr-1"></i>
                    {{ $employee->company->name }}
                </p>
            @endif
        </div>
    </div>
    
    <!-- Employee Information Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Personal Information -->
        <div class="bg-gray-50 rounded-lg p-4">
            <h5 class="font-medium text-gray-900 mb-3">Personal Information</h5>
            <div class="space-y-3">
                <div>
                    <span class="text-sm font-medium text-gray-700">Employee ID:</span>
                    <p class="text-sm text-gray-900">{{ $employee->employee_id }}</p>
                </div>
                <div>
                    <span class="text-sm font-medium text-gray-700">Email:</span>
                    <p class="text-sm text-gray-900">{{ $employee->email ?? 'Not provided' }}</p>
                </div>
                <div>
                    <span class="text-sm font-medium text-gray-700">Phone:</span>
                    <p class="text-sm text-gray-900">{{ $employee->phone ?? 'Not provided' }}</p>
                </div>
                <div>
                    <span class="text-sm font-medium text-gray-700">Address:</span>
                    <p class="text-sm text-gray-900">{{ $employee->address ?? 'Not provided' }}</p>
                </div>
            </div>
        </div>
        
        <!-- Employment Information -->
        <div class="bg-gray-50 rounded-lg p-4">
            <h5 class="font-medium text-gray-900 mb-3">Employment Information</h5>
            <div class="space-y-3">
                <div>
                    <span class="text-sm font-medium text-gray-700">Status:</span>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                        @if($employee->status == 'active') bg-green-100 text-green-800
                        @elseif($employee->status == 'on-leave') bg-yellow-100 text-yellow-800
                        @else bg-red-100 text-red-800 @endif">
                        {{ ucfirst($employee->status ?? 'active') }}
                    </span>
                </div>
                <div>
                    <span class="text-sm font-medium text-gray-700">Hire Date:</span>
                    <p class="text-sm text-gray-900">
                        @if($employee->hire_date)
                            {{ \Carbon\Carbon::parse($employee->hire_date)->format('M j, Y') }}
                        @else
                            Not provided
                        @endif
                    </p>
                </div>
                <div>
                    <span class="text-sm font-medium text-gray-700">Employment Type:</span>
                    <p class="text-sm text-gray-900">{{ ucfirst($employee->employment_type ?? 'Regular') }}</p>
                </div>
                <div>
                    <span class="text-sm font-medium text-gray-700">Department:</span>
                    <p class="text-sm text-gray-900">{{ $employee->department->name ?? 'Not assigned' }}</p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Salary Information -->
    <div class="border-t border-gray-200 pt-6">
        <h5 class="font-medium text-gray-900 mb-3">Salary Information</h5>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-blue-50 rounded-lg p-4">
                <div class="text-sm font-medium text-blue-700 mb-1">Monthly Salary</div>
                <div class="text-2xl font-bold text-blue-900">
                    @if($employee->salary)
                        ₱{{ number_format($employee->salary, 2) }}
                    @else
                        Not set
                    @endif
                </div>
            </div>
            <div class="bg-green-50 rounded-lg p-4">
                <div class="text-sm font-medium text-green-700 mb-1">Annual Salary</div>
                <div class="text-2xl font-bold text-green-900">
                    @if($employee->salary)
                        ₱{{ number_format($employee->salary * 12, 2) }}
                    @else
                        Not set
                    @endif
                </div>
            </div>
            <div class="bg-purple-50 rounded-lg p-4">
                <div class="text-sm font-medium text-purple-700 mb-1">Tax Estimate</div>
                <div class="text-2xl font-bold text-purple-900">
                    @if($employee->salary)
                        @php
                            // Calculate approximate tax based on Philippine tax brackets
                            $annualSalary = $employee->salary * 12;
                            $tax = 0;
                            
                            if ($annualSalary > 8000000) {
                                $tax = 2410000 + ($annualSalary - 8000000) * 0.35;
                            } elseif ($annualSalary > 2000000) {
                                $tax = 490000 + ($annualSalary - 2000000) * 0.32;
                            } elseif ($annualSalary > 800000) {
                                $tax = 130000 + ($annualSalary - 800000) * 0.30;
                            } elseif ($annualSalary > 400000) {
                                $tax = 30000 + ($annualSalary - 400000) * 0.25;
                            } elseif ($annualSalary > 250000) {
                                $tax = ($annualSalary - 250000) * 0.20;
                            }
                            
                            echo '₱' . number_format($tax, 2);
                        @endphp
                    @else
                        N/A
                    @endif
                </div>
            </div>
        </div>
    </div>
    
    <!-- Documents Section -->
    <div class="border-t border-gray-200 pt-6">
        <h5 class="font-medium text-gray-900 mb-3">Documents</h5>
        
        <div class="text-center py-4 bg-gray-50 rounded-lg">
            <i class="fas fa-file text-gray-400 text-2xl mb-2"></i>
            <p class="text-gray-600">No documents uploaded</p>
            <p class="text-sm text-gray-500 mt-1">Click "View Documents" to manage employee documents</p>
        </div>
    </div>
</div>
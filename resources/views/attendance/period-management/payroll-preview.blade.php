@extends('layouts.dashboard-base', ['user' => $user, 'activeRoute' => 'attendance.period-management.index'])

@section('title', 'Payroll Preview - ' . $period['name'])

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white shadow-sm border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="py-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Payroll Preview</h1>
                        <p class="mt-1 text-sm text-gray-600">
                            {{ \Carbon\Carbon::parse($period['start_date'])->format('M j, Y') }} - 
                            {{ \Carbon\Carbon::parse($period['end_date'])->format('M j, Y') }}
                        </p>
                        <p class="mt-1 text-sm text-gray-500">{{ $period['name'] }}</p>
                    </div>
                    <div class="flex space-x-3">
                        <a href="{{ route('attendance.period-management.show', $period['id']) }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                            <i class="fas fa-arrow-left mr-2"></i>
                            Back to Period
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Success Message -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
        @if(session('success'))
            <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-check-circle text-green-400"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                    </div>
                </div>
            </div>
        @endif

        <!-- Period Information -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Period Information</h3>
            </div>
            <div class="px-6 py-4">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Period</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            {{ \Carbon\Carbon::parse($period['start_date'])->format('M j, Y') }} - 
                            {{ \Carbon\Carbon::parse($period['end_date'])->format('M j, Y') }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Department</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $period['department_name'] ?? 'All Departments' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Generated</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            {{ isset($generatedAt) ? $generatedAt->format('M j, Y H:i:s') : \Carbon\Carbon::now()->format('M j, Y H:i:s') }}
                            @if(isset($generatedAt))
                                <br><span class="text-green-600 text-xs"><i class="fas fa-sync-alt mr-1"></i>Fresh Data</span>
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Status</dt>
                        <dd class="mt-1">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                Preview
                            </span>
                        </dd>
                    </div>
                </div>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-2 md:grid-cols-6 gap-4 mb-6">
            <!-- Total Employees -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                <div class="flex items-center">
                    <div class="h-8 w-8 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                        <i class="fas fa-users text-blue-600 text-sm"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-semibold text-gray-900">{{ $summaryData['total_employees'] }}</h3>
                        <p class="text-xs text-gray-600">Employees</p>
                    </div>
                </div>
            </div>

            <!-- Basic Salary -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                <div class="flex items-center">
                    <div class="h-8 w-8 bg-green-100 rounded-lg flex items-center justify-center mr-3">
                        <i class="fas fa-money-bill-wave text-green-600 text-sm"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">₱{{ number_format($summaryData['total_basic_salary'] + $summaryData['total_holiday_basic_pay'], 2) }}</h3>
                        <p class="text-xs text-gray-600">Basic Salary</p>
                    </div>
                </div>
            </div>

            <!-- Holiday Pay -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                <div class="flex items-center">
                    <div class="h-8 w-8 bg-purple-100 rounded-lg flex items-center justify-center mr-3">
                        <i class="fas fa-calendar-alt text-purple-600 text-sm"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">₱{{ number_format($summaryData['total_holiday_premium'] + $summaryData['total_special_holiday_premium'], 2) }}</h3>
                        <p class="text-xs text-gray-600">Holiday Pay</p>
                    </div>
                </div>
            </div>

            <!-- Overtime -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                <div class="flex items-center">
                    <div class="h-8 w-8 bg-yellow-100 rounded-lg flex items-center justify-center mr-3">
                        <i class="fas fa-clock text-yellow-600 text-sm"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">₱{{ number_format($summaryData['total_overtime_pay'], 2) }}</h3>
                        <p class="text-xs text-gray-600">Overtime</p>
                    </div>
                </div>
            </div>

            <!-- Deductions -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                <div class="flex items-center">
                    <div class="h-8 w-8 bg-red-100 rounded-lg flex items-center justify-center mr-3">
                        <i class="fas fa-minus-circle text-red-600 text-sm"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">₱{{ number_format($summaryData['total_deductions'], 2) }}</h3>
                        <p class="text-xs text-gray-600">Deductions</p>
                    </div>
                </div>
            </div>

            <!-- Net Pay -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                <div class="flex items-center">
                    <div class="h-8 w-8 bg-indigo-100 rounded-lg flex items-center justify-center mr-3">
                        <i class="fas fa-wallet text-indigo-600 text-sm"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">₱{{ number_format($summaryData['total_net_pay'], 2) }}</h3>
                        <p class="text-xs text-gray-600">Net Pay</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Preview Table -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-gray-900">Payroll Preview - Review Before Finalizing</h2>
                    <div class="flex space-x-3">
                        <a href="{{ route('attendance.period-management.preview-payroll', $period['id']) }}" class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                            <i class="fas fa-sync-alt mr-2"></i>
                            Refresh Preview
                        </a>
                        <button onclick="exportPreviewToCSV()" class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                            <i class="fas fa-download mr-2"></i>
                            Export CSV
                        </button>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Employee</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Department</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Basic Salary</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Regular Holiday</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Special Holiday</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Overtime</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bonuses</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Deductions</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tax</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Gross Pay</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Net Pay</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($previewPayrolls as $payroll)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $payroll['employee_code'] }}</div>
                                <div class="text-sm text-gray-500">{{ $payroll['employee_name'] }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $payroll['department_name'] ?? 'N/A' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <div class="font-medium">₱{{ number_format($payroll['basic_salary'], 2) }}</div>
                                @if(isset($payroll['basic_salary_details']) && $payroll['basic_salary_details']['total_scheduled_hours'] > 0)
                                    <div class="text-blue-600 text-xs">
                                        <i class="fas fa-clock mr-1"></i>{{ number_format($payroll['basic_salary_details']['total_scheduled_hours'], 1) }} hrs worked
                                    </div>
                                    @if(count($payroll['basic_salary_details']['scheduled_hours_details']) > 1)
                                        <div class="text-gray-500 text-xs">
                                            ({{ count($payroll['basic_salary_details']['scheduled_hours_details']) }} working days)
                                        </div>
                                    @endif
                                @else
                                    <div class="text-gray-500 text-xs">
                                        <i class="fas fa-clock mr-1"></i>0.0 hrs worked
                                    </div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <div class="font-medium">₱{{ number_format($payroll['holiday_premium'], 2) }}</div>
                                @if(isset($payroll['regular_holiday_days']) && $payroll['regular_holiday_days'] > 0)
                                    <div class="text-gray-500 text-xs">({{ $payroll['regular_holiday_days'] }} day{{ $payroll['regular_holiday_days'] > 1 ? 's' : '' }})</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <div class="font-medium">₱{{ number_format($payroll['special_holiday_premium'], 2) }}</div>
                                @if(isset($payroll['special_holiday_days']) && $payroll['special_holiday_days'] > 0)
                                    <div class="text-gray-500 text-xs">({{ $payroll['special_holiday_days'] }} day{{ $payroll['special_holiday_days'] > 1 ? 's' : '' }})</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <div class="text-sm">{{ number_format($payroll['overtime_hours'], 1) }} hrs</div>
                                <div class="text-xs text-gray-500">₱{{ number_format($payroll['overtime_hours'] * $payroll['overtime_rate'], 2) }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                ₱{{ number_format($payroll['bonuses'], 2) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-red-600">
                                ₱{{ number_format($payroll['deductions'], 2) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                ₱{{ number_format($payroll['tax_amount'], 2) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                ₱{{ number_format($payroll['gross_pay'], 2) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">
                                ₱{{ number_format($payroll['net_pay'], 2) }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-gray-50">
                        <tr class="font-semibold">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900" colspan="2">TOTAL</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">₱{{ number_format($summaryData['total_basic_salary'] + $summaryData['total_holiday_basic_pay'], 2) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">₱{{ number_format($summaryData['total_holiday_premium'], 2) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">₱{{ number_format($summaryData['total_special_holiday_premium'], 2) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">₱{{ number_format($summaryData['total_overtime_pay'], 2) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">₱{{ number_format($summaryData['total_bonuses'], 2) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-red-600">₱{{ number_format($summaryData['total_deductions'], 2) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">₱{{ number_format($summaryData['total_tax'], 2) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">₱{{ number_format($summaryData['total_gross_pay'], 2) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">₱{{ number_format($summaryData['total_net_pay'], 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="mt-6">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                <div class="px-6 py-8 text-center">
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <i class="fas fa-exclamation-triangle text-yellow-400"></i>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-yellow-800">Important</h3>
                                <div class="mt-2 text-sm text-yellow-700">
                                    <p>Please review all calculations carefully before finalizing. Once approved, payroll records will be created and cannot be easily modified.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="flex justify-center space-x-4">
                        <a href="{{ route('attendance.period-management.show', $period['id']) }}" class="inline-flex items-center px-6 py-3 border border-gray-300 rounded-lg font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                            <i class="fas fa-times mr-2"></i>
                            Cancel
                        </a>
                        
                        <form method="POST" action="{{ route('attendance.period-management.generate-payroll', $period['id']) }}" class="inline">
                            @csrf
                            <button type="submit" class="inline-flex items-center px-6 py-3 bg-green-600 border border-transparent rounded-lg font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors" onclick="return confirm('Are you sure you want to finalize this payroll? This action cannot be undone.')">
                                <i class="fas fa-check mr-2"></i>
                                Approve & Generate Payroll
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function exportPreviewToCSV() {
    // Implementation for CSV export
    alert('CSV export functionality will be implemented');
}
</script>
@endsection
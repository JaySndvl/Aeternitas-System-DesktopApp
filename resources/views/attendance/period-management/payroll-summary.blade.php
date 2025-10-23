@extends('layouts.dashboard-base', ['user' => $user, 'activeRoute' => 'attendance.period-management.index'])

@section('title', 'Payroll Summary - ' . $period['name'])

@php
    function formatCurrency($amount) {
        return '₱' . number_format($amount, 2);
    }
@endphp

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white shadow-sm border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="py-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Payroll Summary</h1>
                        <p class="mt-1 text-sm text-gray-600">
                            {{ \Carbon\Carbon::parse($period['start_date'])->format('M j, Y') }} - 
                            {{ \Carbon\Carbon::parse($period['end_date'])->format('M j, Y') }}
                        </p>
                        <p class="mt-1 text-sm text-gray-500">{{ $period['name'] }}</p>
                    </div>
                    <div class="flex space-x-3">
                        <a href="{{ route('attendance.period-management.export-payroll', $period['id']) }}" class="inline-flex items-center px-4 py-2 bg-purple-600 border border-transparent rounded-lg font-medium text-white hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 transition-colors">
                            <i class="fas fa-download mr-2"></i>
                            Export CSV
                        </a>
                        <a href="{{ route('attendance.period-management.show', $period['id']) }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                            <i class="fas fa-arrow-left mr-2"></i>
                            Back to Period
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
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

            <!-- Total Gross Pay -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                <div class="flex items-center">
                    <div class="h-8 w-8 bg-green-100 rounded-lg flex items-center justify-center mr-3">
                        <i class="fas fa-money-bill-wave text-green-600 text-sm"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">₱{{ number_format($summaryData['total_gross_pay'], 2) }}</h3>
                        <p class="text-xs text-gray-600">Gross Pay</p>
                    </div>
                </div>
            </div>

            <!-- Total Deductions -->
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

            <!-- Total Net Pay -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                <div class="flex items-center">
                    <div class="h-8 w-8 bg-purple-100 rounded-lg flex items-center justify-center mr-3">
                        <i class="fas fa-wallet text-purple-600 text-sm"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">₱{{ number_format($summaryData['total_net_pay'], 2) }}</h3>
                        <p class="text-xs text-gray-600">Net Pay</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detailed Payroll Table -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-gray-900">Payroll Details</h2>
                    <div class="flex space-x-3">
                        <button onclick="exportToCSV()" class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                            <i class="fas fa-download mr-2"></i>
                            Export CSV
                        </button>
                        <button onclick="printPayroll()" class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                            <i class="fas fa-print mr-2"></i>
                            Print
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
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($payrolls as $payroll)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $payroll->employee->employee_id }}</div>
                                <div class="text-sm text-gray-500">{{ $payroll->employee->full_name }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $payroll->employee->department->name ?? 'N/A' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <div class="font-medium">₱{{ number_format($payroll->basic_salary + $payroll->holiday_basic_pay, 2) }}</div>
                                @if($payroll->scheduled_hours > 0)
                                    <div class="text-blue-600 text-xs">
                                        <i class="fas fa-clock mr-1"></i>{{ number_format($payroll->scheduled_hours, 1) }} hrs worked
                                    </div>
                                @else
                                    <div class="text-gray-500 text-xs">
                                        <i class="fas fa-clock mr-1"></i>0.0 hrs worked
                                    </div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <div class="font-medium">₱{{ number_format($payroll->holiday_premium, 2) }}</div>
                                @if($payroll->regular_holiday_days > 0)
                                    <div class="text-blue-600 text-xs">
                                        <i class="fas fa-calendar mr-1"></i>{{ $payroll->regular_holiday_days }} day{{ $payroll->regular_holiday_days > 1 ? 's' : '' }}
                                    </div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <div class="font-medium">₱{{ number_format($payroll->special_holiday_premium, 2) }}</div>
                                @if($payroll->special_holiday_days > 0)
                                    <div class="text-blue-600 text-xs">
                                        <i class="fas fa-calendar mr-1"></i>{{ $payroll->special_holiday_days }} day{{ $payroll->special_holiday_days > 1 ? 's' : '' }}
                                    </div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <div class="text-sm">{{ number_format($payroll->overtime_hours, 1) }} hrs</div>
                                <div class="text-xs text-gray-500">₱{{ number_format($payroll->overtime_hours * $payroll->overtime_rate, 2) }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                ₱{{ number_format($payroll->bonuses, 2) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                ₱{{ number_format($payroll->deductions, 2) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                ₱{{ number_format($payroll->tax_amount, 2) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                ₱{{ number_format($payroll->gross_pay, 2) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">
                                ₱{{ number_format($payroll->net_pay, 2) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    @if($payroll->status === 'pending') bg-yellow-100 text-yellow-800
                                    @elseif($payroll->status === 'processed') bg-blue-100 text-blue-800
                                    @elseif($payroll->status === 'paid') bg-green-100 text-green-800
                                    @else bg-gray-100 text-gray-800
                                    @endif">
                                    {{ ucfirst($payroll->status) }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-gray-50">
                        <tr class="font-semibold">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900" colspan="2">TOTAL</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">₱{{ number_format($summaryData['total_basic_salary'], 2) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <div class="text-sm">{{ number_format($summaryData['total_overtime_hours'], 1) }} hrs</div>
                                <div class="text-xs text-gray-500">₱{{ number_format($summaryData['total_overtime_pay'], 2) }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">₱{{ number_format($summaryData['total_bonuses'], 2) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">₱{{ number_format($summaryData['total_deductions'], 2) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">₱{{ number_format($summaryData['total_tax'], 2) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">₱{{ number_format($summaryData['total_gross_pay'], 2) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">₱{{ number_format($summaryData['total_net_pay'], 2) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        @if(empty($payrolls))
            <div class="text-center py-12">
                <div class="mx-auto h-16 w-16 text-gray-400">
                    <i class="fas fa-calculator text-4xl"></i>
                </div>
                <h3 class="mt-4 text-lg font-medium text-gray-900">No payroll records found</h3>
                <p class="mt-2 text-sm text-gray-600">No payroll data available for this period. Generate payroll first.</p>
                <div class="mt-6">
                    <a href="{{ route('attendance.period-management.show', $period['id']) }}" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-lg font-medium text-white hover:bg-green-700">
                        <i class="fas fa-calculator mr-2"></i>
                        Generate Payroll
                    </a>
                </div>
            </div>
        @endif
    </div>
</div>

<script>
function exportToCSV() {
    window.location.href = "{{ route('attendance.period-management.export-payroll', $period['id']) }}";
}

function printPayroll() {
    window.print();
}

// Auto-refresh every 30 seconds if there are pending payrolls
@if($payrolls->where('status', 'pending')->count() > 0)
setTimeout(function() {
    location.reload();
}, 30000);
@endif
</script>
@endsection

@extends('layouts.dashboard-base', ['user' => $user, 'activeRoute' => 'employees.index'])

@section('title', 'Employee Payroll')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Payroll History</h1>
            <p class="mt-1 text-sm text-gray-600">Payroll records for {{ $employee->full_name }}</p>
        </div>
        <div class="mt-4 sm:mt-0">
            <a href="{{ route('employees.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                <i class="fas fa-arrow-left mr-2"></i>
                Back to Employees
            </a>
        </div>
    </div>

    <!-- Employee Info Card -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 sm:p-8">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center space-x-6 sm:space-x-8 lg:space-x-4">
                <div class="flex-shrink-0">
                    <div class="h-20 w-20 sm:h-24 sm:w-24 rounded-full bg-gradient-to-r from-blue-500 to-blue-600 flex items-center justify-center shadow-lg border-4 border-white">
                        <span class="text-xl sm:text-2xl font-bold text-white">
                            {{ strtoupper(substr($employee->first_name, 0, 1) . substr($employee->last_name, 0, 1)) }}
                        </span>
                    </div>
                </div>
                <div class="min-w-0 flex-1 space-y-2">
                    <h2 class="text-xl sm:text-2xl font-bold text-gray-900">{{ $employee->full_name }}</h2>
                    <p class="text-base text-gray-600 font-medium">{{ $employee->position }}</p>
                    <p class="text-sm text-gray-500">{{ $employee->department->name }}</p>
                </div>
            </div>
            <div class="mt-6 sm:mt-0 text-right space-y-2">
                <div class="text-sm text-gray-500">Monthly Salary</div>
                <div class="text-2xl sm:text-3xl font-bold text-gray-900">₱{{ number_format($employee->salary, 2) }}</div>
                <div class="text-sm text-gray-500">Hired: {{ $employee->hire_date->format('M d, Y') }}</div>
            </div>
        </div>
    </div>

    <!-- Payroll Records -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-4 sm:px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Payroll Records</h3>
            <p class="mt-1 text-sm text-gray-600">Complete payroll history for this employee</p>
        </div>

        @if($employee->payrolls->count() > 0)
            <!-- Desktop Table -->
            <div class="hidden lg:block overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Pay Period
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Basic Salary
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Overtime
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Bonuses
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Deductions
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Net Pay
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Processed
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($employee->payrolls as $payroll)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">
                                    {{ $payroll->pay_period_start->format('M d') }} - {{ $payroll->pay_period_end->format('M d, Y') }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">₱{{ number_format($payroll->basic_salary, 2) }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">
                                    {{ $payroll->overtime_hours }}h @ ₱{{ number_format($payroll->overtime_rate, 2) }}/h
                                </div>
                                <div class="text-xs text-gray-500">
                                    ₱{{ number_format($payroll->overtime_hours * $payroll->overtime_rate, 2) }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">₱{{ number_format($payroll->bonuses, 2) }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">₱{{ number_format($payroll->deductions, 2) }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-semibold text-gray-900">₱{{ number_format($payroll->net_pay, 2) }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    @if($payroll->status === 'paid') bg-green-100 text-green-800
                                    @elseif($payroll->status === 'processed') bg-blue-100 text-blue-800
                                    @elseif($payroll->status === 'pending') bg-yellow-100 text-yellow-800
                                    @else bg-red-100 text-red-800
                                    @endif">
                                    <div class="w-1.5 h-1.5 rounded-full mr-1.5
                                        @if($payroll->status === 'paid') bg-green-400
                                        @elseif($payroll->status === 'processed') bg-blue-400
                                        @elseif($payroll->status === 'pending') bg-yellow-400
                                        @else bg-red-400
                                        @endif"></div>
                                    {{ ucfirst($payroll->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">
                                    {{ $payroll->processed_at ? $payroll->processed_at->format('M d, Y') : '-' }}
                                </div>
                                @if($payroll->processed_at)
                                <div class="text-xs text-gray-500">
                                    {{ $payroll->processed_at->format('g:i A') }}
                                </div>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Mobile Cards -->
            <div class="lg:hidden">
                @foreach($employee->payrolls as $payroll)
                <div class="border-b border-gray-200 p-4 hover:bg-gray-50 transition-colors">
                    <div class="flex items-start justify-between mb-3">
                        <div>
                            <div class="text-sm font-medium text-gray-900">
                                {{ $payroll->pay_period_start->format('M d') }} - {{ $payroll->pay_period_end->format('M d, Y') }}
                            </div>
                            <div class="text-xs text-gray-500">
                                {{ $payroll->processed_at ? 'Processed: ' . $payroll->processed_at->format('M d, Y g:i A') : 'Not processed' }}
                            </div>
                        </div>
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                            @if($payroll->status === 'paid') bg-green-100 text-green-800
                            @elseif($payroll->status === 'processed') bg-blue-100 text-blue-800
                            @elseif($payroll->status === 'pending') bg-yellow-100 text-yellow-800
                            @else bg-red-100 text-red-800
                            @endif">
                            <div class="w-1.5 h-1.5 rounded-full mr-1
                                @if($payroll->status === 'paid') bg-green-400
                                @elseif($payroll->status === 'processed') bg-blue-400
                                @elseif($payroll->status === 'pending') bg-yellow-400
                                @else bg-red-400
                                @endif"></div>
                            {{ ucfirst($payroll->status) }}
                        </span>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <div class="text-gray-500">Basic Salary</div>
                            <div class="font-medium text-gray-900">₱{{ number_format($payroll->basic_salary, 2) }}</div>
                        </div>
                        <div>
                            <div class="text-gray-500">Overtime</div>
                            <div class="font-medium text-gray-900">
                                {{ $payroll->overtime_hours }}h @ ₱{{ number_format($payroll->overtime_rate, 2) }}
                            </div>
                        </div>
                        <div>
                            <div class="text-gray-500">Bonuses</div>
                            <div class="font-medium text-gray-900">₱{{ number_format($payroll->bonuses, 2) }}</div>
                        </div>
                        <div>
                            <div class="text-gray-500">Deductions</div>
                            <div class="font-medium text-gray-900">₱{{ number_format($payroll->deductions, 2) }}</div>
                        </div>
                    </div>
                    
                    <div class="mt-3 pt-3 border-t border-gray-200">
                        <div class="flex justify-between items-center">
                            <span class="text-sm font-medium text-gray-700">Net Pay</span>
                            <span class="text-lg font-semibold text-gray-900">₱{{ number_format($payroll->net_pay, 2) }}</span>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        @else
            <!-- No Payroll Records -->
            <div class="p-8 text-center">
                <div class="text-gray-500">
                    <i class="fas fa-money-bill-wave text-4xl mb-4"></i>
                    <p class="text-lg font-medium">No payroll records found</p>
                    <p class="text-sm">This employee doesn't have any payroll records yet.</p>
                </div>
            </div>
        @endif
    </div>

    <!-- Summary Stats -->
    @if($employee->payrolls->count() > 0)
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 sm:p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-receipt text-blue-600"></i>
                    </div>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-gray-500">Total Records</p>
                    <p class="text-lg font-semibold text-gray-900">{{ $employee->payrolls->count() }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 sm:p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-check-circle text-green-600"></i>
                    </div>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-gray-500">Paid</p>
                    <p class="text-lg font-semibold text-gray-900">{{ $employee->payrolls->where('status', 'paid')->count() }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 sm:p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-yellow-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-clock text-yellow-600"></i>
                    </div>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-gray-500">Pending</p>
                    <p class="text-lg font-semibold text-gray-900">{{ $employee->payrolls->where('status', 'pending')->count() }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 sm:p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-peso-sign text-purple-600"></i>
                    </div>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-gray-500">Total Paid</p>
                    <p class="text-lg font-semibold text-gray-900">₱{{ number_format($employee->payrolls->where('status', 'paid')->sum('net_pay'), 2) }}</p>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection

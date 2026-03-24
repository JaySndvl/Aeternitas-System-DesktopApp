@extends('layouts.dashboard-base')

@section('title', 'Manager Dashboard')

@php
    $user = auth()->user();
    $pageTitle = 'Manager Dashboard';
    $activeRoute = 'dashboard';
@endphp

@section('content')
    <!-- Welcome Section -->
    <div class="mb-6 sm:mb-8">
        <h2 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-2">Welcome back, {{ $user->full_name }}!</h2>
        <p class="text-sm sm:text-base text-gray-600">Here's what's happening in your department: <strong>{{ $department->name }}</strong></p>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 mb-6 sm:mb-8">
        <x-dashboard.stats-card 
            title="Department Employees" 
            :value="number_format($stats['total_employees'])" 
            icon="fas fa-users" 
            color="blue" 
        />
        
        <x-dashboard.stats-card 
            title="Total Budget" 
            :value="'₱' . number_format($stats['total_budget'], 2)" 
            icon="fas fa-money-bill-wave" 
            color="green" 
        />
        
        <x-dashboard.stats-card 
            title="Used Budget" 
            :value="'₱' . number_format($stats['used_budget'], 2)" 
            icon="fas fa-chart-pie" 
            color="yellow" 
        />
        
        <x-dashboard.stats-card 
            title="Remaining Budget" 
            :value="'₱' . number_format($stats['remaining_budget'], 2)" 
            icon="fas fa-wallet" 
            color="purple" 
        />
    </div>

    <!-- Additional Stats -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        <x-dashboard.stats-card 
            title="Average Salary" 
            :value="'₱' . number_format($stats['average_salary'], 2)" 
            icon="fas fa-calculator" 
            color="indigo" 
        />
        
        <x-dashboard.stats-card 
            title="Budget Utilization" 
            :value="number_format(($stats['used_budget'] / $stats['total_budget']) * 100, 1) . '%'" 
            icon="fas fa-percentage" 
            color="red" 
        />
    </div>

    <!-- Charts and Tables Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
        <!-- Monthly Payroll Summary -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-semibold text-gray-900">Monthly Payroll Summary</h3>
                <button class="text-blue-600 hover:text-blue-700 text-sm font-medium">View All</button>
            </div>
            <div class="space-y-4">
                @forelse($monthly_payroll_summary as $summary)
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <div>
                        <p class="text-sm font-medium text-gray-900">{{ date('F Y', mktime(0, 0, 0, $summary->month, 1, $summary->year)) }}</p>
                        <p class="text-xs text-gray-500">{{ $summary->payroll_count }} payrolls processed</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-medium text-gray-900">₱{{ number_format($summary->total_net_pay, 2) }}</p>
                        <p class="text-xs text-gray-500">Net Pay</p>
                    </div>
                </div>
                @empty
                <div class="text-center py-8">
                    <i class="fas fa-chart-line text-gray-400 text-4xl mb-4"></i>
                    <p class="text-gray-500">No payroll data available</p>
                </div>
                @endforelse
            </div>
        </div>

        <!-- Department Activity -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-semibold text-gray-900">Department Activity</h3>
                <button class="text-blue-600 hover:text-blue-700 text-sm font-medium">View All</button>
            </div>
            <div class="space-y-4">
                <div class="flex items-center">
                    <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center mr-3">
                        <i class="fas fa-user-plus text-green-600 text-sm"></i>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-900">New team member added</p>
                        <p class="text-xs text-gray-500">2 hours ago</p>
                    </div>
                </div>
                <div class="flex items-center">
                    <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center mr-3">
                        <i class="fas fa-money-bill-wave text-blue-600 text-sm"></i>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-900">Payroll processed</p>
                        <p class="text-xs text-gray-500">4 hours ago</p>
                    </div>
                </div>
                <div class="flex items-center">
                    <div class="w-8 h-8 bg-yellow-100 rounded-full flex items-center justify-center mr-3">
                        <i class="fas fa-edit text-yellow-600 text-sm"></i>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-900">Employee record updated</p>
                        <p class="text-xs text-gray-500">6 hours ago</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Department Employees Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900">Department Employees</h3>
                <button class="text-blue-600 hover:text-blue-700 text-sm font-medium">View All</button>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Employee</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Position</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Salary</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Hire Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($department_employees as $employee)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                                    <i class="fas fa-user text-blue-600"></i>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900">{{ $employee->full_name }}</div>
                                    <div class="text-sm text-gray-500">{{ $employee->employee_id }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $employee->position }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">₱{{ number_format($employee->salary, 2) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $employee->hire_date->format('M d, Y') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                Active
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection

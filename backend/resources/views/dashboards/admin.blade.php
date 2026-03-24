@extends('layouts.dashboard-base')

@section('title', 'Admin Dashboard')

@php
    $user = auth()->user();
    $pageTitle = 'Admin Dashboard';
    $activeRoute = 'dashboard';
@endphp

@section('content')
    <!-- Welcome Section -->
    <div class="mb-6 sm:mb-8">
        <h2 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-2">Welcome back, Admin!</h2>
        <p class="text-sm sm:text-base text-gray-600">Here's an overview of your entire organization.</p>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 mb-6 sm:mb-8">
        <x-dashboard.stats-card 
            title="Total Employees" 
            :value="number_format($stats['total_employees'])" 
            icon="fas fa-users" 
            color="blue" 
        />
        
        <x-dashboard.stats-card 
            title="Departments" 
            :value="number_format($stats['total_departments'])" 
            icon="fas fa-building" 
            color="green" 
        />
        
        <x-dashboard.stats-card 
            title="Total Budget" 
            :value="'₱' . number_format($stats['total_budget'], 2)" 
            icon="fas fa-money-bill-wave" 
            color="purple" 
        />
        
        <x-dashboard.stats-card 
            title="Used Budget" 
            :value="'₱' . number_format($stats['used_budget'], 2)" 
            icon="fas fa-chart-pie" 
            color="yellow" 
        />
    </div>

    <!-- Additional Stats -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
        <x-dashboard.stats-card 
            title="Remaining Budget" 
            :value="'₱' . number_format($stats['remaining_budget'], 2)" 
            icon="fas fa-wallet" 
            color="green" 
        />
        
        <x-dashboard.stats-card 
            title="Average Salary" 
            :value="'₱' . number_format($stats['average_salary'], 2)" 
            icon="fas fa-calculator" 
            color="indigo" 
        />
        
        <x-dashboard.stats-card 
            title="Total Accounts" 
            :value="number_format($stats['total_accounts'])" 
            icon="fas fa-user-shield" 
            color="red" 
        />
    </div>

    <!-- Charts and Tables Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
        <!-- Department Statistics -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-semibold text-gray-900">Department Statistics</h3>
                <button class="text-blue-600 hover:text-blue-700 text-sm font-medium">View All</button>
            </div>
            <div class="space-y-4">
                @foreach($department_stats as $dept)
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="w-3 h-3 bg-blue-500 rounded-full mr-3"></div>
                        <span class="text-sm font-medium text-gray-700">{{ $dept->name }}</span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <span class="text-sm text-gray-500">{{ $dept->employees_count }} employees</span>
                        <span class="text-sm text-gray-500">₱{{ number_format($dept->employees_sum_salary, 2) }}</span>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-semibold text-gray-900">System Activity</h3>
                <button class="text-blue-600 hover:text-blue-700 text-sm font-medium">View All</button>
            </div>
            <div class="space-y-4">
                <div class="flex items-center">
                    <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center mr-3">
                        <i class="fas fa-user-plus text-green-600 text-sm"></i>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-900">New employee added</p>
                        <p class="text-xs text-gray-500">2 hours ago</p>
                    </div>
                </div>
                <div class="flex items-center">
                    <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center mr-3">
                        <i class="fas fa-building text-blue-600 text-sm"></i>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-900">Department updated</p>
                        <p class="text-xs text-gray-500">4 hours ago</p>
                    </div>
                </div>
                <div class="flex items-center">
                    <div class="w-8 h-8 bg-yellow-100 rounded-full flex items-center justify-center mr-3">
                        <i class="fas fa-money-bill-wave text-yellow-600 text-sm"></i>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-900">Payroll processed</p>
                        <p class="text-xs text-gray-500">6 hours ago</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Employees Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900">Recent Employees</h3>
                <button class="text-blue-600 hover:text-blue-700 text-sm font-medium">View All</button>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Employee</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Department</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Position</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Salary</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Hire Date</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($recent_employees as $employee)
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
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                {{ $employee->department->name }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $employee->position }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">₱{{ number_format($employee->salary, 2) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $employee->hire_date->format('M d, Y') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection

@extends('layouts.dashboard-base', ['user' => $user, 'activeRoute' => 'attendance.timekeeping'])

@section('title', 'Schedule Details')

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white shadow-sm border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="py-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Schedule Details</h1>
                        <p class="mt-1 text-sm text-gray-600">View employee schedule information</p>
                    </div>
                    <div class="flex space-x-3">
                        <a href="{{ isset($currentFilters) ? route('schedule-v2.edit', array_merge(['schedule' => $schedule], array_filter($currentFilters))) : route('schedule-v2.edit', $schedule) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-lg font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                            <i class="fas fa-edit mr-2"></i>
                            Edit Schedule
                        </a>
                        <a href="{{ isset($currentFilters) ? route('schedule-v2.index', array_filter($currentFilters)) : route('schedule-v2.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                            <i class="fas fa-arrow-left mr-2"></i>
                            Back to Schedules
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Content -->
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Schedule Info -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Schedule Information</h3>
                    </div>
                    <div class="p-6">
                        <!-- Employee Info -->
                        <div class="flex items-center space-x-4 mb-6">
                            <div class="flex-shrink-0 h-16 w-16">
                                <div class="h-16 w-16 rounded-full bg-blue-100 flex items-center justify-center">
                                    <span class="text-xl font-medium text-blue-600">
                                        {{ substr($schedule->employee->first_name, 0, 1) }}{{ substr($schedule->employee->last_name, 0, 1) }}
                                    </span>
                                </div>
                            </div>
                            <div>
                                <h4 class="text-xl font-semibold text-gray-900">{{ $schedule->employee->full_name }}</h4>
                                <p class="text-sm text-gray-600">{{ $schedule->employee->position }}</p>
                                <p class="text-sm text-gray-500">{{ $schedule->employee->department->name }}</p>
                            </div>
                        </div>

                        <!-- Schedule Details -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Date</label>
                                <div class="px-3 py-2 bg-gray-50 rounded-lg border border-gray-200">
                                    <span class="text-sm text-gray-900">{{ $schedule->date->format('l, F j, Y') }}</span>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                                <div class="px-3 py-2 bg-gray-50 rounded-lg border border-gray-200">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-{{ $schedule->status_color }}-100 text-{{ $schedule->status_color }}-800">
                                        {{ $schedule->status }}
                                    </span>
                                </div>
                            </div>

                            @if($schedule->time_in)
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Time In</label>
                                    <div class="px-3 py-2 bg-gray-50 rounded-lg border border-gray-200">
                                        <span class="text-sm text-gray-900">{{ \Carbon\Carbon::createFromFormat('H:i:s', $schedule->time_in)->format('g:i A') }}</span>
                                    </div>
                                </div>
                            @endif

                            @if($schedule->time_out)
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Time Out</label>
                                    <div class="px-3 py-2 bg-gray-50 rounded-lg border border-gray-200">
                                        <span class="text-sm text-gray-900">{{ \Carbon\Carbon::createFromFormat('H:i:s', $schedule->time_out)->format('g:i A') }}</span>
                                    </div>
                                </div>
                            @endif

                            @if($schedule->time_in && $schedule->time_out)
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Working Hours</label>
                                    <div class="px-3 py-2 bg-gray-50 rounded-lg border border-gray-200">
                                        <span class="text-sm text-gray-900">{{ $schedule->working_hours }} hours</span>
                                    </div>
                                </div>
                            @endif
                        </div>

                        @if($schedule->notes)
                            <div class="mt-6">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
                                <div class="px-3 py-2 bg-gray-50 rounded-lg border border-gray-200">
                                    <p class="text-sm text-gray-900 whitespace-pre-wrap">{{ $schedule->notes }}</p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Sidebar Info -->
            <div class="space-y-6">
                <!-- Schedule Actions -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Actions</h3>
                    </div>
                    <div class="p-6 space-y-3">
                        <a href="{{ route('schedule-v2.edit', $schedule) }}" class="w-full inline-flex items-center justify-center px-4 py-2 bg-blue-600 border border-transparent rounded-lg font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                            <i class="fas fa-edit mr-2"></i>
                            Edit Schedule
                        </a>
                        
                        <form action="{{ route('schedule-v2.destroy', $schedule) }}" method="POST" class="w-full">
                            @csrf
                            @method('DELETE')
                            <button type="submit" onclick="return confirm('Are you sure you want to delete this schedule?')" class="w-full inline-flex items-center justify-center px-4 py-2 bg-red-600 border border-transparent rounded-lg font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors">
                                <i class="fas fa-trash mr-2"></i>
                                Delete Schedule
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Schedule Metadata -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Schedule Details</h3>
                    </div>
                    <div class="p-6 space-y-4">
                        <div>
                            <span class="text-sm font-medium text-gray-700">Created:</span>
                            <p class="text-sm text-gray-900">{{ $schedule->created_at->format('M j, Y g:i A') }}</p>
                        </div>

                        @if($schedule->creator)
                            <div>
                                <span class="text-sm font-medium text-gray-700">Created by:</span>
                                <p class="text-sm text-gray-900">{{ $schedule->creator->full_name }}</p>
                            </div>
                        @endif

                        <div>
                            <span class="text-sm font-medium text-gray-700">Last updated:</span>
                            <p class="text-sm text-gray-900">{{ $schedule->updated_at->format('M j, Y g:i A') }}</p>
                        </div>
                    </div>
                </div>

                <!-- Employee Quick Info -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Employee Info</h3>
                    </div>
                    <div class="p-6 space-y-3">
                        <div>
                            <span class="text-sm font-medium text-gray-700">Employee ID:</span>
                            <p class="text-sm text-gray-900">{{ $schedule->employee->employee_id }}</p>
                        </div>
                        
                        <div>
                            <span class="text-sm font-medium text-gray-700">Phone:</span>
                            <p class="text-sm text-gray-900">{{ $schedule->employee->phone }}</p>
                        </div>
                        
                        <div>
                            <span class="text-sm font-medium text-gray-700">Hire Date:</span>
                            <p class="text-sm text-gray-900">{{ $schedule->employee->hire_date->format('M j, Y') }}</p>
                        </div>
                        
                        <div>
                            <span class="text-sm font-medium text-gray-700">Salary:</span>
                            <p class="text-sm text-gray-900">₱{{ number_format($schedule->employee->salary, 2) }}</p>
                        </div>
                        
                        <div class="pt-3 border-t border-gray-200">
                            <a href="{{ route('employees.show', $schedule->employee) }}" class="text-blue-600 hover:text-blue-700 text-sm font-medium">
                                View Employee Profile →
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

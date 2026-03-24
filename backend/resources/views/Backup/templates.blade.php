@extends('layouts.dashboard-base', ['user' => $user, 'activeRoute' => 'attendance.schedule'])

@section('title', 'Schedule Templates')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Schedule Templates</h1>
            <p class="mt-1 text-sm text-gray-600">Create and manage reusable schedule templates</p>
        </div>
        <div class="mt-4 sm:mt-0 flex space-x-3">
            <a href="{{ route('schedule-v2.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                <i class="fas fa-arrow-left mr-2"></i>
                Back to Schedule
            </a>
            <button onclick="createNewTemplate()" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-lg font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                <i class="fas fa-plus mr-2"></i>
                Create Template
            </button>
        </div>
    </div>

    <!-- Template Summary -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 sm:p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-file-alt text-blue-600"></i>
                    </div>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-gray-500">Total Templates</p>
                    <p class="text-lg font-semibold text-gray-900">8</p>
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
                    <p class="text-sm font-medium text-gray-500">Active Templates</p>
                    <p class="text-lg font-semibold text-gray-900">6</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 sm:p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-yellow-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-users text-yellow-600"></i>
                    </div>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-gray-500">Used by Employees</p>
                    <p class="text-lg font-semibold text-gray-900">24</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 sm:p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-clock text-purple-600"></i>
                    </div>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-gray-500">Last Updated</p>
                    <p class="text-lg font-semibold text-gray-900">2 days ago</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Schedule Templates -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-medium text-gray-900">Available Templates</h3>
            <div class="flex space-x-2">
                <button class="px-3 py-1 text-sm border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                    <i class="fas fa-filter mr-1"></i>Filter
                </button>
                <button class="px-3 py-1 text-sm border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                    <i class="fas fa-sort mr-1"></i>Sort
                </button>
            </div>
        </div>
        
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            <!-- Template 1 -->
            <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between mb-2">
                    <h4 class="font-medium text-gray-900">Regular Shift</h4>
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                        Active
                    </span>
                </div>
                <p class="text-sm text-gray-600 mb-3">Monday to Friday, 8:00 AM - 5:00 PM</p>
                <div class="space-y-2 mb-4">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Used by:</span>
                        <span class="font-medium text-gray-900">12 employees</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Total Hours:</span>
                        <span class="font-medium text-gray-900">40h/week</span>
                    </div>
                </div>
                <div class="flex space-x-2">
                    <button class="text-blue-600 hover:text-blue-900 text-sm transition-colors">
                        <i class="fas fa-edit mr-1"></i>Edit
                    </button>
                    <button class="text-green-600 hover:text-green-900 text-sm transition-colors">
                        <i class="fas fa-copy mr-1"></i>Duplicate
                    </button>
                    <button class="text-purple-600 hover:text-purple-900 text-sm transition-colors">
                        <i class="fas fa-eye mr-1"></i>Preview
                    </button>
                </div>
            </div>

            <!-- Template 2 -->
            <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between mb-2">
                    <h4 class="font-medium text-gray-900">Flexible Hours</h4>
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                        Active
                    </span>
                </div>
                <p class="text-sm text-gray-600 mb-3">Monday to Friday, 9:00 AM - 6:00 PM</p>
                <div class="space-y-2 mb-4">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Used by:</span>
                        <span class="font-medium text-gray-900">8 employees</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Total Hours:</span>
                        <span class="font-medium text-gray-900">40h/week</span>
                    </div>
                </div>
                <div class="flex space-x-2">
                    <button class="text-blue-600 hover:text-blue-900 text-sm transition-colors">
                        <i class="fas fa-edit mr-1"></i>Edit
                    </button>
                    <button class="text-green-600 hover:text-green-900 text-sm transition-colors">
                        <i class="fas fa-copy mr-1"></i>Duplicate
                    </button>
                    <button class="text-purple-600 hover:text-purple-900 text-sm transition-colors">
                        <i class="fas fa-eye mr-1"></i>Preview
                    </button>
                </div>
            </div>

            <!-- Template 3 -->
            <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between mb-2">
                    <h4 class="font-medium text-gray-900">Part-time</h4>
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                        Active
                    </span>
                </div>
                <p class="text-sm text-gray-600 mb-3">Monday to Saturday, 10:00 AM - 2:00 PM</p>
                <div class="space-y-2 mb-4">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Used by:</span>
                        <span class="font-medium text-gray-900">4 employees</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Total Hours:</span>
                        <span class="font-medium text-gray-900">24h/week</span>
                    </div>
                </div>
                <div class="flex space-x-2">
                    <button class="text-blue-600 hover:text-blue-900 text-sm transition-colors">
                        <i class="fas fa-edit mr-1"></i>Edit
                    </button>
                    <button class="text-green-600 hover:text-green-900 text-sm transition-colors">
                        <i class="fas fa-copy mr-1"></i>Duplicate
                    </button>
                    <button class="text-purple-600 hover:text-purple-900 text-sm transition-colors">
                        <i class="fas fa-eye mr-1"></i>Preview
                    </button>
                </div>
            </div>

            <!-- Template 4 -->
            <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between mb-2">
                    <h4 class="font-medium text-gray-900">Night Shift</h4>
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                        Active
                    </span>
                </div>
                <p class="text-sm text-gray-600 mb-3">Monday to Friday, 10:00 PM - 6:00 AM</p>
                <div class="space-y-2 mb-4">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Used by:</span>
                        <span class="font-medium text-gray-900">3 employees</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Total Hours:</span>
                        <span class="font-medium text-gray-900">40h/week</span>
                    </div>
                </div>
                <div class="flex space-x-2">
                    <button class="text-blue-600 hover:text-blue-900 text-sm transition-colors">
                        <i class="fas fa-edit mr-1"></i>Edit
                    </button>
                    <button class="text-green-600 hover:text-green-900 text-sm transition-colors">
                        <i class="fas fa-copy mr-1"></i>Duplicate
                    </button>
                    <button class="text-purple-600 hover:text-purple-900 text-sm transition-colors">
                        <i class="fas fa-eye mr-1"></i>Preview
                    </button>
                </div>
            </div>

            <!-- Template 5 -->
            <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between mb-2">
                    <h4 class="font-medium text-gray-900">Weekend Only</h4>
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                        Draft
                    </span>
                </div>
                <p class="text-sm text-gray-600 mb-3">Saturday and Sunday, 9:00 AM - 5:00 PM</p>
                <div class="space-y-2 mb-4">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Used by:</span>
                        <span class="font-medium text-gray-900">0 employees</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Total Hours:</span>
                        <span class="font-medium text-gray-900">16h/week</span>
                    </div>
                </div>
                <div class="flex space-x-2">
                    <button class="text-blue-600 hover:text-blue-900 text-sm transition-colors">
                        <i class="fas fa-edit mr-1"></i>Edit
                    </button>
                    <button class="text-green-600 hover:text-green-900 text-sm transition-colors">
                        <i class="fas fa-copy mr-1"></i>Duplicate
                    </button>
                    <button class="text-purple-600 hover:text-purple-900 text-sm transition-colors">
                        <i class="fas fa-eye mr-1"></i>Preview
                    </button>
                </div>
            </div>

            <!-- Template 6 -->
            <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between mb-2">
                    <h4 class="font-medium text-gray-900">Split Shift</h4>
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                        Testing
                    </span>
                </div>
                <p class="text-sm text-gray-600 mb-3">Morning: 8:00-12:00, Evening: 4:00-8:00</p>
                <div class="space-y-2 mb-4">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Used by:</span>
                        <span class="font-medium text-gray-900">2 employees</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Total Hours:</span>
                        <span class="font-medium text-gray-900">40h/week</span>
                    </div>
                </div>
                <div class="flex space-x-2">
                    <button class="text-blue-600 hover:text-blue-900 text-sm transition-colors">
                        <i class="fas fa-edit mr-1"></i>Edit
                    </button>
                    <button class="text-green-600 hover:text-green-900 text-sm transition-colors">
                        <i class="fas fa-copy mr-1"></i>Duplicate
                    </button>
                    <button class="text-purple-600 hover:text-purple-900 text-sm transition-colors">
                        <i class="fas fa-eye mr-1"></i>Preview
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function createNewTemplate() {
    console.log('Creating new template...');
    // This would open a modal or redirect to template creation page
}
</script>
@endsection


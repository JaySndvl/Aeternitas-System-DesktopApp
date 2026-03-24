@extends('layouts.dashboard-base', ['user' => $user, 'activeRoute' => 'positions.index'])

@section('title', 'Position Details')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">{{ $position->name }}</h1>
                <p class="mt-1 text-sm text-gray-600">Position details and information</p>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('positions.edit', $position) }}" class="inline-flex items-center px-4 py-2 bg-yellow-600 border border-transparent rounded-lg font-medium text-white hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500 transition-colors">
                    <i class="fas fa-edit mr-2"></i>
                    Edit Position
                </a>
                <a href="{{ route('positions.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Back to Positions
                </a>
            </div>
        </div>

        <!-- Position Info Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-briefcase text-blue-600 text-xl"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Position Code</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $position->code }}</p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-building text-green-600 text-xl"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Department</p>
                        <p class="text-lg font-bold text-gray-900">{{ $position->department->name ?? 'No Department' }}</p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-layer-group text-purple-600 text-xl"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Level</p>
                        <p class="text-lg font-bold text-gray-900">{{ $position->level }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Position Details -->
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Position Information</h3>
                    </div>
                    <div class="p-6">
                        <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Position Name</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $position->name }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Position Code</dt>
                                <dd class="mt-1">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        {{ $position->code }}
                                    </span>
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Department</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $position->department }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Level</dt>
                                <dd class="mt-1">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        {{ $position->level === 'Senior' ? 'bg-purple-100 text-purple-800' : 
                                           ($position->level === 'Mid' ? 'bg-blue-100 text-blue-800' : 
                                           ($position->level === 'Lead' ? 'bg-indigo-100 text-indigo-800' : 'bg-gray-100 text-gray-800')) }}">
                                        {{ $position->level }}
                                    </span>
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Status</dt>
                                <dd class="mt-1">
                                    @if($position->is_active)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            Active
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            Inactive
                                        </span>
                                    @endif
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Salary Range</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $position->salary_range }}</dd>
                            </div>
                        </dl>
                        
                        @if($position->description)
                        <div class="mt-6">
                            <dt class="text-sm font-medium text-gray-500">Description</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $position->description }}</dd>
                        </div>
                        @endif
                    </div>
                </div>

                @if($position->requirements && count($position->requirements) > 0)
                <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Requirements</h3>
                    </div>
                    <div class="p-6">
                        <ul class="space-y-3">
                            @foreach($position->requirements as $requirement)
                                <li class="flex items-start">
                                    <i class="fas fa-check text-green-500 mt-1 mr-3 flex-shrink-0"></i>
                                    <span class="text-sm text-gray-900">{{ $requirement }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                @endif

                @if($position->responsibilities && count($position->responsibilities) > 0)
                <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Responsibilities</h3>
                    </div>
                    <div class="p-6">
                        <ul class="space-y-3">
                            @foreach($position->responsibilities as $responsibility)
                                <li class="flex items-start">
                                    <i class="fas fa-tasks text-blue-500 mt-1 mr-3 flex-shrink-0"></i>
                                    <span class="text-sm text-gray-900">{{ $responsibility }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                @endif
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Quick Actions -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Quick Actions</h3>
                    </div>
                    <div class="p-6 space-y-3">
                        <a href="{{ route('positions.edit', $position) }}" class="w-full inline-flex items-center justify-center px-4 py-2 bg-yellow-600 border border-transparent rounded-lg font-medium text-white hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500 transition-colors">
                            <i class="fas fa-edit mr-2"></i>
                            Edit Position
                        </a>
                        <form action="{{ route('positions.destroy', $position) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this position?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="w-full inline-flex items-center justify-center px-4 py-2 bg-red-600 border border-transparent rounded-lg font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors">
                                <i class="fas fa-trash mr-2"></i>
                                Delete Position
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Position Stats -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Position Stats</h3>
                    </div>
                    <div class="p-6">
                        <div class="space-y-4">
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-500">Created</span>
                                <span class="text-sm font-medium text-gray-900">{{ $position->created_at->format('M d, Y') }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-500">Last Updated</span>
                                <span class="text-sm font-medium text-gray-900">{{ $position->updated_at->format('M d, Y') }}</span>
                            </div>
                            @if($position->min_salary && $position->max_salary)
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-500">Salary Range</span>
                                <span class="text-sm font-medium text-gray-900">₱{{ number_format($position->min_salary, 0) }} - ₱{{ number_format($position->max_salary, 0) }}</span>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
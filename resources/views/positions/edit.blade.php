@extends('layouts.dashboard-base', ['user' => $user, 'activeRoute' => 'positions.index'])

@section('title', 'Edit Position')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between space-y-4 sm:space-y-0">
            <div>
                <h1 class="text-xl sm:text-2xl font-bold text-gray-900">Edit Position</h1>
                <p class="mt-1 text-sm text-gray-500">Update position information</p>
            </div>
            <a href="{{ route('positions.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                <i class="fas fa-arrow-left mr-2"></i>
                <span class="hidden sm:inline">Back to Positions</span>
                <span class="sm:hidden">Back</span>
            </a>
        </div>

        <!-- Edit Form -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <form action="{{ route('positions.update', $position) }}" method="POST" class="p-4 sm:p-6 space-y-6">
                @csrf
                @method('PUT')
                
                <!-- Position Information -->
                <div>
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Position Information</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                                Position Name <span class="text-red-500">*</span>
                            </label>
                            <input type="text" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-white text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('name') border-red-500 @enderror" 
                                   id="name" name="name" value="{{ old('name', $position->name) }}" required
                                   placeholder="e.g., Software Engineer">
                            @error('name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div>
                            <label for="code" class="block text-sm font-medium text-gray-700 mb-2">
                                Position Code <span class="text-red-500">*</span>
                            </label>
                            <input type="text" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-white text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('code') border-red-500 @enderror" 
                                   id="code" name="code" value="{{ old('code', $position->code) }}" required maxlength="10"
                                   placeholder="e.g., SE">
                            @error('code')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div class="sm:col-span-2">
                            <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                            <textarea class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-white text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('description') border-red-500 @enderror" 
                                      id="description" name="description" rows="3" placeholder="Describe the position's role and responsibilities...">{{ old('description', $position->description) }}</textarea>
                            @error('description')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Position Details -->
                <div>
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Position Details</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                        <div>
                            <label for="level" class="block text-sm font-medium text-gray-700 mb-2">
                                Level <span class="text-red-500">*</span>
                            </label>
                            <select class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-white text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('level') border-red-500 @enderror" 
                                    id="level" name="level" required>
                                <option value="">Select Level</option>
                                <option value="Entry" {{ old('level', $position->level) == 'Entry' ? 'selected' : '' }}>Entry</option>
                                <option value="Mid" {{ old('level', $position->level) == 'Mid' ? 'selected' : '' }}>Mid</option>
                                <option value="Senior" {{ old('level', $position->level) == 'Senior' ? 'selected' : '' }}>Senior</option>
                                <option value="Lead" {{ old('level', $position->level) == 'Lead' ? 'selected' : '' }}>Lead</option>
                            </select>
                            @error('level')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div>
                            <label for="department_id" class="block text-sm font-medium text-gray-700 mb-2">
                                Department <span class="text-red-500">*</span>
                            </label>
                            <select name="department_id" id="department_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-white text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('department_id') border-red-500 @enderror" required>
                                <option value="">Select Department</option>
                                @if(isset($departments) && is_iterable($departments))
                                    @foreach($departments as $department)
                                        <option value="{{ $department->id }}" {{ old('department_id', $position->department_id) == $department->id ? 'selected' : '' }}>
                                            {{ $department->name }}
                                        </option>
                                    @endforeach
                                @else
                                    <option value="">No departments available</option>
                                @endif
                            </select>
                            @error('department_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div class="sm:col-span-2">
                            <div class="flex items-center">
                                <input type="checkbox" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded" 
                                       id="is_active" name="is_active" value="1" {{ old('is_active', $position->is_active) ? 'checked' : '' }}>
                                <label for="is_active" class="ml-2 block text-sm text-gray-900">
                                    Active Position
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Salary Information -->
                <div>
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Salary Information</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                        <div>
                            <label for="min_salary" class="block text-sm font-medium text-gray-700 mb-2">Minimum Salary</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 sm:text-sm">₱</span>
                                </div>
                                <input type="number" 
                                       class="w-full pl-8 pr-3 py-2 border border-gray-300 rounded-lg bg-white text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('min_salary') border-red-500 @enderror" 
                                       id="min_salary" name="min_salary" value="{{ old('min_salary', $position->min_salary) }}" 
                                       step="0.01" min="0" placeholder="0.00">
                            </div>
                            @error('min_salary')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div>
                            <label for="max_salary" class="block text-sm font-medium text-gray-700 mb-2">Maximum Salary</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 sm:text-sm">₱</span>
                                </div>
                                <input type="number" 
                                       class="w-full pl-8 pr-3 py-2 border border-gray-300 rounded-lg bg-white text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('max_salary') border-red-500 @enderror" 
                                       id="max_salary" name="max_salary" value="{{ old('max_salary', $position->max_salary) }}" 
                                       step="0.01" min="0" placeholder="0.00">
                            </div>
                            @error('max_salary')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Job Details -->
                <div>
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Job Details</h3>
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Requirements</label>
                            <div id="requirements-container">
                                @if(old('requirements'))
                                    @foreach(old('requirements') as $requirement)
                                        <div class="flex mb-2">
                                            <input type="text" class="flex-1 px-3 py-2 border border-gray-300 rounded-lg bg-white text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-transparent" 
                                                   name="requirements[]" value="{{ $requirement }}">
                                            <button type="button" class="ml-2 px-3 py-2 text-red-600 hover:text-red-800 remove-requirement">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    @endforeach
                                @elseif($position->requirements && is_array($position->requirements))
                                    @foreach($position->requirements as $requirement)
                                        <div class="flex mb-2">
                                            <input type="text" class="flex-1 px-3 py-2 border border-gray-300 rounded-lg bg-white text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-transparent" 
                                                   name="requirements[]" value="{{ $requirement }}">
                                            <button type="button" class="ml-2 px-3 py-2 text-red-600 hover:text-red-800 remove-requirement">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    @endforeach
                                @else
                                    <div class="flex mb-2">
                                        <input type="text" class="flex-1 px-3 py-2 border border-gray-300 rounded-lg bg-white text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-transparent" 
                                               name="requirements[]" placeholder="Enter requirement">
                                        <button type="button" class="ml-2 px-3 py-2 text-red-600 hover:text-red-800 remove-requirement">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                @endif
                            </div>
                            <button type="button" class="mt-2 inline-flex items-center px-3 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500" id="add-requirement">
                                <i class="fas fa-plus mr-2"></i> Add Requirement
                            </button>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Responsibilities</label>
                            <div id="responsibilities-container">
                                @if(old('responsibilities'))
                                    @foreach(old('responsibilities') as $responsibility)
                                        <div class="flex mb-2">
                                            <input type="text" class="flex-1 px-3 py-2 border border-gray-300 rounded-lg bg-white text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-transparent" 
                                                   name="responsibilities[]" value="{{ $responsibility }}">
                                            <button type="button" class="ml-2 px-3 py-2 text-red-600 hover:text-red-800 remove-responsibility">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    @endforeach
                                @elseif($position->responsibilities && is_array($position->responsibilities))
                                    @foreach($position->responsibilities as $responsibility)
                                        <div class="flex mb-2">
                                            <input type="text" class="flex-1 px-3 py-2 border border-gray-300 rounded-lg bg-white text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-transparent" 
                                                   name="responsibilities[]" value="{{ $responsibility }}">
                                            <button type="button" class="ml-2 px-3 py-2 text-red-600 hover:text-red-800 remove-responsibility">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    @endforeach
                                @else
                                    <div class="flex mb-2">
                                        <input type="text" class="flex-1 px-3 py-2 border border-gray-300 rounded-lg bg-white text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-transparent" 
                                               name="responsibilities[]" placeholder="Enter responsibility">
                                        <button type="button" class="ml-2 px-3 py-2 text-red-600 hover:text-red-800 remove-responsibility">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                @endif
                            </div>
                            <button type="button" class="mt-2 inline-flex items-center px-3 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500" id="add-responsibility">
                                <i class="fas fa-plus mr-2"></i> Add Responsibility
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="flex justify-end space-x-3 pt-6 border-t border-gray-200">
                    <a href="{{ route('positions.index') }}" class="px-4 py-2 border border-gray-300 rounded-lg font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                        Cancel
                    </a>
                    <button type="submit" class="px-4 py-2 bg-blue-600 border border-transparent rounded-lg font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                        <i class="fas fa-save mr-2"></i>
                        Update Position
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add requirement
    document.getElementById('add-requirement').addEventListener('click', function() {
        const container = document.getElementById('requirements-container');
        const div = document.createElement('div');
        div.className = 'flex mb-2';
        div.innerHTML = `
            <input type="text" class="flex-1 px-3 py-2 border border-gray-300 rounded-lg bg-white text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" 
                   name="requirements[]" placeholder="Enter requirement">
            <button type="button" class="ml-2 px-3 py-2 text-red-600 hover:text-red-800 remove-requirement">
                <i class="fas fa-trash"></i>
            </button>
        `;
        container.appendChild(div);
    });

    // Remove requirement
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-requirement') || e.target.parentElement.classList.contains('remove-requirement')) {
            e.target.closest('.flex').remove();
        }
    });

    // Add responsibility
    document.getElementById('add-responsibility').addEventListener('click', function() {
        const container = document.getElementById('responsibilities-container');
        const div = document.createElement('div');
        div.className = 'flex mb-2';
        div.innerHTML = `
            <input type="text" class="flex-1 px-3 py-2 border border-gray-300 rounded-lg bg-white text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" 
                   name="responsibilities[]" placeholder="Enter responsibility">
            <button type="button" class="ml-2 px-3 py-2 text-red-600 hover:text-red-800 remove-responsibility">
                <i class="fas fa-trash"></i>
            </button>
        `;
        container.appendChild(div);
    });

    // Remove responsibility
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-responsibility') || e.target.parentElement.classList.contains('remove-responsibility')) {
            e.target.closest('.flex').remove();
        }
    });
    });
</script>

<style>
    /* Ensure all form inputs and selects are visible with dark text */
    input[type="text"],
    input[type="number"],
    textarea,
    select {
        color: #111827 !important; /* text-gray-900 */
        background-color: #ffffff !important; /* bg-white */
    }
    
    /* Force select dropdowns to show dark text */
    #level,
    #department_id {
        color: #111827 !important;
        background-color: #ffffff !important;
    }
    
    /* Ensure select options are visible */
    select option {
        color: #111827 !important;
        background-color: #ffffff !important;
    }
    
    select option:checked {
        color: #111827 !important;
        background-color: #f3f4f6 !important;
    }
    
    select option:hover {
        background-color: #e5e7eb !important;
        color: #111827 !important;
    }
    
    /* Input focus states */
    input[type="text"]:focus,
    input[type="number"]:focus,
    textarea:focus,
    select:focus {
        color: #111827 !important;
        background-color: #ffffff !important;
    }
    
    /* Placeholder text */
    input::placeholder,
    textarea::placeholder {
        color: #9ca3af !important; /* text-gray-400 */
        opacity: 1;
    }
    
    /* Requirements and responsibilities inputs */
    #requirements-container input[type="text"],
    #responsibilities-container input[type="text"] {
        color: #111827 !important;
        background-color: #ffffff !important;
    }
</style>
@endsection
@extends('layouts.dashboard-base', ['user' => $user, 'activeRoute' => 'positions.index'])

@section('title', isset($position) ? 'Edit Position' : 'Create Position')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="space-y-6">
        <!-- Header -->
        <x-forms.page-header 
            :title="isset($position) ? 'Edit Position' : 'Create Position'"
            :subtitle="isset($position) ? 'Update position information' : 'Add a new position to the organization'"
            :back-route="route('positions.index')"
            back-text="Back to Positions"
        />

        <!-- Form -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <form action="{{ isset($position) ? route('positions.update', $position) : route('positions.store') }}" 
                  method="POST" 
                  class="p-4 sm:p-6 space-y-6">
                @csrf
                @if(isset($position))
                    @method('PUT')
                @endif
                
                <!-- Position Information -->
                <div>
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Position Information</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                        <x-forms.input 
                            label="Position Name"
                            name="name"
                            :value="isset($position) ? $position->name : ''"
                            required
                            placeholder="e.g., Software Engineer"
                        />
                        
                        <x-forms.input 
                            label="Position Code"
                            name="code"
                            :value="isset($position) ? $position->code : ''"
                            required
                            maxlength="10"
                            placeholder="e.g., SE"
                        />
                        
                        <div class="sm:col-span-2">
                            <x-forms.input 
                                label="Description"
                                name="description"
                                type="textarea"
                                :value="isset($position) ? $position->description : ''"
                                rows="3"
                                placeholder="Describe the position's role and responsibilities..."
                            />
                        </div>
                    </div>
                </div>

                <!-- Position Details -->
                <div>
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Position Details</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                        <x-forms.select 
                            label="Level"
                            name="level"
                            :value="isset($position) ? $position->level : ''"
                            required
                            :options="['Entry' => 'Entry', 'Mid' => 'Mid', 'Senior' => 'Senior', 'Lead' => 'Lead']"
                            placeholder="Select Level"
                        />
                        
                        <x-forms.select 
                            label="Department"
                            name="department_id"
                            :value="isset($position) ? $position->department_id : ''"
                            required
                            :options="isset($departments) && is_iterable($departments) ? $departments->pluck('name', 'id')->toArray() : []"
                            placeholder="Select Department"
                        />
                        
                        <div class="sm:col-span-2">
                            <div class="flex items-center">
                                <input type="checkbox" 
                                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded" 
                                       id="is_active" 
                                       name="is_active" 
                                       value="1" 
                                       {{ old('is_active', isset($position) ? $position->is_active : true) ? 'checked' : '' }}>
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
                                       id="min_salary" 
                                       name="min_salary" 
                                       value="{{ old('min_salary', isset($position) ? $position->min_salary : '') }}" 
                                       step="0.01" 
                                       min="0" 
                                       placeholder="0.00">
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
                                       id="max_salary" 
                                       name="max_salary" 
                                       value="{{ old('max_salary', isset($position) ? $position->max_salary : '') }}" 
                                       step="0.01" 
                                       min="0" 
                                       placeholder="0.00">
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
                                @if(isset($position) && $position->requirements)
                                    @foreach(is_array($position->requirements) ? $position->requirements : json_decode($position->requirements, true) as $requirement)
                                        <div class="flex mb-2">
                                            <input type="text" 
                                                   class="flex-1 px-3 py-2 border border-gray-300 rounded-lg bg-white text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-transparent" 
                                                   name="requirements[]" 
                                                   placeholder="Enter requirement"
                                                   value="{{ $requirement }}">
                                            <button type="button" class="ml-2 px-3 py-2 text-red-600 hover:text-red-800 remove-requirement">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    @endforeach
                                @else
                                    <div class="flex mb-2">
                                        <input type="text" 
                                               class="flex-1 px-3 py-2 border border-gray-300 rounded-lg bg-white text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-transparent" 
                                               name="requirements[]" 
                                               placeholder="Enter requirement">
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
                                @if(isset($position) && $position->responsibilities)
                                    @foreach(is_array($position->responsibilities) ? $position->responsibilities : json_decode($position->responsibilities, true) as $responsibility)
                                        <div class="flex mb-2">
                                            <input type="text" 
                                                   class="flex-1 px-3 py-2 border border-gray-300 rounded-lg bg-white text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-transparent" 
                                                   name="responsibilities[]" 
                                                   placeholder="Enter responsibility"
                                                   value="{{ $responsibility }}">
                                            <button type="button" class="ml-2 px-3 py-2 text-red-600 hover:text-red-800 remove-responsibility">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    @endforeach
                                @else
                                    <div class="flex mb-2">
                                        <input type="text" 
                                               class="flex-1 px-3 py-2 border border-gray-300 rounded-lg bg-white text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-transparent" 
                                               name="responsibilities[]" 
                                               placeholder="Enter responsibility">
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
                        {{ isset($position) ? 'Update Position' : 'Create Position' }}
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
            <input type="text" class="flex-1 px-3 py-2 border border-gray-300 rounded-lg bg-white text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-transparent" 
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
            const container = document.getElementById('requirements-container');
            if (container.children.length > 1) {
                e.target.closest('.flex').remove();
            }
        }
    });

    // Add responsibility
    document.getElementById('add-responsibility').addEventListener('click', function() {
        const container = document.getElementById('responsibilities-container');
        const div = document.createElement('div');
        div.className = 'flex mb-2';
        div.innerHTML = `
            <input type="text" class="flex-1 px-3 py-2 border border-gray-300 rounded-lg bg-white text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-transparent" 
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
            const container = document.getElementById('responsibilities-container');
            if (container.children.length > 1) {
                e.target.closest('.flex').remove();
            }
        }
    });
});
</script>

<style>
    /* Ensure all form inputs and selects are visible with dark text */
    input[type="text"],
    input[type="number"],
    input[type="checkbox"],
    textarea,
    select {
        color: #111827 !important;
        background-color: #ffffff !important;
    }
    
    select option {
        color: #111827 !important;
        background-color: #ffffff !important;
    }
    
    input::placeholder,
    textarea::placeholder {
        color: #9ca3af !important;
        opacity: 1;
    }
</style>
@endsection

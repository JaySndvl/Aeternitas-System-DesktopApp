@extends('layouts.dashboard-base', ['user' => $user, 'activeRoute' => 'departments.index'])

@section('title', isset($department) ? 'Edit Department' : 'Create Department')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="space-y-6">
        <!-- Header -->
        <x-forms.page-header 
            :title="isset($department) ? 'Edit Department' : 'Create Department'"
            :subtitle="isset($department) ? 'Update department information' : 'Add a new department to the organization'"
            :back-route="route('departments.index')"
            back-text="Back to Departments"
        />

        <!-- Form -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <form method="POST" 
                  action="{{ isset($department) ? route('departments.update', $department) : route('departments.store') }}" 
                  class="p-6 space-y-6">
                @csrf
                @if(isset($department))
                    @method('PUT')
                @endif

                <!-- Department Name -->
                <x-forms.input 
                    label="Department Name"
                    name="name"
                    :value="isset($department) ? $department->name : ''"
                    required
                    placeholder="e.g., Human Resources"
                />

                <!-- Description -->
                <x-forms.input 
                    label="Description"
                    name="description"
                    type="textarea"
                    :value="isset($department) ? $department->description : ''"
                    rows="4"
                    placeholder="Describe the department's role and responsibilities..."
                />

                <!-- Location -->
                <x-forms.input 
                    label="Location"
                    name="location"
                    :value="isset($department) ? $department->location : ''"
                    placeholder="e.g., Main Office - Floor 2"
                />

                <!-- Budget -->
                <div>
                    <label for="budget" class="block text-sm font-medium text-gray-700 mb-2">Annual Budget (PHP)</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="text-gray-500 sm:text-sm">₱</span>
                        </div>
                        <input type="number" 
                               name="budget" 
                               id="budget" 
                               value="{{ old('budget', isset($department) ? $department->budget : '') }}" 
                               min="0" 
                               step="0.01"
                               class="w-full pl-8 pr-3 py-2 border border-gray-300 rounded-lg bg-white text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors @error('budget') border-red-500 @enderror"
                               placeholder="0.00">
                    </div>
                    @error('budget')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-xs text-gray-500">Enter the annual budget for this department</p>
                </div>

                <!-- Submit Button -->
                <div class="flex justify-end space-x-3 pt-6 border-t border-gray-200">
                    <a href="{{ route('departments.index') }}" 
                       class="px-4 py-2 border border-gray-300 rounded-lg font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                        Cancel
                    </a>
                    <button type="submit" 
                            class="px-4 py-2 bg-blue-600 border border-transparent rounded-lg font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                        <i class="fas fa-save mr-2"></i>
                        {{ isset($department) ? 'Update Department' : 'Create Department' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

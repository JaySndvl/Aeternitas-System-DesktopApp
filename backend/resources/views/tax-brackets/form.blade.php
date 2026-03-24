@extends('layouts.dashboard-base', ['user' => $user, 'activeRoute' => 'tax-brackets.index'])

@section('title', isset($taxBracket) ? 'Edit Tax Bracket' : 'Create Tax Bracket')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <x-forms.page-header 
        :title="isset($taxBracket) ? 'Edit Tax Bracket' : 'Create Tax Bracket'"
        :subtitle="isset($taxBracket) ? 'Update tax bracket: ' . $taxBracket->name : 'Add a new tax bracket for payroll calculations'"
        :back-route="route('tax-brackets.index')"
        back-text="Back to Tax Brackets"
    />

    <!-- Form -->
    <div class="max-w-4xl">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <form method="POST" 
                  action="{{ isset($taxBracket) ? route('tax-brackets.update', $taxBracket) : route('tax-brackets.store') }}" 
                  class="p-6 space-y-6">
                @csrf
                @if(isset($taxBracket))
                    @method('PUT')
                @endif
                
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Basic Information -->
                    <div class="space-y-4">
                        <div class="bg-gray-50 rounded-lg p-4">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">
                                <i class="fas fa-info-circle mr-2 text-blue-600"></i>
                                Basic Information
                            </h3>
                            
                            <div class="space-y-4">
                                <x-forms.input 
                                    label="Bracket Name"
                                    name="name"
                                    :value="isset($taxBracket) ? $taxBracket->name : ''"
                                    required
                                    placeholder="e.g., 15% Bracket, Exempt Bracket"
                                />

                                <x-forms.input 
                                    label="Description"
                                    name="description"
                                    type="textarea"
                                    :value="isset($taxBracket) ? $taxBracket->description : ''"
                                    rows="3"
                                    placeholder="Optional description for this tax bracket"
                                />

                                <x-forms.input 
                                    label="Sort Order"
                                    name="sort_order"
                                    type="number"
                                    :value="isset($taxBracket) ? $taxBracket->sort_order : '1'"
                                    required
                                    min="1"
                                    placeholder="1"
                                />
                            </div>
                        </div>
                    </div>
                    
                    <!-- Tax Configuration -->
                    <div class="space-y-4">
                        <div class="bg-gray-50 rounded-lg p-4">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">
                                <i class="fas fa-calculator mr-2 text-green-600"></i>
                                Tax Configuration
                            </h3>
                            
                            <!-- Income Range -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                                <div>
                                    <x-forms.input 
                                        label="Minimum Income (₱)"
                                        name="min_income"
                                        type="number"
                                        :value="isset($taxBracket) ? $taxBracket->min_income : ''"
                                        required
                                        min="0"
                                        step="0.01"
                                        placeholder="0.00"
                                    />
                                </div>

                                <div>
                                    <label for="max_income" class="block text-sm font-medium text-gray-700 mb-2">Maximum Income (₱)</label>
                                    <input type="number" name="max_income" id="max_income" min="0" step="0.01"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-white text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('max_income') border-red-500 @enderror" 
                                           placeholder="Leave empty for no limit"
                                           value="{{ old('max_income', isset($taxBracket) ? $taxBracket->max_income : '') }}">
                                    @error('max_income')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                    <p class="text-xs text-gray-500 mt-1">Leave empty for highest bracket</p>
                                </div>
                            </div>

                            <div class="space-y-4">
                                <x-forms.input 
                                    label="Tax Rate (%)"
                                    name="tax_rate"
                                    type="number"
                                    :value="isset($taxBracket) ? $taxBracket->tax_rate : ''"
                                    required
                                    min="0"
                                    max="100"
                                    step="0.01"
                                    placeholder="15.00"
                                />

                                <x-forms.input 
                                    label="Base Tax Amount (₱)"
                                    name="base_tax"
                                    type="number"
                                    :value="isset($taxBracket) ? $taxBracket->base_tax : '0'"
                                    required
                                    min="0"
                                    step="0.01"
                                    placeholder="0.00"
                                />

                                <div>
                                    <label for="excess_over" class="block text-sm font-medium text-gray-700 mb-2">
                                        Excess Over Amount (₱) <span class="text-red-500">*</span>
                                    </label>
                                    <input type="number" name="excess_over" id="excess_over" required min="0" step="0.01"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-white text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('excess_over') border-red-500 @enderror" 
                                           placeholder="0.00"
                                           value="{{ old('excess_over', isset($taxBracket) ? $taxBracket->excess_over : '0') }}">
                                    @error('excess_over')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                    <p class="text-xs text-gray-500 mt-1">Amount to subtract from income before applying tax rate</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Status and Dates -->
                <div class="bg-gray-50 rounded-lg p-4">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">
                        <i class="fas fa-calendar mr-2 text-purple-600"></i>
                        Status and Effective Dates
                    </h3>
                    
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                        <!-- Active Status -->
                        <div>
                            <div class="flex items-center">
                                <input type="checkbox" name="is_active" id="is_active" value="1" 
                                       class="rounded border-gray-300 text-blue-600 focus:ring-blue-500" 
                                       {{ old('is_active', isset($taxBracket) ? $taxBracket->is_active : true) ? 'checked' : '' }}>
                                <label for="is_active" class="ml-2 text-sm font-medium text-gray-700">
                                    Active
                                </label>
                            </div>
                        </div>

                        <!-- Effective From -->
                        <div>
                            <label for="effective_from" class="block text-sm font-medium text-gray-700 mb-2">Effective From</label>
                            <input type="date" name="effective_from" id="effective_from"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-white text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('effective_from') border-red-500 @enderror" 
                                   value="{{ old('effective_from', isset($taxBracket) ? $taxBracket->effective_from : date('Y-m-d')) }}">
                            @error('effective_from')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Effective Until -->
                        <div>
                            <label for="effective_until" class="block text-sm font-medium text-gray-700 mb-2">Effective Until</label>
                            <input type="date" name="effective_until" id="effective_until"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-white text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('effective_until') border-red-500 @enderror" 
                                   value="{{ old('effective_until', isset($taxBracket) ? $taxBracket->effective_until : '') }}">
                            @error('effective_until')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="text-xs text-gray-500 mt-1">Leave empty for no expiration</p>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="flex justify-end space-x-3 pt-6 border-t border-gray-200">
                    <a href="{{ route('tax-brackets.index') }}" class="px-4 py-2 border border-gray-300 rounded-lg font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                        Cancel
                    </a>
                    <button type="submit" class="px-4 py-2 bg-blue-600 border border-transparent rounded-lg font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                        <i class="fas fa-save mr-2"></i>
                        {{ isset($taxBracket) ? 'Update Tax Bracket' : 'Create Tax Bracket' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-calculate excess_over based on min_income
    const minIncomeInput = document.getElementById('min_income');
    const excessOverInput = document.getElementById('excess_over');
    
    if (minIncomeInput && excessOverInput) {
        minIncomeInput.addEventListener('input', function() {
            if (excessOverInput.value === '' || excessOverInput.value === '0') {
                excessOverInput.value = this.value;
            }
        });
    }
});
</script>
@endsection

@extends('layouts.dashboard-base', ['user' => $user, 'activeRoute' => 'tax-brackets.index'])

@section('title', 'Tax Bracket Details')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">{{ $taxBracket->name }}</h1>
            <p class="mt-1 text-sm text-gray-500">Tax Bracket Details</p>
            @if($taxBracket->description)
                <p class="mt-1 text-sm text-gray-500">{{ $taxBracket->description }}</p>
            @endif
        </div>
        <div class="mt-4 sm:mt-0 flex space-x-3">
            <a href="{{ route('tax-brackets.edit', $taxBracket) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-lg font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                <i class="fas fa-edit mr-2"></i>
                Edit Bracket
            </a>
            <a href="{{ route('tax-brackets.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                <i class="fas fa-arrow-left mr-2"></i>
                Back to Tax Brackets
            </a>
        </div>
    </div>
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Tax Bracket Information -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">
                        <i class="fas fa-info-circle mr-2 text-blue-600"></i>
                        Bracket Information
                    </h3>
                </div>
                <div class="p-6 space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Name</p>
                            <p class="text-sm text-gray-900">{{ $taxBracket->name }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">Status</p>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $taxBracket->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $taxBracket->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </div>
                    </div>
                    
                    @if($taxBracket->description)
                    <div>
                        <p class="text-sm font-medium text-gray-500">Description</p>
                        <p class="text-sm text-gray-900">{{ $taxBracket->description }}</p>
                    </div>
                    @endif
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Sort Order</p>
                            <p class="text-sm text-gray-900">{{ $taxBracket->sort_order }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">Created</p>
                            <p class="text-sm text-gray-900">{{ $taxBracket->created_at->format('M j, Y g:i A') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tax Configuration -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">
                        <i class="fas fa-calculator mr-2 text-green-600"></i>
                        Tax Configuration
                    </h3>
                </div>
                <div class="p-6 space-y-4">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Income Range</p>
                        <p class="text-sm text-gray-900">
                            ₱{{ number_format($taxBracket->min_income, 2) }}
                            @if($taxBracket->max_income)
                                - ₱{{ number_format($taxBracket->max_income, 2) }}
                            @else
                                and above
                            @endif
                        </p>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Tax Rate</p>
                            <p class="text-lg font-semibold text-gray-900">{{ $taxBracket->tax_rate }}%</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">Base Tax</p>
                            <p class="text-lg font-semibold text-gray-900">₱{{ number_format($taxBracket->base_tax, 2) }}</p>
                        </div>
                    </div>
                    
                    <div>
                        <p class="text-sm font-medium text-gray-500">Excess Over</p>
                        <p class="text-sm text-gray-900">₱{{ number_format($taxBracket->excess_over, 2) }}</p>
                        <p class="text-xs text-gray-500 mt-1">Amount to subtract from income before applying tax rate</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Effective Dates -->
        <div class="mt-6 bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">
                    <i class="fas fa-calendar mr-2 text-purple-600"></i>
                    Effective Dates
                </h3>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Effective From</p>
                        <p class="text-sm text-gray-900">
                            {{ $taxBracket->effective_from ? $taxBracket->effective_from->format('M j, Y') : 'No start date' }}
                        </p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500">Effective Until</p>
                        <p class="text-sm text-gray-900">
                            {{ $taxBracket->effective_until ? $taxBracket->effective_until->format('M j, Y') : 'No expiration' }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tax Calculation Examples -->
        <div class="mt-6 bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">
                    <i class="fas fa-chart-line mr-2 text-orange-600"></i>
                    Tax Calculation Examples
                </h3>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    @php
                        $examples = [
                            $taxBracket->min_income,
                            $taxBracket->min_income + (($taxBracket->max_income ?? $taxBracket->min_income * 2) - $taxBracket->min_income) / 2,
                            $taxBracket->max_income ?? $taxBracket->min_income * 2
                        ];
                    @endphp
                    
                    @foreach($examples as $income)
                        @if($taxBracket->isIncomeInBracket($income))
                            <div class="bg-gray-50 rounded-lg p-4">
                                <div class="text-center">
                                    <p class="text-sm font-medium text-gray-500">Income</p>
                                    <p class="text-lg font-semibold text-gray-900">₱{{ number_format($income, 2) }}</p>
                                    <p class="text-sm text-gray-500 mt-2">Tax: ₱{{ number_format($taxBracket->calculateTax($income), 2) }}</p>
                                    <p class="text-xs text-gray-400">
                                        Rate: {{ $income > 0 ? number_format(($taxBracket->calculateTax($income) / $income) * 100, 2) : 0 }}%
                                    </p>
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="mt-6 flex justify-end space-x-3">
            <form method="POST" action="{{ route('tax-brackets.destroy', $taxBracket) }}" class="inline" 
                  onsubmit="return confirm('Are you sure you want to delete this tax bracket? This action cannot be undone.')">
                @csrf
                @method('DELETE')
                <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-lg font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors">
                    <i class="fas fa-trash mr-2"></i>
                    Delete Bracket
                </button>
            </form>
        </div>
    </div>
</div>
@endsection

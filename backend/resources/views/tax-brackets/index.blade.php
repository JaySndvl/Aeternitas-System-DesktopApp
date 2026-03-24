    @extends('layouts.dashboard-base', ['user' => $user, 'activeRoute' => 'tax-brackets.index'])

@section('title', 'Tax Brackets Management')

@section('content')
<x-page-header 
    title="Tax Brackets"
    description="Manage income tax brackets and rates for payroll calculations"
    :actions="[
        ['type' => 'link', 'label' => 'Add Tax Bracket', 'href' => route('tax-brackets.create'), 'icon' => 'plus', 'variant' => 'primary'],
        ['type' => 'button', 'label' => 'Load Philippine Tax Brackets', 'icon' => 'flag', 'variant' => 'secondary', 'onclick' => 'document.getElementById(\'loadPhilTaxForm\').submit()']
    ]"
>

    <form id="loadPhilTaxForm" method="POST" action="{{ route('tax-brackets.philippine') }}" class="hidden">
        @csrf
    </form>

        @if(session('success'))
            <div class="mb-6 bg-green-50 border border-green-200 rounded-lg p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-check-circle text-green-400"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                    </div>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 bg-red-50 border border-red-200 rounded-lg p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-exclamation-circle text-red-400"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
                    </div>
                </div>
            </div>
        @endif

        <!-- Tax Calculator -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">
                    <i class="fas fa-calculator mr-2 text-blue-600"></i>
                    Tax Calculator
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label for="income" class="block text-sm font-medium text-gray-700 mb-2">Monthly Income (₱)</label>
                        <input type="number" id="income" name="income" placeholder="Enter monthly income" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label for="tax_date" class="block text-sm font-medium text-gray-700 mb-2">Effective Date</label>
                        <input type="date" id="tax_date" name="tax_date" value="{{ date('Y-m-d') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div class="flex items-end">
                        <button type="button" id="calculateTax" 
                                class="w-full px-4 py-2 bg-blue-600 border border-transparent rounded-lg font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                            <i class="fas fa-calculator mr-2"></i>
                            Calculate Tax
                        </button>
                    </div>
                </div>
                
                <!-- Tax Result -->
                <div id="taxResult" class="hidden mt-4 p-4 bg-blue-50 rounded-lg border border-blue-200">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <p class="text-sm text-blue-600">Monthly Income</p>
                            <p class="text-lg font-semibold text-blue-900" id="resultIncome">₱0.00</p>
                        </div>
                        <div>
                            <p class="text-sm text-blue-600">Tax Amount</p>
                            <p class="text-lg font-semibold text-blue-900" id="resultTax">₱0.00</p>
                        </div>
                        <div>
                            <p class="text-sm text-blue-600">Effective Rate</p>
                            <p class="text-lg font-semibold text-blue-900" id="resultRate">0.00%</p>
                        </div>
                    </div>
                    <div class="mt-3">
                        <p class="text-sm text-blue-600">Tax Bracket Used</p>
                        <p class="font-medium text-blue-900" id="resultBracket">-</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tax Brackets Table -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Income Range</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tax Rate</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Base Tax</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Excess Over</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($taxBrackets as $bracket)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ $bracket->name }}</div>
                                    @if($bracket->description)
                                        <div class="text-xs text-gray-500 mt-1">{{ $bracket->description }}</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">
                                        ₱{{ number_format($bracket->min_income, 2) }}
                                        @if($bracket->max_income)
                                            - ₱{{ number_format($bracket->max_income, 2) }}
                                        @else
                                            and above
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $bracket->tax_rate == 0 ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800' }}">
                                        {{ $bracket->tax_rate }}%
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    ₱{{ number_format($bracket->base_tax, 2) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    ₱{{ number_format($bracket->excess_over, 2) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $bracket->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ $bracket->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex space-x-2">
                                        <a href="{{ route('tax-brackets.show', $bracket) }}" class="text-blue-600 hover:text-blue-900 transition-colors" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('tax-brackets.edit', $bracket) }}" class="text-indigo-600 hover:text-indigo-900 transition-colors" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button" onclick="openDeleteModal('{{ $bracket->id }}', '{{ $bracket->name }}')" class="text-red-600 hover:text-red-900 transition-colors" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center">
                                    <div class="text-gray-500">
                                        <i class="fas fa-percentage text-4xl mb-4"></i>
                                        <p class="text-lg font-medium">No tax brackets found</p>
                                        <p class="text-sm">Get started by creating your first tax bracket or loading the Philippine tax brackets.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const calculateBtn = document.getElementById('calculateTax');
    const incomeInput = document.getElementById('income');
    const dateInput = document.getElementById('tax_date');
    const resultDiv = document.getElementById('taxResult');
    
    calculateBtn.addEventListener('click', function() {
        const income = parseFloat(incomeInput.value);
        const date = dateInput.value;
        
        if (!income || income < 0) {
            alert('Please enter a valid income amount');
            return;
        }
        
        // Show loading state
        calculateBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Calculating...';
        calculateBtn.disabled = true;
        
        // Make AJAX request
        fetch('{{ route("tax-brackets.calculate") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                income: income,
                date: date
            })
        })
        .then(response => response.json())
        .then(data => {
            // Update result display
            document.getElementById('resultIncome').textContent = '₱' + income.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
            document.getElementById('resultTax').textContent = '₱' + data.tax_amount.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
            document.getElementById('resultRate').textContent = data.breakdown.effective_rate.toFixed(2) + '%';
            document.getElementById('resultBracket').textContent = data.breakdown.bracket_used ? data.breakdown.bracket_used.name : 'No applicable bracket';
            
            resultDiv.classList.remove('hidden');
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error calculating tax. Please try again.');
        })
        .finally(() => {
            // Reset button
            calculateBtn.innerHTML = '<i class="fas fa-calculator mr-2"></i>Calculate Tax';
            calculateBtn.disabled = false;
        });
    });
});

// Delete Modal Functions
function openDeleteModal(id, name) {
    document.getElementById('deleteModal').classList.remove('hidden');
    document.getElementById('deleteForm').action = `/tax-brackets/${id}`;
    document.getElementById('deleteItemName').textContent = name;
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.add('hidden');
}
</script>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3 text-center">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
                <i class="fas fa-exclamation-triangle text-red-600"></i>
            </div>
            <h3 class="text-lg font-medium text-gray-900 mt-4">Delete Tax Bracket</h3>
            <div class="mt-2 px-7 py-3">
                <p class="text-sm text-gray-500">
                    Are you sure you want to delete the tax bracket "<span id="deleteItemName" class="font-medium"></span>"? 
                    This action cannot be undone.
                </p>
            </div>
            <div class="items-center px-4 py-3">
                <form id="deleteForm" method="POST" class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-4 py-2 bg-red-600 text-white text-base font-medium rounded-md w-24 mr-2 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-300">
                        Delete
                    </button>
                </form>
                <button onclick="closeDeleteModal()" class="px-4 py-2 bg-gray-300 text-gray-800 text-base font-medium rounded-md w-24 hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-300">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</x-page-header>
@endsection

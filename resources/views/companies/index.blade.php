@extends('layouts.dashboard-base', ['user' => $user, 'activeRoute' => 'companies.index'])

@section('title', 'Companies')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Companies</h1>
            <p class="text-gray-600">Manage company information and settings</p>
        </div>
        <a href="{{ route('companies.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-lg font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
            <i class="fas fa-plus mr-2"></i>
            Add Company
        </a>
    </div>

    <!-- Companies List -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-medium text-gray-900">All Companies</h2>
        </div>
        
        @if($companies->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Company</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Code</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Location</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contact</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Switch Company</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($companies as $company)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                                            <i class="fas fa-building text-blue-600"></i>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">{{ $company->name }}</div>
                                        @if($company->description)
                                            <div class="text-sm text-gray-500">{{ Str::limit($company->description, 50) }}</div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                    {{ $company->code }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                @if($company->city && $company->country)
                                    {{ $company->city }}, {{ $company->country }}
                                @elseif($company->city)
                                    {{ $company->city }}
                                @elseif($company->country)
                                    {{ $company->country }}
                                @else
                                    -
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                @if($company->email)
                                    <div class="flex items-center">
                                        <i class="fas fa-envelope mr-2 text-gray-400"></i>
                                        {{ $company->email }}
                                    </div>
                                @endif
                                @if($company->phone)
                                    <div class="flex items-center mt-1">
                                        <i class="fas fa-phone mr-2 text-gray-400"></i>
                                        {{ $company->phone }}
                                    </div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($company->is_active)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        <i class="fas fa-check-circle mr-1"></i>
                                        Active
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        <i class="fas fa-times-circle mr-1"></i>
                                        Inactive
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <button onclick="openSwitchModal('{{ $company->id }}', '{{ $company->name }}', '{{ $company->code }}', '{{ $company->description }}', '{{ $company->city }}', '{{ $company->country }}', '{{ $company->email }}', '{{ $company->phone }}', '{{ $company->is_active ? 'Active' : 'Inactive' }}')" 
                                        class="inline-flex items-center px-3 py-2 bg-green-600 border border-transparent rounded-lg font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors text-sm">
                                    <i class="fas fa-exchange-alt mr-2"></i>
                                    Switch
                                </button>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex items-center space-x-2">
                                    <a href="{{ route('companies.show', $company) }}" class="text-blue-600 hover:text-blue-900" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('companies.edit', $company) }}" class="text-indigo-600 hover:text-indigo-900" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('companies.destroy', $company) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this company?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-900" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $companies->links() }}
            </div>
        @else
            <div class="text-center py-12">
                <i class="fas fa-building text-gray-400 text-4xl mb-4"></i>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No companies found</h3>
                <p class="text-gray-500 mb-6">Get started by creating your first company.</p>
                <a href="{{ route('companies.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-lg font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                    <i class="fas fa-plus mr-2"></i>
                    Add Company
                </a>
            </div>
        @endif
    </div>
</div>

<!-- Switch Company Modal -->
<div id="switchCompanyModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <!-- Modal Header -->
            <div class="flex items-center justify-between pb-4 border-b">
                <h3 class="text-lg font-medium text-gray-900">Switch Company</h3>
                <button onclick="closeSwitchModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <!-- Modal Body -->
            <div class="mt-4">
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 h-10 w-10">
                            <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                                <i class="fas fa-industry text-blue-600"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <h4 class="text-lg font-medium text-gray-900" id="modalCompanyName"></h4>
                            <p class="text-sm text-gray-500" id="modalCompanyCode"></p>
                        </div>
                    </div>
                </div>
                
                <div class="space-y-3">
                    <div>
                        <label class="text-sm font-medium text-gray-700">Description</label>
                        <p class="text-sm text-gray-900" id="modalCompanyDescription">-</p>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm font-medium text-gray-700">Location</label>
                            <p class="text-sm text-gray-900" id="modalCompanyLocation">-</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-700">Status</label>
                            <p class="text-sm text-gray-900" id="modalCompanyStatus">-</p>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm font-medium text-gray-700">Email</label>
                            <p class="text-sm text-gray-900" id="modalCompanyEmail">-</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-700">Phone</label>
                            <p class="text-sm text-gray-900" id="modalCompanyPhone">-</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Modal Footer -->
            <div class="flex justify-end space-x-3 mt-6 pt-4 border-t">
                <button onclick="closeSwitchModal()" 
                        class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition-colors">
                    Cancel
                </button>
                <button onclick="confirmSwitch()" 
                        class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                    <i class="fas fa-exchange-alt mr-2"></i>
                    Switch to this Company
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let currentCompanyId = null;
let currentCompanyName = null;

function openSwitchModal(id, name, code, description, city, country, email, phone, status) {
    currentCompanyId = id;
    currentCompanyName = name;
    
    // Update modal content
    document.getElementById('modalCompanyName').textContent = name;
    document.getElementById('modalCompanyCode').textContent = code;
    document.getElementById('modalCompanyDescription').textContent = description || '-';
    
    const location = [city, country].filter(Boolean).join(', ') || '-';
    document.getElementById('modalCompanyLocation').textContent = location;
    
    document.getElementById('modalCompanyStatus').innerHTML = status === 'Active' 
        ? '<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800"><i class="fas fa-check-circle mr-1"></i>Active</span>'
        : '<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800"><i class="fas fa-times-circle mr-1"></i>Inactive</span>';
    
    document.getElementById('modalCompanyEmail').textContent = email || '-';
    document.getElementById('modalCompanyPhone').textContent = phone || '-';
    
    // Show modal
    document.getElementById('switchCompanyModal').classList.remove('hidden');
}

function closeSwitchModal() {
    document.getElementById('switchCompanyModal').classList.add('hidden');
    currentCompanyId = null;
    currentCompanyName = null;
}

function confirmSwitch() {
    if (currentCompanyId && currentCompanyName) {
        // For now, we'll just show a success message
        // In the future, this would make an API call to switch the user's company context
        alert(`Switched to ${currentCompanyName}! (This is a placeholder - company switching functionality will be implemented in the future)`);
        closeSwitchModal();
        
        // Future implementation would include:
        // 1. Making an API call to switch company context
        // 2. Updating the header to show the new company name
        // 3. Redirecting to the dashboard or refreshing the page
        // fetch('/api/switch-company', {
        //     method: 'POST',
        //     headers: {
        //         'Content-Type': 'application/json',
        //         'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        //     },
        //     body: JSON.stringify({ company_id: currentCompanyId })
        // }).then(response => response.json())
        // .then(data => {
        //     if (data.success) {
        //         location.reload();
        //     }
        // });
    }
}

// Close modal when clicking outside
document.getElementById('switchCompanyModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeSwitchModal();
    }
});
</script>
@endsection

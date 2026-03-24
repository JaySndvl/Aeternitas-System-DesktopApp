@extends('layouts.dashboard-base', ['user' => $user, 'activeRoute' => 'companies.index'])

@section('title', 'Companies')

@section('content')
<x-page-header 
    title="Companies"
    description="Manage company information and settings"
    :actions="[
        ['type' => 'link', 'label' => 'Add Company', 'href' => route('companies.create'), 'icon' => 'plus', 'variant' => 'primary']
    ]"
>
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
                                            <div class="text-sm text-gray-500">{{ \Illuminate\Support\Str::limit($company->description, 50) }}</div>
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
                                @php
                                    $currentCompany = \App\Helpers\CompanyHelper::getCurrentCompany();
                                    $isCurrentCompany = $currentCompany && $company->id === $currentCompany->id;
                                @endphp
                                
                                @if($isCurrentCompany)
                                    <span class="inline-flex items-center px-3 py-2 bg-blue-600 text-white rounded-lg font-medium text-sm">
                                        <i class="fas fa-check-circle mr-2"></i>
                                        Current
                                    </span>
                                @else
                                    <form method="POST" action="{{ route('companies.switch') }}" class="inline" 
                                          onsubmit="handleCompanySwitch(event, '{{ $company->name }}')">
                                        @csrf
                                        <input type="hidden" name="company_id" value="{{ $company->id }}">
                                        <button type="submit" 
                                                class="inline-flex items-center px-3 py-2 bg-green-600 border border-transparent rounded-lg font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors text-sm">
                                            <i class="fas fa-exchange-alt mr-2"></i>
                                            Switch
                                        </button>
                                    </form>
                                @endif
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
</x-page-header>

<script>
// Company switch handler
window.handleCompanySwitch = function(event, companyName) {
    // Show loading state
    const submitButton = event.target.querySelector('button[type="submit"]');
    if (submitButton) {
        submitButton.disabled = true;
        submitButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Switching...';
    }
    
    // The form will submit normally and page will reload
    return true;
};
</script>
@endsection
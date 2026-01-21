@extends('layouts.dashboard-base', ['user' => $user, 'activeRoute' => 'documents.index'])

@section('title', 'Employee Documents')

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white shadow-sm border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="py-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Employee Documents</h1>
                        <p class="mt-1 text-sm text-gray-600">View and manage employee documents and information</p>
                    </div>
                    <div class="flex space-x-3">
                        <button id="exportBtn" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-lg font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors">
                            <i class="fas fa-file-export mr-2"></i>
                            Export Documents
                        </button>
                        <a href="{{ route('dashboard') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                            <i class="fas fa-arrow-left mr-2"></i>
                            Back to Dashboard
                        </a>
                    </div>
                </div>

                <!-- Company Selector (for admin/hr only) -->
                @if(isset($companies) && count($companies) > 1 && ($user->role === 'admin' || $user->role === 'hr'))
                <div class="mt-4">
                    <form action="{{ route('documents.switch-company') }}" method="POST" class="flex items-center space-x-3">
                        @csrf
                        <span class="text-sm font-medium text-gray-700">Company:</span>
                        <select name="company_id" onchange="this.form.submit()" 
                                class="px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                            @foreach($companies as $company)
                                <option value="{{ $company->id }}" {{ $currentCompanyId == $company->id ? 'selected' : '' }}>
                                    {{ $company->name }}
                                </option>
                            @endforeach
                        </select>
                        <span class="text-sm text-gray-500">
                            <i class="fas fa-building mr-1"></i>
                            Viewing employees for selected company
                        </span>
                    </form>
                </div>
                @elseif(isset($currentCompany) && $currentCompany)
                <div class="mt-4">
                    <div class="inline-flex items-center px-3 py-2 bg-blue-50 text-blue-700 rounded-lg">
                        <i class="fas fa-building mr-2"></i>
                        <span class="font-medium">{{ $currentCompany->name }}</span>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <!-- Filters -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Filter Employees</h3>
            </div>
            <div class="p-6">
                <form id="filterForm" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label for="department" class="block text-sm font-medium text-gray-700 mb-2">Department</label>
                        <select id="department" name="department_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">All Departments</option>
                            @foreach($departments as $department)
                                <option value="{{ $department->id }}" {{ request('department_id') == $department->id ? 'selected' : '' }}>
                                    {{ $department->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Employment Status</label>
                        <select id="status" name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">All Status</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                            <option value="on-leave" {{ request('status') == 'on-leave' ? 'selected' : '' }}>On Leave</option>
                        </select>
                    </div>
                    
                    <div>
                        <label for="search" class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                        <div class="flex">
                            <input type="text" id="search" name="search" value="{{ request('search') }}" 
                                   class="flex-1 px-3 py-2 border border-gray-300 rounded-l-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="Search by name or ID">
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-r-lg font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Employees List -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                <h3 class="text-lg font-medium text-gray-900">Employees</h3>
                <span class="text-sm text-gray-600">{{ $employees->total() }} employee(s) found</span>
            </div>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Employee</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Department</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Position</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Documents</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($employees as $employee)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                                            <span class="text-sm font-medium text-blue-600">
                                                {{ substr($employee->first_name, 0, 1) }}{{ substr($employee->last_name, 0, 1) }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">{{ $employee->full_name }}</div>
                                        <div class="text-sm text-gray-500">{{ $employee->employee_id }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $employee->department->name ?? 'N/A' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $employee->position }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                    @if($employee->status == 'active') bg-green-100 text-green-800
                                    @elseif($employee->status == 'on-leave') bg-yellow-100 text-yellow-800
                                    @else bg-red-100 text-red-800 @endif">
                                    {{ ucfirst($employee->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-800">
                                    <i class="fas fa-file mr-1"></i>
                                    {{ $employee->documents_count ?? 0 }} documents
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <button onclick="viewEmployee({{ $employee->id }})" 
                                        class="text-blue-600 hover:text-blue-900 mr-4">
                                    <i class="fas fa-eye mr-1"></i> View
                                </button>
                                <a href="{{ route('employees.documents', $employee->id) }}" 
                                   class="text-green-600 hover:text-green-900">
                                    <i class="fas fa-folder mr-1"></i> Documents
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center">
                                <div class="text-gray-500">
                                    <i class="fas fa-users text-4xl mb-3"></i>
                                    <p class="text-lg">No employees found</p>
                                    <p class="text-sm mt-1">Try adjusting your filters</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            @if($employees->hasPages())
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $employees->withQueryString()->links() }}
            </div>
            @endif
        </div>
    </div>
</div>

<!-- View Employee Modal -->
<div id="employeeModal" class="hidden fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full max-h-[90vh] overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
            <h3 class="text-lg font-medium text-gray-900">Employee Information</h3>
            <button onclick="closeModal()" class="text-gray-400 hover:text-gray-500">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        
        <div class="p-6 overflow-y-auto max-h-[calc(90vh-120px)]">
            <div id="employeeDetails">
                <!-- Employee details will be loaded here -->
                <div class="text-center py-8">
                    <i class="fas fa-spinner fa-spin text-3xl text-blue-600"></i>
                    <p class="mt-2 text-gray-600">Loading employee details...</p>
                </div>
            </div>
        </div>
        
        <div class="px-6 py-4 border-t border-gray-200 bg-gray-50 flex justify-between space-x-3">
            <div class="flex space-x-2">
                <button onclick="closeModal()" class="px-4 py-2 border border-gray-300 rounded-lg font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                    Close
                </button>
                <button id="exportEmployeeBtn" onclick="exportEmployeeDetails(currentEmployeeId)" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-lg font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors">
                    <i class="fas fa-file-export mr-2"></i>
                    Export Profile
                </button>
            </div>
            <a id="viewDocumentsBtn" href="#" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-lg font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                <i class="fas fa-folder mr-2"></i>
                View Documents
            </a>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    let currentEmployeeId = null;
    
    // Auto-submit form when department or status changes
    document.getElementById('department').addEventListener('change', function() {
        document.getElementById('filterForm').submit();
    });
    
    document.getElementById('status').addEventListener('change', function() {
        document.getElementById('filterForm').submit();
    });
    
    // Export button functionality with options
    document.getElementById('exportBtn').addEventListener('click', function() {
        showExportOptions();
    });
    
    function showExportOptions() {
        const departmentId = document.getElementById('department').value;
        const status = document.getElementById('status').value;
        const search = document.getElementById('search').value;
        
        // Create modal for export options
        const exportModal = document.createElement('div');
        exportModal.className = 'fixed inset-0 bg-gray-600 bg-opacity-75 flex items-center justify-center z-50';
        exportModal.innerHTML = `
            <div class="bg-white rounded-lg shadow-xl max-w-md w-full p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Export Options</h3>
                    <button onclick="this.closest('.fixed').remove()" class="text-gray-400 hover:text-gray-500">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Export Format</label>
                        <div class="space-y-2">
                            <label class="flex items-center">
                                <input type="radio" name="exportFormat" value="csv" checked class="mr-2">
                                <span>CSV (Excel compatible)</span>
                            </label>
                            <label class="flex items-center">
                                <input type="radio" name="exportFormat" value="pdf" class="mr-2">
                                <span>PDF (Printable report)</span>
                            </label>
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Export Scope</label>
                        <div class="space-y-2">
                            <label class="flex items-center">
                                <input type="radio" name="exportScope" value="filtered" checked class="mr-2">
                                <span>Current filtered results (${document.querySelectorAll('tbody tr').length} employees)</span>
                            </label>
                            <label class="flex items-center">
                                <input type="radio" name="exportScope" value="all" class="mr-2">
                                <span>All employees</span>
                            </label>
                        </div>
                    </div>
                </div>
                
                <div class="mt-6 flex justify-end space-x-3">
                    <button onclick="this.closest('.fixed').remove()" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                        Cancel
                    </button>
                    <button onclick="performExport()" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        <i class="fas fa-download mr-2"></i>
                        Export
                    </button>
                </div>
            </div>
        `;
        
        document.body.appendChild(exportModal);
        
        // Store parameters for export
        exportModal.exportParams = { departmentId, status, search };
        
        window.performExport = function() {
            const format = document.querySelector('input[name="exportFormat"]:checked').value;
            const scope = document.querySelector('input[name="exportScope"]:checked').value;
            const params = exportModal.exportParams;
            
            let url = '{{ route("documents.export") }}?type=' + format;
            
            // Only include filters if exporting filtered results
            if (scope === 'filtered') {
                if (params.departmentId) url += '&department_id=' + encodeURIComponent(params.departmentId);
                if (params.status) url += '&status=' + encodeURIComponent(params.status);
                if (params.search) url += '&search=' + encodeURIComponent(params.search);
            }
            
            window.location.href = url;
            exportModal.remove();
        };
    }
    
    // View employee in modal
    function viewEmployee(employeeId) {
        currentEmployeeId = employeeId;
        
        // Show modal
        const modal = document.getElementById('employeeModal');
        modal.classList.remove('hidden');
        
        // Load employee details
        fetch(`/employees/${employeeId}/details`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    document.getElementById('employeeDetails').innerHTML = data.html;
                    
                    // Update the View Documents button
                    const viewDocsBtn = document.getElementById('viewDocumentsBtn');
                    viewDocsBtn.href = `/employees/${employeeId}/documents`;
                    
                    // Add export button to modal
                    const exportBtn = document.getElementById('exportEmployeeBtn');
                    if (exportBtn) {
                        exportBtn.onclick = function() {
                            exportEmployeeDetails(employeeId);
                        };
                    }
                } else {
                    showError('Failed to load employee details');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showError('Error loading employee details: ' + error.message);
            });
    }
    
    // Export single employee details
    function exportEmployeeDetails(employeeId) {
        window.location.href = `/documents/employee/${employeeId}/export`;
    }
    
    // Close modal
    function closeModal() {
        document.getElementById('employeeModal').classList.add('hidden');
    }
    
    // Show error message
    function showError(message) {
        document.getElementById('employeeDetails').innerHTML = `
            <div class="text-center py-8">
                <i class="fas fa-exclamation-triangle text-3xl text-red-600"></i>
                <p class="mt-2 text-gray-600">${message}</p>
            </div>
        `;
    }
    
    // Close modal with ESC key
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closeModal();
        }
    });
    
    // Close modal when clicking outside
    document.getElementById('employeeModal').addEventListener('click', function(event) {
        if (event.target === this) {
            closeModal();
        }
    });
</script>
@endpush
@extends('layouts.dashboard-base', ['user' => $user, 'activeRoute' => 'employees.index'])

@section('title', 'Employee Details')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">{{ $employee->full_name }}</h1>
                <p class="mt-1 text-sm text-gray-500">Employee Details</p>
            </div>
            <div class="flex space-x-3">
                @if(in_array($user->role, ['admin', 'hr', 'manager']))
                    <a href="{{ route('employees.edit', $employee) }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                        <i class="fas fa-edit mr-2"></i>
                        Edit Employee
                    </a>
                @endif
                <a href="{{ route('employees.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Back to Employees
                </a>
            </div>
        </div>

        <!-- Employee Information -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Info -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Personal Information -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Personal Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-500">First Name</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $employee->first_name }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Last Name</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $employee->last_name }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Email Address</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $employee->account?->email ?? 'No email' }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Phone Number</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $employee->phone }}</p>
                        </div>
                    </div>
                </div>

                <!-- Work Information -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Work Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Position</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $employee->position }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Department</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $employee->department->name }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Monthly Salary</label>
                            <p class="mt-1 text-sm text-gray-900">₱{{ number_format($employee->salary, 2) }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Hire Date</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $employee->hire_date->format('M d, Y') }}</p>
                        </div>
                    </div>
                </div>

                <!-- Account Information -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Account Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Role</label>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                {{ $employee->account?->role === 'admin' ? 'bg-red-100 text-red-800' : 
                                   ($employee->account?->role === 'hr' ? 'bg-purple-100 text-purple-800' : 
                                   ($employee->account?->role === 'manager' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800')) }}">
                                {{ ucfirst($employee->account?->role ?? 'No role') }}
                            </span>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Status</label>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                {{ $employee->account?->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                <div class="w-1.5 h-1.5 rounded-full mr-1.5 {{ $employee->account?->is_active ? 'bg-green-400' : 'bg-red-400' }}"></div>
                                {{ $employee->account?->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Last Login</label>
                            <p class="mt-1 text-sm text-gray-900">
                                {{ $employee->account?->last_login_at ? $employee->account->last_login_at->format('M d, Y g:i A') : 'Never' }}
                            </p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Account Created</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $employee->account?->created_at?->format('M d, Y') ?? 'N/A' }}</p>
                        </div>
                    </div>
                </div>

                <!-- Attendance Records -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-medium text-gray-900">Attendance Records</h3>
                        <a href="{{ route('attendance.timekeeping', ['employee_id' => $employee->id]) }}" class="text-sm text-blue-600 hover:text-blue-500">
                            View All →
                        </a>
                    </div>
                    @if($attendanceRecords->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Time In</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Time Out</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Hours</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($attendanceRecords as $record)
                                        <tr class="hover:bg-gray-50 transition-colors">
                                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                                                {{ \Carbon\Carbon::parse($record->date)->format('M d, Y') }}
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                                                @if($record->time_in)
                                                    {{ \Carbon\Carbon::parse($record->time_in)->format('g:i A') }}
                                                @else
                                                    <span class="text-gray-400">-</span>
                                                @endif
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                                                @if($record->time_out)
                                                    {{ \Carbon\Carbon::parse($record->time_out)->format('g:i A') }}
                                                @elseif($record->time_in)
                                                    @php
                                                        $recordDate = \Carbon\Carbon::parse($record->date);
                                                        $isToday = $recordDate->isToday();
                                                    @endphp
                                                    @if($isToday)
                                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                            <div class="w-1.5 h-1.5 rounded-full mr-1.5 bg-blue-400 animate-pulse"></div>
                                                            Working
                                                        </span>
                                                    @else
                                                        <span class="text-gray-400">Not Clocked Out</span>
                                                    @endif
                                                @else
                                                    <span class="text-gray-400">-</span>
                                                @endif
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                                                @if($record->time_out && $record->total_hours)
                                                    {{ \App\Helpers\TimezoneHelper::formatHours($record->total_hours) }}
                                                @else
                                                    <span class="text-gray-400">-</span>
                                                @endif
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap">
                                                @php
                                                    $statusColors = [
                                                        'present' => 'bg-green-100 text-green-800',
                                                        'absent' => 'bg-red-100 text-red-800',
                                                        'absent_excused' => 'bg-yellow-100 text-yellow-800',
                                                        'absent_unexcused' => 'bg-red-100 text-red-800',
                                                        'absent_sick' => 'bg-orange-100 text-orange-800',
                                                        'absent_personal' => 'bg-purple-100 text-purple-800',
                                                        'late' => 'bg-yellow-100 text-yellow-800',
                                                        'half_day' => 'bg-blue-100 text-blue-800',
                                                        'on_leave' => 'bg-indigo-100 text-indigo-800',
                                                    ];
                                                    $statusColor = $statusColors[$record->status] ?? 'bg-gray-100 text-gray-800';
                                                @endphp
                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $statusColor }}">
                                                    {{ ucfirst(str_replace('_', ' ', $record->status)) }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-4">
                            {{ $attendanceRecords->links() }}
                        </div>
                    @else
                        <div class="text-center py-8">
                            <i class="fas fa-clock text-gray-400 text-4xl mb-3"></i>
                            <p class="text-sm text-gray-500">No attendance records found.</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Employee Avatar -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 text-center">
                    <div class="mx-auto h-24 w-24 rounded-full bg-gradient-to-r from-blue-500 to-blue-600 flex items-center justify-center mb-4">
                        <span class="text-2xl font-bold text-white">
                            {{ strtoupper(substr($employee->first_name, 0, 1) . substr($employee->last_name, 0, 1)) }}
                        </span>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900">{{ $employee->full_name }}</h3>
                    <p class="text-sm text-gray-500">{{ $employee->position }}</p>
                    <p class="text-sm text-gray-500">{{ $employee->department->name }}</p>
                </div>

                <!-- Quick Actions -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Quick Actions</h3>
                    <div class="space-y-3">
                        @if(in_array($user->role, ['admin', 'hr', 'manager']))
                            <a href="{{ route('employees.edit', $employee) }}" class="w-full flex items-center justify-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition-colors">
                                <i class="fas fa-edit mr-2"></i>
                                Edit Employee
                            </a>
                        @endif
                        <a href="{{ route('employees.payroll', $employee) }}" class="w-full flex items-center justify-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition-colors">
                            <i class="fas fa-money-bill-wave mr-2"></i>
                            View Payroll
                        </a>
                        <a href="{{ route('attendance.timekeeping', ['employee_id' => $employee->id]) }}" class="w-full flex items-center justify-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition-colors">
                            <i class="fas fa-clock mr-2"></i>
                            View Attendance
                        </a>
                        @if(in_array($user->role, ['admin', 'hr', 'manager']))
                            <button type="button" onclick="openDeleteModal('{{ $employee->id }}', '{{ $employee->full_name }}')" class="w-full flex items-center justify-center px-4 py-2 border border-red-300 rounded-lg text-sm font-medium text-red-700 bg-white hover:bg-red-50 transition-colors">
                                <i class="fas fa-trash mr-2"></i>
                                Delete Employee
                            </button>
                        @endif
                    </div>
                </div>

                <!-- Recent Payrolls -->
                @if($employee->payrolls->count() > 0)
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Recent Payrolls</h3>
                    <div class="space-y-3">
                        @foreach($employee->payrolls->take(3) as $payroll)
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ $payroll->pay_period_start->format('M d') }} - {{ $payroll->pay_period_end->format('M d, Y') }}</p>
                                <p class="text-xs text-gray-500">{{ $payroll->created_at->format('M d, Y') }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-medium text-gray-900">₱{{ number_format($payroll->gross_pay, 2) }}</p>
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                                    {{ $payroll->status === 'paid' ? 'bg-green-100 text-green-800' : 
                                       ($payroll->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                    {{ ucfirst($payroll->status) }}
                                </span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    <div class="mt-4">
                        <a href="{{ route('employees.payroll', $employee) }}" class="text-sm text-blue-600 hover:text-blue-500">View all payrolls →</a>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
// Delete Modal Functions
function openDeleteModal(employeeId, employeeName) {
    document.getElementById('deleteEmployeeId').value = employeeId;
    document.getElementById('deleteEmployeeName').textContent = employeeName;
    document.getElementById('deleteForm').action = `/employees/${employeeId}`;
    document.getElementById('deleteModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden'; // Prevent background scrolling
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.add('hidden');
    document.body.style.overflow = 'auto'; // Restore scrolling
}

// Close modal when clicking outside
document.getElementById('deleteModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeDeleteModal();
    }
});

// Close modal with Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeDeleteModal();
    }
});
</script>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <!-- Background overlay -->
        <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" onclick="closeDeleteModal()"></div>

        <!-- Modal panel -->
        <div class="inline-block w-full max-w-md p-6 my-8 overflow-hidden text-left align-middle transition-all transform bg-white shadow-xl rounded-2xl">
            <!-- Modal header -->
            <div class="flex items-center justify-center w-12 h-12 mx-auto mb-4 bg-red-100 rounded-full">
                <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
            </div>

            <!-- Modal content -->
            <div class="text-center">
                <h3 class="text-lg font-medium text-gray-900 mb-2">Delete Employee</h3>
                <p class="text-sm text-gray-500 mb-6">
                    Are you sure you want to delete <span id="deleteEmployeeName" class="font-semibold text-gray-900"></span>? 
                    This action cannot be undone and will permanently remove all employee data including payroll records.
                </p>
            </div>

            <!-- Modal actions -->
            <div class="flex flex-col sm:flex-row gap-3 sm:gap-3">
                <button type="button" onclick="closeDeleteModal()" 
                    class="flex-1 px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-colors">
                    Cancel
                </button>
                <form id="deleteForm" method="POST" class="flex-1">
                    @csrf
                    @method('DELETE')
                    <input type="hidden" id="deleteEmployeeId" name="employee_id" value="">
                    <button type="submit" 
                        class="w-full px-4 py-2 text-sm font-medium text-white bg-red-600 border border-transparent rounded-lg hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors">
                        Delete Employee
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

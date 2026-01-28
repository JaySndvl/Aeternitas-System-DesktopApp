@extends('layouts.app')

@section('content')
<div class="p-4 sm:p-6 lg:p-8">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Login Logs</h1>
        <p class="text-gray-600 mt-1">Track employee login activities</p>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between">
                <div>
                    <h3 class="text-lg font-medium text-gray-900">Recent Login Activity</h3>
                    <p class="text-sm text-gray-500 mt-1">Showing all login records</p>
                </div>
            </div>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Employee
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            IP Address
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Browser/Device
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Login Time
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($logs as $log)
                        @php
                            $employeeName = $log->account && $log->account->employee 
                                ? $log->account->employee->first_name . ' ' . $log->account->employee->last_name
                                : 'Unknown Employee';
                            $userAgent = $log->user_agent;
                            $browser = '';
                            if (strpos($userAgent, 'Chrome') !== false) $browser = 'Chrome';
                            elseif (strpos($userAgent, 'Firefox') !== false) $browser = 'Firefox';
                            elseif (strpos($userAgent, 'Safari') !== false) $browser = 'Safari';
                            elseif (strpos($userAgent, 'Edge') !== false) $browser = 'Edge';
                            elseif (strpos($userAgent, 'Opera') !== false) $browser = 'Opera';
                            else $browser = 'Unknown Browser';
                        @endphp
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                                            <i class="fas fa-user text-blue-600"></i>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">{{ $employeeName }}</div>
                                        <div class="text-sm text-gray-500">{{ $log->account->email ?? 'N/A' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $log->ip_address }}</div>
                                <div class="text-xs text-gray-500">Location: N/A</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $browser }}</div>
                                <div class="text-xs text-gray-500">Device: Unknown</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $log->created_at->format('M d, Y g:i A') }}</div>
                                <div class="text-xs text-gray-500">{{ $log->created_at->diffForHumans() }}</div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-8 text-center">
                                <i class="fas fa-bell-slash text-gray-300 text-3xl mb-2"></i>
                                <p class="text-sm text-gray-500">No login logs found</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="px-4 py-4 sm:px-6 border-t border-gray-200">
            {{ $logs->links() }}
        </div>
    </div>
</div>
@endsection
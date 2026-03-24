@extends('layouts.dashboard-base', ['user' => $user, 'activeRoute' => 'hr.messages.index'])

@section('title', 'Messages from Employees')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Page Header -->
        <x-page-header 
            title="Messages from Employees" 
            :breadcrumbs="[
                ['name' => 'Dashboard', 'route' => route('dashboard')],
                ['name' => 'HR', 'route' => '#'],
                ['name' => 'Messages', 'current' => true]
            ]"
        />

        <!-- Tabs/Action Buttons -->
        <div class="flex gap-4 mb-8">
            <a href="{{ route('hr.contacts.admin') }}" class="flex items-center gap-2 px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                <i class="fas fa-inbox"></i>
                <span>All Contacts</span>
            </a>
            <a href="{{ route('hr.messages.index') }}" class="flex items-center gap-2 px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
                <i class="fas fa-envelope"></i>
                <span>Messages</span>
            </a>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-blue-100">
                        <i class="fas fa-envelope text-blue-600"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-gray-500 text-sm">Total Messages</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $totalMessages }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-yellow-100">
                        <i class="fas fa-exclamation-circle text-yellow-600"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-gray-500 text-sm">Awaiting Response</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $unreadCount }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-green-100">
                        <i class="fas fa-reply text-green-600"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-gray-500 text-sm">Responded</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $respondedCount }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Messages List -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            @if ($messages->count())
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">From</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subject</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach ($messages as $message)
                                <tr class="hover:bg-gray-50 transition {{ $message->status === 'pending' ? 'bg-blue-50' : '' }}">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div>
                                            <p class="text-sm font-medium text-gray-900">
                                                @if($message->employee)
                                                    {{ $message->employee->first_name }} {{ $message->employee->last_name }}
                                                @else
                                                    {{ $message->user->name ?? 'Unknown' }}
                                                @endif
                                            </p>
                                            <p class="text-xs text-gray-500">{{ $message->user->email ?? '-' }}</p>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <p class="text-sm font-medium text-gray-900">{{ $message->subject }}</p>
                                        <p class="text-xs text-gray-600 mt-1">{{ Str::limit($message->message, 50) }}</p>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-3 py-1 text-xs font-medium rounded-full 
                                            @switch($message->category)
                                                @case('leave') bg-purple-100 text-purple-800 @break
                                                @case('payroll') bg-green-100 text-green-800 @break
                                                @case('benefits') bg-blue-100 text-blue-800 @break
                                                @case('schedule') bg-orange-100 text-orange-800 @break
                                                @case('complaint') bg-red-100 text-red-800 @break
                                                @case('request') bg-yellow-100 text-yellow-800 @break
                                                @default bg-gray-100 text-gray-800
                                            @endswitch
                                        ">
                                            {{ ucfirst($message->category) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-3 py-1 text-xs font-medium rounded-full 
                                            @switch($message->status)
                                                @case('pending') bg-yellow-100 text-yellow-800 @break
                                                @case('in_progress') bg-blue-100 text-blue-800 @break
                                                @case('resolved') bg-green-100 text-green-800 @break
                                                @case('closed') bg-gray-100 text-gray-800 @break
                                            @endswitch
                                        ">
                                            {{ ucfirst(str_replace('_', ' ', $message->status)) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <div>
                                            <p>{{ $message->created_at->format('M d, Y') }}</p>
                                            <p class="text-xs">{{ $message->created_at->format('h:i A') }}</p>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <a href="{{ route('hr.contact.show', $message->id) }}" class="text-blue-600 hover:text-blue-900 font-medium inline-flex items-center gap-1">
                                            <i class="fas fa-envelope-open"></i> Open
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="bg-white px-6 py-4 border-t border-gray-200">
                    {{ $messages->links() }}
                </div>
            @else
                <div class="text-center py-12">
                    <i class="fas fa-inbox text-4xl text-gray-300 mb-4"></i>
                    <p class="text-gray-500 text-lg">No messages yet</p>
                    <p class="text-gray-400 text-sm mt-2">All employee messages will appear here</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

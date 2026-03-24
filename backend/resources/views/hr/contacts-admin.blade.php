@extends('layouts.dashboard-base', ['user' => $user, 'activeRoute' => 'hr.contacts.admin'])

@section('title', 'HR Contact Management')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Page Header -->
        <x-page-header 
            title="Contact Management" 
            :breadcrumbs="[
                ['name' => 'Dashboard', 'route' => route('dashboard')],
                ['name' => 'HR', 'route' => '#'],
                ['name' => 'Contacts', 'current' => true]
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
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-blue-100">
                        <i class="fas fa-inbox text-blue-600"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-gray-500 text-sm">Total Contacts</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $totalContacts }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-yellow-100">
                        <i class="fas fa-clock text-yellow-600"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-gray-500 text-sm">Pending</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $pendingCount }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-blue-100">
                        <i class="fas fa-spinner text-blue-600"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-gray-500 text-sm">In Progress</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $inProgressCount }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-green-100">
                        <i class="fas fa-check-circle text-green-600"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-gray-500 text-sm">Resolved</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $resolvedCount }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-lg shadow mb-8 p-6">
            <form action="{{ route('hr.contacts.admin') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label for="status_filter" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <select id="status_filter" name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">All Statuses</option>
                        <option value="pending" @selected(request('status') === 'pending')>Pending</option>
                        <option value="in_progress" @selected(request('status') === 'in_progress')>In Progress</option>
                        <option value="resolved" @selected(request('status') === 'resolved')>Resolved</option>
                        <option value="closed" @selected(request('status') === 'closed')>Closed</option>
                    </select>
                </div>

                <div>
                    <label for="category_filter" class="block text-sm font-medium text-gray-700 mb-2">Category</label>
                    <select id="category_filter" name="category" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">All Categories</option>
                        <option value="attendance" @selected(request('category') === 'attendance')>Attendance</option>
                        <option value="leave" @selected(request('category') === 'leave')>Leave</option>
                        <option value="payroll" @selected(request('category') === 'payroll')>Payroll</option>
                        <option value="benefits" @selected(request('category') === 'benefits')>Benefits</option>
                        <option value="schedule" @selected(request('category') === 'schedule')>Schedule</option>
                        <option value="general" @selected(request('category') === 'general')>General</option>
                        <option value="complaint" @selected(request('category') === 'complaint')>Complaint</option>
                        <option value="request" @selected(request('category') === 'request')>Request</option>
                    </select>
                </div>

                <div>
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                    <input type="text" id="search" name="search" placeholder="Search by subject..." value="{{ request('search') }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>

                <div class="flex items-end gap-2">
                    <button type="submit" class="flex-1 bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition flex items-center justify-center gap-2">
                        <i class="fas fa-search"></i> Filter
                    </button>
                    <a href="{{ route('hr.contacts.admin') }}" class="flex-1 bg-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-400 transition text-center">
                        Clear
                    </a>
                </div>
            </form>
        </div>

        <!-- Contacts Table -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            @if ($contacts->count())
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Employee</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subject</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach ($contacts as $contact)
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div>
                                            <p class="text-sm font-medium text-gray-900">
                                                @if($contact->employee)
                                                    {{ $contact->employee->first_name }} {{ $contact->employee->last_name }}
                                                @else
                                                    {{ $contact->user->name ?? 'Unknown' }}
                                                @endif
                                            </p>
                                            <p class="text-xs text-gray-500">{{ $contact->user->email ?? '-' }}</p>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <p class="text-sm text-gray-900">{{ \Illuminate\Support\Str::limit($contact->subject, 30) }}</p>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-3 py-1 text-xs font-medium rounded-full 
                                            @switch($contact->category)
                                                @case('attendance') bg-indigo-100 text-indigo-800 @break
                                                @case('leave') bg-purple-100 text-purple-800 @break
                                                @case('payroll') bg-green-100 text-green-800 @break
                                                @case('benefits') bg-blue-100 text-blue-800 @break
                                                @case('schedule') bg-orange-100 text-orange-800 @break
                                                @case('complaint') bg-red-100 text-red-800 @break
                                                @case('request') bg-yellow-100 text-yellow-800 @break
                                                @default bg-gray-100 text-gray-800
                                            @endswitch
                                        ">
                                            {{ ucfirst($contact->category) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-3 py-1 text-xs font-medium rounded-full 
                                            @switch($contact->status)
                                                @case('pending') bg-yellow-100 text-yellow-800 @break
                                                @case('in_progress') bg-blue-100 text-blue-800 @break
                                                @case('resolved') bg-green-100 text-green-800 @break
                                                @case('closed') bg-gray-100 text-gray-800 @break
                                            @endswitch
                                        ">
                                            {{ ucfirst(str_replace('_', ' ', $contact->status)) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <div>
                                            {{ $contact->created_at->format('M d, Y') }}
                                            <p class="text-xs">{{ $contact->created_at->format('h:i A') }}</p>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <a href="{{ route('hr.contact.show', $contact->id) }}" class="text-blue-600 hover:text-blue-900 font-medium">
                                            <i class="fas fa-eye mr-1"></i> View
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="bg-white px-6 py-4 border-t border-gray-200">
                    {{ $contacts->links() }}
                </div>
            @else
                <div class="text-center py-12">
                    <i class="fas fa-inbox text-4xl text-gray-300 mb-4"></i>
                    <p class="text-gray-500 text-lg">No contacts found</p>
                    <p class="text-gray-400 text-sm mt-2">Try adjusting your filters</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

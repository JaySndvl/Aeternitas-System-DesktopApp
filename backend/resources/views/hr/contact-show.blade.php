@extends('layouts.dashboard-base', ['user' => $user, 'activeRoute' => 'hr.contact.show'])

@section('title', 'Contact Details')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div class="flex items-center space-x-4">
            <a href="{{ route('hr.contact.index') }}" class="inline-flex items-center px-4 py-2 text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">
                <i class="fas fa-arrow-left mr-2"></i>
                Back
            </a>
            <div>
                <h1 class="text-2xl font-bold text-gray-900">{{ $hrContact->subject }}</h1>
                <p class="mt-1 text-sm text-gray-500">{{ $hrContact->created_at->format('M d, Y \a\t H:i') }}</p>
            </div>
        </div>
        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold
            @if($hrContact->status === 'pending') bg-yellow-100 text-yellow-800
            @elseif($hrContact->status === 'in_progress') bg-blue-100 text-blue-800
            @elseif($hrContact->status === 'resolved') bg-green-100 text-green-800
            @else bg-gray-100 text-gray-800
            @endif">
            {{ ucfirst(str_replace('_', ' ', $hrContact->status)) }}
        </span>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Original Message -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <h2 class="text-lg font-semibold text-gray-900">Your Message</h2>
                </div>
                
                <div class="p-6">
                    <p class="text-gray-700 whitespace-pre-wrap">{{ $hrContact->message }}</p>
                </div>
            </div>

            <!-- HR Response -->
            @if($hrContact->response)
                <div class="bg-white rounded-lg shadow-sm border border-green-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-green-200 bg-green-50">
                        <div class="flex items-center justify-between">
                            <h2 class="text-lg font-semibold text-green-900">
                                <i class="fas fa-check-circle mr-2"></i>HR Response
                            </h2>
                            @if($hrContact->responder)
                                <span class="text-sm text-green-700">From: {{ $hrContact->responder->email }}</span>
                            @endif
                        </div>
                    </div>
                    
                    <div class="p-6">
                        <p class="text-gray-700 whitespace-pre-wrap">{{ $hrContact->response }}</p>
                        @if($hrContact->responded_at)
                            <p class="mt-4 text-sm text-gray-500">
                                <i class="fas fa-clock mr-1"></i>
                                Responded on {{ $hrContact->responded_at->format('M d, Y \a\t H:i') }}
                            </p>
                        @endif
                    </div>
                </div>
            @elseif(in_array(strtolower($user->role), ['hr', 'admin', 'administrator']))
                <!-- HR Response Form -->
                <form method="POST" action="{{ route('hr.contact.respond', $hrContact) }}" class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                    @csrf
                    
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                        <h2 class="text-lg font-semibold text-gray-900">Send Response</h2>
                    </div>
                    
                    <div class="p-6 space-y-6">
                        <!-- Status -->
                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Update Status</label>
                            <select id="status" name="status" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option value="pending" @selected($hrContact->status === 'pending')>Pending</option>
                                <option value="in_progress" @selected($hrContact->status === 'in_progress')>In Progress</option>
                                <option value="resolved" @selected($hrContact->status === 'resolved')>Resolved</option>
                                <option value="closed" @selected($hrContact->status === 'closed')>Closed</option>
                            </select>
                        </div>

                        <!-- Response -->
                        <div>
                            <label for="response" class="block text-sm font-medium text-gray-700 mb-2">Your Response</label>
                            <textarea id="response" name="response" rows="8" required placeholder="Type your response here..."
                                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">{{ old('response') }}</textarea>
                            @error('response')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Submit Button -->
                        <div class="flex justify-end space-x-3">
                            <a href="{{ route('hr.contact.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg font-medium text-gray-700 hover:bg-gray-50 transition-colors">
                                Cancel
                            </a>
                            <button type="submit" class="inline-flex items-center px-6 py-2 bg-green-600 border border-transparent rounded-lg font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors">
                                <i class="fas fa-check mr-2"></i>
                                Send Response
                            </button>
                        </div>
                    </div>
                </form>
            @else
                <div class="text-center py-12 bg-white rounded-lg shadow-sm border border-gray-200">
                    <i class="fas fa-hourglass text-4xl text-gray-400 mb-4"></i>
                    <p class="text-gray-600">HR will review your message and respond soon.</p>
                </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="lg:col-span-1 space-y-6">
            <!-- Details Card -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <h3 class="text-lg font-semibold text-gray-900">Details</h3>
                </div>
                
                <div class="p-6 space-y-4">
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Category</p>
                        <p class="mt-1 text-sm text-gray-900">{{ ucfirst($hrContact->category) }}</p>
                    </div>

                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Status</p>
                        <p class="mt-1 text-sm text-gray-900">{{ ucfirst(str_replace('_', ' ', $hrContact->status)) }}</p>
                    </div>

                    @if($hrContact->employee)
                        <div>
                            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Employee</p>
                            <p class="mt-1 text-sm text-gray-900">{{ $hrContact->employee->full_name }}</p>
                        </div>
                    @endif

                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Submitted</p>
                        <p class="mt-1 text-sm text-gray-900">{{ $hrContact->created_at->format('M d, Y') }}</p>
                    </div>

                    @if($hrContact->responded_at)
                        <div>
                            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Responded</p>
                            <p class="mt-1 text-sm text-gray-900">{{ $hrContact->responded_at->format('M d, Y') }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Actions Card (HR only) -->
            @if(in_array(strtolower($user->role), ['hr', 'admin', 'administrator']))
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                        <h3 class="text-lg font-semibold text-gray-900">Quick Actions</h3>
                    </div>
                    
                    <div class="p-6 space-y-2">
                        <a href="{{ route('hr.contacts.admin') }}" class="block w-full text-center px-4 py-2 text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">
                            <i class="fas fa-list mr-2"></i>
                            View All Contacts
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

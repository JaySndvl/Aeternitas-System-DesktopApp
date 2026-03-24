@extends('layouts.dashboard-base', ['user' => $user, 'activeRoute' => 'hr.contact.index'])

@section('title', 'Contact HR')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Contact HR</h1>
            <p class="mt-1 text-sm text-gray-500">Send a message to our HR team</p>
        </div>
    </div>

    @if (session('success'))
        <div class="rounded-lg bg-green-50 border border-green-200 p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-check-circle text-green-600"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                </div>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Contact Form -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <h2 class="text-lg font-semibold text-gray-900">Send Message to HR</h2>
                </div>
                
                <div class="p-6">
                    <form method="POST" action="{{ route('hr.contact.store') }}" class="space-y-6">
                        @csrf

                        <!-- Category -->
                        <div>
                            <label for="category" class="block text-sm font-medium text-gray-700 mb-2">Category</label>
                            <select id="category" name="category" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('category') border-red-500 @enderror">
                                <option value="">Select a category...</option>
                                <option value="leave" @selected(old('category') === 'leave')>Leave Request</option>
                                <option value="payroll" @selected(old('category') === 'payroll')>Payroll Issue</option>
                                <option value="benefits" @selected(old('category') === 'benefits')>Benefits</option>
                                <option value="schedule" @selected(old('category') === 'schedule')>Schedule</option>
                                <option value="complaint" @selected(old('category') === 'complaint')>Complaint</option>
                                <option value="request" @selected(old('category') === 'request')>Request</option>
                                <option value="general" @selected(old('category') === 'general')>General Inquiry</option>
                            </select>
                            @error('category')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Subject -->
                        <div>
                            <label for="subject" class="block text-sm font-medium text-gray-700 mb-2">Subject</label>
                            <input type="text" id="subject" name="subject" required placeholder="What is this about?" 
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('subject') border-red-500 @enderror"
                                   value="{{ old('subject') }}">
                            @error('subject')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Message -->
                        <div>
                            <label for="message" class="block text-sm font-medium text-gray-700 mb-2">Message</label>
                            <textarea id="message" name="message" rows="6" required placeholder="Please provide details about your inquiry..."
                                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('message') border-red-500 @enderror">{{ old('message') }}</textarea>
                            @error('message')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-xs text-gray-500">Maximum 5000 characters</p>
                        </div>

                        <!-- Submit Button -->
                        <div class="flex justify-end">
                            <button type="submit" class="inline-flex items-center px-6 py-2 bg-blue-600 border border-transparent rounded-lg font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                                <i class="fas fa-paper-plane mr-2"></i>
                                Send Message
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Quick Info & Guidelines -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <h3 class="text-lg font-semibold text-gray-900">Guidelines</h3>
                </div>
                
                <div class="p-6 space-y-4">
                    <div>
                        <h4 class="text-sm font-semibold text-gray-900 mb-2">
                            <i class="fas fa-clock text-blue-600 mr-2"></i>Response Time
                        </h4>
                        <p class="text-sm text-gray-600">We aim to respond within 2 business days.</p>
                    </div>

                    <div>
                        <h4 class="text-sm font-semibold text-gray-900 mb-2">
                            <i class="fas fa-info-circle text-blue-600 mr-2"></i>What to Include
                        </h4>
                        <p class="text-sm text-gray-600">Provide clear details and any relevant dates or reference numbers.</p>
                    </div>

                    <div>
                        <h4 class="text-sm font-semibold text-gray-900 mb-2">
                            <i class="fas fa-shield-alt text-blue-600 mr-2"></i>Confidentiality
                        </h4>
                        <p class="text-sm text-gray-600">All communications are confidential and handled appropriately.</p>
                    </div>

                    <div>
                        <h4 class="text-sm font-semibold text-gray-900 mb-2">
                            <i class="fas fa-lightbulb text-blue-600 mr-2"></i>Urgent Issues
                        </h4>
                        <p class="text-sm text-gray-600">For urgent matters, please contact HR directly by phone or in person.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Previous Messages -->
    @if($contacts->count() > 0)
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <h2 class="text-lg font-semibold text-gray-900">Your Messages</h2>
            </div>

            <div class="divide-y divide-gray-200">
                @foreach($contacts as $contact)
                    <div class="p-6 hover:bg-gray-50 transition-colors">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <div class="flex items-center space-x-3">
                                    <h3 class="text-base font-semibold text-gray-900">{{ $contact->subject }}</h3>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        @if($contact->status === 'pending') bg-yellow-100 text-yellow-800
                                        @elseif($contact->status === 'in_progress') bg-blue-100 text-blue-800
                                        @elseif($contact->status === 'resolved') bg-green-100 text-green-800
                                        @else bg-gray-100 text-gray-800
                                        @endif">
                                        {{ ucfirst(str_replace('_', ' ', $contact->status)) }}
                                    </span>
                                </div>
                                <p class="mt-2 text-sm text-gray-600">{{ Str::limit($contact->message, 100) }}</p>
                                <div class="mt-2 flex items-center space-x-4 text-xs text-gray-500">
                                    <span><i class="fas fa-tag mr-1"></i>{{ ucfirst($contact->category) }}</span>
                                    <span><i class="fas fa-calendar mr-1"></i>{{ $contact->created_at->format('M d, Y') }}</span>
                                </div>
                            </div>
                            <a href="{{ route('hr.contact.show', $contact) }}" class="ml-4 inline-flex items-center px-3 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors">
                                <i class="fas fa-eye mr-1"></i>
                                View
                            </a>
                        </div>

                        @if($contact->response)
                            <div class="mt-4 p-4 bg-green-50 border border-green-200 rounded-lg">
                                <p class="text-sm font-semibold text-green-900 mb-2">
                                    <i class="fas fa-check-circle mr-1"></i>HR Response
                                </p>
                                <p class="text-sm text-green-800">{{ Str::limit($contact->response, 150) }}</p>
                                @if($contact->responded_at)
                                    <p class="mt-2 text-xs text-green-600">{{ $contact->responded_at->format('M d, Y \a\t H:i') }}</p>
                                @endif
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            @if($contacts->hasPages())
                <div class="px-6 py-4 border-t border-gray-200 flex justify-center">
                    {{ $contacts->links() }}
                </div>
            @endif
        </div>
    @else
        <div class="text-center py-12 bg-white rounded-lg shadow-sm border border-gray-200">
            <i class="fas fa-inbox text-4xl text-gray-400 mb-4"></i>
            <p class="text-gray-600">No messages yet. Send your first message to HR using the form above.</p>
        </div>
    @endif
</div>
@endsection

@extends('layouts.dashboard-base', ['user' => $user, 'activeRoute' => 'hr.help-support'])

@section('title', 'Help & Support')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Page Header -->
        <x-page-header 
            title="Help & Support Center" 
            :breadcrumbs="[
                ['name' => 'Dashboard', 'route' => route('dashboard')],
                ['name' => 'Help & Support', 'current' => true]
            ]"
        />

        <!-- Success Message -->
        @if(session('success'))
            <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg flex items-start gap-3">
                <i class="fas fa-check-circle text-green-600 mt-1"></i>
                <div>
                    <p class="text-green-800 font-medium">{{ session('success') }}</p>
                </div>
            </div>
        @endif

        <!-- Quick Links -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12">
            <div class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition">
                <div class="flex items-center gap-4 mb-4">
                    <div class="p-3 rounded-full bg-blue-100">
                        <i class="fas fa-book text-blue-600 text-xl"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900">FAQ</h3>
                </div>
                <p class="text-gray-600 text-sm">Browse frequently asked questions organized by topic</p>
                <a href="#faq" class="mt-4 inline-block text-blue-600 hover:text-blue-700 font-medium">
                    View FAQ <i class="fas fa-arrow-right ml-1"></i>
                </a>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition">
                <div class="flex items-center gap-4 mb-4">
                    <div class="p-3 rounded-full bg-purple-100">
                        <i class="fas fa-tools text-purple-600 text-xl"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900">Troubleshooting</h3>
                </div>
                <p class="text-gray-600 text-sm">Find solutions to common issues and problems</p>
                <a href="#troubleshooting" class="mt-4 inline-block text-blue-600 hover:text-blue-700 font-medium">
                    View Tips <i class="fas fa-arrow-right ml-1"></i>
                </a>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition">
                <div class="flex items-center gap-4 mb-4">
                    <div class="p-3 rounded-full bg-green-100">
                        <i class="fas fa-envelope text-green-600 text-xl"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900">Support Ticket</h3>
                </div>
                <p class="text-gray-600 text-sm">Create a support ticket for HR assistance</p>
                <a href="#support-form" class="mt-4 inline-block text-blue-600 hover:text-blue-700 font-medium">
                    Create Ticket <i class="fas fa-arrow-right ml-1"></i>
                </a>
            </div>
        </div>

        <!-- FAQ Section -->
        <div id="faq" class="mb-12">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">Frequently Asked Questions</h2>
            
            @foreach($faqs as $faqGroup)
                <div class="mb-8">
                    <h3 class="text-xl font-semibold text-gray-800 mb-4 pb-2 border-b-2 border-blue-500">
                        <i class="fas fa-folder-open text-blue-600 mr-2"></i>{{ $faqGroup['category'] }}
                    </h3>
                    
                    <div class="space-y-4">
                        @foreach($faqGroup['items'] as $faq)
                            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition">
                                <button class="faq-toggle w-full px-6 py-4 flex items-center justify-between hover:bg-gray-50 transition" onclick="toggleFaq(this)">
                                    <span class="text-left font-medium text-gray-900">{{ $faq['question'] }}</span>
                                    <i class="fas fa-chevron-down text-gray-400"></i>
                                </button>
                                <div class="faq-answer hidden px-6 pb-4 pt-0 text-gray-600 bg-gray-50">
                                    {{ $faq['answer'] }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Troubleshooting Section -->
        <div id="troubleshooting" class="mb-12">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">Troubleshooting Guide</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @foreach($troubleshooting as $issue)
                    <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-purple-500">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">
                            <i class="fas fa-exclamation-triangle text-purple-600 mr-2"></i>{{ $issue['title'] }}
                        </h3>
                        
                        <ol class="space-y-2">
                            @foreach($issue['steps'] as $step)
                                <li class="flex gap-3 text-gray-700">
                                    <span class="flex-shrink-0 w-6 h-6 rounded-full bg-purple-100 text-purple-600 flex items-center justify-center text-xs font-semibold">
                                        {{ $loop->iteration }}
                                    </span>
                                    <span>{{ $step }}</span>
                                </li>
                            @endforeach
                        </ol>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Support Ticket Form -->
        <div id="support-form" class="bg-white rounded-lg shadow-md p-8 border-t-4 border-green-500">
            <h2 class="text-2xl font-bold text-gray-900 mb-2">Submit a Support Ticket</h2>
            <p class="text-gray-600 mb-6">Can't find the answer? Create a support ticket and our HR team will help you.</p>
            
            <form method="POST" action="{{ route('hr.help-support-ticket-store') }}" class="space-y-6">
                @csrf

                <!-- Subject -->
                <div>
                    <label for="subject" class="block text-sm font-medium text-gray-700 mb-2">Subject</label>
                    <input 
                        type="text" 
                        id="subject" 
                        name="subject" 
                        placeholder="Brief description of your issue"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                        required
                    >
                    @error('subject')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Category -->
                <div>
                    <label for="category" class="block text-sm font-medium text-gray-700 mb-2">Category</label>
                    <select 
                        id="category" 
                        name="category"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                        required
                    >
                        <option value="">Select a category</option>
                        <option value="attendance">Attendance & Time Tracking</option>
                        <option value="leave">Leave & Time Off</option>
                        <option value="payroll">Payroll & Compensation</option>
                        <option value="schedule">Schedules & Overtime</option>
                        <option value="account">Account & Profile</option>
                        <option value="technical">Technical Issues</option>
                        <option value="other">Other</option>
                    </select>
                    @error('category')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Message -->
                <div>
                    <label for="message" class="block text-sm font-medium text-gray-700 mb-2">Message</label>
                    <textarea 
                        id="message" 
                        name="message"
                        rows="6"
                        placeholder="Provide detailed information about your issue..."
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent resize-vertical"
                        required
                    ></textarea>
                    <p class="mt-1 text-xs text-gray-500">Maximum 5000 characters</p>
                    @error('message')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Submit Button -->
                <div class="flex gap-4">
                    <button 
                        type="submit"
                        class="flex-1 bg-green-600 text-white px-6 py-3 rounded-lg hover:bg-green-700 transition font-medium flex items-center justify-center gap-2"
                    >
                        <i class="fas fa-paper-plane"></i> Submit Ticket
                    </button>
                    <a href="{{ route('hr.help-support') }}" class="flex-1 bg-gray-300 text-gray-700 px-6 py-3 rounded-lg hover:bg-gray-400 transition font-medium text-center">
                        Clear
                    </a>
                </div>
            </form>
        </div>

        <!-- Additional Help -->
        <div class="mt-12 bg-blue-50 border border-blue-200 rounded-lg p-8">
            <div class="flex gap-4">
                <div class="flex-shrink-0">
                    <i class="fas fa-lightbulb text-blue-600 text-2xl mt-1"></i>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-blue-900 mb-2">Need Immediate Help?</h3>
                    <p class="text-blue-800 mb-4">
                        For urgent issues, you can also use the <strong>Contact HR</strong> feature in the sidebar to send a direct message to the HR team. 
                        They typically respond within business hours.
                    </p>
                    <a href="{{ route('hr.contact.index') }}" class="inline-flex items-center gap-2 text-blue-600 hover:text-blue-700 font-medium">
                        <i class="fas fa-arrow-right"></i> Contact HR
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function toggleFaq(button) {
    const answer = button.nextElementSibling;
    const icon = button.querySelector('i');
    
    answer.classList.toggle('hidden');
    icon.classList.toggle('rotate-180');
}
</script>

<style>
.rotate-180 {
    transform: rotate(180deg);
}
</style>
@endsection

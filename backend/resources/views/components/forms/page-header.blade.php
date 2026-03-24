@props(['title', 'subtitle' => '', 'backRoute' => null, 'backText' => 'Back'])

<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between space-y-4 sm:space-y-0">
    <div>
        <h1 class="text-xl sm:text-2xl font-bold text-gray-900">{{ $title }}</h1>
        @if($subtitle)
            <p class="mt-1 text-sm text-gray-500">{{ $subtitle }}</p>
        @endif
    </div>
    @if($backRoute)
        <a href="{{ $backRoute }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
            <i class="fas fa-arrow-left mr-2"></i>
            <span class="hidden sm:inline">{{ $backText }}</span>
            <span class="sm:hidden">Back</span>
        </a>
    @endif
</div>

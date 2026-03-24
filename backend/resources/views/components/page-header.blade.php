@props([
    'title' => '',
    'description' => '',
    'actions' => [], // Array of action buttons
])

<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
        <div>
            @if($title)
                <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">{{ $title }}</h1>
            @endif
            @if($description)
                <p class="mt-1 text-sm text-gray-600">{{ $description }}</p>
            @endif
        </div>
        
        @if(count($actions) > 0)
            <div class="mt-4 sm:mt-0 flex gap-3">
                @foreach($actions as $action)
                    @if($action['type'] === 'link')
                        <a href="{{ $action['href'] }}" @class([
                            'inline-flex items-center px-4 py-2 rounded-lg font-medium transition-colors',
                            'bg-blue-600 text-white border border-transparent hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500' => ($action['variant'] ?? 'primary') === 'primary',
                            'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500' => ($action['variant'] ?? 'primary') === 'secondary',
                            'bg-red-600 text-white border border-transparent hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500' => ($action['variant'] ?? 'primary') === 'danger',
                        ])>
                            @if($action['icon'] ?? false)
                                <i class="fas fa-{{ $action['icon'] }} mr-2"></i>
                            @endif
                            {{ $action['label'] }}
                        </a>
                    @elseif($action['type'] === 'button')
                        <button @if($action['onclick'] ?? false) onclick="{{ $action['onclick'] }}" @endif @class([
                            'inline-flex items-center px-4 py-2 rounded-lg font-medium transition-colors',
                            'bg-blue-600 text-white border border-transparent hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500' => ($action['variant'] ?? 'primary') === 'primary',
                            'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500' => ($action['variant'] ?? 'primary') === 'secondary',
                            'bg-red-600 text-white border border-transparent hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500' => ($action['variant'] ?? 'primary') === 'danger',
                        ])>
                            @if($action['icon'] ?? false)
                                <i class="fas fa-{{ $action['icon'] }} mr-2"></i>
                            @endif
                            {{ $action['label'] }}
                        </button>
                    @endif
                @endforeach
            </div>
        @endif
    </div>

    {{ $slot }}
</div>

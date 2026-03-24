@props(['title', 'value', 'icon', 'color' => 'blue', 'trend' => null])

@php
$colorClasses = [
    'blue' => 'bg-blue-100 text-blue-600',
    'green' => 'bg-green-100 text-green-600',
    'yellow' => 'bg-yellow-100 text-yellow-600',
    'purple' => 'bg-purple-100 text-purple-600',
    'red' => 'bg-red-100 text-red-600',
    'indigo' => 'bg-indigo-100 text-indigo-600',
];
@endphp

<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 sm:p-6 hover:shadow-md transition-shadow">
    <div class="flex items-center">
        <div class="flex-shrink-0">
            <div class="w-10 h-10 sm:w-12 sm:h-12 {{ $colorClasses[$color] }} rounded-lg flex items-center justify-center">
                <i class="{{ $icon }} text-lg sm:text-xl"></i>
            </div>
        </div>
        <div class="ml-3 sm:ml-4 min-w-0 flex-1">
            <p class="text-xs sm:text-sm font-medium text-gray-500 truncate">{{ $title }}</p>
            <p class="text-xl sm:text-2xl font-bold text-gray-900 truncate">{{ $value }}</p>
            @if($trend)
            <p class="text-xs {{ $trend['positive'] ? 'text-green-600' : 'text-red-600' }}">
                <i class="fas fa-arrow-{{ $trend['positive'] ? 'up' : 'down' }} mr-1"></i>
                {{ $trend['value'] }}
            </p>
            @endif
        </div>
    </div>
</div>

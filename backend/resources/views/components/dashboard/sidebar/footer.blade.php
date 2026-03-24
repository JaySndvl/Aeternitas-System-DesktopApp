@props(['user'])

<div class="w-full p-4 border-t border-gray-200 bg-gray-50 flex-shrink-0">
    <div class="flex items-center">
        <div class="flex-shrink-0">
            <div class="w-10 h-10 bg-gradient-to-r from-blue-500 to-blue-600 rounded-xl flex items-center justify-center shadow-lg">
                <i class="fas fa-user text-white text-sm"></i>
            </div>
        </div>
        <div class="ml-3 flex-1">
            <p class="text-sm font-semibold text-gray-900">{{ $user->full_name }}</p>
            <p class="text-xs text-gray-500">{{ ucfirst($user->role) }} • {{ $user->employee->department->name ?? 'System' }}</p>
        </div>
        <div class="flex items-center space-x-2">
            <!-- Status indicator -->
            <div class="w-2 h-2 bg-green-400 rounded-full"></div>
            <span class="text-xs text-gray-500">Online</span>
        </div>
    </div>
    
    <!-- Quick stats for user -->
    <div class="mt-3 grid grid-cols-2 gap-2">
        <div class="bg-white rounded-lg p-2 text-center">
            <p class="text-xs text-gray-500">Last Login</p>
            <p class="text-xs font-medium text-gray-900">{{ $user->last_login_at ? $user->last_login_at->format('M d') : 'Never' }}</p>
        </div>
        <div class="bg-white rounded-lg p-2 text-center">
            <p class="text-xs text-gray-500">Status</p>
            <p class="text-xs font-medium text-green-600">Active</p>
        </div>
    </div>
</div>

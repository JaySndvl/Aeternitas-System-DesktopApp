@props(['title', 'user'])

<header class="bg-gradient-to-r from-white/95 to-blue-50/95 backdrop-blur-sm shadow-sm border-b border-gray-200 sticky top-0 z-40">
    <div class="flex items-center justify-between h-16 sm:h-20 px-3 sm:px-4 lg:px-8">
        <div class="flex items-center flex-1 min-w-0">
            <button class="lg:hidden text-gray-500 hover:text-gray-700 p-2 rounded-lg hover:bg-gray-100 transition-colors flex-shrink-0" onclick="toggleSidebar()">
                <i class="fas fa-bars text-lg sm:text-xl"></i>
            </button>
            <div class="ml-2 sm:ml-4 lg:ml-0 min-w-0 flex-1">
                <h1 class="text-lg sm:text-xl lg:text-2xl font-bold text-gray-900 truncate">{{ $title }}</h1>
                <div class="flex flex-col sm:flex-row sm:items-center space-y-1 sm:space-y-0 sm:space-x-4">
                    <p class="text-xs sm:text-sm text-gray-500 truncate">Welcome back, {{ $user->full_name }}</p>
                    <div class="flex items-center text-xs text-gray-400">
                        <i class="fas fa-clock mr-1"></i>
                        <span class="hidden sm:inline">{{ \App\Helpers\TimezoneHelper::now()->format('M d, Y g:i A') }}</span>
                        <span class="sm:hidden">{{ \App\Helpers\TimezoneHelper::now()->format('g:i A') }}</span>
                        <span class="ml-1 text-blue-600">PHT</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="flex items-center space-x-1 sm:space-x-2 lg:space-x-3 flex-shrink-0">
            <!-- Company Name Display - Hidden on mobile, shown on tablet+ -->
            <div class="hidden md:block">
                <div class="flex items-center px-4 py-2 bg-blue-50 border border-blue-200 rounded-lg">
                    <i class="fas fa-industry text-blue-600 mr-2"></i>
                    <span class="text-sm font-medium text-blue-800">Eternal Bright Sanctuary Inc.</span>
                </div>
            </div>
            
            <!-- Notifications -->
            <button class="relative p-1.5 sm:p-2 text-gray-500 hover:text-gray-700 rounded-lg hover:bg-gray-100 transition-colors">
                <i class="fas fa-bell text-lg sm:text-xl"></i>
                <span class="absolute -top-0.5 -right-0.5 sm:-top-1 sm:-right-1 block h-4 w-4 sm:h-5 sm:w-5 rounded-full bg-red-500 text-white text-xs flex items-center justify-center">3</span>
            </button>
            
            <!-- Messages - Hidden on small mobile -->
            <button class="relative p-1.5 sm:p-2 text-gray-500 hover:text-gray-700 rounded-lg hover:bg-gray-100 transition-colors hidden sm:block">
                <i class="fas fa-envelope text-lg sm:text-xl"></i>
                <span class="absolute -top-0.5 -right-0.5 sm:-top-1 sm:-right-1 block h-4 w-4 sm:h-5 sm:w-5 rounded-full bg-blue-500 text-white text-xs flex items-center justify-center">2</span>
            </button>
            
            <!-- User Menu -->
            <div class="relative">
                <button class="flex items-center space-x-1 sm:space-x-2 p-1.5 sm:p-2 rounded-lg hover:bg-gray-100 transition-colors">
                    <div class="w-6 h-6 sm:w-8 sm:h-8 bg-gradient-to-r from-blue-500 to-blue-600 rounded-full flex items-center justify-center">
                        <i class="fas fa-user text-white text-xs sm:text-sm"></i>
                    </div>
                    <span class="hidden lg:block text-sm font-medium text-gray-700">{{ $user->full_name }}</span>
                    <i class="fas fa-chevron-down text-gray-400 text-xs hidden sm:block"></i>
                </button>
            </div>
            
            <!-- Logout -->
            <form method="POST" action="{{ route('logout') }}" class="inline">
                @csrf
                <button type="submit" class="flex items-center px-2 sm:px-3 py-1.5 sm:py-2 text-sm font-medium text-gray-700 hover:bg-red-50 hover:text-red-600 rounded-lg transition-colors">
                    <i class="fas fa-sign-out-alt text-sm sm:text-base"></i>
                    <span class="hidden lg:block ml-2">Logout</span>
                </button>
            </form>
        </div>
    </div>
</header>

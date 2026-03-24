@props(['user', 'activeRoute' => 'dashboard'])

<div class="fixed inset-y-0 left-0 z-50 w-72 bg-white shadow-2xl transform -translate-x-full transition-all duration-300 ease-in-out lg:translate-x-0 border-r border-gray-100 flex flex-col" id="sidebar">
    <!-- Sidebar Header -->
    <x-dashboard.sidebar.header :user="$user" />

    <!-- Navigation -->
    <div class="flex-1 overflow-y-auto scrollbar-thin scrollbar-thumb-gray-300 scrollbar-track-gray-100 hover:scrollbar-thumb-gray-400 scroll-smooth min-h-0">
        <!-- Navigation content -->
        <div class="pt-2 pb-8">
            <x-dashboard.sidebar.navigation :user="$user" :activeRoute="$activeRoute" />
        </div>
    </div>

    <!-- Sidebar Footer -->
    <x-dashboard.sidebar.footer :user="$user" />
</div>
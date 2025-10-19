@props(['user'])

<div class="flex items-center h-20 px-6 bg-gradient-to-r from-slate-700 to-slate-800 shadow-sm border-b border-slate-600/30">
    <div class="flex items-center flex-1 min-w-0">
        <div class="w-12 h-12 bg-slate-600/50 rounded-lg flex items-center justify-center mr-4 flex-shrink-0">
            <i class="fas fa-chart-line text-slate-200 text-lg"></i>
        </div>
        <div class="min-w-0 flex-1">
            <h1 class="text-slate-100 font-semibold text-base truncate">{{ $user->role === 'hr' ? 'Human Resources' : ucfirst($user->role) . ' Dashboard' }}</h1>
            <p class="text-slate-300 text-sm truncate">Dashboard</p>
        </div>
    </div>
    <button class="lg:hidden text-slate-400 hover:text-slate-200 transition-colors p-2 rounded-lg hover:bg-slate-600/50 flex-shrink-0 ml-2" onclick="toggleSidebar()">
        <i class="fas fa-times text-lg"></i>
    </button>
</div>

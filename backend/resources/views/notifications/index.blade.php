@extends('layouts.dashboard-base')

@section('content')
<div class="bg-gray-100 min-h-screen py-8 px-4 md:px-8">
    <div class="max-w-5xl mx-auto">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-4xl font-bold text-gray-900">Notifications</h1>
                    <p class="text-gray-600 mt-2">Stay updated with recent system activities and alerts</p>
                </div>
                <div class="bg-blue-100 rounded-full p-3">
                    <i class="fas fa-bell text-blue-600 text-2xl"></i>
                </div>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm">Total Notifications</p>
                        <p class="text-3xl font-bold text-gray-900">{{ $notifications->count() }}</p>
                    </div>
                    <div class="bg-blue-100 rounded-full p-3">
                        <i class="fas fa-bell text-blue-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm">Login Alerts</p>
                        <p class="text-3xl font-bold text-gray-900">{{ $notifications->where('type', 'login')->count() }}</p>
                    </div>
                    <div class="bg-blue-100 rounded-full p-3">
                        <i class="fas fa-sign-in-alt text-blue-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm">Pending Requests</p>
                        <p class="text-3xl font-bold text-gray-900">{{ $notifications->whereIn('type', ['leave', 'overtime'])->count() }}</p>
                    </div>
                    <div class="bg-yellow-100 rounded-full p-3">
                        <i class="fas fa-exclamation-circle text-yellow-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm">Clock Activities</p>
                        <p class="text-3xl font-bold text-gray-900">{{ $notifications->where('type', 'clock')->count() }}</p>
                    </div>
                    <div class="bg-green-100 rounded-full p-3">
                        <i class="fas fa-clock text-green-600 text-xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Notifications List -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-xl font-semibold text-gray-900">Recent Activity</h2>
            </div>

            @if($notifications->isEmpty())
                <div class="p-12 text-center">
                    <i class="fas fa-inbox text-gray-400 text-4xl mb-4"></i>
                    <p class="text-gray-600 text-lg">No notifications at this time</p>
                </div>
            @else
                <div class="divide-y divide-gray-200">
                    @foreach($notifications as $notification)
                        <div class="p-6 hover:bg-gray-50 transition-colors duration-200 border-l-4 {{ 'border-' . $notification['color'] . '-500' }}">
                            <div class="flex items-start space-x-4">
                                <!-- Icon -->
                                <div class="flex-shrink-0">
                                    <div class="flex items-center justify-center h-12 w-12 rounded-full bg-{{ $notification['color'] }}-100">
                                        <i class="fas {{ $notification['icon'] }} text-{{ $notification['color'] }}-600"></i>
                                    </div>
                                </div>

                                <!-- Content -->
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <p class="text-sm font-semibold text-gray-900">{{ $notification['title'] }}</p>
                                            <p class="text-gray-600 text-sm mt-1">{{ $notification['description'] }}</p>
                                        </div>
                                    </div>
                                    <p class="text-xs text-gray-500 mt-2">{{ $notification['time'] }}</p>
                                </div>

                                <!-- Action Arrow -->
                                <div class="flex-shrink-0">
                                    <i class="fas fa-chevron-right text-gray-400"></i>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Filter Info -->
        <div class="mt-8 p-4 bg-blue-50 border border-blue-200 rounded-lg">
            <p class="text-sm text-blue-800">
                <i class="fas fa-info-circle mr-2"></i>
                Showing notifications from the last 7 days. This includes login alerts, pending leave/overtime requests, and employee clock in/out activities.
            </p>
        </div>
    </div>
</div>

<style>
    .bg-blue-100 { background-color: #dbeafe; }
    .text-blue-600 { color: #2563eb; }
    .bg-yellow-100 { background-color: #fef08a; }
    .text-yellow-600 { color: #ca8a04; }
    .bg-green-100 { background-color: #dcfce7; }
    .text-green-600 { color: #16a34a; }
    .bg-purple-100 { background-color: #e9d5ff; }
    .text-purple-600 { color: #9333ea; }
    .bg-red-100 { background-color: #fee2e2; }
    .text-red-600 { color: #dc2626; }
    .border-blue-500 { border-color: #3b82f6; }
    .border-yellow-500 { border-color: #eab308; }
    .border-green-500 { border-color: #22c55e; }
    .border-purple-500 { border-color: #a855f7; }
    .border-red-500 { border-color: #ef4444; }
    .bg-blue-50 { background-color: #eff6ff; }
    .border-blue-200 { border-color: #bfdbfe; }
    .text-blue-800 { color: #1e40af; }
</style>
@endsection

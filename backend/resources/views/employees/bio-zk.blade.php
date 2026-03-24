@extends('layouts.dashboard-base', ['user' => $user, 'activeRoute' => 'employees.bio-zk'])

@section('title', 'Bio ZK')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Bio-ZK</h1>
            <p class="mt-1 text-sm text-gray-500">Manage Bio-ZK filter and capture options</p>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="employee_status" class="block text-sm font-medium text-gray-700 mb-2">Employee status</label>
                <select id="employee_status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="">Select employee status</option>
                </select>
            </div>

            <div>
                <label for="period_type" class="block text-sm font-medium text-gray-700 mb-2">Period type</label>
                <select id="period_type" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="">Select period type</option>
                </select>
            </div>

            <div>
                <label for="branch" class="block text-sm font-medium text-gray-700 mb-2">Branch</label>
                <select id="branch" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="">Select branch</option>
                </select>
            </div>

            <div>
                <label for="department" class="block text-sm font-medium text-gray-700 mb-2">Department</label>
                <select id="department" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="">Select department</option>
                </select>
            </div>

            <div>
                <label for="position" class="block text-sm font-medium text-gray-700 mb-2">Position</label>
                <select id="position" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="">Select position</option>
                </select>
            </div>
        </div>

        <div class="mt-8 border-t border-gray-200 pt-6">
            <h2 class="text-sm font-semibold text-gray-900 mb-4">Options</h2>
            <div class="space-y-3">
                <label class="flex items-center gap-3">
                    <input type="checkbox" class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                    <span class="text-sm text-gray-700">With face</span>
                </label>

                <label class="flex items-center gap-3">
                    <input type="checkbox" class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                    <span class="text-sm text-gray-700">With Fingerprint</span>
                </label>

                <label class="flex items-center gap-3">
                    <input type="checkbox" class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                    <span class="text-sm text-gray-700">With EMP ID Bio ID</span>
                </label>
            </div>
        </div>
    </div>
</div>
@endsection

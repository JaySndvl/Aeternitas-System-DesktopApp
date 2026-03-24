@extends('layouts.dashboard-base', ['user' => $user, 'activeRoute' => 'employees.ytd-info'])

@section('title', 'YTD - INFO')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">YTD - INFO</h1>
        <p class="mt-1 text-sm text-gray-500">Year-to-date information setup</p>
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="md:col-span-2">
                <label for="employee_name" class="block text-sm font-medium text-gray-700 mb-2">Employee Name</label>
                <select id="employee_name" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="">Select employee</option>
                    @foreach($employees as $emp)
                        <option value="{{ data_get($emp, 'id') }}" data-empno="{{ data_get($emp, 'employee_id') }}">{{ data_get($emp, 'first_name') }} {{ data_get($emp, 'last_name') }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="empno" class="block text-sm font-medium text-gray-700 mb-2">Empno</label>
                <input type="text" id="empno" class="w-full h-10 px-3 border border-gray-300 rounded-lg bg-gray-50" placeholder="Auto-filled Empno" readonly>
            </div>
        </div>

        <div class="border border-gray-200 rounded-lg p-5">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Previous Companies</h2>

            <div class="grid grid-cols-1 gap-4">
                @php
                    $fields = [
                        'TAXINC',
                        'TAX',
                        '13MON',
                        '13MON TAX',
                        'SSS EMPLYE',
                        'SSS EMPLYR',
                        'MED EMPLYE',
                        'MED EMPLYR',
                        'PAG EMPLYE',
                        'PAG EMPLYR',
                        'ECC',
                    ];
                @endphp

                @foreach($fields as $field)
                    <div class="flex items-center gap-2">
                        <label class="w-36 shrink-0 text-sm font-medium text-gray-700 whitespace-nowrap text-right">{{ $field }}</label>
                        <span class="text-gray-500 shrink-0">:</span>
                        <input type="text" class="w-full max-w-xl h-10 px-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Enter {{ $field }}">
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const employeeSelect = document.getElementById('employee_name');
    const empnoInput = document.getElementById('empno');

    if (!employeeSelect || !empnoInput) {
        return;
    }

    const setEmpno = () => {
        const selectedOption = employeeSelect.options[employeeSelect.selectedIndex];
        empnoInput.value = selectedOption ? (selectedOption.getAttribute('data-empno') || '') : '';
    };

    employeeSelect.addEventListener('change', setEmpno);
    setEmpno();
});
</script>
@endsection

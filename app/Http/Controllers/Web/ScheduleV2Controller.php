<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\EmployeeSchedule;
use App\Models\Employee;
use App\Models\Department;
use App\Helpers\CompanyHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ScheduleV2Controller extends Controller
{
    /**
     * Get current filter state from request
     */
    private function getFilterState(Request $request)
    {
        return array_filter([
            'department_id' => $request->get('department_id'),
            'month' => $request->get('month'),
            'year' => $request->get('year'),
            'search' => $request->get('search')
        ]);
    }

    /**
     * Build URL with current filters for back navigation
     */
    private function buildBackUrl($filters)
    {
        $params = array_filter($filters, function($value) {
            return !is_null($value) && $value !== '';
        });
        
        return route('schedule-v2.index', $params);
    }

    /**
     * Display the schedule management V2 page
     */
    public function index(Request $request)
    {
        $currentCompany = CompanyHelper::getCurrentCompany();
        $selectedDepartment = $request->get('department_id');
        $selectedYear = $request->get('year', now()->year);
        $selectedMonth = $request->get('month', now()->month);
        $searchQuery = $request->get('search');

        // Get departments for filter
        $departmentsQuery = Department::query();
        if ($currentCompany) {
            $departmentsQuery->forCompany($currentCompany->id);
        }
        $departments = $departmentsQuery->get();

        // Get employees for selected department with search
        $employeesQuery = Employee::with('department');
        if ($currentCompany) {
            $employeesQuery->forCompany($currentCompany->id);
        }
        
        $employees = $employeesQuery
            ->when($selectedDepartment, function($query) use ($selectedDepartment) {
                return $query->where('department_id', $selectedDepartment);
            })
            ->when($searchQuery, function($query) use ($searchQuery) {
                return $query->where(function($q) use ($searchQuery) {
                    $q->where('first_name', 'LIKE', "%{$searchQuery}%")
                      ->orWhere('last_name', 'LIKE', "%{$searchQuery}%")
                      ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$searchQuery}%"]);
                });
            })
            ->orderBy('first_name')
            ->get();

        // If no department selected, get all employees for the bulk modal
        $allEmployeesQuery = Employee::with('department');
        if ($currentCompany) {
            $allEmployeesQuery->forCompany($currentCompany->id);
        }
        $allEmployees = $allEmployeesQuery->orderBy('first_name')->get();

        // Get schedules for the selected month
        $schedules = collect();
        if ($selectedDepartment || $searchQuery) {
            $schedules = EmployeeSchedule::with(['employee', 'department'])
                ->whereYear('date', $selectedYear)
                ->whereMonth('date', $selectedMonth)
                ->when($selectedDepartment, function($query) use ($selectedDepartment) {
                    return $query->where('department_id', $selectedDepartment);
                })
                ->when($searchQuery, function($query) use ($searchQuery) {
                    return $query->whereHas('employee', function($q) use ($searchQuery) {
                        $q->where('first_name', 'LIKE', "%{$searchQuery}%")
                          ->orWhere('last_name', 'LIKE', "%{$searchQuery}%")
                          ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$searchQuery}%"]);
                    });
                })
                ->get()
                ->keyBy(function($schedule) {
                    return $schedule->employee_id . '_' . $schedule->date->format('Y-m-d');
                });
        }

        // Generate calendar days for the month
        $calendarDays = $this->generateCalendarDays($selectedYear, $selectedMonth);

        $user = Auth::user();

        return view('attendance.schedule-v2.index', compact(
            'departments',
            'employees',
            'allEmployees',
            'schedules',
            'calendarDays',
            'selectedDepartment',
            'selectedYear',
            'selectedMonth',
            'searchQuery',
            'user'
        ));
    }

    /**
     * Show the form for creating a new schedule V2
     */
    public function create(Request $request)
    {
        $employeeId = $request->get('employee_id');
        $date = $request->get('date', now()->format('Y-m-d'));

        $currentCompany = CompanyHelper::getCurrentCompany();
        
        $employee = null;
        if ($employeeId) {
            $employeeQuery = Employee::with('department');
            if ($currentCompany) {
                $employeeQuery->forCompany($currentCompany->id);
            }
            $employee = $employeeQuery->find($employeeId);
        }

        $employeesQuery = Employee::with('department');
        if ($currentCompany) {
            $employeesQuery->forCompany($currentCompany->id);
        }
        $employees = $employeesQuery->orderBy('first_name')->get();
        
        $departmentsQuery = \App\Models\Department::query();
        if ($currentCompany) {
            $departmentsQuery->forCompany($currentCompany->id);
        }
        $departments = $departmentsQuery->orderBy('name')->get();
        $user = Auth::user();

        // Get current filter state for back navigation
        $currentFilters = $this->getFilterState($request);

        return view('attendance.schedule-v2.create', compact('employee', 'employees', 'departments', 'date', 'user', 'currentFilters'));
    }

    /**
     * Store a newly created schedule V2
     */
    public function store(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'date' => 'required|date',
            'time_in' => 'sometimes|nullable|date_format:H:i',
            'time_out' => 'sometimes|nullable|date_format:H:i',
            'status' => 'required|in:Working,Day Off,Leave,Absent,Regular Holiday,Special Holiday,Overtime',
            'notes' => 'nullable|string|max:500'
        ]);

        // Additional validation: if time_out is provided, it should be after time_in
        if ($request->time_in && $request->time_out) {
            $timeIn = \Carbon\Carbon::createFromFormat('H:i', $request->time_in);
            $timeOut = \Carbon\Carbon::createFromFormat('H:i', $request->time_out);
            
            if ($timeOut->lte($timeIn)) {
                return redirect()->back()
                    ->withErrors(['time_out' => 'Time out must be after time in.'])
                    ->withInput();
            }
        }

        // Get employee to get department_id
        $employee = Employee::find($request->employee_id);

        // Check if schedule already exists for this employee and date
        $existingSchedule = EmployeeSchedule::where('employee_id', $request->employee_id)
            ->where('date', $request->date)
            ->first();

        if ($existingSchedule) {
            return redirect()->back()
                ->withErrors(['date' => 'A schedule already exists for this employee on this date.'])
                ->withInput();
        }

        // Prepare schedule data
        $scheduleData = [
            'employee_id' => $request->employee_id,
            'department_id' => $employee->department_id,
            'date' => $request->date,
            'status' => $request->status,
            'notes' => $request->notes,
            'created_by' => Auth::id(),
        ];

        // Handle time fields based on status
        if (in_array($request->status, ['Working', 'Overtime', 'Regular Holiday', 'Special Holiday', 'Day Off', 'Leave'])) {
            // For working, holiday, day off, and leave statuses, use the provided time values
            $scheduleData['time_in'] = $request->time_in;
            $scheduleData['time_out'] = $request->time_out;
        } else {
            // For non-working statuses (Absent), set time values to null
            $scheduleData['time_in'] = null;
            $scheduleData['time_out'] = null;
        }

        EmployeeSchedule::create($scheduleData);

        // Get current filter state for redirect
        $filters = $this->getFilterState($request);

        return redirect()->route('schedule-v2.index', $filters)
            ->with('success', 'Schedule created successfully.');
    }

    /**
     * Display the specified schedule V2
     */
    public function show(Request $request, EmployeeSchedule $schedule)
    {
        $schedule->load(['employee', 'department', 'creator']);
        $user = Auth::user();

        // Get current filter state for back navigation
        $currentFilters = $this->getFilterState($request);

        return view('attendance.schedule-v2.show', compact('schedule', 'user', 'currentFilters'));
    }

    /**
     * Show the form for editing the specified schedule V2
     */
    public function edit(Request $request, EmployeeSchedule $schedule)
    {
        $schedule->load(['employee', 'department']);
        $user = Auth::user();

        // Get current filter state for back navigation
        $currentFilters = $this->getFilterState($request);

        return view('attendance.schedule-v2.edit', compact('schedule', 'user', 'currentFilters'));
    }

    /**
     * Update the specified schedule V2
     */
        public function update(Request $request, EmployeeSchedule $schedule)
    {
        $request->validate([
            'time_in' => 'sometimes|nullable|date_format:H:i',
            'time_out' => 'sometimes|nullable|date_format:H:i',
            'status' => 'required|in:Working,Day Off,Leave,Absent,Regular Holiday,Special Holiday,Overtime',
            'notes' => 'nullable|string|max:500'
        ]);

        // Additional validation: if time_out is provided, it should be after time_in
        if ($request->time_in && $request->time_out) {
            $timeIn = \Carbon\Carbon::createFromFormat('H:i', $request->time_in);
            $timeOut = \Carbon\Carbon::createFromFormat('H:i', $request->time_out);
            
            if ($timeOut->lte($timeIn)) {
                return redirect()->back()
                    ->withErrors(['time_out' => 'Time out must be after time in.'])
                    ->withInput();
            }
        }

        // Prepare update data
        $updateData = [
            'status' => $request->status,
            'notes' => $request->notes,
        ];

        // Handle time fields based on status
        if (in_array($request->status, ['Working', 'Overtime', 'Regular Holiday', 'Special Holiday', 'Day Off', 'Leave'])) {
            // For working, holiday, day off, and leave statuses, use the provided time values
            $updateData['time_in'] = $request->time_in;
            $updateData['time_out'] = $request->time_out;
        } else {
            // For non-working statuses (Absent), clear time values
            $updateData['time_in'] = null;
            $updateData['time_out'] = null;
        }

        $schedule->update($updateData);

        // Get current filter state for redirect
        $filters = $this->getFilterState($request);

        return redirect()->route('schedule-v2.index', $filters)
            ->with('success', 'Schedule updated successfully.');
    }

    /**
     * Remove the specified schedule V2
     */
    public function destroy(Request $request, EmployeeSchedule $schedule)
    {
        $schedule->delete();

        // Get current filter state for redirect
        $filters = $this->getFilterState($request);

        return redirect()->route('schedule-v2.index', $filters)
            ->with('success', 'Schedule deleted successfully.');
    }

    /**
     * Bulk create schedules V2
     */
    public function bulkCreate(Request $request)
    {
        // Handle employee-specific schedules (from per-employee date selection)
        if ($request->has('employee_schedules')) {
            $request->validate([
                'employee_schedules' => 'required|array|min:1',
                'employee_schedules.*.employee_id' => 'required|exists:employees,id',
                'employee_schedules.*.dates' => 'required|array|min:1',
                'employee_schedules.*.dates.*' => 'date',
                'time_in' => 'sometimes|nullable|date_format:H:i',
                'time_out' => 'sometimes|nullable|date_format:H:i',
                'status' => 'required|in:Working,Day Off,Leave,Absent,Regular Holiday,Special Holiday,Overtime',
                'notes' => 'nullable|string|max:500'
            ]);

            $createdCount = 0;

            foreach ($request->employee_schedules as $employeeSchedule) {
                $employee = Employee::find($employeeSchedule['employee_id']);
                
                foreach ($employeeSchedule['dates'] as $date) {
                    // Check if schedule already exists
                    $existingSchedule = EmployeeSchedule::where('employee_id', $employeeSchedule['employee_id'])
                        ->where('date', $date)
                        ->first();

                    if (!$existingSchedule) {
                        // Prepare schedule data
                        $scheduleData = [
                            'employee_id' => $employeeSchedule['employee_id'],
                            'department_id' => $employee->department_id,
                            'date' => $date,
                            'status' => $request->status,
                            'notes' => $request->notes,
                            'created_by' => Auth::id(),
                        ];

                        // Handle time fields based on status
                        if (in_array($request->status, ['Working', 'Overtime', 'Regular Holiday', 'Special Holiday', 'Day Off', 'Leave'])) {
                            $scheduleData['time_in'] = $request->time_in;
                            $scheduleData['time_out'] = $request->time_out;
                        } else {
                            $scheduleData['time_in'] = null;
                            $scheduleData['time_out'] = null;
                        }

                        EmployeeSchedule::create($scheduleData);
                        $createdCount++;
                    }
                }
            }

            return response()->json([
                'success' => true,
                'message' => "Successfully created {$createdCount} schedule(s).",
                'created_count' => $createdCount
            ]);
        }
        // Handle both date range and specific dates
        elseif ($request->has('dates')) {
            // Handle specific dates (from date select mode)
            $request->validate([
                'employee_ids' => 'required|array|min:1',
                'employee_ids.*' => 'exists:employees,id',
                'dates' => 'required|array|min:1',
                'dates.*' => 'date',
                'time_in' => 'sometimes|nullable|date_format:H:i',
                'time_out' => 'sometimes|nullable|date_format:H:i',
                'status' => 'required|in:Working,Day Off,Leave,Absent,Regular Holiday,Special Holiday,Overtime',
                'notes' => 'nullable|string|max:500'
            ]);

            $createdCount = 0;
            $dates = $request->dates;

            foreach ($request->employee_ids as $employeeId) {
                $employee = Employee::find($employeeId);
                
                foreach ($dates as $date) {
                    // Check if schedule already exists
                    $existingSchedule = EmployeeSchedule::where('employee_id', $employeeId)
                        ->where('date', $date)
                        ->first();

                    if (!$existingSchedule) {
                        // Prepare schedule data
                        $scheduleData = [
                            'employee_id' => $employeeId,
                            'department_id' => $employee->department_id,
                            'date' => $date,
                            'status' => $request->status,
                            'notes' => $request->notes,
                            'created_by' => Auth::id(),
                        ];

                        // Handle time fields based on status
                        if (in_array($request->status, ['Working', 'Overtime', 'Regular Holiday', 'Special Holiday', 'Day Off', 'Leave'])) {
                            $scheduleData['time_in'] = $request->time_in;
                            $scheduleData['time_out'] = $request->time_out;
                        } else {
                            $scheduleData['time_in'] = null;
                            $scheduleData['time_out'] = null;
                        }

                        EmployeeSchedule::create($scheduleData);
                        $createdCount++;
                    }
                }
            }

            return response()->json([
                'success' => true,
                'message' => "Successfully created {$createdCount} schedule(s).",
                'created_count' => $createdCount
            ]);
        } else {
            // Handle date range (original functionality)
            $request->validate([
                'employee_ids' => 'required|array|min:1',
                'employee_ids.*' => 'exists:employees,id',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
                'time_in' => 'sometimes|nullable|date_format:H:i',
                'time_out' => 'sometimes|nullable|date_format:H:i',
                'status' => 'required|in:Working,Day Off,Leave,Absent,Regular Holiday,Special Holiday,Overtime',
                'notes' => 'nullable|string|max:500'
            ]);

            $startDate = Carbon::parse($request->start_date);
            $endDate = Carbon::parse($request->end_date);
            $createdCount = 0;

            foreach ($request->employee_ids as $employeeId) {
                $employee = Employee::find($employeeId);
                
                // Create schedule for each date in the range
                $currentDate = $startDate->copy();
                while ($currentDate->lte($endDate)) {
                    // Check if schedule already exists
                    $existingSchedule = EmployeeSchedule::where('employee_id', $employeeId)
                        ->where('date', $currentDate->format('Y-m-d'))
                        ->first();

                    if (!$existingSchedule) {
                        // Prepare schedule data
                        $scheduleData = [
                            'employee_id' => $employeeId,
                            'department_id' => $employee->department_id,
                            'date' => $currentDate->format('Y-m-d'),
                            'status' => $request->status,
                            'notes' => $request->notes,
                            'created_by' => Auth::id(),
                        ];

                        // Handle time fields based on status
                        if (in_array($request->status, ['Working', 'Overtime', 'Regular Holiday', 'Special Holiday', 'Day Off', 'Leave'])) {
                            $scheduleData['time_in'] = $request->time_in;
                            $scheduleData['time_out'] = $request->time_out;
                        } else {
                            $scheduleData['time_in'] = null;
                            $scheduleData['time_out'] = null;
                        }

                        EmployeeSchedule::create($scheduleData);
                        $createdCount++;
                    }
                    
                    $currentDate->addDay();
                }
            }

            return redirect()->route('schedule-v2.index')
                ->with('success', "Successfully created {$createdCount} schedule(s).");
        }
    }

    /**
     * Bulk delete schedules V2
     */
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'schedule_ids' => 'required|array|min:1',
            'schedule_ids.*' => 'exists:employee_schedules,id'
        ]);

        $deletedCount = EmployeeSchedule::whereIn('id', $request->schedule_ids)->delete();

        if ($deletedCount > 0) {
            return response()->json([
                'success' => true,
                'message' => "Successfully deleted {$deletedCount} schedule(s).",
                'deleted_count' => $deletedCount
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'No schedules were deleted.'
        ]);
    }

    /**
     * Get statistics for schedule V2
     */
    public function getStatistics(Request $request)
    {
        $year = $request->get('year', now()->format('Y'));
        $month = $request->get('month', now()->format('m'));
        $departmentId = $request->get('department_id');

        $query = EmployeeSchedule::whereYear('date', $year)
            ->whereMonth('date', $month);

        if ($departmentId) {
            $query->where('department_id', $departmentId);
        }

        $totalSchedules = $query->count();
        $workingDays = $query->where('status', 'Working')->count();
        $dayOffs = $query->where('status', 'Day Off')->count();
        $leaves = $query->where('status', 'Leave')->count();

        return response()->json([
            'totalSchedules' => $totalSchedules,
            'workingDays' => $workingDays,
            'dayOffs' => $dayOffs,
            'leaves' => $leaves
        ]);
    }

    /**
     * Generate calendar days for a given month
     */
    private function generateCalendarDays($year, $month)
    {
        $days = [];
        $daysInMonth = Carbon::create($year, $month)->daysInMonth;

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = Carbon::create($year, $month, $day);
            $days[] = [
                'day' => $day,
                'date' => $date,
                'is_weekend' => $date->isWeekend(),
                'is_today' => $date->isToday(),
            ];
        }

        return $days;
    }
}

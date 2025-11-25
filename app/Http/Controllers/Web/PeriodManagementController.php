<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\EmployeeSchedule;
use App\Models\AttendanceRecord;
use App\Models\AttendanceLog;
use App\Models\Payroll;
use App\Models\Period;
use App\Services\PayrollGenerationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class PeriodManagementController extends Controller
{
    /**
     * Display a listing of periods
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        $periods = Period::with('department')
            ->orderBy('created_at', 'desc')
            ->get();
        
       
        $filters = $this->getFilterState($request);
        
        return view('attendance.period-management.index', compact('periods', 'user', 'filters'));
    }

    /**
     * Show the form for creating a new period
     */
    public function create(Request $request)
    {
        $user = Auth::user();
        
        // Get departments and employees for filtering
        $departments = \App\Models\Department::orderBy('name')->get();
        $employees = \App\Models\Employee::with('department')->orderBy('first_name')->get();
        
        // Get current filter state for back navigation
        $currentFilters = $this->getFilterState($request);
        
        return view('attendance.period-management.create', compact('user', 'currentFilters', 'departments', 'employees'));
    }

    /**
     * Store a newly created period
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'department_id' => 'nullable|exists:departments,id',
            'employee_ids' => 'nullable|array',
            'employee_ids.*' => 'exists:employees,id',
        ]);

        // Create new period in database
        $period = Period::create([
            'name' => $request->name,
            'description' => $request->description,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'department_id' => $request->department_id,
            'employee_ids' => $request->employee_ids ?? [],
            'created_by' => Auth::user()->full_name,
        ]);
        
        return redirect()->route('attendance.period-management.index')
            ->with('success', 'Period created successfully.');
    }

    /**
     * Display the specified period with attendance analysis
     */
    public function show(Request $request, $periodId)
    {
        $user = Auth::user();
        
        // Find the specific period from database
        $period = Period::with('department')->find($periodId);
        
        if (!$period) {
            return redirect()->route('attendance.period-management.index')
                ->with('error', 'Period not found.');
        }
        
        // Convert to Carbon dates
        $startDate = Carbon::parse($period->start_date);
        $endDate = Carbon::parse($period->end_date);
        
        // Get current filter state for back navigation
        $currentFilters = $this->getFilterState($request);
        
        // Get all employees or filtered employees
        $employees = Employee::with('department');
        
        // Apply department filter if specified
        if (!empty($period->department_id)) {
            $employees = $employees->where('department_id', $period->department_id);
        }
        
        // Apply specific employee filter if specified
        if (!empty($period->employee_ids) && is_array($period->employee_ids)) {
            $employees = $employees->whereIn('id', $period->employee_ids);
        }
        
        $employees = $employees->get();
        
        // Get comprehensive attendance data for the period
        $comprehensiveData = $this->getComprehensiveAttendanceData($startDate, $endDate, $employees);
        
        // Calculate summary statistics
        $summaryData = $this->calculateSummaryDataFromComprehensive($comprehensiveData);
        
        return view('attendance.period-management.show', compact(
            'period', 
            'user', 
            'currentFilters', 
            'comprehensiveData',
            'summaryData',
            'employees'
        ));
    }

    /**
     * Remove the specified period
     */
    public function destroy(Request $request, $periodId = null)
    {
        // Handle both URL parameter and form submission
        $targetPeriodId = $periodId ?: $request->input('period_id');
        
        if (!$targetPeriodId) {
            return redirect()->route('attendance.period-management.index')
                ->with('error', 'Period ID is required.');
        }
        
        // Find the period in database
        $periodToDelete = Period::find($targetPeriodId);
        
        if (!$periodToDelete) {
            return redirect()->route('attendance.period-management.index')
                ->with('error', 'Period not found.');
        }
        
        // Delete the period from database
        $periodToDelete->delete();
        
        return redirect()->route('attendance.period-management.index')
            ->with('success', "Period '{$periodToDelete->name}' deleted successfully.");
    }

    /**
     * Get comprehensive attendance data for all employees in the period
     * 
     * This method creates a detailed attendance report by:
     * 1. Iterating through each employee and each date in the period
     * 2. Fetching attendance records and employee schedules for each day
     * 3. Calculating worked hours, overtime, and attendance status
     * 4. Formatting all data for display in the comprehensive table
     * 
     * @param Carbon $startDate Start date of the period
     * @param Carbon $endDate End date of the period
     * @param Collection $employees Collection of employees to analyze
     * @return array Comprehensive attendance data array
     */
    private function getComprehensiveAttendanceData($startDate, $endDate, $employees)
    {
        $comprehensiveData = [];
        
        foreach ($employees as $employee) {
            $currentDate = $startDate->copy();
            
            while ($currentDate->lte($endDate)) {
                $dateStr = $currentDate->format('Y-m-d');
                
                // Get attendance record for this date
                $attendanceRecord = AttendanceRecord::where('employee_id', $employee->id)
                    ->where('date', $dateStr)
                    ->first();
                
                // Get schedule for this date
                $schedule = EmployeeSchedule::where('employee_id', $employee->id)
                    ->where('date', $dateStr)
                    ->first();
                
                // Determine schedule status
                $scheduleStatus = $this->getScheduleStatus($schedule);
                
                // Determine attendance status
                $attendanceStatus = $this->getAttendanceStatus($attendanceRecord, $schedule);
                
                // Initialize default values for non-working days
                $workedHours = '—';
                $scheduledHours = '—';
                $morningOvertime = 0;
                $eveningOvertime = 0;
                $overtime = 0;
                $nightDifferentialHours = 0;
                $lateMinutes = 0;
                $isNightShift = false;
                
                // Calculate attendance metrics if:
                // 1. Schedule status is 'Working' or 'Regular Holiday' or 'Special Holiday'
                // 2. Schedule status is 'Leave' AND employee has attendance (working on rest day with 1.2x premium)
                // 3. Schedule status is 'Day Off' AND employee has attendance (emergency work)
                $shouldCalculateMetrics = in_array($scheduleStatus, ['Working', 'Regular Holiday', 'Special Holiday']) ||
                    (($scheduleStatus === 'Leave' || $scheduleStatus === 'Day Off') && $attendanceRecord && $attendanceRecord->time_in && $attendanceRecord->time_out);
                
                if ($shouldCalculateMetrics) {
                    $workedHours = $this->calculateWorkedHours($attendanceRecord, $schedule);
                    $scheduledHours = $this->calculateScheduledHours($attendanceRecord, $schedule);
                    $morningOvertime = $this->calculateMorningOvertime($attendanceRecord, $schedule);
                    $eveningOvertime = $this->calculateEveningOvertime($attendanceRecord, $schedule);
                    $overtime = $this->calculateOvertime($attendanceRecord, $schedule);
                    $nightDifferentialHours = $attendanceRecord ? $attendanceRecord->calculateNightShiftHours() : 0;
                    $lateMinutes = $this->calculateLateMinutes($attendanceRecord, $schedule);
                    $isNightShift = $attendanceRecord ? $attendanceRecord->isNightShift() : false;
                }
                
                // Format times
                $scheduleInOut = $this->formatScheduleTime($schedule, $attendanceRecord);
                $actualInOut = $this->formatActualTime($attendanceRecord, $scheduleStatus);
                $workingHours = $this->calculateWorkingHours($schedule, $attendanceRecord);
                
                // Create combined status - handle Leave and Day Off days with attendance (rest day work / emergency work)
                // If employee worked on Leave day, show as "Leave - Present" (indicates premium pay)
                // If employee worked on Day Off, show as "Day Off - Present" (indicates emergency work)
                $combinedStatus = $scheduleStatus;
                if ($scheduleStatus === 'No Schedule') {
                    // If no schedule, show attendance status or "No Schedule"
                    if ($attendanceStatus === 'Present') {
                        $combinedStatus = 'No Schedule - Present';
                    } else {
                        $combinedStatus = 'No Schedule';
                    }
                } elseif ($scheduleStatus === 'Leave' && $attendanceStatus === 'Present') {
                    $combinedStatus = 'Leave - Present';
                } elseif ($scheduleStatus === 'Day Off' && $attendanceStatus === 'Present') {
                    $combinedStatus = 'Day Off - Present';
                } elseif ($scheduleStatus === 'Leave') {
                    $combinedStatus = 'Leave';
                } 
                elseif ($scheduleStatus !== 'Day Off' || $attendanceStatus !== 'Day Off') {
                    $combinedStatus = $scheduleStatus . ' - ' . $attendanceStatus;
                }
                
                $comprehensiveData[] = [
                    'employee_id' => $employee->id,
                    'employee_code' => $employee->employee_code,
                    'employee_name' => $employee->full_name,
                    'date' => $dateStr,
                    'date_formatted' => $currentDate->format('M j, Y'),
                    'schedule_in_out' => $scheduleInOut,
                    'working_hours' => $workingHours,
                    'actual_in_out' => $actualInOut,
                    'worked_hours' => $workedHours,
                    'scheduled_hours' => $scheduledHours,
                    'morning_overtime' => $morningOvertime,
                    'evening_overtime' => $eveningOvertime,
                    'overtime' => $overtime,
                    'night_differential_hours' => $nightDifferentialHours,
                    'late_minutes' => $lateMinutes,
                    'is_night_shift' => $isNightShift,
                    'schedule_status' => $scheduleStatus,
                    'attendance_status' => $attendanceStatus,
                    'combined_status' => $combinedStatus,
                ];
                
                $currentDate->addDay();
            }
        }
        
        return $comprehensiveData;
    }
    
    /**
     * Get schedule status
     */
    private function getScheduleStatus($schedule)
    {
        if (!$schedule) {
            return 'No Schedule'; // No schedule exists for this day
        }
        
        switch ($schedule->status) {
            case 'Working':
                return 'Working';
            case 'Day Off':
            case 'Rest Day':
                return 'Day Off';
            case 'Regular Holiday':
                return 'Regular Holiday';
            case 'Special Holiday':
                return 'Special Holiday';
            case 'Leave':
                return 'Leave';
            default:
                return 'Working'; // Default to Working for unknown status (applies default 9-6 schedule)
        }
    }
    

    private function getAttendanceStatus($attendanceRecord, $schedule = null)
    {
        // Rule 0: If there's no schedule, employee should not be marked as Absent
        // If employee has attendance without schedule, return 'Present' (unscheduled work)
        // If employee has no attendance and no schedule, return 'No Schedule'
        if (!$schedule) {
            if ($attendanceRecord && $attendanceRecord->time_in && $attendanceRecord->time_out) {
                return 'Present'; // Employee worked without schedule (unscheduled work)
            }
            return 'No Schedule'; // No schedule and no attendance - not absent
        }
        
        // Check if schedule has times
        $hasScheduleTimes = $schedule->time_in && $schedule->time_out;
        
        // Rule 0: If schedule is Day Off or Rest Day → check if employee worked
        // If employee worked on Day Off (called in for emergency), return 'Present'
        // If schedule has times and no attendance, return 'Absent'
        // If no schedule times and no attendance, return 'Day Off'
        if ($schedule && ($schedule->status === 'Day Off' || $schedule->status === 'Rest Day')) {
            // If employee has attendance (worked on Day Off - emergency work), return Present
            if ($attendanceRecord && $attendanceRecord->time_in && $attendanceRecord->time_out) {
                return 'Present'; // Employee worked on Day Off - emergency work
            }
            // If schedule has times but no attendance, return Absent
            if ($hasScheduleTimes) {
                return 'Absent'; // Schedule exists with times but no attendance
            }
            return 'Day Off'; // No schedule times and no attendance on Day Off
        }
        
        // Rule 0.5: If schedule is Leave → check if employee worked
        // If employee worked on Leave, return 'Present' (rest day work with 1.2x premium)
        // If schedule has times and no attendance, return 'Absent'
        // If no schedule times and no attendance, return 'Day Off'
        if ($schedule && $schedule->status === 'Leave') {
            // If employee has attendance (worked on Leave day/rest day), return Present
            if ($attendanceRecord && $attendanceRecord->time_in && $attendanceRecord->time_out) {
                return 'Present'; // Employee worked on Leave day - will be paid 1.2x premium
            }
            // If schedule has times but no attendance, return Absent
            if ($hasScheduleTimes) {
                return 'Absent'; // Schedule exists with times but no attendance
            }
            return 'Day Off'; // No schedule times and no attendance on Leave day
        }
        
        // Rule 0.6: If schedule is Regular Holiday or Special Holiday
        // If schedule has times and no attendance, return 'Absent'
        if ($schedule && ($schedule->status === 'Regular Holiday' || $schedule->status === 'Special Holiday')) {
            // If employee has attendance, will be handled by later rules
            // If no attendance and schedule has times, return Absent
            if (!$attendanceRecord && $hasScheduleTimes) {
                return 'Absent'; // Schedule exists with times but no attendance
            }
        }
        
        if (!$attendanceRecord) {
            return 'Absent';
        }
        
        $hasTimeIn = !empty($attendanceRecord->time_in);
        $hasTimeOut = !empty($attendanceRecord->time_out);
        
        // Rule 1: No Time In and No Time Out → Absent
        if (!$hasTimeIn && !$hasTimeOut) {
            return 'Absent';
        }
        
        // Rule 2: Has only Time In or Time Out → Error
        if (($hasTimeIn && !$hasTimeOut) || (!$hasTimeIn && $hasTimeOut)) {
            return 'Error';
        }
        
        // Rule 3: Time In is later than Schedule Out → Absent (compare only time parts)
        if ($hasTimeIn && $schedule && $schedule->time_out) {
            $timeIn = Carbon::parse($attendanceRecord->time_in);
            $scheduleOut = Carbon::parse($schedule->time_out);
            
            // Compare only time parts to avoid date comparison issues
            if ($timeIn->format('H:i:s') > $scheduleOut->format('H:i:s')) {
                return 'Absent';
            }
        }
        
        // Rule 4: Both times are completely outside the scheduled shift → Absent (compare only time parts)
        if ($hasTimeIn && $hasTimeOut && $schedule && $schedule->time_in && $schedule->time_out) {
            $timeIn = Carbon::parse($attendanceRecord->time_in);
            $timeOut = Carbon::parse($attendanceRecord->time_out);
            $scheduleIn = Carbon::parse($schedule->time_in);
            $scheduleOut = Carbon::parse($schedule->time_out);
            
            // Compare only time parts to avoid date comparison issues
            $timeInTime = $timeIn->format('H:i:s');
            $timeOutTime = $timeOut->format('H:i:s');
            $scheduleInTime = $scheduleIn->format('H:i:s');
            $scheduleOutTime = $scheduleOut->format('H:i:s');
            
            // Only mark as Absent if both times are completely outside working range
            // (not just overtime - overtime is valid)
            if (
                ($timeInTime < $scheduleInTime && $timeOutTime < $scheduleInTime) ||
                ($timeInTime > $scheduleOutTime && $timeOutTime > $scheduleOutTime)
            ) {
                return 'Absent';
            }
        }
        
        // Rule 5: Has both Time In and Time Out (valid range) → Present
        if ($hasTimeIn && $hasTimeOut) {
            return 'Present';
        }
        
        return 'Absent';
    }
    
    /**
     * Calculate morning overtime (time worked before scheduled start time)
     * 
     * This method calculates overtime for time worked before the scheduled start time:
     * 1. Compares actual time_in with scheduled time_in
     * 2. Returns positive hours if employee clocked in early
     * 3. Returns 0 if employee clocked in on time or late
     * 
     * @param AttendanceRecord|null $attendanceRecord Employee's attendance record
     * @param EmployeeSchedule|null $schedule Employee's schedule for the day
     * @return float Morning overtime hours (rounded to 2 decimal places)
     */
    private function calculateMorningOvertime($attendanceRecord, $schedule)
    {
        if (
            !$attendanceRecord ||
            !$attendanceRecord->time_in || !$attendanceRecord->time_out
        ) {
            return 0;
        }

        // Get effective schedule (actual or default)
        $effectiveSchedule = $this->getEffectiveSchedule($schedule, $attendanceRecord);
        
        // If no effective schedule (Holiday/Leave), no overtime calculation
        if (!$effectiveSchedule) {
            return 0;
        }

        $timeIn = Carbon::parse($attendanceRecord->time_in);
        $scheduleIn = Carbon::parse($effectiveSchedule['time_in']);
        
        // Compare only time parts to avoid date comparison issues
        $timeInTime = $timeIn->format('H:i:s');
        $scheduleInTime = $scheduleIn->format('H:i:s');
        
        // If employee clocked in before schedule start time, calculate morning overtime
        if ($timeInTime < $scheduleInTime) {
            $scheduleStartMinutes = $scheduleIn->hour * 60 + $scheduleIn->minute;
            $actualStartMinutes = $timeIn->hour * 60 + $timeIn->minute;
            $morningOvertimeMinutes = $scheduleStartMinutes - $actualStartMinutes;
            return round($morningOvertimeMinutes / 60, 2);
        }
        
        return 0; // No morning overtime
    }

   
    private function calculateEveningOvertime($attendanceRecord, $schedule)
    {
        if (
            !$attendanceRecord ||
            !$attendanceRecord->time_in || !$attendanceRecord->time_out
        ) {
            return 0;
        }

        // Get effective schedule (actual or default)
        $effectiveSchedule = $this->getEffectiveSchedule($schedule, $attendanceRecord);
        
        // If no effective schedule (Holiday/Leave), no overtime calculation
        if (!$effectiveSchedule) {
            return 0;
        }

        $timeOut = Carbon::parse($attendanceRecord->time_out);
        $scheduleOut = Carbon::parse($effectiveSchedule['time_out']);
        
        // Compare only time parts to avoid date comparison issues
        $timeOutTime = $timeOut->format('H:i:s');
        $scheduleOutTime = $scheduleOut->format('H:i:s');
        
        // If employee clocked out after schedule end time, calculate evening overtime
        if ($timeOutTime > $scheduleOutTime) {
            $scheduleEndMinutes = $scheduleOut->hour * 60 + $scheduleOut->minute;
            $actualEndMinutes = $timeOut->hour * 60 + $timeOut->minute;
            $eveningOvertimeMinutes = $actualEndMinutes - $scheduleEndMinutes;
            return round($eveningOvertimeMinutes / 60, 2);
        }
        
        return 0; // No evening overtime
    }

    /**
     * Calculate total overtime (morning + evening overtime)
     * 
     * @param AttendanceRecord|null $attendanceRecord Employee's attendance record
     * @param EmployeeSchedule|null $schedule Employee's schedule for the day
     * @return float Total overtime hours (rounded to 2 decimal places)
     */
    private function calculateOvertime($attendanceRecord, $schedule)
    {
        $morningOvertime = $this->calculateMorningOvertime($attendanceRecord, $schedule);
        $eveningOvertime = $this->calculateEveningOvertime($attendanceRecord, $schedule);
        
        return round($morningOvertime + $eveningOvertime, 2);
    }
    
    /**
     * Format decimal hours to readable format (e.g., 8.5 → "8 hrs 30 mins")
     * 
     * @param float $decimalHours
     * @return string Formatted hours and minutes
     */
    private function formatHoursToReadable($decimalHours)
    {
        if ($decimalHours <= 0) {
            return '0 hrs';
        }
        
        $hours = floor($decimalHours);
        $minutes = round(($decimalHours - $hours) * 60);
        
        // Handle minute rounding that might exceed 59
        if ($minutes >= 60) {
            $hours += 1;
            $minutes = 0;
        }
        
        $result = '';
        
        if ($hours > 0) {
            $result .= $hours . ' hr' . ($hours > 1 ? 's' : '');
        }
        
        if ($minutes > 0) {
            if ($hours > 0) {
                $result .= ' ';
            }
            $result .= $minutes . ' min' . ($minutes > 1 ? 's' : '');
        }
        
        return $result ?: '0 hrs';
    }
   

    private function calculateLateMinutes($attendanceRecord, $schedule)
    {
        if (
            !$attendanceRecord ||
            !$attendanceRecord->time_in
        ) {
            return 0;
        }

        // Get effective schedule (actual or default)
        $effectiveSchedule = $this->getEffectiveSchedule($schedule, $attendanceRecord);
        
        // If no effective schedule (Holiday/Leave), no late calculation
        if (!$effectiveSchedule) {
            return 0;
        }

        $timeIn = Carbon::parse($attendanceRecord->time_in);
        $scheduleIn = Carbon::parse($effectiveSchedule['time_in']);
        
        // Compare only time parts to avoid date comparison issues
        $timeInTime = $timeIn->format('H:i:s');
        $scheduleInTime = $scheduleIn->format('H:i:s');
        
        // If employee clocked in after schedule start time, calculate late minutes
        if ($timeInTime > $scheduleInTime) {
            // Calculate minutes between schedule start and actual start time
            $scheduleStartMinutes = $scheduleIn->hour * 60 + $scheduleIn->minute;
            $actualStartMinutes = $timeIn->hour * 60 + $timeIn->minute;
            $lateMinutes = $actualStartMinutes - $scheduleStartMinutes;
            return $lateMinutes;
        }
        
        return 0; // Not late
    }
    
   
    private function getEffectiveSchedule($schedule, $attendanceRecord = null)
    {
        // If no schedule at all (null), return default schedule (9am-6pm)
        if (!$schedule) {
            return [
                'time_in' => '09:00:00',
                'time_out' => '18:00:00'
            ];
        }
        
        // Get schedule status for existing schedule
        $scheduleStatus = $this->getScheduleStatus($schedule);
        
        // SPECIAL CASE: For Day Off days, if employee worked (has attendance), use schedule for calculations
        // Employee is working on Day Off (emergency work)
        if ($scheduleStatus === 'Day Off' && $attendanceRecord && 
            $attendanceRecord->time_in && $attendanceRecord->time_out) {
            // If schedule has times, use them
            if ($schedule->time_in && $schedule->time_out) {
                return [
                    'time_in' => $schedule->time_in,
                    'time_out' => $schedule->time_out
                ];
            }
            // Fallback to default schedule if no times in schedule (for Day Off work calculations)
            return [
                'time_in' => '09:00:00',
                'time_out' => '18:00:00'
            ];
        }
        
        // If schedule status is Day Off (without attendance), return null (no schedule calculations)
        if ($scheduleStatus === 'Day Off') {
            return null;
        }
        
        // SPECIAL CASE: For holidays, if employee worked (has attendance), use the actual schedule times
        if (($scheduleStatus === 'Regular Holiday' || $scheduleStatus === 'Special Holiday') && $attendanceRecord && 
            $attendanceRecord->time_in && $attendanceRecord->time_out) {
            // Use the actual time_in and time_out from the schedule table for holidays
            if ($schedule->time_in && $schedule->time_out) {
                return [
                    'time_in' => $schedule->time_in,
                    'time_out' => $schedule->time_out
                ];
            }
            // Fallback to default schedule if no times in schedule
            return [
                'time_in' => '09:00:00',
                'time_out' => '18:00:00'
            ];
        }
        
        // SPECIAL CASE: For Leave days, if employee worked (has attendance), use schedule for calculations
        // Employee is working on rest day (will be paid 1.2x premium)
        if ($scheduleStatus === 'Leave' && $attendanceRecord && 
            $attendanceRecord->time_in && $attendanceRecord->time_out) {
            // If schedule has times, use them
            if ($schedule->time_in && $schedule->time_out) {
                return [
                    'time_in' => $schedule->time_in,
                    'time_out' => $schedule->time_out
                ];
            }
            // Fallback to default schedule if no times in schedule (for rest day work calculations)
            return [
                'time_in' => '09:00:00',
                'time_out' => '18:00:00'
            ];
        }
        
        // If schedule status is Leave (without attendance), don't apply any schedule
        if ($scheduleStatus === 'Leave') {
            return null;
        }
        
        // For holidays, if there are actual times in the schedule, use them
        if (($scheduleStatus === 'Regular Holiday' || $scheduleStatus === 'Special Holiday') && 
            $schedule->time_in && $schedule->time_out) {
            return [
                'time_in' => $schedule->time_in,
                'time_out' => $schedule->time_out
            ];
        }
        
        // If schedule exists and has time_in and time_out, use it
        if ($schedule->time_in && $schedule->time_out) {
            return [
                'time_in' => $schedule->time_in,
                'time_out' => $schedule->time_out
            ];
        }
        
        // Default schedule: 9:00 AM to 6:00 PM (for Working status with missing times)
        return [
            'time_in' => '09:00:00',
            'time_out' => '18:00:00'
        ];
    }
    
    /**
     * Format schedule time
     */
    private function formatScheduleTime($schedule, $attendanceRecord = null)
    {
        $scheduleStatus = $this->getScheduleStatus($schedule);
        
        // If no schedule exists, show default time (9am-6pm)
        if ($scheduleStatus === 'No Schedule') {
            return '09:00–18:00';
        }
        
        // Check if schedule has times
        $hasScheduleTimes = $schedule && $schedule->time_in && $schedule->time_out;
        // Check if employee has attendance
        $hasAttendance = $attendanceRecord && $attendanceRecord->time_in && $attendanceRecord->time_out;
        
        // For holidays, Day Off, and Leave
        if (in_array($scheduleStatus, ['Regular Holiday', 'Special Holiday', 'Day Off', 'Leave'])) {
            // If schedule has times, use them
            if ($hasScheduleTimes) {
                $timeIn = Carbon::parse($schedule->time_in)->format('H:i');
                $timeOut = Carbon::parse($schedule->time_out)->format('H:i');
                return "{$timeIn}–{$timeOut}";
            }
            // If no schedule times but has attendance, use default time
            if ($hasAttendance) {
                return '09:00–18:00';
            }
            // If no schedule times and no attendance, show status text
            return $scheduleStatus;
        }
        
        $effectiveSchedule = $this->getEffectiveSchedule($schedule);
        
        // If no effective schedule, use default
        if (!$effectiveSchedule) {
            return '09:00–18:00'; // Default schedule
        }
        
        $timeIn = Carbon::parse($effectiveSchedule['time_in'])->format('H:i');
        $timeOut = Carbon::parse($effectiveSchedule['time_out'])->format('H:i');
        
        return "{$timeIn}–{$timeOut}";
    }
    
    /**
     * Format actual time
     */
    private function formatActualTime($attendanceRecord, $scheduleStatus = null)
    {
        // If schedule status is non-working but employee has attendance (emergency work), show actual times
        if (in_array($scheduleStatus, ['Day Off', 'Leave', 'Rest Day'])) {
            // Show actual times if employee worked (has attendance)
            if ($attendanceRecord && $attendanceRecord->time_in && $attendanceRecord->time_out) {
                $timeIn = Carbon::parse($attendanceRecord->time_in)->format('H:i');
                $timeOut = Carbon::parse($attendanceRecord->time_out)->format('H:i');
                return "{$timeIn}–{$timeOut}";
            }
            // No attendance on non-working day
            return '—';
        }
        
        if (!$attendanceRecord) {
            return '—';
        }
        
        $timeIn = $attendanceRecord->time_in ? Carbon::parse($attendanceRecord->time_in)->format('H:i') : '—';
        $timeOut = $attendanceRecord->time_out ? Carbon::parse($attendanceRecord->time_out)->format('H:i') : '—';
        
        return "{$timeIn}–{$timeOut}";
    }
    
    /**
     * Calculate working hours from schedule (subtract 1 hour for lunch break)
     */
    private function calculateWorkingHours($schedule, $attendanceRecord = null)
    {
        $scheduleStatus = $this->getScheduleStatus($schedule);
        
        // If no schedule exists, show default working hours (8 hrs)
        if ($scheduleStatus === 'No Schedule') {
            return '8 hrs';
        }
        
        // Check if schedule has times
        $hasScheduleTimes = $schedule && $schedule->time_in && $schedule->time_out;
        // Check if employee has attendance
        $hasAttendance = $attendanceRecord && $attendanceRecord->time_in && $attendanceRecord->time_out;
        
        // For holidays, Day Off, and Leave
        if (in_array($scheduleStatus, ['Regular Holiday', 'Special Holiday', 'Day Off', 'Leave'])) {
            // If schedule has times, calculate from them
            if ($hasScheduleTimes) {
                $timeIn = Carbon::parse($schedule->time_in);
                $timeOut = Carbon::parse($schedule->time_out);
                $totalHours = round($timeIn->diffInMinutes($timeOut) / 60, 2);
                $workingHours = max(0, $totalHours - 1); // Subtract 1 hour for lunch break
                return $this->formatHoursToReadable($workingHours);
            }
            // If no schedule times but has attendance, use default hours
            if ($hasAttendance) {
                return '8 hrs'; // Default working hours
            }
            // If no schedule times and no attendance, show status text
            return $scheduleStatus;
        }
        
        $effectiveSchedule = $this->getEffectiveSchedule($schedule, null);
        
        // If no effective schedule, use default (9am-6pm = 8 hours)
        if (!$effectiveSchedule) {
            return '8 hrs'; // Default working hours
        }
        
        $timeIn = Carbon::parse($effectiveSchedule['time_in']);
        $timeOut = Carbon::parse($effectiveSchedule['time_out']);
        
        $totalHours = round($timeIn->diffInMinutes($timeOut) / 60, 2);
        $workingHours = max(0, $totalHours - 1); // Subtract 1 hour for lunch break
        
        return $this->formatHoursToReadable($workingHours);
    }
    
    private function calculateWorkedHours($attendanceRecord, $schedule = null)
    {
        if (!$attendanceRecord || !$attendanceRecord->time_in || !$attendanceRecord->time_out) {
            return '—';
        }
        
       
        $effectiveSchedule = $this->getEffectiveSchedule($schedule, $attendanceRecord);
       
        if (!$effectiveSchedule) {
            $scheduleStatus = $this->getScheduleStatus($schedule);
          
            return $scheduleStatus === 'Day Off' ? '—' : $scheduleStatus;
        }
        
        $timeIn = Carbon::parse($attendanceRecord->time_in);
        $timeOut = Carbon::parse($attendanceRecord->time_out);
        
        $scheduleIn = Carbon::parse($effectiveSchedule['time_in']);
        $scheduleOut = Carbon::parse($effectiveSchedule['time_out']);
        
        // Do not calculate worked hours if time_in > schedule_out (compare only time parts)
            if ($timeIn->format('H:i:s') > $scheduleOut->format('H:i:s')) {
                return '0 hrs'; // Invalid attendance - no worked hours
        }
        
        // Only return 0 hrs if both times are completely outside schedule range (compare only time parts)
            $timeInTime = $timeIn->format('H:i:s');
            $timeOutTime = $timeOut->format('H:i:s');
            $scheduleInTime = $scheduleIn->format('H:i:s');
            $scheduleOutTime = $scheduleOut->format('H:i:s');
            
            // If both actual times are completely outside working range
            if (
                ($timeInTime < $scheduleInTime && $timeOutTime < $scheduleInTime) ||
                ($timeInTime > $scheduleOutTime && $timeOutTime > $scheduleOutTime)
            ) {
                return '0 hrs';
        }
        
        // NEW RULE: Include early time-in minutes in total worked hours
        // Calculate total worked hours using actual time_in and actual time_out
        // Subtract 1 hour for lunch break
        $totalHours = round($timeIn->diffInMinutes($timeOut) / 60, 2);
        $workedHours = max(0, $totalHours - 1); // Ensure non-negative result
        
        return $this->formatHoursToReadable($workedHours);
    }

    private function calculateScheduledHours($attendanceRecord, $schedule = null)
    {
        if (!$attendanceRecord || !$attendanceRecord->time_in || !$attendanceRecord->time_out) {
            return '—';
        }
        
        // Get effective schedule (actual or default)
        $effectiveSchedule = $this->getEffectiveSchedule($schedule, $attendanceRecord);
        
        // If no effective schedule (Holiday/Leave), return appropriate display
        if (!$effectiveSchedule) {
            $scheduleStatus = $this->getScheduleStatus($schedule);
            // Return dash for Day Off, otherwise return the status
            return $scheduleStatus === 'Day Off' ? '—' : $scheduleStatus;
        }
        
        $timeIn = Carbon::parse($attendanceRecord->time_in);
        $timeOut = Carbon::parse($attendanceRecord->time_out);
        
        $scheduleIn = Carbon::parse($effectiveSchedule['time_in']);
        $scheduleOut = Carbon::parse($effectiveSchedule['time_out']);
        
        // Calculate the overlap between actual work time and scheduled time
        $actualStartMinutes = $timeIn->hour * 60 + $timeIn->minute;
        $actualEndMinutes = $timeOut->hour * 60 + $timeOut->minute;
        $scheduleStartMinutes = $scheduleIn->hour * 60 + $scheduleIn->minute;
        $scheduleEndMinutes = $scheduleOut->hour * 60 + $scheduleOut->minute;
        
        // Find the overlap period
        $overlapStartMinutes = max($actualStartMinutes, $scheduleStartMinutes);
        $overlapEndMinutes = min($actualEndMinutes, $scheduleEndMinutes);
        
        // If no overlap, return 0 hrs
        if ($overlapStartMinutes >= $overlapEndMinutes) {
            return '0 hrs';
        }
        
        // Calculate scheduled hours (overlap duration)
        $scheduledMinutes = $overlapEndMinutes - $overlapStartMinutes;
        $scheduledHours = round($scheduledMinutes / 60, 2);
        
        // Subtract 1 hour for lunch break (same as worked hours)
        $scheduledHours = max(0, $scheduledHours - 1);
        
        return $this->formatHoursToReadable($scheduledHours);
    }
    
    /**
     * Convert formatted hours string back to decimal hours for calculations
     * 
     * @param string $formattedHours Formatted hours like "8 hrs 30 mins", "8 hrs", "30 mins", "—", "Holiday"
     * @return float Decimal hours
     */
    private function parseFormattedHours($formattedHours)
    {
        // Handle special cases
        if ($formattedHours === '—' || $formattedHours === 'Regular Holiday' || $formattedHours === 'Special Holiday' || $formattedHours === 'Leave' || $formattedHours === 'Day Off') {
            return 0;
        }
        
        // Parse "X hrs Y mins" format
        if (preg_match('/(\d+)\s*hrs?\s*(\d+)\s*mins?/', $formattedHours, $matches)) {
            $hours = (int)$matches[1];
            $minutes = (int)$matches[2];
            return $hours + ($minutes / 60);
        }
        
        // Parse "X hrs" format (no minutes)
        if (preg_match('/(\d+)\s*hrs?/', $formattedHours, $matches)) {
            return (int)$matches[1];
        }
        
        // Parse "X mins" format (no hours)
        if (preg_match('/(\d+)\s*mins?/', $formattedHours, $matches)) {
            return (int)$matches[1] / 60;
        }
        
        return 0;
    }
    
    /**
     * Calculate summary data from comprehensive data
     */
    private function calculateSummaryDataFromComprehensive($comprehensiveData)
    {
        $summary = [
            'total_employees' => collect($comprehensiveData)->pluck('employee_id')->unique()->count(),
            'total_records' => count($comprehensiveData),
            'present_days' => collect($comprehensiveData)->where('attendance_status', 'Present')->count(),
            'absent_days' => collect($comprehensiveData)->where('attendance_status', 'Absent')->count(),
            'error_days' => collect($comprehensiveData)->where('attendance_status', 'Error')->count(),
            'day_off_days' => collect($comprehensiveData)->where('attendance_status', 'Day Off')->count(),
            'total_worked_hours' => collect($comprehensiveData)->map(function($record) {
                return $this->parseFormattedHours($record['worked_hours']);
            })->sum(),
            'total_scheduled_hours' => collect($comprehensiveData)->map(function($record) {
                return $this->parseFormattedHours($record['scheduled_hours']);
            })->sum(),
            'total_morning_overtime_hours' => collect($comprehensiveData)->sum('morning_overtime'),
            'total_evening_overtime_hours' => collect($comprehensiveData)->sum('evening_overtime'),
            'total_overtime_hours' => collect($comprehensiveData)->sum('overtime'),
            'total_night_differential_hours' => collect($comprehensiveData)->sum('night_differential_hours'),
            'total_late_minutes' => collect($comprehensiveData)->sum('late_minutes'),
            'late_instances' => collect($comprehensiveData)->where('late_minutes', '>', 0)->count(),
            'night_shift_instances' => collect($comprehensiveData)->where('is_night_shift', true)->count(),
            'working_days' => collect($comprehensiveData)->where('schedule_status', 'Working')->count(),
            'regular_holiday_days' => collect($comprehensiveData)->where('schedule_status', 'Regular Holiday')->count(),
            'special_holiday_days' => collect($comprehensiveData)->where('schedule_status', 'Special Holiday')->count(),
            'holiday_days' => collect($comprehensiveData)->whereIn('schedule_status', ['Regular Holiday', 'Special Holiday'])->count(),
            'rest_days' => collect($comprehensiveData)->where('schedule_status', 'Day Off')->count(),
        ];
        
        return $summary;
    }





    /**
     * Preview payroll for a specific period (without saving)
     */
    public function previewPayroll(Request $request, $periodId)
    {
        $user = Auth::user();
        
        // Find the specific period from database
        $period = Period::with('department')->find($periodId);
        
        if (!$period) {
            return redirect()->route('attendance.period-management.index')
                ->with('error', 'Period not found.');
        }
        
        try {
            // Convert to Carbon dates
            $startDate = Carbon::parse($period->start_date);
            $endDate = Carbon::parse($period->end_date);
            
            // Get employees for the period
            $employees = Employee::with('department');
            
            // Apply department filter if specified
            if (!empty($period->department_id)) {
                $employees = $employees->where('department_id', $period->department_id);
            }
            
            // Apply specific employee filter if specified
            if (!empty($period->employee_ids) && is_array($period->employee_ids)) {
                $employees = $employees->whereIn('id', $period->employee_ids);
            }
            
            $employees = $employees->get();
            
            if ($employees->isEmpty()) {
                return redirect()->route('attendance.period-management.show', $periodId)
                    ->with('error', 'No employees found for the specified criteria.');
            }
            
            // Force refresh comprehensive attendance data (clear any potential caching)
            $comprehensiveData = $this->getComprehensiveAttendanceData($startDate, $endDate, $employees);
            
            // Convert period to array format for the service
            $periodData = [
                'id' => $period->id,
                'name' => $period->name,
                'start_date' => $period->start_date->format('Y-m-d'),
                'end_date' => $period->end_date->format('Y-m-d'),
                'department_id' => $period->department_id,
                'employee_ids' => $period->employee_ids,
            ];
            
            // Generate payroll preview (without saving to database)
            $payrollService = new PayrollGenerationService();
            $previewPayrolls = $payrollService->generatePayrollPreview($periodData, $comprehensiveData);
            
            // Add employee information to preview data
            foreach ($previewPayrolls as &$previewPayroll) {
                $employee = $employees->firstWhere('id', $previewPayroll['employee_id']);
                if ($employee) {
                    $previewPayroll['employee_name'] = $employee->full_name;
                    $previewPayroll['employee_code'] = $employee->employee_code;
                    $previewPayroll['department_name'] = $employee->department->name ?? 'N/A';
                }
            }
            
            // Store preview data in session for approval with timestamp
            $generatedAt = now();
            session(['payroll_preview' => [
                'period' => $periodData,
                'payrolls' => $previewPayrolls,
                'generated_at' => $generatedAt,
                'data_refreshed' => true
            ]]);
            
            // Calculate summary statistics
            $summaryData = [
                'total_employees' => count($previewPayrolls),
                'total_basic_salary' => collect($previewPayrolls)->sum('basic_salary'),
                'total_holiday_basic_pay' => collect($previewPayrolls)->sum('holiday_basic_pay'),
                'total_holiday_premium' => collect($previewPayrolls)->sum('holiday_premium'),
                'total_special_holiday_premium' => collect($previewPayrolls)->sum('special_holiday_premium'),
                'total_overtime_pay' => collect($previewPayrolls)->sum(function($p) { return $p['overtime_hours'] * $p['overtime_rate']; }),
                'total_bonuses' => collect($previewPayrolls)->sum('bonuses'),
                'total_deductions' => collect($previewPayrolls)->sum('deductions'),
                'total_tax' => collect($previewPayrolls)->sum('tax_amount'),
                'total_gross_pay' => collect($previewPayrolls)->sum('gross_pay'),
                'total_net_pay' => collect($previewPayrolls)->sum('net_pay'),
            ];
            
            // Add success message to indicate data was refreshed
            $request->session()->flash('success', 'Payroll preview generated successfully with fresh data from ' . $generatedAt->format('M d, Y H:i:s'));
            
            return view('attendance.period-management.payroll-preview', compact('period', 'previewPayrolls', 'summaryData', 'generatedAt', 'user'));
            
        } catch (\Exception $e) {
            Log::error('Payroll preview generation failed: ' . $e->getMessage());
            return redirect()->route('attendance.period-management.show', $periodId)
                ->with('error', 'Failed to generate payroll preview: ' . $e->getMessage());
        }
    }

    /**
     * Generate payroll for a specific period
     */
    public function generatePayroll(Request $request, $periodId)
    {
        $user = Auth::user();
        
        // Get preview data from session
        $previewData = session('payroll_preview');
        
        if (!$previewData || $previewData['period']['id'] != $periodId) {
            return redirect()->route('attendance.period-management.show', $periodId)
                ->with('error', 'No payroll preview found. Please generate preview first.');
        }
        
        try {
            $period = $previewData['period'];
            $previewPayrolls = $previewData['payrolls'];
            
            // Convert preview data to actual payroll records
            $generatedPayrolls = [];
            foreach ($previewPayrolls as $previewPayroll) {
                $payroll = Payroll::create([
                    'employee_id' => $previewPayroll['employee_id'],
                    'pay_period_start' => $previewPayroll['pay_period_start'],
                    'pay_period_end' => $previewPayroll['pay_period_end'],
                    'basic_salary' => $previewPayroll['basic_salary'],
                    'holiday_basic_pay' => $previewPayroll['holiday_basic_pay'],
                    'holiday_premium' => $previewPayroll['holiday_premium'],
                    'special_holiday_premium' => $previewPayroll['special_holiday_premium'],
                    'overtime_hours' => $previewPayroll['overtime_hours'],
                    'overtime_rate' => $previewPayroll['overtime_rate'],
                    'scheduled_hours' => $previewPayroll['basic_salary_details']['total_scheduled_hours'] ?? 0,
                    'bonuses' => $previewPayroll['bonuses'],
                    'deductions' => $previewPayroll['deductions'],
                    'tax_amount' => $previewPayroll['tax_amount'],
                    'gross_pay' => $previewPayroll['gross_pay'],
                    'net_pay' => $previewPayroll['net_pay'],
                    'status' => 'pending',
                ]);
                
                $generatedPayrolls[] = $payroll;
            }
            
            // Clear preview data from session
            session()->forget('payroll_preview');
            
            $message = count($generatedPayrolls) . ' payroll record(s) generated successfully.';
            
            return redirect()->route('attendance.period-management.show', $periodId)
                ->with('success', $message);
                
        } catch (\Exception $e) {
            return redirect()->route('attendance.period-management.show', $periodId)
                ->with('error', 'Failed to generate payroll: ' . $e->getMessage());
        }
    }

    /**
     * Show payroll summary for a specific period
     */
    public function showPayrollSummary(Request $request, $periodId)
    {
        $user = Auth::user();
        
        // Find the specific period from database
        $period = Period::with('department')->find($periodId);
        
        if (!$period) {
            return redirect()->route('attendance.period-management.index')
                ->with('error', 'Period not found.');
        }
        
        // Convert to Carbon dates
        $startDate = Carbon::parse($period->start_date);
        $endDate = Carbon::parse($period->end_date);
        
        // Get payroll records for this period
        $payrolls = Payroll::where('pay_period_start', $startDate->format('Y-m-d'))
            ->where('pay_period_end', $endDate->format('Y-m-d'))
            ->with('employee.department')
            ->get();
        
        // Calculate summary statistics
        $summaryData = [
            'total_employees' => $payrolls->count(),
            'total_basic_salary' => $payrolls->sum('basic_salary'),
            'total_overtime_hours' => $payrolls->sum('overtime_hours'),
            'total_overtime_pay' => $payrolls->sum('overtime_hours') * $payrolls->avg('overtime_rate'),
            'total_bonuses' => $payrolls->sum('bonuses'),
            'total_deductions' => $payrolls->sum('deductions'),
            'total_tax' => $payrolls->sum('tax_amount'),
            'total_gross_pay' => $payrolls->sum('gross_pay'),
            'total_net_pay' => $payrolls->sum('net_pay'),
        ];
        
        // Get current filter state for back navigation
        $currentFilters = $this->getFilterState($request);
        
        return view('attendance.period-management.payroll-summary', compact(
            'period', 
            'user', 
            'currentFilters', 
            'payrolls',
            'summaryData'
        ));
    }

    /**
     * Export payroll to CSV
     */
    public function exportPayroll(Request $request, $periodId)
    {
        $user = Auth::user();
        
        // Find the specific period from database
        $period = Period::with('department')->find($periodId);
        
        if (!$period) {
            return redirect()->route('attendance.period-management.index')
                ->with('error', 'Period not found.');
        }
        
        // Convert to Carbon dates
        $startDate = Carbon::parse($period->start_date);
        $endDate = Carbon::parse($period->end_date);
        
        // Get payroll records for this period
        $payrolls = Payroll::where('pay_period_start', $startDate->format('Y-m-d'))
            ->where('pay_period_end', $endDate->format('Y-m-d'))
            ->with('employee.department')
            ->get();
        
        $filename = 'payroll_' . $period->name . '_' . $startDate->format('Y-m-d') . '_to_' . $endDate->format('Y-m-d') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];
        
        $callback = function() use ($payrolls) {
            $file = fopen('php://output', 'w');
            
            // CSV headers
            fputcsv($file, [
                'Employee Code',
                'Employee Name',
                'Department',
                'Basic Salary',
                'Scheduled Hours',
                'Overtime Hours',
                'Overtime Rate',
                'Overtime Pay',
                'Bonuses',
                'Deductions',
                'Tax Amount',
                'Gross Pay',
                'Net Pay',
                'Status'
            ]);
            
            // CSV data
            foreach ($payrolls as $payroll) {
                fputcsv($file, [
                    $payroll->employee->employee_id,
                    $payroll->employee->full_name,
                    $payroll->employee->department->name ?? 'N/A',
                    number_format($payroll->basic_salary + $payroll->holiday_basic_pay, 2),
                    number_format($payroll->scheduled_hours, 2),
                    number_format($payroll->overtime_hours, 2),
                    number_format($payroll->overtime_rate, 2),
                    number_format($payroll->overtime_hours * $payroll->overtime_rate, 2),
                    number_format($payroll->bonuses, 2),
                    number_format($payroll->deductions, 2),
                    number_format($payroll->tax_amount, 2),
                    number_format($payroll->gross_pay, 2),
                    number_format($payroll->net_pay, 2),
                    $payroll->status
                ]);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }

    /**
     * Get current filter state from request
     */
    private function getFilterState(Request $request)
    {
        return array_filter([
            'search' => $request->get('search'),

            'department_id' => $request->get('department_id'),
        ]);
    }
}



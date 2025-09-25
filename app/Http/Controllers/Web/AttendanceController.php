<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\AttendanceRecord;
use App\Models\Employee;
use App\Models\AttendanceSetting;
use App\Models\AttendanceException;
use App\Helpers\TimezoneHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    /**
     * Display daily attendance page
     */
    public function daily(Request $request)
    {
        $date = $request->get('date', today()->format('Y-m-d'));
        $date = Carbon::parse($date);
        
        // Paginate employees
        $employees = Employee::with(['department', 'account'])
            ->whereHas('account', function($query) {
                $query->where('is_active', true);
            })
            ->orderBy('first_name')
            ->paginate(10); // 10 employees per page

        // Get all attendance records for the date (for summary calculation)
        $allAttendanceRecords = AttendanceRecord::with(['employee.department'])
            ->where('date', $date)
            ->get()
            ->keyBy('employee_id');

        // Get attendance records for current page employees only
        $employeeIds = $employees->pluck('id');
        $attendanceRecords = AttendanceRecord::with(['employee.department'])
            ->where('date', $date)
            ->whereIn('employee_id', $employeeIds)
            ->get()
            ->keyBy('employee_id');

        // Calculate summary statistics using all records
        $summary = $this->calculateDailySummary($allAttendanceRecords);

        $user = Auth::user();
        
        return view('attendance.daily', compact('employees', 'attendanceRecords', 'summary', 'date', 'user'));
    }

    /**
     * Display timekeeping page
     */
    public function timekeeping(Request $request)
    {
        $query = AttendanceRecord::with(['employee.department', 'employee.account']);

        // Apply filters
        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        if ($request->filled('department_id')) {
            $query->whereHas('employee', function($q) use ($request) {
                $q->where('department_id', $request->department_id);
            });
        }

        if ($request->filled('date_from')) {
            $query->where('date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('date', '<=', $request->date_to);
        }

        $attendanceRecords = $query->orderBy('date', 'desc')
            ->orderBy('time_in', 'desc')
            ->paginate(20);

        // Calculate summary statistics (recreate query to avoid pagination issues)
        $summaryQuery = AttendanceRecord::with(['employee.department', 'employee.account']);
        
        // Apply same filters for summary
        if ($request->filled('employee_id')) {
            $summaryQuery->where('employee_id', $request->employee_id);
        }
        if ($request->filled('department_id')) {
            $summaryQuery->whereHas('employee', function($q) use ($request) {
                $q->where('department_id', $request->department_id);
            });
        }
        if ($request->filled('date_from')) {
            $summaryQuery->where('date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $summaryQuery->where('date', '<=', $request->date_to);
        }
        
        $allRecords = $summaryQuery->get();
        $summary = $this->calculateTimekeepingSummary($allRecords);

        $employees = Employee::with('department')->get();
        $departments = \App\Models\Department::all();
        $user = Auth::user();

        return view('attendance.timekeeping', compact('attendanceRecords', 'employees', 'departments', 'summary', 'user'));
    }

    /**
     * Show the form for creating a new attendance record
     */
    public function createRecord()
    {
        $employees = Employee::with('department')->get();
        $user = Auth::user();
        
        return view('attendance.create-record', compact('employees', 'user'));
    }

    /**
     * Store a newly created attendance record
     */
    public function storeRecord(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'date' => 'required|date',
            'time_in' => 'required|date_format:H:i',
            'time_out' => 'nullable|date_format:H:i|after:time_in',
            'break_start' => 'nullable|date_format:H:i',
            'break_end' => 'nullable|date_format:H:i|after:break_start',
            'status' => 'required|in:present,absent,late,half_day',
            'notes' => 'nullable|string|max:500'
        ]);

        // Check if record already exists for this employee and date
        $existingRecord = AttendanceRecord::where('employee_id', $request->employee_id)
            ->where('date', $request->date)
            ->first();

        if ($existingRecord) {
            return redirect()->back()->with('error', 'An attendance record already exists for this employee on this date.');
        }

        // Calculate total hours if time_out is provided
        $totalHours = 0;
        $breakDuration = 0;
        $regularHours = 0;
        $overtimeHours = 0;
        
        if ($request->time_out) {
            $timeIn = \Carbon\Carbon::createFromFormat('Y-m-d H:i', $request->date . ' ' . $request->time_in);
            $timeOut = \Carbon\Carbon::createFromFormat('Y-m-d H:i', $request->date . ' ' . $request->time_out);
            
            
            // Calculate break duration if break_start and break_end are provided
            if ($request->break_start && $request->break_end) {
                $breakStart = \Carbon\Carbon::createFromFormat('Y-m-d H:i', $request->date . ' ' . $request->break_start);
                $breakEnd = \Carbon\Carbon::createFromFormat('Y-m-d H:i', $request->date . ' ' . $request->break_end);
                $breakMinutes = $breakEnd->diffInMinutes($breakStart);
                $breakDuration = $breakMinutes / 60;
            } else {
                $breakDuration = 0; // No break entered
            }
            
            // Business Rules:
            // Standard work day: 8 AM to 5 PM (9 hours total)
            // Break time: 1 hour (lunch break)
            // Regular hours: 8 hours (9 hours - 1 hour break)
            // Overtime starts: After 5:30 PM (8 AM + 8 regular hours + 1 hour break = 5 PM, so overtime starts at 5:30 PM)
            
            // Calculate total working time (excluding break)
            $totalMinutes = $timeIn->diffInMinutes($timeOut);
            $totalHours = $totalMinutes / 60;
            $totalHours = $totalHours - $breakDuration; // Subtract break time
            
            
            // Define standard work schedule
            $standardStart = \Carbon\Carbon::createFromFormat('Y-m-d H:i', $request->date . ' 08:00');
            $standardEnd = \Carbon\Carbon::createFromFormat('Y-m-d H:i', $request->date . ' 17:00');
            $overtimeStart = \Carbon\Carbon::createFromFormat('Y-m-d H:i', $request->date . ' 17:30');
            
            // Calculate regular and overtime hours based on business rules
            if ($timeOut <= $standardEnd) {
                // Worked within standard hours (8 AM - 5 PM)
                $regularHours = $totalHours;
                $overtimeHours = 0;
            } elseif ($timeOut <= $overtimeStart) {
                // Worked until 5:30 PM (no overtime yet)
                $regularHours = $totalHours;
                $overtimeHours = 0;
            } else {
                // Worked beyond 5:30 PM (overtime applies)
                // Calculate regular hours: from time in to 5:30 PM, minus break time
                $regularMinutes = $timeIn->diffInMinutes($overtimeStart);
                $regularHours = ($regularMinutes / 60) - $breakDuration;
                
                // Calculate overtime hours: from 5:30 PM to time out
                $overtimeMinutes = $overtimeStart->diffInMinutes($timeOut);
                $overtimeHours = $overtimeMinutes / 60;
                
                // Ensure regular hours don't exceed 8 hours
                $regularHours = min($regularHours, 8);
            }
            
            // Ensure non-negative values
            $totalHours = max(0, $totalHours);
            $regularHours = max(0, $regularHours);
            $overtimeHours = max(0, $overtimeHours);
            
            
        }

        $attendanceRecord = AttendanceRecord::create([
            'employee_id' => $request->employee_id,
            'date' => $request->date,
            'time_in' => $request->date . ' ' . $request->time_in,
            'time_out' => $request->time_out ? $request->date . ' ' . $request->time_out : null,
            'break_start' => $request->break_start ? $request->date . ' ' . $request->break_start : null,
            'break_end' => $request->break_end ? $request->date . ' ' . $request->break_end : null,
            'total_hours' => $totalHours,
            'regular_hours' => $regularHours,
            'overtime_hours' => $overtimeHours,
            'status' => $request->status,
            'notes' => $request->notes,
        ]);

        // Log the attendance action
        $this->logAttendanceAction($attendanceRecord, 'manual_entry', 'Attendance record manually created');

        return redirect()->route('attendance.timekeeping')->with('success', 'Attendance record created successfully.');
    }

    /**
     * Display schedule page
     */
    public function schedule(Request $request)
    {
        $week = $request->get('week', now()->format('Y-\WW'));
        $weekStart = Carbon::parse($week . '1'); // Start of week (Monday)
        $weekEnd = $weekStart->copy()->addDays(6);

        $employees = Employee::with(['workSchedules' => function($query) use ($weekStart, $weekEnd) {
            $query->where('is_active', true)
                  ->where('effective_date', '<=', $weekEnd)
                  ->where(function($q) use ($weekStart) {
                      $q->whereNull('end_date')
                        ->orWhere('end_date', '>=', $weekStart);
                  });
        }])->get();

        $user = Auth::user();

        return view('attendance.schedule', compact('employees', 'weekStart', 'weekEnd', 'user'));
    }

    /**
     * Calculate daily attendance summary
     */
    private function calculateDailySummary($attendanceRecords)
    {
        $total = $attendanceRecords->count();
        $present = $attendanceRecords->where('status', 'present')->count();
        $absent = $attendanceRecords->where('status', 'absent')->count();
        $late = $attendanceRecords->where('status', 'late')->count();
        $halfDay = $attendanceRecords->where('status', 'half_day')->count();

        $attendanceRate = $total > 0 ? round(($present + $late + $halfDay) / $total * 100, 1) : 0;

        return [
            'total' => $total,
            'present' => $present,
            'absent' => $absent,
            'late' => $late,
            'half_day' => $halfDay,
            'attendance_rate' => $attendanceRate,
        ];
    }

    /**
     * Calculate timekeeping summary
     */
    private function calculateTimekeepingSummary($attendanceRecords)
    {
        $totalHours = $attendanceRecords->sum('total_hours');
        $regularHours = $attendanceRecords->where('total_hours', '<=', 8)->sum('total_hours');
        $overtimeHours = $attendanceRecords->where('total_hours', '>', 8)->sum(function($record) {
            return max(0, $record->total_hours - 8);
        });
        
        $totalRecords = $attendanceRecords->count();
        $averageHours = $totalRecords > 0 ? round($totalHours / $totalRecords, 1) : 0;

        return [
            'total_hours' => $totalHours,
            'regular_hours' => $regularHours,
            'overtime_hours' => $overtimeHours,
            'average_hours' => $averageHours,
            'total_records' => $totalRecords,
        ];
    }

    /**
     * Get attendance statistics for dashboard
     */
    public function getStatistics(Request $request)
    {
        $date = $request->get('date', today());
        $date = Carbon::parse($date);

        $stats = [
            'today' => $this->getTodayStats($date),
            'this_week' => $this->getWeekStats($date),
            'this_month' => $this->getMonthStats($date),
        ];

        return response()->json($stats);
    }

    private function getTodayStats($date)
    {
        $records = AttendanceRecord::where('date', $date)->get();
        
        return [
            'total_employees' => Employee::whereHas('account', function($q) {
                $q->where('is_active', true);
            })->count(),
            'present' => $records->where('status', 'present')->count(),
            'absent' => $records->where('status', 'absent')->count(),
            'late' => $records->where('status', 'late')->count(),
            'total_hours' => $records->sum('total_hours'),
            'overtime_hours' => $records->sum('overtime_hours'),
        ];
    }

    private function getWeekStats($date)
    {
        $weekStart = $date->copy()->startOfWeek();
        $weekEnd = $date->copy()->endOfWeek();

        $records = AttendanceRecord::whereBetween('date', [$weekStart, $weekEnd])->get();
        
        return [
            'total_hours' => $records->sum('total_hours'),
            'overtime_hours' => $records->sum('overtime_hours'),
            'average_daily_hours' => $records->avg('total_hours') ?? 0,
        ];
    }

    private function getMonthStats($date)
    {
        $monthStart = $date->copy()->startOfMonth();
        $monthEnd = $date->copy()->endOfMonth();

        $records = AttendanceRecord::whereBetween('date', [$monthStart, $monthEnd])->get();
        
        return [
            'total_hours' => $records->sum('total_hours'),
            'overtime_hours' => $records->sum('overtime_hours'),
            'working_days' => $records->where('status', '!=', 'absent')->count(),
        ];
    }

    /**
     * Log attendance action for audit trail
     */
    private function logAttendanceAction($attendanceRecord, $action, $description = null)
    {
        try {
            \App\Models\AttendanceLog::create([
                'attendance_record_id' => $attendanceRecord->id,
                'action' => $action,
                'performed_by' => auth()->id(),
                'performed_at' => now(),
                'reason' => $description,
            ]);
        } catch (\Exception $e) {
            // Log the error but don't fail the main operation
            \Log::error('Failed to log attendance action: ' . $e->getMessage());
        }
    }
}

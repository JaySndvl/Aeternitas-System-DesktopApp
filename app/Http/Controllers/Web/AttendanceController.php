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

        return view('attendance.schedule-v2.index', compact('employees', 'weekStart', 'weekEnd', 'user'));
    }

    /**
     * Display schedule reports page
     */
    public function scheduleReports(Request $request)
    {
        $user = Auth::user();
        
        // Get report data based on filters
        $reportType = $request->get('report_type', 'weekly');
        $employeeId = $request->get('employee_id');
        $departmentId = $request->get('department_id');
        $dateRange = $request->get('date_range');
        
        // Sample data for now - replace with actual data fetching
        $employees = Employee::with('department')->get();
        $departments = \App\Models\Department::all();
        
        return view('attendance.schedule.reports', compact('user', 'employees', 'departments', 'reportType', 'employeeId', 'departmentId', 'dateRange'));
    }

    /**
     * Display schedule templates page
     */
    public function scheduleTemplates(Request $request)
    {
        $user = Auth::user();
        
        // Get template data based on filters
        $templateType = $request->get('template_type');
        $departmentId = $request->get('department_id');
        $status = $request->get('status');
        
        // Sample data for now - replace with actual data fetching
        $employees = Employee::with('department')->get();
        $departments = \App\Models\Department::all();
        
        return view('attendance.schedule.templates', compact('user', 'employees', 'departments', 'templateType', 'departmentId', 'status'));
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
     * Show the import DTR page
     */
    public function importDtr()
    {
        $user = Auth::user();
        
        // Get recent temp timekeeping imports (last 10 batches)
        $recentImports = \App\Models\TempTimekeeping::select('import_batch_id')
            ->selectRaw('MIN(created_at) as created_at')
            ->groupBy('import_batch_id')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($item) {
                $batchRecords = \App\Models\TempTimekeeping::where('import_batch_id', $item->import_batch_id)->get();
                return [
                    'batch_id' => $item->import_batch_id,
                    'created_at' => $item->created_at,
                    'total_records' => $batchRecords->count(),
                    'processed_records' => $batchRecords->where('is_processed', true)->count(),
                    'pending_records' => $batchRecords->where('is_processed', false)->count(),
                    'employees' => $batchRecords->pluck('employee_id')->unique()->count(),
                    'date_range' => [
                        'start' => $batchRecords->min('date'),
                        'end' => $batchRecords->max('date')
                    ]
                ];
            });
        
        return view('attendance.import-dtr', compact('user', 'recentImports'));
    }

    /**
     * Process the imported DTR file
     */
    public function processImportDtr(Request $request)
    {
        $request->validate([
            'dtr_file' => 'required|file|mimes:xlsx,xls,csv|max:10240', // 10MB max
        ]);

        try {
            $file = $request->file('dtr_file');
            
            // Create a unique filename
            $fileName = 'dtr_' . time() . '_' . $file->getClientOriginalName();
            $tempPath = storage_path('app' . DIRECTORY_SEPARATOR . 'temp' . DIRECTORY_SEPARATOR . $fileName);
            
            // Move the uploaded file to temp directory
            $file->move(storage_path('app' . DIRECTORY_SEPARATOR . 'temp'), $fileName);
            
            // Debug: Log the file path
            \Log::info('DTR File Path: ' . $tempPath);
            \Log::info('File exists: ' . (file_exists($tempPath) ? 'Yes' : 'No'));
            
            // Check if file exists
            if (!file_exists($tempPath)) {
                throw new \Exception('Uploaded file not found at: ' . $tempPath);
            }
            
            $fullPath = $tempPath;
            
            // Parse the DTR data
            $dtrService = new \App\Services\DtrImportService();
            $parsedData = $dtrService->parseDtrData($fullPath);
            
            // Debug: Log parsed data
            \Log::info('Parsed Data Count: ' . $parsedData->count());
            \Log::info('Parsed Data: ' . json_encode($parsedData->toArray()));
            
            // Validate the parsed data
            $validation = $dtrService->validateParsedData($parsedData);
            
            // Store data in session for review
            session([
                'dtr_import_data' => $parsedData->toArray(),
                'dtr_import_validation' => $validation,
                'dtr_import_file' => $file->getClientOriginalName()
            ]);
            
            return redirect()->route('attendance.import-dtr.review');
            
        } catch (\Exception $e) {
            \Log::error('DTR Processing Error: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Failed to process DTR file: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Show the DTR import review page
     */
    public function reviewImportDtr()
    {
        $user = Auth::user();
        $parsedDataArray = session('dtr_import_data', []);
        $parsedData = collect($parsedDataArray);
        $validation = session('dtr_import_validation', ['errors' => collect(), 'warnings' => collect(), 'is_valid' => false]);
        $fileName = session('dtr_import_file', 'Unknown file');
        
        if (empty($parsedDataArray) || $parsedData->isEmpty()) {
            return redirect()->route('attendance.import-dtr')
                ->with('error', 'No import data found. Please upload a file first.');
        }
        
        return view('attendance.import-dtr-review', compact('user', 'parsedData', 'validation', 'fileName'));
    }

    /**
     * Confirm and import the DTR data
     */
    public function confirmImportDtr(Request $request)
    {
        $parsedDataArray = session('dtr_import_data', []);
        $parsedData = collect($parsedDataArray);
        $validation = session('dtr_import_validation', ['errors' => collect(), 'warnings' => collect(), 'is_valid' => false]);
        
        if (empty($parsedDataArray) || $parsedData->isEmpty()) {
            return redirect()->route('attendance.import-dtr')
                ->with('error', 'No import data found. Please upload a file first.');
        }
        
        try {
            // Generate a unique batch ID for this import
            $batchId = \App\Models\TempTimekeeping::generateBatchId();
            $importedCount = 0;
            $errors = collect();
            
            // Debug: Log the start of the process
            \Log::info('Starting temp timekeeping import process', [
                'batch_id' => $batchId,
                'record_count' => $parsedData->count()
            ]);
            
            foreach ($parsedData as $record) {
                // Prepare validation errors for this record
                $recordErrors = [];
                $employee = \App\Models\Employee::where('employee_id', $record['employee_id'])->first();
                
                if (!$employee) {
                    $recordErrors[] = "Employee ID '{$record['employee_id']}' not found in system";
                }
                
                // Check for duplicate records in temp table
                $existingTempRecord = \App\Models\TempTimekeeping::where('employee_id', $record['employee_id'])
                    ->where('date', $record['date'])
                    ->where('import_batch_id', $batchId)
                    ->first();
                
                if ($existingTempRecord) {
                    $recordErrors[] = "Duplicate record for employee {$record['employee_id']} on {$record['date']}";
                }
                
                    // Create temp timekeeping record
                    try {
                        // Auto-process day off records
                        $isProcessed = ($record['status'] === 'day_off') ? true : false;
                        
                        $tempRecord = \App\Models\TempTimekeeping::create([
                            'employee_id' => $record['employee_id'],
                            'employee_name' => $record['employee_name'] ?? null,
                    'date' => $record['date'],
                    'time_in' => $record['time_in'],
                    'time_out' => $record['time_out'],
                            'break_start' => $record['break_start'] ?? null,
                            'break_end' => $record['break_end'] ?? null,
                            'total_hours' => $record['total_hours'] ?? 0,
                            'regular_hours' => $record['regular_hours'] ?? 0,
                            'overtime_hours' => $record['overtime_hours'] ?? 0,
                    'status' => $record['status'],
                            'schedule_status' => $record['schedule_status'] ?? null,
                            'notes' => $record['notes'] ?? null,
                            'validation_errors' => !empty($recordErrors) ? json_encode($recordErrors) : null,
                            'import_batch_id' => $batchId,
                            'is_processed' => $isProcessed
                        ]);
                    
                        \Log::info('Successfully created temp record', [
                            'record_id' => $tempRecord->id,
                            'employee_id' => $record['employee_id'],
                            'date' => $record['date']
                        ]);

                        // Auto-create attendance record for day off
                        if ($record['status'] === 'day_off' && $employee) {
                            try {
                                // Check for existing attendance record
                                $existingAttendanceRecord = \App\Models\AttendanceRecord::where('employee_id', $employee->id)
                                    ->where('date', $record['date'])
                                    ->first();

                                if (!$existingAttendanceRecord) {
                                    \App\Models\AttendanceRecord::create([
                                        'employee_id' => $employee->id,
                                        'date' => $record['date'],
                                        'time_in' => null,
                                        'time_out' => null,
                                        'break_start' => null,
                                        'break_end' => null,
                                        'total_hours' => 0,
                                        'regular_hours' => 0,
                                        'overtime_hours' => 0,
                                        'status' => 'day_off',
                                        'notes' => 'Auto-processed day off',
                                        'is_night_shift' => false
                                    ]);

                                    \Log::info('Auto-created attendance record for day off', [
                                        'employee_id' => $record['employee_id'],
                                        'date' => $record['date']
                                    ]);
                                }
                            } catch (\Exception $e) {
                                \Log::error('Failed to auto-create attendance record for day off', [
                                    'employee_id' => $record['employee_id'],
                                    'date' => $record['date'],
                                    'error' => $e->getMessage()
                                ]);
                            }
                        }
                
                $importedCount++;
                } catch (\Exception $e) {
                    \Log::error('Failed to create temp record', [
                        'employee_id' => $record['employee_id'],
                        'date' => $record['date'],
                        'error' => $e->getMessage()
                    ]);
                    $errors->push("Failed to save record for {$record['employee_id']} on {$record['date']}: " . $e->getMessage());
                }
            }
            
            // Clear session data
            session()->forget(['dtr_import_data', 'dtr_import_validation', 'dtr_import_file']);
            
            \Log::info('Temp timekeeping import completed', [
                'batch_id' => $batchId,
                'imported_count' => $importedCount,
                'error_count' => $errors->count()
            ]);
            
            return redirect()->route('attendance.timekeeping')
                ->with('success', "Successfully saved {$importedCount} records to temporary storage. Records are ready for final processing.");
                
        } catch (\Exception $e) {
            return redirect()->route('attendance.import-dtr.review')
                ->with('error', 'Failed to save data to temporary storage: ' . $e->getMessage());
        }
    }

    /**
     * Show temporary timekeeping records
     */
    public function tempTimekeeping(Request $request)
    {
        $user = Auth::user();
        
        $query = \App\Models\TempTimekeeping::with('employee');
        
        // Filter by batch ID if provided
        if ($request->has('batch') && !empty($request->batch)) {
            $query->where('import_batch_id', $request->batch);
        }
        
        // Get all records first
        $allRecords = $query->orderBy('created_at', 'desc')->get();
        
        // Group by employee and get unique employees
        $allGroupedRecords = $allRecords->groupBy('employee_id');
        $uniqueEmployees = $allGroupedRecords->keys();
        
        // Implement employee-based pagination (10 employees per page)
        $employeesPerPage = 10;
        $currentPage = $request->get('page', 1);
        $offset = ($currentPage - 1) * $employeesPerPage;
        $paginatedEmployees = $uniqueEmployees->slice($offset, $employeesPerPage);
        
        // Get records for paginated employees
        $groupedRecords = collect();
        foreach ($paginatedEmployees as $employeeId) {
            $employeeRecords = $allGroupedRecords[$employeeId];
            $firstRecord = $employeeRecords->first();
            $groupedRecords[$employeeId] = [
                'employee_id' => $firstRecord->employee_id,
                'employee_name' => $firstRecord->employee_name,
                'records' => $employeeRecords->sortBy('date'),
                'total_records' => $employeeRecords->count(),
                'processed_records' => $employeeRecords->where('is_processed', true)->count(),
                'pending_records' => $employeeRecords->where('is_processed', false)->count(),
                'date_range' => [
                    'start' => $employeeRecords->min('date'),
                    'end' => $employeeRecords->max('date')
                ],
                'status_summary' => $employeeRecords->groupBy('status')->map->count()
            ];
        }
        
        // Create pagination info
        $totalEmployees = $uniqueEmployees->count();
        $totalPages = ceil($totalEmployees / $employeesPerPage);
        $hasNextPage = $currentPage < $totalPages;
        $hasPrevPage = $currentPage > 1;
        
        $paginationInfo = [
            'current_page' => $currentPage,
            'total_pages' => $totalPages,
            'total_employees' => $totalEmployees,
            'employees_per_page' => $employeesPerPage,
            'has_next_page' => $hasNextPage,
            'has_prev_page' => $hasPrevPage,
            'next_page' => $hasNextPage ? $currentPage + 1 : null,
            'prev_page' => $hasPrevPage ? $currentPage - 1 : null
        ];
        
        // Get batch information if filtering by batch
        $batchInfo = null;
        if ($request->has('batch') && !empty($request->batch)) {
            $batchRecords = \App\Models\TempTimekeeping::where('import_batch_id', $request->batch)->get();
            $batchInfo = [
                'batch_id' => $request->batch,
                'total_records' => $batchRecords->count(),
                'processed_records' => $batchRecords->where('is_processed', true)->count(),
                'pending_records' => $batchRecords->where('is_processed', false)->count(),
                'employees' => $batchRecords->pluck('employee_id')->unique()->count(),
                'date_range' => [
                    'start' => $batchRecords->min('date'),
                    'end' => $batchRecords->max('date')
                ],
                'created_at' => $batchRecords->first()?->created_at
            ];
        }
        
        return view('attendance.temp-timekeeping', compact('user', 'allRecords', 'groupedRecords', 'batchInfo', 'paginationInfo'));
    }

    /**
     * Approve selected temp timekeeping records and save to attendance_records
     */
    public function approveTempTimekeeping(Request $request)
    {
        $request->validate([
            'selected_records' => 'required|array|min:1',
            'selected_records.*' => 'required|string|exists:temp_timekeeping,id'
        ]);

        try {
            $selectedRecordIds = $request->selected_records;
            $approvedCount = 0;
            $errors = collect();

            \Log::info('Starting temp timekeeping approval process', [
                'selected_count' => count($selectedRecordIds),
                'record_ids' => $selectedRecordIds
            ]);

            foreach ($selectedRecordIds as $recordId) {
                $tempRecord = \App\Models\TempTimekeeping::find($recordId);
                
                if (!$tempRecord) {
                    $errors->push("Record with ID {$recordId} not found");
                    continue;
                }

                if ($tempRecord->is_processed) {
                    $errors->push("Record for employee {$tempRecord->employee_id} on {$tempRecord->date} is already processed");
                    continue;
                }

                // Get or create employee
                $employee = \App\Models\Employee::where('employee_id', $tempRecord->employee_id)->first();
                if (!$employee) {
                    $errors->push("Employee {$tempRecord->employee_id} not found in system");
                    continue;
                }

                // Check for existing attendance record
                $existingRecord = \App\Models\AttendanceRecord::where('employee_id', $employee->id)
                    ->where('date', $tempRecord->date)
                    ->first();

                if ($existingRecord) {
                    $errors->push("Attendance record already exists for employee {$tempRecord->employee_id} on {$tempRecord->date}");
                    continue;
                }

                // Create attendance record
                try {
                    // Set default break times (12pm-1pm) if employee has both time in and time out
                    $breakStart = $tempRecord->break_start;
                    $breakEnd = $tempRecord->break_end;
                    
                    if ($tempRecord->time_in && $tempRecord->time_out && !$breakStart && !$breakEnd) {
                        // Set default break time to 12:00 PM - 1:00 PM
                        $breakStart = \Carbon\Carbon::parse($tempRecord->date)->setTime(12, 0, 0);
                        $breakEnd = \Carbon\Carbon::parse($tempRecord->date)->setTime(13, 0, 0);
                    }
                    
                    $attendanceRecord = \App\Models\AttendanceRecord::create([
                        'employee_id' => $employee->id,
                        'date' => $tempRecord->date,
                        'time_in' => $tempRecord->time_in,
                        'time_out' => $tempRecord->time_out,
                        'break_start' => $breakStart,
                        'break_end' => $breakEnd,
                        'total_hours' => $tempRecord->total_hours,
                        'regular_hours' => $tempRecord->regular_hours,
                        'overtime_hours' => $tempRecord->overtime_hours,
                        'status' => $tempRecord->status,
                        'notes' => $tempRecord->notes,
                        'is_night_shift' => false // Default value
                    ]);

                    // Mark temp record as processed
                    $tempRecord->update(['is_processed' => true]);

                    \Log::info('Successfully approved temp record', [
                        'temp_record_id' => $tempRecord->id,
                        'attendance_record_id' => $attendanceRecord->id,
                        'employee_id' => $tempRecord->employee_id,
                        'date' => $tempRecord->date
                    ]);

                    $approvedCount++;

                } catch (\Exception $e) {
                    \Log::error('Failed to create attendance record', [
                        'temp_record_id' => $tempRecord->id,
                        'employee_id' => $tempRecord->employee_id,
                        'date' => $tempRecord->date,
                        'error' => $e->getMessage()
                    ]);
                    $errors->push("Failed to create attendance record for {$tempRecord->employee_id} on {$tempRecord->date}: " . $e->getMessage());
                }
            }

            \Log::info('Temp timekeeping approval completed', [
                'approved_count' => $approvedCount,
                'error_count' => $errors->count()
            ]);

            if ($approvedCount > 0) {
                $message = "Successfully approved {$approvedCount} records and saved to attendance records.";
                if ($errors->count() > 0) {
                    $message .= " {$errors->count()} records had errors and were not processed.";
                }
                return redirect()->route('attendance.temp-timekeeping')->with('success', $message);
            } else {
                return redirect()->route('attendance.temp-timekeeping')->with('error', 'No records were approved. ' . $errors->implode(', '));
            }

        } catch (\Exception $e) {
            \Log::error('Temp timekeeping approval failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->route('attendance.temp-timekeeping')->with('error', 'Failed to approve records: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for editing an attendance record
     */
    public function editRecord($id)
    {
        $attendanceRecord = AttendanceRecord::with(['employee.department'])->findOrFail($id);
        $employees = Employee::with('department')->get();
        $user = Auth::user();
        
        return view('attendance.edit-record', compact('attendanceRecord', 'employees', 'user'));
    }

    /**
     * Update the specified attendance record
     */
    public function updateRecord(Request $request, $id)
    {
        $attendanceRecord = AttendanceRecord::findOrFail($id);
        
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

        // Check if record already exists for this employee and date (excluding current record)
        $existingRecord = AttendanceRecord::where('employee_id', $request->employee_id)
            ->where('date', $request->date)
            ->where('id', '!=', $id)
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

        // Update the attendance record
        $attendanceRecord->update([
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
        $this->logAttendanceAction($attendanceRecord, 'manual_update', 'Attendance record manually updated');

        return redirect()->route('attendance.timekeeping')->with('success', 'Attendance record updated successfully.');
    }

    /**
     * Delete the specified attendance record
     */
    public function deleteRecord($id)
    {
        $attendanceRecord = AttendanceRecord::findOrFail($id);
        
        // Log the attendance action before deletion
        $this->logAttendanceAction($attendanceRecord, 'manual_delete', 'Attendance record manually deleted');
        
        $attendanceRecord->delete();

        return redirect()->route('attendance.timekeeping')->with('success', 'Attendance record deleted successfully.');
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

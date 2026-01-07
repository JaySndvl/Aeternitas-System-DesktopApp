<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\OvertimeRequest;
use App\Models\Employee;
use App\Models\AttendanceSetting;
use App\Helpers\TimezoneHelper;
use App\Helpers\CompanyHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class OvertimeController extends Controller
{
    /**
     * Display overtime management page
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $currentCompany = CompanyHelper::getCurrentCompany();
        
        $query = OvertimeRequest::with(['employee.department', 'approver']);

        // For employees, only show their own requests
        if ($user->role === 'employee' && $user->employee) {
            $query->where('employee_id', $user->employee->id);
            \Log::info('Employee viewing overtime records', [
                'user_id' => $user->id,
                'employee_id' => $user->employee->id,
                'role' => $user->role,
                'has_employee_relation' => $user->employee ? 'yes' : 'no'
            ]);
        } elseif ($user->role === 'employee' && !$user->employee) {
            \Log::info('Employee without employee relation viewing records', [
                'user_id' => $user->id,
                'role' => $user->role
            ]);
            // If employee role but no employee relation, show no records
            $query->where('id', null);
        } else {
            // Filter by company (but don't apply if employee's company doesn't match - let them see their records)
            // TEMPORARILY DISABLED FOR DEBUGGING
            // if ($currentCompany && $user->role !== 'employee') {
            //     $query->whereHas('employee', function($q) use ($currentCompany) {
            //         $q->where('company_id', $currentCompany->id);
            //     });
            // }
            
            // Apply filters for admins/HR
            if ($request->filled('employee_id')) {
                $query->where('employee_id', $request->employee_id);
            }

            if ($request->filled('department_id')) {
                $query->whereHas('employee', function($q) use ($request) {
                    $q->where('department_id', $request->department_id);
                });
            }
        }

        // Status and date filters apply to all users
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->where('date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('date', '<=', $request->date_to);
        }

        $overtimeRequests = $query->orderBy('date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        \Log::info('Overtime records retrieved', [
            'user_id' => $user->id,
            'role' => $user->role,
            'total_records' => $overtimeRequests->total(),
            'current_page_records' => $overtimeRequests->count()
        ]);

        // Get employees filtered by company
        // For employees, only show their own record
        $employeesQuery = Employee::with('department');
        
        // Check if user is an employee
        $userRole = strtolower(trim($user->role ?? ''));
        $isEmployee = ($userRole === 'employee');
        
        if ($isEmployee && $user->employee) {
            // Employee can only see their own record
            $employeesQuery->where('id', $user->employee->id);
        } else {
            // For admin/hr/manager, show all employees (filtered by company)
            if ($currentCompany) {
            $employeesQuery->where('company_id', $currentCompany->id);
            }
        }
        
        $employees = $employeesQuery->get();
        
        $departments = \App\Models\Department::all();

        // Calculate summary statistics from all records (not just paginated)
        $summaryQuery = OvertimeRequest::with(['employee.department', 'approver']);
        
        // Apply same filters for summary
        if ($user->role === 'employee' && $user->employee) {
            $summaryQuery->where('employee_id', $user->employee->id);
        } else {
            // Filter summary by company (but don't apply if employee's company doesn't match)
            // TEMPORARILY DISABLED FOR DEBUGGING
            // if ($currentCompany && $user->role !== 'employee') {
            //     $summaryQuery->whereHas('employee', function($q) use ($currentCompany) {
            //         $q->where('company_id', $currentCompany->id);
            //     });
            // }
            
            if ($request->filled('employee_id')) {
                $summaryQuery->where('employee_id', $request->employee_id);
            }
            if ($request->filled('department_id')) {
                $summaryQuery->whereHas('employee', function($q) use ($request) {
                    $q->where('department_id', $request->department_id);
                });
            }
        }
        if ($request->filled('status')) {
            $summaryQuery->where('status', $request->status);
        }
        if ($request->filled('date_from')) {
            $summaryQuery->where('date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $summaryQuery->where('date', '<=', $request->date_to);
        }
        
        $allOvertimeRequests = $summaryQuery->get();
        $summary = $this->calculateOvertimeSummary($allOvertimeRequests);

        return view('attendance.overtime', compact('overtimeRequests', 'employees', 'departments', 'summary', 'user'));
    }

    /**
     * Store a new overtime request
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $employee = $user->employee;
        
        if (!$employee) {
            return response()->json(['error' => 'Employee record not found.'], 404);
        }

        $request->validate([
            'date' => 'required|date|after_or_equal:today',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'reason' => 'required|string|max:500',
        ]);

        // Check if there's already an overtime request for this date
        $existingRequest = OvertimeRequest::where('employee_id', $employee->id)
            ->where('date', $request->date)
            ->first();

        if ($existingRequest) {
            return response()->json(['error' => 'You already have an overtime request for this date.'], 400);
        }

        // Calculate hours
        $startDateTime = Carbon::parse($request->date . ' ' . $request->start_time);
        $endDateTime = Carbon::parse($request->date . ' ' . $request->end_time);
        $hours = $endDateTime->diffInHours($startDateTime);

        // Get overtime rate multiplier
        $rateMultiplier = AttendanceSetting::getValue('overtime_rate_multiplier', 1.5);

        $overtimeRequest = OvertimeRequest::create([
            'employee_id' => $employee->id,
            'date' => $request->date,
            'start_time' => $startDateTime,
            'end_time' => $endDateTime,
            'hours' => $hours,
            'rate_multiplier' => $rateMultiplier,
            'reason' => $request->reason,
            'status' => 'pending',
        ]);

        \Log::info('Overtime request created', [
            'request_id' => $overtimeRequest->id,
            'employee_id' => $employee->id,
            'user_id' => $user->id,
            'date' => $request->date,
            'hours' => $hours
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Overtime request submitted successfully',
            'overtime_request' => $overtimeRequest->load('employee.department'),
        ]);
    }

    /**
     * Update overtime request status (approve/reject)
     */
    public function updateStatus(Request $request, $id)
    {
        $user = Auth::user();
        
        // Check if user has permission to approve/reject
        if (!in_array($user->role, ['admin', 'hr'])) {
            return response()->json(['error' => 'Unauthorized access.'], 403);
        }

        $request->validate([
            'status' => 'required|in:approved,rejected',
            'rejection_reason' => 'required_if:status,rejected|string|max:500',
        ]);

        $overtimeRequest = OvertimeRequest::findOrFail($id);

        if ($overtimeRequest->status !== 'pending') {
            return response()->json(['error' => 'This overtime request has already been processed.'], 400);
        }

        $overtimeRequest->update([
            'status' => $request->status,
            'approved_by' => $user->id,
            'approved_at' => TimezoneHelper::now(),
            'rejection_reason' => $request->status === 'rejected' ? $request->rejection_reason : null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Overtime request ' . $request->status . ' successfully',
            'overtime_request' => $overtimeRequest->fresh(['employee.department', 'approver']),
        ]);
    }

    /**
     * Cancel an overtime request
     */
    public function cancel($id)
    {
        $user = Auth::user();
        $employee = $user->employee;
        
        if (!$employee) {
            return response()->json(['error' => 'Employee record not found.'], 404);
        }

        $overtimeRequest = OvertimeRequest::where('id', $id)
            ->where('employee_id', $employee->id)
            ->firstOrFail();

        if ($overtimeRequest->status !== 'pending') {
            return response()->json(['error' => 'Only pending overtime requests can be cancelled.'], 400);
        }

        $overtimeRequest->update(['status' => 'cancelled']);

        return response()->json([
            'success' => true,
            'message' => 'Overtime request cancelled successfully',
        ]);
    }

    /**
     * Get overtime statistics
     */
    public function getStatistics(Request $request)
    {
        $date = $request->get('date', today());
        $date = Carbon::parse($date);

        $stats = [
            'today' => $this->getTodayOvertimeStats($date),
            'this_week' => $this->getWeekOvertimeStats($date),
            'this_month' => $this->getMonthOvertimeStats($date),
        ];

        return response()->json($stats);
    }

    /**
     * Calculate overtime summary
     */
    private function calculateOvertimeSummary($overtimeRequests)
    {
        $total = $overtimeRequests->count();
        $pending = $overtimeRequests->where('status', 'pending')->count();
        $approved = $overtimeRequests->where('status', 'approved')->count();
        $rejected = $overtimeRequests->where('status', 'rejected')->count();
        $totalHours = $overtimeRequests->where('status', 'approved')->sum('hours');

        return [
            'total' => $total,
            'pending' => $pending,
            'approved' => $approved,
            'rejected' => $rejected,
            'total_hours' => $totalHours,
        ];
    }

    private function getTodayOvertimeStats($date)
    {
        $requests = OvertimeRequest::where('date', $date)->get();
        
        return [
            'total_requests' => $requests->count(),
            'pending' => $requests->where('status', 'pending')->count(),
            'approved' => $requests->where('status', 'approved')->count(),
            'total_hours' => $requests->where('status', 'approved')->sum('hours'),
        ];
    }

    private function getWeekOvertimeStats($date)
    {
        $weekStart = $date->copy()->startOfWeek();
        $weekEnd = $date->copy()->endOfWeek();

        $requests = OvertimeRequest::whereBetween('date', [$weekStart, $weekEnd])->get();
        
        return [
            'total_requests' => $requests->count(),
            'approved_hours' => $requests->where('status', 'approved')->sum('hours'),
            'pending_requests' => $requests->where('status', 'pending')->count(),
        ];
    }

    private function getMonthOvertimeStats($date)
    {
        $monthStart = $date->copy()->startOfMonth();
        $monthEnd = $date->copy()->endOfMonth();

        $requests = OvertimeRequest::whereBetween('date', [$monthStart, $monthEnd])->get();
        
        return [
            'total_requests' => $requests->count(),
            'approved_hours' => $requests->where('status', 'approved')->sum('hours'),
            'average_daily_hours' => $requests->where('status', 'approved')->avg('hours') ?? 0,
        ];
    }

    /**
     * Export overtime requests
     */
    public function exportOvertime(Request $request, $format)
    {
        $user = Auth::user();
        $currentCompany = CompanyHelper::getCurrentCompany();
        
        // Build query same as index method
        $query = OvertimeRequest::with(['employee.department', 'approver']);

        // For employees, only show their own requests
        if ($user->role === 'employee' && $user->employee) {
            $query->where('employee_id', $user->employee->id);
        } elseif ($user->role === 'employee' && !$user->employee) {
            $query->where('id', null);
        } else {
            // Apply filters for admins/HR
            if ($request->filled('employee_id')) {
                $query->where('employee_id', $request->employee_id);
            }

            if ($request->filled('department_id')) {
                $query->whereHas('employee', function($q) use ($request) {
                    $q->where('department_id', $request->department_id);
                });
            }
        }

        // Status and date filters apply to all users
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->where('date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('date', '<=', $request->date_to);
        }

        // Get all records (no pagination for export)
        $records = $query->orderBy('date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        // Generate filename
        $dateFrom = $request->filled('date_from') ? Carbon::parse($request->date_from)->format('Y-m-d') : 'all';
        $dateTo = $request->filled('date_to') ? Carbon::parse($request->date_to)->format('Y-m-d') : 'all';
        $filename = 'overtime_requests_' . $dateFrom . '_to_' . $dateTo . '_' . now()->format('Y-m-d');

        switch ($format) {
            case 'pdf':
                return $this->exportOvertimePDF($records, $filename);
            case 'csv':
                return $this->exportOvertimeCSV($records, $filename);
            case 'xls':
                return $this->exportOvertimeXLS($records, $filename);
            default:
                return redirect()->route('attendance.overtime')->with('error', 'Invalid export format.');
        }
    }

    /**
     * Export overtime to PDF
     */
    private function exportOvertimePDF($records, $filename)
    {
        $data = [
            'records' => $records,
            'date' => now()->format('F d, Y'),
        ];

        $pdf = Pdf::loadView('attendance.exports.overtime-pdf', $data);
        return $pdf->download($filename . '.pdf');
    }

    /**
     * Export overtime to CSV
     */
    private function exportOvertimeCSV($records, $filename)
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '.csv"',
        ];

        $callback = function() use ($records) {
            $file = fopen('php://output', 'w');
            
            // BOM for UTF-8
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Headers
            fputcsv($file, [
                'Date',
                'Employee Code',
                'Employee Name',
                'Department',
                'Start Time',
                'End Time',
                'Hours',
                'Rate Multiplier',
                'Amount',
                'Reason',
                'Status',
                'Approved By',
                'Approved At'
            ]);

            // Data
            foreach ($records as $record) {
                $startTime = $record->start_time ? ($record->start_time instanceof Carbon ? $record->start_time->format('H:i:s') : (is_string($record->start_time) ? substr($record->start_time, 11, 8) : $record->start_time)) : 'N/A';
                $endTime = $record->end_time ? ($record->end_time instanceof Carbon ? $record->end_time->format('H:i:s') : (is_string($record->end_time) ? substr($record->end_time, 11, 8) : $record->end_time)) : 'N/A';
                
                // Calculate amount (hours * rate_multiplier * hourly_rate)
                $hourlyRate = $record->employee->hourly_rate ?? 0;
                $amount = $record->hours * $record->rate_multiplier * $hourlyRate;

                fputcsv($file, [
                    Carbon::parse($record->date)->format('Y-m-d'),
                    $record->employee->employee_code ?? 'N/A',
                    $record->employee->full_name ?? 'N/A',
                    $record->employee->department->name ?? 'N/A',
                    $startTime,
                    $endTime,
                    $record->hours ?? 0,
                    $record->rate_multiplier ?? 1,
                    number_format($amount, 2),
                    $record->reason ?? 'N/A',
                    ucfirst($record->status ?? 'pending'),
                    $record->approver ? $record->approver->full_name : 'N/A',
                    $record->approved_at ? Carbon::parse($record->approved_at)->format('Y-m-d H:i:s') : 'N/A'
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export overtime to Excel
     */
    private function exportOvertimeXLS($records, $filename)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set headers
        $headers = ['Date', 'Employee Code', 'Employee Name', 'Department', 'Start Time', 'End Time', 'Hours', 'Rate Multiplier', 'Amount', 'Reason', 'Status', 'Approved By', 'Approved At'];
        $sheet->fromArray($headers, null, 'A1');

        // Style header row
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4472C4']
            ],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ];
        $sheet->getStyle('A1:M1')->applyFromArray($headerStyle);

        // Add data
        $row = 2;
        foreach ($records as $record) {
            $startTime = $record->start_time ? ($record->start_time instanceof Carbon ? $record->start_time->format('H:i:s') : (is_string($record->start_time) ? substr($record->start_time, 11, 8) : $record->start_time)) : 'N/A';
            $endTime = $record->end_time ? ($record->end_time instanceof Carbon ? $record->end_time->format('H:i:s') : (is_string($record->end_time) ? substr($record->end_time, 11, 8) : $record->end_time)) : 'N/A';
            
            // Calculate amount
            $hourlyRate = $record->employee->hourly_rate ?? 0;
            $amount = $record->hours * $record->rate_multiplier * $hourlyRate;

            $sheet->setCellValue('A' . $row, Carbon::parse($record->date)->format('Y-m-d'));
            $sheet->setCellValue('B' . $row, $record->employee->employee_code ?? 'N/A');
            $sheet->setCellValue('C' . $row, $record->employee->full_name ?? 'N/A');
            $sheet->setCellValue('D' . $row, $record->employee->department->name ?? 'N/A');
            $sheet->setCellValue('E' . $row, $startTime);
            $sheet->setCellValue('F' . $row, $endTime);
            $sheet->setCellValue('G' . $row, $record->hours ?? 0);
            $sheet->setCellValue('H' . $row, $record->rate_multiplier ?? 1);
            $sheet->setCellValue('I' . $row, number_format($amount, 2));
            $sheet->setCellValue('J' . $row, $record->reason ?? 'N/A');
            $sheet->setCellValue('K' . $row, ucfirst($record->status ?? 'pending'));
            $sheet->setCellValue('L' . $row, $record->approver ? $record->approver->full_name : 'N/A');
            $sheet->setCellValue('M' . $row, $record->approved_at ? Carbon::parse($record->approved_at)->format('Y-m-d H:i:s') : 'N/A');
            $row++;
        }

        // Auto-size columns
        foreach (range('A', 'M') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Add borders
        $borderStyle = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'CCCCCC']
                ]
            ]
        ];
        $sheet->getStyle('A1:M' . ($row - 1))->applyFromArray($borderStyle);

        $writer = new Xlsx($spreadsheet);
        $tempFile = tempnam(sys_get_temp_dir(), $filename);
        $writer->save($tempFile);

        return response()->download($tempFile, $filename . '.xlsx')->deleteFileAfterSend(true);
    }
}

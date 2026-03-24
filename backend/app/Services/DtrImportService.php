<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class DtrImportService
{
    /**
     * Parse DTR data from Excel file
     */
    public function parseDtrData($filePath)
    {
        try {
            // Try to convert Excel to CSV first
            $csvData = $this->convertExcelToCsv($filePath);
            
            // Validate that we have data
            if (empty($csvData) || !is_array($csvData)) {
                throw new \Exception('No data found in the file');
            }
            
            $parsedData = collect();
            $groupedData = collect();
            
            // Process DTR data - find the actual data section
            $dataStartIndex = $this->findDataStartIndex($csvData);
            $dataRows = array_slice($csvData, $dataStartIndex);
            
            $currentEmployeeId = null;
            $currentEmployeeName = null;
            
            foreach ($dataRows as $rowIndex => $row) {
                try {
                    // Ensure row is an array and has the expected structure
                    if (!is_array($row) || count($row) < 3) {
                        continue;
                    }
                    
                    // Check if this row has employee info (has employee ID in column 0)
                    if (!empty($row[0]) && !empty($row[1])) {
                        // This is an employee info row
                        $currentEmployeeId = trim($row[0]);
                        $currentEmployeeName = trim($row[1]);
                        
                        // Check if this row also has a date in column 2 (first row format)
                        if (!empty($row[2])) {
                            $date = $this->formatDate($row[2]);
                            if ($date) {
                                // Extract all time entries from the row (columns 3-8: IN, OUT, IN, OUT, IN, OUT)
                                $timeEntries = [];
                                for ($i = 3; $i <= 8; $i++) {
                                    if (!empty($row[$i])) {
                                        $formattedTime = $this->formatTime($row[$i]);
                                        if ($formattedTime) {
                                            $timeEntries[] = $formattedTime;
                                        }
                                    }
                                }
                                
                                // Process this row's data - show ALL days from CSV
                                if (isset($date) && $currentEmployeeId) {
                                    $key = $currentEmployeeId . '_' . $date;
                                    if (!$groupedData->has($key)) {
                                        $groupedData->put($key, collect());
                                    }
                                    
                                    if (!empty($timeEntries)) {
                                        // Add all time entries for this employee/date
                                        foreach ($timeEntries as $time) {
                                            $groupedData->get($key)->push([
                                                'employee_id' => $currentEmployeeId,
                                                'employee_name' => $currentEmployeeName,
                                                'date' => $date,
                                                'time' => $time,
                                                'raw_time' => $time
                                            ]);
                                        }
                                    } else {
                                        // No time entries - add a placeholder record for this date
                                        $groupedData->get($key)->push([
                                            'employee_id' => $currentEmployeeId,
                                            'employee_name' => $currentEmployeeName,
                                            'date' => $date,
                                            'time' => null,
                                            'raw_time' => null
                                        ]);
                                    }
                                }
                            }
                        }
                        continue;
                    }
                    
                    // Check if this is a date row (has date in column 2, no employee ID)
                    if (empty($row[0]) && !empty($row[2]) && $currentEmployeeId) {
                        $date = $this->formatDate($row[2]);
                        if (!$date) continue;
                        
                        // Extract all time entries from the row (columns 3-8: IN, OUT, IN, OUT, IN, OUT)
                        $timeEntries = [];
                        for ($i = 3; $i <= 8; $i++) {
                            if (!empty($row[$i])) {
                                $formattedTime = $this->formatTime($row[$i]);
                                if ($formattedTime) {
                                    $timeEntries[] = $formattedTime;
                                }
                            }
                        }
                        
                        // Process this row's data - show ALL days from CSV
                        if (isset($date) && $currentEmployeeId) {
                            $key = $currentEmployeeId . '_' . $date;
                            if (!$groupedData->has($key)) {
                                $groupedData->put($key, collect());
                            }
                            
                            if (!empty($timeEntries)) {
                                // Add all time entries for this employee/date
                                foreach ($timeEntries as $time) {
                                    $groupedData->get($key)->push([
                                        'employee_id' => $currentEmployeeId,
                                        'employee_name' => $currentEmployeeName,
                                        'date' => $date,
                                        'time' => $time,
                                        'raw_time' => $time
                                    ]);
                                }
                            } else {
                                // No time entries - add a placeholder record for this date
                                $groupedData->get($key)->push([
                                    'employee_id' => $currentEmployeeId,
                                    'employee_name' => $currentEmployeeName,
                                    'date' => $date,
                                    'time' => null,
                                    'raw_time' => null
                                ]);
                            }
                        }
                    } else {
                        continue;
                    }
                } catch (\Exception $e) {
                    Log::error('Error processing row ' . $rowIndex . ': ' . $e->getMessage() . ' - Row data: ' . json_encode($row));
                    continue;
                }
            }
            
            // Process each employee's daily data
            foreach ($groupedData as $key => $employeeDayData) {
                $parsedRecord = $this->processEmployeeDayData($employeeDayData);
                if ($parsedRecord) {
                    $parsedData->push($parsedRecord);
                }
            }
            
            return $parsedData;
            
        } catch (\Exception $e) {
            Log::error('DTR Import Error: ' . $e->getMessage());
            Log::error('File path: ' . $filePath);
            Log::error('Error trace: ' . $e->getTraceAsString());
            throw new \Exception('Failed to read Excel file: ' . $e->getMessage());
        }
    }
    
    /**
     * Find the starting index of actual data in CSV
     */
    private function findDataStartIndex($csvData)
    {
        foreach ($csvData as $index => $row) {
            // Look for the header row that contains "Employee No." or "Employee ID"
            if (!empty($row[0]) && (strpos($row[0], 'Employee') !== false || strpos($row[0], 'EMP-') !== false)) {
                return $index;
            }
        }
        return 0; // Fallback to start from beginning
    }
    
    /**
     * Convert Excel file to CSV format
     */
    private function convertExcelToCsv($filePath)
    {
        try {
            // Check if file exists
            if (!file_exists($filePath)) {
                throw new \Exception('File not found: ' . $filePath);
            }
            
            // Get file extension
            $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
            
            if ($extension === 'csv') {
                // Already a CSV file, read it directly
                return $this->readCsvFile($filePath);
            } elseif (in_array($extension, ['xlsx', 'xls'])) {
                // Try to use system command to convert Excel to CSV
                return $this->convertExcelUsingSystem($filePath);
            } else {
                throw new \Exception('Unsupported file format: ' . $extension);
            }
            
        } catch (\Exception $e) {
            throw new \Exception('Failed to convert Excel file: ' . $e->getMessage());
        }
    }
    
    /**
     * Read CSV file
     */
    private function readCsvFile($filePath)
    {
        $data = [];
        try {
            if (($handle = fopen($filePath, 'r')) !== FALSE) {
                // Set locale to handle encoding issues
                setlocale(LC_ALL, 'en_US.UTF-8');
                
                while (($row = fgetcsv($handle, 0, ',')) !== FALSE) {
                    // Clean each cell to remove any trailing data or extra characters
                    $cleanRow = array_map(function($cell) {
                        if (is_string($cell)) {
                            // Remove any trailing whitespace, null bytes, or control characters
                            $cell = trim($cell);
                            $cell = str_replace(["\0", "\r", "\n"], '', $cell);
                            // Remove any trailing data after the main content
                            $cell = preg_replace('/\s+$/', '', $cell);
                        }
                        return $cell;
                    }, $row);
                    
                    $data[] = $cleanRow;
                }
                fclose($handle);
            }
        } catch (\Exception $e) {
            Log::error('Error reading CSV file: ' . $e->getMessage());
            throw new \Exception('Failed to read CSV file: ' . $e->getMessage());
        }
        return $data;
    }
    
    /**
     * Convert Excel using system command (if available)
     */
    private function convertExcelUsingSystem($filePath)
    {
        // Try to use LibreOffice if available
        $outputPath = $filePath . '.csv';
        
        // Check if LibreOffice is available
        $libreOfficePath = $this->findLibreOffice();
        
        if ($libreOfficePath) {
            $command = "\"$libreOfficePath\" --headless --convert-to csv --outdir \"" . dirname($filePath) . "\" \"$filePath\"";
            exec($command, $output, $returnCode);
            
            if ($returnCode === 0 && file_exists($outputPath)) {
                $data = $this->readCsvFile($outputPath);
                unlink($outputPath); // Clean up
                return $data;
            }
        }
        
        // If LibreOffice is not available, ask user to convert manually
        throw new \Exception('Please save your Excel file as CSV format (.csv) and try again. LibreOffice is not available for automatic conversion.');
    }
    
    /**
     * Find LibreOffice installation
     */
    private function findLibreOffice()
    {
        $possiblePaths = [
            'C:\\Program Files\\LibreOffice\\program\\soffice.exe',
            'C:\\Program Files (x86)\\LibreOffice\\program\\soffice.exe',
            '/usr/bin/libreoffice',
            '/usr/local/bin/libreoffice',
            'libreoffice'
        ];
        
        foreach ($possiblePaths as $path) {
            if (is_executable($path) || $this->commandExists($path)) {
                return $path;
            }
        }
        
        return null;
    }
    
    /**
     * Check if command exists
     */
    private function commandExists($command)
    {
        $return = shell_exec(sprintf("which %s", escapeshellarg($command)));
        return !empty($return);
    }
    
    /**
     * Simple Excel reader (basic implementation)
     */
    private function readExcelSimple($filePath)
    {
        // Get actual employees from database for realistic sample data
        $employees = \App\Models\Employee::take(3)->get();
        
        if ($employees->isEmpty()) {
            // Fallback sample data if no employees in database
            $sampleData = [
                ['Employee No.', 'Name', 'Date', 'IN', 'OUT', 'IN', 'OUT', 'IN', 'OUT'], // Header
                ['EMP-0001', 'Sample Employee 1', '2024-12-19', '08:00', '17:00', '', '', '', ''],
                ['EMP-0002', 'Sample Employee 2', '2024-12-19', '08:15', '17:30', '', '', '', ''],
            ];
            return $sampleData;
        }
        
        // Create sample data using actual employees
        $sampleData = [
            ['Employee No.', 'Name', 'Date', 'IN', 'OUT', 'IN', 'OUT', 'IN', 'OUT'], // Header
        ];
        
        foreach ($employees as $employee) {
            $sampleData[] = [
                $employee->employee_id,
                $employee->first_name . ' ' . $employee->last_name,
                '2024-12-19',
                '08:00',
                '17:00',
                '',
                '',
                '',
                ''
            ];
        }
        
        Log::info('Sample data created with ' . count($sampleData) . ' rows');
        return $sampleData;
    }
    
    /**
     * Format date from Excel
     */
    private function formatDate($dateValue)
    {
        try {
            // Clean the date value - remove any extra whitespace or characters
            $cleanDate = trim($dateValue);
            
            // Handle empty values
            if (empty($cleanDate)) {
                return null;
            }
            
            // Try different date formats that might be in the CSV
            $formats = [
                'm/d/Y',      // 09/10/2025
                'm-d-Y',      // 09-10-2025
                'Y-m-d',      // 2025-09-10
                'd/m/Y',      // 10/09/2025
                'd-m-Y',      // 10-09-2025
            ];
            
            foreach ($formats as $format) {
                try {
                    $date = Carbon::createFromFormat($format, $cleanDate);
                    if ($date) {
                        return $date->format('Y-m-d');
                    }
                } catch (\Exception $e) {
                    // Continue to next format
                    continue;
                }
            }
            
            // If no format worked, try Carbon::parse as fallback
            try {
                return Carbon::parse($cleanDate)->format('Y-m-d');
            } catch (\Exception $parseError) {
                Log::error('Carbon parse error for date: ' . $parseError->getMessage() . ' for value: ' . $dateValue);
                return null;
            }
        } catch (\Exception $e) {
            Log::error('Date formatting error: ' . $e->getMessage() . ' for value: ' . $dateValue);
            return null;
        }
    }
    
    /**
     * Format time from Excel
     */
    private function formatTime($timeValue)
    {
        try {
            // Clean the time value - remove any extra whitespace or characters
            $cleanTime = trim($timeValue);
            
            // Handle empty values
            if (empty($cleanTime)) {
                return null;
            }
            
            // Try different time formats that might be in the CSV
            $formats = [
                'H:i',        // 7:42
                'H:i:s',      // 7:42:00
                'g:i A',      // 7:42 AM
                'g:i:s A',    // 7:42:00 AM
            ];
            
            foreach ($formats as $format) {
                try {
                    $time = Carbon::createFromFormat($format, $cleanTime);
                    if ($time) {
                        return $time->format('H:i');
                    }
                } catch (\Exception $e) {
                    // Continue to next format
                    continue;
                }
            }
            
            // If no format worked, try Carbon::parse as fallback
            try {
                return Carbon::parse($cleanTime)->format('H:i');
            } catch (\Exception $parseError) {
                Log::error('Carbon parse error for time: ' . $parseError->getMessage() . ' for value: ' . $timeValue);
                return null;
            }
        } catch (\Exception $e) {
            Log::error('Time formatting error: ' . $e->getMessage() . ' for value: ' . $timeValue);
            return null;
        }
    }
    
    /**
     * Process data for a single employee for a single day
     */
    private function processEmployeeDayData($dayData)
    {
        if ($dayData->isEmpty()) {
            return null;
        }
        
        // Filter out null time entries and sort by time
        $validTimeEntries = $dayData->filter(function($entry) {
            return $entry['time'] !== null;
        });
        
        $firstEntry = $dayData->first();
        
        // Determine time in and time out based on your logic
        $timeIn = null;
        $timeOut = null;
        $totalHours = 0;
        
        if ($validTimeEntries->isNotEmpty()) {
            // Sort by time to get chronological order
            $sortedData = $validTimeEntries->sortBy('time');
            
            // Check if there's only one time entry (indicates no time out)
            if ($sortedData->count() === 1) {
                $timeIn = $this->determineTimeIn($sortedData);
                $timeOut = null; // No time out if only one entry
            } else {
                $timeIn = $this->determineTimeIn($sortedData);
                $timeOut = $this->determineTimeOut($sortedData);
            }
            
            $totalHours = $this->calculateTotalHours($timeIn, $timeOut);
        }
        
        // Get employee from database to check schedule
        $employee = \App\Models\Employee::where('employee_id', $firstEntry['employee_id'])->first();
        $employeeName = 'Unknown Employee';
        
        if ($employee) {
            $employeeName = $employee->first_name . ' ' . $employee->last_name;
        }
        
        // Check existing schedule for this employee and date
        $scheduleStatus = $this->checkScheduleStatus($firstEntry['employee_id'], $firstEntry['date']);
        
        // Calculate final hours based on schedule
        $finalHours = $this->calculateFinalHours($totalHours, $scheduleStatus, $firstEntry['employee_id'], $firstEntry['date'], $timeIn, $timeOut);
        
        // Determine final status based on schedule and attendance
        $status = $this->determineFinalStatus($timeIn, $timeOut, $finalHours, $scheduleStatus);
        
        // Calculate overtime if applicable
        $overtimeHours = $this->calculateOvertime($finalHours, $scheduleStatus, $firstEntry['employee_id'], $firstEntry['date'], $timeIn, $timeOut);
        
        return [
            'employee_id' => $firstEntry['employee_id'],
            'employee_name' => $employeeName,
            'date' => $firstEntry['date'],
            'time_in' => $timeIn,
            'time_out' => $timeOut,
            'total_hours' => $finalHours,
            'overtime_hours' => $overtimeHours,
            'status' => $status,
            'schedule_status' => $scheduleStatus,
            'raw_data' => $dayData->toArray(), // Keep original data for review
        ];
    }
    
    /**
     * Determine time in - first time entry of the day
     */
    private function determineTimeIn($sortedData)
    {
        $firstEntry = $sortedData->first();
        return $firstEntry['time'] ?? null;
    }
    
    /**
     * Determine time out - last time entry of the day
     */
    private function determineTimeOut($sortedData)
    {
        $lastEntry = $sortedData->last();
        return $lastEntry['time'] ?? null;
    }
    
    /**
     * Calculate total hours worked
     */
    private function calculateTotalHours($timeIn, $timeOut)
    {
        if (!$timeIn) {
            Log::info('No time in provided for hours calculation');
            return 0;
        }
        
        try {
            // Parse times with explicit 24-hour format
            $start = Carbon::createFromFormat('H:i', $timeIn);
            
            if ($timeOut) {
                // Normal case: has both time in and time out
                $end = Carbon::createFromFormat('H:i', $timeOut);
                
                // Set both times to the same date for proper calculation
                $start->setDate(2024, 1, 1); // Use a fixed date
                $end->setDate(2024, 1, 1);   // Use the same fixed date
                
                // Check if time out is on the next day (after midnight)
                if ($end->lt($start)) {
                    $end->addDay();
                }
                
                $totalMinutes = $start->diffInMinutes($end);
                
                // Subtract 1 hour for lunch break if working more than 6 hours
                if ($totalMinutes > 360) { // More than 6 hours
                    $lunchBreakMinutes = 60; // 1 hour lunch break
                    $workingMinutes = max(0, $totalMinutes - $lunchBreakMinutes);
                } else {
                    $workingMinutes = $totalMinutes;
                }
                
                $result = round($workingMinutes / 60, 2);
                
                
                return $result;
            } else {
                // Error case: has time in but no time out - calculate based on reasonable assumption
                // If time in is before 12:00 PM, assume 8-hour work day
                // If time in is after 12:00 PM, assume 4-hour work day
                if ($start->hour < 12) {
                    // Morning start - assume 8-hour work day
                    $endOfDay = $start->copy()->addHours(8);
                } else {
                    // Afternoon start - assume 4-hour work day
                    $endOfDay = $start->copy()->addHours(4);
                }
                $totalMinutes = $endOfDay->diffInMinutes($start);
                
                // Subtract 1 hour for lunch break if working more than 6 hours
                if ($totalMinutes > 360) { // More than 6 hours
                    $lunchBreakMinutes = 60; // 1 hour lunch break
                    $workingMinutes = max(0, $totalMinutes - $lunchBreakMinutes);
                } else {
                    $workingMinutes = $totalMinutes;
                }
                
                $result = round($workingMinutes / 60, 2);
                
                
                return $result;
            }
        } catch (\Exception $e) {
            Log::error('Error calculating hours: ' . $e->getMessage() . ' - TimeIn: ' . $timeIn . ', TimeOut: ' . $timeOut);
            return 0;
        }
    }
    
    /**
     * Check schedule status for employee on specific date
     */
    private function checkScheduleStatus($employeeId, $date)
    {
        $employee = \App\Models\Employee::where('employee_id', $employeeId)->first();
        if (!$employee) {
            return null;
        }
        
        $schedule = \App\Models\EmployeeSchedule::where('employee_id', $employee->id)
            ->where('date', $date)
            ->first();
            
        return $schedule ? $schedule->status : null;
    }
    
    /**
     * Determine final status based on schedule and attendance
     */
    private function determineFinalStatus($timeIn, $timeOut, $totalHours, $scheduleStatus)
    {
        // Check if there's actual attendance data
        $hasAttendance = $timeIn || $timeOut;
        
        if ($scheduleStatus) {
            // Handle Day Off schedules first - always return day_off regardless of attendance
            if ($scheduleStatus === 'Day Off' || $scheduleStatus === 'Rest Day') {
                return 'day_off';
            }
            
            if ($hasAttendance) {
                // Employee has both schedule and attendance - show attendance status
                return $this->determineAttendanceStatus($timeIn, $timeOut, $totalHours);
            } else {
                // Employee has schedule but no attendance - show schedule-based status
                switch ($scheduleStatus) {
                    case 'Leave':
                        return 'leave';
                    case 'Holiday':
                        return 'holiday';
                    case 'Overtime':
                        return 'overtime';
                    case 'Working':
                        return 'absent'; // Scheduled to work but no attendance
                    default:
                        return 'absent';
                }
            }
        } else {
            // No schedule found
            if ($hasAttendance) {
                // No schedule but has attendance - show attendance status
                return $this->determineAttendanceStatus($timeIn, $timeOut, $totalHours);
            } else {
                // No schedule and no attendance - show absent
                return 'absent';
            }
        }
    }
    
    /**
     * Determine attendance status (original logic)
     */
    private function determineAttendanceStatus($timeIn, $timeOut, $totalHours)
    {
        if (!$timeIn) {
            return 'absent';
        }
        
        if (!$timeOut) {
            return 'error'; // Time in but no time out - this is an error
        }
        
        // Check if time in and time out are the same (indicates error in data)
        if ($timeIn === $timeOut) {
            return 'error'; // Same time for in and out - this is an error
        }
        
        // Check if late (assuming 8:00 AM is standard start time)
        $standardStart = Carbon::parse('08:00:00');
        $actualStart = Carbon::parse($timeIn);
        
        if ($actualStart->gt($standardStart->addMinutes(1))) { // 1 minutes grace period
            return 'late';
        }
        
        // Check if half day (less than 4 hours)
        if ($totalHours < 4) {
            return 'half_day';
        }
        
        return 'present';
    }
    
    /**
     * Calculate final hours based on schedule
     */
    private function calculateFinalHours($totalHours, $scheduleStatus, $employeeId, $date, $timeIn, $timeOut)
    {
        // If no actual attendance (no time in or time out), return 0 hours
        if (!$timeIn || !$timeOut) {
            return 0;
        }
        
        // If time in and time out are the same (error case), return 0 hours
        if ($timeIn === $timeOut) {
            return 0;
        }
        
        // If no schedule found, use actual hours
        if (!$scheduleStatus || $scheduleStatus !== 'Working') {
            return $totalHours;
        }
        
        // Get the actual employee schedule for this date
        $employee = \App\Models\Employee::where('employee_id', $employeeId)->first();
        if (!$employee) {
            return $totalHours;
        }
        
        // Get the employee schedule for this specific date
        $schedule = \App\Models\EmployeeSchedule::where('employee_id', $employee->id)
            ->where('date', $date)
            ->first();
            
        if ($schedule && $schedule->time_in && $schedule->time_out && $schedule->status === 'Working') {
            // Calculate scheduled hours from the database
            $scheduledStart = $this->parseTimeFromDatabase($schedule->time_in);
            $scheduledEnd = $this->parseTimeFromDatabase($schedule->time_out);
            
            if ($scheduledStart && $scheduledEnd) {
                // Set both times to the same date for proper calculation
                $scheduledStart->setDate(2024, 1, 1);
                $scheduledEnd->setDate(2024, 1, 1);
                
                // Check if scheduled end time is on the next day
                if ($scheduledEnd->lt($scheduledStart)) {
                    $scheduledEnd->addDay();
                }
                
                $scheduledMinutes = $scheduledStart->diffInMinutes($scheduledEnd);
                
                // Subtract 1 hour for lunch break if scheduled hours are more than 6 hours
                if ($scheduledMinutes > 360) { // More than 6 hours
                    $lunchBreakMinutes = 60; // 1 hour lunch break
                    $scheduledWorkingMinutes = max(0, $scheduledMinutes - $lunchBreakMinutes);
                } else {
                    $scheduledWorkingMinutes = $scheduledMinutes;
                }
                
                $scheduledHours = round($scheduledWorkingMinutes / 60, 2);
                
                // Determine if employee was late or worked overtime
                $actualTimeIn = Carbon::createFromFormat('H:i', $timeIn);
                $actualTimeOut = Carbon::createFromFormat('H:i', $timeOut);
                $actualTimeIn->setDate(2024, 1, 1);
                $actualTimeOut->setDate(2024, 1, 1);
                
                // Check if employee was late (time in after scheduled time)
                $isLate = $actualTimeIn->gt($scheduledStart);
                
                // Check if employee worked overtime (time out after scheduled time + grace period)
                $gracePeriodEnd = $scheduledEnd->copy()->addMinutes(30);
                $isOvertime = $actualTimeOut->gt($gracePeriodEnd);
                
                // Determine final hours
                if ($isLate && !$isOvertime) {
                    // Employee was late but didn't work overtime - show actual hours
                    $finalHours = $totalHours;
                } elseif ($isOvertime) {
                    // Employee worked overtime - show actual hours
                    $finalHours = $totalHours;
                } else {
                    // Employee worked within scheduled time - show scheduled hours
                    $finalHours = $scheduledHours;
                }
                
                Log::info('Final hours calculation', [
                    'total_hours' => $totalHours,
                    'scheduled_hours' => $scheduledHours,
                    'time_in' => $timeIn,
                    'time_out' => $timeOut,
                    'scheduled_start' => $scheduledStart->format('H:i'),
                    'scheduled_end' => $scheduledEnd->format('H:i'),
                    'is_late' => $isLate,
                    'is_overtime' => $isOvertime,
                    'returning' => $finalHours
                ]);
                
                return $finalHours;
            }
        }
        
        // If no schedule found, return actual hours
        return $totalHours;
    }
    
    /**
     * Calculate overtime hours based on actual employee schedule
     */
    private function calculateOvertime($totalHours, $scheduleStatus, $employeeId, $date, $timeIn, $timeOut)
    {
        // Always calculate overtime based on actual hours worked
        if ($totalHours <= 0) {
            return 0;
        }
        
        if ($scheduleStatus === 'Overtime') {
            // If scheduled for overtime, all hours are overtime
            return $totalHours;
        }
        
        // Get the actual employee schedule for this date
        $employee = \App\Models\Employee::where('employee_id', $employeeId)->first();
        if (!$employee) {
            // If employee not found, use default 8-hour calculation
            return max(0, $totalHours - 8);
        }
        
        // Get the employee schedule for this specific date
        $schedule = \App\Models\EmployeeSchedule::where('employee_id', $employee->id)
            ->where('date', $date)
            ->first();
            
        if ($schedule && $schedule->time_in && $schedule->time_out && $schedule->status === 'Working') {
            // Calculate scheduled hours from the database with robust parsing
            Log::info('Parsing schedule times', [
                'time_in' => $schedule->time_in,
                'time_out' => $schedule->time_out,
                'employee_id' => $employeeId,
                'date' => $date
            ]);
            
            $scheduledStart = $this->parseTimeFromDatabase($schedule->time_in);
            $scheduledEnd = $this->parseTimeFromDatabase($schedule->time_out);
            
            if (!$scheduledStart || !$scheduledEnd) {
                Log::warning('Failed to parse schedule times, using default 8-hour calculation', [
                    'time_in' => $schedule->time_in,
                    'time_out' => $schedule->time_out
                ]);
                // If parsing fails, use default 8-hour calculation
                return max(0, $totalHours - 8);
            }
            
            // Set both times to the same date for proper calculation
            $scheduledStart->setDate(2024, 1, 1);
            $scheduledEnd->setDate(2024, 1, 1);
            
            // Check if scheduled end time is on the next day
            if ($scheduledEnd->lt($scheduledStart)) {
                $scheduledEnd->addDay();
            }
            
            $scheduledMinutes = $scheduledStart->diffInMinutes($scheduledEnd);
            
            // Subtract 1 hour for lunch break if scheduled hours are more than 6 hours
            if ($scheduledMinutes > 360) { // More than 6 hours
                $lunchBreakMinutes = 60; // 1 hour lunch break
                $scheduledWorkingMinutes = max(0, $scheduledMinutes - $lunchBreakMinutes);
            } else {
                $scheduledWorkingMinutes = $scheduledMinutes;
            }
            
            $scheduledHours = round($scheduledWorkingMinutes / 60, 2);
            
            // Calculate overtime based on scheduled working hours
            // Use scheduled start time for overtime calculation, not actual time in
            $scheduledStartTime = $scheduledStart->format('H:i');
            $scheduledEndTime = $scheduledEnd->format('H:i');
            
            // Calculate hours worked based on scheduled start time and actual time out
            $scheduledStartForCalculation = Carbon::createFromFormat('H:i', $scheduledStartTime);
            $scheduledStartForCalculation->setDate(2024, 1, 1);
            
            $actualTimeOut = null;
            if ($timeOut) {
                $actualTimeOut = Carbon::createFromFormat('H:i', $timeOut);
                $actualTimeOut->setDate(2024, 1, 1);
            }
            
            // Initialize grace period end for logging
            $gracePeriodEnd = null;
            
            if ($actualTimeOut) {
                // Calculate overtime only if they worked beyond their scheduled end time + grace period
                $scheduledEndForCalculation = Carbon::createFromFormat('H:i', $scheduledEndTime);
                $scheduledEndForCalculation->setDate(2024, 1, 1);
                
                // Add grace period (30 minutes) to scheduled end time
                $gracePeriodEnd = $scheduledEndForCalculation->copy()->addMinutes(30);
                
                // Check if actual time out is after scheduled end time + grace period
                if ($actualTimeOut->gt($gracePeriodEnd)) {
                    // Calculate overtime from scheduled end to actual end
                    $overtimeMinutes = $scheduledEndForCalculation->diffInMinutes($actualTimeOut);
                    $overtimeHours = round($overtimeMinutes / 60, 2);
                } else {
                    // No overtime if they left within the grace period
                    $overtimeHours = 0;
                }
            } else {
                // No time out, use default calculation
                $overtimeHours = max(0, $totalHours - $scheduledHours);
            }
            
            Log::info('Overtime calculation with schedule', [
                'scheduled_start' => $schedule->time_in,
                'scheduled_end' => $schedule->time_out,
                'actual_time_in' => $timeIn,
                'actual_time_out' => $timeOut,
                'grace_period_end' => $gracePeriodEnd ? $gracePeriodEnd->format('H:i') : 'N/A',
                'scheduled_hours' => $scheduledHours,
                'total_hours' => $totalHours,
                'overtime_hours' => $overtimeHours
            ]);
            
            return $overtimeHours;
        }
        
        // If no specific schedule found, use default 8-hour calculation
        return max(0, $totalHours - 8);
    }
    
    /**
     * Parse time from database with robust error handling
     */
    private function parseTimeFromDatabase($timeValue)
    {
        try {
            // Clean the time value - remove any extra whitespace or characters
            $cleanTime = trim($timeValue);
            
            // Handle empty values
            if (empty($cleanTime)) {
                return null;
            }
            
            // Try different time formats that might be in the database
            $formats = [
                'H:i:s',      // 08:00:00 (database format)
                'H:i',        // 08:00
                'g:i A',      // 8:00 AM
                'g:i:s A',    // 8:00:00 AM
            ];
            
            foreach ($formats as $format) {
                try {
                    $time = Carbon::createFromFormat($format, $cleanTime);
                    if ($time) {
                        return $time;
                    }
                } catch (\Exception $e) {
                    // Continue to next format
                    continue;
                }
            }
            
            // If no format worked, try Carbon::parse as fallback
            try {
                return Carbon::parse($cleanTime);
            } catch (\Exception $parseError) {
                Log::error('Carbon parse error for database time: ' . $parseError->getMessage() . ' for value: ' . $timeValue);
                return null;
            }
        } catch (\Exception $e) {
            Log::error('Time parsing error from database: ' . $e->getMessage() . ' for value: ' . $timeValue);
            return null;
        }
    }
    
    /**
     * Determine attendance status (legacy method for backward compatibility)
     */
    private function determineStatus($timeIn, $timeOut, $totalHours)
    {
        return $this->determineAttendanceStatus($timeIn, $timeOut, $totalHours);
    }
    
    /**
     * Validate parsed data
     */
    public function validateParsedData($parsedData)
    {
        $errors = collect();
        $warnings = collect();
        
        foreach ($parsedData as $index => $record) {
            // Check if employee exists
            $employee = \App\Models\Employee::where('employee_id', $record['employee_id'])->first();
            if (!$employee) {
                $errors->push("Row " . ($index + 1) . ": Employee ID '{$record['employee_id']}' not found in system");
            }
            
            // Check for duplicate records
            $existingRecord = \App\Models\AttendanceRecord::where('employee_id', $employee?->id)
                ->where('date', $record['date'])
                ->first();
            
            if ($existingRecord) {
                $warnings->push("Row " . ($index + 1) . ": Attendance record already exists for {$employee?->first_name} {$employee?->last_name} on {$record['date']}");
            }
            
            // Check for invalid time ranges
            if ($record['time_in'] && $record['time_out']) {
                $timeIn = Carbon::parse($record['time_in']);
                $timeOut = Carbon::parse($record['time_out']);
                
                if ($timeOut->lte($timeIn)) {
                    $errors->push("Row " . ($index + 1) . ": Time out must be after time in for {$employee?->first_name} {$employee?->last_name}");
                }
            }
            
            // Check for future dates
            if (Carbon::parse($record['date'])->isFuture()) {
                $warnings->push("Row " . ($index + 1) . ": Future date detected for {$employee?->first_name} {$employee?->last_name}");
            }
        }
        
        return [
            'errors' => $errors,
            'warnings' => $warnings,
            'is_valid' => $errors->isEmpty()
        ];
    }
}

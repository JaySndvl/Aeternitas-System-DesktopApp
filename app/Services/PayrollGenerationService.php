<?php

namespace App\Services;

use App\Models\Payroll;
use App\Models\Employee;
use App\Models\AttendanceRecord;
use App\Models\EmployeeSchedule;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PayrollGenerationService
{
    /**
     * Generate payroll preview from comprehensive attendance data (without saving to database)
     *
     * @param array $periodData Period data from session
     * @param array $comprehensiveData Comprehensive attendance data from period management
     * @param array|null $employeeIds Optional array of employee IDs to process
     * @return array Preview payroll data
     */
    public function generatePayrollPreview(array $periodData, array $comprehensiveData, ?array $employeeIds = null): array
    {
        $generatedPayrolls = [];
        
        // Group comprehensive data by employee
        $groupedData = collect($comprehensiveData)->groupBy('employee_id');
        
        foreach ($groupedData as $employeeId => $employeeRecords) {
            $employee = Employee::find($employeeId);
            if ($employee) {
                $payrollData = $this->calculatePayrollPreviewFromRecords($employee, $employeeRecords, $periodData);
                if ($payrollData) {
                    $generatedPayrolls[] = $payrollData;
                }
            }
        }
        
        return $generatedPayrolls;
    }

    /**
     * Generate payroll for a specific period using comprehensive attendance data
     *
     * @param array $periodData Period data from session
     * @param array $comprehensiveData Comprehensive attendance data from period management
     * @param array|null $employeeIds Optional array of employee IDs to process
     * @return array Generated payroll records
     */
    public function generatePayrollFromComprehensiveData(array $periodData, array $comprehensiveData, ?array $employeeIds = null): array
    {
        try {
            DB::beginTransaction();

            $startDate = Carbon::parse($periodData['start_date']);
            $endDate = Carbon::parse($periodData['end_date']);
            
            // Group comprehensive data by employee
            $groupedData = collect($comprehensiveData)->groupBy('employee_id');
            
            // Filter by employee IDs if specified
            if (!empty($employeeIds)) {
                $groupedData = $groupedData->filter(function ($records, $employeeId) use ($employeeIds) {
                    return in_array($employeeId, $employeeIds);
                });
            }
            
            $generatedPayrolls = [];
            
            foreach ($groupedData as $employeeId => $employeeRecords) {
                $employee = Employee::find($employeeId);
                if ($employee) {
                    $payroll = $this->calculatePayrollFromRecords($employee, $employeeRecords, $periodData);
                    if ($payroll) {
                        $generatedPayrolls[] = $payroll;
                    }
                }
            }
            
            DB::commit();
            
            return $generatedPayrolls;
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Payroll generation from comprehensive data failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Generate payroll for a specific period (legacy method)
     *
     * @param array $periodData Period data from session
     * @param array|null $employeeIds Optional array of employee IDs to process
     * @return array Generated payroll records
     */
    public function generatePayrollForPeriod(array $periodData, ?array $employeeIds = null): array
    {
        try {
            DB::beginTransaction();

            $startDate = Carbon::parse($periodData['start_date']);
            $endDate = Carbon::parse($periodData['end_date']);
            
            // Get employees to process
            $employees = Employee::with('department');
            
            if (!empty($employeeIds)) {
                $employees = $employees->whereIn('id', $employeeIds);
            }
            
            // Apply department filter if specified
            if (!empty($periodData['department_id'])) {
                $employees = $employees->where('department_id', $periodData['department_id']);
            }
            
            $employees = $employees->get();
            
            $generatedPayrolls = [];
            
            foreach ($employees as $employee) {
                $payroll = $this->generateEmployeePayroll($employee, $startDate, $endDate);
                if ($payroll) {
                    $generatedPayrolls[] = $payroll;
                }
            }
            
            DB::commit();
            
            return $generatedPayrolls;
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Payroll generation failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Generate payroll for a specific employee and period
     *
     * @param Employee $employee
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return Payroll|null
     */
    public function generateEmployeePayroll(Employee $employee, Carbon $startDate, Carbon $endDate): ?Payroll
    {
        // Check if payroll already exists for this period
        $existingPayroll = Payroll::where('employee_id', $employee->id)
            ->where('pay_period_start', $startDate->format('Y-m-d'))
            ->where('pay_period_end', $endDate->format('Y-m-d'))
            ->first();
            
        if ($existingPayroll) {
            Log::info("Payroll already exists for employee {$employee->employee_id} for period {$startDate->format('Y-m-d')} to {$endDate->format('Y-m-d')}");
            return null;
        }

        // Calculate attendance data
        $attendanceData = $this->calculateAttendanceData($employee, $startDate, $endDate);
        
        // Calculate payroll components using daily rate
        $basicSalary = $this->calculateBasicSalary($employee, $attendanceData);
        $overtimeData = $this->calculateOvertime($employee, $startDate, $endDate);
        $bonuses = $this->calculateBonuses($employee, $attendanceData);
        $deductions = $this->calculateDeductions($employee, $attendanceData);
        
        $grossPay = $basicSalary + $overtimeData['overtime_pay'] + $bonuses;
        $taxAmount = $this->calculateTax($grossPay);
        $netPay = $grossPay - $deductions - $taxAmount;

        // Create payroll record
        $payroll = Payroll::create([
            'employee_id' => $employee->id,
            'pay_period_start' => $startDate->format('Y-m-d'),
            'pay_period_end' => $endDate->format('Y-m-d'),
            'basic_salary' => $basicSalary,
            'overtime_hours' => $overtimeData['overtime_hours'],
            'overtime_rate' => $overtimeData['overtime_rate'],
            'scheduled_hours' => $attendanceData['total_hours'],
            'bonuses' => $bonuses,
            'deductions' => $deductions,
            'tax_amount' => $taxAmount,
            'gross_pay' => $grossPay,
            'net_pay' => $netPay,
            'status' => 'pending',
        ]);

        Log::info("Generated payroll for employee {$employee->employee_id}: Basic: {$basicSalary}, Overtime: {$overtimeData['overtime_pay']}, Net: {$netPay}");
        
        return $payroll;
    }

    /**
     * Calculate attendance data for an employee in a period
     *
     * @param Employee $employee
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return array
     */
    private function calculateAttendanceData(Employee $employee, Carbon $startDate, Carbon $endDate): array
    {
        $attendanceRecords = AttendanceRecord::where('employee_id', $employee->id)
            ->whereBetween('date', [$startDate, $endDate])
            ->get();

        $workingDays = 0;
        $totalHours = 0;
        $overtimeHours = 0;

        foreach ($attendanceRecords as $record) {
            if ($record->status === 'present' || $record->status === 'late') {
                $workingDays++;
                $totalHours += $record->total_hours ?? 0;
                
                // Calculate overtime (hours beyond 8 per day)
                if (($record->total_hours ?? 0) > 8) {
                    $overtimeHours += ($record->total_hours - 8);
                }
            }
        }

        return [
            'working_days' => $workingDays,
            'total_hours' => $totalHours,
            'overtime_hours' => $overtimeHours,
            'attendance_records' => $attendanceRecords
        ];
    }

    /**
     * Calculate basic salary using daily rate and total hours worked
     *
     * @param Employee $employee
     * @param array $attendanceData
     * @return float
     */
    private function calculateBasicSalary(Employee $employee, array $attendanceData): float
    {
        $dailyRate = $employee->daily_rate;
        $totalHours = $attendanceData['total_hours'];
        
        // Formula: Basic Salary = Daily Rate × (Total Hours / 8)
        $basicSalary = $dailyRate * ($totalHours / 8);
        
        return round($basicSalary, 2);
    }

    /**
     * Calculate overtime pay
     *
     * @param Employee $employee
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return array
     */
    private function calculateOvertime(Employee $employee, Carbon $startDate, Carbon $endDate): array
    {
        $attendanceData = $this->calculateAttendanceData($employee, $startDate, $endDate);
        $overtimeHours = $attendanceData['overtime_hours'];
        $overtimeRate = $employee->overtime_rate;
        $overtimePay = $overtimeHours * $overtimeRate;

        return [
            'overtime_hours' => $overtimeHours,
            'overtime_rate' => $overtimeRate,
            'overtime_pay' => round($overtimePay, 2)
        ];
    }

    /**
     * Calculate bonuses (performance, attendance, etc.)
     *
     * @param Employee $employee
     * @param array $attendanceData
     * @return float
     */
    private function calculateBonuses(Employee $employee, array $attendanceData): float
    {
        $bonuses = 0;
        
        // Perfect attendance bonus (example)
        $totalDays = $attendanceData['attendance_records']->count();
        $workingDays = $attendanceData['working_days'];
        
        if ($totalDays > 0 && $workingDays == $totalDays) {
            $bonuses += 500; // Perfect attendance bonus
        }
        
        return $bonuses;
    }

    /**
     * Calculate deductions (late, error, etc.)
     *
     * @param Employee $employee
     * @param array $attendanceData
     * @return float
     */
    private function calculateDeductions(Employee $employee, array $attendanceData): float
    {
        $deductions = 0;
        
        // Late deductions - based on basic salary per minute
        $totalLateMinutes = $attendanceData['attendance_records']->sum('late_minutes');
        if ($totalLateMinutes > 0) {
            // Calculate rate per minute: Daily rate / (8 hours * 60 minutes)
            $minutesPerDay = 8 * 60; // 480 minutes per day
            $ratePerMinute = $employee->daily_rate / $minutesPerDay;
            $deductions += $totalLateMinutes * $ratePerMinute;
        }
        
        // Error deductions (incomplete time records)
        $errorCount = $attendanceData['attendance_records']->where('status', 'error')->count();
        $deductions += $errorCount * $employee->daily_rate; // Full day deduction for error
        
        return $deductions;
    }

    /**
     * Calculate tax amount
     *
     * @param float $grossPay
     * @return float
     */
    private function calculateTax(float $grossPay): float
    {
        // Simple tax calculation (you can implement more complex tax brackets)
        if ($grossPay <= 25000) {
            return 0; // No tax for income below ₱25,000
        } elseif ($grossPay <= 50000) {
            return ($grossPay - 25000) * 0.15; // 15% for ₱25,001 - ₱50,000
        } else {
            return 3750 + (($grossPay - 50000) * 0.25); // 25% for above ₱50,000
        }
    }

    /**
     * Calculate payroll preview from comprehensive records (without saving to database)
     *
     * @param Employee $employee
     * @param \Illuminate\Support\Collection $employeeRecords
     * @param array $periodData
     * @return array|null
     */
    private function calculatePayrollPreviewFromRecords(Employee $employee, $employeeRecords, array $periodData): ?array
    {
        $startDate = Carbon::parse($periodData['start_date']);
        $endDate = Carbon::parse($periodData['end_date']);
        
        // Calculate payroll components
        $basicSalaryData = $this->calculateBasicSalaryFromRecords($employee, $employeeRecords);
        $overtimeData = $this->calculateOvertimeFromRecords($employee, $employeeRecords);
        $nightDifferentialData = $this->calculateNightDifferentialFromRecords($employee, $employeeRecords);
        $holidayData = $this->calculateHolidayPayFromRecords($employee, $employeeRecords);
        $bonuses = $this->calculateBonusesFromRecords($employee, $employeeRecords);
        $deductions = $this->calculateDeductionsFromRecords($employee, $employeeRecords);
        
        // Calculate totals
        // Gross pay should NOT include deductions - deductions are subtracted from gross pay to get net pay
        $grossPay = $basicSalaryData['amount'] + $holidayData['holiday_premium'] + $holidayData['special_holiday_premium'] + $overtimeData['overtime_pay'] + $nightDifferentialData['night_differential_pay'] + $bonuses;
        $taxAmount = $this->calculateTax($grossPay);
        $netPay = $grossPay - $deductions - $taxAmount;

        // Return preview data array (not saved to database)
        return [
            'employee_id' => $employee->id,
            'pay_period_start' => $startDate->format('Y-m-d'),
            'pay_period_end' => $endDate->format('Y-m-d'),
            'basic_salary' => $basicSalaryData['amount'],
            'basic_salary_details' => $basicSalaryData,
            'holiday_basic_pay' => $holidayData['holiday_basic_pay'],
            'holiday_premium' => $holidayData['holiday_premium'],
            'special_holiday_premium' => $holidayData['special_holiday_premium'],
            'regular_holiday_days' => $holidayData['regular_holiday_days'],
            'special_holiday_days' => $holidayData['special_holiday_days'],
            'overtime_hours' => $overtimeData['overtime_hours'],
            'overtime_rate' => $overtimeData['overtime_rate'],
            'bonuses' => $bonuses,
            'deductions' => $deductions,
            'tax_amount' => $taxAmount,
            'gross_pay' => $grossPay,
            'net_pay' => $netPay,
            'status' => 'preview',
        ];
    }

    /**
     * Calculate payroll from comprehensive attendance records
     *
     * @param Employee $employee
     * @param \Illuminate\Support\Collection $employeeRecords
     * @param array $periodData
     * @return Payroll|null
     */
    private function calculatePayrollFromRecords(Employee $employee, $employeeRecords, array $periodData): ?Payroll
    {
        $startDate = Carbon::parse($periodData['start_date']);
        $endDate = Carbon::parse($periodData['end_date']);
        
        // Check if payroll already exists for this period
        $existingPayroll = Payroll::where('employee_id', $employee->id)
            ->where('pay_period_start', $startDate->format('Y-m-d'))
            ->where('pay_period_end', $endDate->format('Y-m-d'))
            ->first();
            
        if ($existingPayroll) {
            Log::info("Payroll already exists for employee {$employee->employee_id} for period {$startDate->format('Y-m-d')} to {$endDate->format('Y-m-d')}");
            return null;
        }

        // Calculate payroll components
        $basicSalaryData = $this->calculateBasicSalaryFromRecords($employee, $employeeRecords);
        $overtimeData = $this->calculateOvertimeFromRecords($employee, $employeeRecords);
        $nightDifferentialData = $this->calculateNightDifferentialFromRecords($employee, $employeeRecords);
        $holidayData = $this->calculateHolidayPayFromRecords($employee, $employeeRecords);
        $bonuses = $this->calculateBonusesFromRecords($employee, $employeeRecords);
        $deductions = $this->calculateDeductionsFromRecords($employee, $employeeRecords);
        
        // Calculate totals
        // Gross pay should NOT include deductions - deductions are subtracted from gross pay to get net pay
        $grossPay = $basicSalaryData['amount'] + $holidayData['holiday_premium'] + $holidayData['special_holiday_premium'] + $overtimeData['overtime_pay'] + $nightDifferentialData['night_differential_pay'] + $bonuses;
        $taxAmount = $this->calculateTax($grossPay);
        $netPay = $grossPay - $deductions - $taxAmount;

        // Create payroll record
        $payroll = Payroll::create([
            'employee_id' => $employee->id,
            'pay_period_start' => $startDate->format('Y-m-d'),
            'pay_period_end' => $endDate->format('Y-m-d'),
            'basic_salary' => $basicSalaryData['amount'],
            'holiday_basic_pay' => $holidayData['holiday_basic_pay'],
            'holiday_premium' => $holidayData['holiday_premium'],
            'special_holiday_premium' => $holidayData['special_holiday_premium'],
            'regular_holiday_days' => $holidayData['regular_holiday_days'],
            'special_holiday_days' => $holidayData['special_holiday_days'],
            'overtime_hours' => $overtimeData['overtime_hours'],
            'overtime_rate' => $overtimeData['overtime_rate'],
            'scheduled_hours' => $basicSalaryData['total_scheduled_hours'],
            'bonuses' => $bonuses,
            'deductions' => $deductions,
            'tax_amount' => $taxAmount,
            'gross_pay' => $grossPay,
            'net_pay' => $netPay,
            'status' => 'pending',
        ]);

        Log::info("Generated payroll for employee {$employee->employee_id}: Basic: {$basicSalaryData['amount']}, Holiday Basic: {$holidayData['holiday_basic_pay']}, Holiday Premium: {$holidayData['holiday_premium']}, Overtime: {$overtimeData['overtime_pay']}, Net: {$netPay}");
        
        return $payroll;
    }

    /**
     * Calculate basic salary from comprehensive records based on actual scheduled hours worked
     */
    private function calculateBasicSalaryFromRecords(Employee $employee, $employeeRecords): array
    {
        $totalScheduledHours = 0;
        $scheduledHoursDetails = [];
        
        // Calculate total scheduled hours for records where employee actually worked
        // Include all work (regular days + holidays) for basic salary calculation
        $workingRecords = $employeeRecords->filter(function($record) {
            // Exclude if schedule status is 'Leave' - even if they have time_in, they shouldn't be paid
            if ($record['schedule_status'] === 'Leave') {
                return false;
            }
            
            // Include if it's a working day with present attendance
            if ($record['schedule_status'] === 'Working' && $record['attendance_status'] === 'Present') {
                return true;
            }
            
            // Include holidays with actual scheduled hours worked
            if (($record['schedule_status'] === 'Regular Holiday' || $record['schedule_status'] === 'Special Holiday') && 
                $this->parseFormattedHours($record['scheduled_hours']) > 0) {
                return true;
            }
            
            // Also include if there are actual scheduled hours worked (for other statuses)
            $scheduledHours = $this->parseFormattedHours($record['scheduled_hours']);
            return $scheduledHours > 0;
        });
        
        foreach ($workingRecords as $record) {
            // Parse scheduled hours from the formatted string (e.g., "7 hrs 1 min" -> 7.017 hours)
            $scheduledHours = $this->parseFormattedHours($record['scheduled_hours']);
            $totalScheduledHours += $scheduledHours;
            
            // Store details for display
            $scheduledHoursDetails[] = [
                'date' => $record['date_formatted'],
                'hours' => $record['scheduled_hours'],
                'decimal_hours' => $scheduledHours
            ];
        }
        
        // Calculate basic salary based on daily rate and scheduled hours
        // Formula: Basic Salary = Daily Rate × (Scheduled Hours / 8)
        $dailyRate = $employee->daily_rate;
        $basicSalary = $dailyRate * ($totalScheduledHours / 8);
        
        
        // Calculate hourly rate for reference
        $hourlyRate = $dailyRate / 8;
        
        return [
            'amount' => round($basicSalary, 2),
            'total_scheduled_hours' => $totalScheduledHours,
            'scheduled_hours_details' => $scheduledHoursDetails,
            'daily_rate' => $dailyRate,
            'hourly_rate' => $hourlyRate
        ];
    }

    /**
     * Calculate overtime from comprehensive records
     */
    private function calculateOvertimeFromRecords(Employee $employee, $employeeRecords): array
    {
        $totalOvertimeHours = $employeeRecords->sum('overtime');
        $overtimePay = $totalOvertimeHours * $employee->overtime_rate;

        return [
            'overtime_hours' => $totalOvertimeHours,
            'overtime_rate' => $employee->overtime_rate,
            'overtime_pay' => round($overtimePay, 2)
        ];
    }

    /**
     * Calculate night differential from comprehensive records
     */
    private function calculateNightDifferentialFromRecords(Employee $employee, $employeeRecords): array
    {
        $nightShiftHours = $employeeRecords->sum('night_differential_hours');
        $nightDifferentialPay = $nightShiftHours * $employee->night_differential_rate;

        return [
            'night_differential_hours' => $nightShiftHours,
            'night_differential_rate' => $employee->night_differential_rate,
            'night_differential_pay' => round($nightDifferentialPay, 2)
        ];
    }

    /**
     * Calculate holiday pay from comprehensive records
     */
    private function calculateHolidayPayFromRecords(Employee $employee, $employeeRecords): array
    {
        $regularHolidayDays = $employeeRecords->where('schedule_status', 'Regular Holiday')->count();
        $specialHolidayDays = $employeeRecords->where('schedule_status', 'Special Holiday')->count();
        
        // Calculate holiday premium based on full days (8 hours per day)
        // Regular holiday: 100% premium on full days (8 hours per day)
        $regularHolidayPremium = $regularHolidayDays * $employee->daily_rate; // 100% holiday premium per day
        
        // Special holiday: 30% premium on full days (8 hours per day)
        $specialHolidayPremium = $specialHolidayDays * $employee->daily_rate * 0.3; // 30% holiday premium per day
        
        // Basic pay for holidays (will be added to basic salary column)
        $holidayBasicPay = ($regularHolidayDays + $specialHolidayDays) * $employee->daily_rate;
        
        // Total holiday pay = basic pay + premium (for display purposes)
        $totalHolidayPay = $holidayBasicPay + $regularHolidayPremium + $specialHolidayPremium;
        

        return [
            'regular_holiday_days' => $regularHolidayDays,
            'special_holiday_days' => $specialHolidayDays,
            'holiday_basic_pay' => round($holidayBasicPay, 2),
            'holiday_premium' => round($regularHolidayPremium, 2),
            'special_holiday_premium' => round($specialHolidayPremium, 2),
            'holiday_pay' => round($totalHolidayPay, 2)
        ];
    }

    /**
     * Calculate bonuses from comprehensive records
     */
    private function calculateBonusesFromRecords(Employee $employee, $employeeRecords): float
    {
        $bonuses = 0;
        
        // Perfect attendance bonus
        $totalWorkingDays = $employeeRecords->where('schedule_status', 'Working')->count();
        $presentDays = $employeeRecords->where('attendance_status', 'Present')->count();
        
        if ($totalWorkingDays > 0 && $presentDays == $totalWorkingDays) {
            $bonuses += 500; // Perfect attendance bonus
        }
        
        // Performance bonus (example: based on overtime hours)
        $totalOvertimeHours = $employeeRecords->sum('overtime');
        if ($totalOvertimeHours > 20) {
            $bonuses += 300; // Overtime performance bonus
        }
        
        return $bonuses;
    }

    /**
     * Calculate deductions from comprehensive records
     */
    private function calculateDeductionsFromRecords(Employee $employee, $employeeRecords): float
    {
        $deductions = 0;
        
        // Late deductions - based on basic salary per minute
        $totalLateMinutes = $employeeRecords->sum('late_minutes');
        if ($totalLateMinutes > 0) {
            // Calculate rate per minute: Daily rate / (8 hours * 60 minutes)
            $minutesPerDay = 8 * 60; // 480 minutes per day
            $ratePerMinute = $employee->daily_rate / $minutesPerDay;
            $deductions += $totalLateMinutes * $ratePerMinute;
        }
        
        // Error deductions (incomplete time records)
        $errorDays = $employeeRecords->where('attendance_status', 'Error')->count();
        $deductions += $errorDays * $employee->daily_rate; // Full day deduction for error
        
        return $deductions;
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
     * Get payroll summary for a period
     *
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return array
     */
    public function getPayrollSummary(Carbon $startDate, Carbon $endDate): array
    {
        $payrolls = Payroll::where('pay_period_start', $startDate->format('Y-m-d'))
            ->where('pay_period_end', $endDate->format('Y-m-d'))
            ->get();

        return [
            'total_employees' => $payrolls->count(),
            'total_gross_pay' => $payrolls->sum('gross_pay'),
            'total_overtime_hours' => $payrolls->sum('overtime_hours'),
            'total_deductions' => $payrolls->sum('deductions'),
            'total_net_pay' => $payrolls->sum('net_pay'),
        ];
    }
}
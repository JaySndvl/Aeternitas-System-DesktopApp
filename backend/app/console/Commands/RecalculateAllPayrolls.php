<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Payroll;
use App\Models\Employee;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RecalculateAllPayrolls extends Command
{
    protected $signature = 'payroll:recalculate-all {--force : Force recalculation}';
    protected $description = 'Recalculate all payroll records with proper values';

    public function handle()
    {
        $this->info('Recalculating all payroll records...');
        
        // Get all payrolls
        $payrolls = Payroll::with('employee')->get();
        
        $this->info("Found {$payrolls->count()} payroll records");
        
        if (!$this->option('force')) {
            if (!$this->confirm('This will recalculate ALL payroll records. Continue?')) {
                return;
            }
        }
        
        $updated = 0;
        $skipped = 0;
        
        $bar = $this->output->createProgressBar($payrolls->count());
        
        foreach ($payrolls as $payroll) {
            try {
                $employee = $payroll->employee;
                
                if (!$employee) {
                    $bar->advance();
                    $skipped++;
                    continue;
                }
                
                // Skip if already has valid data (optional)
                if ($payroll->basic_salary > 0 && $payroll->gross_pay > 0 && !$this->option('force')) {
                    $bar->advance();
                    $skipped++;
                    continue;
                }
                
                // Recalculate based on employee salary
                $basicSalary = $employee->salary;
                
                // Calculate overtime pay
                $overtimePay = ($payroll->overtime_hours ?? 0) * ($payroll->overtime_rate ?? 0);
                
                // Calculate other components
                $nightDiffPay = ($payroll->night_differential_hours ?? 0) * ($payroll->night_differential_rate ?? 0);
                
                // Calculate gross pay
                $grossPay = $basicSalary 
                    + $overtimePay
                    + $nightDiffPay
                    + ($payroll->rest_day_premium_pay ?? 0)
                    + ($payroll->allowances ?? 0)
                    + ($payroll->bonuses ?? 0)
                    + ($payroll->holiday_basic_pay ?? 0)
                    + ($payroll->holiday_premium ?? 0)
                    + ($payroll->special_holiday_premium ?? 0);
                
                // Calculate net pay
                $netPay = $grossPay - ($payroll->deductions ?? 0) - ($payroll->tax_amount ?? 0);
                
                // Update payroll
                $payroll->update([
                    'basic_salary' => $basicSalary,
                    'overtime_pay' => $overtimePay,
                    'night_differential_pay' => $nightDiffPay,
                    'gross_pay' => $grossPay,
                    'net_pay' => $netPay,
                ]);
                
                $updated++;
                $bar->advance();
                
            } catch (\Exception $e) {
                Log::error('Failed to recalculate payroll ' . $payroll->id . ': ' . $e->getMessage());
                $bar->advance();
                $skipped++;
            }
        }
        
        $bar->finish();
        $this->newLine();
        $this->info("✅ Successfully updated {$updated} payroll records");
        $this->info("⏭️  Skipped {$skipped} records");
        
        return Command::SUCCESS;
    }
}
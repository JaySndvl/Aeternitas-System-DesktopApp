<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Payroll;
use App\Models\Employee;

class FixZeroPayrolls extends Command
{
    protected $signature = 'payroll:fix-zero {--recalculate : Recalculate all payrolls}';
    protected $description = 'Fix payroll records with zero values';

    public function handle()
    {
        $this->info('Checking payroll records...');
        
        // Find payrolls with zero values
        $payrolls = Payroll::where('basic_salary', 0)->get();
        
        $this->info("Found {$payrolls->count()} payrolls with zero values");
        
        if ($this->option('recalculate')) {
            if (!$this->confirm('Recalculate ALL payrolls? This will update all records.')) {
                return;
            }
            
            $payrolls = Payroll::all();
        }
        
        $bar = $this->output->createProgressBar($payrolls->count());
        
        foreach ($payrolls as $payroll) {
            $employee = $payroll->employee;
            
            if (!$employee) {
                $bar->advance();
                continue;
            }
            
            // Calculate actual values based on employee salary
            $basicSalary = $employee->salary;
            $overtimePay = ($payroll->overtime_hours ?? 0) * ($payroll->overtime_rate ?? 0);
            $grossPay = $basicSalary 
                + $overtimePay 
                + ($payroll->bonuses ?? 0)
                + ($payroll->allowances ?? 0);
            
            $netPay = $grossPay - ($payroll->deductions ?? 0) - ($payroll->tax_amount ?? 0);
            
            // Update the payroll
            $payroll->update([
                'basic_salary' => $basicSalary,
                'gross_pay' => $grossPay,
                'net_pay' => $netPay
            ]);
            
            $bar->advance();
        }
        
        $bar->finish();
        $this->newLine();
        $this->info('Successfully fixed ' . $payrolls->count() . ' payroll records.');
        
        return Command::SUCCESS;
    }
}
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Payroll;

class FixPayrollOvertime extends Command
{
    protected $signature = 'payroll:fix-overtime';
    protected $description = 'Fix overtime_pay calculation for existing payroll records';

    public function handle()
    {
        $this->info('Starting to fix payroll overtime calculations...');
        
        $payrolls = Payroll::where('overtime_hours', '>', 0)->get();
        
        $bar = $this->output->createProgressBar($payrolls->count());
        
        foreach ($payrolls as $payroll) {
            // Calculate correct overtime pay
            $overtimePay = $payroll->overtime_hours * $payroll->overtime_rate;
            
            // Update the record
            $payroll->update([
                'overtime_pay' => round($overtimePay, 2)
            ]);
            
            $bar->advance();
        }
        
        $bar->finish();
        $this->newLine();
        $this->info('Successfully fixed ' . $payrolls->count() . ' payroll records.');
        
        return Command::SUCCESS;
    }
}
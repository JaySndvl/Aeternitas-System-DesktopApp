<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Models\Payroll;

class ViewServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Share latest payroll with all views for employees
        View::composer('*', function ($view) {
            $user = auth()->user();
            
            if ($user && $user->role === 'employee' && $user->employee) {
                $latestPayroll = Payroll::where('employee_id', $user->employee->id)
                    ->whereIn('status', ['approved', 'processed', 'paid'])
                    ->latest()
                    ->first();
                    
                $view->with('latestEmployeePayroll', $latestPayroll);
            } else {
                // Make sure the variable exists even for non-employees
                $view->with('latestEmployeePayroll', null);
            }
        });
    }
}
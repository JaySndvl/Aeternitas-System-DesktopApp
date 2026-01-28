<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        // Register the payroll command
        \App\Console\Commands\RunPayrollGeneration::class,
    ];

    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule)
    {
        // Example placeholder — uncomment & modify to schedule payroll runs
        // $schedule->command('payroll:run --start=2025-11-01 --end=2025-11-15 --data=/path/to/data.json')->monthlyOn(1, '02:00');
    }

    /**
     * Register the commands for the application.
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }

    protected $routeMiddleware = [
    // ... other middleware
    'employee' => \App\Http\Middleware\EnsureUserIsEmployee::class,
    'log.login' => \App\Http\Middleware\LogUserLogin::class,
];


}
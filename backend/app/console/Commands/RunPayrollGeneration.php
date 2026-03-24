<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use App\Services\PayrollGenerationService;

class RunPayrollGeneration extends Command
{
    protected $signature = 'payroll:run
                            {--start= : Period start date (YYYY-MM-DD)}
                            {--end= : Period end date (YYYY-MM-DD)}
                            {--data= : Path to comprehensive data JSON file (optional)}
                            {--employees= : Comma separated employee IDs (optional)}
                            {--preview : If provided, only generate preview (do not persist)}';

    protected $description = 'Run payroll generation using PayrollGenerationService. Pass comprehensive data JSON file or allow the system to assemble it.';

    protected PayrollGenerationService $service;

    public function __construct(PayrollGenerationService $service)
    {
        parent::__construct();
        $this->service = $service;
    }

    public function handle()
    {
        $start = $this->option('start');
        $end = $this->option('end');
        $dataPath = $this->option('data');
        $employees = $this->option('employees');
        $previewOnly = $this->option('preview');

        if (!$start || !$end) {
            $this->error('start and end dates are required. Use --start=YYYY-MM-DD --end=YYYY-MM-DD');
            return 1;
        }

        $period = ['start_date' => $start, 'end_date' => $end];

        $comprehensiveData = [];
        if ($dataPath) {
            if (!File::exists($dataPath)) {
                $this->error("Data file not found: {$dataPath}");
                return 1;
            }
            $json = File::get($dataPath);
            $comprehensiveData = json_decode($json, true);
            if (!is_array($comprehensiveData)) {
                $this->error('Invalid JSON data file.');
                return 1;
            }
        } else {
            // If no data file is provided, try to assemble from AttendanceRecord model (best-effort)
            $this->info('No --data provided, attempting to assemble comprehensive data from AttendanceRecord.');
            $records = \App\Models\AttendanceRecord::whereBetween('date', [$start, $end])->get()->toArray();
            $comprehensiveData = $records;
        }

        $employeeIds = null;
        if ($employees) {
            $employeeIds = array_filter(array_map('trim', explode(',', $employees)));
        }

        if ($previewOnly) {
            $this->info('Generating payroll preview only (no database writes).');
            $preview = $this->service->generatePayrollPreview($period, $comprehensiveData, $employeeIds);
            $this->info('Preview items: ' . count($preview));
            $out = storage_path('app/payroll_preview_' . now()->format('Ymd_His') . '.json');
            file_put_contents($out, json_encode($preview, JSON_PRETTY_PRINT));
            $this->info("Preview saved to: {$out}");
            return 0;
        }

        $this->info('Generating payroll for period: ' . $start . ' to ' . $end);
        $created = $this->service->generatePayroll($period, $comprehensiveData, $employeeIds);

        $this->info('Payroll generation complete. Created: ' . count($created) . ' records.');
        return 0;
    }
}
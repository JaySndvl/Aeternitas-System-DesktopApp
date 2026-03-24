<?php

require_once 'vendor/autoload.php';

use App\Models\Employee;
use App\Models\AttendanceRecord;
use App\Models\EmployeeSchedule;
use App\Models\Department;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Raw timekeeping data
$timekeepingData = "
EMP-0001	Cerezo, Jerson Marg D.	09/09/2025				
		09/10/2025	7:42	7:42	7:42	17:09
		09/11/2025	7:47	7:47		
		09/12/2025	7:53	17:05		
		09/15/2025	7:46	17:09		
		09/16/2025				
		09/17/2025	7:49	17:07		
		09/18/2025	7:47	17:03		
		09/19/2025	7:45	17:11		
		09/20/2025				
		09/21/2025				
		09/22/2025	7:44	17:04		
		09/23/2025				
		09/24/2025	7:53	17:08		
		09/25/2025	7:50	17:11		
		09/26/2025	7:48	17:05		
		09/27/2025				
		09/28/2025				
		09/29/2025	7:56	17:10		
		09/30/2025				
		10/01/2025	7:50	17:14		
		10/02/2025	7:47	17:07		
		10/03/2025	7:52	17:01		
		10/04/2025				
		10/05/2025				
		10/06/2025				
		10/07/2025				
		10/08/2025				
		10/09/2025				
		10/10/2025				
		10/11/2025				
		10/12/2025				
		10/13/2025				
		10/14/2025				
		10/15/2025				
EMP-0002	Raga, Lowegie	09/09/2025	7:50	17:13		
		09/10/2025	7:43	17:09		
		09/11/2025				
		09/12/2025	7:48	17:07		
		09/15/2025	7:45	17:09		
		09/16/2025	7:51	17:05		
		09/17/2025	7:49	17:07		
		09/18/2025				
		09/19/2025	7:43	17:08		
		09/20/2025				
		09/21/2025				
		09/22/2025	7:50	17:10		
		09/23/2025	7:49	17:11		
		09/24/2025	7:57	17:05		
		09/25/2025				
		09/26/2025	7:53	17:08		
		09/27/2025				
		09/28/2025				
		09/29/2025	7:53	17:12		
		09/30/2025	7:45	17:08		
		10/01/2025	7:56	17:07		
		10/02/2025				
		10/03/2025	7:51	17:10		
		10/04/2025				
		10/05/2025				
		10/06/2025				
		10/07/2025				
		10/08/2025				
		10/09/2025				
		10/10/2025				
		10/11/2025				
		10/12/2025				
		10/13/2025				
		10/14/2025				
		10/15/2025				
EMP-0003	Estares, Alexander 	09/09/2025	7:46	17:03		
		09/10/2025				
		09/11/2025	7:47	17:09		
		09/12/2025	7:48	17:07		
		09/15/2025	7:49	17:07		
		09/16/2025	7:42	17:04		
		09/17/2025				
		09/18/2025	7:58	17:04		
		09/19/2025	7:52	17:11		
		09/20/2025				
		09/21/2025				
		09/22/2025	7:47	17:15		
		09/23/2025	7:43	17:12		
		09/24/2025				
		09/25/2025	7:47	17:05		
		09/26/2025	7:50	17:09		
		09/27/2025				
		09/28/2025				
		09/29/2025	7:55	17:16		
		09/30/2025	7:45	17:10		
		10/01/2025				
		10/02/2025	7:50	17:14		
		10/03/2025	7:50	17:12		
		10/04/2025				
		10/05/2025				
		10/06/2025				
		10/07/2025				
		10/08/2025				
		10/09/2025				
		10/10/2025				
		10/11/2025				
		10/12/2025				
		10/13/2025				
		10/14/2025				
		10/15/2025				
EMP-0004	Guiling, Curt Vincent	09/09/2025	7:49	17:04		
		09/10/2025	7:55	17:07		
		09/11/2025				
		09/12/2025				
		09/15/2025	7:47	17:10		
		09/16/2025	7:47	17:05		
		09/17/2025	7:52	17:06		
		09/18/2025				
		09/19/2025	7:51	17:09		
		09/20/2025				
		09/21/2025				
		09/22/2025	7:50	17:11		
		09/23/2025	7:58	17:15		
		09/24/2025	7:41	17:10		
		09/25/2025				
		09/26/2025	7:53	17:07		
		09/27/2025				
		09/28/2025				
		09/29/2025	7:41	17:10		
		09/30/2025	7:55	17:03		
		10/01/2025	7:49	17:06		
		10/02/2025				
		10/03/2025	7:47	17:05		
		10/04/2025				
		10/05/2025				
		10/06/2025				
		10/07/2025				
		10/08/2025				
		10/09/2025				
		10/10/2025				
		10/11/2025				
		10/12/2025				
		10/13/2025				
		10/14/2025				
		10/15/2025				
EMP-0005	Cawile, Charlie	09/09/2025				
		09/10/2025	7:57	17:06		
		09/11/2025	7:56	17:04		
		09/12/2025	7:47	17:07		
		09/15/2025	7:51	17:05		
		09/16/2025				
		09/17/2025	7:35	17:08		
		09/18/2025	7:49	17:06		
		09/19/2025	7:56	17:11		
		09/20/2025				
		09/21/2025				
		09/22/2025	7:50	17:06		
		09/23/2025				
		09/24/2025	7:53	17:11		
		09/25/2025	7:51	17:10		
		09/26/2025	7:56	17:09		
		09/27/2025				
		09/28/2025				
		09/29/2025	7:52	17:06		
		09/30/2025				
		10/01/2025	7:49	17:10		
		10/02/2025	7:45	17:08		
		10/03/2025	7:55	17:13		
		10/04/2025				
		10/05/2025				
		10/06/2025				
		10/07/2025				
		10/08/2025				
		10/09/2025				
		10/10/2025				
		10/11/2025				
		10/12/2025				
		10/13/2025				
		10/14/2025				
		10/15/2025				
EMP-0006	Bibaro, Reece Denver	09/09/2025	7:57	17:03		
		09/10/2025	7:49	17:07		
		09/11/2025	7:49	17:07		
		09/12/2025	7:53	17:06		
		09/15/2025				
		09/16/2025	7:47	17:07		
		09/17/2025	7:37	17:08		
		09/18/2025	7:53	17:05		
		09/19/2025	7:47	17:10		
		09/20/2025				
		09/21/2025				
		09/22/2025				
		09/23/2025	7:57	17:15		
		09/24/2025	7:37	17:08		
		09/25/2025	7:45	17:11		
		09/26/2025	7:44	17:10		
		09/27/2025				
		09/28/2025				
		09/29/2025				
		09/30/2025	7:34	17:19		
		10/01/2025	7:43	17:14		
		10/02/2025	7:55	17:09		
		10/03/2025	7:57	17:13		
		10/04/2025				
		10/05/2025				
		10/06/2025				
		10/07/2025	7:47	17:11		
		10/08/2025	7:40	17:14		
		10/09/2025	7:49	17:08		
		10/10/2025	7:51	17:13		
		10/11/2025				
		10/12/2025				
		10/13/2025				
		10/14/2025				
		10/15/2025				
EMP-0007	Plaza, Reyven	09/09/2025	7:43	17:07		
		09/10/2025	7:56	17:05		
		09/11/2025	7:49	17:10		
		09/12/2025				
		09/15/2025	7:46	17:05		
		09/16/2025	7:53	17:10		
		09/17/2025	7:51	17:07		
		09/18/2025	7:49	17:04		
		09/19/2025	7:50	17:08		
		09/20/2025				
		09/21/2025				
		09/22/2025	7:42	17:10		
		09/23/2025	7:55	17:09		
		09/24/2025	7:40	17:12		
		09/25/2025	7:44	17:05		
		09/26/2025				
		09/27/2025				
		09/28/2025				
		09/29/2025	7:50	17:15		
		09/30/2025	7:51	17:06		
		10/01/2025	7:47	17:14		
		10/02/2025	7:40	17:12		
		10/03/2025				
		10/04/2025				
		10/05/2025				
		10/06/2025	7:58	17:03		
		10/07/2025	7:55	17:05		
		10/08/2025	7:51	17:08		
		10/09/2025	7:48	17:06		
		10/10/2025				
		10/11/2025				
		10/12/2025				
		10/13/2025				
		10/14/2025				
		10/15/2025				
EMP-0008	Maria Sampalok	09/09/2025	7:43	17:07		
		09/10/2025	7:56	17:05		
		09/11/2025	7:49	17:10		
		09/12/2025				
		09/15/2025	7:46	17:05		
		09/16/2025	7:53	17:10		
		09/17/2025	7:51			
		09/18/2025	7:45	17:04		
		09/19/2025	7:49	17:11		
		09/20/2025				
		09/21/2025				
		09/22/2025				
		09/23/2025				
		09/24/2025				
		09/25/2025				
		09/26/2025				
		09/27/2025				
		09/28/2025				
		09/29/2025				
		09/30/2025				
		10/01/2025				
		10/02/2025				
		10/03/2025				
EMP-0009	Bulbasaur Poke	09/09/2025	7:43	17:07		
		09/10/2025	7:56	17:05		
		09/11/2025	7:49	17:10		
		09/12/2025				
		09/15/2025	7:46	17:05		
		09/16/2025	7:53	17:10		
		09/17/2025	7:51			
		09/18/2025	7:49	17:04		
		09/19/2025	8:49	17:04		
		09/20/2025				
		09/21/2025				
		09/22/2025				
		09/23/2025				
		09/24/2025				
		09/25/2025				
		09/26/2025				
		09/27/2025				
		09/28/2025				
		09/29/2025				
		09/30/2025				
		10/01/2025				
		10/02/2025				
		10/03/2025				
";

function parseTimekeepingData($data) {
    $lines = explode("\n", trim($data));
    $employees = [];
    $currentEmployee = null;
    
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line)) continue;
        
        // Check if line starts with EMP- (new employee)
        if (preg_match('/^(EMP-\d+)\s+(.+?)\s+(\d{2}\/\d{2}\/\d{4})/', $line, $matches)) {
            $employeeCode = $matches[1];
            $employeeName = trim($matches[2]);
            $firstDate = $matches[3];
            
            $currentEmployee = [
                'code' => $employeeCode,
                'name' => $employeeName,
                'records' => []
            ];
            
            // Parse the first date if it has time data
            $timeData = preg_split('/\s+/', $line);
            if (count($timeData) >= 6) {
                $timeIn = $timeData[3] ?? null;
                $timeOut = $timeData[4] ?? null;
                
                if ($timeIn && $timeOut) {
                    $currentEmployee['records'][] = [
                        'date' => $firstDate,
                        'time_in' => $timeIn,
                        'time_out' => $timeOut
                    ];
                }
            }
            
            $employees[] = $currentEmployee;
        }
        // Check if line starts with date (continuation of current employee)
        elseif ($currentEmployee && preg_match('/^(\d{2}\/\d{2}\/\d{4})/', $line, $matches)) {
            $date = $matches[1];
            $timeData = preg_split('/\s+/', $line);
            
            $timeIn = null;
            $timeOut = null;
            
            // Look for time patterns in the line
            for ($i = 1; $i < count($timeData); $i++) {
                if (preg_match('/^\d{1,2}:\d{2}$/', $timeData[$i])) {
                    if ($timeIn === null) {
                        $timeIn = $timeData[$i];
                    } elseif ($timeOut === null) {
                        $timeOut = $timeData[$i];
                        break;
                    }
                }
            }
            
            if ($timeIn && $timeOut) {
                $currentEmployee['records'][] = [
                    'date' => $date,
                    'time_in' => $timeIn,
                    'time_out' => $timeOut
                ];
            }
        }
    }
    
    return $employees;
}

function convertDateFormat($dateStr) {
    // Convert MM/DD/YYYY to YYYY-MM-DD
    $parts = explode('/', $dateStr);
    if (count($parts) === 3) {
        return $parts[2] . '-' . str_pad($parts[0], 2, '0', STR_PAD_LEFT) . '-' . str_pad($parts[1], 2, '0', STR_PAD_LEFT);
    }
    return null;
}

function convertTimeFormat($timeStr, $dateStr) {
    // Convert H:MM to YYYY-MM-DD H:MM:SS
    $date = convertDateFormat($dateStr);
    if ($date && $timeStr) {
        return $date . ' ' . $timeStr . ':00';
    }
    return null;
}

function getOrCreateDepartment() {
    // Get or create a default department
    $department = Department::where('name', 'General')->first();
    if (!$department) {
        $department = Department::create([
            'name' => 'General',
            'description' => 'General Department for imported employees',
            'location' => 'Main Office',
            'budget' => 1000000.00 // PHP 1M budget
        ]);
        echo "Created department: General\n";
    } else {
        echo "Using existing department: General\n";
    }
    return $department;
}

function getOrCreateEmployee($employeeData, $department) {
    // Check if employee exists by employee_id
    $employee = Employee::where('employee_id', $employeeData['code'])->first();
    
    if (!$employee) {
        // Parse name
        $nameParts = explode(',', $employeeData['name']);
        $lastName = trim($nameParts[0]);
        $firstName = isset($nameParts[1]) ? trim($nameParts[1]) : 'Unknown';
        
        $employee = Employee::create([
            'employee_id' => $employeeData['code'],
            'first_name' => $firstName,
            'last_name' => $lastName,
            'phone' => null,
            'department_id' => $department->id,
            'position' => 'Employee',
            'salary' => 0,
            'hire_date' => '2025-01-01'
        ]);
        
        echo "Created employee: {$employeeData['code']} - {$employeeData['name']}\n";
    } else {
        echo "Found existing employee: {$employeeData['code']} - {$employeeData['name']}\n";
    }
    
    return $employee;
}

function insertAttendanceData($employees) {
    $department = getOrCreateDepartment();
    $insertedRecords = 0;
    $insertedSchedules = 0;
    
    foreach ($employees as $employeeData) {
        $employee = getOrCreateEmployee($employeeData, $department);
        
        foreach ($employeeData['records'] as $record) {
            $date = convertDateFormat($record['date']);
            $timeIn = convertTimeFormat($record['time_in'], $record['date']);
            $timeOut = convertTimeFormat($record['time_out'], $record['date']);
            
            if (!$date || !$timeIn || !$timeOut) {
                echo "Skipping invalid record for {$employeeData['code']} on {$record['date']}\n";
                continue;
            }
            
            // Check if attendance record already exists
            $existingRecord = AttendanceRecord::where('employee_id', $employee->id)
                ->where('date', $date)
                ->first();
            
            if (!$existingRecord) {
                // Create attendance record
                AttendanceRecord::create([
                    'employee_id' => $employee->id,
                    'date' => $date,
                    'time_in' => $timeIn,
                    'time_out' => $timeOut,
                    'status' => 'present',
                    'total_hours' => 8.0, // Default 8 hours
                    'regular_hours' => 8.0,
                    'overtime_hours' => 0.0
                ]);
                $insertedRecords++;
            }
            
            // Check if schedule already exists
            $existingSchedule = EmployeeSchedule::where('employee_id', $employee->id)
                ->where('date', $date)
                ->first();
            
            if (!$existingSchedule) {
                // Create employee schedule (default working schedule)
                EmployeeSchedule::create([
                    'employee_id' => $employee->id,
                    'department_id' => $department->id,
                    'date' => $date,
                    'time_in' => '08:00:00',
                    'time_out' => '17:00:00',
                    'status' => 'Working'
                ]);
                $insertedSchedules++;
            }
        }
    }
    
    return ['records' => $insertedRecords, 'schedules' => $insertedSchedules];
}

try {
    echo "Starting timekeeping data insertion...\n";
    
    // Parse the data
    $employees = parseTimekeepingData($timekeepingData);
    echo "Parsed " . count($employees) . " employees\n";
    
    // Insert data
    $result = insertAttendanceData($employees);
    
    echo "\nInsertion completed!\n";
    echo "Inserted {$result['records']} attendance records\n";
    echo "Inserted {$result['schedules']} employee schedules\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

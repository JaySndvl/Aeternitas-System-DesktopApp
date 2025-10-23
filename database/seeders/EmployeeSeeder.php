<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Employee;
use App\Models\Department;
use Carbon\Carbon;

class EmployeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get department IDs
        $itDepartment = Department::where('name', 'Information Technology')->first();
        $hrDepartment = Department::where('name', 'Human Resources')->first();

        $employees = [
            [
                'id' => '22ff1b14-2301-4a27-a022-6736b3c9f318',
                'employee_id' => 'EMP-0001',
                'first_name' => 'Jerson Marg',
                'last_name' => 'Cerezo',
                'phone' => '09',
                'department_id' => $itDepartment->id,
                'position' => 'Software Developer',
                'salary' => 18000.00,
                'hire_date' => '2025-09-08',
                'created_at' => Carbon::parse('2025-10-05 15:33:26'),
                'updated_at' => Carbon::parse('2025-10-05 15:33:26'),
            ],
            [
                'id' => 'b19c96de-91d9-4bcd-a0b6-2010bc7dae8c',
                'employee_id' => 'EMP-0002',
                'first_name' => 'Lowegie',
                'last_name' => 'Raga',
                'phone' => '09',
                'department_id' => $itDepartment->id,
                'position' => 'Software Developer',
                'salary' => 18000.00,
                'hire_date' => '2025-09-08',
                'created_at' => Carbon::parse('2025-10-05 15:36:36'),
                'updated_at' => Carbon::parse('2025-10-05 15:36:36'),
            ],
            [
                'id' => '75282d62-51f2-41a9-a5e3-6b4b6838a5f7',
                'employee_id' => 'EMP-0003',
                'first_name' => 'Alexander',
                'last_name' => 'Estares',
                'phone' => '09',
                'department_id' => $itDepartment->id,
                'position' => 'Software Developer',
                'salary' => 18000.00,
                'hire_date' => '2025-09-08',
                'created_at' => Carbon::parse('2025-10-05 15:37:41'),
                'updated_at' => Carbon::parse('2025-10-05 15:37:41'),
            ],
            [
                'id' => 'b5b39e80-36cc-4f35-abaf-20272f5a461c',
                'employee_id' => 'EMP-0004',
                'first_name' => 'Curt Vincent',
                'last_name' => 'Guiling',
                'phone' => '09',
                'department_id' => $itDepartment->id,
                'position' => 'QA',
                'salary' => 15000.00,
                'hire_date' => '2025-09-08',
                'created_at' => Carbon::parse('2025-10-05 15:39:11'),
                'updated_at' => Carbon::parse('2025-10-05 15:39:11'),
            ],
            [
                'id' => '0c6fcadb-5d42-453e-b85e-04df54d4b42b',
                'employee_id' => 'EMP-0005',
                'first_name' => 'Charlie',
                'last_name' => 'Cawile',
                'phone' => '09',
                'department_id' => $itDepartment->id,
                'position' => 'QA',
                'salary' => 15000.00,
                'hire_date' => '2025-09-08',
                'created_at' => Carbon::parse('2025-10-05 15:39:54'),
                'updated_at' => Carbon::parse('2025-10-05 15:39:54'),
            ],
            [
                'id' => 'c413f98e-d91b-4c61-b4d4-eb00e40a34b1',
                'employee_id' => 'EMP-0006',
                'first_name' => 'Reece',
                'last_name' => 'Bibaro',
                'phone' => '09',
                'department_id' => $itDepartment->id,
                'position' => 'QA',
                'salary' => 15000.00,
                'hire_date' => '2025-09-08',
                'created_at' => Carbon::parse('2025-10-05 15:40:28'),
                'updated_at' => Carbon::parse('2025-10-05 15:40:28'),
            ],
            [
                'id' => '243e7606-9542-4c71-867c-05bea5e658d3',
                'employee_id' => 'EMP-0007',
                'first_name' => 'Reyven',
                'last_name' => 'Plaza',
                'phone' => '09',
                'department_id' => $itDepartment->id,
                'position' => 'QA',
                'salary' => 15000.00,
                'hire_date' => '2025-09-08',
                'created_at' => Carbon::parse('2025-10-05 15:41:02'),
                'updated_at' => Carbon::parse('2025-10-05 15:41:02'),
            ],
            [
                'id' => 'b5aecc58-69dc-4d2c-b435-d8f13a9416ef',
                'employee_id' => 'EMP-0008',
                'first_name' => 'Maria',
                'last_name' => 'Sampalok',
                'phone' => '09',
                'department_id' => $itDepartment->id,
                'position' => 'QA',
                'salary' => 15000.00,
                'hire_date' => '2025-09-08',
                'created_at' => Carbon::parse('2025-10-05 17:08:30'),
                'updated_at' => Carbon::parse('2025-10-05 17:08:30'),
            ],
            [
                'id' => '41c930c9-40d9-4e8b-9575-87e1ae3c8ab7',
                'employee_id' => 'EMP-0009',
                'first_name' => 'Bulbasaur',
                'last_name' => 'Poke',
                'phone' => '09',
                'department_id' => $itDepartment->id,
                'position' => 'Software Developer',
                'salary' => 18000.00,
                'hire_date' => '2025-09-08',
                'created_at' => Carbon::parse('2025-10-05 17:09:28'),
                'updated_at' => Carbon::parse('2025-10-05 17:09:28'),
            ],
            [
                'id' => '4a189262-87df-48a6-8155-97fbe315e830',
                'employee_id' => 'EMP-0010',
                'first_name' => 'Balingka',
                'last_name' => 'Kalat',
                'phone' => '09',
                'department_id' => $hrDepartment->id,
                'position' => 'Hr Manager',
                'salary' => 20000.00,
                'hire_date' => '2025-09-09',
                'created_at' => Carbon::parse('2025-10-09 15:44:09'),
                'updated_at' => Carbon::parse('2025-10-09 15:44:09'),
            ],
        ];

        foreach ($employees as $employee) {
            Employee::create($employee);
        }

        $this->command->info('Employees created successfully!');
    }
}

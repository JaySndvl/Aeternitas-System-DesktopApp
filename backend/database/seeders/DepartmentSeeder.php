<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Department;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $departments = [
            [
                'name' => 'Human Resources',
                'description' => 'Manages employee relations, recruitment, and HR policies',
                'location' => 'Main Office - Floor 2',
                'budget' => 2500000.00, // PHP 2.5M
            ],
            [
                'name' => 'Information Technology',
                'description' => 'Handles all IT infrastructure, software development, and technical support',
                'location' => 'Main Office - Floor 3',
                'budget' => 3750000.00, // PHP 3.75M
            ],
            [
                'name' => 'Finance',
                'description' => 'Manages financial planning, accounting, and budget oversight',
                'location' => 'Main Office - Floor 1',
                'budget' => 3000000.00, // PHP 3M
            ],
            [
                'name' => 'Marketing',
                'description' => 'Responsible for brand management, advertising, and market research',
                'location' => 'Main Office - Floor 2',
                'budget' => 2000000.00, // PHP 2M
            ],
            [
                'name' => 'Operations',
                'description' => 'Oversees daily business operations and process improvement',
                'location' => 'Main Office - Floor 1',
                'budget' => 4000000.00, // PHP 4M
            ],
        ];

        foreach ($departments as $department) {
            Department::create($department);
        }

        $this->command->info('Departments created successfully!');
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PositionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $departments = \App\Models\Department::all()->keyBy('name');

        $positions = [
            [
                'name' => 'Manager',
                'code' => 'MGR',
                'description' => 'Oversees team operations and strategic planning',
                'level' => 'Senior',
                'department_id' => $departments['Human Resources']->id ?? null,
                'min_salary' => 50000.00,
                'max_salary' => 80000.00,
                'requirements' => json_encode([
                    'Bachelor\'s degree in Business or related field',
                    '5+ years of management experience',
                    'Strong leadership skills',
                    'Excellent communication skills'
                ]),
                'responsibilities' => json_encode([
                    'Lead and manage team members',
                    'Develop and implement strategies',
                    'Monitor team performance',
                    'Make key business decisions'
                ])
            ],
            // ...repeat for all other positions, mapping department_id from $departments...
        ];

        foreach ($positions as $position) {
            if ($position['department_id']) {
                \App\Models\Position::create($position);
            }
        }
    }
}

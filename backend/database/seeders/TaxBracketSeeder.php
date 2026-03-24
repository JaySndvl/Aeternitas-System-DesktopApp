<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\TaxBracket;
use Carbon\Carbon;

class TaxBracketSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing tax brackets
        TaxBracket::truncate();
        
        $brackets = [
            [
                'name' => 'Exempt',
                'description' => 'Up to ₱20,833 - Exempt from tax',
                'min_income' => 0,
                'max_income' => 20833,
                'tax_rate' => 0,
                'base_tax' => 0,
                'excess_over' => 0,
                'sort_order' => 1,
            ],
            [
                'name' => '15% Bracket',
                'description' => '₱20,834 – ₱33,333 - 15% of the excess over ₱20,833',
                'min_income' => 20834,
                'max_income' => 33333,
                'tax_rate' => 15,
                'base_tax' => 0,
                'excess_over' => 20833,
                'sort_order' => 2,
            ],
            [
                'name' => '20% Bracket',
                'description' => '₱33,334 – ₱66,667 - ₱1,875 + 20% of the excess over ₱33,333',
                'min_income' => 33334,
                'max_income' => 66667,
                'tax_rate' => 20,
                'base_tax' => 1875,
                'excess_over' => 33333,
                'sort_order' => 3,
            ],
            [
                'name' => '25% Bracket',
                'description' => '₱66,668 – ₱166,667 - ₱8,541.80 + 25% of the excess over ₱66,667',
                'min_income' => 66668,
                'max_income' => 166667,
                'tax_rate' => 25,
                'base_tax' => 8541.80,
                'excess_over' => 66667,
                'sort_order' => 4,
            ],
            [
                'name' => '30% Bracket',
                'description' => '₱166,668 – ₱666,667 - ₱33,541.80 + 30% of the excess over ₱166,667',
                'min_income' => 166668,
                'max_income' => 666667,
                'tax_rate' => 30,
                'base_tax' => 33541.80,
                'excess_over' => 166667,
                'sort_order' => 5,
            ],
            [
                'name' => '35% Bracket',
                'description' => 'Over ₱666,667 - ₱183,541.80 + 35% of the excess over ₱666,667',
                'min_income' => 666668,
                'max_income' => null,
                'tax_rate' => 35,
                'base_tax' => 183541.80,
                'excess_over' => 666667,
                'sort_order' => 6,
            ],
        ];
        
        foreach ($brackets as $bracketData) {
            TaxBracket::create(array_merge($bracketData, [
                'is_active' => true,
                'effective_from' => Carbon::now()->startOfYear(),
            ]));
        }
        
        $this->command->info('Tax brackets seeded successfully!');
    }
}

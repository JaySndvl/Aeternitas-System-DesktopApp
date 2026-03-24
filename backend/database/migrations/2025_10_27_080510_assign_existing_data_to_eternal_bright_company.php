<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Find or create "Eternal Bright" company
        $company = DB::table('companies')
            ->where('name', 'Eternal Bright')
            ->orWhere('name', 'LIKE', '%Eternal Bright%')
            ->orWhere('name', 'LIKE', '%eternal bright%')
            ->first();

        if (!$company) {
            // Create the company if it doesn't exist
            $companyId = \Ramsey\Uuid\Uuid::uuid4()->toString();
            DB::table('companies')->insert([
                'id' => $companyId,
                'name' => 'Eternal Bright',
                'code' => 'EB' . time(),
                'description' => 'Eternal Bright Company',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            $companyId = $company->id;
        }

        // Update all departments to belong to Eternal Bright
        DB::table('departments')
            ->whereNull('company_id')
            ->update(['company_id' => $companyId]);

        // Update all employees to belong to Eternal Bright
        DB::table('employees')
            ->whereNull('company_id')
            ->update(['company_id' => $companyId]);

        // Update all positions to belong to Eternal Bright
        DB::table('positions')
            ->whereNull('company_id')
            ->update(['company_id' => $companyId]);

        echo "Assigned all existing records to Eternal Bright company (ID: {$companyId})\n";
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Clear company assignments (set to null)
        DB::table('departments')->update(['company_id' => null]);
        DB::table('employees')->update(['company_id' => null]);
        DB::table('positions')->update(['company_id' => null]);
    }
};

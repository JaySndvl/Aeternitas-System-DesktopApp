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
        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE hr_contacts MODIFY category ENUM('attendance','leave','payroll','benefits','schedule','general','complaint','request') NOT NULL DEFAULT 'general'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE hr_contacts MODIFY category ENUM('leave','payroll','benefits','schedule','general','complaint','request') NOT NULL DEFAULT 'general'");
        }
    }
};

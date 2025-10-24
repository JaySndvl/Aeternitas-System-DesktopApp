<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add 'Absent' to the employee_schedules status enum
        DB::statement("ALTER TABLE employee_schedules MODIFY COLUMN status ENUM('Working', 'Day Off', 'Leave', 'Absent', 'Regular Holiday', 'Special Holiday', 'Overtime') DEFAULT 'Working'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove 'Absent' from the employee_schedules status enum
        DB::statement("ALTER TABLE employee_schedules MODIFY COLUMN status ENUM('Working', 'Day Off', 'Leave', 'Regular Holiday', 'Special Holiday', 'Overtime') DEFAULT 'Working'");
    }
};

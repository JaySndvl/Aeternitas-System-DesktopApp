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
        // Update the enum values for attendance_records status column
        DB::statement("ALTER TABLE attendance_records MODIFY COLUMN status ENUM('present', 'absent', 'absent_excused', 'absent_unexcused', 'absent_sick', 'absent_personal', 'late', 'half_day', 'on_leave') DEFAULT 'absent'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to original enum values
        DB::statement("ALTER TABLE attendance_records MODIFY COLUMN status ENUM('present', 'absent', 'late', 'half_day', 'on_leave') DEFAULT 'absent'");
    }
};

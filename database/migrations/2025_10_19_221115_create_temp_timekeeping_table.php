<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('temp_timekeeping', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('employee_id'); // Employee ID from CSV/Excel
            $table->string('employee_name')->nullable(); // Employee name from CSV/Excel
            $table->date('date');
            $table->timestamp('time_in')->nullable();
            $table->timestamp('time_out')->nullable();
            $table->timestamp('break_start')->nullable();
            $table->timestamp('break_end')->nullable();
            $table->decimal('total_hours', 8, 2)->default(0);
            $table->decimal('regular_hours', 8, 2)->default(0);
            $table->decimal('overtime_hours', 8, 2)->default(0);
            $table->enum('status', ['present', 'absent', 'late', 'half_day', 'on_leave', 'day_off', 'holiday', 'error'])->default('absent');
            $table->string('schedule_status')->nullable(); // Working, Day Off, Leave, Holiday, Overtime
            $table->text('notes')->nullable();
            $table->text('validation_errors')->nullable(); // Store any validation errors
            $table->string('import_batch_id')->nullable(); // To group records from same import
            $table->boolean('is_processed')->default(false); // Flag to track if record has been processed
            $table->timestamps();
            
            $table->index(['employee_id', 'date']);
            $table->index(['import_batch_id']);
            $table->index(['is_processed']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('temp_timekeeping');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * This table stores multiple time in/out entries per day for an employee.
     * Each attendance_record can have multiple time_entries.
     */
    public function up(): void
    {
        Schema::create('time_entries', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('attendance_record_id');
            $table->timestamp('time_in');
            $table->timestamp('time_out')->nullable();
            $table->decimal('hours_worked', 8, 2)->default(0);
            $table->enum('entry_type', ['regular', 'overtime', 'makeup'])->default('regular');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('attendance_record_id')
                ->references('id')
                ->on('attendance_records')
                ->onDelete('cascade');
            
            $table->index(['attendance_record_id', 'time_in']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('time_entries');
    }
};

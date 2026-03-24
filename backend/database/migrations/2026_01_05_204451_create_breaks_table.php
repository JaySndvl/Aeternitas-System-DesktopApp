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
        Schema::create('employee_breaks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('attendance_record_id');
            $table->timestamp('break_start');
            $table->timestamp('break_end')->nullable();
            $table->integer('break_duration_minutes')->nullable(); // Calculated when break ends
            $table->timestamps();

            $table->foreign('attendance_record_id')->references('id')->on('attendance_records')->onDelete('cascade');
            $table->index(['attendance_record_id', 'break_start']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_breaks');
    }
};

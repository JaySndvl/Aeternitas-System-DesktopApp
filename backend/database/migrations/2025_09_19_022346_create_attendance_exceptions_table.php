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
        Schema::create('attendance_exceptions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->date('date');
            $table->enum('type', ['holiday', 'company_event', 'emergency_closure', 'special_workday']);
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_paid')->default(true);
            $table->boolean('is_working_day')->default(false);
            $table->timestamps();

            $table->unique(['date', 'type']);
            $table->index(['date']);
            $table->index(['type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_exceptions');
    }
};

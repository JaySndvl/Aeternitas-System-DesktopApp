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
        Schema::create('leave_balances', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('employee_id');
            $table->integer('year');
            $table->integer('vacation_days_total')->default(15);
            $table->integer('vacation_days_used')->default(0);
            $table->integer('sick_days_total')->default(10);
            $table->integer('sick_days_used')->default(0);
            $table->integer('personal_days_total')->default(5);
            $table->integer('personal_days_used')->default(0);
            $table->integer('emergency_days_total')->default(3);
            $table->integer('emergency_days_used')->default(0);
            $table->integer('maternity_days_total')->default(0);
            $table->integer('maternity_days_used')->default(0);
            $table->integer('paternity_days_total')->default(0);
            $table->integer('paternity_days_used')->default(0);
            $table->integer('bereavement_days_total')->default(0);
            $table->integer('bereavement_days_used')->default(0);
            $table->integer('study_days_total')->default(0);
            $table->integer('study_days_used')->default(0);
            $table->timestamps();

            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
            $table->unique(['employee_id', 'year']);
            $table->index(['year']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_balances');
    }
};

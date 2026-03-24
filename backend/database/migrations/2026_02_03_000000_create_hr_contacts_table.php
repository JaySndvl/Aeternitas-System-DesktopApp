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
        Schema::create('hr_contacts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('employee_id')->nullable();
            $table->uuid('user_id');
            $table->string('subject');
            $table->longText('message');
            $table->enum('category', ['leave', 'payroll', 'benefits', 'schedule', 'general', 'complaint', 'request'])->default('general');
            $table->enum('status', ['pending', 'in_progress', 'resolved', 'closed'])->default('pending');
            $table->longText('response')->nullable();
            $table->uuid('responded_by')->nullable();
            $table->timestamp('responded_at')->nullable();
            $table->timestamps();

            // Foreign keys
            $table->foreign('employee_id')->references('id')->on('employees')->nullOnDelete();
            $table->foreign('user_id')->references('id')->on('accounts')->onDelete('cascade');
            $table->foreign('responded_by')->references('id')->on('accounts')->nullOnDelete();

            // Indexes
            $table->index('user_id');
            $table->index('employee_id');
            $table->index('status');
            $table->index('category');
            $table->index(['user_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hr_contacts');
    }
};

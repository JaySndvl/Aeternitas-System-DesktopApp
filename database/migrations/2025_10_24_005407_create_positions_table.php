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
        Schema::create('positions', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // Position name (e.g., "Manager", "Software Engineer")
            $table->string('code')->unique(); // Position code (e.g., "MGR", "SE", "RAF")
            $table->text('description')->nullable(); // Detailed description of the position
            $table->string('level')->nullable(); // Position level (e.g., "Senior", "Junior", "Lead")
            $table->uuid('department_id'); // Department this position belongs to
            $table->foreign('department_id')->references('id')->on('departments')->onDelete('cascade');
            $table->decimal('min_salary', 10, 2)->nullable(); // Minimum salary for this position
            $table->decimal('max_salary', 10, 2)->nullable(); // Maximum salary for this position
            $table->boolean('is_active')->default(true); // Whether this position is currently active
            $table->json('requirements')->nullable(); // Job requirements as JSON
            $table->json('responsibilities')->nullable(); // Job responsibilities as JSON
            $table->timestamps();
            
            // Indexes for better performance
            $table->index(['is_active', 'department_id']);
            $table->index('level');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('positions');
    }
};

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
        Schema::create('tax_brackets', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name'); // e.g., "Philippine Income Tax 2024"
            $table->string('description')->nullable();
            $table->decimal('min_income', 15, 2); // Minimum income for this bracket
            $table->decimal('max_income', 15, 2)->nullable(); // Maximum income (null for highest bracket)
            $table->decimal('tax_rate', 5, 2); // Tax rate as percentage (e.g., 15.00 for 15%)
            $table->decimal('base_tax', 15, 2)->default(0); // Base tax amount for this bracket
            $table->decimal('excess_over', 15, 2)->default(0); // Amount to subtract from income before applying rate
            $table->integer('sort_order')->default(0); // Order of brackets (lowest to highest)
            $table->boolean('is_active')->default(true);
            $table->date('effective_from')->nullable();
            $table->date('effective_until')->nullable();
            $table->timestamps();
            
            $table->index(['is_active', 'sort_order']);
            $table->index(['min_income', 'max_income']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tax_brackets');
    }
};

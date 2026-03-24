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
        Schema::create('attendance_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('attendance_record_id');
            $table->enum('action', ['created', 'updated', 'deleted', 'approved', 'rejected', 'time_in', 'time_out', 'break_start', 'break_end']);
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->uuid('performed_by');
            $table->timestamp('performed_at');
            $table->text('reason')->nullable();
            $table->timestamps();

            $table->foreign('attendance_record_id')->references('id')->on('attendance_records')->onDelete('cascade');
            $table->foreign('performed_by')->references('id')->on('accounts')->onDelete('cascade');
            $table->index(['attendance_record_id', 'action']);
            $table->index(['performed_by', 'performed_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_logs');
    }
};

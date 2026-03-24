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
        // Update existing 'Holiday' records to 'Regular Holiday'
        DB::table('employee_schedules')
            ->where('status', 'Holiday')
            ->update(['status' => 'Regular Holiday']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Update 'Regular Holiday' back to 'Holiday'
        DB::table('employee_schedules')
            ->where('status', 'Regular Holiday')
            ->update(['status' => 'Holiday']);
    }
};

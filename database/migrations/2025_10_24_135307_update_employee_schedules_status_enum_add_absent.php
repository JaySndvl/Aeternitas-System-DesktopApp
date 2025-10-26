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
        // SQLite doesn't support MODIFY COLUMN or ENUM
        // The status column will accept any string value
        // This migration is effectively a no-op for SQLite
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // SQLite doesn't support MODIFY COLUMN or ENUM
        // This migration is effectively a no-op for SQLite
    }
};

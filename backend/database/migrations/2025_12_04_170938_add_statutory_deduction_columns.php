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
        Schema::table('payrolls', function (Blueprint $table) {
            // Add statutory deduction columns if they don't exist
            if (!Schema::hasColumn('payrolls', 'sss')) {
                $table->decimal('sss', 12, 2)->nullable()->after('deductions');
            }
            if (!Schema::hasColumn('payrolls', 'phic')) {
                $table->decimal('phic', 12, 2)->nullable()->after('sss');
            }
            if (!Schema::hasColumn('payrolls', 'hdmf')) {
                $table->decimal('hdmf', 12, 2)->nullable()->after('phic');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payrolls', function (Blueprint $table) {
            // Only drop if they exist
            if (Schema::hasColumn('payrolls', 'sss')) {
                $table->dropColumn('sss');
            }
            if (Schema::hasColumn('payrolls', 'phic')) {
                $table->dropColumn('phic');
            }
            if (Schema::hasColumn('payrolls', 'hdmf')) {
                $table->dropColumn('hdmf');
            }
        });
    }
};
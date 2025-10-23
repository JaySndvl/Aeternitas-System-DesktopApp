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
            $table->decimal('holiday_basic_pay', 10, 2)->default(0)->after('basic_salary');
            $table->decimal('holiday_premium', 10, 2)->default(0)->after('holiday_basic_pay');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payrolls', function (Blueprint $table) {
            $table->dropColumn(['holiday_basic_pay', 'holiday_premium']);
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class FixPayrollsStatusColumnLength extends Migration
{
    public function up()
    {
        // For MySQL/MariaDB - increase the status column length
        Schema::table('payrolls', function (Blueprint $table) {
            $table->string('status', 50)->default('pending')->change();
        });
        
        // Also add the rejection_reason column if it doesn't exist
        if (!Schema::hasColumn('payrolls', 'rejection_reason')) {
            Schema::table('payrolls', function (Blueprint $table) {
                $table->text('rejection_reason')->nullable()->after('status');
            });
        }
    }

    public function down()
    {
        Schema::table('payrolls', function (Blueprint $table) {
            $table->string('status', 20)->default('pending')->change();
            
            if (Schema::hasColumn('payrolls', 'rejection_reason')) {
                $table->dropColumn('rejection_reason');
            }
        });
    }
}
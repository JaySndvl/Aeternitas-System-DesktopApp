<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddRejectionColumnsToPayrollsTable extends Migration
{
    public function up()
    {
        Schema::table('payrolls', function (Blueprint $table) {
            // Add missing columns for rejection
            if (!Schema::hasColumn('payrolls', 'rejected_at')) {
                $table->timestamp('rejected_at')->nullable()->after('paid_at');
            }
            
            if (!Schema::hasColumn('payrolls', 'rejected_by')) {
                // Check the users table structure first
                $usersTableInfo = DB::select(DB::raw("SHOW COLUMNS FROM users WHERE Field = 'id'"));
                $usersIdType = $usersTableInfo[0]->Type ?? null;
                
                if (str_contains($usersIdType, 'char')) {
                    // Users table uses UUID
                    $table->uuid('rejected_by')->nullable()->after('rejected_at');
                } else {
                    // Users table uses bigInteger
                    $table->unsignedBigInteger('rejected_by')->nullable()->after('rejected_at');
                }
            }
            
            if (!Schema::hasColumn('payrolls', 'rejection_reason')) {
                $table->text('rejection_reason')->nullable()->after('rejected_by');
            }
        });
        
        // Add foreign key constraint in a separate statement
        Schema::table('payrolls', function (Blueprint $table) {
            if (Schema::hasColumn('payrolls', 'rejected_by')) {
                $table->foreign('rejected_by')
                    ->references('id')
                    ->on('users')
                    ->onDelete('set null');
            }
        });
        
        // Modify status column separately
        if (Schema::hasColumn('payrolls', 'status')) {
            DB::statement("ALTER TABLE payrolls MODIFY status VARCHAR(50) DEFAULT 'pending'");
        }
    }

    public function down()
    {
        Schema::table('payrolls', function (Blueprint $table) {
            // Drop foreign key first
            $table->dropForeign(['rejected_by']);
            
            // Then drop columns
            $table->dropColumn(['rejected_at', 'rejected_by', 'rejection_reason']);
        });
        
        // Revert status column
        if (Schema::hasColumn('payrolls', 'status')) {
            DB::statement("ALTER TABLE payrolls MODIFY status VARCHAR(20) DEFAULT 'pending'");
        }
    }
}
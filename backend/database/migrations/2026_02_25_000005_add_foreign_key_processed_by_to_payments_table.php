<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Use raw SQL to check and drop the foreign key if it exists (MySQL only)
        if (Schema::hasColumn('payments', 'processed_by')) {
            $fkName = null;
            $result = DB::select("SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_NAME = 'payments' AND COLUMN_NAME = 'processed_by' AND CONSTRAINT_SCHEMA = DATABASE() AND REFERENCED_TABLE_NAME IS NOT NULL");
            if (!empty($result)) {
                $fkName = $result[0]->CONSTRAINT_NAME;
                DB::statement("ALTER TABLE payments DROP FOREIGN KEY `$fkName`");
            }
            Schema::table('payments', function (Blueprint $table) {
                $table->foreign('processed_by')
                    ->references('id')
                    ->on('accounts')
                    ->onDelete('set null');
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('payments', 'processed_by')) {
            $fkName = null;
            $result = DB::select("SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_NAME = 'payments' AND COLUMN_NAME = 'processed_by' AND CONSTRAINT_SCHEMA = DATABASE() AND REFERENCED_TABLE_NAME IS NOT NULL");
            if (!empty($result)) {
                $fkName = $result[0]->CONSTRAINT_NAME;
                DB::statement("ALTER TABLE payments DROP FOREIGN KEY `$fkName`");
            }
        }
    }
};

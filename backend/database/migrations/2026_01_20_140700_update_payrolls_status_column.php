<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class UpdatePayrollsStatusColumn extends Migration
{
    public function up()
    {
        // For MySQL/MariaDB
        Schema::table('payrolls', function (Blueprint $table) {
            $table->string('status', 50)->change(); // Increase length to 50
        });
    }

    public function down()
    {
        Schema::table('payrolls', function (Blueprint $table) {
            $table->string('status', 20)->change(); // Revert to original
        });
    }
}
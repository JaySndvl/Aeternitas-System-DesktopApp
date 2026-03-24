<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdatePaymentsTableDataTypes extends Migration
{
    public function up()
    {
        // Change employee_id from bigint to string to match employees.id (UUID)
        Schema::table('payments', function (Blueprint $table) {
            $table->string('employee_id', 36)->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->unsignedBigInteger('employee_id')->nullable()->change();
        });
    }
}
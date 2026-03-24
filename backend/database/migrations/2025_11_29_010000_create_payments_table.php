<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentsTable extends Migration
{
    public function up()
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();

            // Match the payrolls.id data type - if payrolls uses UUID, use string
            $table->string('payroll_id')->nullable()->index();
            $table->string('employee_id', 36)->nullable()->index();

            $table->decimal('amount', 14, 2)->default(0);
            $table->string('status')->default('pending');
            $table->string('transaction_id')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            // Only add foreign key if payrolls table exists and uses string/UUID
            if (Schema::hasTable('payrolls') && Schema::getColumnType('payrolls', 'id') === 'string') {
                $table->foreign('payroll_id')->references('id')->on('payrolls')->onDelete('set null');
            }

            // Do not add foreign key here; will add in a separate migration after both tables exist
        });
    }

    public function down()
    {
        \Illuminate\Support\Facades\Schema::dropIfExists('payments');
        exit;
    }
}

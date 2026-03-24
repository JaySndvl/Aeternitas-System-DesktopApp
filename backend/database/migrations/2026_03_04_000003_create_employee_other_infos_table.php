<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_other_infos', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('employee_id')->unique();
            $table->string('address')->nullable();
            $table->string('pov_address')->nullable();
            $table->string('no_street')->nullable();
            $table->string('barangay')->nullable();
            $table->string('town_district')->nullable();
            $table->string('city_province')->nullable();
            $table->string('birthplace')->nullable();
            $table->string('religion')->nullable();
            $table->string('blood_type')->nullable();
            $table->string('citizenship')->nullable();
            $table->string('height')->nullable();
            $table->string('weight')->nullable();
            $table->string('phone')->nullable();
            $table->string('mobile')->nullable();
            $table->string('drivers_license')->nullable();
            $table->string('prc_no')->nullable();
            $table->string('father')->nullable();
            $table->string('mother')->nullable();
            $table->string('spouse')->nullable();
            $table->boolean('spouse_employed')->default(false);
            $table->string('photo_path')->nullable();
            $table->timestamps();

            $table->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_other_infos');
    }
};

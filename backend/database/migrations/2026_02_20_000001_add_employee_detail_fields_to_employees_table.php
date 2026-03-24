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
        Schema::table('employees', function (Blueprint $table) {
            $table->date('date_of_birth')->nullable()->after('hire_date');
            $table->string('civil_status')->nullable()->after('date_of_birth');
            $table->text('home_address')->nullable()->after('civil_status');
            $table->text('current_address')->nullable()->after('home_address');
            $table->string('mobile_number', 20)->nullable()->after('current_address');

            $table->string('facebook_link')->nullable()->after('mobile_number');
            $table->string('linkedin_link')->nullable()->after('facebook_link');
            $table->string('ig_link')->nullable()->after('linkedin_link');
            $table->string('other_link')->nullable()->after('ig_link');

            $table->string('emergency_full_name')->nullable()->after('other_link');
            $table->string('emergency_relationship')->nullable()->after('emergency_full_name');
            $table->text('emergency_home_address')->nullable()->after('emergency_relationship');
            $table->text('emergency_current_address')->nullable()->after('emergency_home_address');
            $table->string('emergency_mobile_number', 20)->nullable()->after('emergency_current_address');
            $table->string('emergency_email')->nullable()->after('emergency_mobile_number');
            $table->string('emergency_facebook_link')->nullable()->after('emergency_email');

            $table->date('loan_start_date')->nullable()->after('emergency_facebook_link');
            $table->date('loan_end_date')->nullable()->after('loan_start_date');
            $table->decimal('loan_total_amount', 12, 2)->nullable()->after('loan_end_date');
            $table->decimal('loan_monthly_amortization', 12, 2)->nullable()->after('loan_total_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn([
                'date_of_birth',
                'civil_status',
                'home_address',
                'current_address',
                'mobile_number',
                'facebook_link',
                'linkedin_link',
                'ig_link',
                'other_link',
                'emergency_full_name',
                'emergency_relationship',
                'emergency_home_address',
                'emergency_current_address',
                'emergency_mobile_number',
                'emergency_email',
                'emergency_facebook_link',
                'loan_start_date',
                'loan_end_date',
                'loan_total_amount',
                'loan_monthly_amortization',
            ]);
        });
    }
};

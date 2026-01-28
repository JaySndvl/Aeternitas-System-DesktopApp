<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Check if table doesn't exist before creating
        if (!Schema::hasTable('login_logs')) {
            Schema::create('login_logs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->string('ip_address', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->timestamps();
                
                // Check if users table exists before adding foreign key
                if (Schema::hasTable('users')) {
                    $table->foreign('user_id')
                          ->references('id')
                          ->on('users')
                          ->onDelete('cascade');
                }
            });
            echo "Login logs table created successfully.\n";
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('login_logs');
    }
};
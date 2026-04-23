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
        Schema::create('users', function (Blueprint $table) {
            $table->id('user_id');
            $table->string('user_password', 255);
            $table->enum('user_role', ['alumni', 'employer', 'admin', 'super_admin', 'registrar']);
            $table->string('user_first_name', 100);
            $table->string('user_last_name', 100);
            $table->string('user_middle_name', 100)->nullable();
            $table->string('user_suffix', 10)->nullable();
            $table->string('user_email', 255)->unique();
            $table->string('user_number', 20)->nullable();
            $table->string('user_profile_picture', 255)->nullable();
            $table->boolean('user_active')->default(true);
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};

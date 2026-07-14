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
        Schema::create('alumnus_skill', function (Blueprint $table) {
            $table->foreignId('alumnus_id')->constrained('alumni', 'user_id')->onDelete('cascade');
            $table->foreignId('skill_id')->constrained('skills', 'skill_id')->onDelete('cascade');
            $table->primary(['alumnus_id', 'skill_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alumnus_skill');
    }
};

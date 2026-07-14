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
        Schema::create('experiences', function (Blueprint $table) {
            $table->id('experience_id');
            $table->foreignId('alumnus_id')->constrained('alumni', 'user_id')->onDelete('cascade');
            $table->foreignId('industry_id')->constrained('industries', 'industry_id');
            $table->string('employment_type', 100);
            $table->integer('duration_months');
            $table->string('company_name');
            $table->string('job_title');
            $table->text('job_description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('experiences');
    }
};

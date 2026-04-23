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
        Schema::create('job_applications', function (Blueprint $table) {
            $table->id('application_id');
            $table->foreignId('job_id')->constrained('job_postings', 'job_posting_id')->onDelete('cascade');
            $table->foreignId('alumnus_id')->constrained('alumni', 'user_id')->onDelete('cascade');
            $table->string('alumnus_resume', 255);
            $table->timestamp('application_date');
            $table->enum('application_status', ['hire', 'decline', 'shorlisted']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_applications');
    }
};

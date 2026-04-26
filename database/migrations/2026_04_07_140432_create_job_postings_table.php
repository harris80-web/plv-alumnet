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
        Schema::create('job_postings', function (Blueprint $table) {
            $table->id('job_posting_id');
            $table->foreignId('employer_id')->constrained('employers', 'user_id')->onDelete('cascade');
            $table->string('job_posting_title', 255);
            $table->string('job_posting_company', 255);
            $table->text('job_posting_address');
            $table->enum('job_posting_employment_type', ['Full-Time', 'Part-Time', 'Freelance']);
            $table->enum('job_posting_setup', ['On-Site', 'Remote', 'Hybrid']);
            $table->text('job_posting_description');
            $table->date('job_posting_date')->default(now()->toDateString());
            $table->date('job_closing_date');
            $table->text('job_posting_image')->nullable();
            $table->boolean('job_approved')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_postings');
    }
};

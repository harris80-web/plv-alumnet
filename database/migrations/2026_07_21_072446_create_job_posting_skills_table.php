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
        Schema::create('job_posting_skills', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('job_posting_id');
            $table->unsignedBigInteger('skill_id');
            $table->boolean('is_required')->default(true); // required vs. preferred/nice-to-have
            $table->unsignedTinyInteger('weight')->default(3); // 1 (low) - 5 (critical)
 
            $table->foreign('job_posting_id')->references('job_posting_id')->on('job_postings')->onDelete('cascade');
            $table->foreign('skill_id')->references('skill_id')->on('skills')->onDelete('cascade');
            $table->unique(['job_posting_id', 'skill_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_posting_skills');
    }
};

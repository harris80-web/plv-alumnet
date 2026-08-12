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
        Schema::create('job_bookmarks', function (Blueprint $table) {
            $table->id('bookmark_id');
            $table->foreignId('job_id')->constrained('job_postings', 'job_posting_id')->onDelete('cascade');
            $table->foreignId('alumnus_id')->constrained('alumni', 'user_id')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['job_id', 'alumnus_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_bookmarks');
    }
};

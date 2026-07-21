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
        Schema::create('job_matches', function (Blueprint $table) {
            $table->id('match_id');
            $table->unsignedBigInteger('job_posting_id');
            $table->unsignedBigInteger('alumnus_id');
            $table->decimal('score', 5, 2); // 0.00 - 100.00
            $table->json('score_breakdown')->nullable(); // per-criterion detail for the employer UI
            $table->timestamp('computed_at')->useCurrent();
            $table->timestamps();
 
            $table->foreign('job_posting_id')->references('job_posting_id')->on('job_postings')->onDelete('cascade');
            $table->foreign('alumnus_id')->references('user_id')->on('alumni')->onDelete('cascade');
            $table->unique(['job_posting_id', 'alumnus_id']);
            $table->index(['job_posting_id', 'score']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_matches');
    }
};

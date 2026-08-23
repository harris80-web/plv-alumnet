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
        Schema::create('employer_reviews', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employer_id');
            $table->foreign('employer_id')->references('user_id')->on('employers')->cascadeOnDelete();
            $table->unsignedBigInteger('alumnus_id');
            $table->foreign('alumnus_id')->references('user_id')->on('alumni')->cascadeOnDelete();
            $table->enum('vote', ['upvote', 'downvote']);
            $table->text('review_body')->nullable();
            $table->timestamps();

            // One vote per alumnus per company — voting again updates this
            // same row (see EmployerReviewController::vote()) rather than
            // stacking duplicate votes.
            $table->unique(['employer_id', 'alumnus_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employer_reviews');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Splits company up/downvoting out of employer_reviews (one row per
 * employer+alumnus, requiring the alumnus to have been hired there) into
 * its own per-JOB-POSTING table — any alumnus can vote, on any posting,
 * whether or not that company ever hired them, and the same alumnus can
 * cast a separate vote on every posting a company has (not just one vote
 * total per company). Star ratings stay exactly as they were, in
 * employer_reviews, still hire-gated.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_posting_votes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('job_posting_id');
            $table->foreign('job_posting_id')->references('job_posting_id')->on('job_postings')->cascadeOnDelete();
            $table->unsignedBigInteger('alumnus_id');
            $table->foreign('alumnus_id')->references('user_id')->on('alumni')->cascadeOnDelete();
            $table->enum('vote', ['upvote', 'downvote']);
            $table->timestamps();

            // One vote per alumnus per JOB POSTING — voting again on the
            // same posting updates this row rather than stacking a
            // duplicate; voting on a DIFFERENT posting (even from the same
            // company) is a separate row entirely.
            $table->unique(['job_posting_id', 'alumnus_id']);
        });

        // Best-effort one-time carry-over of existing per-employer votes:
        // attach each to any one of that employer's job postings (there's
        // no way to know which specific posting an old employer-level vote
        // was "about", so this is just so the handful of pre-existing test
        // votes aren't silently lost rather than a meaningful reconstruction).
        $existingVotes = DB::table('employer_reviews')->whereNotNull('vote')->get(['employer_id', 'alumnus_id', 'vote', 'created_at', 'updated_at']);
        foreach ($existingVotes as $old) {
            $jobPostingId = DB::table('job_postings')->where('user_id', $old->employer_id)->value('job_posting_id');
            if (!$jobPostingId) {
                continue;
            }
            DB::table('job_posting_votes')->insert([
                'job_posting_id' => $jobPostingId,
                'alumnus_id' => $old->alumnus_id,
                'vote' => $old->vote,
                'created_at' => $old->created_at,
                'updated_at' => $old->updated_at,
            ]);
        }

        Schema::table('employer_reviews', function (Blueprint $table) {
            $table->dropColumn('vote');
        });
    }

    public function down(): void
    {
        Schema::table('employer_reviews', function (Blueprint $table) {
            $table->enum('vote', ['upvote', 'downvote'])->nullable()->after('alumnus_id');
        });

        Schema::dropIfExists('job_posting_votes');
    }
};

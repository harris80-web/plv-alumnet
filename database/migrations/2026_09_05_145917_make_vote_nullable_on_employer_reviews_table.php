<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * vote was NOT NULL when this table only ever recorded a thumbs
     * up/down. Now that a 5-star rating exists as its own, independent
     * thing on the same row, an alumnus needs to be able to rate a company
     * without ever having cast a vote on it at all — vote must allow null.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE employer_reviews MODIFY vote ENUM('upvote', 'downvote') NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("UPDATE employer_reviews SET vote = 'upvote' WHERE vote IS NULL");
        DB::statement("ALTER TABLE employer_reviews MODIFY vote ENUM('upvote', 'downvote') NOT NULL");
    }
};

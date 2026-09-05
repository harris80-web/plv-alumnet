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
        Schema::table('employer_reviews', function (Blueprint $table) {
            // A separate, optional 5-star rating alongside the existing
            // thumbs up/down vote — same row (same unique employer_id +
            // alumnus_id pair already on this table), so it's automatically
            // deduped per-company with zero schema change needed for that.
            // Nullable: an alumnus can vote without rating, or rate without
            // voting.
            $table->unsignedTinyInteger('rating')->nullable()->after('vote');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employer_reviews', function (Blueprint $table) {
            $table->dropColumn('rating');
        });
    }
};

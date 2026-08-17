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
        Schema::table('job_matches', function (Blueprint $table) {
            // AI semantic assessment, layered on top of the deterministic
            // `score`/`score_breakdown` — kept separate so `score` (frozen
            // onto JobApplication::application_score at apply time via
            // JobMatchService::scoreFor()) never depends on an external API
            // call succeeding.
            $table->decimal('ai_score', 5, 2)->nullable()->after('score_breakdown');
            $table->text('ai_explanation')->nullable()->after('ai_score');
            $table->timestamp('ai_computed_at')->nullable()->after('ai_explanation');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('job_matches', function (Blueprint $table) {
            $table->dropColumn(['ai_score', 'ai_explanation', 'ai_computed_at']);
        });
    }
};

<?php

namespace App\Console\Commands;

use App\Models\Alumnus;
use App\Models\JobMatch;
use App\Services\JobMatchAiService;
use App\Services\JobMatchService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Powers the alumni dashboard's "Job Matches For You" section — there's no
 * button for this, it runs on a schedule (see routes/console.php) so
 * recommendations are already sitting there next time the alumnus logs in.
 *
 * Two passes, kept separate so AI cost/latency never gates the ranking:
 *  1. Deterministic (JobMatchService::scoreFor, cheap) for every alumnus
 *     with any resume progress against every open+approved job posting —
 *     runs every time this command fires.
 *  2. AI semantic enrichment (JobMatchAiService, one Gemini call per pair),
 *     only for each alumnus's top-N deterministic matches, and only when
 *     --ai is passed — the hourly schedule entry omits it, a separate daily
 *     entry passes it, so Gemini only gets hit once a day per alumnus.
 */
class RecomputeJobMatches extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'job-matches:recompute
        {--ai : Also run the Gemini semantic pass on each alumnus\'s top deterministic matches}
        {--per-alumnus=10 : How many top deterministic matches per alumnus get AI-enriched}
        {--limit= : Cap how many alumni are processed this run (oldest-AI-refresh-first)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recompute alumnus/job-posting match scores that power the alumni dashboard job recommendations';

    public function handle(JobMatchService $matchService, JobMatchAiService $aiService)
    {
        $useAi = (bool) $this->option('ai');
        $perAlumnus = max(1, (int) $this->option('per-alumnus'));
        $limit = $this->option('limit') !== null ? max(1, (int) $this->option('limit')) : null;

        if ($useAi && !$aiService->isConfigured()) {
            $this->warn('Gemini API key is not configured — skipping the AI pass, deterministic scores only.');
            $useAi = false;
        }

        $alumniQuery = Alumnus::where('alumnus_resume_completeness', '>', 0);

        // When AI-enriching, work through alumni whose matches were AI-refreshed
        // longest ago (or never) first, so a --limit cap rotates fairly across
        // the whole alumni base run over run instead of starving the same tail.
        // withMin (a correlated subquery) avoids the GROUP BY + SELECT * clash
        // a manual join/groupBy hits under ONLY_FULL_GROUP_BY.
        if ($useAi) {
            $alumniQuery
                ->withMin('jobMatches', 'ai_computed_at')
                ->orderByRaw('job_matches_min_ai_computed_at IS NOT NULL, job_matches_min_ai_computed_at ASC');
        }

        if ($limit) {
            $alumniQuery->limit($limit);
        }

        $alumni = $alumniQuery->get();
        $this->info("Recomputing matches for {$alumni->count()} alumni" . ($useAi ? ' (with AI enrichment)' : ' (deterministic only)') . '...');

        $aiCalls = 0;

        foreach ($alumni as $alumnus) {
            $matches = $matchService->refreshForAlumnus($alumnus);

            if (!$useAi) {
                continue;
            }

            $topMatches = $matches->sortByDesc(fn (JobMatch $m) => (float) $m->score)->take($perAlumnus);

            foreach ($topMatches as $match) {
                // Already AI-scored in the last 24h — don't re-spend a Gemini
                // call re-confirming something that hasn't likely changed.
                if ($match->ai_computed_at && $match->ai_computed_at->gt(now()->subDay())) {
                    continue;
                }

                try {
                    $result = $aiService->assess($match->jobPosting, $alumnus);
                    $match->update([
                        'ai_score' => $result['score'],
                        'ai_explanation' => $result['explanation'],
                        'ai_computed_at' => now(),
                    ]);
                    $aiCalls++;
                } catch (\Throwable $e) {
                    Log::warning('JobMatchAiService::assess failed', [
                        'job_posting_id' => $match->job_posting_id,
                        'alumnus_id' => $match->alumnus_id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }

        $this->info("Done." . ($useAi ? " {$aiCalls} Gemini call(s) made." : ''));
    }
}

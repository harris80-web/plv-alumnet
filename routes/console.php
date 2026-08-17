<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('job:decline-on-job-expire')->daily();

// Powers the alumni dashboard's "Job Matches For You" section — deterministic
// scores refresh hourly (cheap, no external calls); the Gemini semantic pass
// only runs once a day (and only for the top matches per alumnus) to keep
// API usage bounded. See RecomputeJobMatches for the full split.
Schedule::command('job-matches:recompute')->hourly();
Schedule::command('job-matches:recompute --ai --limit=100')->dailyAt('02:00');

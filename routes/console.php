<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// job:decline-on-job-expire is no longer scheduled here — replaced by
// App\Jobs\DeclineShortlistedOnJobExpiry, a per-posting delayed queue job
// dispatched from JobPostingController when a posting's closing date is
// set/changed (see scheduleExpiryCheck() there). This app has no cron on
// Windows/XAMPP to actually drive a daily Schedule entry, so it silently
// never ran; the delayed-job approach only needs the same `queue:work`
// process already wanted for queued mail. The command itself still works
// (bug fixed) and stays available as a manual catch-up sweep:
//   php artisan job:decline-on-job-expire

// Powers the alumni dashboard's "Job Matches For You" section — deterministic
// scores refresh hourly (cheap, no external calls); the Gemini semantic pass
// only runs once a day (and only for the top matches per alumnus) to keep
// API usage bounded. See RecomputeJobMatches for the full split.
Schedule::command('job-matches:recompute')->hourly();
Schedule::command('job-matches:recompute --ai --limit=100')->dailyAt('02:00');

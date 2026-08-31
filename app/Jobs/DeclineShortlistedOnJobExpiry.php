<?php

namespace App\Jobs;

use App\Models\JobApplication;
use App\Models\JobPosting;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * One-shot, per-posting replacement for the old daily `job:decline-on-job-
 * expire` schedule entry — that command only ever ran if something invoked
 * `php artisan schedule:run` on a timer, which nothing in this Windows/
 * XAMPP setup actually does. This instead gets dispatched with ->delay()
 * timed to a specific posting's closing date the moment that date is set
 * (see JobPostingController::addJobPost()/editJobPost()), and only needs
 * the same `php artisan queue:work` process the app already wants running
 * for queued mail.
 *
 * Deliberately re-validates everything at execution time rather than
 * trusting the state that existed when it was dispatched, so an edited or
 * deleted posting can't leave this doing the wrong thing:
 *  - Holds just the posting's id, not the Eloquent model itself — a
 *    (soft-)deleted posting simply means JobPosting::find() returns null
 *    below, a clean no-op, instead of the ModelNotFoundException a
 *    directly-serialized model reference would throw when this runs.
 *  - Re-checks job_closing_date against "today" right before acting. If
 *    the closing date was pushed later after this was queued, this
 *    (now-early) run finds the posting still open and does nothing — the
 *    edit that changed the date dispatches its own correctly-timed
 *    replacement, so there's no need to find and cancel this stale queued
 *    row.
 */
class DeclineShortlistedOnJobExpiry implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public readonly int $jobPostingId)
    {
    }

    public function handle(): void
    {
        $job = JobPosting::find($this->jobPostingId);

        // Gone (soft-deleted) or somehow has no closing date on file — nothing to do.
        if (!$job || !$job->job_closing_date) {
            return;
        }

        // Same "expired" definition as the rest of the app (JobPosting::
        // scopeOpen()/scopeActive()): a posting closing today is still open
        // through the end of today, only strictly-past dates count.
        if (Carbon::parse($job->job_closing_date)->greaterThanOrEqualTo(Carbon::today())) {
            return;
        }

        JobApplication::where('job_id', $job->job_posting_id)
            ->where('application_status', 'shortlisted')
            ->update(['application_status' => 'declined']);
    }

    /** Delay target for a given posting — the moment its closing date has fully elapsed. */
    public static function delayFor(JobPosting $job): ?Carbon
    {
        if (!$job->job_closing_date) {
            return null;
        }

        return Carbon::parse($job->job_closing_date)->addDay()->startOfDay();
    }
}

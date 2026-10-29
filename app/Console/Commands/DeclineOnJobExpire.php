<?php

namespace App\Console\Commands;

use App\Models\JobApplication;
use App\Models\JobPosting;
use App\Services\JobExpiryService;
use Illuminate\Console\Command;

/**
 * Manual catch-up sweep — the normal path is the per-posting delayed job
 * (App\Jobs\DeclineShortlistedOnJobExpiry). Runs the same JobExpiryService
 * logic across every already-expired posting, so it can email a lot of
 * people about posts that closed long ago: it shows the count and asks
 * first, and --no-email declines without notifying anyone.
 */
class DeclineOnJobExpire extends Command
{
    protected $signature = 'job:decline-on-job-expire
        {--no-email : Decline without sending emails or in-app notifications}
        {--force : Skip the confirmation prompt}';

    protected $description = 'Decline pending and shortlisted applicants on expired jobs (and email them that the post expired)';

    public function handle(JobExpiryService $expiryService)
    {
        $expiredJobIds = JobPosting::expired()->pluck('job_posting_id');
        $openApplications = JobApplication::whereIn('job_id', $expiredJobIds)
            ->whereIn('application_status', JobExpiryService::STATUSES_CLOSED_ON_EXPIRY)
            ->count();

        if ($openApplications === 0) {
            $this->info('No pending or shortlisted applicants on expired jobs.');
            return self::SUCCESS;
        }

        $notify = !$this->option('no-email');
        $question = "Decline {$openApplications} pending/shortlisted applicant(s) on expired jobs"
            . ($notify ? ' and email each of them?' : ' WITHOUT emailing them?');

        if (!$this->option('force') && !$this->confirm($question)) {
            $this->warn('Cancelled — nothing changed.');
            return self::SUCCESS;
        }

        $declined = 0;
        JobPosting::whereIn('job_posting_id', $expiredJobIds)->each(function (JobPosting $job) use ($expiryService, $notify, &$declined) {
            $declined += $expiryService->declineOpenApplications($job, $notify);
        });

        $this->info("Declined {$declined} applicant(s) from expired jobs" . ($notify ? ' and queued their emails.' : ' (no emails sent).'));

        return self::SUCCESS;
    }
}

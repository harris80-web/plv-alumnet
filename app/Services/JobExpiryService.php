<?php

namespace App\Services;

use App\Mail\JobExpiredApplicantMail;
use App\Models\JobApplication;
use App\Models\JobPosting;
use App\Models\UserNotification;
use Illuminate\Support\Facades\Mail;

/**
 * What happens to a job posting's still-open applications once it expires:
 * every pending and shortlisted applicant is declined and told why (the
 * post expired — not "the employer chose someone else", which is what the
 * regular decline email says). Hired/declined applications are left alone.
 *
 * Shared by the per-posting delayed job (DeclineShortlistedOnJobExpiry,
 * the normal path) and the manual catch-up command (job:decline-on-job-expire)
 * so the two can never drift apart.
 */
class JobExpiryService
{
    /** Application statuses that get closed out when their posting expires. */
    public const STATUSES_CLOSED_ON_EXPIRY = ['pending', 'shortlisted'];

    /**
     * @param  bool  $notify  false = decline silently (no email / in-app notification)
     * @return int  how many applications were declined
     */
    public function declineOpenApplications(JobPosting $job, bool $notify = true): int
    {
        $applications = JobApplication::with('alumnus.user')
            ->where('job_id', $job->job_posting_id)
            ->whereIn('application_status', self::STATUSES_CLOSED_ON_EXPIRY)
            ->get();

        foreach ($applications as $application) {
            $application->application_status = 'declined';
            $application->save();
            $application->setRelation('job', $job);

            if (!$notify) {
                continue;
            }

            // Same email + in-app notification pairing as
            // JobApplicationController::declineApplication(), just with
            // expiry-specific wording.
            $email = $application->alumnus?->user?->user_email;
            if ($email) {
                Mail::to($email)->queue(new JobExpiredApplicantMail($application));
            }

            UserNotification::create([
                'user_id' => $application->alumnus_id,
                'type' => 'job_application_declined',
                'reference_id' => $application->job_id,
                'title' => 'Job post expired',
                'body' => "The job post \"{$job->job_posting_title}\" at {$job->job_posting_company} has expired, so your application has been closed.",
            ]);
        }

        return $applications->count();
    }
}

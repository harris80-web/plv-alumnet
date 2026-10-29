<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Sent to each pending/shortlisted applicant when the job posting they
 * applied to expires (see App\Services\JobExpiryService) — distinct from
 * DeclineApplicantMail, whose wording says the employer picked someone else.
 */
class JobExpiredApplicantMail extends Mailable
{
    use Queueable, SerializesModels;

    public $application;

    public function __construct($application)
    {
        $this->application = $application;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Job Post Expired: ' . $this->application->job->job_posting_title,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mails.jobExpiredApplication',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}

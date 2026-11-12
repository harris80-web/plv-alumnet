<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/** Employer counterpart to ActivateAlumniMail / ActivateAdminMail. */
class ActivateEmployerMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;

    public function __construct($user)
    {
        $this->user = $user;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your Employer Account is Active',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mails.activateEmployer',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}

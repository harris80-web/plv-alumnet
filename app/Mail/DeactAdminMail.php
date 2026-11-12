<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Admin counterpart to DeactAlumniMail / DeactEmployerMail — same
 * deactivation notice, worded for a staff account rather than an alumni one.
 */
class DeactAdminMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $reason;

    public function __construct($user, $reason)
    {
        $this->user = $user;
        $this->reason = $reason;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Admin Account Deactivation Notice',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mails.deactAdmin',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}

<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/**
 * Plain message the admin sends from Ayarlar » SMTP to verify the mail
 * transport. Carries the effective mailer details so a delivered copy also
 * documents which configuration produced it.
 */
class SmtpTestMail extends Mailable
{
    public function envelope(): Envelope
    {
        return new Envelope(subject: config('app.name') . ' — SMTP test məktubu');
    }

    public function content(): Content
    {
        return new Content(
            text: 'emails.smtp-test',
            with: [
                'sentAt' => now()->format('d.m.Y H:i:s'),
                'mailer' => config('mail.default'),
                'host'   => config('mail.mailers.smtp.host'),
                'port'   => config('mail.mailers.smtp.port'),
            ],
        );
    }
}

<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WelcomeMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public array $payload = []) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Welcome to RentFit',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.user.welcome',
            with: ['payload' => $this->payload],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}

<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BookingStatusChangedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public array $payload) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'RentFit Booking Status Update',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.booking.status-changed',
            with: ['payload' => $this->payload],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}

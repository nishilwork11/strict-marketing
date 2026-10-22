<?php

namespace App\Mail;

use App\Support\AdminDates;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

// The test sent from the email page in the admin panel. It goes out the same way as
// the enquiry emails, so if it arrives, they will too.
class TestEmail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Test email from the '.config('site.brand.name').' website',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'mail.test-email',
            with: [
                'sentAt' => AdminDates::full(now()),
                'url' => route('admin.email'),
            ],
        );
    }
}

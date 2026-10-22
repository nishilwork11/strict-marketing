<?php

namespace App\Mail;

use App\Support\AdminDates;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

// The email sent for each enquiry from the contact form. Replying to it answers the visitor.
class NewEnquiry extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @param  array{name: string, email: string, phone: string, company: string, services: list<string>, budget: string, message: string}  $enquiry
     * @param  string|null  $adminUrl  The enquiry's page in the admin panel, when it was saved there
     */
    public function __construct(public array $enquiry, public ?string $adminUrl = null) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            replyTo: [new Address($this->enquiry['email'], $this->enquiry['name'])],
            subject: "New enquiry from {$this->enquiry['name']}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $e = $this->enquiry;
        $notGiven = '<span style="color: #8c8c8c;">Not given</span>';
        $tel = preg_replace('/[^\d+]/', '', $e['phone']);

        // What the visitor typed is escaped here and sent as HTML, so Markdown never formats it
        return new Content(
            markdown: 'mail.new-enquiry',
            with: [
                'firstName' => Str::before($e['name'], ' '),
                'details' => [
                    'Name' => e($e['name']),
                    'Email' => '<a href="mailto:'.e($e['email']).'">'.e($e['email']).'</a>',
                    'Phone' => $e['phone'] !== '' ? '<a href="tel:'.e($tel).'">'.e($e['phone']).'</a>' : $notGiven,
                    'Company' => $e['company'] !== '' ? e($e['company']) : $notGiven,
                    'Interested in' => $e['services'] ? e(implode(', ', $e['services'])) : $notGiven,
                    'Budget' => $e['budget'] !== '' ? e($e['budget']) : $notGiven,
                    'Received' => e(AdminDates::full(now())),
                ],
                'messageHtml' => str_replace(["\r\n", "\r", "\n"], '', nl2br(e($e['message']))),
            ],
        );
    }
}

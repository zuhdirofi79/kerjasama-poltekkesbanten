<?php

namespace App\Mail;

use App\Models\PartnershipApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PartnershipApplicationConfirmationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public PartnershipApplication $application) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Pengajuan Kerja Sama Telah Diterima — Poltekkes Kemenkes Banten',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.partnership-application-confirmation',
        );
    }
}

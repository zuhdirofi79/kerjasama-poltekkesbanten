<?php

namespace App\Mail;

use App\Models\PartnershipApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewPartnershipApplicationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public PartnershipApplication $application)
    {
        $this->application->loadMissing(['attachments']);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[Kerjasama Poltekkes Banten] Pengajuan Kerja Sama Baru',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.partnership-application-new',
        );
    }
}

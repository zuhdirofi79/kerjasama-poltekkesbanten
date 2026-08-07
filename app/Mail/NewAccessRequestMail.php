<?php

namespace App\Mail;

use App\Models\DocumentAccessRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewAccessRequestMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public DocumentAccessRequest $accessRequest)
    {
        $this->accessRequest->loadMissing([
            'user',
            'document.yearFolder.category.section',
            'document.institutionFolder',
        ]);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[Kerjasama Poltekkes Banten] Permintaan Akses Dokumen Baru',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.access-request-new',
        );
    }
}

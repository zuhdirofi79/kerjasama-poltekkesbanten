<?php

namespace App\Mail;

use App\Models\DocumentAccessRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AccessRequestDecisionMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public DocumentAccessRequest $accessRequest)
    {
        $this->accessRequest->loadMissing(['document', 'user']);
    }

    public function envelope(): Envelope
    {
        $status = $this->accessRequest->status->label();

        return new Envelope(
            subject: "[Kerjasama Poltekkes Banten] Permintaan Akses: {$status}",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.access-request-decision',
        );
    }
}

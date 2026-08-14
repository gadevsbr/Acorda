<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SourceAlertMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string $sourceName,
        public readonly string $type,
        public readonly string $severity,
        public readonly string $alertMessage,
        public readonly string $officialUrl,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: '[Acorda Alcobaça] Alerta de fonte');
    }

    public function content(): Content
    {
        return new Content(text: 'emails.source-alert');
    }
}

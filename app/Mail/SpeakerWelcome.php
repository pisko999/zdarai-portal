<?php

declare(strict_types=1);

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SpeakerWelcome extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string $name,
        public readonly string $email,
        public readonly string $password,
        public readonly string $loginUrl,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Vítej v týmu ŽďárAI — tvůj přístup do portálu',
            replyTo: [new Address('klara@zdarai.cz', 'Klára')],
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.speaker-welcome',
        );
    }
}

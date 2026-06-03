<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\Registration;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SpeakerPollLinkUpdate extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Registration $registration,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Oprava: nový odkaz na anketu 🍻',
            replyTo: [new Address('klara@zdarai.cz', 'Klára')],
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.speaker-poll-link-update',
            with: [
                'name' => $this->registration->name,
                'pollUrl' => url('/anketa/' . $this->registration->token),
            ],
        );
    }
}

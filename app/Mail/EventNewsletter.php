<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\Event;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EventNewsletter extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Event $event,
        public readonly string $unsubscribeUrl = '',
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            replyTo: ['klara@zdarai.cz'],
            subject: '🎤 ' . $this->event->title . ' — ' . $this->event->date->locale('cs')->translatedFormat('j. F Y'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.event-newsletter',
        );
    }
}

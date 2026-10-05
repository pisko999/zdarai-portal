<?php

declare(strict_types=1);

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Pozvánka na akci spřátelené komunity (pořádá jiný subjekt, ne ŽďárAI).
 * Data akce pocházejí z config/partner_events.php.
 */
class PartnerEventInvite extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param  array<string, mixed>  $partnerEvent
     * @param  string  $audience  'subscriber' (odběratel newsletteru) | 'registrant' (účastník akce)
     */
    public function __construct(
        public readonly array $partnerEvent,
        public readonly string $unsubscribeUrl = '',
        public readonly string $audience = 'subscriber',
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            replyTo: ['klara@zdarai.cz'],
            subject: '🤝 Pozvánka: ' . $this->partnerEvent['title'] . ' — ' . $this->partnerEvent['date_short'],
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.partner-event-invite',
        );
    }
}

<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\Event;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Pozvánka na akci spřátelené komunity (pořádá jiný subjekt, ne ŽďárAI).
 * Data akce pocházejí z config/partner_events.php.
 *
 * Volitelné klíče configu:
 *  - 'view'             vlastní Blade šablona (jinak emails.partner-event-invite)
 *  - 'subject'          vlastní předmět (jinak „🤝 Pozvánka: <title> — <date_short>“)
 *  - 'promo_event_slug' slug akce ŽďárAI pro box na konci (jen published, jinak se box vynechá)
 */
class PartnerEventInvite extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param  array<string, mixed>  $partnerEvent
     * @param  string  $audience  'subscriber' (odběratel newsletteru) | 'registrant' (účastník akce)
     *                            | 'list' (jednorázový seznam od pořadatele, bez odhlašovacího odkazu)
     */
    public function __construct(
        public readonly array $partnerEvent,
        public readonly string $unsubscribeUrl = '',
        public readonly string $audience = 'subscriber',
    ) {}

    public function envelope(): Envelope
    {
        $subject = ! empty($this->partnerEvent['subject'])
            ? (string) $this->partnerEvent['subject']
            : '🤝 Pozvánka: ' . $this->partnerEvent['title'] . ' — ' . $this->partnerEvent['date_short'];

        return new Envelope(
            replyTo: ['klara@zdarai.cz'],
            subject: $subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: ! empty($this->partnerEvent['view'])
                ? (string) $this->partnerEvent['view']
                : 'emails.partner-event-invite',
            with: [
                'promoEvent' => $this->promoEvent(),
            ],
        );
    }

    /**
     * Akce ŽďárAI k propagaci na konci mailu — dohledává se až při renderu
     * (ve frontě se neserializuje), jen ve stavu published.
     */
    private function promoEvent(): ?Event
    {
        $slug = $this->partnerEvent['promo_event_slug'] ?? null;

        if (! is_string($slug) || $slug === '') {
            return null;
        }

        return Event::query()
            ->where('slug', $slug)
            ->where('status', 'published')
            ->with('talks.speaker')
            ->first();
    }
}

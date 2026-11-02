<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\Event;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;

/**
 * Pozvánka na akci spřátelené komunity (pořádá jiný subjekt, ne ŽďárAI).
 * Data akce pocházejí z config/partner_events.php.
 *
 * Volitelné klíče configu:
 *  - 'view'             vlastní Blade šablona (jinak emails.partner-event-invite)
 *  - 'subject'          vlastní předmět (jinak „🤝 Pozvánka: <title> — <date_short>“)
 *  - 'promo_event_slug' slug akce ŽďárAI pro box na konci (jen published, jinak se box vynechá)
 *  - 'promo_event_dnyai_url' odkaz na stránku naší akce v programu Dny AI (v boxu)
 *  - 'optin_source'     zdroj pro dobrovolné přihlášení k newsletteru — jen audience 'list'
 *                       a jen se zadaným $recipientEmail (podepsaný odkaz per příjemce)
 */
class PartnerEventInvite extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param  array<string, mixed>  $partnerEvent
     * @param  string  $audience  'subscriber' (odběratel newsletteru) | 'registrant' (účastník akce)
     *                            | 'list' (jednorázový seznam od pořadatele, bez odhlašovacího odkazu)
     * @param  string  $recipientEmail  e-mail příjemce — pro podepsaný opt-in odkaz (audience 'list')
     */
    public function __construct(
        public readonly array $partnerEvent,
        public readonly string $unsubscribeUrl = '',
        public readonly string $audience = 'subscriber',
        public readonly string $recipientEmail = '',
    ) {}

    /** Platnost podepsaného opt-in odkazu (dny od renderu mailu). */
    public const OPTIN_VALID_DAYS = 60;

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
                'optinUrl' => $this->optinUrl(),
            ],
        );
    }

    /**
     * Podepsaný odkaz na dobrovolné přihlášení k newsletteru — jen pro audience
     * 'list' s e-mailem příjemce a 'optin_source' v configu. Generuje se při
     * renderu (ve frontě až při odeslání), platnost OPTIN_VALID_DAYS.
     */
    private function optinUrl(): ?string
    {
        $source = $this->partnerEvent['optin_source'] ?? null;
        $email = mb_strtolower(trim($this->recipientEmail));

        if ($this->audience !== 'list' || $email === '' || ! is_string($source) || $source === '') {
            return null;
        }

        return URL::temporarySignedRoute(
            'newsletter.optin',
            now()->addDays(self::OPTIN_VALID_DAYS),
            ['email' => $email, 'source' => $source],
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

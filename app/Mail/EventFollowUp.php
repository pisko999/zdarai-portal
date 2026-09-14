<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\Event;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Follow-up po proběhlé akci — poděkování (varianta „attended") nebo
 * shrnutí pro ty, kdo nedorazili (varianta „missed"). Tělo mailu je
 * v obou případech stejné: prezentace v příloze + tipy na akce v okolí.
 */
class EventFollowUp extends Mailable
{
    use Queueable, SerializesModels;

    public const VARIANT_ATTENDED = 'attended';

    public const VARIANT_MISSED = 'missed';

    public const VARIANT_SPEAKER = 'speaker';

    /**
     * Akce v okolí, na které v mailu zveme. Ručně udržovaný seznam —
     * všechny údaje ověřené na stránkách pořadatelů (11. 9. 2026).
     * `time` nechávej prázdné, dokud není čas spolehlivě ověřený.
     *
     * @var list<array{title: string, organizer: string, date: string, time: string, venue: string, program: list<string>, url: string, note: string}>
     */
    public const UPCOMING_EXTERNAL = [
        [
            'title' => 'Humanoidní roboti v Jihlavě: co už umí a kde zatím selhávají?',
            'organizer' => 'GDG Jihlava',
            'date' => 'čtvrtek 17. září 2026',
            'time' => '17:00',
            'venue' => 'Vysoká škola polytechnická Jihlava, Tolstého 1556',
            'program' => [
                'Dva humanoidní roboti na platformě Unitree G1 naživo — Senti (Sentio Robotix) s vlastními chapadly a AI mozkem nad Gemini, a Karel (Česká IT Akademie).',
                'Martin Krček a tým České IT Akademie o tom, co roboti reálně zvládnou: výdrž baterie, spolehlivost úchopu, latence odezvy.',
            ],
            'url' => 'https://gdg.community.dev/e/mnj3ws/',
            'note' => 'Vstup zdarma, registrace nutná.',
        ],
        [
            'title' => 'AI Monday Jihlava #8',
            'organizer' => 'AI Monday',
            'date' => 'pondělí 21. září 2026',
            'time' => '18:00',
            'venue' => 'Pivovarská restaurace, Vrchlického 2, Jihlava',
            'program' => [
                'Ondřej Hanousek — Příběh Cashe: Od promptu k ledgeru a zase zpátky',
                'Radim Světlák — Malé AI modely na slabém hardwaru',
            ],
            'url' => 'https://www.aimonday.cz/events/ai-monday-jihlava-8',
            'note' => 'Registrace nutná, kapacita se plní.',
        ],
    ];

    /**
     * @param  list<string>  $attachmentPaths  absolutní cesty k PDF prezentacím
     */
    public function __construct(
        public readonly Event $event,
        public readonly string $variant = self::VARIANT_ATTENDED,
        public readonly string $recipientName = '',
        public readonly string $unsubscribeUrl = '',
        public readonly array $attachmentPaths = [],
        /** Název přednášky adresáta — jen pro variantu `speaker`. */
        public readonly string $talkTitle = '',
    ) {}

    public function envelope(): Envelope
    {
        $subject = match ($this->variant) {
            self::VARIANT_ATTENDED => 'Díky za ŽďárAI #3 — prezentace v příloze a tipy na Jihlavu',
            self::VARIANT_SPEAKER => 'Díky za přednášku na ŽďárAI #3',
            default => 'Co zaznělo na ŽďárAI #3 — prezentace v příloze a tipy na Jihlavu',
        };

        return new Envelope(
            replyTo: ['klara@zdarai.cz'],
            subject: $subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.event-follow-up',
            with: ['externalEvents' => self::UPCOMING_EXTERNAL],
        );
    }

    /**
     * @return list<Attachment>
     */
    public function attachments(): array
    {
        return array_map(
            static fn (string $path): Attachment => Attachment::fromPath($path)->withMime('application/pdf'),
            $this->attachmentPaths,
        );
    }
}

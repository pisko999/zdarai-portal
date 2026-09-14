<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Mail\AfterPartyPollInvite;
use App\Mail\EventFollowUp;
use App\Mail\EventNewsletter;
use App\Mail\EventReminder;
use App\Mail\FirstMeetupInfo;
use App\Mail\RegistrationApproved;
use App\Mail\RegistrationConfirmed;
use App\Mail\RegistrationWaitlisted;
use App\Mail\SpeakerPollLinkUpdate;
use App\Mail\SpeakerWelcome;
use App\Models\Event;
use App\Models\Registration;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Livewire\Component;
use Throwable;

/**
 * Náhled e-mailových šablon v administraci — pouze ke čtení.
 *
 * Komponenta nic neodesílá, nic neukládá do DB ani nezařazuje do fronty:
 * mailable se jen sestaví nad ukázkovými daty a vyrenderuje přes ->render().
 *
 * Konstruktory mailables se liší (Event / Registration / prosté řetězce),
 * proto je mapování šablon explicitní — viz self::TEMPLATES (popisky)
 * a self::makeMailable() (sestavení). Žádná reflexe, žádné hádání.
 */
final class NewsletterPreview extends Component
{
    /** Ukázkový token registrace — nikdy neexistuje v DB, slouží jen k sestavení odkazů. */
    private const SAMPLE_TOKEN = '00000000-0000-4000-8000-0000000000ff';

    private const SAMPLE_NAME = 'Jan Ukázka';

    private const SAMPLE_EMAIL = 'ukazka@example.com';

    /**
     * Katalog šablon. Klíč = hodnota ve <select>, musí mít protějšek v makeMailable().
     *
     * @var array<string, array{label: string, group: string, note: string}>
     */
    public const TEMPLATES = [
        'event-newsletter' => [
            'label' => 'Newsletter k akci',
            'group' => 'Newsletter',
            'note' => 'Pozvánka na akci pro odběratele newsletteru. Program se bere z přednášek vybrané akce.',
        ],
        'event-follow-up-attended' => [
            'label' => 'Follow-up — účastníkům (attended)',
            'group' => 'Newsletter',
            'note' => 'Poděkování těm, kdo na akci dorazili. Prezentace jdou v příloze.',
        ],
        'event-follow-up-missed' => [
            'label' => 'Follow-up — nedorazivším (missed)',
            'group' => 'Newsletter',
            'note' => 'Shrnutí pro odběratele, kteří na akci nebyli.',
        ],
        'event-follow-up-speaker' => [
            'label' => 'Follow-up — přednášejícím (speaker)',
            'group' => 'Newsletter',
            'note' => 'Poděkování přednášejícím dané akce.',
        ],
        'event-reminder' => [
            'label' => 'Připomínka akce (3 dny předem)',
            'group' => 'Registrace',
            'note' => 'Automatická připomínka z příkazu reminders:send.',
        ],
        'first-meetup-info' => [
            'label' => 'Info před akcí (mapa + anketa)',
            'group' => 'Registrace',
            'note' => 'Mapa je v mailu vložená jako cid: příloha — v náhledu se proto nezobrazí.',
        ],
        'registration-confirmed' => [
            'label' => 'Registrace přijata',
            'group' => 'Registrace',
            'note' => 'Náhled ukazuje variantu s odkazem na nastavení hesla.',
        ],
        'registration-approved' => [
            'label' => 'Registrace potvrzena',
            'group' => 'Registrace',
            'note' => 'Ukázková registrace nemá účet, blok „nastavit heslo“ se proto nevykreslí.',
        ],
        'registration-waitlisted' => [
            'label' => 'Čekací listina',
            'group' => 'Registrace',
            'note' => 'Odchází při naplněné kapacitě akce.',
        ],
        'after-party-poll-invite' => [
            'label' => 'Pozvánka do ankety (posezení po akci)',
            'group' => 'Anketa',
            'note' => 'Odkaz míří na anketu s ukázkovým tokenem — reálně nefunguje.',
        ],
        'speaker-poll-link-update' => [
            'label' => 'Oprava odkazu na anketu',
            'group' => 'Anketa',
            'note' => 'Opravný mail k rozbitému odkazu do ankety.',
        ],
        'speaker-welcome' => [
            'label' => 'Uvítání přednášejícího (přístup do portálu)',
            'group' => 'Přednášející',
            'note' => 'Nezávisí na akci. Heslo v náhledu je ukázkové.',
        ],
    ];

    public string $template = 'event-newsletter';

    public ?int $eventId = null;

    public function mount(): void
    {
        $event = Event::query()->where('date', '>=', now()->startOfDay())->orderBy('date')->first()
            ?? Event::query()->orderByDesc('date')->first();

        $this->eventId = $event?->id;
    }

    public function render(): View
    {
        return view('livewire.admin.newsletter-preview', [
            'events' => $this->events(),
            'groupedTemplates' => $this->groupedTemplates(),
            'preview' => $this->preview(),
        ]);
    }

    /**
     * @return Collection<int, Event>
     */
    private function events(): Collection
    {
        return Event::query()->orderByDesc('date')->get();
    }

    /**
     * @return array<string, array<string, string>>  skupina => [klíč => popisek]
     */
    private function groupedTemplates(): array
    {
        $grouped = [];

        foreach (self::TEMPLATES as $key => $meta) {
            $grouped[$meta['group']][$key] = $meta['label'];
        }

        return $grouped;
    }

    /**
     * Sestaví a vyrenderuje náhled zvolené šablony.
     *
     * @return array{subject: string, html: string, attachments: list<array{name: string, size: string}>, error: string|null}|null
     */
    private function preview(): ?array
    {
        if (! isset(self::TEMPLATES[$this->template])) {
            return null;
        }

        $event = $this->eventId !== null
            ? Event::query()->with('talks.speaker')->find($this->eventId)
            : null;

        if ($event === null && $this->needsEvent($this->template)) {
            return null;
        }

        $attachmentPaths = $this->attachmentPathsFor($this->template);

        try {
            $mailable = $this->makeMailable($this->template, $event, $attachmentPaths);

            // Všechny mailables projektu definují envelope(); guard drží náhled
            // funkční i pro šablonu, která by ji v budoucnu neměla.
            $subject = method_exists($mailable, 'envelope')
                ? (string) $mailable->envelope()->subject
                : (string) ($mailable->subject ?? '');

            return [
                'subject' => $subject,
                'html' => $mailable->render(),
                'attachments' => $this->describeAttachments($attachmentPaths),
                'error' => null,
            ];
        } catch (Throwable $e) {
            return [
                'subject' => '',
                'html' => '',
                'attachments' => [],
                'error' => $e->getMessage(),
            ];
        }
    }

    /** Jediná šablona nezávislá na akci je uvítání přednášejícího. */
    private function needsEvent(string $template): bool
    {
        return $template !== 'speaker-welcome';
    }

    /**
     * Explicitní mapování šablona → konstruktor mailable.
     *
     * @param  list<string>  $attachmentPaths
     */
    private function makeMailable(string $template, ?Event $event, array $attachmentPaths): Mailable
    {
        // speaker-welcome akci nepotřebuje, ostatní ano — ošetřeno v preview().
        if ($template === 'speaker-welcome') {
            return new SpeakerWelcome(
                name: self::SAMPLE_NAME,
                email: self::SAMPLE_EMAIL,
                password: 'ukazkove-heslo',
                loginUrl: route('login'),
            );
        }

        /** @var Event $event */
        $registration = $this->sampleRegistration($event);
        $unsubscribeUrl = route('newsletter.unsubscribe', self::SAMPLE_TOKEN);

        return match ($template) {
            'event-newsletter' => new EventNewsletter(
                event: $event,
                unsubscribeUrl: $unsubscribeUrl,
            ),

            // Pojmenované argumenty — konstruktor EventFollowUp se rozrůstá,
            // pozice parametrů se na to nedá spolehnout.
            'event-follow-up-attended' => new EventFollowUp(
                event: $event,
                variant: EventFollowUp::VARIANT_ATTENDED,
                recipientName: self::SAMPLE_NAME,
                unsubscribeUrl: $unsubscribeUrl,
                attachmentPaths: $attachmentPaths,
            ),
            'event-follow-up-missed' => new EventFollowUp(
                event: $event,
                variant: EventFollowUp::VARIANT_MISSED,
                recipientName: self::SAMPLE_NAME,
                unsubscribeUrl: $unsubscribeUrl,
                attachmentPaths: $attachmentPaths,
            ),
            'event-follow-up-speaker' => new EventFollowUp(
                event: $event,
                variant: EventFollowUp::VARIANT_SPEAKER,
                recipientName: self::SAMPLE_NAME,
                unsubscribeUrl: $unsubscribeUrl,
                attachmentPaths: $attachmentPaths,
                talkTitle: (string) ($event->talks->first()?->title ?? ''),
            ),

            'event-reminder' => new EventReminder(registration: $registration),
            'first-meetup-info' => new FirstMeetupInfo(registration: $registration),
            'registration-confirmed' => new RegistrationConfirmed(
                registration: $registration,
                setPasswordUrl: url('/reset-password/' . self::SAMPLE_TOKEN . '?email=' . urlencode(self::SAMPLE_EMAIL)),
            ),
            'registration-approved' => new RegistrationApproved(registration: $registration),
            'registration-waitlisted' => new RegistrationWaitlisted(registration: $registration),
            'after-party-poll-invite' => new AfterPartyPollInvite(registration: $registration),
            'speaker-poll-link-update' => new SpeakerPollLinkUpdate(registration: $registration),
        };
    }

    /**
     * Ukázková registrace — NEPERSISTOVANÁ instance, do DB se nikdy nezapisuje.
     * Vazba na akci se nastavuje ručně, aby se nedotazovala databáze.
     */
    private function sampleRegistration(Event $event): Registration
    {
        $registration = new Registration([
            'event_id' => $event->id,
            'name' => self::SAMPLE_NAME,
            'email' => self::SAMPLE_EMAIL,
            'token' => self::SAMPLE_TOKEN,
            'payment_status' => 'free',
            'email_opt_out' => false,
            'is_speaker' => false,
        ]);

        $registration->setRelation('event', $event);
        $registration->setRelation('user', null);

        return $registration;
    }

    /**
     * Přílohy bere follow-up ze stejné složky jako příkaz followup:send.
     * Čte se jen výpis adresáře, soubory se nikam nekopírují.
     *
     * @return list<string>
     */
    private function attachmentPathsFor(string $template): array
    {
        if (! str_starts_with($template, 'event-follow-up')) {
            return [];
        }

        $dir = (string) config('zdarai.presentations_dir', storage_path('app/private/prezentace'));
        $files = glob($dir . '/*.pdf') ?: [];
        sort($files);

        return array_values($files);
    }

    /**
     * @param  list<string>  $paths
     * @return list<array{name: string, size: string}>
     */
    private function describeAttachments(array $paths): array
    {
        $out = [];

        foreach ($paths as $path) {
            $bytes = is_file($path) ? (int) filesize($path) : 0;

            $out[] = [
                'name' => basename($path),
                'size' => number_format($bytes / 1024, 0, ',', ' ') . ' kB',
            ];
        }

        return $out;
    }
}

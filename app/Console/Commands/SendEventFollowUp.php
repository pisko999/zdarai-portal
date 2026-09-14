<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Mail\EventFollowUp;
use App\Models\Event;
use App\Models\Registration;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

/**
 * Follow-up po proběhlé akci ve dvou variantách bez překryvu publika:
 *   attended — registrovaní na dané akci (poděkování),
 *   missed   — aktivní odběratelé newsletteru + registrovaní na starších
 *              akcích, mínus segment attended („co jste propásli").
 * Nikdo nedostane obě varianty.
 */
class SendEventFollowUp extends Command
{
    protected $signature = 'followup:send
        {event : ID proběhlé akce}
        {--segment=all : attended | missed | speakers | all (speakers je vždy jen na výslovné vyžádání)}
        {--test= : Poslat jen testovací e-mail na zadanou adresu (obě varianty)}
        {--dry-run : Jen vypsat příjemce, nic neodesílat}
        {--no-attachments : Odeslat bez PDF příloh}
        {--exclude= : Čárkou oddělené e-maily, které vynechat (organizátoři apod.)}
        {--keep-speakers : Neodfiltrovávat přednášející akce}';

    protected $description = 'Rozešle follow-up po proběhlé akci — poděkování účastníkům a shrnutí ostatním, s prezentacemi v příloze.';

    private const ATTACHMENT_DIR = 'app/private/prezentace';

    /** Přepisovatelné v testech přes config, ať nezávisí na reálném storage. */
    private function attachmentDir(): string
    {
        return (string) config('zdarai.presentations_dir', storage_path(self::ATTACHMENT_DIR));
    }

    public function handle(): int
    {
        $event = Event::with('talks.speaker')->find((int) $this->argument('event'));

        if (! $event) {
            $this->error('Událost nenalezena.');

            return self::FAILURE;
        }

        $attachments = $this->option('no-attachments') ? [] : $this->collectAttachments();

        if ($attachments === [] && ! $this->option('no-attachments')) {
            $this->error('Ve složce ' . $this->attachmentDir() . ' nejsou žádná PDF.');
            $this->line('Nahraj prezentace, nebo spusť s --no-attachments.');

            return self::FAILURE;
        }

        $this->info("Událost: {$event->title} ({$event->date->format('j. n. Y H:i')})");
        foreach ($attachments as $path) {
            $this->line('  📎 ' . basename($path) . ' (' . number_format(filesize($path) / 1024, 0, ',', ' ') . ' kB)');
        }

        if ($testAddress = $this->option('test')) {
            foreach ([EventFollowUp::VARIANT_ATTENDED, EventFollowUp::VARIANT_MISSED] as $variant) {
                Mail::to((string) $testAddress)->queue(
                    new EventFollowUp($event, $variant, 'Petře', url('/'), $attachments)
                );
                $this->line("  → testovací varianta „{$variant}“ zařazena do fronty");
            }

            $this->info("Testovací e-maily zařazeny na: {$testAddress}");

            return self::SUCCESS;
        }

        if ($this->option('segment') === 'speakers') {
            return $this->dispatchSegments($event, [
                EventFollowUp::VARIANT_SPEAKER => $this->speakersSegment($event),
            ], $attachments);
        }

        $attended = $this->attendedSegment($event);
        $missed = $this->missedSegment($event, $attended->keys()->all());

        // Lidé se registrují pod víc adresami. Dedup podle e-mailu je nechytí,
        // takže sjednocujeme ještě podle normalizovaného jména.
        $missed = $this->dropByName($missed, $attended, 'už je v segmentu attended');
        $missed = $this->dedupeByName($missed);

        // Přednášející akce tam byli, i když registraci nemají — „nedorazili
        // jste" by jim přišlo minimálně divně. Zaslouží si osobní poděkování.
        if (! $this->option('keep-speakers')) {
            $speakers = $this->speakerNames($event);
            $attended = $this->dropNames($attended, $speakers, 'přednášející');
            $missed = $this->dropNames($missed, $speakers, 'přednášející');
        }

        if ($raw = $this->option('exclude')) {
            $excluded = collect(explode(',', (string) $raw))
                ->map(static fn (string $e): string => mb_strtolower(trim($e)))
                ->filter()
                ->all();

            foreach ($excluded as $email) {
                foreach ([$attended, $missed] as $segment) {
                    if ($segment->has($email)) {
                        $this->line("  ⊘ {$email} — vynecháno (--exclude)");
                    }
                }
            }

            $attended = $attended->except($excluded);
            $missed = $missed->except($excluded);
        }

        $segments = match ($this->option('segment')) {
            'attended' => [EventFollowUp::VARIANT_ATTENDED => $attended],
            'missed' => [EventFollowUp::VARIANT_MISSED => $missed],
            default => [
                EventFollowUp::VARIANT_ATTENDED => $attended,
                EventFollowUp::VARIANT_MISSED => $missed,
            ],
        };

        return $this->dispatchSegments($event, $segments, $attachments);
    }

    /**
     * @param  array<string, Collection<string, array{name: string, unsubscribe: string, talk?: string}>>  $segments
     * @param  list<string>  $attachments
     */
    private function dispatchSegments(Event $event, array $segments, array $attachments): int
    {
        $total = 0;

        foreach ($segments as $variant => $recipients) {
            $this->newLine();
            $this->comment(sprintf('Segment „%s“ — %d příjemců', $variant, $recipients->count()));

            foreach ($recipients as $email => $recipient) {
                $this->line("  → {$email} ({$recipient['name']})");

                if (! $this->option('dry-run')) {
                    Mail::to($email)->queue(new EventFollowUp(
                        $event,
                        $variant,
                        $recipient['name'],
                        $recipient['unsubscribe'],
                        $attachments,
                        $recipient['talk'] ?? '',
                    ));
                }

                $total++;
            }
        }

        $this->newLine();

        if ($this->option('dry-run')) {
            $this->warn("DRY RUN — nic neodesláno. Příjemců by bylo: {$total}.");

            return self::SUCCESS;
        }

        $this->info("Zařazeno do fronty: {$total} e-mailů.");

        return self::SUCCESS;
    }

    /**
     * Přednášející akce. Tabulka speakers nemá e-mail, takže ho dohledáváme
     * v registracích podle normalizovaného jména — kdo přednášel, bývá
     * registrovaný z některé starší akce.
     *
     * @return Collection<string, array{name: string, unsubscribe: string, talk: string}>
     */
    private function speakersSegment(Event $event): Collection
    {
        // Bez filtru na email_opt_out: ten příznak se nastavuje přes /opt-out/{token},
        // jehož stránka slibuje pouze „nebudeme vám zasílat připomínky". Osobní
        // poděkování za přednášku připomínka není — ale ať je to vidět, vypíšeme to.
        $candidates = Registration::orderBy('id')->get();
        $out = collect();

        foreach ($event->talks->sortBy('sort_order') as $talk) {
            if (! $talk->speaker) {
                continue;
            }

            $wanted = $this->normalizeName($talk->speaker->name);
            $match = $candidates->first(fn (Registration $r): bool => $this->normalizeName($r->name) === $wanted);

            if (! $match) {
                $this->warn("  ⚠ {$talk->speaker->name} — e-mail nedohledán v registracích, vynecháno");

                continue;
            }

            if ($match->email_opt_out) {
                $this->warn("  ⚠ {$match->email} má email_opt_out (odhlášené připomínky z akce #{$match->event_id}) — posíláme přesto, jde o osobní poděkování, ne připomínku");
            }

            $out->put(mb_strtolower(trim($match->email)), [
                // Oslovení bez jména: vokativ z databáze spolehlivě neposkládáme.
                'name' => '',
                'unsubscribe' => route('opt-out', $match->token),
                'talk' => (string) $talk->title,
            ]);
        }

        return $out;
    }

    /**
     * Registrovaní na dané akci — poděkování.
     *
     * @return Collection<string, array{name: string, unsubscribe: string}>
     */
    private function attendedSegment(Event $event): Collection
    {
        return $this->fromRegistrations(
            Registration::where('event_id', $event->id)
        );
    }

    /**
     * Odběratelé newsletteru + registrovaní na starších akcích, mínus segment attended.
     *
     * @param  list<string>  $exclude  e-maily ze segmentu attended (lowercase)
     * @return Collection<string, array{name: string, unsubscribe: string}>
     */
    private function missedSegment(Event $event, array $exclude): Collection
    {
        $older = $this->fromRegistrations(
            Registration::where('event_id', '!=', $event->id)
        );

        $subscribers = collect();

        foreach (DB::table('newsletter_subscribers')->whereNull('unsubscribed_at')->get() as $sub) {
            $subscribers->put(mb_strtolower(trim($sub->email)), [
                'name' => (string) ($sub->name ?? ''),
                'unsubscribe' => route('newsletter.unsubscribe', $sub->token),
            ]);
        }

        // Odběratelský token má přednost — je to newsletter, ne mail k registraci.
        return $older->merge($subscribers)->except($exclude);
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder<Registration>  $query
     * @return Collection<string, array{name: string, unsubscribe: string}>
     */
    private function fromRegistrations($query): Collection
    {
        $out = collect();

        $registrations = $query
            ->whereNotIn('payment_status', ['cancelled', 'waitlist'])
            ->where('email_opt_out', false)
            ->orderBy('id')
            ->get();

        foreach ($registrations as $registration) {
            $out->put(mb_strtolower(trim($registration->email)), [
                'name' => (string) $registration->name,
                'unsubscribe' => route('opt-out', $registration->token),
            ]);
        }

        return $out;
    }

    /**
     * Jména přednášejících dané akce (normalizovaná).
     *
     * @return list<string>
     */
    private function speakerNames(Event $event): array
    {
        return $event->talks
            ->map(static fn ($talk) => $talk->speaker?->name)
            ->filter()
            ->map(fn (string $name): string => $this->normalizeName($name))
            ->unique()
            ->values()
            ->all();
    }

    /**
     * Odstraní z $segment příjemce, jejichž jméno je i v $other.
     *
     * @param  Collection<string, array{name: string, unsubscribe: string}>  $segment
     * @param  Collection<string, array{name: string, unsubscribe: string}>  $other
     * @return Collection<string, array{name: string, unsubscribe: string}>
     */
    private function dropByName(Collection $segment, Collection $other, string $reason): Collection
    {
        $names = $other->map(fn (array $r): string => $this->normalizeName($r['name']))
            ->filter()
            ->unique()
            ->all();

        return $this->dropNames($segment, array_values($names), $reason);
    }

    /**
     * @param  Collection<string, array{name: string, unsubscribe: string}>  $segment
     * @param  list<string>  $names  normalizovaná jména
     * @return Collection<string, array{name: string, unsubscribe: string}>
     */
    private function dropNames(Collection $segment, array $names, string $reason): Collection
    {
        return $segment->reject(function (array $recipient, string $email) use ($names, $reason): bool {
            $hit = in_array($this->normalizeName($recipient['name']), $names, true);

            if ($hit) {
                $this->line("  ⊘ {$email} ({$recipient['name']}) — vynecháno: {$reason}");
            }

            return $hit;
        });
    }

    /**
     * Jedna osoba s víc adresami dostane mail jen jednou (na první adresu).
     *
     * @param  Collection<string, array{name: string, unsubscribe: string}>  $segment
     * @return Collection<string, array{name: string, unsubscribe: string}>
     */
    private function dedupeByName(Collection $segment): Collection
    {
        $seen = [];

        return $segment->reject(function (array $recipient, string $email) use (&$seen): bool {
            $name = $this->normalizeName($recipient['name']);

            if ($name === '') {
                return false;
            }

            if (isset($seen[$name])) {
                $this->line("  ⊘ {$email} ({$recipient['name']}) — vynecháno: duplicita osoby, už píšeme na {$seen[$name]}");

                return true;
            }

            $seen[$name] = $email;

            return false;
        });
    }

    private function normalizeName(string $name): string
    {
        $ascii = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $name);

        return preg_replace('/[^a-z]/', '', mb_strtolower((string) $ascii)) ?? '';
    }

    /**
     * @return list<string>
     */
    private function collectAttachments(): array
    {
        $files = glob($this->attachmentDir() . '/*.pdf') ?: [];
        sort($files);

        return array_values($files);
    }
}

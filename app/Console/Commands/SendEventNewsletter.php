<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Mail\EventNewsletter;
use App\Models\Event;
use App\Models\NewsletterEventSend;
use App\Models\NewsletterSubscriber;
use Illuminate\Console\Command;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Mail;
use Throwable;

/**
 * Rozešle newsletter k akci ŽďárAI (App\Mail\EventNewsletter) aktivním
 * odběratelům newsletteru se souhlasem (TF #3933).
 *
 * Ochrana proti dvojímu odeslání: claim = řádek v newsletter_event_sends
 * s unique(event_id, newsletter_subscriber_id). Insert uspěje jen jednou,
 * i při souběžném běhu; při selhání zařazení do fronty se claim smaže.
 */
class SendEventNewsletter extends Command
{
    protected $signature = 'newsletter:send
        {event : ID nebo slug akce}
        {--test= : Poslat jen jeden testovací e-mail na zadanou adresu}
        {--dry-run : Jen vypsat příjemce, nic neodesílat ani neclaimovat}
        {--exclude= : Čárkou oddělené e-maily, které vynechat}
        {--force : Neptat se na potvrzení (neinteraktivní běh)}';

    protected $description = 'Rozešle newsletter k akci aktivním odběratelům newsletteru se souhlasem.';

    public function handle(): int
    {
        $needle = (string) $this->argument('event');

        // Slug má přednost; čistě číselný argument se pak zkusí jako ID.
        $event = Event::query()->with(['talks.speaker', 'translations'])->where('slug', $needle)->first()
            ?? (ctype_digit($needle)
                ? Event::query()->with(['talks.speaker', 'translations'])->find((int) $needle)
                : null);

        if ($event === null) {
            $this->error("Akce „{$needle}“ nenalezena (zadej ID nebo slug).");

            return self::FAILURE;
        }

        $this->info("Akce: #{$event->id} {$event->title} ({$event->date->format('j. n. Y H:i')}, {$event->status})");

        $problem = $this->eventProblem($event);

        if ($testAddress = $this->option('test')) {
            if ($problem !== null) {
                $this->warn("Pozor: {$problem} Ostrá rozesílka by byla odmítnuta.");
            }

            Mail::to((string) $testAddress)->queue(new EventNewsletter($event, url('/')));
            $this->info("Testovací e-mail zařazen na: {$testAddress}");

            return self::SUCCESS;
        }

        $recipients = $this->excludeOption($this->subscribers());
        $alreadySent = NewsletterEventSend::query()
            ->where('event_id', $event->id)
            ->pluck('newsletter_subscriber_id')
            ->flip();

        $this->newLine();
        $this->comment(sprintf('Příjemci — %d', $recipients->count()));
        foreach ($recipients as $email => $recipient) {
            $name = $recipient['name'] !== '' ? " ({$recipient['name']})" : '';
            $sent = $alreadySent->has($recipient['id']) ? ' [už odesláno]' : '';
            $this->line("  → {$email}{$name}{$sent}");
        }
        $this->newLine();

        if ($this->option('dry-run')) {
            if ($problem !== null) {
                $this->warn("Pozor: {$problem} Ostrá rozesílka by byla odmítnuta.");
            }
            $pending = $recipients->filter(fn (array $r): bool => ! $alreadySent->has($r['id']))->count();
            $this->warn("DRY RUN — nic neodesláno. Příjemců by bylo: {$recipients->count()} (z toho dosud neodesláno: {$pending}).");

            return self::SUCCESS;
        }

        if ($problem !== null) {
            $this->error("{$problem} Nic neodesláno.");

            return self::FAILURE;
        }

        if ($recipients->isEmpty()) {
            $this->warn('Žádní příjemci, nic k odeslání.');

            return self::SUCCESS;
        }

        if (! $this->option('force') && ! $this->input->isInteractive()) {
            $this->error('Neinteraktivní běh bez --force — nic neodesláno.');

            return self::FAILURE;
        }

        if (! $this->option('force')
            && ! $this->confirm("Opravdu rozeslat newsletter k akci „{$event->title}“ {$recipients->count()} příjemcům?", false)
        ) {
            $this->warn('Zrušeno — nic neodesláno.');

            return self::FAILURE;
        }

        $queued = 0;
        $skipped = 0;
        $failed = 0;

        foreach ($recipients as $email => $recipient) {
            // Atomický claim: unique(event_id, newsletter_subscriber_id) pustí insert jen jednou.
            try {
                $claim = NewsletterEventSend::create([
                    'event_id' => $event->id,
                    'newsletter_subscriber_id' => $recipient['id'],
                    'email' => $email,
                    'sent_at' => now(),
                ]);
            } catch (UniqueConstraintViolationException) {
                $this->line("  ⊘ {$email} — už odesláno, přeskočeno");
                $skipped++;

                continue;
            }

            try {
                Mail::to($email)->queue(new EventNewsletter($event, $recipient['unsubscribe']));
                $queued++;
            } catch (Throwable $e) {
                // Zařazení selhalo → uvolni claim, ať to příští běh zkusí znovu.
                $claim->delete();
                report($e);
                $this->error("  ✗ {$email} — nepodařilo se zařadit: {$e->getMessage()}");
                $failed++;
            }
        }

        $this->newLine();
        $this->info("Zařazeno do fronty: {$queued}. Přeskočeno (už odesláno): {$skipped}."
            . ($failed > 0 ? " Selhalo: {$failed}." : ''));

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }

    /**
     * Důvod, proč akci nelze ostře rozeslat (null = v pořádku).
     */
    private function eventProblem(Event $event): ?string
    {
        if ($event->status !== 'published') {
            return "Akce není publikovaná (status {$event->status}).";
        }

        if ($event->date->isPast()) {
            return 'Akce už proběhla.';
        }

        return null;
    }

    /**
     * Aktivní odběratelé se souhlasem, e-mail normalizovaný a unikátní
     * (první záznam podle id vyhrává).
     *
     * @return Collection<string, array{id: int, name: string, unsubscribe: string}>
     */
    private function subscribers(): Collection
    {
        $out = collect();

        $subscribers = NewsletterSubscriber::active()
            ->whereNotNull('consent_at')
            ->orderBy('id')
            ->get();

        foreach ($subscribers as $sub) {
            $email = mb_strtolower(trim((string) $sub->email));

            if ($email === '' || $out->has($email)) {
                continue;
            }

            $out->put($email, [
                'id' => (int) $sub->id,
                'name' => (string) ($sub->name ?? ''),
                'unsubscribe' => route('newsletter.unsubscribe', $sub->token),
            ]);
        }

        return $out;
    }

    /**
     * @param  Collection<string, array{id: int, name: string, unsubscribe: string}>  $out
     * @return Collection<string, array{id: int, name: string, unsubscribe: string}>
     */
    private function excludeOption(Collection $out): Collection
    {
        if ($raw = $this->option('exclude')) {
            $excluded = collect(explode(',', (string) $raw))
                ->map(static fn (string $e): string => mb_strtolower(trim($e)))
                ->filter()
                ->all();

            foreach ($excluded as $email) {
                if ($out->has($email)) {
                    $this->line("  ⊘ {$email} — vynecháno (--exclude)");
                }
            }

            $out = $out->except($excluded);
        }

        return $out;
    }
}

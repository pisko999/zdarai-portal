<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Mail\PartnerEventInvite;
use App\Models\NewsletterSubscriber;
use App\Models\Registration;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Throwable;

/**
 * Rozešle pozvánku na akci spřátelené komunity (config/partner_events.php)
 * aktivním odběratelům newsletteru se souhlasem (--segment=subscribers),
 * účastníkům akcí ŽďárAI, kteří nejsou v newsletteru (--segment=registrants),
 * nebo jednorázovému seznamu z CSV od pořadatele (--segment=list --list=<cesta>).
 *
 * Segment list: CSV s hlavičkou Name,Email,Company,Role,Status,Registration Date.
 * Company může obsahovat neuvozovkovanou čárku → e-mail = sloupec 1, jméno =
 * sloupec 0, status = předposlední sloupec. Vynechá status cancelled (pokud
 * není --include-cancelled) a neplatné e-maily (varování). Mail jde s audience
 * 'list' bez odhlašovacího odkazu, s podepsaným opt-in odkazem do newsletteru
 * (per příjemce); obsah CSV se nikam neukládá.
 *
 * Doporučené pořadí rozesílky: list → subscribers → registrants.
 *
 * Ochrana proti dvojímu odeslání: atomický claim v cache (redis) per
 * klíč akce + sha1(e-mail), platný 90 dní. Klíč je společný pro všechny segmenty,
 * takže nikdo nedostane pozvánku dvakrát.
 */
class SendPartnerInvite extends Command
{
    private const SEGMENTS = ['list', 'subscribers', 'registrants'];

    protected $signature = 'partner-invite:send
        {key : Klíč akce v config/partner_events.php}
        {--segment=subscribers : Segment příjemců: list | subscribers | registrants (doporučené pořadí: list → subscribers → registrants)}
        {--list= : Cesta k CSV (povinné pro --segment=list), relativně k base_path nebo absolutní}
        {--test= : Poslat jen jeden testovací e-mail na zadanou adresu}
        {--dry-run : Jen vypsat příjemce, nic neodesílat ani neclaimovat}
        {--exclude= : Čárkou oddělené e-maily, které vynechat}
        {--include-cancelled : Segment list: zahrnout i řádky se statusem cancelled}
        {--force : Neptat se na potvrzení (neinteraktivní běh)}';

    protected $description = 'Rozešle pozvánku na akci spřátelené komunity (segmenty list | subscribers | registrants; doporučené pořadí list → subscribers → registrants).';

    public function handle(): int
    {
        $key = (string) $this->argument('key');
        $partnerEvent = config("partner_events.{$key}");

        if (! is_array($partnerEvent) || $partnerEvent === []) {
            $this->error("Neznámý klíč akce „{$key}“ (viz config/partner_events.php).");

            return self::FAILURE;
        }

        $segment = (string) $this->option('segment');

        if (! in_array($segment, self::SEGMENTS, true)) {
            $this->error("Neplatný segment „{$segment}“ (povoleno: " . implode(', ', self::SEGMENTS) . ').');

            return self::FAILURE;
        }

        $audience = match ($segment) {
            'registrants' => 'registrant',
            'list' => 'list',
            default => 'subscriber',
        };

        $this->info("Akce: {$partnerEvent['title']} ({$partnerEvent['date_label']})");
        $this->info("Segment: {$segment}");

        if ($testAddress = $this->option('test')) {
            Mail::to((string) $testAddress)->queue(
                new PartnerEventInvite(
                    $partnerEvent,
                    $audience === 'list' ? '' : url('/'),
                    $audience,
                    mb_strtolower(trim((string) $testAddress)),
                )
            );
            $this->info("Testovací e-mail zařazen na: {$testAddress}");

            return self::SUCCESS;
        }

        if ($this->option('include-cancelled') && $segment !== 'list') {
            $this->error('--include-cancelled platí jen pro --segment=list.');

            return self::FAILURE;
        }

        if ($segment === 'list') {
            $listRecipients = $this->listRecipients();

            if ($listRecipients === null) {
                return self::FAILURE;
            }
        }

        $recipients = $this->excludeOption(match ($segment) {
            'registrants' => $this->registrants(),
            'list' => $listRecipients,
            default => $this->subscribers(),
        });

        $this->newLine();
        $this->comment(sprintf('Příjemci (segment %s) — %d', $segment, $recipients->count()));
        foreach ($recipients as $email => $recipient) {
            $name = $recipient['name'] !== '' ? " ({$recipient['name']})" : '';
            $this->line("  → {$email}{$name}");
        }
        $this->newLine();

        if ($this->option('dry-run')) {
            $this->warn("DRY RUN — nic neodesláno. Příjemců by bylo: {$recipients->count()} (segment {$segment}).");

            return self::SUCCESS;
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
            && ! $this->confirm("Opravdu rozeslat pozvánku {$recipients->count()} příjemcům (segment {$segment})?", false)
        ) {
            $this->warn('Zrušeno — nic neodesláno.');

            return self::FAILURE;
        }

        $queued = 0;
        $skipped = 0;
        $failed = 0;

        foreach ($recipients as $email => $recipient) {
            $claimKey = "partner-invite:{$key}:" . sha1($email);

            // Atomický claim: Cache::add uspěje jen pokud klíč ještě neexistuje.
            if (! Cache::add($claimKey, now()->toIso8601String(), now()->addDays(90))) {
                $this->line("  ⊘ {$email} — už odesláno, přeskočeno");
                $skipped++;

                continue;
            }

            try {
                Mail::to($email)->queue(new PartnerEventInvite($partnerEvent, $recipient['unsubscribe'], $audience, $email));
                $queued++;
            } catch (Throwable $e) {
                // Zařazení selhalo → uvolni claim, ať to příští běh zkusí znovu.
                Cache::forget($claimKey);
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
     * Aktivní odběratelé se souhlasem, e-mail normalizovaný a unikátní
     * (první záznam podle id vyhrává).
     *
     * @return Collection<string, array{name: string, unsubscribe: string}>
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
                'name' => (string) ($sub->name ?? ''),
                'unsubscribe' => route('newsletter.unsubscribe', $sub->token),
            ]);
        }

        return $out;
    }

    /**
     * Účastníci akcí (registrace mimo cancelled/waitlist), kteří nejsou
     * v newsletter_subscribers (ani jako odhlášení) a nemají email_opt_out
     * na žádné své registraci. Jméno a token z nejnovější registrace.
     *
     * @return Collection<string, array{name: string, unsubscribe: string}>
     */
    private function registrants(): Collection
    {
        $normalize = static fn (?string $email): string => mb_strtolower(trim((string) $email));

        $subscriberEmails = NewsletterSubscriber::query()
            ->pluck('email')
            ->map($normalize)
            ->flip();

        $optedOut = Registration::query()
            ->where('email_opt_out', true)
            ->pluck('email')
            ->map($normalize)
            ->flip();

        $out = collect();

        // Sestupně podle id → první výskyt e-mailu = nejnovější registrace.
        $registrations = Registration::query()
            ->whereNotIn('payment_status', ['cancelled', 'waitlist'])
            ->orderByDesc('id')
            ->get(['id', 'name', 'email', 'token']);

        foreach ($registrations as $registration) {
            $email = $normalize($registration->email);

            if ($email === ''
                || $out->has($email)
                || $subscriberEmails->has($email)
                || $optedOut->has($email)
            ) {
                continue;
            }

            $out->put($email, [
                'name' => (string) $registration->name,
                'unsubscribe' => route('opt-out', $registration->token),
            ]);
        }

        return $out->sortKeys();
    }

    /**
     * Příjemci z CSV (--list). Vrací null při chybějícím / nečitelném souboru
     * (chyba už vypsaná). Bez odhlašovacího odkazu — mail je jednorázový.
     *
     * @return Collection<string, array{name: string, unsubscribe: string}>|null
     */
    private function listRecipients(): ?Collection
    {
        $path = trim((string) $this->option('list'));

        if ($path === '') {
            $this->error('Segment list vyžaduje --list=<cesta k CSV>.');

            return null;
        }

        if (! str_starts_with($path, '/')) {
            $path = base_path($path);
        }

        $handle = is_file($path) && is_readable($path) ? @fopen($path, 'rb') : false;

        if ($handle === false) {
            $this->error("Soubor seznamu nelze přečíst: {$path}");

            return null;
        }

        $includeCancelled = (bool) $this->option('include-cancelled');
        $out = collect();
        $warnings = 0;
        $cancelled = 0;
        $line = 0;

        try {
            while (($row = fgetcsv($handle, null, ',', '"', '')) !== false) {
                $line++;

                // Hlavička a prázdné řádky
                if ($line === 1 || $row === [null] || count($row) < 3) {
                    continue;
                }

                $email = mb_strtolower(trim((string) $row[1]));
                $name = trim((string) preg_replace('/\s+/u', ' ', (string) $row[0]));
                $status = mb_strtolower(trim((string) $row[count($row) - 2]));

                if ($status === 'cancelled') {
                    $cancelled++;

                    if (! $includeCancelled) {
                        continue;
                    }
                }

                if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
                    $this->warn("  ! řádek {$line}: neplatný e-mail — vynecháno");
                    $warnings++;

                    continue;
                }

                if ($out->has($email)) {
                    continue;
                }

                $out->put($email, ['name' => $name, 'unsubscribe' => '']);
            }
        } finally {
            fclose($handle);
        }

        $this->line($includeCancelled
            ? "Seznam: {$out->count()} platných, včetně cancelled ({$cancelled} řádků), varování: {$warnings}."
            : "Seznam: {$out->count()} platných, vynecháno cancelled: {$cancelled}, varování: {$warnings}.");

        return $out->sortKeys();
    }

    /**
     * @param  Collection<string, array{name: string, unsubscribe: string}>  $out
     * @return Collection<string, array{name: string, unsubscribe: string}>
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

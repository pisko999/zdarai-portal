<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Mail\PartnerEventInvite;
use App\Models\NewsletterSubscriber;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Throwable;

/**
 * Rozešle pozvánku na akci spřátelené komunity (config/partner_events.php)
 * aktivním odběratelům newsletteru se souhlasem.
 *
 * Ochrana proti dvojímu odeslání: atomický claim v cache (redis) per
 * klíč akce + sha1(e-mail), platný 90 dní.
 */
class SendPartnerInvite extends Command
{
    protected $signature = 'partner-invite:send
        {key : Klíč akce v config/partner_events.php}
        {--test= : Poslat jen jeden testovací e-mail na zadanou adresu}
        {--dry-run : Jen vypsat příjemce, nic neodesílat ani neclaimovat}
        {--exclude= : Čárkou oddělené e-maily, které vynechat}
        {--force : Neptat se na potvrzení (neinteraktivní běh)}';

    protected $description = 'Rozešle pozvánku na akci spřátelené komunity odběratelům newsletteru se souhlasem.';

    public function handle(): int
    {
        $key = (string) $this->argument('key');
        $partnerEvent = config("partner_events.{$key}");

        if (! is_array($partnerEvent) || $partnerEvent === []) {
            $this->error("Neznámý klíč akce „{$key}“ (viz config/partner_events.php).");

            return self::FAILURE;
        }

        $this->info("Akce: {$partnerEvent['title']} ({$partnerEvent['date_label']})");

        if ($testAddress = $this->option('test')) {
            Mail::to((string) $testAddress)->queue(new PartnerEventInvite($partnerEvent, url('/')));
            $this->info("Testovací e-mail zařazen na: {$testAddress}");

            return self::SUCCESS;
        }

        $recipients = $this->recipients();

        $this->newLine();
        $this->comment(sprintf('Příjemci — %d', $recipients->count()));
        foreach ($recipients as $email => $recipient) {
            $name = $recipient['name'] !== '' ? " ({$recipient['name']})" : '';
            $this->line("  → {$email}{$name}");
        }
        $this->newLine();

        if ($this->option('dry-run')) {
            $this->warn("DRY RUN — nic neodesláno. Příjemců by bylo: {$recipients->count()}.");

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
            && ! $this->confirm("Opravdu rozeslat pozvánku {$recipients->count()} příjemcům?", false)
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
                Mail::to($email)->queue(new PartnerEventInvite($partnerEvent, $recipient['unsubscribe']));
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
     * (první záznam podle id vyhrává), mínus --exclude.
     *
     * @return Collection<string, array{name: string, unsubscribe: string}>
     */
    private function recipients(): Collection
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

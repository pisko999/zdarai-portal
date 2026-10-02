<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Mail\EventReminder;
use App\Models\Registration;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SendEventReminders extends Command
{
    protected $signature = 'reminders:send
                            {--dry-run : Jen vypíše kandidáty, nic neclaimuje ani neodesílá}';

    protected $description = 'Odešle emailové připomínky registrovaným účastníkům 3 dny před akcí';

    public function handle(): int
    {
        $threeDaysFromNow = now()->addDays(3);
        $dryRun = (bool) $this->option('dry-run');

        $registrations = Registration::query()
            ->whereNull('reminder_sent_at')
            ->where('email_opt_out', false)
            ->whereNotIn('payment_status', ['cancelled', 'waitlist'])
            ->whereHas('event', fn ($q) => $q
                ->where('status', 'published')
                ->whereBetween('date', [
                    $threeDaysFromNow->copy()->startOfDay(),
                    $threeDaysFromNow->copy()->endOfDay(),
                ])
            )
            ->with('event')
            ->get();

        if ($dryRun) {
            foreach ($registrations as $registration) {
                $this->line("[dry-run] #{$registration->id} {$registration->email}");
            }
            $this->info("Dry-run: {$registrations->count()} kandidátů, nic neodesláno.");

            return Command::SUCCESS;
        }

        $count = 0;
        $skipped = 0;
        $failed = 0;

        foreach ($registrations as $registration) {
            // Atomický claim: uspěje jen jeden proces (affected rows === 1).
            $claimed = Registration::query()
                ->whereKey($registration->getKey())
                ->whereNull('reminder_sent_at')
                ->update(['reminder_sent_at' => now()]);

            if ($claimed !== 1) {
                $skipped++;
                continue;
            }

            try {
                Mail::to($registration->email)->queue(new EventReminder($registration));
                $count++;
            } catch (Throwable $e) {
                // Zařazení do fronty selhalo → uvolni claim, ať to příští běh zkusí znovu.
                Registration::query()
                    ->whereKey($registration->getKey())
                    ->update(['reminder_sent_at' => null]);

                report($e);
                $this->error("Připomínku pro registraci #{$registration->id} se nepodařilo zařadit: {$e->getMessage()}");
                $failed++;
            }
        }

        $this->info("Odesláno {$count} připomínek." . ($skipped > 0 ? " Přeskočeno (claim jiným procesem): {$skipped}." : ''));

        return $failed > 0 ? Command::FAILURE : Command::SUCCESS;
    }
}

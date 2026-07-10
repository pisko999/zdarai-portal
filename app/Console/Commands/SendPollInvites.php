<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Mail\AfterPartyPollInvite;
use App\Models\Event;
use App\Models\Registration;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendPollInvites extends Command
{
    protected $signature = 'poll:send-invites
        {event : ID akce}
        {--test= : Poslat jen testovací e-mail na zadanou adresu}';

    protected $description = 'Rozešle pozvánku k anketě „posezení po akci" aktivním registracím akce. Idempotentní (poll_invite_sent_at).';

    public function handle(): int
    {
        $event = Event::find((int) $this->argument('event'));

        if (! $event) {
            $this->error('Událost nenalezena.');

            return self::FAILURE;
        }

        $this->info("Událost: {$event->title} ({$event->date->format('j. n. Y H:i')})");

        $activeQuery = Registration::where('event_id', $event->id)
            ->whereNotIn('payment_status', ['cancelled', 'waitlist']);

        if ($testAddress = $this->option('test')) {
            $registration = $activeQuery->orderBy('id')->first();

            if (! $registration) {
                $this->error('Akce nemá žádnou aktivní registraci — není z čeho vzít testovací data.');

                return self::FAILURE;
            }

            Mail::to((string) $testAddress)->queue(new AfterPartyPollInvite($registration));

            $this->info("Testovací e-mail zařazen do fronty na: {$testAddress}");
            $this->line("Použita data registrace #{$registration->id}: {$registration->name} ({$registration->email})");
            $this->line('Anketa: ' . route('poll', $registration->token));
            $this->comment('poll_invite_sent_at NEBYL nastaven — ostrý běh registraci pošle znovu.');

            return self::SUCCESS;
        }

        $registrations = $activeQuery
            ->where('email_opt_out', false)
            ->whereNull('poll_invite_sent_at')
            ->get();

        if ($registrations->isEmpty()) {
            $this->warn('Žádní noví příjemci — nic k odeslání.');

            return self::SUCCESS;
        }

        $sent = 0;
        foreach ($registrations as $registration) {
            Mail::to($registration->email)->queue(new AfterPartyPollInvite($registration));
            $registration->update(['poll_invite_sent_at' => now()]);
            $this->line("  → {$registration->email} ({$registration->name})");
            $sent++;
        }

        $this->info("Zařazeno do fronty: {$sent} e-mailů.");

        return self::SUCCESS;
    }
}

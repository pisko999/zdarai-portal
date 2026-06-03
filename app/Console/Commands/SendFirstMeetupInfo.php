<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Mail\FirstMeetupInfo;
use App\Models\Event;
use App\Models\Registration;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendFirstMeetupInfo extends Command
{
    protected $signature = 'mail:first-meetup-send {--event=} {--include-test} {--force}';

    protected $description = 'Rozešle informační e-mail (mapa + anketa) VŠEM aktivním registracím akce. Respektuje email_opt_out.';

    public function handle(): int
    {
        $event = $this->option('event')
            ? Event::find((int) $this->option('event'))
            : (Event::where('date', '>=', now()->startOfDay())->orderBy('date')->first()
                ?? Event::orderByDesc('date')->first());

        if (! $event) {
            $this->error('Nenalezena žádná událost v databázi.');
            return self::FAILURE;
        }

        $query = Registration::where('event_id', $event->id)
            ->where(function ($q): void {
                $q->whereNull('email_opt_out')->orWhere('email_opt_out', false);
            })
            ->where('payment_status', '!=', 'cancelled');

        if (! $this->option('include-test')) {
            $query->where('name', '!=', 'Test (preview)');
        }

        $registrations = $query->get();

        $this->info("Událost: {$event->title} ({$event->date->format('j. n. Y H:i')})");
        $this->info("Příjemců: {$registrations->count()}");

        if ($registrations->isEmpty()) {
            $this->warn('Žádní příjemci — nic k odeslání.');
            return self::SUCCESS;
        }

        if (! $this->option('force') && ! $this->confirm('Opravdu rozeslat informační e-mail těmto příjemcům?')) {
            $this->warn('Zrušeno.');
            return self::SUCCESS;
        }

        $sent = 0;
        foreach ($registrations as $registration) {
            Mail::to($registration->email)->send(new FirstMeetupInfo($registration));
            $this->line("  → {$registration->email} ({$registration->name})");
            $sent++;
        }

        $this->info("Zařazeno do fronty k odeslání: {$sent} e-mailů.");

        return self::SUCCESS;
    }
}

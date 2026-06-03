<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Mail\FirstMeetupInfo;
use App\Models\Event;
use App\Models\Registration;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendFirstMeetupInfoTest extends Command
{
    protected $signature = 'mail:first-meetup-test {email=spinarp@gmail.com} {--event=}';

    protected $description = 'Odešle TESTOVACÍ informační e-mail (mapa + anketa) na zadanou adresu. Nerozesílá účastníkům.';

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

        $email = (string) $this->argument('email');

        // Testovací registrace (oddělená od reálných účastníků) — pro funkční token, anketu a tracking.
        $registration = Registration::firstOrCreate(
            ['event_id' => $event->id, 'email' => $email],
            ['name' => 'Test (preview)', 'payment_status' => 'free']
        );

        Mail::to($email)->send(new FirstMeetupInfo($registration));

        $this->info("Testovací e-mail odeslán na: {$email}");
        $this->line("Událost: {$event->title} ({$event->date->format('j. n. Y H:i')})");
        $this->line('Anketa:  ' . url('/anketa/' . $registration->token));
        $this->line('Pixel:   ' . url('/e/o/' . $registration->token . '.png'));

        return self::SUCCESS;
    }
}

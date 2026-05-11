<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Mail\EventReminder;
use App\Models\Registration;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendEventReminders extends Command
{
    protected $signature = 'reminders:send';
    protected $description = 'Odešle emailové připomínky registrovaným účastníkům 3 dny před akcí';

    public function handle(): int
    {
        $threeDaysFromNow = now()->addDays(3);

        $registrations = Registration::query()
            ->whereNull('reminder_sent_at')
            ->where('email_opt_out', false)
            ->whereNotIn('payment_status', ['cancelled'])
            ->whereHas('event', fn($q) => $q
                ->where('status', 'published')
                ->whereBetween('date', [
                    $threeDaysFromNow->copy()->startOfDay(),
                    $threeDaysFromNow->copy()->endOfDay(),
                ])
            )
            ->with('event')
            ->get();

        $count = 0;
        foreach ($registrations as $registration) {
            Mail::to($registration->email)->queue(new EventReminder($registration));
            $registration->update(['reminder_sent_at' => now()]);
            $count++;
        }

        $this->info("Odesláno {$count} připomínek.");
        return Command::SUCCESS;
    }
}

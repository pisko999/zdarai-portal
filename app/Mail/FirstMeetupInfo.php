<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\Registration;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class FirstMeetupInfo extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Registration $registration,
    ) {}

    public function envelope(): Envelope
    {
        $isToday = $this->registration->event->date->isToday();
        $prefix = $isToday ? 'Dnes večer: ' : 'Už zítra: ';

        return new Envelope(
            replyTo: [new Address('klara@zdarai.cz', 'Klára')],
            subject: $prefix . $this->registration->event->title . ' — info, mapa a anketa 🍺',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.first-meetup-info',
            with: [
                'isToday'  => $this->registration->event->date->isToday(),
                'pollUrl'  => url('/anketa/' . $this->registration->token),
                'pixelUrl' => url('/e/o/' . $this->registration->token . '.png'),
                'eventUrl' => url('/udalosti/' . $this->registration->event->slug),
                'optOutUrl' => url('/opt-out/' . $this->registration->token),
            ],
        );
    }
}

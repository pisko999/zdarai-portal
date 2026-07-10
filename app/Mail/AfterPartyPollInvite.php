<?php

declare(strict_types=1);

namespace App\Mail;

use App\Livewire\DinnerPoll;
use App\Models\Registration;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AfterPartyPollInvite extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Registration $registration,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            replyTo: [new Address('klara@zdarai.cz', 'Klára')],
            subject: 'Posezení po ŽďárAI #2 — dáš si večeři, nebo jen pivo? 🍽️',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.after-party-poll',
            with: [
                'pollUrl' => route('poll', $this->registration->token),
                'optOutUrl' => url('/opt-out/' . $this->registration->token),
                'foodOptions' => DinnerPoll::FOOD_OPTIONS,
            ],
        );
    }
}

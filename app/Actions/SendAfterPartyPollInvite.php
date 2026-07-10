<?php

declare(strict_types=1);

namespace App\Actions;

use App\Mail\AfterPartyPollInvite;
use App\Models\Registration;
use Illuminate\Support\Facades\Mail;

/**
 * Pošle pozvánku k anketě „posezení po akci" nové (nebo právě potvrzené) registraci,
 * ALE jen pokud pro danou akci už běží anketní kampaň — tj. alespoň jedna registrace
 * akce má nastaveno poll_invite_sent_at (kampaň startuje ručně přes `poll:send-invites`).
 *
 * Idempotentní: poll_invite_sent_at se nastavuje ihned při zařazení do fronty,
 * takže opakovaný běh commandu ani opakované volání nepošle duplicitu.
 */
class SendAfterPartyPollInvite
{
    /**
     * @return bool true pokud byla pozvánka zařazena do fronty
     */
    public function execute(Registration $registration): bool
    {
        // Už dostal/a — nikdy neposílat podruhé.
        if ($registration->poll_invite_sent_at !== null) {
            return false;
        }

        // Jen aktivní registrace (ne waitlist, ne cancelled) bez opt-outu.
        if (in_array($registration->payment_status, ['cancelled', 'waitlist'], true)) {
            return false;
        }

        if ($registration->email_opt_out) {
            return false;
        }

        // Jen akce v budoucnosti.
        $event = $registration->event;
        if ($event === null || ! $event->date->isFuture()) {
            return false;
        }

        // Kampaň pro akci musí už běžet (odstartovaná commandem poll:send-invites).
        $campaignRunning = Registration::where('event_id', $event->id)
            ->whereKeyNot($registration->id)
            ->whereNotNull('poll_invite_sent_at')
            ->exists();

        if (! $campaignRunning) {
            return false;
        }

        // Nejdřív označit, pak queue — command je díky poll_invite_sent_at idempotentní.
        $registration->update(['poll_invite_sent_at' => now()]);
        Mail::to($registration->email)->queue(new AfterPartyPollInvite($registration));

        return true;
    }
}

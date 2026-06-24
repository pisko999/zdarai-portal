<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\NewsletterSubscriber;

class SubscribeToNewsletter
{
    /**
     * Vytvoří nebo aktualizuje odběratele newsletteru.
     * Pokud už existuje (i odhlášený), provede re-subscribe (consent_at=now, unsubscribed_at=null).
     */
    public function execute(string $email, ?string $name, ?int $userId, string $source): NewsletterSubscriber
    {
        $subscriber = NewsletterSubscriber::firstOrNew(['email' => $email]);

        $subscriber->name ??= $name;
        if ($userId !== null) {
            $subscriber->user_id = $userId;
        }
        $subscriber->consent_at = now();
        $subscriber->unsubscribed_at = null;

        // Zachovej původní source u re-subscribe, jen u nového nastav.
        if (! $subscriber->exists) {
            $subscriber->source = $source;
        }

        $subscriber->save();

        return $subscriber;
    }
}

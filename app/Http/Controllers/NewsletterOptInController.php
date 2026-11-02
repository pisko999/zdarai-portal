<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\NewsletterOptInRequest;
use App\Models\NewsletterSubscriber;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Dobrovolné přihlášení k newsletteru z podepsaného odkazu v e-mailu
 * (např. pozvánka pro účastníky AI Monday Žďár #1, kteří souhlas nedali).
 *
 * GET jen zobrazí potvrzení a nic nezapisuje — linkové skenery (Safe Links
 * apod.) GET proklikávají samy; souhlas = POST formulář odeslaný člověkem.
 */
class NewsletterOptInController extends Controller
{
    public function show(NewsletterOptInRequest $request): View
    {
        return view('newsletter.optin', [
            'email' => $request->normalizedEmail(),
            // Stejná podepsaná URL (cesta + původní query string) → POST projde middlewarem signed.
            'action' => $request->getRequestUri(),
        ]);
    }

    public function store(NewsletterOptInRequest $request): View
    {
        $email = $request->normalizedEmail();
        $source = $request->source();

        try {
            $subscriber = $this->subscribe($email, $source);
        } catch (UniqueConstraintViolationException) {
            // Souběžný POST založil záznam mezi naším SELECT a INSERT → zopakuj nad existujícím.
            $subscriber = $this->subscribe($email, $source);
        }

        return view('newsletter.optin-done', [
            'unsubscribeUrl' => route('newsletter.unsubscribe', $subscriber->token),
        ]);
    }

    /**
     * Neexistuje → nový se souhlasem; odhlášený → znovu přihlásit (source se
     * nepřepisuje, pokud už je); aktivní bez souhlasu → doplnit consent_at;
     * aktivní se souhlasem → beze změny.
     */
    private function subscribe(string $email, string $source): NewsletterSubscriber
    {
        return DB::transaction(function () use ($email, $source): NewsletterSubscriber {
            $subscriber = NewsletterSubscriber::query()
                ->where('email', $email)
                ->lockForUpdate()
                ->first();

            if ($subscriber === null) {
                return NewsletterSubscriber::create([
                    'email' => $email,
                    'consent_at' => now(),
                    'source' => $source,
                ]);
            }

            if ($subscriber->unsubscribed_at !== null) {
                $subscriber->unsubscribed_at = null;
                $subscriber->consent_at = now();
            } elseif ($subscriber->consent_at === null) {
                $subscriber->consent_at = now();
            } else {
                return $subscriber;
            }

            if (blank($subscriber->source)) {
                $subscriber->source = $source;
            }

            $subscriber->save();

            return $subscriber;
        });
    }
}

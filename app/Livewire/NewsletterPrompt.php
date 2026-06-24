<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Actions\SubscribeToNewsletter;
use Illuminate\View\View;
use Livewire\Component;

class NewsletterPrompt extends Component
{
    /**
     * Zda se má modal vůbec vykreslit (řízeno na mount).
     */
    public bool $show = false;

    public function mount(): void
    {
        $this->show = $this->shouldShow();
    }

    private function shouldShow(): bool
    {
        $user = auth()->user();

        if ($user === null) {
            return false;
        }

        // Už jednou viděl → neukazuj.
        if ($user->newsletter_prompted_at !== null) {
            return false;
        }

        // Už je aktivním odběratelem → neukazuj.
        $alreadyActive = $user->newsletterSubscriber()->whereNull('unsubscribed_at')->exists();

        return ! $alreadyActive;
    }

    public function subscribe(): void
    {
        $user = auth()->user();

        if ($user !== null) {
            app(SubscribeToNewsletter::class)->execute(
                email: $user->email,
                name: $user->name,
                userId: $user->id,
                source: 'modal',
            );
        }

        $this->dismissAndMark();
    }

    public function dismiss(): void
    {
        $this->dismissAndMark();
    }

    /**
     * Po jakékoli akci (souhlas i zavření) označíme, aby se modal už neukazoval.
     */
    private function dismissAndMark(): void
    {
        $user = auth()->user();

        if ($user !== null && $user->newsletter_prompted_at === null) {
            $user->forceFill(['newsletter_prompted_at' => now()])->save();
        }

        $this->show = false;
    }

    public function render(): View
    {
        return view('livewire.newsletter-prompt');
    }
}

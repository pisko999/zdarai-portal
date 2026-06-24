<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\ConsentLog;
use Illuminate\Support\Facades\Cookie;
use Illuminate\View\View;
use Livewire\Component;

class CookieConsent extends Component
{
    /**
     * Verze souhlasu. Při změně textů/kategorií zvyš → lišta se zobrazí znovu.
     */
    public const CONSENT_VERSION = '1';

    /**
     * Platnost cookie v minutách (~6 měsíců).
     */
    private const COOKIE_LIFETIME = 60 * 24 * 30 * 6;

    private const COOKIE_NAME = 'cookie_consent';

    public bool $analytics = false;

    /**
     * Zda už uživatel udělil platný souhlas v aktuální verzi (řídí zobrazení lišty).
     */
    public bool $decided = false;

    public function mount(): void
    {
        $raw = request()->cookie(self::COOKIE_NAME);
        $data = $this->decodeCookie($raw);

        if ($data !== null && ($data['version'] ?? null) === self::CONSENT_VERSION) {
            $this->decided = true;
            $this->analytics = (bool) ($data['analytics'] ?? false);
        }
    }

    public function acceptAll(): void
    {
        $this->store(true);
    }

    public function rejectAll(): void
    {
        $this->store(false);
    }

    /**
     * Uložení dle aktuálního stavu přepínačů (z panelu Nastavení).
     */
    public function savePreferences(): void
    {
        $this->store($this->analytics);
    }

    private function store(bool $analytics): void
    {
        $this->analytics = $analytics;
        $this->decided = true;

        $payload = [
            'version'   => self::CONSENT_VERSION,
            'necessary' => true,
            'analytics' => $analytics,
            'ts'        => now()->timestamp,
        ];

        Cookie::queue(Cookie::make(
            name: self::COOKIE_NAME,
            value: (string) json_encode($payload),
            minutes: self::COOKIE_LIFETIME,
            path: '/',
            domain: null,
            secure: app()->environment('production'),
            httpOnly: false,            // čteno i Alpine/JS kvůli GA gatingu
            raw: false,
            sameSite: 'lax',
        ));

        $this->logConsent($analytics);

        // Informuj front-end (Alpine) — umožní injektnout/odebrat GA bez reloadu.
        $this->dispatch('cookie-consent-updated', analytics: $analytics);
    }

    private function logConsent(bool $analytics): void
    {
        ConsentLog::create([
            'user_id'         => auth()->id(),
            'ip_hash'         => $this->hashIp(request()->ip()),
            'categories'      => ['necessary' => true, 'analytics' => $analytics],
            'consent_version' => self::CONSENT_VERSION,
            'user_agent'      => \Illuminate\Support\Str::limit((string) request()->userAgent(), 250, ''),
        ]);
    }

    private function hashIp(?string $ip): ?string
    {
        if ($ip === null || $ip === '') {
            return null;
        }

        return hash('sha256', $ip . config('app.key'));
    }

    /**
     * @return array<string, mixed>|null
     */
    private function decodeCookie(?string $raw): ?array
    {
        if ($raw === null || $raw === '') {
            return null;
        }

        $decoded = json_decode($raw, true);

        return is_array($decoded) ? $decoded : null;
    }

    public function render(): View
    {
        return view('livewire.cookie-consent');
    }
}

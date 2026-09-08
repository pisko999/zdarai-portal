<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Přepnutí jazyka (CZ/EN).
 *
 * Cíl přesměrování se odvozuje VÝHRADNĚ z hlavičky Referer — nikdy z
 * redirect()->back() ani ze session `_previous.url`, protože ta může
 * obsahovat samotné /lang/{locale} (Chrome preload, dvojí volání) a vést
 * k nekonečné 302 smyčce. Cizí referer (LinkedIn, Google) se ignoruje.
 */
final class LocaleController extends Controller
{
    private const ALLOWED = ['cs', 'en'];

    public function __invoke(Request $request, string $locale): RedirectResponse
    {
        if (! in_array($locale, self::ALLOWED, true)) {
            abort(404);
        }

        session(['locale' => $locale]);

        $target = $this->safeTarget($request);

        return $target !== null
            ? redirect()->to($target)
            : redirect()->route('home');
    }

    /**
     * Vrátí referer, pokud je same-host a nejde o /lang/*; jinak null.
     */
    private function safeTarget(Request $request): ?string
    {
        $referer = $request->headers->get('referer');
        if (! is_string($referer) || $referer === '') {
            return null;
        }

        $parts = parse_url($referer);
        if ($parts === false || ! isset($parts['host'])) {
            return null;
        }

        if (strcasecmp($parts['host'], $request->getHost()) !== 0) {
            return null;
        }

        $path = $parts['path'] ?? '/';
        if ($path === '/lang' || str_starts_with($path, '/lang/')) {
            return null;
        }

        return $referer;
    }
}

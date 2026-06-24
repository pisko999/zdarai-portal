<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Event;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;

class OgImageService
{
    /**
     * Šířka jednoho znaku DejaVu Sans Mono Bold ≈ 0.60 × pointsize.
     * (Změřeno přes `convert label:` — monospace, konstantní šířka.)
     */
    private const CHAR_RATIO = 0.60;

    private const WIDTH = 1200;

    private const HEIGHT = 630;

    /**
     * Vrátí absolutní veřejnou URL OG obrázku pro danou akci.
     * Vygeneruje obrázek, pokud ještě neexistuje. Při chybě vrací brand default.
     */
    public function forEvent(Event $event): string
    {
        $relative = $this->relativePath($event);

        if (! Storage::disk('public')->exists($relative)) {
            if (! $this->generate($event, $relative)) {
                return asset('og-default.png');
            }
        }

        return $this->absoluteUrl($relative);
    }

    /**
     * Vynutí (pře)generování OG obrázku pro akci. Vrací absolutní URL nebo fallback.
     */
    public function regenerateForEvent(Event $event): string
    {
        $relative = $this->relativePath($event);

        if (! $this->generate($event, $relative)) {
            return asset('og-default.png');
        }

        return $this->absoluteUrl($relative);
    }

    /**
     * Cesta na public disku: og/events/{id}-{hash}.png
     * Hash z (title+date+updated_at) zajistí invalidaci při změně dat akce.
     */
    private function relativePath(Event $event): string
    {
        $hash = substr(md5(implode('|', [
            (string) $event->getTranslation('title'),
            (string) $event->date?->toIso8601String(),
            (string) $event->updated_at?->toIso8601String(),
        ])), 0, 10);

        return "og/events/{$event->id}-{$hash}.png";
    }

    private function absoluteUrl(string $relative): string
    {
        // Storage::url vrací cestu dle filesystem konfigurace; sjednotíme na absolutní URL.
        $url = Storage::disk('public')->url($relative);

        if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://')) {
            return $url;
        }

        return rtrim((string) config('app.url'), '/') . '/' . ltrim($url, '/');
    }

    /**
     * Vyrenderuje SVG → PNG přes CLI `convert`. Vrací true při úspěchu.
     */
    private function generate(Event $event, string $relative): bool
    {
        try {
            // Před regenerací uklidíme staré varianty (jiný hash) pro tuto akci.
            $this->purgeOldVariants($event, $relative);

            $svg = $this->buildSvg($event);

            $tmpDir = storage_path('app/og-tmp');
            if (! is_dir($tmpDir)) {
                @mkdir($tmpDir, 0775, true);
            }

            $tmpSvg = $tmpDir . '/' . uniqid('og_', true) . '.svg';
            $tmpPng = $tmpDir . '/' . uniqid('og_', true) . '.png';

            file_put_contents($tmpSvg, $svg);

            // -background none zachová gradient z SVG; density kvůli ostrosti.
            $result = Process::timeout(30)->run([
                'convert',
                '-background', 'none',
                '-density', '96',
                $tmpSvg,
                '-resize', self::WIDTH . 'x' . self::HEIGHT,
                $tmpPng,
            ]);

            if (! $result->successful() || ! is_file($tmpPng)) {
                Log::warning('OgImageService: convert selhal', [
                    'event_id' => $event->id,
                    'exit' => $result->exitCode(),
                    'stderr' => $result->errorOutput(),
                ]);
                @unlink($tmpSvg);

                return false;
            }

            Storage::disk('public')->put($relative, (string) file_get_contents($tmpPng));

            @unlink($tmpSvg);
            @unlink($tmpPng);

            return true;
        } catch (\Throwable $e) {
            Log::warning('OgImageService: výjimka při generování OG', [
                'event_id' => $event->id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    private function purgeOldVariants(Event $event, string $keep): void
    {
        $dir = 'og/events';
        foreach (Storage::disk('public')->files($dir) as $file) {
            if (str_starts_with(basename($file), $event->id . '-') && $file !== $keep) {
                Storage::disk('public')->delete($file);
            }
        }
    }

    /**
     * Sestaví SVG šablonu s daty akce. Veškerý dynamický text je XML-escapovaný.
     * Vícebarevný/víceřádkový text = SAMOSTATNÉ <text> elementy (žádné tspan continuation —
     * ImageMagick je renderuje špatně).
     */
    private function buildSvg(Event $event): string
    {
        $w = self::WIDTH;
        $h = self::HEIGHT;

        $title = trim((string) $event->getTranslation('title'));
        $titleLines = $this->wrapTitle($title, 30, 2);

        $dateLine = $this->formatDate($event);
        $place = 'IT NeŠkola · Žďár nad Sázavou';

        $titleFontSize = $this->titleFontSize($titleLines);
        $lineHeight = (int) round($titleFontSize * 1.18);

        $mono = 'DejaVu Sans Mono';

        // Meta blok (datum + místo) ukotven napevno ke spodku — stabilní rozložení
        // bez ohledu na počet řádků názvu.
        $metaY = 528;     // baseline data
        $placeY = 568;    // baseline místa

        // Baseline prvního řádku názvu: jednořádkový níž (vizuální střed),
        // dvouřádkový výš, aby měl odstup od meta bloku.
        $titleTop = count($titleLines) >= 2 ? 410 : 460;
        $titleSvg = '';
        $ty = $titleTop;
        foreach ($titleLines as $line) {
            $titleSvg .= sprintf(
                '<text x="80" y="%d" font-family="%s" font-weight="bold" font-size="%d" fill="#ffffff">%s</text>',
                $ty,
                $mono,
                $titleFontSize,
                $this->esc($line)
            );
            $ty += $lineHeight;
        }

        // Logo mark: zelený zaoblený čtvereček s "AI" (x=80, y=210, 110×110).
        // Wordmark: "Žďár" zeleně + "AI" bíle, mono bold pointsize 90.
        // Char width @90 ≈ 54px. "Žďár" = 4 znaky ≈ 216px → "AI" začíná ~ x.
        $markX = 80;
        $markY = 210;
        $markSize = 110;
        $markCenterX = $markX + (int) round($markSize / 2);
        $markTextY = $markY + 73; // baseline pro vertikální vystředění "AI" @54
        $wordX = $markX + $markSize + 36; // mezera za logem
        $wordY = 300;
        $wordFont = 90;
        $charW = (int) round($wordFont * self::CHAR_RATIO);
        $zdarWidth = mb_strlen('Žďár') * $charW;
        $aiX = $wordX + $zdarWidth;

        $dateEsc = $this->esc($dateLine);
        $placeEsc = $this->esc($place);

        return <<<SVG
<?xml version="1.0" encoding="UTF-8"?>
<svg xmlns="http://www.w3.org/2000/svg" width="{$w}" height="{$h}" viewBox="0 0 {$w} {$h}">
  <defs>
    <linearGradient id="bg" x1="0" y1="0" x2="1" y2="1">
      <stop offset="0%" stop-color="#0a0a0a"/>
      <stop offset="100%" stop-color="#0f1713"/>
    </linearGradient>
  </defs>

  <rect x="0" y="0" width="{$w}" height="{$h}" fill="url(#bg)"/>
  <rect x="24" y="24" width="1152" height="582" rx="20"
        fill="none" stroke="#22c55e" stroke-width="2" stroke-opacity="0.7"/>

  <!-- terminal prompt -->
  <text x="80" y="125" font-family="{$mono}" font-size="34" fill="#22c55e">~/zdarai $ ./udalosti --next</text>

  <!-- logo mark -->
  <rect x="{$markX}" y="{$markY}" width="{$markSize}" height="{$markSize}" rx="22" fill="#22c55e"/>
  <text x="{$markCenterX}" y="{$markTextY}" text-anchor="middle"
        font-family="{$mono}" font-weight="bold" font-size="54" fill="#0a0a0a">AI</text>

  <!-- wordmark: Žďár (green) + AI (white) jako samostatné <text> -->
  <text x="{$wordX}" y="{$wordY}" font-family="{$mono}" font-weight="bold" font-size="{$wordFont}" fill="#22c55e">Žďár</text>
  <text x="{$aiX}" y="{$wordY}" font-family="{$mono}" font-weight="bold" font-size="{$wordFont}" fill="#ffffff">AI</text>

  <!-- event title -->
  {$titleSvg}

  <!-- date + place -->
  <text x="80" y="{$metaY}" font-family="{$mono}" font-size="30" fill="#9ca3af">{$dateEsc}</text>
  <text x="80" y="{$placeY}" font-family="{$mono}" font-size="26" fill="#6b7280">{$placeEsc}</text>
</svg>
SVG;
    }

    /**
     * Velikost fontu názvu dle délky nejdelšího řádku, aby se vešel do šířky ~1040px.
     */
    private function titleFontSize(array $lines): int
    {
        $maxLen = 0;
        foreach ($lines as $line) {
            $maxLen = max($maxLen, mb_strlen($line));
        }

        $maxLen = max($maxLen, 1);
        $available = self::WIDTH - 160; // 80px padding na každé straně
        $size = (int) floor($available / ($maxLen * self::CHAR_RATIO));

        return max(34, min(60, $size));
    }

    /**
     * Zalomí název na max $maxLines řádků po slovech, ~$maxChars znaků na řádek.
     * Poslední řádek případně zkrátí elipsou.
     */
    private function wrapTitle(string $title, int $maxChars, int $maxLines): array
    {
        if ($title === '') {
            return [''];
        }

        $words = preg_split('/\s+/', $title) ?: [$title];
        $lines = [];
        $current = '';

        foreach ($words as $word) {
            $candidate = $current === '' ? $word : $current . ' ' . $word;
            if (mb_strlen($candidate) <= $maxChars || $current === '') {
                $current = $candidate;
            } else {
                $lines[] = $current;
                $current = $word;
                if (count($lines) === $maxLines) {
                    break;
                }
            }
        }

        if (count($lines) < $maxLines && $current !== '') {
            $lines[] = $current;
        }

        // Pokud zůstal nezpracovaný obsah (název delší než maxLines), zkrať poslední řádek.
        $joined = implode(' ', $lines);
        if (mb_strlen($joined) < mb_strlen($title) && count($lines) === $maxLines) {
            $last = $lines[$maxLines - 1];
            if (mb_strlen($last) > $maxChars - 1) {
                $last = mb_substr($last, 0, $maxChars - 1);
            }
            $lines[$maxLines - 1] = rtrim($last) . '…';
        }

        return $lines;
    }

    private function formatDate(Event $event): string
    {
        $date = $event->date;
        if ($date === null) {
            return '';
        }

        // Česky: "4. 6. 2026 · 18:00"
        return $date->format('j. n. Y') . ' · ' . $date->format('H:i');
    }

    private function esc(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_XML1, 'UTF-8');
    }
}

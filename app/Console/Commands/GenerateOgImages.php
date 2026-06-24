<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Event;
use App\Services\OgImageService;
use Illuminate\Console\Command;

class GenerateOgImages extends Command
{
    protected $signature = 'og:generate {--force : Přegeneruje i existující obrázky}';

    protected $description = 'Vygeneruje OG (náhledové) obrázky pro publikované akce';

    public function handle(OgImageService $service): int
    {
        $events = Event::where('status', 'published')->get();

        if ($events->isEmpty()) {
            $this->info('Žádné publikované akce.');

            return self::SUCCESS;
        }

        $force = (bool) $this->option('force');
        $ok = 0;
        $fail = 0;

        foreach ($events as $event) {
            $url = $force
                ? $service->regenerateForEvent($event)
                : $service->forEvent($event);

            if (str_contains($url, '/storage/og/events/')) {
                $ok++;
                $this->line("  ✓ #{$event->id} {$event->getTranslation('title')} → {$url}");
            } else {
                $fail++;
                $this->warn("  ✗ #{$event->id} fallback (convert selhal): {$url}");
            }
        }

        $this->info("Hotovo: {$ok} OK, {$fail} fallback.");

        return $fail > 0 ? self::FAILURE : self::SUCCESS;
    }
}

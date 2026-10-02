<?php

declare(strict_types=1);

use App\Models\Event;

it('homepage layout neobsahuje hreflang ani odkazy na /lang/ jako alternate', function () {
    $response = $this->get('/');

    $response->assertOk();
    $response->assertDontSee('hreflang', false);
    $response->assertDontSee('rel="alternate"', false);
});

it('sitemap neobsahuje hreflang a je validní XML', function () {
    Event::factory()->create([
        'status' => 'published',
        'date'   => now()->addDays(7),
    ]);

    $response = $this->get('/sitemap.xml');

    $response->assertOk();
    $content = $response->getContent();

    expect($content)
        ->not->toContain('hreflang')
        ->not->toContain('xhtml:link')
        ->not->toContain('/lang/');

    $previous = libxml_use_internal_errors(true);
    $xml = simplexml_load_string($content);
    $errors = libxml_get_errors();
    libxml_clear_errors();
    libxml_use_internal_errors($previous);

    expect($xml)->not->toBeFalse()
        ->and($errors)->toBeEmpty()
        ->and($xml->getName())->toBe('urlset')
        ->and(count($xml->url))->toBeGreaterThanOrEqual(2);
});

it('robots.txt zakazuje /lang/ a zachovává Sitemap', function () {
    $robots = file_get_contents(public_path('robots.txt'));

    expect($robots)
        ->toContain('User-agent: *')
        ->toContain('Disallow: /lang/')
        ->toContain('Disallow: /admin')
        ->toContain('Sitemap: https://www.zdarai.cz/sitemap.xml');
});

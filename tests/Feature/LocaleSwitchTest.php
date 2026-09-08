<?php

declare(strict_types=1);

it('bez refereru přesměruje na homepage a uloží locale', function () {
    $response = $this->get('/lang/cs');

    $response->assertRedirect('/');
    $response->assertSessionHas('locale', 'cs');
});

it('cizí referer (LinkedIn) přesměruje na homepage', function () {
    $this->withHeaders(['Referer' => 'https://www.linkedin.com/feed/'])
        ->get('/lang/en')
        ->assertRedirect('/')
        ->assertSessionHas('locale', 'en');
});

it('cizí referer (Google) přesměruje na homepage', function () {
    $this->withHeaders(['Referer' => 'https://www.google.com/'])
        ->get('/lang/cs')
        ->assertRedirect('/');
});

it('same-host referer přesměruje zpět na původní stránku', function () {
    $referer = rtrim((string) config('app.url'), '/') . '/o-nas';

    $this->withHeaders(['Referer' => $referer])
        ->get('/lang/en')
        ->assertRedirect($referer)
        ->assertSessionHas('locale', 'en');
});

it('same-host referer na /lang/* přesměruje na homepage (ochrana proti smyčce)', function () {
    $referer = rtrim((string) config('app.url'), '/') . '/lang/en';

    $this->withHeaders(['Referer' => $referer])
        ->get('/lang/cs')
        ->assertRedirect('/');
});

it('dvojí volání bez refereru ve stejné session nikdy nesmyčkuje', function () {
    $this->get('/lang/cs')->assertRedirect('/');
    $this->get('/lang/cs')->assertRedirect('/');
});

it('neznámé locale vrátí 404', function () {
    $this->get('/lang/de')->assertNotFound();
    $this->assertNull(session('locale'));
});

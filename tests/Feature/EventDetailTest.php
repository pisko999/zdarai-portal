<?php

declare(strict_types=1);

use App\Models\Event;

it('zobrazí detail události', function () {
    $event = Event::factory()->create([
        'status' => 'published',
        'date'   => now()->addDays(7),
    ]);

    $response = $this->get('/udalosti/' . $event->slug);
    $response->assertStatus(200);
    $response->assertSee($event->title);
});

it('vrátí 404 pro neexistující událost', function () {
    $response = $this->get('/udalosti/neexistujici-slug-12345');
    $response->assertStatus(404);
});

it('vrátí 404 pro draft událost', function () {
    $event = Event::factory()->create(['status' => 'draft']);
    $response = $this->get('/udalosti/' . $event->slug);
    $response->assertStatus(404);
});

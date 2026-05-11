<?php

declare(strict_types=1);

use App\Models\Event;

it('zobrazí homepage', function () {
    $response = $this->get('/');
    $response->assertStatus(200);
    $response->assertSee('ŽďárAI');
});

it('zobrazí nadcházející události', function () {
    $event = Event::factory()->create([
        'status' => 'published',
        'date'   => now()->addDays(7),
        'title'  => 'Test Event XYZ',
    ]);

    $response = $this->get('/');
    $response->assertStatus(200);
    $response->assertSee('Test Event XYZ');
});

it('skryje draft události', function () {
    Event::factory()->create([
        'status' => 'draft',
        'date'   => now()->addDays(7),
        'title'  => 'Draft Event Hidden',
    ]);

    $response = $this->get('/');
    $response->assertStatus(200);
    $response->assertDontSee('Draft Event Hidden');
});

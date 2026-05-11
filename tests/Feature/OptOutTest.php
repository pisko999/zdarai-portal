<?php

declare(strict_types=1);

use App\Models\Event;
use App\Models\Registration;

it('opt-out odhlásí uživatele z emailů', function () {
    $event = Event::factory()->create();
    $registration = Registration::factory()->create([
        'event_id'      => $event->id,
        'email_opt_out' => false,
    ]);

    $response = $this->get('/opt-out/' . $registration->token);
    $response->assertStatus(200);

    expect($registration->fresh()->email_opt_out)->toBeTrue();
});

it('opt-out s neplatným tokenem vrátí 404', function () {
    $response = $this->get('/opt-out/neplatny-token-xyz');
    $response->assertStatus(404);
});

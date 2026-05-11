<?php

declare(strict_types=1);

use App\Models\User;

it('neautorizovaný uživatel nemůže přistoupit k adminu', function () {
    $response = $this->get('/admin');
    $response->assertRedirect('/login');
});

it('normální uživatel nemůže přistoupit k adminu', function () {
    $user = User::factory()->create(['is_admin' => false]);

    $response = $this->actingAs($user)->get('/admin');
    $response->assertStatus(403);
});

it('admin uživatel má přístup k adminu', function () {
    $user = User::factory()->create(['is_admin' => true]);

    $response = $this->actingAs($user)->get('/admin');
    $response->assertStatus(200);
});

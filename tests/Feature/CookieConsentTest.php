<?php

declare(strict_types=1);

use App\Livewire\CookieConsent;
use App\Models\ConsentLog;
use App\Models\User;
use Livewire\Livewire;

it('logs consent and sets analytics on accept all', function () {
    Livewire::test(CookieConsent::class)
        ->assertSet('decided', false)
        ->call('acceptAll')
        ->assertSet('decided', true)
        ->assertSet('analytics', true)
        ->assertDispatched('cookie-consent-updated');

    $log = ConsentLog::firstOrFail();
    expect($log->categories)->toBe(['necessary' => true, 'analytics' => true])
        ->and($log->consent_version)->toBe(CookieConsent::CONSENT_VERSION)
        ->and($log->user_id)->toBeNull();
});

it('rejects analytics but keeps necessary', function () {
    Livewire::test(CookieConsent::class)
        ->call('rejectAll')
        ->assertSet('analytics', false);

    expect(ConsentLog::firstOrFail()->categories)->toBe(['necessary' => true, 'analytics' => false]);
});

it('associates consent log with authenticated user', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(CookieConsent::class)
        ->call('acceptAll');

    expect(ConsentLog::firstOrFail()->user_id)->toBe($user->id);
});

it('hashes ip and never stores raw value', function () {
    Livewire::test(CookieConsent::class)->call('acceptAll');

    $log = ConsentLog::firstOrFail();
    expect($log->ip_hash)->not->toBeNull()
        ->and(strlen((string) $log->ip_hash))->toBe(64);
});

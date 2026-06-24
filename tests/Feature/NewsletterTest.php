<?php

declare(strict_types=1);

use App\Actions\SubscribeToNewsletter;
use App\Livewire\NewsletterPrompt;
use App\Models\NewsletterSubscriber;
use App\Models\User;
use Livewire\Livewire;

it('creates a subscriber via the action', function () {
    app(SubscribeToNewsletter::class)->execute('a@b.cz', 'Jan', null, 'registration');

    $sub = NewsletterSubscriber::firstOrFail();
    expect($sub->email)->toBe('a@b.cz')
        ->and($sub->source)->toBe('registration')
        ->and($sub->token)->not->toBeEmpty()
        ->and($sub->consent_at)->not->toBeNull();
});

it('re-subscribes an unsubscribed subscriber', function () {
    $sub = NewsletterSubscriber::factory()->unsubscribed()->create(['email' => 'x@y.cz']);

    app(SubscribeToNewsletter::class)->execute('x@y.cz', null, null, 'modal');

    $sub->refresh();
    expect($sub->unsubscribed_at)->toBeNull()
        ->and(NewsletterSubscriber::count())->toBe(1);
});

it('active scope excludes unsubscribed', function () {
    NewsletterSubscriber::factory()->create();
    NewsletterSubscriber::factory()->unsubscribed()->create();

    expect(NewsletterSubscriber::active()->count())->toBe(1);
});

it('unsubscribe route marks subscriber as unsubscribed', function () {
    $sub = NewsletterSubscriber::factory()->create();

    $this->get(route('newsletter.unsubscribe', $sub->token))
        ->assertOk()
        ->assertViewIs('newsletter.unsubscribed');

    expect($sub->fresh()->unsubscribed_at)->not->toBeNull();
});

it('shows prompt only to eligible users and marks prompted on dismiss', function () {
    $user = User::factory()->create(['newsletter_prompted_at' => null]);

    Livewire::actingAs($user)
        ->test(NewsletterPrompt::class)
        ->assertSet('show', true)
        ->call('dismiss')
        ->assertSet('show', false);

    expect($user->fresh()->newsletter_prompted_at)->not->toBeNull();
});

it('does not show prompt to already prompted users', function () {
    $user = User::factory()->create(['newsletter_prompted_at' => now()]);

    Livewire::actingAs($user)
        ->test(NewsletterPrompt::class)
        ->assertSet('show', false);
});

it('subscribes via modal and marks prompted', function () {
    $user = User::factory()->create(['newsletter_prompted_at' => null]);

    Livewire::actingAs($user)
        ->test(NewsletterPrompt::class)
        ->call('subscribe');

    expect(NewsletterSubscriber::where('email', $user->email)->where('source', 'modal')->exists())->toBeTrue()
        ->and($user->fresh()->newsletter_prompted_at)->not->toBeNull();
});

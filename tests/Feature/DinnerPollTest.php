<?php

declare(strict_types=1);

use App\Livewire\DinnerPoll;
use App\Models\DinnerPollResponse;
use App\Models\Registration;
use Livewire\Livewire;

it('accepts a dinner submission with a food from the list', function () {
    $registration = Registration::factory()->create(['payment_status' => 'free']);
    $food = DinnerPoll::FOOD_OPTIONS[1];

    Livewire::test(DinnerPoll::class, ['registration' => $registration])
        ->set('attendance', 'dinner')
        ->set('foodChoice', $food)
        ->set('note', 'bez cibule')
        ->call('submit')
        ->assertHasNoErrors()
        ->assertSet('submitted', true);

    $poll = DinnerPollResponse::where('registration_id', $registration->id)->first();
    expect($poll)->not->toBeNull()
        ->and($poll->attendance)->toBe('dinner')
        ->and($poll->food_choice)->toBe($food)
        ->and($poll->note)->toBe('bez cibule')
        ->and($poll->responded_at)->not->toBeNull();
});

it('accepts the "other" food choice and stores its canonical value', function () {
    $registration = Registration::factory()->create(['payment_status' => 'free']);

    Livewire::test(DinnerPoll::class, ['registration' => $registration])
        ->set('attendance', 'dinner')
        ->set('foodChoice', DinnerPoll::OTHER_CHOICE)
        ->set('note', 'smažák s hranolkama')
        ->call('submit')
        ->assertHasNoErrors();

    expect($registration->fresh()->dinnerPoll->food_choice)->toBe('Jiné (viz poznámka)');
});

it('rejects a food choice outside the list', function () {
    $registration = Registration::factory()->create(['payment_status' => 'free']);

    Livewire::test(DinnerPoll::class, ['registration' => $registration])
        ->set('attendance', 'dinner')
        ->set('foodChoice', 'Svíčková na smetaně — 999 Kč')
        ->call('submit')
        ->assertHasErrors(['foodChoice' => 'in']);

    expect(DinnerPollResponse::where('registration_id', $registration->id)->exists())->toBeFalse();
});

it('requires a food choice when attendance is dinner', function () {
    $registration = Registration::factory()->create(['payment_status' => 'free']);

    Livewire::test(DinnerPoll::class, ['registration' => $registration])
        ->set('attendance', 'dinner')
        ->call('submit')
        ->assertHasErrors(['foodChoice' => 'required_if']);
});

it('does not require a food choice for beer-only attendance', function () {
    $registration = Registration::factory()->create(['payment_status' => 'free']);

    Livewire::test(DinnerPoll::class, ['registration' => $registration])
        ->set('attendance', 'beer')
        ->call('submit')
        ->assertHasNoErrors()
        ->assertSet('submitted', true);

    $poll = $registration->fresh()->dinnerPoll;
    expect($poll)->not->toBeNull()
        ->and($poll->food_choice)->toBeNull();
});

it('mounts without crashing for a legacy free-text food choice from event #1', function () {
    $registration = Registration::factory()->create(['payment_status' => 'free']);
    DinnerPollResponse::create([
        'registration_id' => $registration->id,
        'attendance' => 'dinner',
        'food_choice' => 'Svíčková na smetaně, knedlík',
        'note' => 'stará odpověď z Ingotu',
        'opened_at' => now()->subMonth(),
        'responded_at' => now()->subMonth(),
    ]);

    Livewire::test(DinnerPoll::class, ['registration' => $registration])
        ->assertSet('submitted', true)
        ->assertSet('foodChoice', 'Svíčková na smetaně, knedlík')
        ->assertSee('stará odpověď z Ingotu')
        ->assertSee('Svíčková na smetaně, knedlík');
});

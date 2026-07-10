<?php

declare(strict_types=1);

use App\Livewire\Admin\RegistrationManager;
use App\Livewire\RegistrationForm;
use App\Mail\AfterPartyPollInvite;
use App\Mail\RegistrationApproved;
use App\Mail\RegistrationConfirmed;
use App\Models\Event;
use App\Models\Registration;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;

/**
 * Vytvoří budoucí publikovanou akci s BĚŽÍCÍ anketní kampaní
 * (jedna registrace už má poll_invite_sent_at — jako po běhu poll:send-invites).
 */
function eventWithRunningPollCampaign(int $capacity = 40): Event
{
    $event = Event::factory()->published()->create([
        'date' => now()->addDays(10),
        'capacity' => $capacity,
        'price' => null,
    ]);

    Registration::factory()->create([
        'event_id' => $event->id,
        'payment_status' => 'free',
        'poll_invite_sent_at' => now()->subDay(),
    ]);

    return $event;
}

it('nová aktivní registrace na akci s běžící kampaní dostane pozvánku k anketě', function () {
    Mail::fake();

    $event = eventWithRunningPollCampaign();

    Livewire::test(RegistrationForm::class, ['event' => $event])
        ->set('name', 'Nový Účastník')
        ->set('email', 'novy@example.com')
        ->call('register')
        ->assertSet('success', true);

    $registration = Registration::where('email', 'novy@example.com')->first();

    expect($registration)->not->toBeNull()
        ->and($registration->poll_invite_sent_at)->not->toBeNull();

    Mail::assertQueued(RegistrationConfirmed::class, fn ($mail) => $mail->hasTo('novy@example.com'));
    Mail::assertQueued(AfterPartyPollInvite::class, fn ($mail) => $mail->hasTo('novy@example.com'));
});

it('registrace na akci BEZ běžící kampaně pozvánku nedostane', function () {
    Mail::fake();

    $event = Event::factory()->published()->create([
        'date' => now()->addDays(10),
        'capacity' => 40,
        'price' => null,
    ]);

    Livewire::test(RegistrationForm::class, ['event' => $event])
        ->set('name', 'Bez Kampaně')
        ->set('email', 'bez-kampane@example.com')
        ->call('register')
        ->assertSet('success', true);

    $registration = Registration::where('email', 'bez-kampane@example.com')->first();

    expect($registration->poll_invite_sent_at)->toBeNull();

    Mail::assertQueued(RegistrationConfirmed::class);
    Mail::assertNotQueued(AfterPartyPollInvite::class);
});

it('waitlist registrace pozvánku nedostane', function () {
    Mail::fake();

    // Kapacita 1 — jedinou aktivní registrací (s poll_invite_sent_at) je akce plná.
    $event = eventWithRunningPollCampaign(capacity: 1);

    Livewire::test(RegistrationForm::class, ['event' => $event])
        ->set('name', 'Na Čekačce')
        ->set('email', 'cekacka@example.com')
        ->call('register')
        ->assertSet('waitlisted', true);

    $registration = Registration::where('email', 'cekacka@example.com')->first();

    expect($registration->payment_status)->toBe('waitlist')
        ->and($registration->poll_invite_sent_at)->toBeNull();

    Mail::assertNotQueued(AfterPartyPollInvite::class, fn ($mail) => $mail->hasTo('cekacka@example.com'));
});

it('registrace s email opt-out pozvánku nedostane', function () {
    Mail::fake();

    $event = eventWithRunningPollCampaign();

    Livewire::test(RegistrationForm::class, ['event' => $event])
        ->set('name', 'Opt Out')
        ->set('email', 'opt-out@example.com')
        ->set('email_opt_in', false)
        ->call('register')
        ->assertSet('success', true);

    $registration = Registration::where('email', 'opt-out@example.com')->first();

    expect($registration->email_opt_out)->toBeTrue()
        ->and($registration->poll_invite_sent_at)->toBeNull();

    Mail::assertNotQueued(AfterPartyPollInvite::class, fn ($mail) => $mail->hasTo('opt-out@example.com'));
});

it('registrace na akci v minulosti pozvánku nedostane', function () {
    Mail::fake();

    $event = Event::factory()->published()->create([
        'date' => now()->subDay(),
        'capacity' => 40,
        'price' => null,
    ]);

    Registration::factory()->create([
        'event_id' => $event->id,
        'payment_status' => 'free',
        'poll_invite_sent_at' => now()->subDays(2),
    ]);

    Livewire::test(RegistrationForm::class, ['event' => $event])
        ->set('name', 'Pozdě Příchozí')
        ->set('email', 'pozde@example.com')
        ->call('register')
        ->assertSet('success', true);

    expect(Registration::where('email', 'pozde@example.com')->first()->poll_invite_sent_at)->toBeNull();

    Mail::assertNotQueued(AfterPartyPollInvite::class);
});

it('command po registračním hooku neposílá pozvánku duplicitně', function () {
    Mail::fake();

    $event = eventWithRunningPollCampaign();

    Livewire::test(RegistrationForm::class, ['event' => $event])
        ->set('name', 'Jednou Stačí')
        ->set('email', 'jednou@example.com')
        ->call('register')
        ->assertSet('success', true);

    $this->artisan('poll:send-invites', ['event' => $event->id])
        ->expectsOutputToContain('Žádní noví příjemci')
        ->assertSuccessful();

    // Hook poslal právě jednu pozvánku, command žádnou další.
    Mail::assertQueuedCount(2); // RegistrationConfirmed + AfterPartyPollInvite
    Mail::assertQueued(AfterPartyPollInvite::class, 1);
});

it('povýšení z waitlistu adminem pošle pozvánku při běžící kampani', function () {
    Mail::fake();

    $event = eventWithRunningPollCampaign();

    $waitlisted = Registration::factory()->create([
        'event_id' => $event->id,
        'payment_status' => 'waitlist',
        'email' => 'povyseny@example.com',
    ]);

    Livewire::test(RegistrationManager::class)
        ->call('confirmRegistration', $waitlisted->id);

    $waitlisted->refresh();

    expect($waitlisted->payment_status)->toBe('confirmed')
        ->and($waitlisted->poll_invite_sent_at)->not->toBeNull();

    Mail::assertQueued(RegistrationApproved::class, fn ($mail) => $mail->hasTo('povyseny@example.com'));
    Mail::assertQueued(AfterPartyPollInvite::class, fn ($mail) => $mail->hasTo('povyseny@example.com'));
});

it('povýšení z waitlistu BEZ běžící kampaně pozvánku nepošle', function () {
    Mail::fake();

    $event = Event::factory()->published()->create([
        'date' => now()->addDays(10),
        'capacity' => 40,
        'price' => null,
    ]);

    $waitlisted = Registration::factory()->create([
        'event_id' => $event->id,
        'payment_status' => 'waitlist',
    ]);

    Livewire::test(RegistrationManager::class)
        ->call('confirmRegistration', $waitlisted->id);

    expect($waitlisted->refresh()->poll_invite_sent_at)->toBeNull();

    Mail::assertQueued(RegistrationApproved::class);
    Mail::assertNotQueued(AfterPartyPollInvite::class);
});

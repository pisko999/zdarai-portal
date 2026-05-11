<?php

declare(strict_types=1);

use App\Livewire\RegistrationForm;
use App\Mail\RegistrationConfirmed;
use App\Models\Event;
use App\Models\Registration;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;

it('lze se zaregistrovat na událost', function () {
    Mail::fake();

    $event = Event::factory()->create([
        'status'   => 'published',
        'date'     => now()->addDays(14),
        'capacity' => 50,
    ]);

    Livewire::test(RegistrationForm::class, ['event' => $event])
        ->set('name', 'Jan Novák')
        ->set('email', 'jan@example.com')
        ->call('register')
        ->assertSet('success', true);

    expect(Registration::where('email', 'jan@example.com')->exists())->toBeTrue();
    Mail::assertQueued(RegistrationConfirmed::class);
});

it('nelze se zaregistrovat na plnou událost', function () {
    $event = Event::factory()->create([
        'status'   => 'published',
        'date'     => now()->addDays(14),
        'capacity' => 1,
    ]);

    Registration::factory()->create([
        'event_id'       => $event->id,
        'payment_status' => 'free',
    ]);

    Livewire::test(RegistrationForm::class, ['event' => $event])
        ->set('name', 'Jan Novák')
        ->set('email', 'jan@example.com')
        ->call('register')
        ->assertSet('success', false);
});

it('nelze se registrovat dvakrát stejným emailem', function () {
    Mail::fake();

    $event = Event::factory()->create([
        'status'   => 'published',
        'date'     => now()->addDays(14),
        'capacity' => 50,
    ]);

    Registration::factory()->create([
        'event_id'       => $event->id,
        'email'          => 'duplicate@example.com',
        'payment_status' => 'free',
    ]);

    Livewire::test(RegistrationForm::class, ['event' => $event])
        ->set('name', 'Jiný Člověk')
        ->set('email', 'duplicate@example.com')
        ->call('register')
        ->assertSet('success', false);
});

it('validuje povinná pole', function () {
    $event = Event::factory()->create([
        'status' => 'published',
        'date'   => now()->addDays(14),
    ]);

    Livewire::test(RegistrationForm::class, ['event' => $event])
        ->set('name', '')
        ->set('email', 'invalid-email')
        ->call('register')
        ->assertHasErrors(['name', 'email']);
});

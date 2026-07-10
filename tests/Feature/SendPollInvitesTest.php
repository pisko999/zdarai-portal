<?php

declare(strict_types=1);

use App\Mail\AfterPartyPollInvite;
use App\Models\Event;
use App\Models\Registration;
use Illuminate\Support\Facades\Mail;

it('sends a test email to the given address without marking poll_invite_sent_at', function () {
    Mail::fake();

    $event = Event::factory()->create();
    $registration = Registration::factory()->create([
        'event_id' => $event->id,
        'payment_status' => 'free',
    ]);

    $this->artisan('poll:send-invites', ['event' => $event->id, '--test' => 'test@example.com'])
        ->expectsOutputToContain($registration->name)
        ->assertSuccessful();

    Mail::assertQueued(AfterPartyPollInvite::class, function (AfterPartyPollInvite $mail) use ($registration) {
        return $mail->hasTo('test@example.com')
            && ! $mail->hasTo($registration->email)
            && $mail->registration->is($registration);
    });
    Mail::assertQueuedCount(1);

    expect($registration->fresh()->poll_invite_sent_at)->toBeNull();
});

it('sends live invites only to eligible registrations and marks them sent', function () {
    Mail::fake();

    $event = Event::factory()->create();

    $eligible = Registration::factory()->create(['event_id' => $event->id, 'payment_status' => 'free']);
    $cancelled = Registration::factory()->create(['event_id' => $event->id, 'payment_status' => 'cancelled']);
    $waitlist = Registration::factory()->create(['event_id' => $event->id, 'payment_status' => 'waitlist']);
    $optedOut = Registration::factory()->create(['event_id' => $event->id, 'payment_status' => 'free', 'email_opt_out' => true]);
    $alreadySent = Registration::factory()->create(['event_id' => $event->id, 'payment_status' => 'paid', 'poll_invite_sent_at' => now()->subDay()]);
    $otherEvent = Registration::factory()->create(['payment_status' => 'free']);

    $this->artisan('poll:send-invites', ['event' => $event->id])->assertSuccessful();

    Mail::assertQueued(AfterPartyPollInvite::class, fn ($mail) => $mail->hasTo($eligible->email));
    Mail::assertQueuedCount(1);

    foreach ([$cancelled, $waitlist, $optedOut, $otherEvent] as $skipped) {
        Mail::assertNotQueued(AfterPartyPollInvite::class, fn ($mail) => $mail->hasTo($skipped->email));
        expect($skipped->fresh()->poll_invite_sent_at)->toBeNull();
    }

    expect($eligible->fresh()->poll_invite_sent_at)->not->toBeNull()
        ->and($alreadySent->fresh()->poll_invite_sent_at->isSameDay(now()->subDay()))->toBeTrue();
});

it('is idempotent — a second run queues nothing', function () {
    Mail::fake();

    $event = Event::factory()->create();
    Registration::factory()->count(2)->create(['event_id' => $event->id, 'payment_status' => 'free']);

    $this->artisan('poll:send-invites', ['event' => $event->id])->assertSuccessful();
    Mail::assertQueuedCount(2);

    $this->artisan('poll:send-invites', ['event' => $event->id])
        ->expectsOutputToContain('Žádní noví příjemci')
        ->assertSuccessful();
    Mail::assertQueuedCount(2);
});

it('fails for a missing event', function () {
    Mail::fake();

    $this->artisan('poll:send-invites', ['event' => 999])->assertFailed();
    Mail::assertNothingQueued();
});

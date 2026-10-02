<?php

declare(strict_types=1);

use App\Mail\EventReminder;
use App\Models\Event;
use App\Models\Registration;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

function reminderEvent(): Event
{
    return Event::factory()->published()->create([
        'date' => now()->addDays(3)->setTime(18, 0),
    ]);
}

it('queues reminders only for eligible registrations and marks them', function () {
    Mail::fake();

    $event = reminderEvent();
    $eligible = Registration::factory()->create(['event_id' => $event->id, 'payment_status' => 'free']);
    $cancelled = Registration::factory()->create(['event_id' => $event->id, 'payment_status' => 'cancelled']);
    $waitlist = Registration::factory()->create(['event_id' => $event->id, 'payment_status' => 'waitlist']);
    $optedOut = Registration::factory()->create(['event_id' => $event->id, 'payment_status' => 'free', 'email_opt_out' => true]);
    $otherDay = Registration::factory()->create([
        'event_id' => Event::factory()->published()->create(['date' => now()->addDays(5)])->id,
        'payment_status' => 'free',
    ]);
    $draft = Registration::factory()->create([
        'event_id' => Event::factory()->create(['status' => 'draft', 'date' => now()->addDays(3)])->id,
        'payment_status' => 'free',
    ]);

    $this->artisan('reminders:send')->assertSuccessful();

    Mail::assertQueued(EventReminder::class, fn ($mail) => $mail->hasTo($eligible->email));
    Mail::assertQueuedCount(1);
    expect($eligible->fresh()->reminder_sent_at)->not->toBeNull();

    foreach ([$cancelled, $waitlist, $optedOut, $otherDay, $draft] as $skipped) {
        expect($skipped->fresh()->reminder_sent_at)->toBeNull();
    }
});

it('is idempotent — a second run dispatches nothing', function () {
    Mail::fake();

    $event = reminderEvent();
    Registration::factory()->count(2)->create(['event_id' => $event->id, 'payment_status' => 'free']);

    $this->artisan('reminders:send')->assertSuccessful();
    Mail::assertQueuedCount(2);

    $this->artisan('reminders:send')->assertSuccessful();
    Mail::assertQueuedCount(2);
});

it('skips a registration claimed by another process between SELECT and claim', function () {
    Mail::fake();

    $event = reminderEvent();
    $stolen = Registration::factory()->create(['event_id' => $event->id, 'payment_status' => 'free']);
    $mine = Registration::factory()->create(['event_id' => $event->id, 'payment_status' => 'free']);

    // Hned po kandidátním SELECTu "jiný proces" claimne $stolen.
    $done = false;
    DB::listen(function (QueryExecuted $query) use (&$done, $stolen): void {
        if ($done || ! str_starts_with(strtolower($query->sql), 'select')
            || ! str_contains($query->sql, 'registrations')
            || ! str_contains($query->sql, 'reminder_sent_at')) {
            return;
        }
        $done = true;
        DB::table('registrations')->where('id', $stolen->id)->update(['reminder_sent_at' => now()->subMinute()]);
    });

    $this->artisan('reminders:send')->assertSuccessful();

    expect($done)->toBeTrue();
    Mail::assertNotQueued(EventReminder::class, fn ($mail) => $mail->hasTo($stolen->email));
    Mail::assertQueued(EventReminder::class, fn ($mail) => $mail->hasTo($mine->email));
    Mail::assertQueuedCount(1);
});

it('dry-run neither claims nor queues anything', function () {
    Mail::fake();

    $event = reminderEvent();
    $registration = Registration::factory()->create(['event_id' => $event->id, 'payment_status' => 'free']);

    $this->artisan('reminders:send', ['--dry-run' => true])->assertSuccessful();

    Mail::assertNothingQueued();
    expect($registration->fresh()->reminder_sent_at)->toBeNull();
});

it('releases the claim when queueing fails', function () {
    $event = reminderEvent();
    $registration = Registration::factory()->create(['event_id' => $event->id, 'payment_status' => 'free']);

    Mail::shouldReceive('to')->andThrow(new RuntimeException('queue down'));

    $this->artisan('reminders:send')->assertFailed();

    expect($registration->fresh()->reminder_sent_at)->toBeNull();
});

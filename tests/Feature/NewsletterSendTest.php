<?php

declare(strict_types=1);

use App\Mail\EventNewsletter;
use App\Models\Event;
use App\Models\NewsletterEventSend;
use App\Models\NewsletterSubscriber;
use Illuminate\Support\Facades\Mail;

beforeEach(function () {
    Mail::fake();
});

function newsletterEvent(array $attrs = []): Event
{
    return Event::factory()->published()->create(array_merge([
        'slug' => 'zdarai-4',
        'date' => now()->addWeeks(2),
    ], $attrs));
}

function seedNewsletterSubscribers(): array
{
    $active = NewsletterSubscriber::factory()->create(['email' => 'aktivni@example.com', 'token' => 'tok-aktivni']);
    $other = NewsletterSubscriber::factory()->create(['email' => 'Druhy@Example.com', 'token' => 'tok-druhy']);
    $excluded = NewsletterSubscriber::factory()->create(['email' => 'organizator@example.com']);
    $unsub = NewsletterSubscriber::factory()->unsubscribed()->create(['email' => 'odhlaseny@example.com']);

    return compact('active', 'other', 'excluded', 'unsub');
}

it('neznámá akce selže', function () {
    $this->artisan('newsletter:send', ['event' => 'neexistuje', '--force' => true])
        ->expectsOutputToContain('nenalezena')
        ->assertFailed();

    Mail::assertNothingQueued();
});

it('najde akci podle ID i slugu', function () {
    $event = newsletterEvent();

    $this->artisan('newsletter:send', ['event' => (string) $event->id, '--dry-run' => true])
        ->expectsOutputToContain("Akce: #{$event->id}")
        ->assertSuccessful();

    $this->artisan('newsletter:send', ['event' => 'zdarai-4', '--dry-run' => true])
        ->expectsOutputToContain("Akce: #{$event->id}")
        ->assertSuccessful();
});

it('dry-run nic nepošle ani neclaimuje', function () {
    newsletterEvent();
    seedNewsletterSubscribers();

    $this->artisan('newsletter:send', ['event' => 'zdarai-4', '--dry-run' => true])
        ->expectsOutputToContain('aktivni@example.com')
        ->expectsOutputToContain('Příjemců by bylo: 3')
        ->assertSuccessful();

    Mail::assertNothingQueued();
    expect(NewsletterEventSend::count())->toBe(0);
});

it('--test pošle jen jeden e-mail na testovací adresu a nic neclaimuje', function () {
    $event = newsletterEvent();
    seedNewsletterSubscribers();

    $this->artisan('newsletter:send', ['event' => 'zdarai-4', '--test' => 'petr@example.com'])
        ->assertSuccessful();

    Mail::assertQueuedCount(1);
    Mail::assertQueued(EventNewsletter::class, fn (EventNewsletter $m) => $m->hasTo('petr@example.com')
        && $m->unsubscribeUrl === url('/')
        && $m->event->is($event));
    expect(NewsletterEventSend::count())->toBe(0);
});

it('ostrý běh pošle jen aktivním se souhlasem, respektuje exclude a normalizuje e-mail', function () {
    $event = newsletterEvent();
    seedNewsletterSubscribers();

    $this->artisan('newsletter:send', [
        'event' => 'zdarai-4',
        '--exclude' => ' ORGANIZATOR@example.com ',
        '--force' => true,
    ])
        ->expectsOutputToContain('Zařazeno do fronty: 2')
        ->assertSuccessful();

    Mail::assertQueuedCount(2);
    Mail::assertQueued(EventNewsletter::class, fn ($m) => $m->hasTo('aktivni@example.com'));
    Mail::assertQueued(EventNewsletter::class, fn ($m) => $m->hasTo('druhy@example.com'));
    foreach (['organizator@example.com', 'odhlaseny@example.com'] as $email) {
        Mail::assertNotQueued(EventNewsletter::class, fn ($m) => $m->hasTo($email));
    }

    expect(NewsletterEventSend::where('event_id', $event->id)->count())->toBe(2);
});

it('unsubscribe URL obsahuje token odběratele', function () {
    newsletterEvent();
    seedNewsletterSubscribers();

    $this->artisan('newsletter:send', ['event' => 'zdarai-4', '--force' => true])->assertSuccessful();

    Mail::assertQueued(EventNewsletter::class, fn (EventNewsletter $m) => $m->hasTo('aktivni@example.com')
        && $m->unsubscribeUrl === route('newsletter.unsubscribe', 'tok-aktivni'));
    Mail::assertQueued(EventNewsletter::class, fn (EventNewsletter $m) => $m->hasTo('druhy@example.com')
        && $m->unsubscribeUrl === route('newsletter.unsubscribe', 'tok-druhy'));
});

it('druhý běh nepošle nic (claim v newsletter_event_sends)', function () {
    newsletterEvent();
    seedNewsletterSubscribers();

    $this->artisan('newsletter:send', ['event' => 'zdarai-4', '--force' => true])->assertSuccessful();
    Mail::assertQueuedCount(3);

    $this->artisan('newsletter:send', ['event' => 'zdarai-4', '--force' => true])
        ->expectsOutputToContain('už odesláno, přeskočeno')
        ->expectsOutputToContain('Zařazeno do fronty: 0. Přeskočeno (už odesláno): 3.')
        ->assertSuccessful();

    Mail::assertQueuedCount(3);
});

it('existující claim (souběžný běh) zabrání odeslání, nový odběratel dostane newsletter', function () {
    $event = newsletterEvent();
    $subs = seedNewsletterSubscribers();
    NewsletterEventSend::factory()->create([
        'event_id' => $event->id,
        'newsletter_subscriber_id' => $subs['active']->id,
        'email' => 'aktivni@example.com',
    ]);

    $this->artisan('newsletter:send', ['event' => 'zdarai-4', '--force' => true])
        ->expectsOutputToContain('aktivni@example.com — už odesláno, přeskočeno')
        ->expectsOutputToContain('Zařazeno do fronty: 2. Přeskočeno (už odesláno): 1.')
        ->assertSuccessful();

    Mail::assertNotQueued(EventNewsletter::class, fn ($m) => $m->hasTo('aktivni@example.com'));
});

it('claim je per akce — newsletter k jiné akci se pošle znovu', function () {
    newsletterEvent();
    newsletterEvent(['slug' => 'zdarai-5', 'date' => now()->addWeeks(6)]);
    NewsletterSubscriber::factory()->create(['email' => 'aktivni@example.com']);

    $this->artisan('newsletter:send', ['event' => 'zdarai-4', '--force' => true])->assertSuccessful();
    $this->artisan('newsletter:send', ['event' => 'zdarai-5', '--force' => true])->assertSuccessful();

    Mail::assertQueuedCount(2);
});

it('unique index v DB brání duplicitnímu claimu', function () {
    $send = NewsletterEventSend::factory()->create();

    expect(fn () => NewsletterEventSend::create([
        'event_id' => $send->event_id,
        'newsletter_subscriber_id' => $send->newsletter_subscriber_id,
        'email' => $send->email,
        'sent_at' => now(),
    ]))->toThrow(\Illuminate\Database\UniqueConstraintViolationException::class);
});

it('odběratel bez souhlasu nedostane nic', function () {
    newsletterEvent();
    NewsletterSubscriber::factory()->create(['email' => 'aktivni@example.com']);
    // consent_at je ve schématu NOT NULL; pokud by ho DB přesto povolila, filtr ho musí vyřadit.
    $noConsent = NewsletterSubscriber::factory()->make(['email' => 'bez-souhlasu@example.com', 'consent_at' => null]);
    try {
        $noConsent->save();
    } catch (\Illuminate\Database\QueryException) {
        // Schéma consent_at bez souhlasu nepovolí — filtr pak nemá co vyřadit.
    }

    $this->artisan('newsletter:send', ['event' => 'zdarai-4', '--force' => true])->assertSuccessful();

    Mail::assertQueuedCount(1);
    Mail::assertNotQueued(EventNewsletter::class, fn ($m) => $m->hasTo('bez-souhlasu@example.com'));
});

it('bez potvrzení nepošle nic', function () {
    newsletterEvent(['title' => 'ŽďárAI #4']);
    seedNewsletterSubscribers();

    $this->artisan('newsletter:send', ['event' => 'zdarai-4'])
        ->expectsConfirmation('Opravdu rozeslat newsletter k akci „ŽďárAI #4“ 3 příjemcům?', 'no')
        ->assertFailed();

    Mail::assertNothingQueued();
    expect(NewsletterEventSend::count())->toBe(0);
});

it('neinteraktivní běh bez --force nepošle nic', function () {
    newsletterEvent();
    seedNewsletterSubscribers();

    $this->artisan('newsletter:send', ['event' => 'zdarai-4', '--no-interaction' => true])
        ->expectsOutputToContain('Neinteraktivní běh bez --force')
        ->assertFailed();

    Mail::assertNothingQueued();
    expect(NewsletterEventSend::count())->toBe(0);
});

it('odmítne nepublikovanou akci i s --force', function () {
    newsletterEvent(['status' => 'draft']);
    seedNewsletterSubscribers();

    $this->artisan('newsletter:send', ['event' => 'zdarai-4', '--force' => true])
        ->expectsOutputToContain('není publikovaná')
        ->assertFailed();

    Mail::assertNothingQueued();
});

it('odmítne akci v minulosti i s --force', function () {
    newsletterEvent(['date' => now()->subDay()]);
    seedNewsletterSubscribers();

    $this->artisan('newsletter:send', ['event' => 'zdarai-4', '--force' => true])
        ->expectsOutputToContain('už proběhla')
        ->assertFailed();

    Mail::assertNothingQueued();
});

it('--test u nepublikované akce varuje, ale pošle náhled', function () {
    newsletterEvent(['status' => 'draft']);

    $this->artisan('newsletter:send', ['event' => 'zdarai-4', '--test' => 'petr@example.com'])
        ->expectsOutputToContain('Ostrá rozesílka by byla odmítnuta')
        ->assertSuccessful();

    Mail::assertQueuedCount(1);
});

it('selhání zařazení uvolní claim', function () {
    newsletterEvent();
    NewsletterSubscriber::factory()->create(['email' => 'aktivni@example.com']);

    Mail::shouldReceive('to')->andThrow(new RuntimeException('redis down'));

    $this->artisan('newsletter:send', ['event' => 'zdarai-4', '--force' => true])
        ->assertFailed();

    expect(NewsletterEventSend::count())->toBe(0);
});

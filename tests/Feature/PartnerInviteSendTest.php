<?php

declare(strict_types=1);

use App\Mail\PartnerEventInvite;
use App\Models\NewsletterSubscriber;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;

beforeEach(function () {
    Mail::fake();
    Cache::flush();
});

function seedPartnerSubscribers(): array
{
    $active = NewsletterSubscriber::factory()->create(['email' => 'aktivni@example.com', 'token' => 'tok-aktivni']);
    $dupe = NewsletterSubscriber::factory()->create(['email' => '  AKTIVNI@Example.com ']);
    $other = NewsletterSubscriber::factory()->create(['email' => 'Druhy@Example.com', 'token' => 'tok-druhy']);
    $excluded = NewsletterSubscriber::factory()->create(['email' => 'organizator@example.com']);
    $unsub = NewsletterSubscriber::factory()->unsubscribed()->create(['email' => 'odhlaseny@example.com']);

    return compact('active', 'dupe', 'other', 'excluded', 'unsub');
}

it('neznámý klíč akce selže', function () {
    $this->artisan('partner-invite:send', ['key' => 'neexistuje', '--force' => true])
        ->assertFailed();

    Mail::assertNothingQueued();
});

it('dry-run nic nepošle ani neclaimuje', function () {
    seedPartnerSubscribers();

    $this->artisan('partner-invite:send', ['key' => 'ai-monday-zdar-1', '--dry-run' => true])
        ->expectsOutputToContain('aktivni@example.com')
        ->expectsOutputToContain('Příjemců by bylo: 3')
        ->assertSuccessful();

    Mail::assertNothingQueued();
    expect(Cache::has('partner-invite:ai-monday-zdar-1:' . sha1('aktivni@example.com')))->toBeFalse();
});

it('--test pošle jen jeden e-mail na testovací adresu', function () {
    seedPartnerSubscribers();

    $this->artisan('partner-invite:send', ['key' => 'ai-monday-zdar-1', '--test' => 'petr@example.com'])
        ->assertSuccessful();

    Mail::assertQueuedCount(1);
    Mail::assertQueued(PartnerEventInvite::class, fn (PartnerEventInvite $m) => $m->hasTo('petr@example.com')
        && $m->unsubscribeUrl === url('/'));
    expect(Cache::has('partner-invite:ai-monday-zdar-1:' . sha1('petr@example.com')))->toBeFalse();
});

it('ostrý běh pošle jen aktivním se souhlasem, respektuje exclude a deduplikuje case', function () {
    seedPartnerSubscribers();

    $this->artisan('partner-invite:send', [
        'key' => 'ai-monday-zdar-1',
        '--exclude' => ' ORGANIZATOR@example.com ',
        '--force' => true,
    ])
        ->expectsOutputToContain('Zařazeno do fronty: 2')
        ->assertSuccessful();

    Mail::assertQueuedCount(2);
    Mail::assertQueued(PartnerEventInvite::class, fn ($m) => $m->hasTo('aktivni@example.com'));
    Mail::assertQueued(PartnerEventInvite::class, fn ($m) => $m->hasTo('druhy@example.com'));
    foreach (['organizator@example.com', 'odhlaseny@example.com'] as $email) {
        Mail::assertNotQueued(PartnerEventInvite::class, fn ($m) => $m->hasTo($email));
    }
});

it('unsubscribe URL obsahuje token odběratele', function () {
    seedPartnerSubscribers();

    $this->artisan('partner-invite:send', ['key' => 'ai-monday-zdar-1', '--force' => true])
        ->assertSuccessful();

    Mail::assertQueued(PartnerEventInvite::class, fn (PartnerEventInvite $m) => $m->hasTo('aktivni@example.com')
        && $m->unsubscribeUrl === route('newsletter.unsubscribe', 'tok-aktivni')
        && str_contains($m->unsubscribeUrl, 'tok-aktivni'));
    Mail::assertQueued(PartnerEventInvite::class, fn (PartnerEventInvite $m) => $m->hasTo('druhy@example.com')
        && str_contains($m->unsubscribeUrl, 'tok-druhy'));
});

it('druhý běh nepošle nic (claim)', function () {
    seedPartnerSubscribers();

    $this->artisan('partner-invite:send', ['key' => 'ai-monday-zdar-1', '--force' => true])->assertSuccessful();
    Mail::assertQueuedCount(3);

    $this->artisan('partner-invite:send', ['key' => 'ai-monday-zdar-1', '--force' => true])
        ->expectsOutputToContain('už odesláno, přeskočeno')
        ->expectsOutputToContain('Zařazeno do fronty: 0. Přeskočeno (už odesláno): 3.')
        ->assertSuccessful();

    Mail::assertQueuedCount(3);
});

it('bez potvrzení nepošle nic', function () {
    seedPartnerSubscribers();

    $this->artisan('partner-invite:send', ['key' => 'ai-monday-zdar-1'])
        ->expectsConfirmation('Opravdu rozeslat pozvánku 3 příjemcům (segment subscribers)?', 'no')
        ->assertFailed();

    Mail::assertNothingQueued();
});

it('neinteraktivní běh bez --force nepošle nic', function () {
    seedPartnerSubscribers();

    $this->artisan('partner-invite:send', ['key' => 'ai-monday-zdar-1', '--no-interaction' => true])
        ->assertFailed();

    Mail::assertNothingQueued();
});

it('selhání zařazení uvolní claim', function () {
    NewsletterSubscriber::factory()->create(['email' => 'aktivni@example.com']);

    Mail::shouldReceive('to')->andThrow(new RuntimeException('redis down'));

    $this->artisan('partner-invite:send', ['key' => 'ai-monday-zdar-1', '--force' => true])
        ->assertFailed();

    expect(Cache::has('partner-invite:ai-monday-zdar-1:' . sha1('aktivni@example.com')))->toBeFalse();
});

// ── Segment registrants (TF #3331) ─────────────────────────────────────────

function seedPartnerRegistrants(): array
{
    $event = \App\Models\Event::factory()->create();
    $reg = fn (array $attrs) => \App\Models\Registration::factory()->create(
        array_merge(['event_id' => $event->id, 'payment_status' => 'free', 'email_opt_out' => false], $attrs)
    );

    // Účastník bez newsletteru — dvě registrace, různý case → 1 příjemce, jméno+token z nejnovější.
    $reg(['email' => 'Ucastnik@Example.com', 'name' => 'Starší Jméno', 'token' => 'tok-old']);
    $reg(['email' => ' ucastnik@example.com ', 'name' => 'Novější Jméno', 'token' => 'tok-new']);

    // Odběratel (aktivní) i odhlášený odběratel → vyřazeni.
    NewsletterSubscriber::factory()->create(['email' => 'odberatel@example.com']);
    $reg(['email' => 'ODBERATEL@example.com']);
    NewsletterSubscriber::factory()->unsubscribed()->create(['email' => 'byvaly@example.com']);
    $reg(['email' => 'byvaly@example.com']);

    // Opt-out na jiné (i zrušené) registraci → vyřazen.
    $reg(['email' => 'optout@example.com']);
    $reg(['email' => 'OptOut@example.com', 'payment_status' => 'cancelled', 'email_opt_out' => true]);

    // Jen cancelled / waitlist → vyřazeni.
    $reg(['email' => 'zruseny@example.com', 'payment_status' => 'cancelled']);
    $reg(['email' => 'cekatel@example.com', 'payment_status' => 'waitlist']);

    // Druhý platný účastník + jeden vyloučený přes --exclude.
    $reg(['email' => 'druhy-ucastnik@example.com', 'payment_status' => 'paid', 'token' => 'tok-druhy-u']);
    $reg(['email' => 'organizator@example.com']);

    return ['event' => $event];
}

it('neplatný segment selže a nic nepošle', function () {
    seedPartnerSubscribers();

    $this->artisan('partner-invite:send', ['key' => 'ai-monday-zdar-1', '--segment' => 'vsichni', '--force' => true])
        ->expectsOutputToContain('Neplatný segment')
        ->assertFailed();

    Mail::assertNothingQueued();
});

it('segment registrants: výběr vyřadí odběratele, opt-out, cancelled/waitlist a deduplikuje case', function () {
    seedPartnerRegistrants();

    $this->artisan('partner-invite:send', [
        'key' => 'ai-monday-zdar-1',
        '--segment' => 'registrants',
        '--exclude' => 'organizator@example.com',
        '--force' => true,
    ])
        ->expectsOutputToContain('Segment: registrants')
        ->expectsOutputToContain('Zařazeno do fronty: 2')
        ->assertSuccessful();

    Mail::assertQueuedCount(2);
    Mail::assertQueued(PartnerEventInvite::class, fn ($m) => $m->hasTo('ucastnik@example.com'));
    Mail::assertQueued(PartnerEventInvite::class, fn ($m) => $m->hasTo('druhy-ucastnik@example.com'));
    foreach ([
        'odberatel@example.com', 'byvaly@example.com', 'optout@example.com',
        'zruseny@example.com', 'cekatel@example.com', 'organizator@example.com',
    ] as $email) {
        Mail::assertNotQueued(PartnerEventInvite::class, fn ($m) => $m->hasTo($email));
    }
});

it('segment registrants: dry-run vypíše segment a jméno z nejnovější registrace', function () {
    seedPartnerRegistrants();

    $this->artisan('partner-invite:send', ['key' => 'ai-monday-zdar-1', '--segment' => 'registrants', '--dry-run' => true])
        ->expectsOutputToContain('ucastnik@example.com (Novější Jméno)')
        ->expectsOutputToContain('Příjemců by bylo: 3 (segment registrants)')
        ->assertSuccessful();

    Mail::assertNothingQueued();
});

it('segment registrants: unsubscribe je opt-out URL s tokenem nejnovější registrace a audience registrant', function () {
    seedPartnerRegistrants();

    $this->artisan('partner-invite:send', ['key' => 'ai-monday-zdar-1', '--segment' => 'registrants', '--force' => true])
        ->assertSuccessful();

    Mail::assertQueued(PartnerEventInvite::class, fn (PartnerEventInvite $m) => $m->hasTo('ucastnik@example.com')
        && $m->unsubscribeUrl === route('opt-out', 'tok-new')
        && $m->audience === 'registrant');
    Mail::assertQueued(PartnerEventInvite::class, fn (PartnerEventInvite $m) => $m->hasTo('druhy-ucastnik@example.com')
        && $m->unsubscribeUrl === route('opt-out', 'tok-druhy-u'));
});

it('segment subscribers posílá s audience subscriber', function () {
    seedPartnerSubscribers();

    $this->artisan('partner-invite:send', ['key' => 'ai-monday-zdar-1', '--force' => true])->assertSuccessful();

    Mail::assertQueued(PartnerEventInvite::class, fn (PartnerEventInvite $m) => $m->audience === 'subscriber');
    Mail::assertNotQueued(PartnerEventInvite::class, fn (PartnerEventInvite $m) => $m->audience !== 'subscriber');
});

it('claim je sdílený: po běhu subscribers registrants nepošle stejnému e-mailu', function () {
    // Odběratel s claimem z běhu subscribers; jeho e-mail mezitím odběr nemá (simulace: claim existuje).
    Cache::add('partner-invite:ai-monday-zdar-1:' . sha1('ucastnik@example.com'), now()->toIso8601String(), now()->addDays(90));
    seedPartnerRegistrants();

    $this->artisan('partner-invite:send', ['key' => 'ai-monday-zdar-1', '--segment' => 'registrants', '--force' => true])
        ->expectsOutputToContain('ucastnik@example.com — už odesláno, přeskočeno')
        ->assertSuccessful();

    Mail::assertNotQueued(PartnerEventInvite::class, fn ($m) => $m->hasTo('ucastnik@example.com'));
    Mail::assertQueued(PartnerEventInvite::class, fn ($m) => $m->hasTo('druhy-ucastnik@example.com'));
});

it('claim z běhu subscribers blokuje registrants (end-to-end)', function () {
    NewsletterSubscriber::factory()->create(['email' => 'spolecny@example.com']);

    $this->artisan('partner-invite:send', ['key' => 'ai-monday-zdar-1', '--force' => true])->assertSuccessful();
    Mail::assertQueuedCount(1);

    // Odběratel se pak smaže z newsletter_subscribers, ale je registrovaný na akci.
    NewsletterSubscriber::query()->delete();
    \App\Models\Registration::factory()->create(['email' => 'Spolecny@Example.com', 'payment_status' => 'free']);

    $this->artisan('partner-invite:send', ['key' => 'ai-monday-zdar-1', '--segment' => 'registrants', '--force' => true])
        ->expectsOutputToContain('Zařazeno do fronty: 0. Přeskočeno (už odesláno): 1.')
        ->assertSuccessful();

    Mail::assertQueuedCount(1);
});

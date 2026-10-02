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
        ->expectsConfirmation('Opravdu rozeslat pozvánku 3 příjemcům?', 'no')
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

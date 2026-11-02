<?php

declare(strict_types=1);

use App\Models\NewsletterSubscriber;
use Illuminate\Support\Facades\URL;

// TF #4151 — dobrovolné přihlášení k newsletteru z podepsaného odkazu.

function optinUrl(string $email = 'anna@example.com', string $source = 'ai-monday-1'): string
{
    return URL::temporarySignedRoute('newsletter.optin', now()->addDays(60), ['email' => $email, 'source' => $source]);
}

it('GET bez podpisu vrací 403', function () {
    $this->get('/newsletter/prihlasit?email=anna@example.com&source=ai-monday-1')->assertForbidden();
});

it('GET s upraveným parametrem vrací 403', function () {
    $this->get(str_replace('anna%40example.com', 'eva%40example.com', optinUrl()))->assertForbidden();
});

it('GET s expirovaným podpisem vrací 403', function () {
    $url = URL::temporarySignedRoute('newsletter.optin', now()->subMinute(), ['email' => 'anna@example.com', 'source' => 'ai-monday-1']);

    $this->get($url)->assertForbidden();
});

it('GET s podpisem zobrazí potvrzení s formulářem a nic nezapíše', function () {
    $this->get(optinUrl())
        ->assertOk()
        ->assertSee('anna@example.com')
        ->assertSee('Ano, chci odebírat')
        ->assertSee('method="POST"', false)
        ->assertSee('name="_token"', false);

    expect(NewsletterSubscriber::count())->toBe(0);
});

it('POST bez podpisu vrací 403 a nic nezapíše', function () {
    $this->post('/newsletter/prihlasit?email=anna@example.com&source=ai-monday-1')->assertForbidden();

    expect(NewsletterSubscriber::count())->toBe(0);
});

it('POST vytvoří odběratele se souhlasem a source z odkazu', function () {
    $this->post(optinUrl('Anna@Example.com'))
        ->assertOk()
        ->assertSee('Hotovo, jsi přihlášen/a');

    $sub = NewsletterSubscriber::sole();

    expect($sub->email)->toBe('anna@example.com')
        ->and($sub->consent_at)->not->toBeNull()
        ->and($sub->unsubscribed_at)->toBeNull()
        ->and($sub->source)->toBe('ai-monday-1');

    $this->post(optinUrl('anna@example.com'))->assertSee(route('newsletter.unsubscribe', $sub->token), false);
});

it('POST znovu přihlásí odhlášeného a zachová původní source', function () {
    $sub = NewsletterSubscriber::factory()->create([
        'email' => 'anna@example.com',
        'source' => 'registration',
        'consent_at' => now()->subYear(),
        'unsubscribed_at' => now()->subMonth(),
    ]);

    $this->post(optinUrl())->assertOk();

    $sub->refresh();

    expect($sub->unsubscribed_at)->toBeNull()
        ->and($sub->consent_at->isToday())->toBeTrue()
        ->and($sub->source)->toBe('registration')
        ->and(NewsletterSubscriber::count())->toBe(1);
});

it('POST je idempotentní — aktivní odběratel se souhlasem se nemění', function () {
    $consent = now()->subMonths(3)->startOfSecond();
    $sub = NewsletterSubscriber::factory()->create([
        'email' => 'anna@example.com', 'source' => 'modal', 'consent_at' => $consent, 'unsubscribed_at' => null,
    ]);

    $this->post(optinUrl())->assertOk();
    $this->post(optinUrl())->assertOk();

    $sub->refresh();
    expect(NewsletterSubscriber::count())->toBe(1)
        ->and($sub->consent_at->equalTo($consent))->toBeTrue()
        ->and($sub->source)->toBe('modal');
});


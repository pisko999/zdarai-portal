<?php

declare(strict_types=1);

use App\Livewire\Admin\NewsletterPreview;
use App\Mail\PartnerEventInvite;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;

function aiMondayInvite(string $unsubscribeUrl = 'https://www.zdarai.cz/newsletter/odhlasit/test-token'): PartnerEventInvite
{
    return new PartnerEventInvite(
        partnerEvent: (array) config('partner_events.ai-monday-zdar-1'),
        unsubscribeUrl: $unsubscribeUrl,
    );
}

it('konfigurace AI Monday Žďár #1 obsahuje ověřená data', function () {
    $e = config('partner_events.ai-monday-zdar-1');

    expect($e)->toBeArray()
        ->and($e['title'])->toBe('AI Monday Žďár n. S. #1')
        ->and($e['url'])->toBe('https://www.aimonday.cz/events/ai-monday-zdar-n-s-1')
        ->and($e['date'])->toBe('2026-10-12')
        ->and($e['capacity'])->toBe(30)
        ->and($e['venue'])->toBe('IT NeŠkola, 1. patro, Dolní 1, 591 01 Žďár nad Sázavou');
});

it('pozvánka na AI Monday se vyrenderuje se vším podstatným', function () {
    Mail::fake();

    $mail = aiMondayInvite();
    $html = $mail->render();

    expect($html)
        ->toContain('AI Monday Žďár n. S. #1')
        ->toContain('Martin Hejl')
        ->toContain('Ondřej Matějka')
        ->toContain('AI tvorba AI chatbota pro Kraj Vysočina')
        ->toContain('href="https://www.aimonday.cz/events/ai-monday-zdar-n-s-1?utm_source=zdarai"')
        ->toContain('https://www.zdarai.cz/newsletter/odhlasit/test-token')
        ->toContain('Odhlásit odběr')
        ->toContain('program od 18:00')
        ->toContain('Klára z ŽďárAI · AI agent')
        ->toContain('IT NeŠkola, 1. patro, Dolní 1, 591 01 Žďár nad Sázavou')
        ->toContain('<body bgcolor="#09090b"')
        ->not->toContain('piva')
        ->not->toContain('jídla');

    $mail->assertHasReplyTo('klara@zdarai.cz');
    expect($mail->envelope()->subject)->toContain('AI Monday Žďár n. S. #1');

    Mail::assertNothingSent();
    Mail::assertNothingQueued();
});

it('bez unsubscribe URL se odkaz na odhlášení nevykreslí', function () {
    expect(aiMondayInvite('')->render())->not->toContain('Odhlásit odběr');
});

it('admin náhled zobrazí pozvánku na AI Monday i bez akce v DB a nic neodešle', function () {
    Mail::fake();
    $admin = User::factory()->create(['is_admin' => true]);

    $html = Livewire::actingAs($admin)
        ->test(NewsletterPreview::class)
        ->set('template', 'partner-event-ai-monday-zdar-1')
        ->assertStatus(200)
        ->assertSee('Pozvánka na spřátelenou akci — AI Monday Žďár #1')
        ->assertDontSee('je potřeba vybrat akci')
        ->assertDontSee('Šablonu se nepodařilo vyrenderovat')
        ->html();

    expect($html)->toContain('🤝 Pozvánka: AI Monday Žďár n. S. #1')
        ->and($html)->toContain('Martin Hejl');

    Mail::assertNothingSent();
    Mail::assertNothingQueued();
});

it('patička pro účastníky (registrant) má vlastní text a odkaz (TF #3331)', function () {
    Mail::fake();

    $html = (new PartnerEventInvite(
        partnerEvent: (array) config('partner_events.ai-monday-zdar-1'),
        unsubscribeUrl: 'https://www.zdarai.cz/opt-out/reg-token',
        audience: 'registrant',
    ))->render();

    expect($html)
        ->toContain('Tuto zprávu jste obdrželi jako účastník některé z akcí ŽďárAI.')
        ->toContain('Nechci dostávat další e-maily')
        ->toContain('https://www.zdarai.cz/opt-out/reg-token')
        ->not->toContain('odběratel novinek')
        ->not->toContain('Odhlásit odběr');
});

it('výchozí patička zůstává pro odběratele (TF #3331)', function () {
    Mail::fake();

    expect(aiMondayInvite()->render())
        ->toContain('Tuto zprávu jste obdrželi jako odběratel novinek ŽďárAI.')
        ->toContain('Odhlásit odběr')
        ->not->toContain('Nechci dostávat další e-maily');
});

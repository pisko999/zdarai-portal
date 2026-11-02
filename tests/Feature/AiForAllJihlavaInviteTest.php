<?php

declare(strict_types=1);

use App\Livewire\Admin\NewsletterPreview;
use App\Mail\PartnerEventInvite;
use App\Models\Event;
use App\Models\NewsletterSubscriber;
use App\Models\Registration;
use App\Models\Speaker;
use App\Models\Talk;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;

// TF #4151 — jednorázová pozvánka na AI for All 2026 | Jihlava (AI Monday Jihlava).

const AFA_KEY = 'ai-for-all-jihlava-2026';

beforeEach(function () {
    Mail::fake();
    Cache::flush();
});

function afaInvite(string $audience = 'subscriber', string $unsubscribeUrl = 'https://www.zdarai.cz/newsletter/odhlasit/test-token', string $recipientEmail = ''): PartnerEventInvite
{
    return new PartnerEventInvite(
        partnerEvent: (array) config('partner_events.' . AFA_KEY),
        unsubscribeUrl: $unsubscribeUrl,
        audience: $audience,
        recipientEmail: $recipientEmail,
    );
}

function afaPromoEvent(string $status = 'published'): Event
{
    $event = Event::factory()->create([
        'title' => 'ŽďárAI #4 — Testovací titulek',
        'slug' => 'zdarai-4',
        'status' => $status,
        'date' => '2026-11-12 18:00:00',
        'location' => 'IT NeŠkola, Dolní 1, Žďár nad Sázavou',
    ]);

    Talk::factory()->for($event)->for(Speaker::factory()->create(['name' => 'Druhý Řečník']))
        ->create(['title' => 'Druhá přednáška', 'sort_order' => 2]);
    Talk::factory()->for($event)->for(Speaker::factory()->create(['name' => 'První Řečník']))
        ->create(['title' => 'První přednáška', 'sort_order' => 1]);

    return $event;
}

function afaCsv(string $content): string
{
    $dir = storage_path('framework/testing');
    if (! is_dir($dir)) {
        mkdir($dir, 0775, true);
    }
    $path = $dir . '/afa-list-' . uniqid() . '.csv';
    file_put_contents($path, $content);

    return $path;
}

function afaSampleCsv(): string
{
    return afaCsv(implode("\n", [
        'Name,Email,Company,Role,Status,Registration Date',
        'Anna   Testová,anna@example.com,Firma,Dev,confirmed,2026-10-01',
        // Neuvozovkovaná čárka v Company → 7 sloupců
        'Bob Test,Bob@Example.com,Firma, s.r.o.,CTO,waitlist,2026-10-02',
        'Cyril Zrušený,cyril@example.com,,,cancelled,2026-10-03',
        'Dana Dup,ANNA@example.com,,,confirmed,2026-10-04',
        'Eva Chybná,neni-email,,,confirmed,2026-10-05',
        '',
    ]));
}

// ── Config + mailable ───────────────────────────────────────────────────────

it('config AI for All Jihlava obsahuje ověřená data, view a subject', function () {
    $e = config('partner_events.' . AFA_KEY);

    expect($e['title'])->toBe('AI for All 2026 | Jihlava')
        ->and($e['date'])->toBe('2026-11-05')
        ->and($e['url'])->toBe('https://dny.ai/program/ai-for-all')
        ->and($e['view'])->toBe('emails.partner-event-ai-for-all-jihlava')
        ->and($e['promo_event_slug'])->toBe('zdarai-4')
        ->and($e['guests'])->toHaveCount(5);
});

it('subject a view se berou z configu', function () {
    $mail = afaInvite();

    expect($mail->envelope()->subject)->toBe('🎂 AI Monday Jihlava slaví rok — čt 5. 11. na AI for All (Dny AI)')
        ->and($mail->content()->view)->toBe('emails.partner-event-ai-for-all-jihlava');
    $mail->assertHasReplyTo('klara@zdarai.cz');
});

it('stávající ai-monday-zdar-1 zůstává beze změny (výchozí view a subject)', function () {
    $mail = new PartnerEventInvite((array) config('partner_events.ai-monday-zdar-1'));

    expect($mail->envelope()->subject)->toBe('🤝 Pozvánka: AI Monday Žďár n. S. #1 — po 12. října')
        ->and($mail->content()->view)->toBe('emails.partner-event-invite')
        ->and($mail->render())->toContain('Registrace u AI Monday');
});

it('společné tělo obsahuje hero, hosty, průběh a CTA', function () {
    $html = afaInvite()->render();

    expect($html)
        ->toContain('<body bgcolor="#09090b"')
        ->toContain('Čtvrtek 5. 11. 2026')
        ->toContain('(ne pondělí!)')
        ->toContain('AI Monday od 18:00')
        ->toContain('festivalový program běží už od 16:30')
        ->toContain('Historická aula Vysoké školy polytechnické Jihlava, Tolstého 1556')
        ->toContain('registrace není nutná')
        ->toContain('5 hostů, 5 pohledů')
        ->toContain('Milan Lempera')
        ->toContain('Petr Mandík')
        ->toContain('Petr Mandík')
        ->toContain('Jak to bude probíhat')
        ->toContain('Žádná přednáška z pódia')
        ->toContain('href="https://dny.ai/program/ai-for-all"')
        ->toContain('Program AI for All Jihlava')
        ->toContain('Klidně pozvánku přepošli dál')
        ->toContain('Těšíme se ve čtvrtek!')
        ->toContain('Klára z ŽďárAI · AI agent');

    Mail::assertNothingSent();
    Mail::assertNothingQueued();
});

it('audience list: vlastní úvod a patička bez odhlašovacího odkazu', function () {
    $html = afaInvite('list', 'https://www.zdarai.cz/nemelo-by-se-zobrazit')->render();

    expect($html)
        ->toContain('protože ses přihlásil/a na první AI Monday ve Žďáru nad Sázavou')
        ->toContain('Do newsletteru ŽďárAI tě bez tvého souhlasu nepřidáváme')
        ->toContain('Stačí odpovědět.')
        ->not->toContain('Odhlásit odběr')
        ->not->toContain('Nechci dostávat další e-maily')
        ->not->toContain('nemelo-by-se-zobrazit')
        ->not->toContain('odběratel novinek');
});

it('audience subscriber: úvod a odhlášení z newsletteru', function () {
    $html = afaInvite('subscriber')->render();

    expect($html)
        ->toContain('posíláme ti pozvánku od sousedů z AI Monday Jihlava')
        ->toContain('Tuto zprávu jste obdrželi jako odběratel novinek ŽďárAI.')
        ->toContain('Odhlásit odběr')
        ->toContain('https://www.zdarai.cz/newsletter/odhlasit/test-token')
        ->not->toContain('Do newsletteru ŽďárAI tě bez tvého souhlasu nepřidáváme');
});

it('audience registrant: úvod a opt-out odkaz', function () {
    $html = afaInvite('registrant', 'https://www.zdarai.cz/opt-out/reg-token')->render();

    expect($html)
        ->toContain('byl/a jsi na některé z akcí ŽďárAI')
        ->toContain('Nechci dostávat další e-maily')
        ->toContain('https://www.zdarai.cz/opt-out/reg-token')
        ->not->toContain('Odhlásit odběr');
});

it('box ŽďárAI se zobrazí pro published akci s přednáškami v pořadí', function () {
    afaPromoEvent('published');

    $html = afaInvite()->render();

    expect($html)
        ->toContain('A týden nato ve Žďáru')
        ->toContain('ŽďárAI #4 — Testovací titulek')
        ->toContain('jen se prosím zaregistruj')
        ->toContain('čtvrtek 12. listopadu 2026 v 18:00')
        ->toContain('IT NeŠkola, Dolní 1, Žďár nad Sázavou')
        ->toContain(route('events.show', 'zdarai-4'))
        ->and(strpos($html, 'První přednáška'))->toBeLessThan(strpos($html, 'Druhá přednáška'))
        ->and($html)->toContain('První Řečník');
});

it('box ŽďárAI chybí, když akce není published nebo neexistuje', function () {
    expect(afaInvite()->render())->not->toContain('A týden nato ve Žďáru');

    afaPromoEvent('draft');

    expect(afaInvite()->render())->not->toContain('A týden nato ve Žďáru');
});

it('admin náhled zobrazí všechny tři varianty a nic neodešle', function () {
    $admin = User::factory()->create(['is_admin' => true]);

    foreach ([
        'list' => 'Chci dostávat novinky ŽďárAI',
        'subscriber' => 'Odhlásit odběr',
        'registrant' => 'Nechci dostávat další e-maily',
    ] as $audience => $needle) {
        $html = Livewire::actingAs($admin)
            ->test(NewsletterPreview::class)
            ->set('template', 'partner-event-ai-for-all-jihlava-' . $audience)
            ->assertStatus(200)
            ->assertDontSee('Šablonu se nepodařilo vyrenderovat')
            ->html();

        expect($html)->toContain('AI Monday Jihlava slaví rok')
            ->and($html)->toContain($needle);
    }

    Mail::assertNothingSent();
    Mail::assertNothingQueued();
});

// ── Příkaz: segment list ────────────────────────────────────────────────────

it('segment list bez --list selže a nic nepošle', function () {
    $this->artisan('partner-invite:send', ['key' => AFA_KEY, '--segment' => 'list', '--force' => true])
        ->expectsOutputToContain('vyžaduje --list')
        ->assertFailed();

    Mail::assertNothingQueued();
});

it('segment list s neexistujícím souborem selže', function () {
    $this->artisan('partner-invite:send', [
        'key' => AFA_KEY, '--segment' => 'list', '--list' => '/neexistuje/seznam.csv', '--force' => true,
    ])->assertFailed();

    Mail::assertNothingQueued();
});

it('segment list: dry-run parsuje CSV (7 sloupců, cancelled, dedup, neplatné) a nic nefrontuje', function () {
    $path = afaSampleCsv();

    $this->artisan('partner-invite:send', [
        'key' => AFA_KEY, '--segment' => 'list', '--list' => $path, '--dry-run' => true,
    ])
        ->expectsOutputToContain('anna@example.com (Anna Testová)')
        ->expectsOutputToContain('bob@example.com (Bob Test)')
        ->expectsOutputToContain('varování: 1')
        ->expectsOutputToContain('Příjemců by bylo: 2 (segment list)')
        ->doesntExpectOutputToContain('cyril@example.com')
        ->assertSuccessful();

    Mail::assertNothingQueued();
    expect(Cache::has('partner-invite:' . AFA_KEY . ':' . sha1('anna@example.com')))->toBeFalse();

    @unlink($path);
});

it('segment list: ostrý běh posílá s audience list bez unsubscribe a respektuje exclude', function () {
    $path = afaSampleCsv();

    $this->artisan('partner-invite:send', [
        'key' => AFA_KEY, '--segment' => 'list', '--list' => $path,
        '--exclude' => 'BOB@example.com', '--force' => true,
    ])
        ->expectsOutputToContain('Zařazeno do fronty: 1')
        ->assertSuccessful();

    Mail::assertQueuedCount(1);
    Mail::assertQueued(PartnerEventInvite::class, fn (PartnerEventInvite $m) => $m->hasTo('anna@example.com')
        && $m->audience === 'list'
        && $m->unsubscribeUrl === '');

    @unlink($path);
});

it('segment list: --test posílá s audience list', function () {
    $path = afaSampleCsv();

    $this->artisan('partner-invite:send', [
        'key' => AFA_KEY, '--segment' => 'list', '--list' => $path, '--test' => 'petr@example.com',
    ])->assertSuccessful();

    Mail::assertQueuedCount(1);
    Mail::assertQueued(PartnerEventInvite::class, fn (PartnerEventInvite $m) => $m->hasTo('petr@example.com')
        && $m->audience === 'list' && $m->unsubscribeUrl === '');

    @unlink($path);
});

it('claim je sdílený: kdo dostal mail ze segmentu list, v subscribers a registrants se přeskočí', function () {
    $path = afaSampleCsv();

    $this->artisan('partner-invite:send', [
        'key' => AFA_KEY, '--segment' => 'list', '--list' => $path, '--force' => true,
    ])->assertSuccessful();
    Mail::assertQueuedCount(2);

    NewsletterSubscriber::factory()->create(['email' => 'Anna@Example.com']);
    NewsletterSubscriber::factory()->create(['email' => 'novy@example.com']);

    $this->artisan('partner-invite:send', ['key' => AFA_KEY, '--segment' => 'subscribers', '--force' => true])
        ->expectsOutputToContain('Zařazeno do fronty: 1. Přeskočeno (už odesláno): 1.')
        ->assertSuccessful();

    Registration::factory()->create(['email' => 'bob@example.com', 'payment_status' => 'free', 'email_opt_out' => false]);

    $this->artisan('partner-invite:send', ['key' => AFA_KEY, '--segment' => 'registrants', '--force' => true])
        ->expectsOutputToContain('Zařazeno do fronty: 0. Přeskočeno (už odesláno): 1.')
        ->assertSuccessful();

    Mail::assertQueuedCount(3);

    @unlink($path);
});


// ── Rozšíření: --include-cancelled, odkaz na Dny AI, opt-in odkaz ───────────

it('segment list: --include-cancelled zahrne i zrušené registrace', function () {
    $path = afaSampleCsv();

    $this->artisan('partner-invite:send', [
        'key' => AFA_KEY, '--segment' => 'list', '--list' => $path, '--include-cancelled' => true, '--dry-run' => true,
    ])
        ->expectsOutputToContain('cyril@example.com (Cyril Zrušený)')
        ->expectsOutputToContain('včetně cancelled (1 řádků)')
        ->expectsOutputToContain('Příjemců by bylo: 3 (segment list)')
        ->assertSuccessful();

    $this->artisan('partner-invite:send', [
        'key' => AFA_KEY, '--segment' => 'list', '--list' => $path, '--include-cancelled' => true, '--force' => true,
    ])->expectsOutputToContain('Zařazeno do fronty: 3')->assertSuccessful();

    Mail::assertQueued(PartnerEventInvite::class, fn (PartnerEventInvite $m) => $m->hasTo('cyril@example.com')
        && $m->audience === 'list' && $m->recipientEmail === 'cyril@example.com');

    @unlink($path);
});

it('--include-cancelled mimo segment list selže', function () {
    $this->artisan('partner-invite:send', [
        'key' => AFA_KEY, '--segment' => 'subscribers', '--include-cancelled' => true, '--force' => true,
    ])->expectsOutputToContain('jen pro --segment=list')->assertFailed();

    Mail::assertNothingQueued();
});

it('segment list: ostrý běh i --test předávají mailable e-mail příjemce', function () {
    $path = afaSampleCsv();

    $this->artisan('partner-invite:send', [
        'key' => AFA_KEY, '--segment' => 'list', '--list' => $path, '--force' => true,
    ])->assertSuccessful();

    Mail::assertQueued(PartnerEventInvite::class, fn (PartnerEventInvite $m) => $m->hasTo('bob@example.com')
        && $m->recipientEmail === 'bob@example.com');

    $this->artisan('partner-invite:send', [
        'key' => AFA_KEY, '--segment' => 'list', '--list' => $path, '--test' => 'Petr@Example.com',
    ])->assertSuccessful();

    Mail::assertQueued(PartnerEventInvite::class, fn (PartnerEventInvite $m) => $m->hasTo('Petr@Example.com')
        && $m->recipientEmail === 'petr@example.com');

    @unlink($path);
});

it('mail pro list obsahuje podepsaný opt-in odkaz pro daného příjemce', function () {
    $html = afaInvite('list', '', 'anna@example.com')->render();

    expect($html)
        ->toContain('Chci dostávat novinky ŽďárAI')
        ->toContain('zhruba jednou měsíčně')
        ->toContain('Nechceš podobné tipy? Stačí odpovědět.');

    preg_match('~href="([^"]*/newsletter/prihlasit\?[^"]+)"~', $html, $m);
    expect($m)->toHaveKey(1);

    $url = html_entity_decode($m[1]);
    parse_str((string) parse_url($url, PHP_URL_QUERY), $query);

    expect($query['email'])->toBe('anna@example.com')
        ->and($query['source'])->toBe('ai-monday-1')
        ->and($query)->toHaveKeys(['expires', 'signature'])
        ->and((int) $query['expires'])->toBeGreaterThan(now()->addDays(59)->timestamp)
        ->and((int) $query['expires'])->toBeLessThanOrEqual(now()->addDays(60)->timestamp);

    $this->get($url)->assertOk()->assertSee('anna@example.com');
});

it('mail pro list bez e-mailu příjemce a mail pro subscriber/registrant opt-in odkaz nemají', function () {
    expect(afaInvite('list')->render())->not->toContain('/newsletter/prihlasit')
        ->and(afaInvite('subscriber', 'https://www.zdarai.cz/newsletter/odhlasit/t', 'anna@example.com')->render())
        ->not->toContain('/newsletter/prihlasit')->not->toContain('Chci dostávat novinky ŽďárAI')
        ->and(afaInvite('registrant', 'https://www.zdarai.cz/opt-out/t', 'anna@example.com')->render())
        ->not->toContain('/newsletter/prihlasit')->not->toContain('Chci dostávat novinky ŽďárAI');
});

it('box ŽďárAI odkazuje na stránku akce v programu Dny AI', function () {
    afaPromoEvent('published');

    expect(afaInvite()->render())
        ->toContain('href="https://dny.ai/program/zdarai-4-lokalni-ai-asistent-observabilita-ragu"')
        ->toContain('součástí festivalu Dny AI</a>');

    $config = (array) config('partner_events.' . AFA_KEY);
    unset($config['promo_event_dnyai_url']);
    $html = (new PartnerEventInvite($config))->render();

    expect($html)->toContain('součástí festivalu Dny AI.')
        ->not->toContain('zdarai-4-lokalni-ai-asistent');
});

it('admin náhled list ukazuje opt-in tlačítko s ukázkovou adresou', function () {
    $admin = User::factory()->create(['is_admin' => true]);

    $html = Livewire::actingAs($admin)
        ->test(NewsletterPreview::class)
        ->set('template', 'partner-event-ai-for-all-jihlava-list')
        ->html();

    expect($html)->toContain('Chci dostávat novinky ŽďárAI')
        ->and($html)->toContain('ukazka%40example.com');

    expect(NewsletterSubscriber::count())->toBe(0);
});

it('hosté: doména je odkaz jen s link v configu, jinak se brání autolinku', function () {
    $html = afaInvite()->render();

    expect($html)
        ->toContain('CTO Commity&zwnj;.cz')
        ->not->toContain('href="https://commity.cz')
        ->toContain('<a href="https://www.umelainteligence.cz"')
        ->toContain('>Uměláinteligence.cz</a>')
        ->toContain('<a href="https://inqubo.cz"');
});

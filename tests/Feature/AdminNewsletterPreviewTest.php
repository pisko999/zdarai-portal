<?php

declare(strict_types=1);

use App\Livewire\Admin\NewsletterPreview;
use App\Models\Event;
use App\Models\Registration;
use App\Models\Speaker;
use App\Models\Talk;
use App\Models\User;
use Livewire\Livewire;

function newsletterPreviewEvent(): Event
{
    $event = Event::factory()->create([
        'title' => 'ŽďárAI #3',
        'date' => now()->addDays(10),
    ]);

    Talk::factory()->create([
        'event_id' => $event->id,
        'speaker_id' => Speaker::factory()->create(['name' => 'Testovací Přednášející'])->id,
        'sort_order' => 1,
    ]);

    return $event;
}

it('nepřihlášený uživatel se na náhled newsletterů nedostane', function () {
    $this->get('/admin/newslettery')->assertRedirect('/login');
});

it('běžný uživatel se na náhled newsletterů nedostane', function () {
    $user = User::factory()->create(['is_admin' => false]);

    $this->actingAs($user)->get('/admin/newslettery')->assertStatus(403);
});

it('admin otevře stránku s náhledem newsletterů', function () {
    newsletterPreviewEvent();
    $admin = User::factory()->create(['is_admin' => true]);

    $this->actingAs($admin)
        ->get('/admin/newslettery')
        ->assertStatus(200)
        ->assertSee('Newslettery');
});

it('komponenta se vyrenderuje a nabídne všechny šablony', function () {
    newsletterPreviewEvent();
    $admin = User::factory()->create(['is_admin' => true]);

    $component = Livewire::actingAs($admin)->test(NewsletterPreview::class);

    $component->assertStatus(200);

    foreach (NewsletterPreview::TEMPLATES as $meta) {
        $component->assertSee($meta['label']);
    }
});

it('náhled každé šablony proběhne bez výjimky', function (string $template) {
    newsletterPreviewEvent();
    $admin = User::factory()->create(['is_admin' => true]);

    Livewire::actingAs($admin)
        ->test(NewsletterPreview::class)
        ->set('template', $template)
        ->assertStatus(200)
        ->assertDontSee('Šablonu se nepodařilo vyrenderovat')
        ->assertDontSee('je potřeba vybrat akci');
})->with(array_keys(NewsletterPreview::TEMPLATES));

it('náhled nic nezapisuje do databáze', function () {
    newsletterPreviewEvent();
    $admin = User::factory()->create(['is_admin' => true]);

    $component = Livewire::actingAs($admin)->test(NewsletterPreview::class);

    foreach (array_keys(NewsletterPreview::TEMPLATES) as $template) {
        $component->set('template', $template)->assertStatus(200);
    }

    expect(Registration::count())->toBe(0);
});

it('náhled zobrazí předmět mailu a izolovaný iframe', function () {
    newsletterPreviewEvent();
    $admin = User::factory()->create(['is_admin' => true]);

    $html = Livewire::actingAs($admin)
        ->test(NewsletterPreview::class)
        ->set('template', 'event-newsletter')
        ->html();

    expect($html)->toContain('srcdoc=')
        ->and($html)->toContain('Předmět')
        ->and($html)->toContain('🎤 ŽďárAI #3')          // předmět z envelope()
        ->and($html)->toContain('&lt;!DOCTYPE html&gt;'); // tělo mailu escapované v srcdoc
});

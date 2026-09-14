<?php

declare(strict_types=1);

use App\Mail\EventFollowUp;
use App\Models\Event;
use App\Models\NewsletterSubscriber;
use App\Models\Registration;
use App\Models\Speaker;
use App\Models\Talk;
use Illuminate\Support\Facades\Mail;

beforeEach(function () {
    Mail::fake();

    // Přílohy bereme z prázdné temp složky, ať testy nezávisí na reálném storage.
    $this->emptyDir = sys_get_temp_dir() . '/followup-test-' . uniqid();
    mkdir($this->emptyDir);
    config()->set('zdarai.presentations_dir', $this->emptyDir);

    $this->past = Event::factory()->create(['date' => now()->subDay()]);
    $this->older = Event::factory()->create(['date' => now()->subMonth()]);
});

/** @return list<string> příjemci dané varianty */
function recipientsOf(string $variant): array
{
    $emails = [];

    Mail::assertQueued(EventFollowUp::class, function (EventFollowUp $mail) use ($variant, &$emails) {
        if ($mail->variant === $variant) {
            foreach ($mail->to as $to) {
                $emails[] = $to['address'];
            }
        }

        return true;
    });

    return $emails;
}

it('pošle účastníkům poděkování a ostatním shrnutí', function () {
    Registration::factory()->create([
        'event_id' => $this->past->id, 'email' => 'prisel@example.com',
        'payment_status' => 'free', 'email_opt_out' => false,
    ]);
    Registration::factory()->create([
        'event_id' => $this->older->id, 'email' => 'stary@example.com',
        'payment_status' => 'free', 'email_opt_out' => false,
    ]);
    NewsletterSubscriber::factory()->create(['email' => 'odberatel@example.com', 'unsubscribed_at' => null]);

    $this->artisan('followup:send', ['event' => $this->past->id, '--no-attachments' => true])
        ->assertSuccessful();

    expect(recipientsOf(EventFollowUp::VARIANT_ATTENDED))->toBe(['prisel@example.com']);
    expect(recipientsOf(EventFollowUp::VARIANT_MISSED))
        ->toContain('stary@example.com', 'odberatel@example.com')
        ->not->toContain('prisel@example.com');
});

it('nepošle obě varianty jednomu člověku se dvěma adresami', function () {
    Registration::factory()->create([
        'event_id' => $this->past->id, 'name' => 'Jan Gabriel',
        'email' => 'info@firma.cz', 'payment_status' => 'free', 'email_opt_out' => false,
    ]);
    Registration::factory()->create([
        'event_id' => $this->older->id, 'name' => 'Jan Gabriel',
        'email' => 'jan.gabriel@firma.cz', 'payment_status' => 'free', 'email_opt_out' => false,
    ]);

    $this->artisan('followup:send', ['event' => $this->past->id, '--no-attachments' => true])
        ->assertSuccessful();

    expect(recipientsOf(EventFollowUp::VARIANT_MISSED))->not->toContain('jan.gabriel@firma.cz');
    Mail::assertQueuedCount(1);
});

it('napíše jedné osobě jen jednou, i když má víc adres v segmentu missed', function () {
    foreach (['a@example.com', 'b@example.com'] as $email) {
        Registration::factory()->create([
            'event_id' => $this->older->id, 'name' => 'Bohumír Sláma',
            'email' => $email, 'payment_status' => 'free', 'email_opt_out' => false,
        ]);
    }

    $this->artisan('followup:send', ['event' => $this->past->id, '--no-attachments' => true])
        ->assertSuccessful();

    expect(recipientsOf(EventFollowUp::VARIANT_MISSED))->toHaveCount(1);
});

it('vynechá přednášející akce, i když nemají registraci', function () {
    $speaker = Speaker::factory()->create(['name' => 'Jan Zbirovský']);
    Talk::factory()->create(['event_id' => $this->past->id, 'speaker_id' => $speaker->id]);

    Registration::factory()->create([
        'event_id' => $this->older->id, 'name' => 'Jan Zbirovský',
        'email' => 'jan@zbirovsky.example', 'payment_status' => 'free', 'email_opt_out' => false,
    ]);

    $this->artisan('followup:send', ['event' => $this->past->id, '--no-attachments' => true])
        ->assertSuccessful();

    Mail::assertNotQueued(EventFollowUp::class);
});

it('respektuje email_opt_out i zrušené registrace', function () {
    Registration::factory()->create([
        'event_id' => $this->past->id, 'email' => 'optout@example.com',
        'payment_status' => 'free', 'email_opt_out' => true,
    ]);
    Registration::factory()->create([
        'event_id' => $this->past->id, 'email' => 'zrusil@example.com',
        'payment_status' => 'cancelled', 'email_opt_out' => false,
    ]);

    $this->artisan('followup:send', ['event' => $this->past->id, '--no-attachments' => true])
        ->assertSuccessful();

    Mail::assertNotQueued(EventFollowUp::class);
});

it('v dry-run režimu nic neodešle', function () {
    Registration::factory()->create([
        'event_id' => $this->past->id, 'email' => 'prisel@example.com',
        'payment_status' => 'free', 'email_opt_out' => false,
    ]);

    $this->artisan('followup:send', [
        'event' => $this->past->id, '--no-attachments' => true, '--dry-run' => true,
    ])->assertSuccessful();

    Mail::assertNothingQueued();
});

afterEach(function () {
    foreach (glob($this->emptyDir . '/*') ?: [] as $file) {
        unlink($file);
    }

    rmdir($this->emptyDir);
});

it('selže, pokud nejsou k dispozici PDF přílohy', function () {
    $this->artisan('followup:send', ['event' => $this->past->id])->assertFailed();
});

it('přiloží PDF ze složky prezentací', function () {
    file_put_contents($this->emptyDir . '/slidy.pdf', '%PDF-1.4 test');

    Registration::factory()->create([
        'event_id' => $this->past->id, 'email' => 'prisel@example.com',
        'payment_status' => 'free', 'email_opt_out' => false,
    ]);

    $this->artisan('followup:send', ['event' => $this->past->id])->assertSuccessful();

    Mail::assertQueued(EventFollowUp::class, fn (EventFollowUp $mail): bool => $mail->attachmentPaths === [$this->emptyDir . '/slidy.pdf']);
});

it('pošle přednášejícím vlastní variantu s názvem jejich přednášky', function () {
    $speaker = Speaker::factory()->create(['name' => 'Jan Zbirovský']);
    Talk::factory()->create([
        'event_id' => $this->past->id, 'speaker_id' => $speaker->id,
        'title' => 'Lokální jazykové modely II',
    ]);
    Registration::factory()->create([
        'event_id' => $this->older->id, 'name' => 'Jan Zbirovský',
        'email' => 'jan@zbirovsky.example', 'payment_status' => 'free', 'email_opt_out' => false,
    ]);

    $this->artisan('followup:send', [
        'event' => $this->past->id, '--segment' => 'speakers', '--no-attachments' => true,
    ])->assertSuccessful();

    Mail::assertQueued(EventFollowUp::class, fn (EventFollowUp $mail): bool => $mail->variant === EventFollowUp::VARIANT_SPEAKER
        && $mail->talkTitle === 'Lokální jazykové modely II'
        && $mail->attachmentPaths === []);
});

it('osloví i přednášejícího s odhlášenými připomínkami', function () {
    $speaker = Speaker::factory()->create(['name' => 'Tomáš Miškovský']);
    Talk::factory()->create(['event_id' => $this->past->id, 'speaker_id' => $speaker->id]);
    Registration::factory()->create([
        'event_id' => $this->older->id, 'name' => 'Tomáš Miškovský',
        'email' => 'miki@example.cz', 'payment_status' => 'free', 'email_opt_out' => true,
    ]);

    $this->artisan('followup:send', [
        'event' => $this->past->id, '--segment' => 'speakers', '--no-attachments' => true,
    ])->assertSuccessful();

    Mail::assertQueuedCount(1);
});

it('segment all přednášejícím nic neposílá', function () {
    $speaker = Speaker::factory()->create(['name' => 'Jan Zbirovský']);
    Talk::factory()->create(['event_id' => $this->past->id, 'speaker_id' => $speaker->id]);
    Registration::factory()->create([
        'event_id' => $this->older->id, 'name' => 'Jan Zbirovský',
        'email' => 'jan@zbirovsky.example', 'payment_status' => 'free', 'email_opt_out' => false,
    ]);

    $this->artisan('followup:send', ['event' => $this->past->id, '--no-attachments' => true])
        ->assertSuccessful();

    Mail::assertNotQueued(EventFollowUp::class);
});

it('vyrenderuje každou variantu bez chyby', function (string $variant) {
    $speaker = Speaker::factory()->create(['name' => 'Jan Zbirovský']);
    Talk::factory()->create([
        'event_id' => $this->past->id, 'speaker_id' => $speaker->id,
        'title' => 'Lokální jazykové modely II',
    ]);

    $mail = new EventFollowUp(
        $this->past->fresh()->load('talks.speaker'),
        $variant,
        'Michale',
        'https://example.test/opt-out/abc',
        [],
        'Lokální jazykové modely II',
    );

    $html = $mail->render();

    expect($html)->toContain('ŽďárAI')
        ->and($mail->envelope()->subject)->not->toBeEmpty();
})->with([
    EventFollowUp::VARIANT_ATTENDED,
    EventFollowUp::VARIANT_MISSED,
    EventFollowUp::VARIANT_SPEAKER,
]);

it('zmíní přílohy jen když nějaké opravdu jsou', function () {
    $event = $this->past->fresh()->load('talks.speaker');

    $without = (new EventFollowUp($event, EventFollowUp::VARIANT_ATTENDED, '', '', []))->render();
    $with = (new EventFollowUp($event, EventFollowUp::VARIANT_ATTENDED, '', '', ['/tmp/x.pdf']))->render();

    expect($without)->not->toContain('V příloze')
        ->and($with)->toContain('V příloze');
});

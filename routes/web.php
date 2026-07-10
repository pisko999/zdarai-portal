<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('home');
});

Route::get('/udalosti/{slug}', function (string $slug) {
    $event = \App\Models\Event::where('slug', $slug)
        ->where('status', 'published')
        ->with(['talks.speaker', 'talks.translations', 'registrations', 'translations'])
        ->firstOrFail();
    return view('events.show', compact('event'));
})->name('events.show');

// Opt-out route pro emailové odhlášení připomínek
Route::get('/opt-out/{token}', function (string $token) {
    $registration = \App\Models\Registration::where('token', $token)->firstOrFail();
    $registration->update(['email_opt_out' => true]);
    return view('opt-out');
})->name('opt-out');

// Newsletter — odhlášení přes neuhodnutelný UUID token
Route::get('/newsletter/odhlasit/{token}', function (string $token) {
    $subscriber = \App\Models\NewsletterSubscriber::where('token', $token)->firstOrFail();
    if ($subscriber->unsubscribed_at === null) {
        $subscriber->update(['unsubscribed_at' => now()]);
    }
    return view('newsletter.unsubscribed');
})->middleware('throttle:20,1')->name('newsletter.unsubscribe');

// Tracking pixel — sledování otevření informačního e-mailu
Route::get('/e/o/{token}.png', function (string $token, \Illuminate\Http\Request $request) {
    $registration = \App\Models\Registration::where('token', $token)->first();
    if ($registration) {
        $open = \App\Models\EmailOpen::firstOrNew([
            'registration_id' => $registration->id,
            'mailable' => 'first_meetup_info',
        ]);
        $now = now();
        $open->first_opened_at ??= $now;
        $open->last_opened_at = $now;
        $open->open_count = (int) $open->open_count + 1;
        $open->ip_address = $request->ip();
        $open->user_agent = \Illuminate\Support\Str::limit((string) $request->userAgent(), 250, '');
        $open->save();
    }

    // 1x1 transparentní GIF
    $gif = base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7');
    return response($gif, 200, [
        'Content-Type' => 'image/gif',
        'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
        'Pragma' => 'no-cache',
    ]);
})->name('email.pixel');

// Anketa — zájem o večeři/pivo po akci (sleduje otevření přes token)
Route::get('/anketa/{token}', function (string $token) {
    $registration = \App\Models\Registration::where('token', $token)
        ->with('event')
        ->firstOrFail();

    \App\Models\DinnerPollResponse::firstOrCreate(
        ['registration_id' => $registration->id],
        ['opened_at' => now()],
    );

    return view('poll', compact('registration'));
})->name('poll');

Route::get('/lang/{locale}', function (string $locale) {
    $allowed = ['cs', 'en'];
    if (!in_array($locale, $allowed, true)) {
        abort(404);
    }
    session(['locale' => $locale]);
    return redirect()->back();
})->name('lang.switch');

Route::get('/o-nas', fn() => view('about'))->name('about');

Route::get('/flayer', function () {
    $event = \App\Models\Event::with(['talks.speaker'])
        ->where('status', 'published')
        ->where('date', '>=', now())
        ->orderBy('date')
        ->firstOrFail();
    return view('flayer', compact('event'));
})->name('flayer');

Route::get('/flayer/pdf', function () {
    $event = \App\Models\Event::with(['talks.speaker'])
        ->where('status', 'published')
        ->where('date', '>=', now())
        ->orderBy('date')
        ->firstOrFail();

    $slug = \Illuminate\Support\Str::slug($event->title);
    $filename = "zdarai-flyer-{$slug}.pdf";
    $path = storage_path("app/public/flyers/{$filename}");

    if (! is_dir(dirname($path))) {
        mkdir(dirname($path), 0755, true);
    }

    \Spatie\Browsershot\Browsershot::url(route('flayer'))
        ->setChromePath('/usr/bin/google-chrome')
        ->noSandbox()
        ->waitUntilNetworkIdle()
        ->paperSize(148, 210)
        ->landscape(false)
        ->margins(0, 0, 0, 0)
        ->showBackground()
        ->save($path);

    return response()->download($path, $filename, [
        'Content-Type' => 'application/pdf',
    ]);
})->name('flayer.pdf');

Route::get('/archiv', function () {
    $events = \App\Models\Event::where('status', 'published')
        ->where('date', '<', now())
        ->with(['talks', 'registrations'])
        ->orderByDesc('date')
        ->get();
    return view('archive', compact('events'));
})->name('archive');

Route::get('/sitemap.xml', function () {
    $events = \App\Models\Event::where('status', 'published')
        ->orderByDesc('date')
        ->get(['slug', 'updated_at']);

    $content = view('sitemap', compact('events'))->render();
    return response($content, 200)
        ->header('Content-Type', 'application/xml; charset=UTF-8');
})->name('sitemap');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/moje-registrace', function () {
    return view('my-registrations');
})->middleware('auth')->name('my-registrations');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

// Admin routes
Route::prefix('admin')
    ->middleware(['auth', 'is_admin'])
    ->group(function () {
        Route::get('/', fn() => view('admin.index'))->name('admin.dashboard');
        Route::get('/events', fn() => view('admin.events.index'))->name('admin.events.index');
        Route::get('/talks', fn() => view('admin.talks.index'))->name('admin.talks.index');
        Route::get('/speakers', fn() => view('admin.speakers.index'))->name('admin.speakers.index');
        Route::get('/registrations', fn() => view('admin.registrations.index'))->name('admin.registrations.index');
        Route::get('/mailing', fn() => view('admin.mailing.index'))->name('admin.mailing.index');
        Route::get('/users', fn() => view('admin.users.index'))->name('admin.users.index');
    });

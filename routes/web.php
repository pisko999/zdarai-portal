<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('home');
});

Route::get('/udalosti/{slug}', function (string $slug) {
    $event = \App\Models\Event::where('slug', $slug)
        ->where('status', 'published')
        ->with(['talks.speaker', 'registrations'])
        ->firstOrFail();
    return view('events.show', compact('event'));
})->name('events.show');

// Opt-out route pro emailové odhlášení připomínek
Route::get('/opt-out/{token}', function (string $token) {
    $registration = \App\Models\Registration::where('token', $token)->firstOrFail();
    $registration->update(['email_opt_out' => true]);
    return view('opt-out');
})->name('opt-out');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

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
    });

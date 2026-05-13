<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\Registration;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;

class RegistrationApproved extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public readonly ?string $setPasswordUrl;

    public function __construct(
        public readonly Registration $registration,
    ) {
        $user = $registration->user;

        if ($user && DB::table('password_reset_tokens')->where('email', $user->email)->exists()) {
            // Uživatel ještě nenastavil heslo — obnov token na 24h
            $token = Password::broker()->createToken($user);
            $this->setPasswordUrl = url('/reset-password/' . $token . '?email=' . urlencode($user->email));
        } else {
            $this->setPasswordUrl = null;
        }
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Registrace potvrzena — ' . $this->registration->event->title,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.registration-approved',
        );
    }
}

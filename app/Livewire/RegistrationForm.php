<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Mail\RegistrationConfirmed;
use App\Models\Event;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Livewire\Component;

class RegistrationForm extends Component
{
    public Event $event;

    public string $name = '';
    public string $email = '';
    public string $ai_level = '';
    public string $organization = '';
    public bool $email_opt_in = true;

    public bool $success = false;
    public string $errorMessage = '';

    protected function rules(): array
    {
        return [
            'name'         => ['required', 'string', 'min:2', 'max:100'],
            'email'        => ['required', 'email:rfc', 'max:200'],
            'ai_level'     => ['nullable', 'in:beginner,intermediate,advanced,expert'],
            'organization' => ['nullable', 'string', 'max:255'],
        ];
    }

    protected function messages(): array
    {
        return [
            'name.required'  => 'Jméno je povinné.',
            'name.min'       => 'Jméno musí mít alespoň 2 znaky.',
            'email.required' => 'E-mail je povinný.',
            'email.email'    => 'Zadejte platný e-mail.',
        ];
    }

    public function register(): void
    {
        $this->errorMessage = '';
        $this->validate();

        try {
            DB::transaction(function (): void {
                $event = Event::lockForUpdate()->find($this->event->id);

                if ($event->isFull()) {
                    $this->errorMessage = 'Omlouváme se, kapacita byla právě naplněna.';
                    return;
                }

                $exists = Registration::where('event_id', $event->id)
                    ->where('email', $this->email)
                    ->whereNotIn('payment_status', ['cancelled'])
                    ->exists();

                if ($exists) {
                    $this->errorMessage = 'Na tuto akci jste již zaregistrován/a.';
                    return;
                }

                // Resolve user_id — přihlášený uživatel / existující účet / nový účet
                $userId = auth()->id();
                $setPasswordUrl = null;

                if (!$userId) {
                    $existingUser = User::where('email', $this->email)->first();
                    if ($existingUser) {
                        $userId = $existingUser->id;
                    } else {
                        // Nový uživatel — vytvoříme účet s náhodným heslem
                        $newUser = User::create([
                            'name'     => $this->name,
                            'email'    => $this->email,
                            'password' => Hash::make(Str::random(32)),
                        ]);
                        $userId = $newUser->id;
                        $token = Password::broker()->createToken($newUser);
                        $setPasswordUrl = url('/reset-password/' . $token . '?email=' . urlencode($this->email));
                    }
                }

                $registration = Registration::create([
                    'event_id'       => $event->id,
                    'user_id'        => $userId,
                    'name'           => $this->name,
                    'email'          => $this->email,
                    'token'          => (string) Str::uuid(),
                    'payment_status' => $event->price === null ? 'free' : 'pending',
                    'email_opt_out'  => ! $this->email_opt_in,
                    'ai_level'       => $this->ai_level ?: null,
                    'organization'   => $this->organization ?: null,
                ]);

                Mail::to($this->email)
                    ->queue(new RegistrationConfirmed($registration, $setPasswordUrl));
            });

            if (empty($this->errorMessage)) {
                $this->success = true;
                $this->reset(['name', 'email', 'ai_level', 'organization', 'email_opt_in']);
            }
        } catch (\Throwable $e) {
            report($e);
            $this->errorMessage = 'Nastala neočekávaná chyba. Zkuste to prosím znovu.';
        }
    }

    public function render(): View
    {
        return view('livewire.registration-form');
    }
}

<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\DinnerPollResponse;
use App\Models\Registration;
use Illuminate\View\View;
use Livewire\Attributes\Validate;
use Livewire\Component;

class DinnerPoll extends Component
{
    public Registration $registration;

    #[Validate('required|in:dinner,beer,none')]
    public string $attendance = '';

    #[Validate('nullable|string|max:255')]
    public string $foodChoice = '';

    #[Validate('nullable|string|max:255')]
    public string $note = '';

    public bool $submitted = false;

    public function mount(Registration $registration): void
    {
        $this->registration = $registration;

        $poll = $this->registration->dinnerPoll;
        if ($poll && $poll->responded_at) {
            $this->attendance = $poll->attendance ?? '';
            $this->foodChoice = $poll->food_choice ?? '';
            $this->note = $poll->note ?? '';
            $this->submitted = true;
        }
    }

    public function submit(): void
    {
        $this->validate();

        DinnerPollResponse::updateOrCreate(
            ['registration_id' => $this->registration->id],
            [
                'attendance' => $this->attendance,
                'food_choice' => $this->attendance === 'dinner' ? $this->foodChoice : null,
                'note' => $this->note,
                'responded_at' => now(),
            ]
        );

        $this->submitted = true;
    }

    public function render(): View
    {
        return view('livewire.dinner-poll');
    }
}

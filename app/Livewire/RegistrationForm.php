<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\Event;
use Illuminate\View\View;
use Livewire\Component;

class RegistrationForm extends Component
{
    public Event $event;

    public function render(): View
    {
        return view('livewire.registration-form');
    }
}

<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\Event;
use Illuminate\View\View;
use Livewire\Component;

class EventList extends Component
{
    public string $filter = 'upcoming'; // upcoming | past

    public function render(): View
    {
        $events = Event::query()
            ->where('status', 'published')
            ->when($this->filter === 'upcoming', fn ($q) => $q->where('date', '>=', now()))
            ->when($this->filter === 'past', fn ($q) => $q->where('date', '<', now()))
            ->orderBy('date', $this->filter === 'past' ? 'desc' : 'asc')
            ->with(['talks.speaker', 'talks.translations', 'registrations', 'translations'])
            ->get();

        return view('livewire.event-list', ['events' => $events]);
    }
}

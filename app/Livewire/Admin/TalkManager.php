<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Models\Event;
use App\Models\Speaker;
use App\Models\Talk;
use Illuminate\View\View;
use Livewire\Component;

class TalkManager extends Component
{
    // Filters
    public string $filterEvent = '';

    // Modal
    public bool $showModal = false;
    public ?int $editingId = null;

    // Form fields
    public ?int $event_id = null;
    public ?int $speaker_id = null;
    public string $title = '';
    public string $description = '';
    public string $duration_minutes = '';
    public string $sort_order = '0';

    // Delete
    public ?int $confirmDelete = null;

    protected function rules(): array
    {
        return [
            'event_id'         => ['required', 'integer', 'exists:events,id'],
            'speaker_id'       => ['nullable', 'integer', 'exists:speakers,id'],
            'title'            => ['required', 'string', 'min:3', 'max:255'],
            'description'      => ['nullable', 'string', 'max:5000'],
            'duration_minutes' => ['nullable', 'integer', 'min:1', 'max:480'],
            'sort_order'       => ['required', 'integer', 'min:0'],
        ];
    }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->editingId = null;
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $talk = Talk::findOrFail($id);
        $this->editingId = $id;
        $this->event_id = $talk->event_id;
        $this->speaker_id = $talk->speaker_id;
        $this->title = $talk->title;
        $this->description = $talk->description ?? '';
        $this->duration_minutes = $talk->duration_minutes !== null ? (string) $talk->duration_minutes : '';
        $this->sort_order = (string) $talk->sort_order;
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'event_id'         => $this->event_id,
            'speaker_id'       => $this->speaker_id ?: null,
            'title'            => $this->title,
            'description'      => $this->description ?: null,
            'duration_minutes' => $this->duration_minutes !== '' ? (int) $this->duration_minutes : null,
            'sort_order'       => (int) $this->sort_order,
        ];

        if ($this->editingId) {
            Talk::findOrFail($this->editingId)->update($data);
        } else {
            Talk::create($data);
        }

        $this->showModal = false;
        $this->resetForm();
        session()->flash('success', $this->editingId ? 'Přednáška aktualizována.' : 'Přednáška vytvořena.');
    }

    public function confirmDeleteTalk(int $id): void
    {
        $this->confirmDelete = $id;
    }

    public function deleteTalk(): void
    {
        if ($this->confirmDelete) {
            Talk::findOrFail($this->confirmDelete)->delete();
            $this->confirmDelete = null;
            session()->flash('success', 'Přednáška smazána.');
        }
    }

    private function resetForm(): void
    {
        $this->event_id = null;
        $this->speaker_id = null;
        $this->title = '';
        $this->description = '';
        $this->duration_minutes = '';
        $this->sort_order = '0';
        $this->resetErrorBag();
    }

    public function render(): View
    {
        $talks = Talk::query()
            ->when($this->filterEvent, fn ($q) => $q->where('event_id', $this->filterEvent))
            ->with(['event', 'speaker'])
            ->orderBy('event_id', 'desc')
            ->orderBy('sort_order')
            ->get();

        $events = Event::orderByDesc('date')->get(['id', 'title']);
        $speakers = Speaker::orderBy('name')->get(['id', 'name']);

        return view('livewire.admin.talk-manager', compact('talks', 'events', 'speakers'));
    }
}

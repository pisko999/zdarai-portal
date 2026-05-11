<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Models\Event;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Livewire\Component;

class EventManager extends Component
{
    // List
    public string $search = '';

    // Modal
    public bool $showModal = false;
    public ?int $editingId = null;

    // Form fields
    public string $title = '';
    public string $description = '';
    public string $date = '';
    public string $location = '';
    public ?int $capacity = 50;
    public string $price = '';
    public string $status = 'draft';

    // Delete confirm
    public ?int $confirmDelete = null;

    protected function rules(): array
    {
        return [
            'title'       => ['required', 'string', 'min:3', 'max:255'],
            'description' => ['nullable', 'string', 'max:10000'],
            'date'        => ['required', 'date', 'after:now'],
            'location'    => ['nullable', 'string', 'max:255'],
            'capacity'    => ['required', 'integer', 'min:1', 'max:9999'],
            'price'       => ['nullable', 'numeric', 'min:0'],
            'status'      => ['required', 'in:draft,published,archived'],
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
        $event = Event::findOrFail($id);
        $this->editingId = $id;
        $this->title = $event->title;
        $this->description = $event->description ?? '';
        $this->date = $event->date->format('Y-m-d\TH:i');
        $this->location = $event->location ?? '';
        $this->capacity = $event->capacity;
        $this->price = $event->price !== null ? (string) $event->price : '';
        $this->status = $event->status;
        $this->showModal = true;
    }

    public function save(): void
    {
        $rules = $this->rules();
        if ($this->editingId) {
            $event = Event::findOrFail($this->editingId);
            if ($event->date->format('Y-m-d\TH:i') === $this->date) {
                $rules['date'] = ['required', 'date'];
            }
        }
        $this->validate($rules);

        $data = [
            'title'       => $this->title,
            'slug'        => Str::slug($this->title) . '-' . now()->timestamp,
            'description' => $this->description ?: null,
            'date'        => $this->date,
            'location'    => $this->location ?: null,
            'capacity'    => $this->capacity,
            'price'       => $this->price !== '' ? (float) $this->price : null,
            'status'      => $this->status,
        ];

        if ($this->editingId) {
            $event = Event::findOrFail($this->editingId);
            if ($event->title === $this->title) {
                unset($data['slug']);
            }
            $event->update($data);
        } else {
            Event::create($data);
        }

        $this->showModal = false;
        $this->resetForm();
        session()->flash('success', $this->editingId ? 'Událost aktualizována.' : 'Událost vytvořena.');
    }

    public function confirmDeleteEvent(int $id): void
    {
        $this->confirmDelete = $id;
    }

    public function deleteEvent(): void
    {
        if ($this->confirmDelete) {
            Event::findOrFail($this->confirmDelete)->delete();
            $this->confirmDelete = null;
            session()->flash('success', 'Událost smazána.');
        }
    }

    private function resetForm(): void
    {
        $this->title = '';
        $this->description = '';
        $this->date = '';
        $this->location = '';
        $this->capacity = 50;
        $this->price = '';
        $this->status = 'draft';
        $this->resetErrorBag();
    }

    public function render(): View
    {
        $events = Event::query()
            ->when($this->search, fn ($q) => $q->where('title', 'like', "%{$this->search}%"))
            ->withCount(['registrations as active_registrations_count' => function ($q) {
                $q->whereNotIn('payment_status', ['cancelled']);
            }])
            ->orderByDesc('date')
            ->get();

        return view('livewire.admin.event-manager', ['events' => $events]);
    }
}

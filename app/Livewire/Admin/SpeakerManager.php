<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Models\Speaker;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithFileUploads;

class SpeakerManager extends Component
{
    use WithFileUploads;

    public bool $showModal = false;
    public ?int $editingId = null;

    public string $name = '';
    public string $bio = '';
    public string $github_url = '';
    public string $linkedin_url = '';
    public $avatar = null;

    public ?int $confirmDelete = null;

    protected function rules(): array
    {
        return [
            'name'         => ['required', 'string', 'min:2', 'max:100'],
            'bio'          => ['nullable', 'string', 'max:2000'],
            'github_url'   => ['nullable', 'url', 'max:255'],
            'linkedin_url' => ['nullable', 'url', 'max:255'],
            'avatar'       => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
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
        $speaker = Speaker::findOrFail($id);
        $this->editingId = $id;
        $this->name = $speaker->name;
        $this->bio = $speaker->bio ?? '';
        $this->github_url = $speaker->github_url ?? '';
        $this->linkedin_url = $speaker->linkedin_url ?? '';
        $this->avatar = null;
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'name'         => $this->name,
            'bio'          => $this->bio ?: null,
            'github_url'   => $this->github_url ?: null,
            'linkedin_url' => $this->linkedin_url ?: null,
        ];

        if ($this->avatar) {
            $path = $this->avatar->store('avatars', 'public');
            $data['avatar'] = $path;

            if ($this->editingId) {
                $old = Speaker::find($this->editingId)?->avatar;
                if ($old) {
                    Storage::disk('public')->delete($old);
                }
            }
        }

        if ($this->editingId) {
            Speaker::findOrFail($this->editingId)->update($data);
        } else {
            Speaker::create($data);
        }

        $this->showModal = false;
        $this->resetForm();
        session()->flash('success', $this->editingId ? 'Přednášející aktualizován.' : 'Přednášející vytvořen.');
    }

    public function confirmDeleteSpeaker(int $id): void
    {
        $this->confirmDelete = $id;
    }

    public function deleteSpeaker(): void
    {
        if ($this->confirmDelete) {
            $speaker = Speaker::findOrFail($this->confirmDelete);
            if ($speaker->avatar) {
                Storage::disk('public')->delete($speaker->avatar);
            }
            $speaker->delete();
            $this->confirmDelete = null;
            session()->flash('success', 'Přednášející smazán.');
        }
    }

    private function resetForm(): void
    {
        $this->name = '';
        $this->bio = '';
        $this->github_url = '';
        $this->linkedin_url = '';
        $this->avatar = null;
        $this->resetErrorBag();
    }

    public function render(): View
    {
        $speakers = Speaker::withCount('talks')->orderBy('name')->get();
        return view('livewire.admin.speaker-manager', compact('speakers'));
    }
}

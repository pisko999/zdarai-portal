<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\View\View;

class UserManager extends Component
{
    use WithPagination;

    public string $search = '';
    public ?int $confirmDelete = null;

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function toggleAdmin(int $id): void
    {
        $user = User::findOrFail($id);

        // Nesmíme odebrat admin práva sobě samému
        if ($user->id === auth()->id()) {
            return;
        }

        $user->update(['is_admin' => ! $user->is_admin]);
    }

    public function confirmDelete(int $id): void
    {
        $this->confirmDelete = $id;
    }

    public function cancelDelete(): void
    {
        $this->confirmDelete = null;
    }

    public function delete(int $id): void
    {
        // Nesmíme smazat sebe
        if ($id === auth()->id()) {
            return;
        }

        User::findOrFail($id)->delete();
        $this->confirmDelete = null;
    }

    public function render(): View
    {
        $users = User::query()
            ->withCount('registrations')
            ->when($this->search, fn ($q) => $q
                ->where('name', 'like', '%' . $this->search . '%')
                ->orWhere('email', 'like', '%' . $this->search . '%')
            )
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('livewire.admin.user-manager', compact('users'));
    }
}

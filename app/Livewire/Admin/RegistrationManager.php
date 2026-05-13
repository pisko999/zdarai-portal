<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\Registration;
use App\Models\Event;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class RegistrationManager extends Component
{
    public string $filterEvent = '';
    public string $filterStatus = '';
    public string $search = '';

    public ?int $confirmDelete = null;

    public function updatedFilterEvent(): void
    {
        // Reset search when event filter changes
        $this->search = '';
    }

    public function updatePaymentStatus(int $id, string $status): void
    {
        $allowed = ['waitlist', 'free', 'pending', 'paid', 'confirmed', 'cancelled'];
        if (!in_array($status, $allowed, true)) {
            return;
        }
        Registration::findOrFail($id)->update(['payment_status' => $status]);
        session()->flash('success', 'Status aktualizován.');
    }

    public function confirmRegistration(int $id): void
    {
        $reg = Registration::findOrFail($id);
        if ($reg->payment_status === 'waitlist') {
            $reg->update(['payment_status' => 'confirmed']);
            session()->flash('success', 'Registrace potvrzena — účastník má zarezervované místo.');
        }
    }

    public function confirmDeleteRegistration(int $id): void
    {
        $this->confirmDelete = $id;
    }

    public function deleteRegistration(): void
    {
        if ($this->confirmDelete) {
            Registration::findOrFail($this->confirmDelete)->delete();
            $this->confirmDelete = null;
            session()->flash('success', 'Registrace smazána.');
        }
    }

    public function exportCsv(): StreamedResponse
    {
        $registrations = $this->getQuery()->get();

        $filename = 'registrace_' . ($this->filterEvent ? 'udalost-' . $this->filterEvent . '_' : '') . date('Y-m-d') . '.csv';

        return response()->streamDownload(function () use ($registrations) {
            $handle = fopen('php://output', 'w');
            // BOM pro Excel UTF-8
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($handle, [
                'ID', 'Jméno', 'E-mail', 'Organizace', 'Úroveň AI', 'Událost',
                'Platební status', 'Email opt-out', 'Datum registrace'
            ], ';');

            foreach ($registrations as $reg) {
                fputcsv($handle, [
                    $reg->id,
                    $reg->name,
                    $reg->email,
                    $reg->organization ?? '',
                    match($reg->ai_level) {
                        'beginner'     => 'Začátečník',
                        'intermediate' => 'Pokročilý',
                        'advanced'     => 'Expert',
                        'expert'       => 'Profesionál',
                        default        => '',
                    },
                    $reg->event?->title ?? '',
                    $reg->payment_status,
                    $reg->email_opt_out ? 'Ano' : 'Ne',
                    $reg->created_at->format('j. n. Y H:i'),
                ], ';');
            }
            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function getQuery()
    {
        return Registration::query()
            ->when($this->filterEvent, fn($q) => $q->where('event_id', $this->filterEvent))
            ->when($this->filterStatus, fn($q) => $q->where('payment_status', $this->filterStatus))
            ->when($this->search, fn($q) => $q->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                  ->orWhere('email', 'like', "%{$this->search}%");
            }))
            ->with('event')
            ->orderByDesc('created_at');
    }

    public function render(): View
    {
        $registrations = $this->getQuery()->get();
        $events = Event::orderByDesc('date')->get(['id', 'title']);

        return view('livewire.admin.registration-manager', compact('registrations', 'events'));
    }
}

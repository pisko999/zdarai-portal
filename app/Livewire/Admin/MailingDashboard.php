<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Models\Event;
use App\Models\Registration;
use Illuminate\View\View;
use Livewire\Component;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MailingDashboard extends Component
{
    public ?int $eventId = null;

    public function mount(): void
    {
        $event = Event::where('date', '>=', now()->startOfDay())->orderBy('date')->first()
            ?? Event::orderByDesc('date')->first();
        $this->eventId = $event?->id;
    }

    public function exportPollCsv(): StreamedResponse
    {
        $registrations = $this->registrationsQuery()->get();

        $filename = 'anketa_vecere_' . date('Y-m-d') . '.csv';

        return response()->streamDownload(function () use ($registrations) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF)); // BOM pro Excel

            fputcsv($handle, [
                'Jméno', 'E-mail', 'Odpověď', 'Vybrané jídlo', 'Poznámka',
                'Anketa otevřena', 'Odpovězeno',
            ], ';');

            foreach ($registrations as $reg) {
                $poll = $reg->dinnerPoll;
                fputcsv($handle, [
                    $reg->name,
                    $reg->email,
                    $poll?->responded_at ? $poll->attendanceLabel() : 'Neodpověděl(a)',
                    $poll?->food_choice ?? '',
                    $poll?->note ?? '',
                    $poll?->opened_at?->format('j. n. Y H:i') ?? '',
                    $poll?->responded_at?->format('j. n. Y H:i') ?? '',
                ], ';');
            }
            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function registrationsQuery()
    {
        return Registration::query()
            ->when($this->eventId, fn ($q) => $q->where('event_id', $this->eventId))
            ->whereNotIn('payment_status', ['cancelled'])
            ->with(['dinnerPoll', 'emailOpens'])
            ->orderBy('name');
    }

    public function render(): View
    {
        $registrations = $this->registrationsQuery()->get();
        $events = Event::orderByDesc('date')->get(['id', 'title', 'date']);

        $stats = [
            'total' => $registrations->count(),
            'emailOpened' => $registrations->filter(fn ($r) => $r->emailOpens->isNotEmpty())->count(),
            'pollOpened' => $registrations->filter(fn ($r) => $r->dinnerPoll?->opened_at)->count(),
            'pollResponded' => $registrations->filter(fn ($r) => $r->dinnerPoll?->responded_at)->count(),
            'dinner' => $registrations->filter(fn ($r) => $r->dinnerPoll?->responded_at && $r->dinnerPoll->attendance === 'dinner')->count(),
            'beer' => $registrations->filter(fn ($r) => $r->dinnerPoll?->responded_at && $r->dinnerPoll->attendance === 'beer')->count(),
            'none' => $registrations->filter(fn ($r) => $r->dinnerPoll?->responded_at && $r->dinnerPoll->attendance === 'none')->count(),
        ];

        return view('livewire.admin.mailing-dashboard', compact('registrations', 'events', 'stats'));
    }
}

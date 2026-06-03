<div>
    {{-- Filtr události --}}
    <div class="flex flex-wrap items-center gap-3 mb-6">
        <select wire:model.live="eventId" class="bg-gray-900 border border-green-900/40 rounded px-3 py-2 text-sm text-green-300">
            @foreach($events as $event)
                <option value="{{ $event->id }}">{{ $event->title }} ({{ \Illuminate\Support\Carbon::parse($event->date)->format('j. n. Y') }})</option>
            @endforeach
        </select>

        <button wire:click="exportPollCsv" class="ml-auto border border-green-700 text-green-400 hover:bg-green-950 px-4 py-2 rounded text-sm transition">
            ⬇ Export ankety (CSV)
        </button>
    </div>

    {{-- Statistiky --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <div class="border border-green-900/40 rounded-lg p-5 bg-gray-900/30">
            <div class="text-green-700 text-xs uppercase font-bold mb-1">Registrací</div>
            <div class="text-3xl font-bold text-green-400">{{ $stats['total'] }}</div>
        </div>
        <div class="border border-green-900/40 rounded-lg p-5 bg-gray-900/30">
            <div class="text-green-700 text-xs uppercase font-bold mb-1">Otevřelo e-mail</div>
            <div class="text-3xl font-bold text-green-400">{{ $stats['emailOpened'] }}<span class="text-sm text-green-800"> / {{ $stats['total'] }}</span></div>
        </div>
        <div class="border border-green-900/40 rounded-lg p-5 bg-gray-900/30">
            <div class="text-green-700 text-xs uppercase font-bold mb-1">Otevřelo anketu</div>
            <div class="text-3xl font-bold text-green-400">{{ $stats['pollOpened'] }}<span class="text-sm text-green-800"> / {{ $stats['total'] }}</span></div>
        </div>
        <div class="border border-green-900/40 rounded-lg p-5 bg-gray-900/30">
            <div class="text-green-700 text-xs uppercase font-bold mb-1">Odpovědělo</div>
            <div class="text-3xl font-bold text-green-400">{{ $stats['pollResponded'] }}<span class="text-sm text-green-800"> / {{ $stats['total'] }}</span></div>
        </div>
    </div>

    {{-- Souhrn ankety --}}
    <h2 class="text-sm font-bold text-green-600 uppercase mb-3">Anketa — souhrn pro rezervaci</h2>
    <div class="grid grid-cols-3 gap-4 mb-8">
        <div class="border border-green-900/40 rounded-lg p-5 bg-gray-900/30 text-center">
            <div class="text-3xl mb-1">🍽️</div>
            <div class="text-2xl font-bold text-green-400">{{ $stats['dinner'] }}</div>
            <div class="text-xs text-green-700 mt-1">na večeři</div>
        </div>
        <div class="border border-green-900/40 rounded-lg p-5 bg-gray-900/30 text-center">
            <div class="text-3xl mb-1">🍺</div>
            <div class="text-2xl font-bold text-green-400">{{ $stats['beer'] }}</div>
            <div class="text-xs text-green-700 mt-1">jen na pivo</div>
        </div>
        <div class="border border-green-900/40 rounded-lg p-5 bg-gray-900/30 text-center">
            <div class="text-3xl mb-1">🚫</div>
            <div class="text-2xl font-bold text-gray-500">{{ $stats['none'] }}</div>
            <div class="text-xs text-green-700 mt-1">bez zájmu</div>
        </div>
    </div>

    {{-- Tabulka účastníků --}}
    <h2 class="text-sm font-bold text-green-600 uppercase mb-3">Přehled účastníků</h2>
    <div class="border border-green-900/40 rounded-lg overflow-hidden overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-900 text-green-700 text-xs uppercase">
                    <th class="text-left px-4 py-3">Jméno</th>
                    <th class="text-left px-4 py-3">E-mail</th>
                    <th class="text-center px-4 py-3">E-mail otevřen</th>
                    <th class="text-center px-4 py-3">Anketa</th>
                    <th class="text-left px-4 py-3">Odpověď</th>
                    <th class="text-left px-4 py-3">Jídlo / poznámka</th>
                </tr>
            </thead>
            <tbody>
                @forelse($registrations as $reg)
                    @php $open = $reg->emailOpens->first(); $poll = $reg->dinnerPoll; @endphp
                    <tr class="border-t border-green-900/20 hover:bg-gray-900/30 transition">
                        <td class="px-4 py-3 text-white">{{ $reg->name }}</td>
                        <td class="px-4 py-3 text-gray-400">{{ $reg->email }}</td>
                        <td class="px-4 py-3 text-center">
                            @if($open)
                                <span class="text-green-400" title="{{ $open->last_opened_at?->format('j. n. Y H:i') }} · {{ $open->open_count }}×">✓</span>
                            @else
                                <span class="text-gray-700">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($poll?->responded_at)
                                <span class="text-green-400" title="Odpovězeno {{ $poll->responded_at->format('j. n. Y H:i') }}">✓ odpověď</span>
                            @elseif($poll?->opened_at)
                                <span class="text-yellow-600" title="Otevřeno {{ $poll->opened_at->format('j. n. Y H:i') }}">otevřel</span>
                            @else
                                <span class="text-gray-700">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-gray-300">{{ $poll?->responded_at ? $poll->attendanceLabel() : '' }}</td>
                        <td class="px-4 py-3 text-gray-500">
                            {{ $poll?->food_choice }}{{ $poll?->food_choice && $poll?->note ? ' · ' : '' }}{{ $poll?->note }}
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-8 text-center text-green-800">Žádné registrace.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

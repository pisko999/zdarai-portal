<div>
    @if(session('success'))
        <div class="mb-4 px-4 py-3 bg-green-950/50 border border-green-800 rounded text-green-400 text-sm">{{ session('success') }}</div>
    @endif

    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-xl font-bold text-white">Registrace</h2>
            <p class="text-green-700 text-xs mt-0.5">{{ $registrations->count() }} záznamů</p>
        </div>
        <button wire:click="exportCsv"
            class="border border-green-800 text-green-600 hover:text-green-400 hover:border-green-700 font-bold px-4 py-2 rounded text-sm transition">
            ⬇ Export CSV
        </button>
    </div>

    {{-- Filters --}}
    <div class="flex flex-wrap gap-3 mb-4">
        <input type="text" wire:model.live="search" placeholder="Hledat jméno / email..."
            class="bg-gray-950 border border-green-900/50 rounded px-3 py-2 text-green-300 text-sm w-56 focus:border-green-700 focus:outline-none">
        <select wire:model.live="filterEvent"
            class="bg-gray-950 border border-green-900/50 rounded px-3 py-2 text-green-300 text-sm focus:border-green-700 focus:outline-none">
            <option value="">Všechny události</option>
            @foreach($events as $event)
                <option value="{{ $event->id }}">{{ $event->title }}</option>
            @endforeach
        </select>
        <select wire:model.live="filterStatus"
            class="bg-gray-950 border border-green-900/50 rounded px-3 py-2 text-green-300 text-sm focus:border-green-700 focus:outline-none">
            <option value="">Všechny statusy</option>
            <option value="waitlist">Čekací listina</option>
            <option value="free">Zdarma</option>
            <option value="pending">Čekající platba</option>
            <option value="paid">Zaplaceno</option>
            <option value="confirmed">Potvrzeno</option>
            <option value="cancelled">Zrušeno</option>
        </select>
    </div>

    <div class="border border-green-900/40 rounded-lg overflow-hidden overflow-x-auto">
        <table class="w-full text-sm min-w-[800px]">
            <thead class="bg-gray-900 text-green-700 text-xs uppercase">
                <tr>
                    <th class="text-left px-4 py-3">Jméno</th>
                    <th class="text-left px-4 py-3">E-mail</th>
                    <th class="text-left px-4 py-3">Organizace</th>
                    <th class="text-left px-4 py-3">Úroveň AI</th>
                    <th class="text-left px-4 py-3">Událost</th>
                    <th class="text-left px-4 py-3">Status platby</th>
                    <th class="text-left px-4 py-3">Datum</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-green-900/20">
                @forelse($registrations as $reg)
                    <tr class="hover:bg-gray-900/30 transition">
                        <td class="px-4 py-3 text-white">
                            {{ $reg->name }}
                            @if($reg->email_opt_out)
                                <span class="ml-1 text-xs text-gray-700" title="Odhlášen z emailů">✉</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-green-600 text-xs">{{ $reg->email }}</td>
                        <td class="px-4 py-3 text-gray-500 text-xs">{{ $reg->organization ?? '—' }}</td>
                        <td class="px-4 py-3">
                            @if($reg->ai_level)
                                <span class="mono text-xs px-2 py-0.5 rounded border
                                    {{ match($reg->ai_level) {
                                        'beginner' => 'border-blue-900 text-blue-400',
                                        'intermediate' => 'border-green-900 text-green-400',
                                        'advanced' => 'border-yellow-900 text-yellow-400',
                                        'expert' => 'border-purple-900 text-purple-400',
                                        default => 'border-gray-800 text-gray-500',
                                    } }}">
                                    {{ match($reg->ai_level) {
                                        'beginner' => 'Začátečník',
                                        'intermediate' => 'Pokročilý',
                                        'advanced' => 'Expert',
                                        'expert' => 'Profesionál',
                                        default => $reg->ai_level,
                                    } }}
                                </span>
                            @else
                                <span class="text-gray-700 text-xs">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-green-700 text-xs">{{ $reg->event?->title ?? '—' }}</td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                <select wire:change="updatePaymentStatus({{ $reg->id }}, $event.target.value)"
                                    class="bg-gray-900 border border-green-900/40 rounded px-2 py-0.5 text-xs focus:outline-none
                                        {{ match($reg->payment_status) {
                                            'confirmed' => 'text-green-300',
                                            'paid'      => 'text-green-400',
                                            'pending'   => 'text-yellow-500',
                                            'cancelled' => 'text-red-600',
                                            'waitlist'  => 'text-orange-400',
                                            default     => 'text-green-700',
                                        } }}">
                                    <option value="waitlist"   {{ $reg->payment_status === 'waitlist'   ? 'selected' : '' }}>📋 Čekací listina</option>
                                    <option value="free"       {{ $reg->payment_status === 'free'       ? 'selected' : '' }}>Zdarma</option>
                                    <option value="pending"    {{ $reg->payment_status === 'pending'    ? 'selected' : '' }}>Čekající platba</option>
                                    <option value="paid"       {{ $reg->payment_status === 'paid'       ? 'selected' : '' }}>Zaplaceno</option>
                                    <option value="confirmed"  {{ $reg->payment_status === 'confirmed'  ? 'selected' : '' }}>✓ Potvrzeno</option>
                                    <option value="cancelled"  {{ $reg->payment_status === 'cancelled'  ? 'selected' : '' }}>Zrušeno</option>
                                </select>
                                @if($reg->payment_status === 'waitlist')
                                    <button wire:click="confirmRegistration({{ $reg->id }})"
                                        class="text-xs bg-green-900/40 hover:bg-green-800/60 text-green-400 px-2 py-0.5 rounded border border-green-800 transition whitespace-nowrap"
                                        title="Potvrdit — přidat na seznam účastníků">
                                        ✓ Potvrdit
                                    </button>
                                @endif
                            </div>
                        </td>
                        <td class="px-4 py-3 text-green-800 text-xs">{{ $reg->created_at->format('j. n. Y H:i') }}</td>
                        <td class="px-4 py-3 text-right">
                            @if($confirmDelete === $reg->id)
                                <button wire:click="deleteRegistration"
                                    class="text-xs text-red-500 hover:text-red-400 transition">Potvrdit</button>
                                <button wire:click="$set('confirmDelete', null)"
                                    class="text-xs text-green-900 hover:text-green-700 transition ml-2">Zrušit</button>
                            @else
                                <button wire:click="confirmDeleteRegistration({{ $reg->id }})"
                                    class="text-xs text-red-800 hover:text-red-600 transition">Smazat</button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-green-800">Žádné registrace.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

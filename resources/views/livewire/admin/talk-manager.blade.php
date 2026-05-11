<div>
    @if(session('success'))
        <div class="mb-4 px-4 py-3 bg-green-950/50 border border-green-800 rounded text-green-400 text-sm">{{ session('success') }}</div>
    @endif

    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-xl font-bold text-white">Přednášky</h2>
            <p class="text-green-700 text-xs mt-0.5">{{ $talks->count() }} záznamů</p>
        </div>
        <button wire:click="openCreate"
            class="bg-green-600 hover:bg-green-500 text-gray-950 font-bold px-4 py-2 rounded text-sm transition">
            + Nová přednáška
        </button>
    </div>

    {{-- Filter by event --}}
    <div class="mb-4">
        <select wire:model.live="filterEvent"
            class="bg-gray-950 border border-green-900/50 rounded px-3 py-2 text-green-300 text-sm focus:border-green-700 focus:outline-none">
            <option value="">Všechny události</option>
            @foreach($events as $event)
                <option value="{{ $event->id }}">{{ $event->title }}</option>
            @endforeach
        </select>
    </div>

    <div class="border border-green-900/40 rounded-lg overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-900 text-green-700 text-xs uppercase">
                <tr>
                    <th class="text-left px-4 py-3">Přednáška</th>
                    <th class="text-left px-4 py-3">Událost</th>
                    <th class="text-left px-4 py-3">Přednášející</th>
                    <th class="text-left px-4 py-3">Délka</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-green-900/20">
                @forelse($talks as $talk)
                    <tr class="hover:bg-gray-900/30 transition">
                        <td class="px-4 py-3 text-white font-medium">{{ $talk->title }}</td>
                        <td class="px-4 py-3 text-green-700 text-xs">{{ $talk->event?->title ?? '—' }}</td>
                        <td class="px-4 py-3 text-green-700">{{ $talk->speaker?->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-green-600">{{ $talk->duration_minutes ? $talk->duration_minutes . ' min' : '—' }}</td>
                        <td class="px-4 py-3 text-right space-x-2">
                            <button wire:click="openEdit({{ $talk->id }})"
                                class="text-xs text-green-700 hover:text-green-400 transition">Upravit</button>
                            @if($confirmDelete === $talk->id)
                                <button wire:click="deleteTalk"
                                    class="text-xs text-red-500 hover:text-red-400 transition">Potvrdit</button>
                                <button wire:click="$set('confirmDelete', null)"
                                    class="text-xs text-green-900 hover:text-green-700 transition">Zrušit</button>
                            @else
                                <button wire:click="confirmDeleteTalk({{ $talk->id }})"
                                    class="text-xs text-red-800 hover:text-red-600 transition">Smazat</button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-8 text-center text-green-800">Žádné přednášky.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Modal --}}
    @if($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/70">
            <div class="bg-gray-950 border border-green-900/40 rounded-xl shadow-2xl w-full max-w-xl mx-4 max-h-[90vh] overflow-y-auto">
                <div class="p-6 border-b border-green-900/30 flex items-center justify-between">
                    <h3 class="text-white font-bold">{{ $editingId ? 'Upravit přednášku' : 'Nová přednáška' }}</h3>
                    <button wire:click="$set('showModal', false)" class="text-green-800 hover:text-green-600 text-xl leading-none">&times;</button>
                </div>
                <form wire:submit.prevent="save" class="p-6 space-y-4">
                    {{-- Language tabs --}}
                    <div class="border border-gray-800 rounded-lg overflow-hidden">
                        <div class="flex border-b border-gray-800">
                            <button type="button" wire:click="$set('langTab', 'cs')"
                                class="{{ $langTab === 'cs' ? 'bg-gray-900 text-green-400 border-b-2 border-green-400' : 'text-gray-500 hover:text-gray-300' }} px-4 py-2 text-sm font-bold transition flex-1">
                                🇨🇿 Česky
                            </button>
                            <button type="button" wire:click="$set('langTab', 'en')"
                                class="{{ $langTab === 'en' ? 'bg-gray-900 text-green-400 border-b-2 border-green-400' : 'text-gray-500 hover:text-gray-300' }} px-4 py-2 text-sm font-bold transition flex-1">
                                🇬🇧 English
                            </button>
                        </div>
                        {{-- CS tab --}}
                        <div class="{{ $langTab === 'cs' ? '' : 'hidden' }} p-4 space-y-3 bg-gray-900/30">
                            <div>
                                <label class="block text-gray-500 text-xs font-bold uppercase mb-1">Název přednášky (CZ) *</label>
                                <input type="text" wire:model="title_cs"
                                    class="w-full bg-gray-900 border border-gray-700 rounded px-3 py-2 text-gray-100 text-sm focus:border-green-500 focus:outline-none">
                                @error('title_cs') <span class="text-red-400 text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-gray-500 text-xs font-bold uppercase mb-1">Popis (CZ)</label>
                                <textarea wire:model="description_cs" rows="3"
                                    class="w-full bg-gray-900 border border-gray-700 rounded px-3 py-2 text-gray-100 text-sm focus:border-green-500 focus:outline-none resize-none"></textarea>
                            </div>
                        </div>
                        {{-- EN tab --}}
                        <div class="{{ $langTab === 'en' ? '' : 'hidden' }} p-4 space-y-3 bg-gray-900/30">
                            <div>
                                <label class="block text-gray-500 text-xs font-bold uppercase mb-1">Talk title (EN) <span class="text-gray-700 font-normal normal-case">(optional)</span></label>
                                <input type="text" wire:model="title_en"
                                    class="w-full bg-gray-900 border border-gray-700 rounded px-3 py-2 text-gray-100 text-sm focus:border-green-500 focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-gray-500 text-xs font-bold uppercase mb-1">Description (EN)</label>
                                <textarea wire:model="description_en" rows="3"
                                    class="w-full bg-gray-900 border border-gray-700 rounded px-3 py-2 text-gray-100 text-sm focus:border-green-500 focus:outline-none resize-none"></textarea>
                            </div>
                        </div>
                    </div>
                    <div>
                        <label class="block text-green-600 text-xs font-bold uppercase mb-1">Událost *</label>
                        <select wire:model="event_id"
                            class="w-full bg-gray-900 border border-green-900/50 rounded px-3 py-2 text-green-300 text-sm focus:border-green-700 focus:outline-none">
                            <option value="">-- Vyberte událost --</option>
                            @foreach($events as $event)
                                <option value="{{ $event->id }}">{{ $event->title }}</option>
                            @endforeach
                        </select>
                        @error('event_id') <span class="text-red-400 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-green-600 text-xs font-bold uppercase mb-1">Přednášející</label>
                        <select wire:model="speaker_id"
                            class="w-full bg-gray-900 border border-green-900/50 rounded px-3 py-2 text-green-300 text-sm focus:border-green-700 focus:outline-none">
                            <option value="">-- Bez přednášejícího --</option>
                            @foreach($speakers as $speaker)
                                <option value="{{ $speaker->id }}">{{ $speaker->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-green-600 text-xs font-bold uppercase mb-1">Délka (min)</label>
                            <input type="number" wire:model="duration_minutes" min="1" max="480"
                                class="w-full bg-gray-900 border border-green-900/50 rounded px-3 py-2 text-green-300 text-sm focus:border-green-700 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-green-600 text-xs font-bold uppercase mb-1">Pořadí</label>
                            <input type="number" wire:model="sort_order" min="0"
                                class="w-full bg-gray-900 border border-green-900/50 rounded px-3 py-2 text-green-300 text-sm focus:border-green-700 focus:outline-none">
                        </div>
                    </div>
                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" wire:click="$set('showModal', false)"
                            class="px-4 py-2 text-sm text-green-700 hover:text-green-500 transition">Zrušit</button>
                        <button type="submit"
                            class="px-6 py-2 bg-green-600 hover:bg-green-500 text-gray-950 font-bold rounded text-sm transition">
                            {{ $editingId ? 'Uložit' : 'Vytvořit' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>

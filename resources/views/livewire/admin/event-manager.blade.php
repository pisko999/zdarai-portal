<div>
    {{-- Flash message --}}
    @if(session('success'))
        <div class="mb-4 px-4 py-3 bg-green-950/50 border border-green-800 rounded text-green-400 text-sm">{{ session('success') }}</div>
    @endif

    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-xl font-bold text-white">Události</h2>
            <p class="text-green-700 text-xs mt-0.5">{{ $events->count() }} záznamů</p>
        </div>
        <button wire:click="openCreate"
            class="bg-green-600 hover:bg-green-500 text-gray-950 font-bold px-4 py-2 rounded text-sm transition">
            + Nová událost
        </button>
    </div>

    {{-- Search --}}
    <div class="mb-4">
        <input type="text" wire:model.live="search" placeholder="Hledat..."
            class="bg-gray-950 border border-green-900/50 rounded px-3 py-2 text-green-300 text-sm w-64 focus:border-green-700 focus:outline-none">
    </div>

    {{-- Table --}}
    <div class="border border-green-900/40 rounded-lg overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-900 text-green-700 text-xs uppercase">
                <tr>
                    <th class="text-left px-4 py-3">Název</th>
                    <th class="text-left px-4 py-3">Datum</th>
                    <th class="text-left px-4 py-3">Status</th>
                    <th class="text-left px-4 py-3">Registrace</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-green-900/20">
                @forelse($events as $event)
                    <tr class="hover:bg-gray-900/30 transition">
                        <td class="px-4 py-3 text-white font-medium">{{ $event->title }}</td>
                        <td class="px-4 py-3 text-green-600">{{ $event->date->format('j. n. Y H:i') }}</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-0.5 rounded text-xs font-bold
                                {{ $event->status === 'published' ? 'bg-green-900/50 text-green-400' :
                                   ($event->status === 'draft' ? 'bg-gray-800 text-gray-400' : 'bg-gray-900 text-gray-600') }}">
                                {{ match($event->status) {
                                    'published' => 'Publikováno',
                                    'draft' => 'Koncept',
                                    'archived' => 'Archivováno',
                                    default => $event->status,
                                } }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-green-600">
                            {{ $event->active_registrations_count }} / {{ $event->capacity }}
                        </td>
                        <td class="px-4 py-3 text-right space-x-2">
                            <button wire:click="openEdit({{ $event->id }})"
                                class="text-xs text-green-700 hover:text-green-400 transition">Upravit</button>
                            @if($confirmDelete === $event->id)
                                <button wire:click="deleteEvent"
                                    class="text-xs text-red-500 hover:text-red-400 transition">Potvrdit smazání</button>
                                <button wire:click="$set('confirmDelete', null)"
                                    class="text-xs text-green-900 hover:text-green-700 transition">Zrušit</button>
                            @else
                                <button wire:click="confirmDeleteEvent({{ $event->id }})"
                                    class="text-xs text-red-800 hover:text-red-600 transition">Smazat</button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-8 text-center text-green-800">Žádné události.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Modal --}}
    @if($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/70">
            <div class="bg-gray-950 border border-green-900/40 rounded-xl shadow-2xl w-full max-w-2xl mx-4 max-h-[90vh] overflow-y-auto">
                <div class="p-6 border-b border-green-900/30 flex items-center justify-between">
                    <h3 class="text-white font-bold">{{ $editingId ? 'Upravit událost' : 'Nová událost' }}</h3>
                    <button wire:click="$set('showModal', false)" class="text-green-800 hover:text-green-600 transition text-xl leading-none">&times;</button>
                </div>
                <form wire:submit.prevent="save" class="p-6 space-y-4">
                    {{-- Language tabs --}}
                    <div class="border border-gray-800 rounded-lg overflow-hidden">
                        {{-- Tab headers --}}
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
                                <label class="block text-gray-500 text-xs font-bold uppercase mb-1">Název (CZ) *</label>
                                <input type="text" wire:model="title_cs"
                                    class="w-full bg-gray-900 border border-gray-700 rounded px-3 py-2 text-gray-100 text-sm focus:border-green-500 focus:outline-none">
                                @error('title_cs') <span class="text-red-400 text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-gray-500 text-xs font-bold uppercase mb-1">Popis (CZ)</label>
                                <textarea wire:model="description_cs" rows="4"
                                    class="w-full bg-gray-900 border border-gray-700 rounded px-3 py-2 text-gray-100 text-sm focus:border-green-500 focus:outline-none resize-none"></textarea>
                            </div>
                        </div>
                        {{-- EN tab --}}
                        <div class="{{ $langTab === 'en' ? '' : 'hidden' }} p-4 space-y-3 bg-gray-900/30">
                            <div>
                                <label class="block text-gray-500 text-xs font-bold uppercase mb-1">Title (EN) <span class="text-gray-700 font-normal normal-case">(optional)</span></label>
                                <input type="text" wire:model="title_en"
                                    class="w-full bg-gray-900 border border-gray-700 rounded px-3 py-2 text-gray-100 text-sm focus:border-green-500 focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-gray-500 text-xs font-bold uppercase mb-1">Description (EN)</label>
                                <textarea wire:model="description_en" rows="4"
                                    class="w-full bg-gray-900 border border-gray-700 rounded px-3 py-2 text-gray-100 text-sm focus:border-green-500 focus:outline-none resize-none"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-green-600 text-xs font-bold uppercase mb-1">Datum a čas *</label>
                            <input type="datetime-local" wire:model="date"
                                class="w-full bg-gray-900 border border-green-900/50 rounded px-3 py-2 text-green-300 text-sm focus:border-green-700 focus:outline-none">
                            @error('date') <span class="text-red-400 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-green-600 text-xs font-bold uppercase mb-1">Místo konání</label>
                            <input type="text" wire:model="location"
                                class="w-full bg-gray-900 border border-green-900/50 rounded px-3 py-2 text-green-300 text-sm focus:border-green-700 focus:outline-none">
                        </div>
                    </div>
                    <div class="grid grid-cols-3 gap-4">
                        <div>
                            <label class="block text-green-600 text-xs font-bold uppercase mb-1">Kapacita *</label>
                            <input type="number" wire:model="capacity" min="1"
                                class="w-full bg-gray-900 border border-green-900/50 rounded px-3 py-2 text-green-300 text-sm focus:border-green-700 focus:outline-none">
                            @error('capacity') <span class="text-red-400 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-green-600 text-xs font-bold uppercase mb-1">Cena (Kč)</label>
                            <input type="number" wire:model="price" min="0" placeholder="prázdné = zdarma"
                                class="w-full bg-gray-900 border border-green-900/50 rounded px-3 py-2 text-green-300 text-sm focus:border-green-700 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-green-600 text-xs font-bold uppercase mb-1">Status *</label>
                            <select wire:model="status"
                                class="w-full bg-gray-900 border border-green-900/50 rounded px-3 py-2 text-green-300 text-sm focus:border-green-700 focus:outline-none">
                                <option value="draft">Koncept</option>
                                <option value="published">Publikováno</option>
                                <option value="archived">Archivováno</option>
                            </select>
                        </div>
                    </div>
                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" wire:click="$set('showModal', false)"
                            class="px-4 py-2 text-sm text-green-700 hover:text-green-500 transition">Zrušit</button>
                        <button type="submit"
                            class="px-6 py-2 bg-green-600 hover:bg-green-500 text-gray-950 font-bold rounded text-sm transition">
                            {{ $editingId ? 'Uložit změny' : 'Vytvořit' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>

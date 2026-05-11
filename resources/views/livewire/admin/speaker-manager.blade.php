<div>
    @if(session('success'))
        <div class="mb-4 px-4 py-3 bg-green-950/50 border border-green-800 rounded text-green-400 text-sm">{{ session('success') }}</div>
    @endif

    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-xl font-bold text-white">Přednášející</h2>
            <p class="text-green-700 text-xs mt-0.5">{{ $speakers->count() }} záznamů</p>
        </div>
        <button wire:click="openCreate"
            class="bg-green-600 hover:bg-green-500 text-gray-950 font-bold px-4 py-2 rounded text-sm transition">
            + Nový přednášející
        </button>
    </div>

    <div class="border border-green-900/40 rounded-lg overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-900 text-green-700 text-xs uppercase">
                <tr>
                    <th class="text-left px-4 py-3">Jméno</th>
                    <th class="text-left px-4 py-3">Bio</th>
                    <th class="text-left px-4 py-3">Přednášky</th>
                    <th class="text-left px-4 py-3">Linky</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-green-900/20">
                @forelse($speakers as $speaker)
                    <tr class="hover:bg-gray-900/30 transition">
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                @if($speaker->avatar)
                                    <img src="{{ Storage::url($speaker->avatar) }}" alt="{{ $speaker->name }}"
                                        class="w-8 h-8 rounded-full border border-green-900 object-cover">
                                @else
                                    <div class="w-8 h-8 rounded-full border border-green-900 bg-green-950 flex items-center justify-center text-green-600 text-xs font-bold">
                                        {{ mb_substr($speaker->name, 0, 1) }}
                                    </div>
                                @endif
                                <span class="text-white font-medium">{{ $speaker->name }}</span>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-green-700 text-xs max-w-xs truncate">{{ Str::limit($speaker->bio, 60) }}</td>
                        <td class="px-4 py-3 text-green-600">{{ $speaker->talks_count }}</td>
                        <td class="px-4 py-3 space-x-2">
                            @if($speaker->github_url)
                                <a href="{{ $speaker->github_url }}" target="_blank" rel="noopener noreferrer" class="text-xs text-green-800 hover:text-green-600 transition">GitHub</a>
                            @endif
                            @if($speaker->linkedin_url)
                                <a href="{{ $speaker->linkedin_url }}" target="_blank" rel="noopener noreferrer" class="text-xs text-green-800 hover:text-green-600 transition">LinkedIn</a>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right space-x-2">
                            <button wire:click="openEdit({{ $speaker->id }})"
                                class="text-xs text-green-700 hover:text-green-400 transition">Upravit</button>
                            @if($confirmDelete === $speaker->id)
                                <button wire:click="deleteSpeaker"
                                    class="text-xs text-red-500 hover:text-red-400 transition">Potvrdit</button>
                                <button wire:click="$set('confirmDelete', null)"
                                    class="text-xs text-green-900 hover:text-green-700 transition">Zrušit</button>
                            @else
                                <button wire:click="confirmDeleteSpeaker({{ $speaker->id }})"
                                    class="text-xs text-red-800 hover:text-red-600 transition">Smazat</button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-8 text-center text-green-800">Žádní přednášející.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Modal --}}
    @if($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/70">
            <div class="bg-gray-950 border border-green-900/40 rounded-xl shadow-2xl w-full max-w-lg mx-4 max-h-[90vh] overflow-y-auto">
                <div class="p-6 border-b border-green-900/30 flex items-center justify-between">
                    <h3 class="text-white font-bold">{{ $editingId ? 'Upravit přednášejícího' : 'Nový přednášející' }}</h3>
                    <button wire:click="$set('showModal', false)" class="text-green-800 hover:text-green-600 text-xl leading-none">&times;</button>
                </div>
                <form wire:submit.prevent="save" class="p-6 space-y-4">
                    <div>
                        <label class="block text-green-600 text-xs font-bold uppercase mb-1">Jméno *</label>
                        <input type="text" wire:model="name"
                            class="w-full bg-gray-900 border border-green-900/50 rounded px-3 py-2 text-green-300 text-sm focus:border-green-700 focus:outline-none">
                        @error('name') <span class="text-red-400 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-green-600 text-xs font-bold uppercase mb-1">Bio</label>
                        <textarea wire:model="bio" rows="4"
                            class="w-full bg-gray-900 border border-green-900/50 rounded px-3 py-2 text-green-300 text-sm focus:border-green-700 focus:outline-none resize-none"></textarea>
                    </div>
                    <div>
                        <label class="block text-green-600 text-xs font-bold uppercase mb-1">GitHub URL</label>
                        <input type="url" wire:model="github_url" placeholder="https://github.com/..."
                            class="w-full bg-gray-900 border border-green-900/50 rounded px-3 py-2 text-green-300 text-sm focus:border-green-700 focus:outline-none">
                        @error('github_url') <span class="text-red-400 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-green-600 text-xs font-bold uppercase mb-1">LinkedIn URL</label>
                        <input type="url" wire:model="linkedin_url" placeholder="https://linkedin.com/in/..."
                            class="w-full bg-gray-900 border border-green-900/50 rounded px-3 py-2 text-green-300 text-sm focus:border-green-700 focus:outline-none">
                        @error('linkedin_url') <span class="text-red-400 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-green-600 text-xs font-bold uppercase mb-1">Foto <span class="text-green-900 font-normal">(.jpg, .png, .webp, max 2MB)</span></label>
                        <input type="file" wire:model="avatar" accept="image/jpeg,image/png,image/webp"
                            class="text-green-700 text-sm file:mr-3 file:bg-green-950 file:border file:border-green-800 file:text-green-400 file:rounded file:px-2 file:py-1 file:text-xs file:cursor-pointer">
                        @error('avatar') <span class="text-red-400 text-xs">{{ $message }}</span> @enderror
                        @if($avatar)
                            <img src="{{ $avatar->temporaryUrl() }}" alt="Preview" class="mt-2 w-16 h-16 rounded-full object-cover border border-green-900">
                        @endif
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

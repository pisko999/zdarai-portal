<div>
    {{-- Vyhledávání --}}
    <div class="flex gap-3 mb-6">
        <input type="text" wire:model.live.debounce.300ms="search"
            placeholder="Hledat podle jména nebo e-mailu..."
            class="flex-1 bg-gray-950 border border-gray-800 rounded-lg px-4 py-2.5 text-gray-100 placeholder-gray-700 focus:border-green-500 focus:outline-none text-sm mono">
        <div class="text-gray-600 text-xs self-center">{{ $users->total() }} uživatelů</div>
    </div>

    {{-- Tabulka --}}
    <div class="border border-gray-800 rounded-lg overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-900/60 border-b border-gray-800">
                <tr>
                    <th class="text-left px-4 py-3 text-gray-500 font-bold uppercase text-xs mono">Jméno / E-mail</th>
                    <th class="text-left px-4 py-3 text-gray-500 font-bold uppercase text-xs mono hidden md:table-cell">Registrace</th>
                    <th class="text-left px-4 py-3 text-gray-500 font-bold uppercase text-xs mono hidden md:table-cell">Datum registrace</th>
                    <th class="text-center px-4 py-3 text-gray-500 font-bold uppercase text-xs mono">Admin</th>
                    <th class="text-right px-4 py-3 text-gray-500 font-bold uppercase text-xs mono">Akce</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-800/60">
                @forelse($users as $user)
                    <tr class="hover:bg-gray-900/30 transition {{ $confirmDelete === $user->id ? 'bg-red-950/20' : '' }}">
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                <div class="w-7 h-7 rounded-full bg-gray-800 flex items-center justify-center flex-shrink-0">
                                    <span class="text-gray-400 text-xs font-bold">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                                </div>
                                <div>
                                    <div class="text-white text-sm font-medium">
                                        {{ $user->name }}
                                        @if($user->id === auth()->id())
                                            <span class="text-xs text-green-700 ml-1">(vy)</span>
                                        @endif
                                    </div>
                                    <div class="text-gray-600 text-xs mono">{{ $user->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3 hidden md:table-cell">
                            <span class="text-gray-400 text-sm">{{ $user->registrations_count }}</span>
                        </td>
                        <td class="px-4 py-3 hidden md:table-cell">
                            <span class="text-gray-600 text-xs">{{ $user->created_at->format('j. n. Y') }}</span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($user->id === auth()->id())
                                {{-- Sebe nelze odebrat z adminů --}}
                                <span class="inline-block w-5 h-5 rounded bg-green-400 text-gray-950 text-xs font-black flex items-center justify-center">✓</span>
                            @else
                                <button wire:click="toggleAdmin({{ $user->id }})"
                                    class="w-9 h-5 rounded-full transition relative {{ $user->is_admin ? 'bg-green-500' : 'bg-gray-700' }}"
                                    title="{{ $user->is_admin ? 'Odebrat admin práva' : 'Udělit admin práva' }}">
                                    <span class="absolute top-0.5 w-4 h-4 rounded-full bg-white transition-all {{ $user->is_admin ? 'left-4' : 'left-0.5' }}"></span>
                                </button>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            @if($confirmDelete === $user->id)
                                <div class="flex items-center justify-end gap-2">
                                    <span class="text-red-400 text-xs">Smazat?</span>
                                    <button wire:click="delete({{ $user->id }})"
                                        class="text-xs bg-red-900/50 hover:bg-red-800 text-red-300 px-2 py-1 rounded transition">Ano</button>
                                    <button wire:click="cancelDelete"
                                        class="text-xs text-gray-500 hover:text-gray-300 px-2 py-1 rounded border border-gray-700 transition">Ne</button>
                                </div>
                            @else
                                @if($user->id !== auth()->id())
                                    <button wire:click="confirmDelete({{ $user->id }})"
                                        class="text-gray-700 hover:text-red-500 transition text-xs mono">smazat</button>
                                @endif
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-10 text-center text-gray-700">
                            Žádní uživatelé nenalezeni.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($users->hasPages())
        <div class="mt-4">
            {{ $users->links() }}
        </div>
    @endif
</div>

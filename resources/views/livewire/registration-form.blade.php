<div>
    @if($success)
        <div class="border border-green-700 bg-green-950/50 rounded-xl p-8 text-center">
            <div class="text-4xl mb-4">✅</div>
            <h3 class="text-white font-bold text-xl mb-2">Registrace potvrzena!</h3>
            <p class="text-green-600 text-sm">Potvrzovací e-mail byl odeslán. Těšíme se na vás!</p>
        </div>
    @else
        @if($errorMessage)
            <div class="border border-red-800 bg-red-950/30 rounded-lg px-4 py-3 mb-4 text-red-400 text-sm">
                {{ $errorMessage }}
            </div>
        @endif

        <form wire:submit.prevent="register" class="border border-green-900/40 rounded-xl p-6 bg-gray-900/20 space-y-4">
            <div>
                <label class="block text-green-600 text-xs font-bold uppercase mb-1" for="name">Jméno a příjmení *</label>
                <input id="name" type="text" wire:model="name"
                    class="w-full bg-gray-950 border border-green-900/50 rounded-lg px-4 py-3 text-green-300 placeholder-green-900 focus:border-green-700 focus:outline-none focus:ring-1 focus:ring-green-800 transition text-sm"
                    placeholder="Jan Novák" autocomplete="name">
                @error('name') <span class="text-red-400 text-xs mt-1 block">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-green-600 text-xs font-bold uppercase mb-1" for="email">E-mail *</label>
                <input id="email" type="email" wire:model="email"
                    class="w-full bg-gray-950 border border-green-900/50 rounded-lg px-4 py-3 text-green-300 placeholder-green-900 focus:border-green-700 focus:outline-none focus:ring-1 focus:ring-green-800 transition text-sm"
                    placeholder="jan@example.com" autocomplete="email">
                @error('email') <span class="text-red-400 text-xs mt-1 block">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-green-600 text-xs font-bold uppercase mb-1" for="dietary_notes">Dietní omezení <span class="text-green-900 font-normal normal-case">(nepovinné)</span></label>
                <input id="dietary_notes" type="text" wire:model="dietary_notes"
                    class="w-full bg-gray-950 border border-green-900/50 rounded-lg px-4 py-3 text-green-300 placeholder-green-900 focus:border-green-700 focus:outline-none focus:ring-1 focus:ring-green-800 transition text-sm"
                    placeholder="vegetarián, alergie na ořechy, ...">
                @error('dietary_notes') <span class="text-red-400 text-xs mt-1 block">{{ $message }}</span> @enderror
            </div>

            <div class="flex items-start gap-3">
                <input id="email_opt_in" type="checkbox" wire:model="email_opt_in"
                    class="mt-0.5 accent-green-500" checked>
                <label for="email_opt_in" class="text-green-700 text-xs leading-relaxed">
                    Souhlasím se zasíláním emailových připomínek o akci (lze kdykoli odhlásit)
                </label>
            </div>

            <button type="submit"
                wire:loading.attr="disabled"
                class="w-full bg-green-600 hover:bg-green-500 disabled:opacity-50 text-gray-950 font-bold py-3 px-6 rounded-lg transition text-sm">
                <span wire:loading.remove>Registrovat se →</span>
                <span wire:loading>Odesílám...</span>
            </button>

            <p class="text-green-900 text-xs text-center">
                Vaše data jsou zpracována pouze pro účely organizace akce.
            </p>
        </form>
    @endif
</div>

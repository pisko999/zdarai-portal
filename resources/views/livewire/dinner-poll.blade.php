<div class="max-w-lg mx-auto px-4 py-16">
    <a href="/" class="text-gray-500 hover:text-green-400 text-sm mb-6 inline-block transition">← zpět na hlavní stránku</a>

    <h1 class="text-white text-2xl font-bold mb-2">🍽️ Posezení po akci</h1>
    <p class="text-green-700 text-sm mb-8">
        {{ $registration->event->title }} · {{ $registration->event->date->format('j. n. Y') }}
    </p>

    @if($submitted)
        <div class="border border-green-700 rounded-lg p-6 bg-green-950/30 text-center">
            <div class="text-4xl mb-4">✓</div>
            <h2 class="text-white text-lg font-bold mb-2">Děkujeme, {{ $registration->name }}!</h2>
            <p class="text-green-600 text-sm mb-4">Tvoji odpověď máme uloženou. Můžeš ji kdykoli změnit a znovu odeslat.</p>
            <div class="text-left text-sm text-gray-400 border-t border-green-900/30 pt-4 mt-4">
                <p class="mb-1"><span class="text-green-700">Odpověď:</span> {{ $registration->dinnerPoll?->attendanceLabel() }}</p>
                @if($registration->dinnerPoll?->food_choice)
                    <p class="mb-1"><span class="text-green-700">Jídlo:</span> {{ $registration->dinnerPoll->food_choice }}</p>
                @endif
                @if($registration->dinnerPoll?->note)
                    <p><span class="text-green-700">Poznámka:</span> {{ $registration->dinnerPoll->note }}</p>
                @endif
            </div>
            <button wire:click="$set('submitted', false)" class="mt-6 text-green-600 hover:text-green-400 text-sm transition">
                Upravit odpověď →
            </button>
        </div>
    @else
        <p class="text-gray-400 text-sm leading-relaxed mb-6">
            Po programu se chystáme do <strong class="text-green-500">Restaurace Krystal</strong>
            (Dolní 183/30, Žďár nad Sázavou).
            Dej nám vědět, jestli dorazíš — ať víme, pro kolik lidí zarezervovat místo.
        </p>

        <div class="border-l-2 border-green-600 bg-green-950/20 text-green-300 text-sm rounded p-3 mb-6">
            ⏰ Restaurace potřebuje počty jídel nahlásit předem — odpověz prosím
            <strong>do středy 8.&nbsp;7.&nbsp;2026 20:00</strong>. Díky!
        </div>

        <form wire:submit="submit" class="space-y-6">
            <div>
                <label class="block text-green-600 text-xs uppercase font-bold mb-2">Dorazíš na posezení?</label>
                <div class="space-y-2">
                    @foreach(['dinner' => '🍽️ Přijdu na večeři', 'beer' => '🍺 Přijdu jen na pivo', 'none' => '🚫 Nemám zájem'] as $val => $label)
                        <label class="flex items-center gap-3 border border-gray-800 rounded-lg px-4 py-3 cursor-pointer hover:border-green-800 transition {{ $attendance === $val ? 'border-green-600 bg-green-950/30' : 'bg-gray-900/40' }}">
                            <input type="radio" wire:model.live="attendance" value="{{ $val }}" class="accent-green-600">
                            <span class="text-gray-200 text-sm">{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
                @error('attendance') <p class="text-red-500 text-xs mt-2">{{ $message }}</p> @enderror
            </div>

            @if($attendance === 'dinner')
                <div>
                    <label class="block text-green-600 text-xs uppercase font-bold mb-2">Vyber si jídlo</label>
                    <p class="text-gray-500 text-xs mb-3">Ceny jsou informativní — každý si platí sám.</p>
                    <div class="space-y-2">
                        @foreach($foodOptions as $option)
                            <label class="flex items-start gap-3 border border-gray-800 rounded-lg px-4 py-3 cursor-pointer hover:border-green-800 transition {{ $foodChoice === $option ? 'border-green-600 bg-green-950/30' : 'bg-gray-900/40' }}">
                                <input type="radio" wire:model.live="foodChoice" value="{{ $option }}" class="accent-green-600 mt-0.5 shrink-0">
                                <span class="text-gray-200 text-sm">{{ $option }}</span>
                            </label>
                        @endforeach
                        <label class="flex items-start gap-3 border border-gray-800 rounded-lg px-4 py-3 cursor-pointer hover:border-green-800 transition {{ $foodChoice === $otherChoice ? 'border-green-600 bg-green-950/30' : 'bg-gray-900/40' }}">
                            <input type="radio" wire:model.live="foodChoice" value="{{ $otherChoice }}" class="accent-green-600 mt-0.5 shrink-0">
                            <span class="text-gray-200 text-sm">{{ $otherLabel }}</span>
                        </label>
                    </div>
                    @error('foodChoice') <p class="text-red-500 text-xs mt-2">{{ $message }}</p> @enderror
                </div>
            @endif

            <div>
                <label class="block text-green-600 text-xs uppercase font-bold mb-2">Poznámka (volitelné)</label>
                <input type="text" wire:model="note" placeholder="jiné jídlo (řízek, smažák…), alergie, +1 host, …"
                       class="w-full bg-gray-900 border border-gray-800 rounded-lg px-4 py-3 text-gray-200 text-sm focus:border-green-700 focus:outline-none">
                @error('note') <p class="text-red-500 text-xs mt-2">{{ $message }}</p> @enderror
            </div>

            <button type="submit" class="w-full bg-green-600 hover:bg-green-500 text-white font-bold py-3 rounded-lg transition">
                Odeslat odpověď
            </button>
        </form>
    @endif
</div>

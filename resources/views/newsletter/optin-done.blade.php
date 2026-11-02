<x-layouts.app :title="'Hotovo — novinky ŽďárAI'">
    <div class="max-w-lg mx-auto px-4 py-24 text-center">
        <div class="text-4xl mb-6">✓</div>
        <h1 class="text-white text-2xl font-bold mb-3">Hotovo, jsi přihlášen/a</h1>
        <p class="text-green-700">Pozvánky na další akce ŽďárAI ti budeme posílat e-mailem. Díky!</p>
        <p class="text-gray-500 text-xs leading-relaxed mt-6">
            Rozmyslel/a sis to? <a href="{{ $unsubscribeUrl }}" class="text-green-600 hover:text-green-400 transition">Odhlásit odběr</a>
        </p>
        <a href="/" class="inline-block mt-6 text-green-600 hover:text-green-400 text-sm transition">{{ __('messages.event.back') }}</a>
    </div>
</x-layouts.app>

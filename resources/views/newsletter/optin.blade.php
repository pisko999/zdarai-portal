<x-layouts.app :title="'Novinky ŽďárAI — přihlášení k odběru'">
    <div class="max-w-lg mx-auto px-4 py-24 text-center">
        <div class="text-4xl mb-6 text-green-400 mono">✉</div>
        <h1 class="text-white text-2xl font-bold mb-3">Chceš dostávat novinky ŽďárAI?</h1>
        <p class="text-gray-400 text-sm leading-relaxed mb-2">
            Pozvánky na ŽďárAI (zhruba jednou měsíčně) budeme posílat na adresu
            <strong class="text-white">{{ $email }}</strong>.
        </p>
        <p class="text-gray-500 text-xs leading-relaxed mb-8">
            Souhlas můžeš kdykoli odvolat odkazem v každém e-mailu.
        </p>

        <form method="POST" action="{{ $action }}" class="space-y-4">
            @csrf
            <button type="submit"
                class="w-full bg-green-400 hover:bg-green-300 text-gray-950 font-bold py-3 px-6 rounded-lg transition text-sm">
                Ano, chci odebírat
            </button>
        </form>

        <a href="/" class="inline-block mt-6 text-green-600 hover:text-green-400 text-sm transition">Ne, děkuji &mdash; zpět na zdarai.cz</a>
    </div>
</x-layouts.app>

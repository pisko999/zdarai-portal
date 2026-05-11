<x-layouts.app 
    title="{{ __('messages.nav.about') }} — ŽďárAI"
    description="Měsíční AI přednášky pro vývojáře v Žďáru nad Sázavou. Každý první čtvrtek v MtgForFun.">
    <div class="max-w-3xl mx-auto px-4 py-16">
        <h1 class="text-3xl font-bold text-white mb-8">&gt;_ O nás</h1>
        
        <div class="space-y-8">
            <div class="border border-gray-800 rounded-xl p-6 bg-gray-900/50">
                <h2 class="text-green-400 font-bold text-lg mb-4">Co je ŽďárAI?</h2>
                <p class="text-gray-400 leading-relaxed">
                    ŽďárAI je měsíční setkání vývojářů a technicky zdatné veřejnosti, kteří mají zájem
                    o umělou inteligenci. Každý <strong class="text-green-400">první čtvrtek v měsíci</strong> se
                    setkáváme v prostředí MtgForFun ve Žďáru nad Sázavou.
                </p>
            </div>

            <div class="border border-gray-800 rounded-xl p-6 bg-gray-900/50">
                <h2 class="text-green-400 font-bold text-lg mb-4">Formát akcí</h2>
                <ul class="text-gray-400 space-y-3">
                    <li class="flex items-start gap-2">
                        <span class="text-green-500 mt-0.5">→</span>
                        <span><strong class="text-green-400">2+ přednášky</strong> na téma AI od místních i pozvaných řečníků</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="text-green-500 mt-0.5">→</span>
                        <span><strong class="text-green-400">Networking</strong> v přátelském prostředí</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="text-green-500 mt-0.5">→</span>
                        <span><strong class="text-green-400">Diskuze</strong> a sdílení zkušeností</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="text-green-500 mt-0.5">→</span>
                        <span>Většina akcí je <strong class="text-green-400">zdarma</strong></span>
                    </li>
                </ul>
            </div>

            <div class="border border-gray-800 rounded-xl p-6 bg-gray-900/50">
                <h2 class="text-green-400 font-bold text-lg mb-4">Místo konání</h2>
                <p class="text-gray-400 leading-relaxed">
                    <strong class="text-green-400">MtgForFun</strong><br>
                    Žďár nad Sázavou<br>
                    <span class="text-gray-600 text-sm">Útulný obchod s deskovými hrami a karetními hrami — ideální prostředí pro technické setkání</span>
                </p>
            </div>

            <div class="border border-gray-800 rounded-xl p-6 bg-gray-900/50">
                <h2 class="text-green-400 font-bold text-lg mb-4">Chcete přednášet?</h2>
                <p class="text-gray-400 leading-relaxed mb-4">
                    Máte zajímavé zkušenosti s AI, které byste chtěli sdílet? Rádi vás uvítáme jako přednášejícího!
                </p>
                <a href="mailto:info@mtgforfun.cz" 
                   class="inline-flex items-center gap-2 border border-gray-700 text-gray-400 hover:text-green-400 hover:border-green-500/50 px-4 py-2 rounded text-sm transition">
                    Kontaktujte nás →
                </a>
            </div>
        </div>
    </div>
</x-layouts.app>

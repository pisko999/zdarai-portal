<x-layouts.app 
    title="{{ __('messages.about.title') }} — ŽďárAI"
    description="Měsíční AI přednášky pro vývojáře v Žďáru nad Sázavou. Každý první čtvrtek v Hernici.">
    <div class="max-w-3xl mx-auto px-4 py-16">
        <h1 class="text-3xl font-bold text-white mb-8">&gt;_ {{ __('messages.about.title') }}</h1>
        
        <div class="space-y-8">
            <div class="border border-gray-800 rounded-xl p-6 bg-gray-900/50">
                <h2 class="text-green-400 font-bold text-lg mb-4">{{ __('messages.about.what_title') }}</h2>
                <p class="text-gray-400 leading-relaxed">
                    {!! __('messages.about.what_text') !!}
                </p>
            </div>

            <div class="border border-gray-800 rounded-xl p-6 bg-gray-900/50">
                <h2 class="text-green-400 font-bold text-lg mb-4">{{ __('messages.about.format_title') }}</h2>
                <ul class="text-gray-400 space-y-3">
                    <li class="flex items-start gap-2">
                        <span class="text-green-500 mt-0.5">→</span>
                        <span><strong class="text-green-400">{{ __('messages.about.format_talks') }}</strong></span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="text-green-500 mt-0.5">→</span>
                        <span><strong class="text-green-400">{{ __('messages.about.format_networking') }}</strong></span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="text-green-500 mt-0.5">→</span>
                        <span><strong class="text-green-400">{{ __('messages.about.format_discussion') }}</strong></span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="text-green-500 mt-0.5">→</span>
                        <span>{{ __('messages.about.format_free') }}</span>
                    </li>
                </ul>
            </div>

            <div class="border border-gray-800 rounded-xl p-6 bg-gray-900/50">
                <h2 class="text-green-400 font-bold text-lg mb-4">{{ __('messages.about.venue_title') }}</h2>
                <p class="text-gray-400 leading-relaxed">
                    <strong class="text-green-400">Hernice</strong><br>
                    Nádražní 1141/44, Žďár nad Sázavou<br>
                    <span class="text-gray-500 text-xs">provozováno firmou <a href="https://www.mtgforfun.cz" target="_blank" rel="noopener" class="hover:text-green-400 transition">MtgForFun</a></span><br>
                    <span class="text-gray-600 text-sm">{{ __('messages.about.venue_note') }}</span>
                </p>
            </div>

            <div class="border border-gray-800 rounded-xl p-6 bg-gray-900/50">
                <h2 class="text-green-400 font-bold text-lg mb-4">{{ __('messages.about.speak_title') }}</h2>
                <p class="text-gray-400 leading-relaxed mb-4">
                    {{ __('messages.about.speak_text') }}
                </p>
                <a href="mailto:spinarp@gmail.com" 
                   class="inline-flex items-center gap-2 border border-gray-700 text-gray-400 hover:text-green-400 hover:border-green-500/50 px-4 py-2 rounded text-sm transition">
                    {{ __('messages.about.speak_cta') }}
                </a>
            </div>
        </div>
    </div>
</x-layouts.app>

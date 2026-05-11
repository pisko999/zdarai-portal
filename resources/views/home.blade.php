<x-layouts.app>
    <x-slot name="title">ŽďárAI — AI události pro vývojáře ve Žďáru</x-slot>

    <div class="max-w-5xl mx-auto px-4 py-16">
        {{-- Hero --}}
        <div class="mb-16 text-center">
            <div class="inline-flex items-center gap-2 border border-green-900 rounded-full px-4 py-1.5 text-xs text-green-600 mb-6">
                <span class="w-1.5 h-1.5 bg-green-500 rounded-full animate-pulse"></span>
                Každý první čtvrtek v měsíci
            </div>
            <h1 class="text-4xl font-bold text-white mb-4 leading-tight">
                Umělá inteligence<br>
                <span class="text-green-400">pro vývojáře</span>
            </h1>
            <p class="text-green-700 text-lg max-w-xl mx-auto">
                Měsíční přednášky a networking v útulném prostředí MtgForFun ve Žďáru nad Sázavou.
            </p>
        </div>

        {{-- Event list --}}
        <livewire:event-list />
    </div>
</x-layouts.app>

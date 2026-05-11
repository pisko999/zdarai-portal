<x-layouts.app>
    <x-slot name="title">ŽďárAI — AI události pro vývojáře ve Žďáru</x-slot>

    {{-- Hero --}}
    <section class="relative max-w-6xl mx-auto px-6 pt-20 pb-16">
        <div class="scanline absolute inset-0 pointer-events-none"></div>
        <div class="mono text-green-400/40 text-sm mb-4 fade-in">$ ./zdarai --next-event</div>
        <h1 class="text-6xl font-black leading-none mb-4 fade-in cursor">
            AI pro<br>
            <span class="text-green-400 glow-text">programátory</span>
        </h1>
        <p class="text-gray-400 text-lg max-w-xl mb-8 fade-in">
            {{ __("messages.hero.subtitle") }}
        </p>
        <div class="flex gap-4 fade-in">
            <a href="#events" class="px-6 py-3 bg-green-400 text-gray-950 font-bold rounded hover:bg-green-300 transition glow">
                {{ __("messages.hero.cta") }} →
            </a>
            <a href="/o-nas" class="px-6 py-3 border border-green-500/30 text-green-400 mono text-sm rounded hover:border-green-400 transition">
                Co je ŽďárAI?
            </a>
        </div>
    </section>

    {{-- Divider --}}
    <div class="max-w-6xl mx-auto px-6">
        <div class="border-t border-green-500/10 mono text-xs text-green-400/30 pt-2">
            // {{ __("messages.events.upcoming") }} ──────────────────────────────────────────
        </div>
    </div>

    {{-- Event list --}}
    <section id="events" class="max-w-6xl mx-auto px-6 py-12">
        <livewire:event-list />
    </section>

    {{-- Info strip --}}
    <div class="border-y border-green-500/10 bg-green-500/[0.03] py-10 my-8">
        <div class="max-w-6xl mx-auto px-6 grid grid-cols-3 gap-8 text-center">
            <div>
                <div class="mono text-green-400 text-3xl font-bold">1×</div>
                <div class="text-gray-500 text-sm mono mt-1">měsíčně / první čtvrtek</div>
            </div>
            <div>
                <div class="mono text-green-400 text-3xl font-bold">2+</div>
                <div class="text-gray-500 text-sm mono mt-1">přednášky + networking</div>
            </div>
            <div>
                <div class="mono text-green-400 text-3xl font-bold">∞</div>
                <div class="text-gray-500 text-sm mono mt-1">káva, čaj, deskohry</div>
            </div>
        </div>
    </div>
</x-layouts.app>

<x-layouts.app 
    title="{{ __('messages.nav.archive') }} — ŽďárAI"
    description="Archiv minulých AI přednášek ve Žďáru nad Sázavou.">
    <div class="max-w-5xl mx-auto px-4 py-16">
        <h1 class="text-3xl font-bold text-white mb-2">&gt;_ Archiv</h1>
        <p class="text-green-700 mb-10">Historie proběhlých AI setkání</p>
        
        <div class="space-y-4">
            @forelse($events as $event)
                @php
                    $registered = $event->registrationCount();
                @endphp
                <div class="border border-green-900/30 rounded-lg p-5 bg-gray-900/20 opacity-80 hover:opacity-100 transition">
                    <div class="flex items-start justify-between gap-4">
                        <div class="text-center min-w-[52px]">
                            <div class="text-green-800 text-xs uppercase font-bold">{{ $event->date->format('M') }}</div>
                            <div class="text-green-700 text-3xl font-bold leading-none">{{ $event->date->format('j') }}</div>
                            <div class="text-green-900 text-xs">{{ $event->date->format('Y') }}</div>
                        </div>
                        <div class="flex-1">
                            <h2 class="text-green-300 font-bold mb-1">
                                <a href="/udalosti/{{ $event->slug }}" class="hover:text-white transition">{{ $event->title }}</a>
                            </h2>
                            @if($event->talks->isNotEmpty())
                                <div class="flex flex-wrap gap-1 mt-2">
                                    @foreach($event->talks as $talk)
                                        <span class="text-xs text-green-800 bg-green-950/30 border border-green-900/30 rounded px-2 py-0.5">
                                            {{ $talk->title }}
                                        </span>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                        <div class="text-right text-xs text-green-800">
                            {{ $registered }} účastníků
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-16 text-green-800">
                    <div class="text-4xl mb-4">📚</div>
                    <div>Archiv je zatím prázdný.</div>
                </div>
            @endforelse
        </div>
    </div>
</x-layouts.app>

<x-layouts.app
    :title="$event->title . ' — ŽďárAI'"
    :description="\Illuminate\Support\Str::limit(strip_tags($event->description ?? ''), 155)"
    ogType="event">
    <div class="max-w-4xl mx-auto px-4 py-12">
        {{-- Back link --}}
        <a href="/" class="text-green-700 hover:text-green-500 text-sm mb-6 inline-block transition">← zpět na seznam</a>

        {{-- Admin bar --}}
        @if(auth()->check() && auth()->user()->is_admin)
            <div class="mb-6 p-3 border border-green-900/20 rounded bg-green-950/20 text-xs">
                <span class="text-green-700">Admin:</span>
                <a href="/admin/registrations?event={{ $event->id }}" class="text-green-600 hover:text-green-400 ml-2 transition">
                    Správa registrací ({{ $event->registrationCount() }}) →
                </a>
                <a href="/admin/events" class="text-green-600 hover:text-green-400 ml-4 transition">
                    Upravit událost →
                </a>
            </div>
        @endif

        {{-- Event hero --}}
        <div class="border border-green-900/40 rounded-xl p-8 mb-8 bg-gray-900/30">
            <div class="flex flex-wrap items-center gap-3 mb-4 text-sm">
                <span class="text-green-600 font-bold">{{ $event->date->format('j. n. Y') }}</span>
                <span class="text-green-900">·</span>
                <span class="text-green-700">{{ $event->date->format('H:i') }}</span>
                @if($event->location)
                    <span class="text-green-900">·</span>
                    <span class="border border-green-900 px-2 py-0.5 rounded text-green-700">📍 {{ $event->location }}</span>
                @endif
                @if($event->price !== null)
                    <span class="text-yellow-500 border border-yellow-800 px-2 py-0.5 rounded font-bold">{{ number_format((float)$event->price, 0) }} Kč</span>
                @else
                    <span class="text-green-500 border border-green-800 px-2 py-0.5 rounded font-bold">ZDARMA</span>
                @endif
            </div>

            <h1 class="text-3xl font-bold text-white mb-4">{{ $event->title }}</h1>

            {{-- Capacity --}}
            @php
                $registered = $event->registrationCount();
                $isFull = $event->isFull();
                $pct = $event->capacity > 0 ? round(($registered / $event->capacity) * 100) : 0;
            @endphp
            <div class="mb-6">
                <div class="flex justify-between text-xs text-green-700 mb-1">
                    <span>Obsazenost</span>
                    <span>{{ $registered }} / {{ $event->capacity }} míst</span>
                </div>
                <div class="h-2 bg-green-950 rounded-full">
                    <div class="h-2 rounded-full transition-all {{ $pct > 80 ? 'bg-red-500' : 'bg-green-600' }}" style="width: {{ $pct }}%"></div>
                </div>
            </div>

            <div class="text-green-600 leading-relaxed whitespace-pre-wrap">{{ $event->description }}</div>
        </div>

        {{-- Talks --}}
        @if($event->talks->isNotEmpty())
            <section class="mb-10">
                <h2 class="text-lg font-bold text-green-400 mb-4 tracking-wider uppercase text-sm">Program</h2>
                <div class="space-y-4">
                    @foreach($event->talks->sortBy('sort_order') as $talk)
                        <div class="border border-green-900/40 rounded-lg p-5 bg-gray-900/20">
                            <div class="flex items-start gap-4">
                                @if($talk->speaker)
                                    <div class="flex-shrink-0">
                                        @if($talk->speaker->avatar)
                                            <img src="{{ Storage::url($talk->speaker->avatar) }}" alt="{{ $talk->speaker->name }}" class="w-12 h-12 rounded-full border border-green-900 object-cover">
                                        @else
                                            <div class="w-12 h-12 rounded-full border border-green-900 bg-green-950 flex items-center justify-center text-green-600 font-bold text-lg">
                                                {{ mb_substr($talk->speaker->name, 0, 1) }}
                                            </div>
                                        @endif
                                    </div>
                                @endif
                                <div class="flex-1">
                                    <h3 class="text-white font-bold mb-1">{{ $talk->title }}</h3>
                                    @if($talk->speaker)
                                        <div class="text-green-600 text-sm mb-2">
                                            {{ $talk->speaker->name }}
                                            @if($talk->speaker->github_url)
                                                &nbsp;<a href="{{ $talk->speaker->github_url }}" target="_blank" rel="noopener noreferrer" class="text-green-800 hover:text-green-600 transition">GitHub</a>
                                            @endif
                                            @if($talk->speaker->linkedin_url)
                                                &nbsp;<a href="{{ $talk->speaker->linkedin_url }}" target="_blank" rel="noopener noreferrer" class="text-green-800 hover:text-green-600 transition">LinkedIn</a>
                                            @endif
                                        </div>
                                        @if($talk->speaker->bio)
                                            <p class="text-green-800 text-xs mb-2 italic">{{ $talk->speaker->bio }}</p>
                                        @endif
                                    @endif
                                    @if($talk->description)
                                        <p class="text-green-700 text-sm">{{ $talk->description }}</p>
                                    @endif
                                    @if($talk->duration_minutes)
                                        <div class="mt-2 text-xs text-green-900">⏱ {{ $talk->duration_minutes }} minut</div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- Registration section --}}
        <section id="registrace" class="scroll-mt-24">
            <h2 class="text-lg font-bold text-green-400 mb-4 tracking-wider uppercase text-sm">Registrace</h2>
            @if($event->date < now())
                <div class="border border-green-900/30 rounded-lg p-6 text-center text-green-800">Tato akce již proběhla.</div>
            @elseif($isFull)
                <div class="border border-red-900/30 rounded-lg p-6 text-center text-red-700">Kapacita akce je plná.</div>
            @else
                <livewire:registration-form :event="$event" />
            @endif
        </section>
    </div>
</x-layouts.app>

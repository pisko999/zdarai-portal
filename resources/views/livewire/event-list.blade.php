<div>
    {{-- Filter tabs --}}
    <div class="flex gap-4 mb-8">
        <button wire:click="$set('filter', 'upcoming')"
            class="{{ $filter === 'upcoming' ? 'text-green-400 border-b border-green-400' : 'text-green-700 hover:text-green-500' }} pb-1 text-sm font-semibold transition">
            {{ __("messages.events.upcoming") }}
        </button>
        <button wire:click="$set('filter', 'past')"
            class="{{ $filter === 'past' ? 'text-green-400 border-b border-green-400' : 'text-green-700 hover:text-green-500' }} pb-1 text-sm font-semibold transition">
            {{ __("messages.events.archive") }}
        </button>
    </div>

    @forelse($events as $event)
        @php
            $registered = $event->registrationCount();
            $isFull = $event->isFull();
            $pct = $event->capacity > 0 ? round(($registered / $event->capacity) * 100) : 0;
        @endphp
        <div class="border border-green-900/40 rounded-lg mb-4 hover:border-green-700/60 transition bg-gray-900/30">
            <div class="p-6">
                <div class="flex items-start justify-between gap-4">
                    {{-- Date block --}}
                    <div class="text-center min-w-[52px]">
                        <div class="text-green-600 text-xs uppercase font-bold">{{ $event->date->format('M') }}</div>
                        <div class="text-green-400 text-3xl font-bold leading-none">{{ $event->date->format('j') }}</div>
                        <div class="text-green-800 text-xs">{{ $event->date->format('Y') }}</div>
                    </div>

                    {{-- Content --}}
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-2">
                            @if($event->price === null)
                                <span class="text-xs font-bold text-green-500 border border-green-800 px-2 py-0.5 rounded">{{ __("messages.events.free") }}</span>
                            @else
                                <span class="text-xs font-bold text-yellow-500 border border-yellow-800 px-2 py-0.5 rounded">{{ number_format((float) $event->price, 0) }} Kč</span>
                            @endif
                            @if($isFull)
                                <span class="text-xs font-bold text-red-500 border border-red-800 px-2 py-0.5 rounded">{{ __("messages.events.full") }}</span>
                            @endif
                        </div>
                        <h2 class="text-white text-lg font-bold mb-1">
                            <a href="/udalosti/{{ $event->slug }}" class="hover:text-green-400 transition">{{ $event->title }}</a>
                        </h2>
                        <p class="text-green-700 text-sm mb-3 line-clamp-2">{{ $event->description }}</p>

                        {{-- Talks --}}
                        @if($event->talks->isNotEmpty())
                            <div class="flex flex-wrap gap-2 mb-3">
                                @foreach($event->talks as $talk)
                                    <span class="text-xs text-green-700 bg-green-950/50 border border-green-900/50 rounded px-2 py-0.5">
                                        🎙️ {{ $talk->title }}
                                    </span>
                                @endforeach
                            </div>
                        @endif

                        {{-- Capacity bar --}}
                        <div class="h-1 bg-green-950 rounded-full mb-1">
                            <div class="h-1 rounded-full transition-all {{ $pct > 80 ? 'bg-red-500' : 'bg-green-600' }}"
                                style="width: {{ $pct }}%"></div>
                        </div>
                        <div class="text-xs text-green-800">
                            {{ __("messages.events.capacity", ['registered' => $registered, 'capacity' => $event->capacity]) }}
                        </div>
                    </div>

                    {{-- CTA --}}
                    @if($filter === 'upcoming' && !$isFull)
                        <a href="/udalosti/{{ $event->slug }}#registrace"
                           class="flex-shrink-0 bg-green-600 hover:bg-green-500 text-gray-950 font-bold text-sm px-4 py-2 rounded transition">
                            {{ __("messages.events.register") }}
                        </a>
                    @elseif($isFull)
                        <span class="flex-shrink-0 text-red-700 border border-red-900 text-sm px-4 py-2 rounded opacity-50">{{ __("messages.events.full") }}</span>
                    @endif
                </div>
            </div>
        </div>
    @empty
        <div class="text-center py-16 text-green-800">
            <div class="text-4xl mb-4">🤖</div>
            <div class="text-lg">{{ $filter === 'upcoming' ? __("messages.events.empty.upcoming") : __("messages.events.empty.past") }}</div>
        </div>
    @endforelse
</div>

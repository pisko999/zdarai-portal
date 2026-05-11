<div>
    {{-- Filter tabs --}}
    <div class="flex gap-4 mb-8">
        <button wire:click="$set('filter', 'upcoming')"
            class="{{ $filter === 'upcoming' ? 'text-green-400 border-b border-green-400' : 'text-gray-600 hover:text-gray-400' }} pb-1 text-sm font-semibold transition mono">
            {{ __("messages.events.upcoming") }}
        </button>
        <button wire:click="$set('filter', 'past')"
            class="{{ $filter === 'past' ? 'text-green-400 border-b border-green-400' : 'text-gray-600 hover:text-gray-400' }} pb-1 text-sm font-semibold transition mono">
            {{ __("messages.events.archive") }}
        </button>
    </div>

    @forelse($events as $index => $event)
        @php
            $registered = $event->registrationCount();
            $isFull = $event->isFull();
            $pct = $event->capacity > 0 ? round(($registered / $event->capacity) * 100) : 0;
            $isFeatured = $filter === 'upcoming' && $index === 0;
            $dayNames = ['ne', 'po', 'út', 'st', 'čt', 'pá', 'so'];
            $dayName = $dayNames[$event->date->dayOfWeek];
            $dateStr = $dayName . ' ' . $event->date->format('j.n.Y') . ' // ' . $event->date->format('H:i');
        @endphp
        <div class="mb-4 {{ $isFeatured ? 'border border-green-500/40 rounded-lg p-6 bg-green-500/5 hover:bg-green-500/10 transition glow' : 'border border-gray-800 rounded-lg p-6 bg-gray-900/50 hover:border-gray-700 transition' }}">
            {{-- Date --}}
            <div class="mono text-green-400/60 text-xs mb-2">{{ $dateStr }}</div>

            {{-- Title --}}
            <h2 class="{{ $isFeatured ? 'text-2xl font-bold mb-2' : 'text-xl font-bold text-gray-300 mb-2' }}">
                <a href="/udalosti/{{ $event->slug }}" class="hover:text-green-400 transition">{{ $event->title }}</a>
            </h2>

            {{-- Description --}}
            <p class="text-gray-400 text-sm mb-4 line-clamp-2">{{ $event->description }}</p>

            {{-- Talks + Speaker avatars --}}
            @if($event->talks->isNotEmpty())
                <div class="flex flex-wrap items-center gap-3 mb-4">
                    @foreach($event->talks as $talk)
                        <div class="flex items-center gap-1.5">
                            @if($talk->speaker)
                                @if($talk->speaker->avatar)
                                    <img src="{{ Storage::url($talk->speaker->avatar) }}" alt="{{ $talk->speaker->name }}"
                                         class="w-5 h-5 rounded-full border border-gray-700 object-cover">
                                @else
                                    <div class="w-5 h-5 rounded-full border border-gray-700 bg-gray-800 flex items-center justify-center text-xs text-gray-400 font-bold">
                                        {{ mb_substr($talk->speaker->name, 0, 1) }}
                                    </div>
                                @endif
                            @endif
                            <span class="text-xs text-gray-500">{{ $talk->title }}</span>
                        </div>
                    @endforeach
                </div>
            @endif

            {{-- Bottom row --}}
            <div class="flex items-end justify-between gap-4">
                <div class="flex-1">
                    {{-- Capacity bar --}}
                    <div class="h-1 bg-gray-800 rounded mb-1.5">
                        <div class="h-1 rounded transition-all {{ $pct > 80 ? 'bg-red-500' : 'bg-green-400' }}"
                             style="width: {{ $pct }}%"></div>
                    </div>
                    <div class="flex items-center gap-3 text-xs">
                        @if($event->price === null)
                            <span class="{{ $isFeatured ? 'font-bold text-green-400' : 'text-gray-400 font-bold' }}">{{ __("messages.events.free") }}</span>
                        @else
                            <span class="font-bold text-yellow-400">{{ number_format((float) $event->price, 0) }} Kč</span>
                        @endif
                        @if($isFull)
                            <span class="font-bold text-red-400">{{ __("messages.events.full") }}</span>
                        @else
                            <span class="text-gray-600">{{ __("messages.events.capacity", ['registered' => $registered, 'capacity' => $event->capacity]) }}</span>
                        @endif
                    </div>
                </div>

                {{-- CTA --}}
                @if($filter === 'upcoming' && !$isFull)
                    <a href="/udalosti/{{ $event->slug }}#registrace"
                       class="{{ $isFeatured ? 'px-4 py-2 bg-green-400 text-gray-950 text-sm font-bold rounded hover:bg-green-300 transition' : 'px-4 py-2 border border-gray-700 text-gray-400 text-sm rounded hover:border-gray-500 transition' }}">
                        {{ __("messages.events.register") }}
                    </a>
                @elseif($isFull)
                    <span class="px-4 py-2 border border-red-900 text-red-700 text-sm rounded opacity-50">{{ __("messages.events.full") }}</span>
                @endif
            </div>
        </div>
    @empty
        <div class="text-center py-16 text-gray-600">
            <div class="text-4xl mb-4">🤖</div>
            <div class="text-lg mono">{{ $filter === 'upcoming' ? __("messages.events.empty.upcoming") : __("messages.events.empty.past") }}</div>
        </div>
    @endforelse
</div>

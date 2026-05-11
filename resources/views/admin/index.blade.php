<x-layouts.admin title="Dashboard">
    <h1 class="text-2xl font-bold text-white mb-8">&gt;_ Dashboard</h1>

    {{-- Stats grid --}}
    @php
        $totalEvents = \App\Models\Event::count();
        $publishedEvents = \App\Models\Event::where('status', 'published')->count();
        $upcomingEvents = \App\Models\Event::where('status', 'published')->where('date', '>=', now())->count();
        $totalTalks = \App\Models\Talk::count();
        $totalSpeakers = \App\Models\Speaker::count();
        $totalRegistrations = \App\Models\Registration::whereNotIn('payment_status', ['cancelled'])->count();
        $paidRegistrations = \App\Models\Registration::where('payment_status', 'paid')->count();
        $pendingRegistrations = \App\Models\Registration::where('payment_status', 'pending')->count();
        $freeRegistrations = \App\Models\Registration::where('payment_status', 'free')->count();
        $cancelledRegistrations = \App\Models\Registration::where('payment_status', 'cancelled')->count();
    @endphp

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <div class="border border-green-900/40 rounded-lg p-5 bg-gray-900/30">
            <div class="text-green-700 text-xs uppercase font-bold mb-1">Události celkem</div>
            <div class="text-3xl font-bold text-green-400">{{ $totalEvents }}</div>
            <div class="text-xs text-green-800 mt-1">{{ $publishedEvents }} publikovaných · {{ $upcomingEvents }} nadcházejících</div>
        </div>
        <div class="border border-green-900/40 rounded-lg p-5 bg-gray-900/30">
            <div class="text-green-700 text-xs uppercase font-bold mb-1">Přednášky</div>
            <div class="text-3xl font-bold text-green-400">{{ $totalTalks }}</div>
            <div class="text-xs text-green-800 mt-1">{{ $totalSpeakers }} přednášejících</div>
        </div>
        <div class="border border-green-900/40 rounded-lg p-5 bg-gray-900/30">
            <div class="text-green-700 text-xs uppercase font-bold mb-1">Registrace</div>
            <div class="text-3xl font-bold text-green-400">{{ $totalRegistrations }}</div>
            <div class="text-xs text-green-800 mt-1">{{ $cancelledRegistrations }} zrušeno</div>
        </div>
        <div class="border border-green-900/40 rounded-lg p-5 bg-gray-900/30">
            <div class="text-green-700 text-xs uppercase font-bold mb-1">Zaplaceno</div>
            <div class="text-3xl font-bold text-yellow-400">{{ $paidRegistrations }}</div>
            <div class="text-xs text-green-800 mt-1">{{ $pendingRegistrations }} čekají · {{ $freeRegistrations }} zdarma</div>
        </div>
    </div>

    {{-- Upcoming events --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div>
            <h2 class="text-sm font-bold text-green-600 uppercase mb-3">Nadcházející události</h2>
            <div class="border border-green-900/40 rounded-lg overflow-hidden">
                @php
                    $upcoming = \App\Models\Event::where('status', 'published')
                        ->where('date', '>=', now())
                        ->withCount(['registrations as active_reg_count' => fn($q) => $q->whereNotIn('payment_status', ['cancelled'])])
                        ->orderBy('date')
                        ->limit(5)
                        ->get();
                @endphp
                @forelse($upcoming as $event)
                    <div class="flex items-center justify-between px-4 py-3 border-b border-green-900/20 last:border-0 hover:bg-gray-900/30 transition">
                        <div>
                            <div class="text-white text-sm font-medium">{{ $event->title }}</div>
                            <div class="text-green-700 text-xs">{{ $event->date->format('j. n. Y H:i') }}</div>
                        </div>
                        <div class="text-right">
                            <div class="text-green-400 text-sm font-bold">{{ $event->active_reg_count }}/{{ $event->capacity }}</div>
                            <div class="text-green-900 text-xs">registrací</div>
                        </div>
                    </div>
                @empty
                    <div class="px-4 py-6 text-center text-green-800 text-sm">Žádné nadcházející události.</div>
                @endforelse
            </div>
        </div>

        {{-- Recent registrations --}}
        <div>
            <h2 class="text-sm font-bold text-green-600 uppercase mb-3">Poslední registrace</h2>
            <div class="border border-green-900/40 rounded-lg overflow-hidden">
                @php
                    $recentRegs = \App\Models\Registration::with('event')
                        ->orderByDesc('created_at')
                        ->limit(8)
                        ->get();
                @endphp
                @forelse($recentRegs as $reg)
                    <div class="flex items-center justify-between px-4 py-2.5 border-b border-green-900/20 last:border-0 hover:bg-gray-900/30 transition">
                        <div>
                            <div class="text-white text-sm">{{ $reg->name }}</div>
                            <div class="text-green-800 text-xs">{{ $reg->event?->title ?? '—' }}</div>
                        </div>
                        <div class="text-right">
                            <span class="text-xs px-2 py-0.5 rounded font-bold
                                {{ match($reg->payment_status) {
                                    'paid' => 'bg-green-900/50 text-green-400',
                                    'pending' => 'bg-yellow-900/40 text-yellow-500',
                                    'cancelled' => 'bg-red-900/30 text-red-600',
                                    default => 'bg-green-950 text-green-700',
                                } }}">
                                {{ match($reg->payment_status) {
                                    'paid' => 'Zaplaceno',
                                    'pending' => 'Čeká',
                                    'cancelled' => 'Zrušeno',
                                    default => 'Zdarma',
                                } }}
                            </span>
                            <div class="text-green-900 text-xs mt-0.5">{{ $reg->created_at->diffForHumans() }}</div>
                        </div>
                    </div>
                @empty
                    <div class="px-4 py-6 text-center text-green-800 text-sm">Žádné registrace.</div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Quick links --}}
    <div class="mt-6 flex flex-wrap gap-3">
        <a href="/admin/events" class="text-xs text-green-700 hover:text-green-500 border border-green-900/40 px-3 py-1.5 rounded transition">📅 Správa událostí</a>
        <a href="/admin/registrations" class="text-xs text-green-700 hover:text-green-500 border border-green-900/40 px-3 py-1.5 rounded transition">📋 Správa registrací</a>
        <a href="/admin/speakers" class="text-xs text-green-700 hover:text-green-500 border border-green-900/40 px-3 py-1.5 rounded transition">👤 Přednášející</a>
        <a href="/" target="_blank" class="text-xs text-green-800 hover:text-green-600 border border-green-900/20 px-3 py-1.5 rounded transition">🌐 Zobrazit portál ↗</a>
    </div>
</x-layouts.admin>
